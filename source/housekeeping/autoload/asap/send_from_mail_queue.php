<?php

	if ($currentDatabase == 'staging' || $currentDatabase == 'production') {

		$maxMessagesToSend = 5;

		// retrieve unsent mail in order of priority, and then in order of earliest queued first
			$unsentMail = retrieveFromDb('mail_queue', null, null, null, null, null, $tablePrefix . "mail_queue.sent_on = '0000-00-00 00:00:00' AND " . $tablePrefix . "mail_queue.failed_attempts < '" . $systemPreferences['Maximum allowable failures per email address'] . "'", null, $tablePrefix . 'mail_queue.priority DESC, ' . $tablePrefix . 'mail_queue.queued_on ASC');
			$logger->logItInMemory("Found " . count($unsentMail) . " queued messages");
			$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

			if (count($unsentMail)) {

				for ($counter = 0; $counter < min(count($unsentMail), $maxMessagesToSend); $counter++) {

					$logger->logItInMemory("Preparing to send email to " . $unsentMail[0]['to_email'] . " (mail_id " . $unsentMail[0]['mail_id'] . ")");
					$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

					$success = $notifier->sendFromMailQueue($unsentMail[$counter]['mail_id']);

					if ($success) {
						// update date sent
							updateDbSingle('mail_queue', array('sent_on'=>date('Y-m-d H:i:s')), array('mail_id'=>$unsentMail[$counter]['mail_id']));
						// update log
							$logger->logItInMemory("Successfully sent email to " . $unsentMail[$counter]['to_email'] . " (mail_id " . $unsentMail[$counter]['mail_id'] . ")");
							$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
					}
					else {
						// update failure count
							updateDbSingle('mail_queue', array('failed_attempts'=>($unsentMail[$counter]['failed_attempts'] + 1)), array('mail_id'=>$unsentMail[$counter]['mail_id']));
						// update log
							$logger->logItInMemory("Unable to send email to " . $unsentMail[$counter]['to_email'] . " (mail_id " . $unsentMail[$counter]['mail_id'] . ")");
							$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
					}

				}

			}

	}
?>
