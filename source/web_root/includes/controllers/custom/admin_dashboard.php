<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in['is_administrator']) forceLoginThenRedirectHere();
		
	// parse query string
		$pageStatus = $parameter1;

	// queries
		// alerts
			$alerts = retrieveFromDb('logs', null, array('error'=>'1', 'resolved'=>'0'), null, null, null, null, null, $tablePrefix . 'logs.connected_on DESC');

		// comments
			$flaggedComments = retrieveFlaggedComments();

		// registrants
			$registrants = retrieveRegistrants(null, null);

		// unsent mail
			$unsentMail = retrieveFromDb('mail_queue', null, null, null, null, null, $tablePrefix . "mail_queue.sent_on = '0000-00-00 00:00:00'", null, $tablePrefix . 'mail_queue.queued_on DESC');

		// system stats
			$errno = 0;
			$errstr = '';
			$isMailServerActive = fsockopen($mail_TL['Host'], 25, $errno, $errstr, 5);
			if ($isMailServerActive) $mailServerStatus = 'Active';
			else $mailServerStatus = 'Inactive';
			
			$dbSize = retrieveDbSize_TL();

	$pageTitle = 'Dashboard';
	$sectionTitle = 'Administration';
		
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
						header('Location: /admin_dashboard/alert_resolved');
						exit();
				}
				else {
					$pageError .= "Unable to resolve alert for some reason. ";
				}
		}
		
		elseif ($_POST['formName'] == 'editRegistrantsForm' && $_POST['registrantToApprove']) {

			// retrieve registrant info
				$registration = retrieveSingleFromDb('registrations', null, array('registration_id'=>$_POST['registrantToApprove']));
				if (count($registration) < 1) $pageError = "There was a problem locating the desired registrant. ";
				else {

					// add user
						$confirmed = approveRegistration($registration[0]['registration_id'], $logged_in['user_id']);
			
						if (!$confirmed) $pageError = "There was a problem approving the desired registrant. ";
						else {

							// redirect
								header('Location: /admin_dashboard/registrant_approved');
								exit();

						}
				}
		}

		elseif ($_POST['formName'] == 'editRegistrantsForm' && $_POST['registrantToDelete']) {
			
			// retrieve registrant info
				$registrant = retrieveSingleFromDb('registrations', null, array('registration_id'=>$_POST['registrantToDelete']));
			
			// delete registrant
				deleteFromDb('registrations', array('registration_id'=>$_POST['registrantToDelete']), null, null, null, null, 1);
				
			// update log
				$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has deleted the registrant &quot;" . $registrant[0]['full_name'] . "&quot; (registration_id " . $_POST['registrantToDelete'] . ")";
				$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'registration_id=' . $_POST['registrantToDelete']));
				
			// redirect
				header('Location: /admin_dashboard/registrant_deleted');
				exit();
				
		}
		
	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>