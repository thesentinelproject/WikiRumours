<?php

	if (@$tl->settings['Enable backups']) {

		// back up database

			$destinationPath = __DIR__ . '/../../../backups/db';
			
			// if destination path doesn't exist, stop
				if (!file_exists($destinationPath)) {
					$activity = "Error encountered during " . pathinfo(__FILE__, PATHINFO_FILENAME) . ": Can't find destination directory for database backup";
					
					$output .= $activity . "\n";
					
					$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);

					emailSystemNotification($activity, 'Critical error');
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
									
								$filename = date("Y-m-d_H-i-s") . '_' . $tl->db['Name'] . '.gz';
								$command = "mysqldump --opt -h " . $tl->db['Server'] . " -u" . $tl->db['User'] . " -p" . $tl->db['Password'] . " " . $tl->db['Name'] . " | gzip > " . $filename;
								system($command);
										
								if (!file_exists($filename)) {
									$activity = "Error encountered during " . pathinfo(__FILE__, PATHINFO_FILENAME) . ": Unable to create backup for some reason";
									
									$output .= $activity . "\n";
									
									$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);

									emailSystemNotification($activity, 'Critical error');
								}
								else {
								
									// move backup to appropriate directory
										$success = rename($filename, $destinationPath . '/' . date('Y-m-d') . '/' . $filename);
												
										if (!file_exists($destinationPath . '/' . date('Y-m-d') . '/' . $filename)) {
											$activity = "Error encountered during " . pathinfo(__FILE__, PATHINFO_FILENAME) . ": Unable to create backup for some reason";
											
											$output .= $activity . "\n";
											
											$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);

											emailSystemNotification($activity, 'Critical error');
										}

								}

						}

				}

		// back up files

			$folders = [
				'vault' => 'vault',
				'uploads' => 'web_root/uploads'
			];

			foreach ($folders as $folderName =>$folderPath) {

				$destinationPath = __DIR__ . '/../../../backups/' . $folderName;
				
				// if destination path doesn't exist, stop
					if (!file_exists($destinationPath)) {
						$activity = "Error encountered during " . pathinfo(__FILE__, PATHINFO_FILENAME) . ": Can't find " . $folderPath . " for file backup";
						
						$output .= $activity . "\n";
						
						$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);

						emailSystemNotification($activity, 'Critical error');
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
									$output .= "Attempting to zip files\n";

									$filename = date("Y-m-d_H-i-s") . '_' . $folderName . '.gz';

									$success = $archiver->archive($filename, $destinationPath . '/' . date('Y-m-d'), __DIR__ . '/../../../' . $folderPath, false, false);
										
									if (!$success) {
										$activity = "Error encountered during " . pathinfo(__FILE__, PATHINFO_FILENAME) . ": Unable to create file backup for some reason";
										
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
