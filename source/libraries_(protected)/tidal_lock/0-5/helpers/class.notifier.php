<?php

	class notifier_TL {

		public function sendFromMailQueue($mailID) {

			global $tl;
			$input_validator = new input_validator_TL();
			$parser = new parser_TL();

			// retrieve message
				if ($mailID) $result = retrieveFromDb('mail_queue', null, ['mail_id'=>$mailID]);

			// check for errors
				if (!@$mailID) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No mail ID specified.\n";
					return false;
				}

				if (!count($result)) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to locate desired email.\n";
					return false;
				}

				if (!$result[0]['to_email'] || !$input_validator->validateEmailRobust($result[0]['to_email'])) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Invalid recipient email address.\n";
					return false;
				}

				if (!$result[0]['from_email'] || !$input_validator->validateEmailRobust($result[0]['from_email'])) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Invalid sender email address.\n";
					return false;
				}

				if (!$result[0]['message_text']) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Missing message content.\n";
					return false;
				}

				if (!$result[0]['subject']) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Missing subject line.\n";
					return false;
				}

				if ($result[0]['attachments_location'] && !file_exists($result[0]['attachments_location'])) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to locate attachments.\n";
					return false;
				}

			// clean input
				if (!@$result[0]['message_html']) $result[0]['message_html'] = nl2br($result[0]['message_text']);

				if (@$result[0]['attachments_location']) {
					$result[0]['attachments_location'] = rtrim($result[0]['attachments_location'], '/') . '/';
					$attachments = array();
					if ($handle = opendir($result[0]['attachments_location'] . '.')) {
						while (false !== ($file = readdir($handle))) {
							if ($file != "." && $file != "..") {
								$attachments[] = $file;
							}
						}
						closedir($handle);
					}
				}

			// prepare email
				$phpMailer = new PHPMailer(true);
				
				$phpMailer->CharSet = 'UTF-8';
				
				if ($tl->mail['Host']) {
					$phpMailer->IsSMTP();
					$phpMailer->MailerDebug = false;
					$phpMailer->SMTPAuth = true;
					$phpMailer->SMTPDebug = 0;
					if (@$tl->mail['Secure']) $phpMailer->SMTPSecure = $tl->mail['Secure'];
					$phpMailer->Host = $tl->mail['Host'];
					if (@$tl->mail['Port']) $phpMailer->Port = $tl->mail['Port'];
					$phpMailer->Username = $tl->mail['User'];
					$phpMailer->Password = $tl->mail['Password'];
					$phpMailer->do_debug = 0;
				}
				else {
					$phpMailer->Host = 'localhost';
					$phpMailer->SMTPAuth = false;
				}
						
				$phpMailer->From = $result[0]['from_email'];
				$phpMailer->FromName = $result[0]['from_name'];
				$phpMailer->AddAddress($result[0]['to_email'], $result[0]['to_name']);
				if ($result[0]['reply_email']) $phpMailer->AddReplyTo($result[0]['reply_email'], $result[0]['reply_name']);
		
				if (count(@$attachments)) {
					foreach ($attachments as $attachment) {
						$phpMailer->AddAttachment($result[0]['attachments_location'] . $attachment);
					}
				}
										
				$phpMailer->IsHTML(true);
				
				$phpMailer->Subject = $result[0]['subject'];
				$phpMailer->Body    = $result[0]['message_html'];
				$phpMailer->AltBody = $result[0]['message_text'];

			// send email
				try {
					$phpMailer->Send();
				} catch (phpmailerException $e) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": " . $parser->removeHTML($e->errorMessage()) . "\n";
					return false;
				} catch (Exception $e) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": " . $e->getMessage() . "\n";
					return false;
				}

			return true;

		}

	}

?>
