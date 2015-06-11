<?php

	$maxMessagesToSend = 5;

	// retrieve unsent mail (earliest first)
		$unsentMail = retrieveFromDb('mail_queue', null, null, null, null, null, $tablePrefix . "mail_queue.sent_on = '0000-00-00 00:00:00' AND " . $tablePrefix . "mail_queue.failed_attempts < '" . $systemPreferences['Maximum allowable failures per email address'] . "'", null, $tablePrefix . 'mail_queue.queued_on ASC');
		$logger->logItInMemory("Found " . count($unsentMail) . " queued messages");
		$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

		if (count($unsentMail)) {

			for ($counter = 0; $counter < min(count($unsentMail), $maxMessagesToSend); $counter++) {

				$error = null;

				// validate input
					if (!$unsentMail[$counter]['to_email'] || !$input_validator->validateEmailRobust($unsentMail[$counter]['to_email'])) $error .= "Invalid recipient email address.";
					if (!$unsentMail[$counter]['from_email'] || !$input_validator->validateEmailRobust($unsentMail[$counter]['from_email'])) $error .= "Invalid sender email address.";
					if (!$unsentMail[$counter]['message_text']) $error .= "Missing message content.";
					if (!$unsentMail[$counter]['message_html']) $error .= "Missing HTML message content.";
					if (!$unsentMail[$counter]['subject']) $error .= "Missing subject line.";
					if ($unsentMail[$counter]['attachments_location'] && !file_exists($unsentMail[$counter]['attachments_location'])) $error .= "Unable to locate attachments.";

					if (!$error) {

						// send mail
							$phpMailer = new PHPMailer(true);
							
							$phpMailer->IsSMTP();
							$phpMailer->MailerDebug = false;
							$phpMailer->CharSet = 'UTF-8';
							
							if ($mail_TL['Host']) {
								$phpMailer->Host = $mail_TL['Host'];
								$phpMailer->Username = $mail_TL['User'];
								$phpMailer->Password = $mail_TL['Password'];
								$phpMailer->SMTPAuth = true;
								$phpMailer->SMTPDebug = 0;
								$phpMailer->do_debug = 0;
							}
							else {
								$phpMailer->Host = 'localhost';
								$phpMailer->SMTPAuth = false;
							}
							
							$phpMailer->From = $unsentMail[$counter]['from_email'];
							$phpMailer->FromName = $unsentMail[$counter]['from_name'];
							$phpMailer->AddAddress($unsentMail[$counter]['to_email'], $unsentMail[$counter]['to_name']);
							if ($unsentMail[$counter]['reply_email']) $phpMailer->AddReplyTo($unsentMail[$counter]['reply_email'], $unsentMail[$counter]['reply_name']);
					
							if ($unsentMail[$counter]['attachments_location'] != '') {
								$attachmentsLocation = rtrim($unsentMail[$counter]['attachments_location'], '/') . '/';
								$fileArray = array();
								$counter = 0;
							
								if ($handle = opendir($attachmentsLocation . '.')) {
									while (false !== ($file = readdir($handle))) {
										if ($file != "." && $file != "..") {
											$fileArray[$counter] = $file;
											$counter++;
										}
									}
									closedir($handle);
								}
							
								sort($fileArray);
												
								for ($counter = 0; $counter < count($fileArray); $counter++) {
									$phpMailer->AddAttachment($attachmentsLocation . $fileArray[$counter]);
								}
							}
											
							$phpMailer->IsHTML(true);
							
							$phpMailer->Subject = $unsentMail[$counter]['subject'];
							$phpMailer->Body    = $unsentMail[$counter]['message_html'];
							$phpMailer->AltBody = $unsentMail[$counter]['message_text'];

							$logger->logItInMemory("Preparing to send email to " . $unsentMail[$counter]['to_email'] . " (mail_id " . $unsentMail[$counter]['mail_id'] . ")");
							$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

							try {
								$phpMailer->Send();
							} catch (phpmailerException $e) {
								$error .= $parser->removeHTML($e->errorMessage());
							} catch (Exception $e) {
								$error .= $e->getMessage();
							}

					}

					if ($error) {
						// update failure count
							updateDb('mail_queue', array('failed_attempts'=>($unsentMail[$counter]['failed_attempts'] + 1)), array('mail_id'=>$unsentMail[$counter]['mail_id']), null, null, null, null, 1);
						// update log
							$logger->logItInMemory("Unable to send email to " . $unsentMail[$counter]['to_email'] . " (mail_id " . $unsentMail[$counter]['mail_id'] . "): " . $error);
							$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
					}
					else {
						// update date sent
							updateDb('mail_queue', array('sent_on'=>date('Y-m-d H:i:s')), array('mail_id'=>$unsentMail[$counter]['mail_id']), null, null, null, null, 1);
						// update log
							$logger->logItInMemory("Successfully sent email to " . $unsentMail[$counter]['to_email'] . " (mail_id " . $unsentMail[$counter]['mail_id'] . ")");
							$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
					}

			}

		}

?>
