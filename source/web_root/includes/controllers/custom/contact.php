<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {

	// check honeypot
			if (@$_POST['company']) $tl->page['error'] .= "Nice try, Skynet. No bots allowed. ";

		// check timer
			if (time() - @$_POST['timer'] < 2) $tl->page['error'] .= "There was a problem with your form data. Please take a moment and try again. "; // if form submitted in under 2 sec, presumed to be a bot
					
		// clean input
			if (!$tl->page['error']) $_POST = $parser->trimAll($_POST);

		// check for errors
			if (!$tl->page['error']) {
				if (!$_POST['name']) $tl->page['error'] .= "Please provide your name. ";
				if (!$input_validator->validateEmailRobust($_POST['email'])) $tl->page['error'] .= "There appears to be a problem with your email address. ";
				if (!$_POST['message']) $tl->page['error'] .= "Please write a brief message. ";
								
				$notifications_widget = new notifications_widget_TL();
				$recipients = $notifications_widget->retrieveAdminRecipients([$tablePrefix . 'notification_recipients_x_types.type_id'=>'2']); // contact form
				if (count($recipients) < 1) $tl->page['error'] .= "No recipients specified for the subject selected. ";
			}
			
		// send email
			if (!$tl->page['error']) {
				
				for ($counter = 0; $counter < count($recipients); $counter++) {
					$emailSent = emailFromUser($_POST['name'], $_POST['email'], $_POST['username'], $_POST['telephone'], $_POST['message'], $recipients[$counter]['email']);
					if ($emailSent) {

						// capture user agent metadata
							$detector->browser();
							$detector->connection();
							$connectionID = insertIntoDb('browser_connections', ['connected_on'=>date('Y-m-d H:i:s'), 'mail_id'=>$emailSent, 'user_agent'=>$detector->browser['user_agent'], 'ip'=>$detector->connection['ip'], 'country_id'=>$detector->connection['country']]);
							if ($logged_in) updateDbSingle('browser_connections', array('user_id'=>$logged_in['user_id']), array('mail_id'=>$emailSent));

						// update log
							$activity = $_POST['name'] . " (";
							if ($_POST['username']) $activity .= $_POST['username'] . " / ";
							if ($_POST['telephone']) $activity .= $_POST['telephone'] . " / ";
							$activity .= $_POST['email'];
							$activity .= ") has messaged " . $tl->settings['Name of this application'] . " through the contact form:\n\n" . $_POST['message'];
							$logger->logItInDb($activity);

							if ($logged_in) $author = ['user_id'=>$logged_in['user_id'], 'first_name'=>$logged_in['first_name'], 'last_name'=>$logged_in['last_name'], 'email'=>$logged_in['email'], 'phone'=>$logged_in['primary_phone']];
							$attributableOutput = $attributable->capture($activity, null, @$author, ['domain_alias_id'=>@$tl->page['domain_alias']['cms_id']]);
							if (!@count($attributableOutput['content']['success'])) emailSystemNotification(__FILE__ . ": " . (is_array($attributableOutput) ? print_r($attributableOutput, true) : $attributableOutput), 'Attributable failure');
							
					}
					else {
						$tl->page['error'] = "Unknown error attempting to send email. Please try again.";
						$activity = "Contact form failed to send email.";
						$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'));

						$attributableOutput = $attributable->capture($activity, null, ['user_id'=>$userID, 'first_name'=>$registration[0]['first_name'], 'last_name'=>$registration[0]['last_name'], 'email'=>$registration[0]['email'], 'phone'=>$registration[0]['primary_phone']], ['domain_alias_id'=>@$tl->page['domain_alias']['cms_id']], 1);
						if (!@count($attributableOutput['content']['success'])) emailSystemNotification(__FILE__ . ": " . (is_array($attributableOutput) ? print_r($attributableOutput, true) : $attributableOutput), 'Attributable failure');
					}
				}
			}
						
		// redirect URL
			if (!$tl->page['error']) $authentication_manager->forceRedirect('/contact/success=message_sent');

	}
	
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
	
	else {
	}
		
?>