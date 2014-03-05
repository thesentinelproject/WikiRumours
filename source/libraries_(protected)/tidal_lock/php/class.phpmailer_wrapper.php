<?php

	class phpmailerWrapper_TL {
		
		public function sendEmail($toName, $toAddress, $fromName, $fromAddress, $subject, $messageHtml, $messagePlain, $attachmentsLocation = null, $replyToName = false, $replyToAddress = false) {
	
			global $mail_TL;
			$validator = new inputValidator_TL();
			$parser = new parser_TL();
	
			// check for errors
				if (!$mail_TL['Host'] || !$mail_TL['User'] || !$mail_TL['User']) {
					errorManager_TL::addError("Mail server not specified in configuration file.");
					return false;
				}
				if (!$toAddress || !$validator->validateEmailRobust($toAddress)) {
					errorManager_TL::addError("Missing recipient email address.");
					return false;
				}
				if (!$fromAddress || !$validator->validateEmailRobust($fromAddress)) {
					errorManager_TL::addError("Missing sender email address.");
					return false;
				}
				if (!$messagePlain) {
					errorManager_TL::addError("Missing ASCII message content.");
					return false;
				}
				if (!$messageHtml) {
					errorManager_TL::addError("Missing HTML message content.");
					return false;
				}
				if (!$subject) {
					errorManager_TL::addError("Missing subject line.");
					return false;
				}
			
			$phpMailer = new PHPMailer(true);
			
			try {
	
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
				
				$phpMailer->From = $fromAddress;
				$phpMailer->FromName = $fromName;
		
				$phpMailer->AddAddress($toAddress, $toName);
		
				if ($replyToAddress) $phpMailer->AddReplyTo($replyToAddress, $replyToName);
		
				if ($attachmentsLocation != '') {
					if (file_exists($attachmentsLocation)) {
						$attachmentsLocation = rtrim($attachmentsLocation, '/') . '/';
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
					else {
						errorManager_TL::addError("Unable to find attachment(s) at " . $attachmentsLocation);
						return false;
					}
					
				}
								
				$phpMailer->IsHTML(true);
				
				$phpMailer->Subject = $subject;
				$phpMailer->Body    = $messageHtml;
				$phpMailer->AltBody = $messagePlain;
	
				$phpMailer->Send();
				
			} catch (phpmailerException $e) {
				errorManager_TL::addError($parser->removeHTML($e->errorMessage()));
				return false;
			} catch (Exception $e) {
				errorManager_TL::addError($e->getMessage());
				return false;
			}
	
			return true;
			
		}
		
	}

/*
	PHP Mailer Wrapper

	::	DESCRIPTION
	
		Abstracts the process of sending email with PHP Mailer.

	::	DEPENDENT ON
	
		inputValidator_TL
		fileManager_TL
		parsers_TL

	::	VERSION HISTORY

	::	LICENSE
	
		Copyright (C) Timothy Quinn / Tidal Lock / Consolidated Biro
		
		Permission is hereby granted, free of charge, to any person
		obtaining a copy of this software and associated documentation
		files (the "Software"), to deal in the Software without
		restriction, including without limitation the rights to use,
		copy, modify, merge, publish, distribute, sublicense, and/or
		sell copies of the Software, and to permit persons to whom the
		Software is furnished to do so, subject to the following
		conditions:
		
		The above copyright notice and this permission notice shall be
		included in all copies or substantial portions of the Software.
		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
		EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
		OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
		NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
		HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
		WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
		FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
		OTHER DEALINGS IN THE SOFTWARE.
*/
	
?>
