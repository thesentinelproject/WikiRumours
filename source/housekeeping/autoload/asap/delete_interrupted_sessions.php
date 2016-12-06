<?php

	if (@$tl->settings['Keep interrupted sessions for']) {

		// calculate expiry
			$expiry = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') - floatval($tl->settings['Keep interrupted sessions for']), date('Y')));

		// delete metadata
			$output .= "Looking for interrupted sessions prior to " . $expiry . "\n";
			
			$deleted = deleteFromDb('sessions', null, null, null, null, "connected_on < '" . $expiry . "'");
			
			$output .= "Deleted " . floatval($deleted) . " expired sessions\n";

	}
	else {
		$output .= "No expiry provided in settings\n";
	}

?>
