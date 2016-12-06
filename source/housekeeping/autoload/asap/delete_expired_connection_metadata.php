<?php

	if (@$tl->settings['Keep connection metadata for']) {

		// calculate expiry
			$expiry = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') - floatval($tl->settings['Keep connection metadata for']), date('Y')));

		// delete metadata
			$output .= "Looking for metadata prior to " . $expiry . "\n";

			$deleted = deleteFromDb('browser_connections', null, null, null, null, "connected_on < '" . $expiry . "'");

			$output .= "Deleted " . floatval($deleted) . " expired metadata\n";

	}
	else {
		$output .= "No expiry provided in settings\n";
	}

?>
