<?php

	if (@$systemPreferences['Delete downloadables after']) {

		$downloadPath = __DIR__ . '/../../../web_root/downloads';
		$numberOfItemsDeleted = 0;
		
		// make sure download directory exists
			if (!file_exists($downloadPath)) {
				$logger->logItInMemory("Error encountered during " . pathinfo(__FILE__, PATHINFO_FILENAME) . ": Can't find download directory at " . $downloadPath);
				$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
			}
			else {
			
				// retrieve subfolders
					$downloads = $directory_manager->read($downloadPath, false, true, false);
					
				// delete expired downloadables
					for ($counter = 0; $counter < count($downloads); $counter++) {
						$timeCreated = strtotime(substr($this->downloads[$counter], 1, 10) . ' ' . substr($this->downloads[$counter], 12, 2) . ':' . substr($this->downloads[$counter], 15, 2) . ':' . substr($this->downloads[$counter], 18, 2));
						$createdOn = date('Y-m-d H:i:s', $timeCreated);
						$expiresOn = date('Y-m-d H:i:s', mktime(date('H', $timeCreated), date('i', $timeCreated), date('s', $timeCreated), date('m', $timeCreated), date('d', $timeCreated) + floatval($systemPreferences['Delete downloadables after']), date('Y', $timeCreated)));

						if ($expiresOn < date('Y-m-d H:i:s')) {
							
							// delete subfolder
								$success = $directory_manager->remove($downloads[$counter]);
								
							// check if successful
								if ($success) $numberOfItemsDeleted++;
								else {
									$activity = "Error encountered during " . pathinfo(__FILE__, PATHINFO_FILENAME) . ": Unknown error attempting to delete the folder " . $downloads[$counter];
									
									$logger->logItInMemory($activity);
									$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
									
									$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);
									emailSystemNotification($activity, 'Critical error');
								}
								
						}
					}
					
				// update log
					$logger->logItInMemory("Removed " . floatval($numberOfItemsDeleted) . " item(s)");
					$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

			}
			
	}

?>
