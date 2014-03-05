<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in['is_administrator']) forceLoginThenRedirectHere();
		
	// parse query string
		$report = $parameter1;
		if (!$report) $report = 'dashboard';
		
		if ($parameter2 == 'default') $filters = null;
		else {
			$filters = array();
			$result = explode('|', urldecode($parameter2));
			foreach ($result as $keyValue) {
				$filterKeyValues = explode('=', $keyValue);
				foreach ($filterKeyValues as $key => $value) {
					if (trim($key) && trim($value)) $filters[trim($key)] = trim($value);
				}
			}
		}
		
		$pageSuccess = $parameter3;

	// queries
		if ($report == 'dashboard') {
			$numberOfRumoursToDisplay = 5;
			$rumours = retrieveRumours(array($tablePrefix . 'rumours.enabled'=>'1', 'assigned_to'=>'0'), null ,null, $tablePrefix . 'rumours.updated_on DESC, ' . $tablePrefix . 'rumours.created_on DESC', '0,' . ($numberOfRumoursToDisplay + 1));
			$users = retrieveUsers(null, null, null, $tablePrefix . 'users.registered_on DESC');
			$registrants = retrieveRegistrants(null, null);
			$alerts = retrieveFromDb('logs', array('error'=>'1', 'resolved'=>'0'), null, null, null, null, $tablePrefix . 'logs.connected_on DESC');
			$flaggedComments = retrieveFlaggedComments();
		}
		elseif ($report == 'rumours') {
			if (!@$filters['sortBy']) $filters['sortBy'] = $tablePrefix . 'rumours.updated_on DESC';

			$result = countInDb('rumours', 'rumour_id');
			$numberOfRumours = floatval(@$result[0]['count']);
			
			$numberOfPages = max(1, ceil($numberOfRumours / $maxNumberOfTableRowsPerPage));
			$filters['page'] = floatval(@$filters['page']);
			if ($filters['page'] < 1) $filters['page'] = 1;
			elseif ($filters['page'] > $numberOfPages) $filters['page'] = $numberOfPages;
			
			$rumours = retrieveRumours(null, null, null, $filters['sortBy'], floatval(($filters['page'] * $maxNumberOfTableRowsPerPage) - $maxNumberOfTableRowsPerPage) . ',' . $maxNumberOfTableRowsPerPage);
		}
		elseif ($report == 'users') {
			if (!@$filters['sortBy']) $filters['sortBy'] = $tablePrefix . 'users.username ASC';

			$result = countInDb('users', 'user_id');
			$numberOfUsers = floatval(@$result[0]['count']);
			
			$numberOfPages = max(1, ceil($numberOfUsers / $maxNumberOfTableRowsPerPage));
			$filters['page'] = floatval(@$filters['page']);
			if ($filters['page'] < 1) $filters['page'] = 1;
			elseif ($filters['page'] > $numberOfPages) $filters['page'] = $numberOfPages;
			
			$users = retrieveUsers(null, null, null, $filters['sortBy'], floatval(($filters['page'] * $maxNumberOfTableRowsPerPage) - $maxNumberOfTableRowsPerPage) . ',' . $maxNumberOfTableRowsPerPage);
		}
		
	// instantiate required class(es)
		$validator = new inputValidator_TL();
		$operators = new operators_TL();
		$parser = new parser_TL();
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {
		
		$pageError = '';

		if ($_POST['formName'] == 'dashboardAlertsForm' && $_POST['alertToResolve']) {
			// resolve alert
				$success = updateDb('logs', array('resolved'=>'1'), array('log_id'=>$_POST['alertToResolve']), null, null, null, null, 1);
				if ($success) {
					// update log
						$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has resolved log_id " . $_POST['log_id'];
						$logger->logItInDb($activity);
					// redirect
						header('Location: /admin_dashboard/dashboard/default/alert_resolved');
						exit();
				}
				else {
					$pageError .= "Unable to resolve alert for some reason. ";
				}
		}
		
		elseif ($_POST['formName'] == 'editRegistrantsForm' && $_POST['registrantToApprove']) {

			// retrieve registrant info
				$registrant = retrieveRegistrants(array('registration_id'=>$_POST['registrantToApprove']), null, null, null, 1);
			
			// add user
				$user_id = insertIntoDb('users', array('username'=>$registrant[0]['username'], 'password_hash'=>$registrant[0]['password_hash'], 'first_name'=>$registrant[0]['first_name'], 'last_name'=>$registrant[0]['last_name'], 'email'=>$registrant[0]['email'], 'phone'=>$registrant[0]['phone'], 'secondary_phone'=>$registrant[0]['secondary_phone'], 'sms_notifications'=>$registrant[0]['sms_notifications'], 'country'=>$registrant[0]['country'], 'province_state'=>$registrant[0]['province_state'], 'other_province_state'=>$registrant[0]['other_province_state'], 'region'=>$registrant[0]['region'], 'registered_on'=>$registrant[0]['registered_on'], 'ok_to_contact'=>'1', 'enabled'=>'1', 'referred_by'=>$registrant[0]['referred_by']));
			
				if (!$user_id) $pageError .= "Unable to approve registrant. ";
				else {
					// delete registrant
						deleteFromDb('registrations', array('registration_id'=>$_POST['registrantToApprove']), null, null, null, null, 1);
		
					// send welcome email
						emailNewUser($registrant[0]['full_name'], $registrant[0]['email']);
						
					// update log
						$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has approved the registrant &quot;" . $registrant[0]['full_name'] . "&quot; (registration_id " . $_POST['registrantToApprove'] . " / user_id " . $user_id . ")";
						$logger->logItInDb($activity);
						
					// redirect
						header('Location: /admin_dashboard/dashboard/default/registrant_approved');
						exit();
				}
		}

		elseif ($_POST['formName'] == 'editRegistrantsForm' && $_POST['registrantToDelete']) {
			
			// retrieve registrant info
				$registrant = retrieveFromDb('registrations', array('registration_id'=>$_POST['registrantToDelete']), null, null, null, null, null, 1);
			
			// delete registrant
				deleteFromDb('registrations', array('registration_id'=>$_POST['registrantToDelete']), null, null, null, null, 1);
				
			// update log
				$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has deleted the registrant &quot;" . $registrant[0]['full_name'] . "&quot; (registration_id " . $_POST['registrantToDelete'] . ")";
				$logger->logItInDb($activity);
				
			// redirect
				header('Location: /admin_dashboard/dashboard/default/registrant_deleted');
				exit();
				
		}
		
		elseif ($_POST['formName'] == 'emailUserForm') {

			// clean input
				$_POST = $parser->trimAll($_POST);
				
			// check for errors
				if (!$_POST['name']) $pageError .= "Please provide a name for the recipient of your email. ";
				if (!$validator->validateEmailRobust($_POST['email'])) $pageError .= "Please provide a valid email address for the recipient of your email. ";
				if ($_POST['reply_to'] && !$validator->validateEmailRobust($_POST['reply_to'])) $pageError .= "Please provide a valid email address for the sender of your email. ";
				if (!$_POST['message']) $pageError .= "Please provide a message for the recipient of your email. ";
				
			// send email
				if (!$pageError) {
					$success = emailToUser($_POST['name'], $_POST['email'], $_POST['message'], $_POST['reply_to']);
					if (!$success) $pageError = "Email failed for unknown reason.";
					else {
						// update log
							$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") sent the following email to " . $_POST['name'] . " (" . $_POST['email'] . "): " . $_POST['message'];
							$logger->logItInDb($activity);
						// redirect
							header('Location: /admin_dashboard/dashboard/default/email_sent');
							exit();
					}
				}
				
		}
		
	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>