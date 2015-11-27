<?php

	if ($systemPreferences['Enable database backups']) {

		$destinationPath = __DIR__ . '/../../../backups/db';
		$numberOfBackupsDeleted = 0;
		
		// make sure backup directory exists
			if (!file_exists($destinationPath)) {
				$activity = "Error encountered during " . pathinfo(__FILE__, PATHINFO_FILENAME) . ": Can't find destination directory for backups";
				
				$logger->logItInMemory($activity);
				$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
				
				$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);
				emailSystemNotification($activity, 'Critical error');
			}
			else {
			
				// retrieve list of backups
					$expiryDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - floatval($systemPreferences['Keep backups for']), date('Y')));
					$backups = $directory_manager->read($destinationPath, false, true);
					
				// delete expired backups
					for ($counter = 0; $counter < count($backups); $counter++) {
						$backups[$counter] = rtrim($backups[$counter], '/');
						$backups[$counter] = substr($backups[$counter], strrpos($backups[$counter], '/') + 1); // strip off parent folders
						if ($backups[$counter] < $expiryDate) {
							
							// delete folder
								$success = $directory_manager->remove($destinationPath . '/' . $backups[$counter]);
								
							// check if successful
								if ($success) $numberOfBackupsDeleted++;
								else {
									$activity = "Unknown error attempting to delete the expired backup " . $backups[$counter];
									
									$logger->logItInMemory($activity);
									$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
									
									$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);
									emailSystemNotification($activity, 'Critical error');
								}
								
						}
					}
					
			}
			
		// update log
			$logger->logItInMemory("Removed " . floatval($numberOfBackupsDeleted) . " expired backup(s)");
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

	}
	else {
		$logger->logItInMemory("Backups disabled");
		$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
	}

?>
