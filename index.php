<?php

require 'rest.php';

// example:

module('onemodule', function() {
	return "emil";
});

handler('base', function($onemodule) {
	return response("base: hi " . $onemodule, "text");
});

handler('user', function($env, $onemodule) {
	return response([
		"name" => $onemodule,
		"id" => $env->getParam('userId'),
		"method" => $env->getMethod(),
		"stamp" => "23496812649",
		"foo" => [
			"bar",
			"with",
			"salt"
		]
	]);
});

/* function when
 * 
 * @param path {string} the pattern to match
 * @param route 1. {string} the name of the handler to load
 *              2. {array}  an array like [ "handler"=> "thenameofthehandler", "dependencies"=> "files to load (with modules or handlers)" ]
 */
when("/", [
	"handler"   => "base",
	"dependencies" => []
]);

when("/user/:userId", "user");

when("/info/:cmd*/g/:add?", "user");

otherwise("error");
