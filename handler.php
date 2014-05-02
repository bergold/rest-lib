<?php

needs('modules');

$examplehandler = function($det) {
	return response([
		$det->process(),
		[
			"services"   => $det->process("services"),
			"directives" => $det->process("directives"),
			"filter"     => $det->process("filter"),
			"controller" => $det->process("controller"),
			"background" => $det->process("background"),
			"ui"         => $det->process("ui"),
		]
	]);
};

handler('base', $examplehandler);

handler('det-chat', $examplehandler);
