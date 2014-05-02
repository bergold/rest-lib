<?php

class Det {
	public $subs = [
		"services" =>   [6.5,   8],
		"directives" => [1,     6],
		"filter" =>     [0.5,   1],
		"controller" => [4.25, 11],
		"background" => [3,     3],
		"ui" =>         [2.25, 10]
	];
	
	public function process($sub = null) {
		if (is_null($sub)) {
			$status = [0, 0];
			foreach ($this->subs as $sub) {
				$status[0] += $sub[0];
				$status[1] += $sub[1];
			}
			return ($status[0] / $status[1]) * 100;
		} else {
			$sub = $this->subs[$sub];
			return ($sub[0] / $sub[1]) * 100;
		}
	}
}

module('det', function() {
	return new Det();
});
