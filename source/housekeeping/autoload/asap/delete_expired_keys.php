<?php

	// delete keys
		$deleted = deleteFromDb('user_keys', null, null, null, null, "expiry != '0000-00-00 00:00:00' AND expiry < '" . date('Y-m-d H:i:s') . "'");

	// update log
		$output .= "Deleted " . floatval($deleted) . " expired key(s)\n";

?>
