<?php

require 'rest.php';

// example:

config('JSON_PRETTY_PRINT', true);

handler('error', function($env) {
	return response([
		"error" => [
			"code" => "400",
			"msg" => "Invalid request"
		]
	], 'json', 400);
});

/* function when
 * 
 * @param path {string} the pattern to match
 * @param route 1. {string} the name of the handler to load
 *              2. {array}  an array like [ "handler"=> "thenameofthehandler", "dependencies"=> "files to load (with modules or handlers)" ]
 */
when("/", [
	"handler"   => "base",
	"dependencies" => ['handler']
]);

when("/det-chat", [
	"handler" => "det-chat",
	"dependencies" => ['handler']
]);

when("/i/want/to/check/:tests", [
	"handler" => "tester",
	"dependencies" => ["tests"]
]);

otherwise("error");
