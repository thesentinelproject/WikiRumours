<?php

	$startTimeInSeconds = time();

	// begin logging new connection
		$housekeepingActivity = "Initiating housekeeping";
		$logID = $logger->logItInDb($housekeepingActivity, null, null, array('is_released'=>'0'));

	// run monthly housekeeping tasks
		$oneMonthAgo = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m') - 1, date('d'), date('Y')));
		if ($handle = opendir(__DIR__ . '/../housekeeping/autoload/monthly/.')) {
			while (false !== ($file = readdir($handle))) {
				if (substr_count($file, '.php') > 0) {
					$taskName = str_replace('.php', '', $file);
					$previousConnection = retrieveSingleFromDb('log_tasks', null, ['task'=>$taskName, 'is_error'=>'0'], null, null, null, "completed_on >= '" . $oneMonthAgo . "'", null, "completed_on DESC");
					if (!count($previousConnection)) {
						$output = null;
						$taskID = insertIntoDb('log_tasks', ['task'=>$taskName, 'is_error'=>'1', 'log_id'=>$logID, 'completed_on'=>date('Y-m-d H:i:s')]);
						include __DIR__ . '/../housekeeping/autoload/monthly/' . $file;
						updateDbSingle('log_tasks', ['output'=>trim($output), 'is_error'=>'0', 'completed_on'=>date('Y-m-d H:i:s')], ['task_id'=>$taskID]);
					}
				}
			}
			closedir($handle);
		}

	// run weekly housekeeping tasks
		$oneWeekAgo = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') - 7, date('Y')));
		if ($handle = opendir(__DIR__ . '/../housekeeping/autoload/weekly/.')) {
			while (false !== ($file = readdir($handle))) {
				if (substr_count($file, '.php') > 0) {
					$taskName = str_replace('.php', '', $file);
					$previousConnection = retrieveSingleFromDb('log_tasks', null, ['task'=>$taskName, 'is_error'=>'0'], null, null, null, "completed_on >= '" . $oneWeekAgo . "'", null, "completed_on DESC");
					if (!count($previousConnection)) {
						$output = null;
						$taskID = insertIntoDb('log_tasks', ['task'=>$taskName, 'is_error'=>'1', 'log_id'=>$logID, 'completed_on'=>date('Y-m-d H:i:s')]);
						include __DIR__ . '/../housekeeping/autoload/weekly/' . $file;
						updateDbSingle('log_tasks', ['output'=>trim($output), 'is_error'=>'0', 'completed_on'=>date('Y-m-d H:i:s')], ['task_id'=>$taskID]);
					}
				}
			}
			closedir($handle);
		}

	// run daily housekeeping tasks
		$oneDayAgo = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') - 1, date('Y')));
		if ($handle = opendir(__DIR__ . '/../housekeeping/autoload/daily/.')) {
			while (false !== ($file = readdir($handle))) {
				if (substr_count($file, '.php') > 0) {
					$taskName = str_replace('.php', '', $file);
					$previousConnection = retrieveSingleFromDb('log_tasks', null, ['task'=>$taskName, 'is_error'=>'0'], null, null, null, "completed_on >= '" . $oneDayAgo . "'", null, "completed_on DESC");
					if (!count($previousConnection)) {
						$output = null;
						$taskID = insertIntoDb('log_tasks', ['task'=>$taskName, 'is_error'=>'1', 'log_id'=>$logID, 'completed_on'=>date('Y-m-d H:i:s')]);
						include __DIR__ . '/../housekeeping/autoload/daily/' . $file;
						updateDbSingle('log_tasks', ['output'=>trim($output), 'is_error'=>'0', 'completed_on'=>date('Y-m-d H:i:s')], ['task_id'=>$taskID]);
					}
				}
			}
			closedir($handle);
		}

	// run hourly housekeeping tasks
		$oneHourAgo = date('Y-m-d H:i:s', mktime(date('H') - 1, date('i'), date('s'), date('m'), date('d'), date('Y')));
		if ($handle = opendir(__DIR__ . '/../housekeeping/autoload/hourly/.')) {
			while (false !== ($file = readdir($handle))) {
				if (substr_count($file, '.php') > 0) {
					$taskName = str_replace('.php', '', $file);
					$previousConnection = retrieveSingleFromDb('log_tasks', null, ['task'=>$taskName, 'is_error'=>'0'], null, null, null, "completed_on >= '" . $oneHourAgo . "'", null, "completed_on DESC");
					if (!count($previousConnection)) {
						$output = null;
						$taskID = insertIntoDb('log_tasks', ['task'=>$taskName, 'is_error'=>'1', 'log_id'=>$logID, 'completed_on'=>date('Y-m-d H:i:s')]);
						include __DIR__ . '/../housekeeping/autoload/hourly/' . $file;
						updateDbSingle('log_tasks', ['output'=>trim($output), 'is_error'=>'0', 'completed_on'=>date('Y-m-d H:i:s')], ['task_id'=>$taskID]);
					}
				}
			}
			closedir($handle);
		}

	// run rotating housekeeping tasks
		$tasks = array();
		if ($handle = opendir(__DIR__ . '/../housekeeping/autoload/rotation/.')) {
			while (false !== ($file = readdir($handle))) {
				if (substr_count($file, '.php') > 0) {
					$taskName = str_replace('.php', '', $file);
					$previousConnection = retrieveSingleFromDb('log_tasks', null, ['task'=>$taskName, 'is_error'=>'0'], null, null, null, null, null, "completed_on DESC");
					if (count($previousConnection)) $tasks[$taskName] = $previousConnection['completed_on'];
					else $tasks[$taskName] = '0000-00-00 00:00:00';
				}
			}
			closedir($handle);
		}

		if (count($tasks)) {
			asort($tasks);
			$output = null;
			$taskID = insertIntoDb('log_tasks', ['task'=>$taskName, 'is_error'=>'1', 'log_id'=>$logID, 'completed_on'=>date('Y-m-d H:i:s')]);
			include __DIR__ . '/../housekeeping/autoload/rotation/' . $tasks[$taskNumber];
			updateDbSingle('log_tasks', ['output'=>trim($output), 'is_error'=>'0', 'completed_on'=>date('Y-m-d H:i:s')], ['task_id'=>$taskID]);
		}

	// run ASAP housekeeping tasks
		if ($handle = opendir(__DIR__ . '/../housekeeping/autoload/asap/.')) {
			while (false !== ($file = readdir($handle))) {
				if (substr_count($file, '.php') > 0) {
					$taskName = str_replace('.php', '', $file);
					$output = null;
					$taskID = insertIntoDb('log_tasks', ['task'=>$taskName, 'is_error'=>'1', 'log_id'=>$logID, 'completed_on'=>date('Y-m-d H:i:s')]);
					include __DIR__ . '/../housekeeping/autoload/asap/' . $file;
					updateDbSingle('log_tasks', ['output'=>trim($output), 'is_error'=>'0', 'completed_on'=>date('Y-m-d H:i:s')], ['task_id'=>$taskID]);
				}
			}
			closedir($handle);
		}

		
	// finish logging new connection
		$tasks = retrieveFromDb('log_tasks', null, ['log_id'=>$logID], null, null, null, null, null, "completed_on ASC");
		foreach ($tasks as $task) {
			$housekeepingActivity .= "\n" . date('H:i:s', strtotime($task['completed_on'])) . ": " . $task['task'] . ($task['is_error'] ? " (failed)" : null) . ($task['output'] ? "\n" . "<small><span class='text-muted'>" . str_replace(array("\r", "\n"), "<br />", $task['output']) . "</span></small>" : null);
		}
		$housekeepingActivity .= "\nTerminating housekeeping";

		$logger->logItInDb($housekeepingActivity, $logID, null, array('is_released'=>'1', 'connection_length_in_seconds'=>max(1, time() - $startTimeInSeconds)));
				
?>
