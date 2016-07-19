<?php

	// initialize
		$maximumAlertsToSend = 10;
		$fiveMinutesAgo = date('Y-m-d H:i:s', mktime(date('H'), date('i') - 5, date('s'), date('m'), date('d'), date('Y')));

	// find unalerted sessions
		$unalerted = retrieveFromDb('sessions', null, ['alerted_on'=>'0000-00-00 00:00:00'], null, null, null, "connected_on < '" . $fiveMinutesAgo . "'");

	// alert
		for ($counter = 0; $counter < min(count($unalerted), $maximumAlertsToSend); $counter++) {

			$user = null;

			if ($unalerted[$counter]['user_id']) {
				$result = retrieveSingleFromDb('users', null, ['user_id'=>$unalerted[$counter]['user_id']]);
				$user = trim(@$result[0]['first_name'] . " " . @$result[0]['last_name']);
			}

			if (!@$user) {
				$detector->connection['ip'] = $unalerted[$counter]['ip'];
				$detector->connection();
				$agent_info = $detector->parseUserAgent($unalerted[$counter]['user_agent']);
				
				$user = "An unidentified user ";
				if (@$detector->connection['country']) $user .= "in " . trim(@$detector->connection['city'] . ", " . @$detector->connection['country'], ', ') . " ";
				if (@$agent_info['browser']) $user .= "using " . trim(@$agent_info['browser'] . " " . @$agent_info['browser_version'], ' ') . " ";
				if (@$agent_info['os']) $user .= "on " . trim(@$agent_info['os'] . " " . @$agent_info['os_version'], ' ') . " ";
				$user .= "(" . $unalerted[$counter]['ip'] . ")";
			}

			$activity = $user . " encountered a broken page" . (@$unalerted[$counter]['template'] ? " at " . $unalerted[$counter]['template'] : false);

			$logger->logItInMemory($activity);
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
			$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'));
			emailSystemNotification($activity, 'Critical error');

			updateDbSingle('sessions', ['alerted_on'=>date('Y-m-d H:i:s')], ['session_id'=>$unalerted[$counter]['session_id']]);

		}

?>
