<?php
	if (@$systemPreferences['Pending registrations auto-deleted after']) {

		// calculate expiry
			$expiry = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') - $systemPreferences['Pending registrations auto-deleted after'], date('Y')));
			$logger->logItInMemory("Looking for registrations prior to " . $expiry);
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

		// delete registrants
			$deleted = deleteFromDb('registrations', null, null, null, null, "registered_on < '" . $expiry . "'");
			$logger->logItInMemory("Deleted " . floatval($deleted) . " registrant(s)");
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

	}
	else {
		$logger->logItInMemory("No expiry provided in settings");
		$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
	}

?>
