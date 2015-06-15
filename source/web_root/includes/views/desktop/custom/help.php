<?php
	$pageTitle = "Help";
	include 'includes/views/desktop/shared/page_top.php';

//	echo "<div class='container'>\n";
	echo "  <div class='row'>\n";
	echo "    <div class='col-lg-9 col-md-6 col-sm-6 col-xs-12'>\n";
	echo "      <h2>FAQs</h2>\n";
	
	if (!count($faqs)) echo "      No FAQs to display.\n";
	else {

		if (@$otherCriteria) {

			echo "      <div class='faqSection'>\n";
			echo "        <strong>Search results</strong><br />\n";
			for ($faqCounter = 0; $faqCounter < count($faqs); $faqCounter++) {
				echo "        <div id='question_" . $faqs[$faqCounter]['faq_id'] . "' class='faqQuestion'>\n";
				echo "          <a href='javascript:void(0)' onClick='return false'>" . $faqs[$faqCounter]['question'] . "</a>\n";
				echo "        </div>\n";
				echo "        <div id='answer_" . $faqs[$faqCounter]['faq_id'] . "' class='faqAnswer'>\n";
				echo "          " . nl2br($faqs[$faqCounter]['answer']) . "\n";
				echo "        </div>\n";
			}
			echo "      </div>\n";
			
		}
		else {
		
			for ($sectionCounter = 0; $sectionCounter < count($faqs); $sectionCounter++) {
				echo "      <div class='faqSection'>\n";
				if (@$faqs[$sectionCounter]['section_name']) echo "        <strong>" . $faqs[$sectionCounter]['section_name'] . "</strong><br />\n";
				for ($faqCounter = 0; $faqCounter < count(@$faqs[$sectionCounter]['FAQs']); $faqCounter++) {
					echo "        <div id='question_" . $faqs[$sectionCounter]['FAQs'][$faqCounter]['faq_id'] . "' class='faqQuestion'>\n";
					echo "          <a href='javascript:void(0)' onClick='return false'>" . $faqs[$sectionCounter]['FAQs'][$faqCounter]['question'] . "</a>\n";
					echo "        </div>\n";
					echo "        <div id='answer_" . $faqs[$sectionCounter]['FAQs'][$faqCounter]['faq_id'] . "' class='faqAnswer'>\n";
					echo "          " . nl2br($faqs[$sectionCounter]['FAQs'][$faqCounter]['answer']) . "\n";
					echo "        </div>\n";
				}
				echo "      </div>\n";
			}
			
		}
		
	}
	
	echo "      <p>\n";
	echo "        " . $form->start('searchFaqForm', null, 'post', null, null, array('onSubmit'=>'validateSearchFaqForm(); return false;')) . "\n";
	/* Keywords */		echo "        <div class='form-group'><div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>" . $form->input('search', 'keywords', $keywords, true, null, 'form-control') . "</div></div>\n";
	/* Actions */		echo "        <div class='form-group'><div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>\n";
						echo "          " . $form->input('submit', 'search', null, false, 'Seach', 'btn btn-info') . "\n";
						echo "          " . $form->input('button', 'reset', null, false, 'Reset', 'btn btn-link', null, null, null, null, array('onClick'=>'document.location.href="/help"')) . "\n";
						echo "          " . $form->input('button', 'contact', null, false, 'Still need help?', 'btn btn-link', null, null, null, null, array('onClick'=>'document.location.href="/contact"')) . "\n";
						echo "        </div></div>\n";
	echo "        " . $form->end() . "\n";
	echo "      </p>\n";
	
	echo "    </div>\n";
	echo "    <div class='col-lg-3 col-md-6 col-sm-6 col-xs-12'>\n";
	echo "      <h2>Videos</h2>\n";
	echo "      <iframe width='100%' height='200' src='//www.youtube.com/embed/UQIFFHa7l_U' frameborder='0' allowfullscreen></iframe>\n";
	echo "    </div>\n";
	echo "  </div>\n";
//	echo "</div>\n";
	
	include 'includes/views/desktop/shared/page_bottom.php';
?>
