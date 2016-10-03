<?php

	if (@$tl->settings['Keep logs for']) {

		// calculate expiry
			$expiry = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') - floatval($tl->settings['Keep logs for']), date('Y')));
			$logger->logItInMemory("Looking for logs prior to " . $expiry);
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

		// delete logs
			$deleted = deleteFromDb('logs', null, null, null, null, "connected_on < '" . $expiry . "'");
			$logger->logItInMemory("Deleted " . floatval($deleted) . " expired log(s)");
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

	}
	else {
		$logger->logItInMemory("No expiry provided in settings");
		$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
	}

?>
