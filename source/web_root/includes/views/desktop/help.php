<?php
	$pageTitle = "Help";
	include 'includes/views/desktop/shared/page_top.php';

	if ($pageError) echo "<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $pageError . "</div>\n";

	echo "<div class='container'>\n";
	echo "  <div class='row'>\n";
	echo "    <div class='col-md-9'>\n";
	echo "      <h2>FAQs</h2>\n";
	
	if (!count($faqs)) echo "      No FAQs to display.\n";
	else {

		if (@$otherCriteria) {

			echo "      <div class='faqChapter'>\n";
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
		
			for ($chapterCounter = 0; $chapterCounter < count($faqs); $chapterCounter++) {
				echo "      <div class='faqChapter'>\n";
				if (@$faqs[$chapterCounter]['chapter_name']) echo "        <strong>" . $faqs[$chapterCounter]['chapter_name'] . "</strong><br />\n";
				for ($faqCounter = 0; $faqCounter < count(@$faqs[$chapterCounter]['FAQs']); $faqCounter++) {
					echo "        <div id='question_" . $faqs[$chapterCounter]['FAQs'][$faqCounter]['faq_id'] . "' class='faqQuestion'>\n";
					echo "          <a href='javascript:void(0)' onClick='return false'>" . $faqs[$chapterCounter]['FAQs'][$faqCounter]['question'] . "</a>\n";
					echo "        </div>\n";
					echo "        <div id='answer_" . $faqs[$chapterCounter]['FAQs'][$faqCounter]['faq_id'] . "' class='faqAnswer'>\n";
					echo "          " . nl2br($faqs[$chapterCounter]['FAQs'][$faqCounter]['answer']) . "\n";
					echo "        </div>\n";
				}
				echo "      </div>\n";
			}
			
		}
		
	}
	
	echo "      <p>\n";
	echo "        " . $form->start('searchFaqForm', null, 'post', 'form-inline', null, array('onSubmit'=>'validateSearchFaqForm(); return false;')) . "\n";
	/* Keywords */		echo "        <div class='form-group'>" . $form->input('search', 'keywords', $keywords, true, null, 'form-control') . "</div>\n";
	/* Actions */		echo "        <div class='form-group'>\n";
						echo "          " . $form->input('submit', 'search', null, false, 'Seach', 'btn btn-info') . "\n";
						echo "          " . $form->input('button', 'reset', null, false, 'Reset', 'btn btn-link', null, null, null, null, array('onClick'=>'document.location.href="/help"')) . "\n";
						echo "          " . $form->input('button', 'contact', null, false, 'Still need help?', 'btn btn-link', null, null, null, null, array('onClick'=>'document.location.href="/contact"')) . "\n";
						echo "        </div>\n";
	echo "        " . $form->end() . "\n";
	echo "      </p>\n";
	
	echo "    </div>\n";
	echo "    <div class='md-col-3'>\n";
	echo "      <h2>Videos</h2>\n";
	echo "      <iframe width='160' height='90' src='//www.youtube.com/embed/UQIFFHa7l_U' frameborder='0' allowfullscreen></iframe>\n";
	echo "      <div><small><a href='http://www.youtube.com/embed/UQIFFHa7l_U' target='_blank'>Introduction to WikiRumours</a></small></div>\n";
	echo "    </div>\n";
	echo "  </div>\n";
	echo "</div>\n";
	
	include 'includes/views/desktop/shared/page_bottom.php';
?>
