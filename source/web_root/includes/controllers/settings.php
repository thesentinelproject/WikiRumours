<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in['is_administrator']) forceLoginThenRedirectHere();
		
	// parse query string
		if ($parameter1 == 'default') $filters = null;
		else {
			$filters = array();
			$result = explode('|', urldecode($parameter1));
			foreach ($result as $keyValue) {
				$filterKeyValues = explode('=', $keyValue);
				foreach ($filterKeyValues as $key => $value) {
					if (trim($key) && trim($value)) $filters[trim($key)] = trim($value);
				}
			}
		}
		
		$pageSuccess = $parameter2;

	// queries
		$notifications = retrieveFromDb('notifications', null, null, null, null);
		
		$faqs = retrieveFaqs(null, null, null, $tablePrefix . 'faq_chapters.chapter_position ASC, ' . $tablePrefix . 'faqs.faq_position ASC');
		$faqChapters = retrieveFaqChapters(null, null, null, $tablePrefix . 'faq_chapters.chapter_position ASC');
		$allChapters = array();
		for ($counter = 0; $counter < count($faqChapters); $counter++) {
			$allChapters[$faqChapters[$counter]['chapter_id']] = $faqChapters[$counter]['name'];
		}
		$allPositions = array();
		for ($counter = 1; $counter <= 100; $counter++) {
			$allPositions[$counter] = $counter;
		}
		
	// instantiate required class(es)
		$validator = new inputValidator_TL();
		$operators = new operators_TL();
		$parser = new parser_TL();
		$fileManager = new fileManager_TL();
		$converter = new mediaConverter_TL();
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {
		
		$pageError = '';

		if ($_POST['formName'] == 'editPreferencesForm' && $_POST['logoToDelete'] == 'Y') {
			$success = unlink($userLogo);
			if (!$success) $pageError .= "There was a problem deleting the custom logo. ";
			else {
				header('Location: /settings/default/logo_deleted');
				exit();
			}
		}
		
		elseif ($_POST['formName'] == 'editPreferencesForm') {

			// clean input
				$_POST = $parser->trimAll($_POST);
				
			// check for errors
				if (!$_POST['appName']) $_POST['appName'] = 'WikiRumours';
				if (!$_POST['appDescription']) $_POST['appDescription'] = 'Conflict mitigration through information';
				if ($_FILES['changeLogo']['tmp_name']) {
					if (!$fileManager->isImage($_FILES['changeLogo']['tmp_name'])) $pageError .= "An invalid image was uploaded; please upload a JPG, PNG or GIF. ";
				}
				
			if (!$pageError) {

				// update preferences
					deleteFromDb('preferences', array('user_id'=>'', 'preference'=>'appName'), null, null, null, null, 1);
					deleteFromDb('preferences', array('user_id'=>'', 'preference'=>'appDescription'), null, null, null, null, 1);
					insertIntoDb('preferences', array('preference'=>'appName', 'value'=>$_POST['appName']));
					insertIntoDb('preferences', array('preference'=>'appDescription', 'value'=>$_POST['appDescription']));
					
				// update profile image
					if ($_FILES['changeLogo']['tmp_name']) {

						// delete old image
							if (file_exists($userLogo)) {
								$success = unlink($userLogo);
								if (!$success) $pageError .= "There was a problem deleting the custom logo. ";
							}
							
						// save new image
							if (!$pageError) {
								$success = $converter->convertImage($_FILES['changeLogo']['tmp_name'], 'logo.png', 'assets/preferences', 180, 0, null);
								if (!file_exists('assets/preferences/logo.png')) $pageError .= "There was a problem saving your custom logo. ";
							}
					}
					
				if (!$pageError) {
					
					// update log
						$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated system preferences";
						$logger->logItInDb($activity);
					
					// redirect
						header('Location: /settings/default/preferences_updated');
						exit();
						
				}
					
					
			}
			
		}
		
		elseif ($_POST['formName'] == 'editFaqForm' && $_POST['faqToUpdate']) {

			// clean input
				$_POST = $parser->trimAll($_POST);
				
			// check for errors
				if (!$_POST['question_' . $_POST['faqToUpdate']]) $pageError .= "Please specify a question. ";
				if (!$_POST['answer_' . $_POST['faqToUpdate']]) $pageError .= "Please specify an answer. ";

			if (!$pageError) {
				
				// update database
					updateDb('faqs', array('faq_position'=>$_POST['faq_position_' . $_POST['faqToUpdate']], 'question'=>$_POST['question_' . $_POST['faqToUpdate']], 'answer'=>$_POST['answer_' . $_POST['faqToUpdate']], 'chapter_id'=>$_POST['chapter_id_' . $_POST['faqToUpdate']]), array('faq_id'=>$_POST['faqToUpdate']), null, null, null);
					
				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated the FAQ &quot;" . $_POST['question_' . $_POST['faqToUpdate']] . "&quot; (" . $_POST['faqToUpdate'] . ")";
					$logger->logItInDb($activity);
				
				// redirect
					header('Location: /settings/default/faq_updated');
					exit();

			}
					
		}
		
		elseif ($_POST['formName'] == 'editFaqForm' && $_POST['addFaq']) {
			
			// clean input
				$_POST = $parser->trimAll($_POST);
				
			// check for errors
				if (!$_POST['question_add']) $pageError .= "Please specify a question. ";
				if (!$_POST['answer_add']) $pageError .= "Please specify an answer. ";

			if (!$pageError) {
				
				// update database
					$faqID = insertIntoDb('faqs', array('faq_position'=>$_POST['faq_position_add'], 'question'=>$_POST['question_add'], 'answer'=>$_POST['answer_add'], 'chapter_id'=>$_POST['chapter_id_add']));
					
				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has added the FAQ &quot;" . $_POST['question_' . $_POST['faqToEdit']] . "&quot; (" . $faqID . ")";
					$logger->logItInDb($activity);
				
				// redirect
					header('Location: /settings/default/faq_added');
					exit();

			}
					
		}
		
		elseif ($_POST['formName'] == 'editFaqForm' && $_POST['faqToDelete']) {
			
			// delete FAQ
				deleteFromDb('faqs', array('faq_id'=>$_POST['faqToDelete']), null, null, null, null, 1);
				
			// update log
				$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has deleted the FAQ &quot;" . $_POST['question_' . $_POST['faqToDelete']] . "&quot; (" . $_POST['faqToDelete'] . ")";
				$logger->logItInDb($activity);
				
			// redirect
				header('Location: /settings/default/faq_deleted');
				exit();
				
		}

		elseif ($_POST['formName'] == 'editFaqChapterForm' && $_POST['faqChapterToUpdate']) {
			
			// clean input
				$_POST = $parser->trimAll($_POST);
				
			// check for errors
				if (!$_POST['name_' . $_POST['faqChapterToUpdate']]) $pageError .= "Please specify a chapter name. ";

			if (!$pageError) {
				
				// update database
					updateDb('faq_chapters', array('chapter_position'=>$_POST['chapter_position_' . $_POST['faqChapterToUpdate']], 'name'=>$_POST['name_' . $_POST['faqChapterToUpdate']]), array('chapter_id'=>$_POST['faqChapterToUpdate']), null, null, null);
					
				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated the FAQ chapter &quot;" . $_POST['name_' . $_POST['faqChapterToUpdate']] . "&quot; (" . $_POST['faqChapterToUpdate'] . ")";
					$logger->logItInDb($activity);
				
				// redirect
					header('Location: /settings/default/faq_chapter_updated');
					exit();

			}
					
		}
		
		elseif ($_POST['formName'] == 'editFaqChapterForm' && $_POST['addFaqChapter']) {
			
			// clean input
				$_POST = $parser->trimAll($_POST);
				
			// check for errors
				if (!$_POST['name_add']) $pageError .= "Please specify a chapter name. ";

			if (!$pageError) {
				
				// update database
					$chapterID = insertIntoDb('faq_chapters', array('chapter_position'=>$_POST['chapter_position_add'], 'name'=>$_POST['name_add']));
					
				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has added the FAQ chapter &quot;" . $_POST['name_' . $_POST['faqChapterToEdit']] . "&quot; (" . $chapterID . ")";
					$logger->logItInDb($activity);
				
				// redirect
					header('Location: /settings/default/faq_chapter_added');
					exit();

			}
					
		}
		
		elseif ($_POST['formName'] == 'editFaqChapterForm' && $_POST['faqChapterToDelete']) {
			
			// delete chapter
				deleteFromDb('faq_chapters', array('chapter_id'=>$_POST['faqChapterToDelete']), null, null, null, null, 1);
				
			// update log
				$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has deleted the FAQ chapter &quot;" . $_POST['name_' . $_POST['faqChapterToDelete']] . "&quot; (" . $_POST['faqChapterToDelete'] . ")";
				$logger->logItInDb($activity);
				
			// redirect
				header('Location: /settings/default/faq_chapter_deleted');
				exit();
		}
		
		elseif ($_POST['formName'] == 'editNotificationsForm' && $_POST['notificationEmailToUpdate']) {

			// clean input
				$_POST = $parser->trimAll($_POST);
				if (isset($_POST['new_registrations_' . $_POST['notificationEmailToUpdate']])) $_POST['new_registrations_' . $_POST['notificationEmailToUpdate']] = 1;
				if (isset($_POST['contact_form_' . $_POST['notificationEmailToUpdate']])) $_POST['contact_form_' . $_POST['notificationEmailToUpdate']] = 1;
				
			// check for errors
				if (!$validator->validateEmailBasic($_POST['notification_email_' . $_POST['notificationEmailToUpdate']])) $pageError .= "Please specify a valid email address. ";

			if (!$pageError) {
				
				// update database
					updateDb('notifications', array('email'=>$_POST['notification_email_' . $_POST['notificationEmailToUpdate']], 'new_registrations'=>$_POST['new_registrations_' . $_POST['notificationEmailToUpdate']], 'contact_form'=>$_POST['contact_form_' . $_POST['notificationEmailToUpdate']]), array('notification_id'=>$_POST['notificationEmailToUpdate']), null, null, null);
					
				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated a notification for &quot;" . $_POST['notification_email_' . $_POST['notificationEmailToUpdate']] . "&quot; (notification_id " . $_POST['notificationEmailToUpdate'] . ")";
					$logger->logItInDb($activity);
				
				// redirect
					header('Location: /settings/default/notification_updated');
					exit();

			}
			
		}
		
		elseif ($_POST['formName'] == 'editNotificationsForm' && $_POST['notificationEmailToDelete']) {
			
			// delete notification
				deleteFromDb('notifications', array('notification_id'=>$_POST['notificationEmailToDelete']), null, null, null, null, 1);
				
			// update log
				$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has deleted a notification for &quot;" . $_POST['notification_email_' . $_POST['notificationEmailToDelete']] . "&quot; (notification_id " . $_POST['notificationEmailToDelete'] . ")";
				$logger->logItInDb($activity);
				
			// redirect
				header('Location: /settings/default/notification_deleted');
				exit();
				
		}
		
		elseif ($_POST['formName'] == 'editNotificationsForm' && $_POST['addNotificationEmail']) {
			
			// clean input
				$_POST = $parser->trimAll($_POST);
				if (isset($_POST['new_registrations_add'])) $_POST['new_registrations_add'] = 1;
				if (isset($_POST['contact_form_add'])) $_POST['contact_form_add'] = 1;
				
			// check for errors
				if (!$validator->validateEmailBasic($_POST['notification_email_add'])) $pageError .= "Please specify a valid email address. ";

			if (!$pageError) {
				
				// update database
					$notificationID = insertIntoDb('notifications', array('email'=>$_POST['notification_email_add'], 'new_registrations'=>$_POST['new_registrations_add'], 'contact_form'=>$_POST['contact_form_add']));
					
				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has added a notification for &quot;" . $_POST['notification_email_add'] . "&quot; (notification_id " . $notificationID . ")";
					$logger->logItInDb($activity);
				
				// redirect
					header('Location: /settings/default/notification_added');
					exit();
					
			}
			
		}
		
	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>