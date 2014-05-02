rest-lib
========

With this PHP libary you're able to develop fast and powerfull php-backends.
> Informations: http://de.wikipedia.org/wiki/Representational_State_Transfer

For full documentation see https://github.com/bergold/rest-lib/wiki

Getting started
---------------

Make sure every request comes to `index.php`
```
# .htaccess
RewriteEngine On
RewriteRule . index.php [L]
```

First you have to require `rest.php` in your index.php:
```php
<?php
// index.php

// first require rest.php
require 'rest.php';

```

Now, you can start with the realy interesting stuff.

### Define a route:
```php
when('/', [
  "handler" => "base",
  "dependencies" => ['afile', 'another']
]);
```
The string '/' is matched against `$env->getPath()`.
If the path matches, the files 'afile.php' and 'another.php' are included and the handler _base_ is invoked.

```php
when('/user/:id/action/:action*', "base");
```
This route will match `/user/324/action/repos/new` and extract:
* `id: 324`
* `action: 'repos/new'`

This data is stored in `$routeParams` and the handler _base_ is invoked.

### Define a handler
```php
handler('base', function() {
  // do anything
  
  // define the response
  response([
    "for" => "example",
    "json" => "data"
  ], "json", 200);
});
```

The `response`-call sets the `Content-Type: application/json`, the `http_response_code(200)` and echos a parsed json.
The script exits immediately.

### Use modules
```php
// define a module
module('amodule', function() {
  return "foo";
});

// define a handler and inject module 'amodule'
handler('ahandler', function($amodule) {
  // $amodule == "foo"
});
```


### Load other files
```php
needs('this/file');
```
This will load the file 'this/file.php'

> Important: If you don't use "dependencies" in your route, make sure your handlers are loaded before the route definition
