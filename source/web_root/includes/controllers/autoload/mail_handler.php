<?php

/*	--------------------------------------
	Registration & login
	-------------------------------------- */

	function emailRegistrationKey($name, $email, $key) {

		if (!$name || !$email || !$key) return false;
		
		global $mail_TL;
		global $environmentals;
		global $systemPreferences;
		global $pseudonym;
		
		$url = $environmentals['protocol'] . $environmentals['absoluteRoot'] . "/confirm_registration/" . $key;
		
		$subject = "[" . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . "] Please confirm your registration";

		$messagePlain = "Thank you for registering with " . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . ". Please click on the link below to confirm your registration.\n\n" . $url . "\n\nPlease do not reply to this message.";
		$messageHtml = createHtmlEmail("Thank you for registering with " . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . ". Please click on the link below to confirm your registration.<br /><br /><a href='" . $url . "'>" . $url . "</a><br /><br />Please do not reply to this message.");

		return insertIntoDb('mail_queue', array('to_name'=>$name, 'to_email'=>$email, 'from_name'=>(@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']), 'from_email'=>(@$pseudonym['outgoing_email'] ? $pseudonym['outgoing_email'] : $mail_TL['OutgoingAddress']), 'subject'=>$subject, 'message_html'=>$messageHtml, 'message_text'=>$messagePlain, 'queued_on'=>date('Y-m-d H:i:s')));
		
	}

	function emailPasswordResetKey($name, $email, $key) {
		
		if (!$name || !$email || !$key) return false;
		
		global $mail_TL;
		global $environmentals;
		global $systemPreferences;
		global $pseudonym;

		$url = $environmentals['protocol'] . $environmentals['absoluteRoot'] . "/reset_password/" . $key;
		
		$subject = "[" . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . "] Reset your password";

		$messagePlain = "A request has been received to reset your password on " . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . ". If this is a valid request, please click on the link below; otherwise do nothing and your password will remain unchanged.\n\n" . $url . "\n\nPlease do not reply to this message.";
		$messageHtml = createHtmlEmail("A request has been received to reset your password on " . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . ". If this is a valid request, please click on the link below; otherwise do nothing and your password will remain unchanged.<br /><br /><a href='" . $url . "'>" . $url . "</a><br /><br />Please do not reply to this message.");

		return insertIntoDb('mail_queue', array('to_name'=>$name, 'to_email'=>$email, 'from_name'=>(@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']), 'from_email'=>(@$pseudonym['outgoing_email'] ? $pseudonym['outgoing_email'] : $mail_TL['OutgoingAddress']), 'subject'=>$subject, 'message_html'=>$messageHtml, 'message_text'=>$messagePlain, 'queued_on'=>date('Y-m-d H:i:s')));
		
	}

	function emailNewEmailKey($name, $email, $key) {
		
		if (!$name || !$email || !$key) return false;
		
		global $mail_TL;
		global $environmentals;
		global $systemPreferences;
		global $pseudonym;

		$url = $environmentals['protocol'] . $environmentals['absoluteRoot'] . "/reset_email/" . $key;
		
		$subject = "[" . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . "] Update your email address";

		$messagePlain = "A request has been received to update your email address on " . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . ". If this is a valid request, please click on the link below; otherwise do nothing and your email address will remain unchanged.\n\n" . $url . "\n\nPlease do not reply to this message.";
		$messageHtml = createHtmlEmail("A request has been received to update your email address on " . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . ". If this is a valid request, please click on the link below; otherwise do nothing and your email address will remain unchanged.<br /><br /><a href='" . $url . "'>" . $url . "</a><br /><br />Please do not reply to this message.");

		return insertIntoDb('mail_queue', array('to_name'=>$name, 'to_email'=>$email, 'from_name'=>(@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']), 'from_email'=>(@$pseudonym['outgoing_email'] ? $pseudonym['outgoing_email'] : $mail_TL['OutgoingAddress']), 'subject'=>$subject, 'message_html'=>$messageHtml, 'message_text'=>$messagePlain, 'queued_on'=>date('Y-m-d H:i:s')));
		
	}

	function emailNewUser($name, $email) {

		if (!$name || !$email) return false;
		
		global $mail_TL;
		global $environmentals;
		global $systemPreferences;
		global $pseudonym;
		
		$url = $environmentals['protocol'] . $environmentals['absoluteRoot'];
		
		$subject = "[" . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . "] Welcome aboard!";

		$messagePlain = "You've successfully registered with " . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . ".\n\n" . $url . "\n\nPlease do not reply to this message.";
		$messageHtml = createHtmlEmail("You've successfully registered with " . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . ".<br /><br /><a href='" . $url . "'>" . $url . "</a><br /><br />Please do not reply to this message.");

		return insertIntoDb('mail_queue', array('to_name'=>$name, 'to_email'=>$email, 'from_name'=>(@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']), 'from_email'=>(@$pseudonym['outgoing_email'] ? $pseudonym['outgoing_email'] : $mail_TL['OutgoingAddress']), 'subject'=>$subject, 'message_html'=>$messageHtml, 'message_text'=>$messagePlain, 'queued_on'=>date('Y-m-d H:i:s')));
		
	}

	function emailAdministratorAboutSuccessfulRegistrant($adminEmail, $registrant) {
		
		if (!$adminEmail || !$registrant) return false;
		
		global $mail_TL;
		global $environmentals;
		global $systemPreferences;
		global $pseudonym;

		$subject = "[" . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . "] New user registration";

		$messagePlain = $registrant . " has successfully registered at " . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . ".";
		$messageHtml = createHtmlEmail(str_replace("\n", "<br />", $messagePlain));

		return insertIntoDb('mail_queue', array('to_name'=>(@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']), 'to_email'=>$adminEmail, 'from_name'=>(@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']), 'from_email'=>(@$pseudonym['outgoing_email'] ? $pseudonym['outgoing_email'] : $mail_TL['OutgoingAddress']), 'subject'=>$subject, 'message_html'=>$messageHtml, 'message_text'=>$messagePlain, 'queued_on'=>date('Y-m-d H:i:s')));
		
	}
	
/*	--------------------------------------
	Watchlist notifications
	-------------------------------------- */
	
	function notifyUserOfRumourStatusUpdate($name, $email, $publicID, $description, $status) {

		if (!$name || !$email || !$publicID || !$description || !$status) return false;
		
		global $mail_TL;
		global $environmentals;
		global $systemPreferences;
		global $parser;
		global $pseudonym;

		$url = $environmentals['protocol'] . $environmentals['absoluteRoot'] . "/rumour/" . $publicID . "/" . $parser->seoFriendlySuffix($description);
		
		$subject = "[" . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . "] Status update on a rumour you're watching";

		$messagePlain = "The following rumour has updated its status to " . $status . ":\n\n" . $url . "\n\nPlease do not reply to this message.";
		$messageHtml = "The following rumour has updated its status to " . $status . ":<br /><br /><a href='" . $url . "'>" . $description . "</a><br /><br />Please do not reply to this message.";
		$messageHtml = createHtmlEmail(str_replace("\n", "<br />", $messageHtml));
		
		return insertIntoDb('mail_queue', array('to_name'=>$name, 'to_email'=>$email, 'from_name'=>(@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']), 'from_email'=>(@$pseudonym['outgoing_email'] ? $pseudonym['outgoing_email'] : $mail_TL['OutgoingAddress']), 'subject'=>$subject, 'message_html'=>$messageHtml, 'message_text'=>$messagePlain, 'queued_on'=>date('Y-m-d H:i:s')));

	}

	function notifyUserOfRumourComment($name, $email, $publicID, $description, $comment, $author) {

		if (!$name || !$email || !$publicID || !$description || !$comment || !$author) return false;
		
		global $mail_TL;
		global $environmentals;
		global $systemPreferences;
		global $parser;
		global $pseudonym;
		
		$url = $environmentals['protocol'] . $environmentals['absoluteRoot'] . "/rumour/" . $publicID . "/" . $parser->seoFriendlySuffix($description);
		
		$subject = "[" . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . "] New comment on a rumour you're watching";

		$messagePlain = $author . " has left a comment on the rumour " . '"' . $description . '"' . " (" . $url . "):\n\n" . $comment . "\n\nPlease do not reply to this message.";
		$messageHtml = $author . " has left a comment on the rumour <a href='" . $url . "'>" . $description . "</a>:<br /><br />" . $comment . "<br /><br />Please do not reply to this message.";
		$messageHtml = createHtmlEmail(str_replace("\n", "<br />", $messageHtml));
		
		return insertIntoDb('mail_queue', array('to_name'=>$name, 'to_email'=>$email, 'from_name'=>(@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']), 'from_email'=>(@$pseudonym['outgoing_email'] ? $pseudonym['outgoing_email'] : $mail_TL['OutgoingAddress']), 'subject'=>$subject, 'message_html'=>$messageHtml, 'message_text'=>$messagePlain, 'queued_on'=>date('Y-m-d H:i:s')));
		
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
		global $parser;
		global $pseudonym;
		
		$url = $environmentals['protocol'] . $environmentals['absoluteRoot'] . "/rumour/" . $publicID . "/" . $parser->seoFriendlySuffix($description);
		
		if ($assignedToMe) $subject = "[" . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . "] You've been assigned a rumour";
		else $subject = "[" . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . "] New rumour";
		
		if ($assignedToMe) {
			$messagePlain = "The following rumour has been assigned to you:\n\n" . $url . "\n\nPlease do not reply to this message.";
			$messageHtml = "The following rumour has been assigned to you:<br /><br /><a href='" . $url . "'>" . $description . "</a><br /><br />Please do not reply to this message.";
		}
		else {
			$messagePlain = "The following rumour has been added to " . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . " and requires moderation:\n\n" . $url . "\n\nPlease do not reply to this message.";
			$messageHtml = "The following rumour has been added to " . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . " and requires moderation:<br /><br /><a href='" . $url . "'>" . $description . "</a><br /><br />Please do not reply to this message.";
		}
		
		$messageHtml = createHtmlEmail(str_replace("\n", "<br />", $messageHtml));
		
		return insertIntoDb('mail_queue', array('to_name'=>$name, 'to_email'=>$email, 'from_name'=>(@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']), 'from_email'=>(@$pseudonym['outgoing_email'] ? $pseudonym['outgoing_email'] : $mail_TL['OutgoingAddress']), 'subject'=>$subject, 'message_html'=>$messageHtml, 'message_text'=>$messagePlain, 'queued_on'=>date('Y-m-d H:i:s')));
		
	}
	
/*	--------------------------------------
	User communications
	-------------------------------------- */
	
	function emailToUser($name, $email, $replyToName, $replyToEmail, $subject, $message) {
		
		if (!$name || !$email || !$subject || !$message) return false;

		global $mail_TL;
		global $systemPreferences;
		global $pseudonym;

		if ($replyToEmail && !$replyToName) $replyToName = (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']);
		
		$messagePlain = $message;
		$messageHtml = createHtmlEmail(str_replace("\n", "<br />", $messagePlain));
		
		return insertIntoDb('mail_queue', array('to_name'=>$name, 'to_email'=>$email, 'from_name'=>(@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']), 'from_email'=>(@$pseudonym['outgoing_email'] ? $pseudonym['outgoing_email'] : $mail_TL['OutgoingAddress']), 'subject'=>$subject, 'message_html'=>$messageHtml, 'message_text'=>$messagePlain, 'reply_name'=>$replyToName, 'reply_email'=>$replyToEmail, 'queued_on'=>date('Y-m-d H:i:s')));
		
	}

	function emailFromUser($name, $email, $username, $telephone, $message, $adminEmail) {
		
		if (!$name || !$email || !$message || !$adminEmail) return false;
		
		global $mail_TL;
		global $environmentals;
		global $systemPreferences;
		global $pseudonym;

		$subject = "[" . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . "] Message from user";

		$messagePlain = $name . " (" . $email;
		if ($username) $messagePlain .= " / " . $username;
		if ($telephone) $messagePlain .= " / " . $telephone;
		$messagePlain .= ") writes:\n\n" . $message . "\n\nSystem diagnostics: " . $environmentals['client'] . " / Cookies: " . $environmentals['acceptsCookies'];
		$messageHtml = createHtmlEmail(str_replace("\n", "<br />", $messagePlain));

		return insertIntoDb('mail_queue', array('to_name'=>(@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']), 'to_email'=>$adminEmail, 'from_name'=>(@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']), 'from_email'=>(@$pseudonym['outgoing_email'] ? $pseudonym['outgoing_email'] : $mail_TL['OutgoingAddress']), 'subject'=>$subject, 'message_html'=>$messageHtml, 'message_text'=>$messagePlain, 'reply_name'=>$from, 'reply_email'=>$email, 'queued_on'=>date('Y-m-d H:i:s')));
		
	}
	
/*	--------------------------------------
	System communications
	-------------------------------------- */
	
	function emailSystemNotification($message, $subject = 'System notification') {
		
		if (!$subject || !$message) return false;
		
		global $mail_TL;
		global $systemPreferences;
		global $pseudonym;

		$subject = "[" . (@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']) . "] " . $subject;

		$messagePlain =  $message;
		$messageHtml = createHtmlEmail(str_replace("\n", "<br />", $messagePlain));

		return insertIntoDb('mail_queue', array('to_name'=>(@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']), 'to_email'=>$mail_TL['IncomingAddress'], 'from_name'=>(@$pseudonym['name'] ? $pseudonym['name'] : $systemPreferences['Name of this application']), 'from_email'=>(@$pseudonym['outgoing_email'] ? $pseudonym['outgoing_email'] : $mail_TL['OutgoingAddress']), 'subject'=>$subject, 'message_html'=>$messageHtml, 'message_text'=>$messagePlain, 'queued_on'=>date('Y-m-d H:i:s')));
		
	}
	
/*	--------------------------------------
	Email template
	-------------------------------------- */
	
	function createHtmlEmail($content) {

		global $systemPreferences;
		global $environmentals;
		global $pseudonym;
		global $file_manager;

		$html = $file_manager->readTextFile(__DIR__ . '../../../views/shared/email.html');

		$html = str_replace('{URL}', $environmentals['protocol'] . $environmentals['absoluteRoot'], $html);
		$html = str_replace('{LOGO}', trim($environmentals['protocol'] . $environmentals['absoluteRoot'], ' /') . '/' . (@$pseudonym['pseudonym_id'] ? 'assets/pseudonym_logos/' . $pseudonym['pseudonym_id'] . '.' . $pseudonym['logo_ext']: 'resources/img/logo.png'), $html);
		$html = str_replace('{CONTENT}', $content, $html);

		return $html;

	}
			
?>