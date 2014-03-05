<?php

	$connectionType = 'R';
	$startTimeInSeconds = time();
	$console = '';
	
	// begin logging new connection
		$activity = $connectionTypes[$connectionType]. ' connected';
		$logger->logItInMemory($activity);
		$logID = $logger->logItInDb($logger->retrieveLogFromMemory(), null, array('connection_type'=>$connectionType, 'connection_released'=>'0', ));

	// execute mandatory housekeeping tasks
		$mandatoryHousekeepingTasks = array(
			'removeOldLogs',
			'removeOldBackups',
			'removeOldKeys',
			'removeOldRegistrants'
		);
		
		foreach($mandatoryHousekeepingTasks as $function) {
			$logger->logItInMemory("Initiating " . $function);
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
			$function();
			$logger->logItInMemory("Terminating " . $function);
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
		}
		
	// execute rotating housekeeping tasks (use for resource-intensive or low-frequency tasks)
		$rotatingHousekeepingTasks = array(
			'databaseBackup'
		);

		$previousConnection = retrieveFromDb('logs', null, null, null, null, null, 'connected_on DESC', 1);
		
		if (count($rotatingHousekeepingTasks) > 0) {
			$nextHousekeepingTask = floatval($previousConnection[0]['task_counter']) + 1;
			if ($nextHousekeepingTask >= count($rotatingHousekeepingTasks)) $nextHousekeepingTask = 0;

			$logger->logItInMemory("Initiating " . $rotatingHousekeepingTasks[$nextHousekeepingTask]);
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
			$rotatingHousekeepingTasks[$nextHousekeepingTask]();
			$logger->logItInMemory("Terminating " . $rotatingHousekeepingTasks[$nextHousekeepingTask]);
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
		}
		
	// finish logging new connection
		$endTimeInSeconds = time();
		$connectionLengthInSeconds = max(1, $endTimeInSeconds - $startTimeInSeconds);
		$logger->logItInMemory($connectionTypes[$connectionType]. " connection terminated");
		$logger->logItInDb($logger->retrieveLogFromMemory(), $logID, array('connection_released'=>'1', 'connection_length_in_seconds'=>$connectionLengthInSeconds, 'task_counter'=>$nextHousekeepingTask));
		
	/*	-----------------------------
		Housekeeping functions
		----------------------------- */
			
		function databaseBackup() {
			
			global $systemPreferences;
			global $logger;
			global $logID;
			global $db_TL;
			global $mail_TL;
			global $phpmailerWrapper;
			
			$destinationPath = '../backups/db';
			
			// if destination path doesn't exist, stop
				if (!file_exists($destinationPath)) {
					$activity = "Can't find destination directory for backup";
					
					$logger->logItInMemory($activity);
					$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
					
					$logger->logItInDb($activity, null, array('error'=>'1', 'resolved'=>'0'));
					emailSystemNotification($activity, 'Critical error');
					return false;
				}
								
			// check if daily backup already exists
				if (file_exists($destinationPath . '/' . date('Y-m-d'))) {
					$logger->logItInMemory("Today's backup already exists");
					$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
					return true;
				}
						
			// create backup folder
				mkdir ($destinationPath . '/' . date('Y-m-d'));
								
			// create backup
				$logger->logItInMemory("Attempting to dump database to text file and compress");
				$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
					
				$backupFilename = $db_TL['Name'] . '_' . date("Y-m-d_H-i-s") . '.gz';
				$command = "mysqldump --opt -h " . $db_TL['Server'] . " -u" . $db_TL['User'] . " -p" . $db_TL['Password'] . " " . $db_TL['Name'] . " | gzip > " . $backupFilename;
				system($command);
						
				if (!file_exists($backupFilename)) {
					$activity = "Unable to create backup for some reason";
					
					$logger->logItInMemory($activity);
					$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
					
					$logger->logItInDb($activity, null, array('error'=>'1', 'resolved'=>'0'));
					emailSystemNotification($activity, 'Critical error');
					return false;
				}
				
			// move backup to appropriate directory
				$success = rename($backupFilename, $destinationPath . '/' . date('Y-m-d') . '/' . $backupFilename);
						
				if (!file_exists($destinationPath . '/' . date('Y-m-d') . '/' . $backupFilename)) {
					$activity = "Unable to create backup for some reason";
					
					$logger->logItInMemory($activity);
					$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
					
					$logger->logItInDb($activity, null, array('error'=>'1', 'resolved'=>'0'));
					emailSystemNotification($activity, 'Critical error');
					return false;
				}
								
			// email backup, if desired
				if ($mail_TL['AddressForBackups']) {
					$subject = '[' . $systemPreferences['appName'] . '] DB Backup';
					$message = 'Database successfully backed up and attached';
					$error = '';
					$success = $phpmailerWrapper->sendEmail($systemPreferences['appName'], $mail_TL['AddressForBackups'], $systemPreferences['appName'], $mail_TL['OutgoingAddress'], $subject, $message, $message, $destinationPath . '/' . date('Y-m-d'), '', '');
					if (!$success) {
						$activity = "Unable to email backup";
						
						$logger->logItInMemory($activity);
						$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
						
						$logger->logItInDb($activity, null, array('error'=>'1', 'resolved'=>'0'));
						emailSystemNotification($activity, 'Critical error');
						return false;
					}
				}
				
				return true;
				
		}
			
		function removeOldBackups() {
			
			global $logger;
			global $logID;
			global $numberOfDaysToPreserveDbBackups;
			
			$numberOfBackupsDeleted = 0;
			
			// make sure backup directory exists
				if (!file_exists('../backups/db')) {
					$activity = "Can't find destination directory for backups";
					
					$logger->logItInMemory($activity);
					$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
					
					$logger->logItInDb($activity, null, array('error'=>'1', 'resolved'=>'0'));
					emailSystemNotification($activity, 'Critical error');
					return false;
				}
				else {
				
					// retrieve list of backups
						$expiryDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - floatval($numberOfDaysToPreserveDbBackups), date('Y')));
						$directoryManager = new directoryManager_TL();
						$backups = $directoryManager->read('../backups/db', false, true);
						
					// delete expired backups
						for ($counter = 0; $counter < count($backups); $counter++) {
							$backups[$counter] = substr($backups[$counter], 14); // strip off parent folders
							if ($backups[$counter] < $expiryDate) {
								
								// delete folder
									$success = $directoryManager->remove('../backups/db/' . $backups[$counter]);
									
								// check if successful
									if ($success) $numberOfBackupsDeleted++;
									else {
										$activity = "Unknown error attempting to delete the expired backup " . $backups[$counter];
										
										$logger->logItInMemory($activity);
										$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
										
										$logger->logItInDb($activity, null, array('error'=>'1', 'resolved'=>'0'));
										emailSystemNotification($activity, 'Critical error');
										return false;
									}
									
							}
						}
						
				}
				
			// update log
				$logger->logItInMemory("Removed " . floatval($numberOfBackupsDeleted) . " expired backup(s)");
				$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
				
		}
				
		function removeOldLogs() {

			global $numberOfDaysToPreserveLogs;
			global $logger;
			global $logID;
			
			// delete logs
				$expiryDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - floatval($numberOfDaysToPreserveLogs), date('Y')));
				$numberOfLogsDeleted = deleteFromDb('logs', null, null, null, null, "connected_on < '" . $expiryDate . "'");

			// update log
				$logger->logItInMemory("Deleted " . floatval($numberOfLogsDeleted) . " expired log(s)");
				$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
				
		}
		
		function removeOldKeys() {

			global $logger;
			global $logID;
			
			// delete keys
				$deleted = deleteFromDb('user_keys', null, null, null, null, "expiry < '" . date('Y-m-d H:i:s') . "'");

			// update log
				$logger->logItInMemory("Deleted " . floatval($deleted) . " expired key(s)");
				$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
				
		}
				
		function removeOldRegistrants() {

			global $logger;
			global $logID;
			global $numberOfDaysToPreserveRegistations;
			
			// delete logs
				$expiry = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - $numberOfDaysToPreserveRegistations, date('Y')));
				$deleted = deleteFromDb('registrations', null, null, null, null, "registered_on < '" . $expiry . "'");

			// update log
				$logger->logItInMemory("Deleted " . floatval($deleted) . " registrant(s)");
				$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
				
		}
				
?>
