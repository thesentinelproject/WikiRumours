<?php

	if ($tl->settings['Enable database backups']) {

		$destinationPath = __DIR__ . '/../../../backups/db';
		
		// if destination path doesn't exist, stop
			if (!file_exists($destinationPath)) {
				$activity = "Error encountered during " . pathinfo(__FILE__, PATHINFO_FILENAME) . ": Can't find destination directory for backup";
				
				$output .= $activity . "\n";
				
				$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);
				emailSystemNotification($activity, 'Critical error');
				return false;
			}
			else {
							
				// check if daily backup already exists
					if (file_exists($destinationPath . '/' . date('Y-m-d'))) {
						$output .= "Today's backup already exists\n";
					}
					else {
							
						// create backup folder
							mkdir ($destinationPath . '/' . date('Y-m-d'));
											
						// create backup
							$output .= "Attempting to dump database to text file and compress\n";
								
							$backupFilename = $tl->db['Name'] . '_' . date("Y-m-d_H-i-s") . '.gz';
							$command = "mysqldump --opt -h " . $tl->db['Server'] . " -u" . $tl->db['User'] . " -p" . $tl->db['Password'] . " " . $tl->db['Name'] . " | gzip > " . $backupFilename;
							system($command);
									
							if (!file_exists($backupFilename)) {
								$activity = "Error encountered during " . pathinfo(__FILE__, PATHINFO_FILENAME) . ": Unable to create backup for some reason";
								
								$output .= $activity . "\n";
								
								$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);
								emailSystemNotification($activity, 'Critical error');
							}
							else {
							
								// move backup to appropriate directory
									$success = rename($backupFilename, $destinationPath . '/' . date('Y-m-d') . '/' . $backupFilename);
											
									if (!file_exists($destinationPath . '/' . date('Y-m-d') . '/' . $backupFilename)) {
										$activity = "Error encountered during " . pathinfo(__FILE__, PATHINFO_FILENAME) . ": Unable to create backup for some reason";
										
										$output .= $activity . "\n";
										
										$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);
										emailSystemNotification($activity, 'Critical error');
									}

							}

					}

			}

	}
	else {
		$output .= "Backups disabled\n";
	}
				
?>
