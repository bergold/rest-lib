rest-lib
========

With this PHP libary you're able to develop fast and powerfull php-backends.
> Informations: http://de.wikipedia.org/wiki/Representational_State_Transfer

For full documentation see https://github.com/bergold/rest-lib/wiki

Usage
-----

Make sure every request comes to index.php
```
# .htaccess
RewriteEngine On
RewriteRule . index.php [L]
```

First you have to require rest.php in your index.php:
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
If the path matches '/', the files 'afile.php' and 'another.php' are included and the handler _base_ is invoked.
> The files are loaded relative to your index.php

### Define a handler
```php
handler('base', function() {
  // do anything
  
  // define the response
  response([
    "for" => "example",
    "json" => "data"
  ], "json");
});
```

### Use modules
```php
// define a module
module('amodule', function() {
  return "foo";
});

// define a handler and inject module _amodule_
handler('ahandler', function($amodule) {
  // $amodule == "foo"
});
```


### Load other files
```php
needs('this/file');
```
will load the file 'this/file.php'

> Important: If you don't use "dependencies" in your route, make sure your handlers are loaded before the route definition
