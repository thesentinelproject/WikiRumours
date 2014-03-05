<?php

	function redirectForMobile($specificPath) {
		
		global $environmentals;
		
		$detect = new Mobile_Detect;

		if ($detect->isMobile()) {

			if ($environmentals['subdomain'] != 'm') {
				if ($specificPath) $url = $specificPath;
				else $url = $environmentals['folder'] . $environmentals['page'];
				
				header('Location: http://m.' . $environmentals['domain'] . $url);
				exit();
			}
		}
	
	}

?>
