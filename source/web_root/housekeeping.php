<?php

	$startTimeInSeconds = time();
	$console = '';
	
	// begin logging new connection
		$logger->logItInMemory("Initiating housekeeping");
		$logID = $logger->logItInDb($logger->retrieveLogFromMemory(false), null, null, array('is_released'=>'0'));

	// run monthly housekeeping tasks
		$logger->logItInMemory("Looking for monthly housekeeping tasks");
		$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
		$oneMonthAgo = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m') - 1, date('d'), date('Y')));
		if ($handle = opendir(__DIR__ . '/../housekeeping/autoload/monthly/.')) {
			while (false !== ($file = readdir($handle))) {
				if (substr_count($file, '.php') > 0) {
					$taskName = str_replace('.php', '', $file);
					$previousConnection = retrieveSingleFromDb('logs', array('log_id'), null, null, null, null, "activity LIKE '%Terminating " . $taskName . "%' AND activity NOT LIKE '%Error encountered during " . $taskName . "%' AND connected_on >= '" . $oneMonthAgo . "'", null, "connected_on DESC");
					if (!count($previousConnection)) {
						$logger->logItInMemory("Initiating " . $taskName);
						$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
						if (substr_count($file, '.php') > 0) include __DIR__ . '/../housekeeping/autoload/monthly/' . $file;
						$logger->logItInMemory("Terminating " . $taskName);
						$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
					}
				}
			}
			closedir($handle);
		}

	// run weekly housekeeping tasks
		$logger->logItInMemory("Looking for weekly housekeeping tasks");
		$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
		$oneWeekAgo = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') - 7, date('Y')));
		if ($handle = opendir(__DIR__ . '/../housekeeping/autoload/weekly/.')) {
			while (false !== ($file = readdir($handle))) {
				if (substr_count($file, '.php') > 0) {
					$taskName = str_replace('.php', '', $file);
					$previousConnection = retrieveSingleFromDb('logs', array('log_id'), null, null, null, null, "activity LIKE '%Terminating " . $taskName . "%' AND activity NOT LIKE '%Error encountered during " . $taskName . "%' AND connected_on >= '" . $oneWeekAgo . "'", null, "connected_on DESC");
					if (!count($previousConnection)) {
						$logger->logItInMemory("Initiating " . $taskName);
						$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
						include __DIR__ . '/../housekeeping/autoload/weekly/' . $file;
						$logger->logItInMemory("Terminating " . $taskName);
						$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
					}
				}
			}
			closedir($handle);
		}

	// run daily housekeeping tasks
		$logger->logItInMemory("Looking for daily housekeeping tasks");
		$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
		$oneDayAgo = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') - 1, date('Y')));
		if ($handle = opendir(__DIR__ . '/../housekeeping/autoload/daily/.')) {
			while (false !== ($file = readdir($handle))) {
				if (substr_count($file, '.php') > 0) {
					$taskName = str_replace('.php', '', $file);
					$previousConnection = retrieveSingleFromDb('logs', array('log_id'), null, null, null, null, "activity LIKE '%Terminating " . $taskName . "%' AND activity NOT LIKE '%Error encountered during " . $taskName . "%' AND connected_on >= '" . $oneDayAgo . "'", null, "connected_on DESC");
					if (!count($previousConnection)) {
						$logger->logItInMemory("Initiating " . $taskName);
						$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
						include __DIR__ . '/../housekeeping/autoload/daily/' . $file;
						$logger->logItInMemory("Terminating " . $taskName);
						$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
					}
				}
			}
			closedir($handle);
		}

	// run hourly housekeeping tasks
		$logger->logItInMemory("Looking for hourly housekeeping tasks");
		$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
		$oneHourAgo = date('Y-m-d H:i:s', mktime(date('H') - 1, date('i'), date('s'), date('m'), date('d'), date('Y')));
		if ($handle = opendir(__DIR__ . '/../housekeeping/autoload/hourly/.')) {
			while (false !== ($file = readdir($handle))) {
				if (substr_count($file, '.php') > 0) {
					$taskName = str_replace('.php', '', $file);
					$previousConnection = retrieveSingleFromDb('logs', array('log_id'), null, null, null, null, "activity LIKE '%Terminating " . $taskName . "%' AND activity NOT LIKE '%Error encountered during " . $taskName . "%' AND connected_on >= '" . $oneHourAgo . "'", null, "connected_on DESC");
					if (!count($previousConnection)) {
						$logger->logItInMemory("Initiating " . $taskName);
						$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
						include __DIR__ . '/../housekeeping/autoload/hourly/' . $file;
						$logger->logItInMemory("Terminating " . $taskName);
						$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
					}
				}
			}
			closedir($handle);
		}

	// run rotating housekeeping tasks
		$logger->logItInMemory("Looking for rotating housekeeping tasks");
		$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
		$tasks = array();
		if ($handle = opendir(__DIR__ . '/../housekeeping/autoload/rotation/.')) {
			while (false !== ($file = readdir($handle))) {
				if (substr_count($file, '.php') > 0) $tasks[$counter] = str_replace('.php', '', $file);
			}
			closedir($handle);
		}

		if (count($tasks)) {
			$otherCriteria = "1=1 OR";
			foreach ($tasks as $counter=>$task) {
				$otherCriteria .= " activity LIKE '%" . $task . "%'";
			}
			$previousConnection = retrieveSingleFromDb('logs', array('connected_on'), null, null, null, null, $otherCriteria, null, "connected_on DESC");

			$taskNumber = 0;
			if (count($previousConnection)) {
				foreach ($tasks as $counter=>$task) {
					if (strpos($previousConnection[0]['activity'], "Terminating " . $task)) {
						$taskNumber = $counter + 1;
					}
				}
				if ($taskNumber == count($tasks)) $taskNumber = 0;
			}

			$logger->logItInMemory("Initiating " . $tasks[$taskNumber]);
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
			include __DIR__ . '/../housekeeping/autoload/rotation/' . $tasks[$taskNumber];
			$logger->logItInMemory("Terminating " . $tasks[$taskNumber]);
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
		}

	// run ASAP housekeeping tasks
		$logger->logItInMemory("Looking for immediate housekeeping tasks");
		$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
		if ($handle = opendir(__DIR__ . '/../housekeeping/autoload/asap/.')) {
			while (false !== ($file = readdir($handle))) {
				if (substr_count($file, '.php') > 0) {
					$taskName = str_replace('.php', '', $file);
					$logger->logItInMemory("Initiating " . $taskName);
					$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
					include __DIR__ . '/../housekeeping/autoload/asap/' . $file;
					$logger->logItInMemory("Terminating " . $taskName);
					$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
				}
			}
			closedir($handle);
		}

		
	// finish logging new connection
		$endTimeInSeconds = time();
		$connectionLengthInSeconds = max(1, $endTimeInSeconds - $startTimeInSeconds);
		$logger->logItInMemory("Terminating housekeeping");
		$logger->logItInDb($logger->retrieveLogFromMemory(), $logID, null, array('is_released'=>'1', 'connection_length_in_seconds'=>$connectionLengthInSeconds));
				
?>
