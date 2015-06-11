<?php

	// delete logs
		$expiryDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - floatval($systemPreferences['Keep logs for']), date('Y')));
		$numberOfLogsDeleted = deleteFromDb('logs', null, null, null, null, $tablePrefix . "connected_on < '" . $expiryDate . "'");

	// update log
		$logger->logItInMemory("Deleted " . floatval($numberOfLogsDeleted) . " expired log(s)");
		$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

?>
