<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$pageSuccess = $parameter1;

	// instantiate required class(es)
		$validator = new inputValidator_TL();
		$operators = new operators_TL();
		$parser = new parser_TL();
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {

		// check honeypot
			if (@$_POST['company']) $pageError .= "Nice try, Skynet. No bots allowed. ";

		// check timer
			if (time() - @$_POST['timer'] < 2) $pageError .= "There was a problem with your form data. Please take a moment and try again. "; // if form submitted in under 2 sec, presumed to be a bot
					
		// clean input
			if (!$pageError) $_POST = $parser->trimAll($_POST);

		// check for errors
			if (!$pageError) {
				if (!$_POST['name']) $pageError .= "Please provide your name. ";
				if (!$validator->validateEmailRobust($_POST['email'])) $pageError .= "There appears to be a problem with your email address. ";
				if (!$_POST['message']) $pageError .= "Please write a brief message. ";
							
				$recipients = retrieveFromDb('notifications', array('contact_form'=>'1'), null, null, null);
				if (count($recipients) < 1) $pageError .= "No recipients specified for the subject selected. ";
			}
						
		// send email
			if (!$pageError) {
				for ($counter = 0; $counter < count($recipients); $counter++) {
					$emailSent = emailFromUser($_POST['name'], $_POST['email'], $_POST['username'], $_POST['telephone'], $_POST['message'], $recipients[$counter]['email']);
					if ($emailSent) {
						// update log
							$activity = $_POST['name'] . " (";
							if ($_POST['username']) $activity .= $_POST['username'] . " / ";
							if ($_POST['telephone']) $activity .= $_POST['telephone'] . " / ";
							$activity .= $_POST['email'];
							$activity .= ") has messaged " . $systemPreferences['appName'] . " through the contact form:\n\n" . $_POST['message'];
							$logger->logItInDb($activity);
					}
					else {
						$pageError = "Unknown error attempting to send email. Please try again.";
						$logger->logItInDb("Contact form failed to send email.", null, array('error'=>'1', 'resolved'=>'0'));
					}
				}
			}
			
		// redirect URL
			if (!$pageError) {
				header ('Location: /contact/message_sent');
				exit();
			}

	}
	
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
	
	else {
	}
		
?>