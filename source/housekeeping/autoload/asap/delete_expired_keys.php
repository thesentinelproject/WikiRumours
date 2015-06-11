<?php

	// delete keys
		$deleted = deleteFromDb('user_keys', null, null, null, null, $tablePrefix . "expiry < '" . date('Y-m-d H:i:s') . "'");

	// update log
		$logger->logItInMemory("Deleted " . floatval($deleted) . " expired key(s)");
		$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

?>
