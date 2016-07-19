<?php

	if (@$systemPreferences['Keep connection metadata for']) {

		// calculate expiry
			$expiry = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') - floatval($systemPreferences['Keep connection metadata for']), date('Y')));

		// delete metadata
			$logger->logItInMemory("Looking for metadata prior to " . $expiry);
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
			$deleted = deleteFromDb('browser_connections', null, null, null, null, "connected_on < '" . $expiry . "'");
			$logger->logItInMemory("Deleted " . floatval($deleted) . " expired metadata");
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

	}
	else {
		$logger->logItInMemory("No expiry provided in settings");
		$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
	}

?>
