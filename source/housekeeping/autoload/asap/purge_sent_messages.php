<?php

	if (@$tl->settings['Purge sent messages from mail queue after']) {

		// calculate expiry
			$expiry = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') - $tl->settings['Purge sent messages from mail queue after'], date('Y')));
			
			$output .= "Looking for archived messages prior to " . $expiry . "\n";

		// delete keys
			$deleted = deleteFromDb('mail_queue', null, null, null, null, "sent_on != '0000-00-00 00:00:00' AND sent_on < '" . $expiry . "'");

			$output .= "Deleted " . floatval($deleted) . " archived sent message(s)\n";

	}
	else {
		$output .= "No expiry provided in settings\n";
	}

?>
