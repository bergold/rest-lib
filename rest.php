<?php

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
    
    public function getParams() {
        return $this->params;
    }
    
    public function setParams($p) {
        $this->params = $p;
    }
    
}

$env = new Environment();


class ModuleLoader {
    
    private $modules = array();
    
    public function getDep($dep) {
        if (isset($this->modules[$dep])) {
            return $this->modules[$dep];
        } else {
            // [todo] load this depency
        }
    }
    
    public function getDeps($deps) {
        foreach ($deps as $i => $dep) {
            $deps[$i] = $this->getDep($dep);
        }
    }
    
    public function invoke($handler) {
        global $env;
        echo "<i>matched route: </i><b>$handler</b><i> with params </i><b>"; var_dump($env->getParams()); echo "</b>";
    }
    
}

$moduleloader = new ModuleLoader();


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


function when($path, $handler) {
    global $router;
    $router->when($path, $handler);
}

function otherwise($handler) {
    global $router;
    $router->otherwise($handler);
}

function handler() {
    
}

function module() {
    
}

function response($data, $type = 'json') {
    
}
