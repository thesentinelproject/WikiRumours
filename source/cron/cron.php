#!/usr/local/bin/php -q
<?php

		$isCron = true;
		$pathToWebRoot = '../web_root/';
		include "../web_root/initialize.php";
		if ($cronConnectionIntervalInMinutes) include "../web_root/housekeeping.php";
		$dbConnection->close();
		
?>
