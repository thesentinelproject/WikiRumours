<?php

	if (@$systemPreferences['Purge sent messages from mail queue after']) {

		// calculate expiry
			$expiry = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') - $systemPreferences['Purge sent messages from mail queue after'], date('Y')));
			$logger->logItInMemory("Looking for archived messages prior to " . $expiry);
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

		// delete keys
			$deleted = deleteFromDb('mail_queue', null, null, null, null, "sent_on != '0000-00-00 00:00:00' AND sent_on < '" . $expiry . "'");
			$logger->logItInMemory("Deleted " . floatval($deleted) . " archived sent message(s)");
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

	}
	else {
		$logger->logItInMemory("No expiry provided in settings");
		$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
	}

?>
