<?php

	if ($systemPreferences['Enable database backups']) {

		$destinationPath = __DIR__ . '/../../../backups/db';
		
		// if destination path doesn't exist, stop
			if (!file_exists($destinationPath)) {
				$activity = "Error encountered during " . pathinfo(__FILE__, PATHINFO_FILENAME) . ": Can't find destination directory for backup";
				
				$logger->logItInMemory($activity);
				$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
				
				$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);
				emailSystemNotification($activity, 'Critical error');
				return false;
			}
			else {
							
				// check if daily backup already exists
					if (file_exists($destinationPath . '/' . date('Y-m-d'))) {
						$logger->logItInMemory("Today's backup already exists");
						$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
					}
					else {
							
						// create backup folder
							mkdir ($destinationPath . '/' . date('Y-m-d'));
											
						// create backup
							$logger->logItInMemory("Attempting to dump database to text file and compress");
							$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
								
							$backupFilename = $db_TL['Name'] . '_' . date("Y-m-d_H-i-s") . '.gz';
							$command = "mysqldump --opt -h " . $db_TL['Server'] . " -u" . $db_TL['User'] . " -p" . $db_TL['Password'] . " " . $db_TL['Name'] . " | gzip > " . $backupFilename;
							system($command);
									
							if (!file_exists($backupFilename)) {
								$activity = "Error encountered during " . pathinfo(__FILE__, PATHINFO_FILENAME) . ": Unable to create backup for some reason";
								
								$logger->logItInMemory($activity);
								$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
								
								$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);
								emailSystemNotification($activity, 'Critical error');
							}
							else {
							
								// move backup to appropriate directory
									$success = rename($backupFilename, $destinationPath . '/' . date('Y-m-d') . '/' . $backupFilename);
											
									if (!file_exists($destinationPath . '/' . date('Y-m-d') . '/' . $backupFilename)) {
										$activity = "Error encountered during " . pathinfo(__FILE__, PATHINFO_FILENAME) . ": Unable to create backup for some reason";
										
										$logger->logItInMemory($activity);
										$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
										
										$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);
										emailSystemNotification($activity, 'Critical error');
									}

							}

					}

			}

	}
	else {
		$logger->logItInMemory("Backups disabled");
		$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
	}
				
?>
