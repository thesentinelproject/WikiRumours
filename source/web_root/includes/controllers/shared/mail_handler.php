<?php

/*	--------------------------------------
	Registration & login
	-------------------------------------- */

	function emailRegistrationKey($name, $email, $key) {

		if (!$name || !$email || !$key) return false;
		
		global $mail_TL;
		global $environmentals;
		global $systemPreferences;
		global $phpmailerWrapper;
		
		$url = $environmentals['protocol'] . $environmentals['absoluteRoot'] . "/confirm_registration/" . $key;
		
		$subject = "[" . $systemPreferences['appName'] . "] Please confirm your registration";

		$messagePlain = "Thank you for registering with " . $systemPreferences['appName'] . ". Please click on the link below to confirm your registration.\n\n" . $url . "\n\nPlease do not reply to this message.";
		$messageHtml = "Thank you for registering with " . $systemPreferences['appName'] . ". Please click on the link below to confirm your registration.<br /><br /><a href='" . $url . "'>" . $url . "</a><br /><br />Please do not reply to this message.";
		$messageHtml = str_replace('{CONTENT}', $messageHtml, loadHtmlEmailTemplate());

		return $phpmailerWrapper->sendEmail($name, $email, $systemPreferences['appName'], $mail_TL['OutgoingAddress'], $subject, $messageHtml, $messagePlain);
		
	}

	function emailPasswordResetKey($name, $email, $key) {
		
		if (!$name || !$email || !$key) return false;
		
		global $mail_TL;
		global $environmentals;
		global $systemPreferences;
		global $phpmailerWrapper;

		$url = $environmentals['protocol'] . $environmentals['absoluteRoot'] . "/reset_password/" . $key;
		
		$subject = "[" . $systemPreferences['appName'] . "] Reset your password";

		$messagePlain = "A request has been received to reset your password on " . $systemPreferences['appName'] . ". If this is a valid request, please click on the link below; otherwise do nothing and your password will remain unchanged.\n\n" . $url . "\n\nPlease do not reply to this message.";
		$messageHtml = "A request has been received to reset your password on " . $systemPreferences['appName'] . ". If this is a valid request, please click on the link below; otherwise do nothing and your password will remain unchanged.<br /><br /><a href='" . $url . "'>" . $url . "</a><br /><br />Please do not reply to this message.";
		$messageHtml = str_replace('{CONTENT}', $messageHtml, loadHtmlEmailTemplate());

		return $phpmailerWrapper->sendEmail($name, $email, $systemPreferences['appName'], $mail_TL['OutgoingAddress'], $subject, $messageHtml, $messagePlain);
		
	}

	function emailNewEmailKey($name, $email, $key) {
		
		if (!$name || !$email || !$key) return false;
		
		global $mail_TL;
		global $environmentals;
		global $systemPreferences;
		global $phpmailerWrapper;

		$url = $environmentals['protocol'] . $environmentals['absoluteRoot'] . "/reset_email/" . $key;
		
		$subject = "[" . $systemPreferences['appName'] . "] Update your email address";

		$messagePlain = "A request has been received to update your email address on " . $systemPreferences['appName'] . ". If this is a valid request, please click on the link below; otherwise do nothing and your email address will remain unchanged.\n\n" . $url . "\n\nPlease do not reply to this message.";
		$messageHtml = "A request has been received to update your email address on " . $systemPreferences['appName'] . ". If this is a valid request, please click on the link below; otherwise do nothing and your email address will remain unchanged.<br /><br /><a href='" . $url . "'>" . $url . "</a><br /><br />Please do not reply to this message.";
		$messageHtml = str_replace('{CONTENT}', $messageHtml, loadHtmlEmailTemplate());

		return $phpmailerWrapper->sendEmail($name, $email, $systemPreferences['appName'], $mail_TL['OutgoingAddress'], $subject, $messageHtml, $messagePlain);
		
	}

	function emailNewUser($name, $email) {

		if (!$name || !$email) return false;
		
		global $mail_TL;
		global $environmentals;
		global $systemPreferences;
		global $phpmailerWrapper;
		
		$url = $environmentals['protocol'] . $environmentals['absoluteRoot'];
		
		$subject = "[" . $systemPreferences['appName'] . "] Welcome aboard!";

		$messagePlain = "You've successfully registered with " . $systemPreferences['appName'] . ".\n\n" . $url . "\n\nPlease do not reply to this message.";
		$messageHtml = "You've successfully registered with " . $systemPreferences['appName'] . ".<br /><br /><a href='" . $url . "'>" . $url . "</a><br /><br />Please do not reply to this message.";
		$messageHtml = str_replace('{CONTENT}', $messageHtml, loadHtmlEmailTemplate());
		
		return $phpmailerWrapper->sendEmail($name, $email, $systemPreferences['appName'], $mail_TL['OutgoingAddress'], $subject, $messageHtml, $messagePlain);
		
	}

	function emailAdministratorAboutNewUser($adminEmail, $registrant) {
		
		if (!$adminEmail || !$registrant) return false;
		
		global $mail_TL;
		global $environmentals;
		global $systemPreferences;
		global $phpmailerWrapper;

		$subject = "[" . $systemPreferences['appName'] . "] New user registration";

		$messagePlain = $registrant . " has successfully completed registration.";
		$messageHtml = str_replace("\n", "<br />", $messagePlain);
		$messageHtml = str_replace('{CONTENT}', $messageHtml, loadHtmlEmailTemplate());

		return $phpmailerWrapper->sendEmail($systemPreferences['appName'], $adminEmail, $systemPreferences['appName'], $mail_TL['OutgoingAddress'], $subject, $messageHtml, $messagePlain, '', '', '');
		
	}
	
/*	--------------------------------------
	Watchlist notifications
	-------------------------------------- */
	
	function notifyUserOfRumourUpdate($name, $email, $publicID, $description, $status) {

		if (!$name || !$email || !$publicID || !$description || !$status) return false;
		
		global $mail_TL;
		global $environmentals;
		global $systemPreferences;
		global $phpmailerWrapper;
		$parser = new parser_TL();
		
		$url = $environmentals['protocol'] . $environmentals['absoluteRoot'] . "/rumour/" . $publicID . "/" . $parser->seoFriendlySuffix($description);
		
		$subject = "[" . $systemPreferences['appName'] . "] Status update on a rumour you're watching";

		$messagePlain = "The following rumour has updated its status to " . $status . ":\n\n" . $url . "\n\nPlease do not reply to this message.";
		$messageHtml = "The following rumour has updated its status to " . $status . ":<br /><br /><a href='" . $url . "'>" . $description . "</a><br /><br />Please do not reply to this message.";
		$messageHtml = str_replace('{CONTENT}', $messageHtml, loadHtmlEmailTemplate());
		
		return $phpmailerWrapper->sendEmail($name, $email, $systemPreferences['appName'], $mail_TL['OutgoingAddress'], $subject, $messageHtml, $messagePlain);
		
	}
	
/*	--------------------------------------
	Rumours
	-------------------------------------- */
	
	function notifyOfRumour($name, $email, $publicID, $description, $assignedToMe = false) {

		if (!$name || !$email || !$publicID || !$description) return false;
				
		global $mail_TL;
		global $environmentals;
		global $systemPreferences;
		global $phpmailerWrapper;
		$parser = new parser_TL();
		
		$url = $environmentals['protocol'] . $environmentals['absoluteRoot'] . "/rumour/" . $publicID . "/" . $parser->seoFriendlySuffix($description);
		
		if ($assignedToMe) $subject = "[" . $systemPreferences['appName'] . "] You've been assigned a rumour";
		else $subject = "[" . $systemPreferences['appName'] . "] New rumour";
		
		if ($assignedToMe) {
			$messagePlain = "The following rumour has been assigned to you:\n\n" . $url . "\n\nPlease do not reply to this message.";
			$messageHtml = "The following rumour has been assigned to you:<br /><br /><a href='" . $url . "'>" . $description . "</a><br /><br />Please do not reply to this message.";
		}
		else {
			$messagePlain = "The following rumour has been added to " . $systemPreferences['appName'] . " and requires moderation:\n\n" . $url . "\n\nPlease do not reply to this message.";
			$messageHtml = "The following rumour has been added to " . $systemPreferences['appName'] . " and requires moderation:<br /><br /><a href='" . $url . "'>" . $description . "</a><br /><br />Please do not reply to this message.";
		}
		
		$messageHtml = str_replace('{CONTENT}', $messageHtml, loadHtmlEmailTemplate());
		
		return $phpmailerWrapper->sendEmail($name, $email, $systemPreferences['appName'], $mail_TL['OutgoingAddress'], $subject, $messageHtml, $messagePlain);
		
	}
	
/*	--------------------------------------
	User communications
	-------------------------------------- */
	
	function emailToUser($name, $email, $message, $replyTo) {
		
		if (!$name || !$email || !$message) return false;

		global $mail_TL;
		global $environmentals;
		global $systemPreferences;
		global $phpmailerWrapper;

		if ($replyTo) {
			$replyToName = $systemPreferences['appName'];
			$replyToEmail = $replyTo;
		}
		
		$subject = "[" . $systemPreferences['appName'] . "] Message";

		$messagePlain = $message;
		$messageHtml = str_replace("\n", "<br />", $messagePlain);
		$messageHtml = str_replace('{CONTENT}', $messageHtml, loadHtmlEmailTemplate());

		return $phpmailerWrapper->sendEmail($name, $email, $systemPreferences['appName'], $mail_TL['IncomingAddress'], $subject, $messageHtml, $messagePlain, '', $replyToName, $replyToEmail);
		
	}

	function emailFromUser($name, $email, $username, $telephone, $message) {
		
		if (!$name || !$email || !$message) return false;
		
		global $mail_TL;
		global $environmentals;
		global $systemPreferences;
		global $phpmailerWrapper;

		$subject = "[" . $systemPreferences['appName'] . "] Message from user";

		$messagePlain = $name . " (" . $email;
		if ($username) $messagePlain .= " / " . $username;
		if ($telephone) $messagePlain .= " / " . $telephone;
		$messagePlain .= ") writes:\n\n" . $message . "\n\nSystem diagnostics: " . $environmentals['client'] . " / Cookies: " . $environmentals['acceptsCookies'];
		$messageHtml = str_replace("\n", "<br />", $messagePlain);
		$messageHtml = str_replace('{CONTENT}', $messageHtml, loadHtmlEmailTemplate());

		return $phpmailerWrapper->sendEmail($systemPreferences['appName'], $mail_TL['IncomingAddress'], $systemPreferences['appName'], $mail_TL['OutgoingAddress'], $subject, $messageHtml, $messagePlain, '', $name, $email);
		
	}
	
/*	--------------------------------------
	System communications
	-------------------------------------- */
	
	function emailSystemNotification($message, $subject = 'System notification') {
		
		if (!$subject || !$message) return false;
		
		global $mail_TL;
		global $systemPreferences;
		global $phpmailerWrapper;

		$subject = "[" . $systemPreferences['appName'] . "] " . $subject;

		$messagePlain =  $message;
		$messageHtml = str_replace("\n", "<br />", $messagePlain);
		$messageHtml = str_replace('{CONTENT}', $messageHtml, loadHtmlEmailTemplate());

		return $phpmailerWrapper->sendEmail($systemPreferences['appName'], $mail_TL['IncomingAddress'], $systemPreferences['appName'], $mail_TL['OutgoingAddress'], $subject, $messageHtml, $messagePlain);
		
	}
	
/*	--------------------------------------
	Email template
	-------------------------------------- */
	
	function loadHtmlEmailTemplate() {
		global $environmentals;
		global $logo;
		
		$fileManager = new fileManager_TL();
		$content = $fileManager->readTextFile('includes/views/shared/email.html');
		$content = str_replace('{URL}', trim($environmentals['protocol'] . $environmentals['absoluteRoot'], '/'), $content);
		$content = str_replace('{LOGO}', $logo, $content);
		return $content;
	}
			
?>