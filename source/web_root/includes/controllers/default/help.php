<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$keywords = trim(urldecode($tl->page['parameter1']));

	// queries
		if ($keywords) {
			$otherCriteria = '';
			$keywordExplode = explode(' ', $keywords);
			foreach ($keywordExplode as $keyword) {
				if ($keyword) {
					if ($otherCriteria) $otherCriteria .= " AND";
					$otherCriteria .= " (" . $tablePrefix . "faqs.question LIKE '%" . addSlashes($keyword) . "%' OR " . $tablePrefix . "faqs.answer LIKE '%" . addSlashes($keyword) . "%')";
				}
			}
			$otherCriteria = trim($otherCriteria);
		}
		
		if (@$otherCriteria) {
			$faqs = retrieveFaqs(null, null, $otherCriteria, $tablePrefix . 'faq_sections.position ASC, ' . $tablePrefix . 'faqs.position ASC');
			
			for ($faqCounter = 0; $faqCounter < count(@$faqs); $faqCounter++) {
				$pageJavaScript .=	"  $('#question_" . $faqs[$faqCounter]['faq_id'] . "').click(function () {\n" .
									"    $('#answer_" . $faqs[$faqCounter]['faq_id'] . "').slideToggle('slow');\n" .
									"   });\n" .
									"  $('#answer_" . $faqs[$faqCounter]['faq_id'] . "').click(function () {\n" .
									"    $('#answer_" . $faqs[$faqCounter]['faq_id'] . "').slideToggle('slow');\n" .
									"   });\n";
			}
		}
		else {
			$faqs = retrieveFaqs(null, null);
			
			for ($sectionCounter = 0; $sectionCounter < count($faqs); $sectionCounter++) {
				for ($faqCounter = 0; $faqCounter < count(@$faqs[$sectionCounter]['FAQs']); $faqCounter++) {
					$pageJavaScript .=	"  $('#question_" . $faqs[$sectionCounter]['FAQs'][$faqCounter]['faq_id'] . "').click(function () {\n" .
										"    $('#answer_" . $faqs[$sectionCounter]['FAQs'][$faqCounter]['faq_id'] . "').slideToggle('slow');\n" .
										"   });\n" .
										"  $('#answer_" . $faqs[$sectionCounter]['FAQs'][$faqCounter]['faq_id'] . "').click(function () {\n" .
										"    $('#answer_" . $faqs[$sectionCounter]['FAQs'][$faqCounter]['faq_id'] . "').slideToggle('slow');\n" .
										"   });\n";
				}
			}
		}
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */

	if (count($_POST) > 0) {

		$tl->page['error'] = '';
		
		// clean input
			$_POST = $parser->trimAll($_POST);

		// check for errors
			if (!$input_validator->isStringValid($_POST['keywords'], "abcdefghijklmnopqrstuvwxyz0123456789-' ", '')) $tl->page['error'] .= "Please specify only alphanumeric characters. ";
			
		// redirect URL
			if (!$tl->page['error']) $authentication_manager->forceRedirect('/help/' . urlencode($_POST['keywords']));
	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
				
	else {
	}
	
?>