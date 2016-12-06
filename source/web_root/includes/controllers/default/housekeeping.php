<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in) $authentication_manager->forceLoginThenRedirectHere();
		
		if (!$logged_in['can_run_housekeeping']) $tl->page['error'] = "You aren't authorized to perform housekeeping.";

	// queries
		$action = $tl->page['parameter1'];
		if ($action == 'run') include 'housekeeping.php';
		elseif ($action == 'source_code') {
			if ($tl->page['parameter2']) $filters = $keyvalue_array->keyValueToArray(urldecode($tl->page['parameter2']), '|');
			$url = '../housekeeping/autoload/' . $filters['folder'] . '/' . $filters['script'] . '.php';
			if (!file_exists($url)) $tl->page['error'] = "Unable to locate source code at " . $url;
		}
		else {

			// bot status		
				if ($tl->settings['Enable cron connections'] && $tl->settings['Interval between cron connections'] > 0) $botStatus = "Enabled";
				else $botStatus = "Disabled";

			// previous cron
				$result = retrieveSingleFromDb('logs', null, array('connection_type'=>'R'), null, null, null, null, null, 'connected_on DESC');
				if (!count($result)) $previousCron = "Never";
				else $previousCron = $operators->howLongAgo($result[0]['connected_on']);

			// previous manual
				$result = retrieveSingleFromDb('logs', null, array('connection_type'=>'U'), null, null, null, null, null, 'connected_on DESC');
				if (!count($result)) $previousManual = "Never";
				else $previousManual = $operators->howLongAgo($result[0]['connected_on']);

		}

/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {
	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
			
	else {
	}
		
?>