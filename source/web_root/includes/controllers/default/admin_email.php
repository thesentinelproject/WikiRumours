<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$pageStatus = $parameter1;

	// authenticate user
		if (!$logged_in['is_administrator']) forceLoginThenRedirectHere();
		
	// queries
		$notifications = retrieveFromDb('notifications');
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {
		
		$pageError = '';

		if ($_POST['formName'] == 'editNotificationsForm' && $_POST['notificationEmailToDelete']) {
			
			// delete notification
				deleteFromDb('notifications', array('notification_id'=>$_POST['notificationEmailToDelete']), null, null, null, null, 1);
				
			// update log
				$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has deleted a notification for &quot;" . $_POST['notification_email_' . $_POST['notificationEmailToDelete']] . "&quot; (notification_id " . $_POST['notificationEmailToDelete'] . ")";
				$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'notification_id=' . $_POST['notificationEmailToDelete']));
				
			// redirect
				header('Location: /admin_email/notification_deleted');
				exit();
				
		}
		
		elseif ($_POST['formName'] == 'editNotificationsForm') {
			
			// clean input
				$_POST = $parser->trimAll($_POST);
				$checkboxesToParse = array('new_registrations', 'contact_form');
				foreach ($checkboxesToParse as $checkbox) {
					// check edit
						for ($counter = 0; $counter < count($notifications); $counter++) {
							if (isset($_POST[$checkbox . '_' . $notifications[$counter]['notification_id']])) $_POST[$checkbox . '_' . $notifications[$counter]['notification_id']] = 1;
							else $_POST[$checkbox . '_' . $notifications[$counter]['notification_id']] = 0;
						}
					// check add
						if (isset($_POST[$checkbox . '_add'])) $_POST[$checkbox . '_add'] = 1;
						else $_POST[$checkbox . '_add'] = 0;
				}
				
			// check for errors
				// check edit
					for ($counter = 0; $counter < count($notifications); $counter++) {
						if (!$_POST['recipient_name_' . $notifications[$counter]['notification_id']]) $pageError .= "Please specify a recipient name. ";
						if (!$_POST['recipient_email_' . $notifications[$counter]['notification_id']] || !$input_validator->validateEmailBasic($_POST['recipient_email_' . $notifications[$counter]['notification_id']])) $pageError .= "Please provide a valid recipient email. ";
					}
				// check add
					if ($_POST['recipient_email_add'] && !$_POST['recipient_name_add']) $pageError .= "Please specify a recipient name. ";
					if (($_POST['recipient_name_add'] && !$_POST['recipient_email_add']) || ($_POST['recipient_email_add'] && !$input_validator->validateEmailBasic($_POST['recipient_email_add']))) $pageError .= "Please provide a valid recipient email. ";

			if (!$pageError) {
				
				// update database
					// update edit
						for ($counter = 0; $counter < count($notifications); $counter++) {
							updateDb('notifications', array('recipient_name'=>$_POST['recipient_name_' . $notifications[$counter]['notification_id']], 'recipient_email'=>$_POST['recipient_email_' . $notifications[$counter]['notification_id']]), array('notification_id'=>$notifications[$counter]['notification_id']), null, null, null, null, 1);
							foreach ($checkboxesToParse as $checkbox) {
								updateDb('notifications', array($checkbox=>$_POST[$checkbox . '_' . $notifications[$counter]['notification_id']]), array('notification_id'=>$notifications[$counter]['notification_id']), null, null, null, null, 1);
							}
						}
					// update add
						if ($_POST['recipient_name_add'] && $_POST['recipient_email_add']) {
							$notificationID = insertIntoDb('notifications', array('recipient_name'=>$_POST['recipient_name_add'], 'recipient_email'=>$_POST['recipient_email_add']));
							foreach ($checkboxesToParse as $checkbox) {
								updateDb('notifications', array($checkbox=>$_POST[$checkbox . '_add']), array('notification_id'=>$notificationID), null, null, null, null, 1);
							}
						}
					
				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated email notifications";
					$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id']));
				
				// redirect
					header('Location: /admin_email/notifications_updated');
					exit();
					
			}
			
		}
		
		elseif ($_POST['formName'] == 'emailUserForm' && $logged_in['can_send_email']) {

			// clean input
				$_POST = $parser->trimAll($_POST);
				
			// check for errors
				if (!$_POST['name']) $pageError .= "Please provide a name for the recipient of your email. ";
				if (!$input_validator->validateEmailRobust($_POST['email'])) $pageError .= "Please provide a valid email address for the recipient of your email. ";
				if ($_POST['reply_to_email'] && !$input_validator->validateEmailRobust($_POST['reply_to_email'])) $pageError .= "Please provide a valid email address for the sender of your email. ";
				if (!$_POST['subject']) $pageError .= "Please provide a subject for the email. ";
				if (!$_POST['message']) $pageError .= "Please provide a message for the email. ";
				
			// send email
				if (!$pageError) {
					$success = emailToUser($_POST['name'], $_POST['email'], $_POST['reply_to_name'], $_POST['reply_to_email'], $_POST['subject'], $_POST['message']);
					if (!$success) $pageError = "Email failed for unknown reason.";
					else {
						// update log
							$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") sent an email to " . $_POST['name'] . " (" . $_POST['email'] . ") with the subject &quot;" . $_POST['subject'] . "&quot;";
							$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id']));
						// redirect
							header('Location: /admin_email/email_queued');
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