<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$keywords = trim(urldecode($parameter1));

	// queries
		if ($keywords) {
			$otherCriteria = '';
			$keywordExplode = explode(' ', $keywords);
			foreach ($keywordExplode as $keyword) {
				if ($keyword) {
					if ($otherCriteria) $otherCriteria .= " AND";
					$otherCriteria .= " (faqs.question LIKE '%" . addSlashes($keyword) . "%' OR faqs.answer LIKE '%" . addSlashes($keyword) . "%')";
				}
			}
			$otherCriteria = trim($otherCriteria);
		}
		
		if (@$otherCriteria) {
			$faqs = retrieveFaqs(null, null, $otherCriteria, 'faq_chapters.chapter_position ASC, faqs.faq_position ASC');
			
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
			
			for ($chapterCounter = 0; $chapterCounter < count($faqs); $chapterCounter++) {
				for ($faqCounter = 0; $faqCounter < count(@$faqs[$chapterCounter]['FAQs']); $faqCounter++) {
					$pageJavaScript .=	"  $('#question_" . $faqs[$chapterCounter]['FAQs'][$faqCounter]['faq_id'] . "').click(function () {\n" .
										"    $('#answer_" . $faqs[$chapterCounter]['FAQs'][$faqCounter]['faq_id'] . "').slideToggle('slow');\n" .
										"   });\n" .
										"  $('#answer_" . $faqs[$chapterCounter]['FAQs'][$faqCounter]['faq_id'] . "').click(function () {\n" .
										"    $('#answer_" . $faqs[$chapterCounter]['FAQs'][$faqCounter]['faq_id'] . "').slideToggle('slow');\n" .
										"   });\n";
				}
			}
		}
		
	// instantiate required class(es)
		$validator = new inputValidator_TL();
		$parser = new parser_TL();
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */

	if (count($_POST) > 0) {

		$pageError = '';
		
		// clean input
			$_POST = $parser->trimAll($_POST);

		// check for errors
			if (!$validator->isStringValid($_POST['keywords'], "abcdefghijklmnopqrstuvwxyz0123456789-' ", '')) $pageError .= "Please specify only alphanumeric characters. ";
			
		// redirect URL
			if (!$pageError) {
				header('Location: /faqs/' . urlencode($_POST['keywords']));
				exit();
			}
	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
				
	else {
	}
	
?>