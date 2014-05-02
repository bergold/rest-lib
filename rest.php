<?php

// util
function func_get_argNames($funcName) {
    $f = new ReflectionFunction($funcName);
    $result = array();
    foreach ($f->getParameters() as $param) {
        $result[] = $param->name;   
    }
    return $result;
}


// class Config
class Config {
	
	private static $c = [
		'DEPS_BASE_PATH' => '',
		'ALLOW_MODULE_OVERWRITE' => false,
		'JSON_PRETTY_PRINT' => false
	];
	
	static function set($key, $val) {
		if (isset(self::$c[$key]))
			self::$c[$key] = $val;
	}
	
	static function get($key) {
		return self::$c[$key];
	}
	
}


// class Environment
class Environment {
    
    private $params = array();
    
    public function getBaseURL() {
        return preg_replace("/\/index\.php$/", "", $_SERVER['SCRIPT_NAME']);
    }
    
    public function getPath() {
        $p = preg_replace("/^" . preg_quote($this->getBaseURL(), "/") . "/", "", $_SERVER['REQUEST_URI']);
        $p = preg_replace("/\?.*$/", "", $p);
        return $p;
    }
    
    public function getArgs() {
        return $_GET;
    }
    
    public function getHeaders() {
    	return getallheaders();
    }
    
    public function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    public function getParams() {
        return $this->params;
    }
    public function getParam($key) {
    	return $this->getParams()[$key];
    }
    
    public function setParams($p) {
        $this->params = $p;
    }
    
    public function getData() {
    	return file_get_contents('php://input');
    }
    
}

$env = new Environment();


// class ModuleLoader
class ModuleLoader {
    
    private $includes = array();
    private $modules  = array();
    private $handler  = array();
    
    public function needs($dep) {
    	$path = config('DEPS_BASE_PATH') . $dep . ".php";
    	if (isset($this->includes[$dep])) return true;
    	if (!is_file($path)) throw new Exception("FILE_NOT_FOUND: The file '$path' was not found");
    	include $path;
    	return true;
    }
    
    public function compile($fn) {
    	$deps = func_get_argNames($fn);
    	$deps = $this->getDeps($deps);
    	return call_user_func_array($fn, $deps);
    }
    
    public function getDep($dep) {
    	global $env;
        if (!isset($this->modules[$dep])) throw new Exception("MODULE_NOT_FOUND: The module '$dep' was not found");
        return $this->compile($this->modules[$dep]);
    }
    
    public function addModule($name, $fn) {
    	if (isset($this->modules[$name]) && !config('ALLOW_MODULE_OVERWRITE')) throw new Exception("MODULE_OVERWRITE_FORBIDDEN: The module '$name' already exists");
    	$this->modules[$name] = $fn;
    }
    
    public function addHandler($name, $fn) {
    	$this->handler[$name] = $fn;
    }
    
    public function getDeps($deps) {
        foreach ($deps as $i => $dep) {
            $deps[$i] = $this->getDep($dep);
        }
        return $deps;
    }
    
    public function invoke($route) {
        global $env;
        $handler = is_array($route) ? $route['handler']      : $route;
        $deps    = is_array($route) ? $route['dependencies'] : [];
        foreach ($deps as $dep) {
        	$this->needs($dep);
        }
        if (!isset($this->handler[$handler])) throw new Exception("HANDLER_NOT_FOUND: The handler '$handler' was not found");
        $fn = $this->handler[$handler];
    	$this->compile($fn);
    }
    
}

$moduleloader = new ModuleLoader();


// class Router
class Router {
    
    private $matched = false;
    private $routes = array();
    
    public function when($path, $handler) {
        global $env, $moduleloader;
        
        if ($this->matched) return;
        
        $route = $this->pathRegExp($path);
        $route['handler'] = $handler;
        
        $params = $this->switchRouteMatcher($env->getPath(), $route);
        
        if (false !== $params) {
            $this->matched = true;
            $env->setParams($params);
            $moduleloader->invoke($handler);
        }
    }
    
    public function otherwise($handler) {
        global $moduleloader;
        
        if ($this->matched) return;
        
        $moduleloader->invoke($handler);
    }
    
    private function pathRegExp($path) {
        $ret = array(
            "originalPath" => $path,
            "regexp"       => $path
        );
        $keys = array();

        $callback = function($matches) use (&$keys) {
            $_      = $matches[0];
            $slash  = isset($matches[1]) ? $matches[1] : null;
            $key    = isset($matches[2]) ? $matches[2] : null;
            $option = isset($matches[3]) ? $matches[3] : null;

            $optional = ($option === "?") ? $option : null;
            $star     = ($option === "*") ? $option : null;
            $slash    = $slash ? $slash : '';
            array_push($keys, array("name" => $key, "optional" => !!$optional));

            return ''
                . ($optional ? '' : $slash)
                . '(?:'
                . ($optional ? $slash : '')
                . ($star ? '(.+?)' : '([^/]+)')
                . ($optional ? $optional : '')
                . ')'
                . ($optional ? $optional : '');
        };

        $path = preg_replace("/([().])/", "\\\\$1", $path);
        $path = preg_replace_callback("/(\/)?:(\w+)([\?\*])?/", $callback, $path);
        $path = preg_replace("/([\/$\*])/", "\\\\$1", $path);

        $ret['regexp'] = "/^" . $path . "$/i";
        $ret['keys'] = $keys;
        return $ret;
    }
    
    private function switchRouteMatcher($on, $route) {
        $keys = $route['keys'];
        $params = array();
        
        if (!$route['regexp']) return false;
        
        preg_match($route['regexp'], $on, $m);
        if (!$m) return false;
        
        for ($i = 1; $i < count($m); ++$i) {
            $key = $keys[$i - 1];
            
            $val = gettype($m[$i]) == 'string' ? urldecode($m[$i]) : $m[$i];

            if ($key && $val) {
                $params[$key['name']] = $val;
            }
        }
        return $params;
    }
    
}

$router = new Router();


// class ResponseParser
class ResponseParser {
	
	private $map_type_mimes = [
		"text" => "text/plain",
		"json" => "application/json"
	];
	
	public function generate($data, $type, $status) {
		header('Content-type: ' . (isset($this->map_type_mimes[$type]) ? $this->map_type_mimes[$type] : $type));
		http_response_code($status);
		switch ($type) {
			case 'json':
				echo $this->gen_json($data); break;
			default:
				echo $data; break;
		}
		exit($status);
	}
	
	private function gen_json($data) {
		if (config('JSON_PRETTY_PRINT')) return json_encode($data, JSON_PRETTY_PRINT);
		return json_encode($data);
	}
	
}

$responseparser = new ResponseParser();


// global functions
function config($key, $val = null) {
	if (is_null($val)) return Config::get($key);
	else Config::set($key, $val);
}

function when($path, $handler) {
    global $router;
    $router->when($path, $handler);
}

function otherwise($handler) {
    global $router;
    $router->otherwise($handler);
}

function handler($name, $fn) {
	global $moduleloader;
    $moduleloader->addHandler($name, $fn);
}

function module($name, $fn) {
	global $moduleloader;
    $moduleloader->addModule($name, $fn);
}

function needs($path) {
	global $moduleloader;
	$moduleloader->needs($path);
}

function response($data, $type = 'json', $status = 200) {
    global $responseparser;
    $responseparser->generate($data, $type, $status);
}


// standard modules
module('env', function() use($env) { return $env; });
module('routeParams', function() use($env) { return $env->getParams(); });
module('rawData', function() use($env) { return $env->getData(); });
module('reqMethod', function() use($env) { return $env->getMethod(); });
module('reqHeaders', function() use($env) { return $env->getHeaders(); });
module('args', function() use($env) { return $env->getArgs(); });
