<?php
	if (@$tl->settings['Pending registrations auto-deleted after']) {

		// calculate expiry
			$expiry = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') - $tl->settings['Pending registrations auto-deleted after'], date('Y')));
			
			$output .= "Looking for registrations prior to " . $expiry . "\n";

		// delete registrants
			$deleted = deleteFromDb('registrations', null, null, null, null, "registered_on < '" . $expiry . "'");

			$output .= "Deleted " . floatval($deleted) . " registrant(s)\n";

	}
	else {
		$output .= "No expiry provided in settings\n";
	}

?>
