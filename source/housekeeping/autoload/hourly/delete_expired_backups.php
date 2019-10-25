<?php

	if (@$tl->settings['Enable backups'] && @$tl->settings['Keep backups for']) {

		$folders = [
			'db',
			'vault',
			'uploads'
		];

		foreach ($folders as $folderName) {

			$destinationPath = __DIR__ . '/../../../backups/' . $folderName;

			$numberOfBackupsDeleted = 0;
		
			// make sure backup directory exists
				if (!file_exists($destinationPath)) {
					$activity = "Error encountered during " . pathinfo(__FILE__, PATHINFO_FILENAME) . ": Can't find " . $destinationPath;
					
					$output .= $activity . "\n";
					
					$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);

					emailSystemNotification($activity, 'Critical error');
				}
				else {
				
					// retrieve list of backups
						$expiryDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - floatval($tl->settings['Keep backups for']), date('Y')));
						
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
										$activity = "Error encountered during " . pathinfo(__FILE__, PATHINFO_FILENAME) . ": Unknown error attempting to delete " . $backups[$counter];
										
										$output .= $activity . "\n";
										
										$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);

										emailSystemNotification($activity, 'Critical error');
									}
									
							}

						}
						
				}
				
			// update log
				$output .= "Removed " . floatval($numberOfBackupsDeleted) . " expired backup(s)\n";

		}

	}
	else {
		$output .= "Backups disabled\n";
	}

?>
