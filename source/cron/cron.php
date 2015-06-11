#!/usr/local/bin/php -q
<?php

		$isCron = true;
		include __DIR__ . "/../web_root/initialize.php";
		if ($systemPreferences['Enable cron connections'] && $systemPreferences['Interval between cron connections'] > 0) include __DIR__ . "/../web_root/housekeeping.php";
		$dbConnection->close();
		
?>
