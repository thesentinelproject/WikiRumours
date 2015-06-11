<?php

	if (@$numberOfDaysToPreserveRegistations) {

		// delete registrants
			$expiry = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - $systemPreferences['Pending registrations auto-deleted after'], date('Y')));
			$deleted = deleteFromDb('registrations', null, null, null, null, $tablePrefix . "registered_on < '" . $expiry . "'");

		// update log
			$logger->logItInMemory("Deleted " . floatval($deleted) . " registrant(s)");
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

	}

?>
