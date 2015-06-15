<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$pageStatus = $parameter1;

	// authenticate user
		if (!$logged_in['is_administrator'] || !$logged_in['can_edit_content']) forceLoginThenRedirectHere();
		
	// queries
		$faqs = retrieveFaqs(null, null, null, $tablePrefix . 'faq_sections.position ASC, ' . $tablePrefix . 'faqs.position ASC');
		$faqSections = retrieveFaqSections(null ,null, null, $tablePrefix . 'faq_sections.position ASC');

		$allSections = array();
		for ($counter = 0; $counter < count($faqSections); $counter++) {
			$allSections[$faqSections[$counter]['section_id']] = $faqSections[$counter]['name'];
		}

		$allPositions = array();
		for ($counter = 1; $counter <= 100; $counter++) {
			$allPositions[$counter] = $counter;
		}
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {
		
		$pageError = '';

		if ($_POST['formName'] == 'editFaqForm' && $_POST['faqToDelete']) {
			
			// delete FAQ
				deleteFromDb('faqs', array('faq_id'=>$_POST['faqToDelete']), null, null, null, null, 1);
				
			// update log
				$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has deleted the FAQ &quot;" . $_POST['question_' . $_POST['faqToDelete']] . "&quot; (faq_id " . $_POST['faqToDelete'] . ")";
				$logger->logItInDb($activity);
				
			// redirect
				header('Location: /admin_faqs/faq_deleted');
				exit();
				
		}

		elseif ($_POST['formName'] == 'editFaqForm') {

			// clean input
				$_POST = $parser->trimAll($_POST);
				
			// check for errors
				// check edit
					for ($counter = 0; $counter < count($faqs); $counter++) {
						if (!$_POST['question_' . $faqs[$counter]['faq_id']]) $pageError .= "Please specify a question. ";
						if (!$_POST['answer_' . $faqs[$counter]['faq_id']]) $pageError .= "Please specify an answer. ";
					}
				// check add
					if ($_POST['answer_add'] && !$_POST['question_add']) $pageError .= "Please specify a question. ";
					if ($_POST['question_add'] && !$_POST['answer_add']) $pageError .= "Please specify an answer. ";

			if (!$pageError) {
				
				// update edit
					for ($counter = 0; $counter < count($faqs); $counter++) {
						updateDb('faqs', array('question'=>$_POST['question_' . $faqs[$counter]['faq_id']], 'answer'=>$_POST['answer_' . $faqs[$counter]['faq_id']], 'position'=>$_POST['position_' . $faqs[$counter]['faq_id']] ,'section_id'=>$_POST['section_id_' . $faqs[$counter]['faq_id']]), array('faq_id'=>$faqs[$counter]['faq_id']), null, null, null, null, 1);
					}
				// update add
					if ($_POST['question_add'] && $_POST['answer_add']) {
						$faqID = insertIntoDb('faqs', array('question'=>$_POST['question_add'], 'answer'=>$_POST['answer_add'], 'position'=>$_POST['position_add'] ,'section_id'=>$_POST['section_id_add']));
					}

				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated FAQs";
					$logger->logItInDb($activity);
				
				// redirect
					header('Location: /admin_faqs/faqs_updated');
					exit();

			}
					
		}
		
		elseif ($_POST['formName'] == 'editFaqSectionForm' && $_POST['faqSectionToDelete']) {
			
			// delete chapter and remove association from FAQs
				deleteFromDb('faq_sections', array('section_id'=>$_POST['faqSectionToDelete']), null, null, null, null, 1);
				updateDb('faqs', array('section_id'=>''), array('section_id'=>$_POST['faqSectionToDelete']));
				
			// update log
				$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has deleted the FAQ section &quot;" . $_POST['name_' . $_POST['faqSectionToDelete']] . "&quot; (section_id " . $_POST['faqSectionToDelete'] . ")";
				$logger->logItInDb($activity);
				
			// redirect
				header('Location: /admin_faqs/faq_section_deleted');
				exit();
		}
		
		elseif ($_POST['formName'] == 'editFaqSectionForm') {
			
			// clean input
				$_POST = $parser->trimAll($_POST);
				
			// check for errors
				for ($counter = 0; $counter < count($faqSections); $counter++) {
					if (!$_POST['name_' . $faqSections[$counter]['section_id']]) $pageError .= "Please specify a section name. ";
				}

			if (!$pageError) {
				
				// update database
					// update edit
						for ($counter = 0; $counter < count($faqSections); $counter++) {
							updateDb('faq_sections', array('position'=>$_POST['position_' . $faqSections[$counter]['section_id']], 'name'=>$_POST['name_' . $faqSections[$counter]['section_id']]), array('section_id'=>$faqSections[$counter]['section_id']), null, null, null);
						}
					// update add
						if ($_POST['name_add']) {
							$sectionID = insertIntoDb('faq_sections', array('name'=>$_POST['name_add'], 'position'=>$_POST['position_add']));
						}
					
				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated FAQ chapters";
					$logger->logItInDb($activity);
				
				// redirect
					header('Location: /admin_faqs/faq_sections_updated');
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