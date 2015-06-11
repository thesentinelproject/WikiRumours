<?php

	if (@$systemPreferences['Purge sent messages from mail queue after']) {

		// delete keys
			$expiry = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - $systemPreferences['Purge sent messages from mail queue after'], date('Y')));
			$deleted = deleteFromDb('mail_queue', null, null, null, null, $tablePrefix . "sent_on != '0000-00-00 00:00:00' AND " . $tablePrefix . "saved_on < '" . $expiry . "'");

		// update log
			$logger->logItInMemory("Deleted " . floatval($deleted) . " archived sent message(s)");
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

	}

?>
