<?php

	function dumpToConsole_TL($input, $excludePrior = false) {
		
		global $console;
		
		if ($excludePrior) $console = $input;
		else $console .= $input;
		
	}

?>
