<?php

	if (@$tl->settings['Keep logs for']) {

		// calculate expiry
			$expiry = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') - floatval($tl->settings['Keep logs for']), date('Y')));

		// delete logs
			$output .= "Looking for logs prior to " . $expiry . "\n";

			$deleted = deleteFromDb('log_tasks', null, null, null, null, "completed_on < '" . $expiry . "'");
			$deleted = deleteFromDb('logs', null, null, null, null, "connected_on < '" . $expiry . "'");

			$output .= "Deleted " . floatval($deleted) . " expired log(s)\n";

		// delete external API calls
			$output .= "Looking for API calls prior to " . $expiry . "\n";

			$deleted = deleteFromDb('api_calls_external', null, null, null, null, "queried_on < '" . $expiry . "'");

			$output .= "Deleted " . floatval($deleted) . " expired api calls(s)\n";

	}
	else {
		$output .= "No expiry provided in settings\n";
	}

?>
