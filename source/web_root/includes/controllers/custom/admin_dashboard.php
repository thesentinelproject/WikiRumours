<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in['is_administrator']) $authentication_manager->forceLoginThenRedirectHere(true);
		
	// parse query string
		$query_string = urldecode(@$tl->page['parameter1']);
		if ($query_string == 'index') $query_string = null;
		$query_string = trim($query_string . "|alerts_only=true", '| ');

	// queries
		// alerts
			$logs = new logs_widget_TL();
			$logs->initialize(['template_name'=>$tl->page['template'], 'query_string'=>$query_string, 'columns'=>['connected_on'=>'', 'activity'=>'']]);

		// comments
			$flaggedComments = retrieveFlaggedComments();

		// registrants
			$registrants = retrieveRegistrants(null, null);

		// unsent mail
			$unsentMail = retrieveFromDb('mail_queue', null, null, null, null, null, $tablePrefix . "mail_queue.sent_on = '0000-00-00 00:00:00'", null, $tablePrefix . 'mail_queue.queued_on DESC');

		// system stats
			$errno = 0;
			$errstr = '';
			$isMailServerActive = fsockopen($tl->mail['Host'], 25, $errno, $errstr, 5);
			if ($isMailServerActive) $mailServerStatus = 'Active';
			else $mailServerStatus = 'Inactive';
			
			$dbSize = retrieveDbSize_TL();

	$tl->page['title'] = 'Dashboard';
	$tl->page['section'] = 'Administration';
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {
		
		$tl->page['error'] = '';

		if ($_POST['formName'] == 'editRegistrantsForm' && $_POST['registrantToApprove']) {

			// retrieve registrant info
				$registration = retrieveSingleFromDb('registrations', null, array('registration_id'=>$_POST['registrantToApprove']));
				if (count($registration) < 1) $tl->page['error'] = "There was a problem locating the desired registrant. ";
				else {

					// add user
						$confirmed = approveRegistration($registration[0]['registration_id'], $logged_in['user_id']);
			
						if (!$confirmed) $tl->page['error'] = "There was a problem approving the desired registrant. ";
						else {

							// redirect
								$authentication_manager->forceRedirect('/admin_dashboard/index/success=registrant_approved');

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
				$authentication_manager->forceRedirect('/admin_dashboard/index/success=registrant_deleted');
				
		}
		
	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>