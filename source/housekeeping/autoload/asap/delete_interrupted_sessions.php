<?php

	if (@$systemPreferences['Keep interrupted sessions for']) {

		// calculate expiry
			$expiry = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') - floatval($systemPreferences['Keep interrupted sessions for']), date('Y')));

		// delete metadata
			$logger->logItInMemory("Looking for interrupted sessions prior to " . $expiry);
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
			$deleted = deleteFromDb('sessions', null, null, null, null, "connected_on < '" . $expiry . "'");
			$logger->logItInMemory("Deleted " . floatval($deleted) . " expired sessions");
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

	}
	else {
		$logger->logItInMemory("No expiry provided in settings");
		$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
	}

?>
