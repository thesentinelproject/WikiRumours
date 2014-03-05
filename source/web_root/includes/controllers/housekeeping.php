<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in) forceLoginThenRedirectHere();
		
		if (!$logged_in['can_run_housekeeping']) $pageError = "You aren't authorized to perform housekeeping.";

		$areYouSure = $parameter1;
		
		if ($cronConnectionIntervalInMinutes > 0 && $numberOfMinutesSincePreviousCronConnection < 15) $botStatus = "<span class='label label-success'>ONLINE</span>";
		else $botStatus = "<span class='label label-danger'>OFFLINE</span>";
		
		$numberOfMinutesSincePreviousCronConnection = null;
		$previousCronConnection = retrieveFromDb('logs', array('connection_type'=>'R'), null, null, null, null, 'connected_on DESC', 1);
		if (count($previousCronConnection) > 0) $numberOfMinutesSincePreviousCronConnection = round((strtotime(date('Y-m-d H:i:s')) - strtotime($previousCronConnection[0]['connected_on'])) / 60, 0);
		
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