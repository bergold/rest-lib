rest-lib
========

With this PHP libary you're able to develop fast and powerfull php-backends.
Informations: http://de.wikipedia.org/wiki/Representational_State_Transfer

usage
-----

Make sure every request comes to index.php

.htaccess
```
RewriteEngine On
RewriteRule . index.php [L]
```

First you have to require rest.php in your index.php:

index.php
```php
<?php
// first require rest.php
require 'rest.php';

```

Now, you can start with the realy interesting stuff.

###Define a route:
```php
when('/', [
  "handler" => "base",
  "dependencies" => ['afile', 'another']
]);
```
The files 'afile.php' and 'another.php' are included and the handler _base_ is invoked.
> The files are loaded relative to your index.php

> Important: If you don't use "dependencies" in your route, make sure your handlers are loaded before the route definition
