<?php

	$trashPath = __DIR__ . '/../../../trash';
	$numberOfItemsDeleted = 0;
	
	// make sure trash directory exists
		if (!file_exists($trashPath)) {
			$activity = "Error encountered during " . pathinfo(__FILE__, PATHINFO_FILENAME) . ": Can't find trash bin at " . $trashPath;
			
			$output .= $activity . "\n";
			
			$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);
			emailSystemNotification($activity, 'Critical error');
		}
		else {
		
			// retrieve list of backups
				$trashBin = $directory_manager->read($trashPath, false, true, false);
				
			// delete expired backups
				for ($counter = 0; $counter < count($trashBin); $counter++) {
					$trashBin[$counter] = substr($trashBin[$counter], strpos($trashBin[$counter], 'trash/') + 6); // strip off parent folders
					if ($trashBin[$counter] < date('Y-m-d_H-i-s')) {
						
						// delete folder
							$success = $directory_manager->remove($trashPath . '/' . $trashBin[$counter]);
							
						// check if successful
							if ($success) $numberOfItemsDeleted++;
							else {
								$activity = "Error encountered during " . pathinfo(__FILE__, PATHINFO_FILENAME) . ": Unknown error attempting to delete the folder " . $trashBin[$counter];
								
								$output .= $activity . "\n";
								
								$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);
								emailSystemNotification($activity, 'Critical error');
							}
							
					}
				}
				
			// update log
				$output .= "Removed " . floatval($numberOfItemsDeleted) . " item(s)\n";

		}
		
?>
