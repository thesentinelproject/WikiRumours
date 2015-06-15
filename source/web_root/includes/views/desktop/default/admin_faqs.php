<?php
	$pageTitle = "FAQs";
	$sectionTitle = "Administration";
	include 'includes/views/desktop/shared/page_top.php';

	// FAQs
		echo "<div class='pageModule'>\n";
		echo "  <h2>FAQs</h2>\n";
		echo "  " . $form->start('editFaqForm', '', 'post', null, null, array('onClick'=>'validateUpdateFaqs(); return false;')) . "\n";
		echo "  " . $form->input('hidden', 'faqToDelete') . "\n";

		for ($counter = 0; $counter < count($faqs); $counter++) {
			echo "  <div class='form-group'>\n";
			echo "    <div class='col-lg-8 col-md-8 col-sm-8 col-xs-8'>" . $form->input('select', 'section_id_' . $faqs[$counter]['faq_id'], $operators->firstTrue(@$_POST['section_id_' . $faqs[$counter]['faq_id']], $faqs[$counter]['section_id']), false, '|Section', 'form-control', $allSections) . "</div>\n";
			echo "    <div class='col-lg-2 col-md-2 col-sm-2 col-xs-2'>" . $form->input('number', 'position_' . $faqs[$counter]['faq_id'], $operators->firstTrue(@$_POST['position_' . $faqs[$counter]['faq_id']], $faqs[$counter]['position']), false, '|#', 'form-control') . "</div>\n";
			echo "    <div class='col-lg-2 col-md-2 col-sm-2 col-xs-2'>" . $form->input('button', null, null, false, 'Delete', 'btn btn-link', null, null, null, null, array('onClick'=>'validateDeleteFaq("' . $faqs[$counter]['faq_id'] . '");')) . "</div>\n";
			echo "  </div>\n";
			echo "  <div class='form-group'>\n";
			echo "    <div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>" . $form->input('text', 'question_' . $faqs[$counter]['faq_id'], $operators->firstTrue(@$_POST['question_' . $faqs[$counter]['faq_id']], $faqs[$counter]['question']), true, '|Question', 'form-control', null, 255) . "</div>\n";
			echo "  </div>\n";
			echo "  <div class='form-group'>\n";
			echo "    <div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>" . $form->input('textarea', 'answer_' . $faqs[$counter]['faq_id'], $operators->firstTrue(@$_POST['answer_' . $faqs[$counter]['faq_id']], $faqs[$counter]['answer']), true, '|Answer', 'form-control', null, null, array('rows'=>'5')) . "</div>\n";
			echo "  </div>\n";
			echo "  <hr />";
		}
		echo "  <div class='form-group'>\n";
		echo "    <div class='col-lg-8 col-md-8 col-sm-8 col-xs-8'>" . $form->input('select', 'section_id_add', @$_POST['section_id_add'], false, '|Section', 'form-control', $allSections) . "</div>\n";
		echo "    <div class='col-lg-4 col-md-4 col-sm-4 col-xs-4'>" . $form->input('number', 'position_add', @$_POST['position_add'], false, '|#', 'form-control') . "</div>\n";
		echo "  </div>\n";
		echo "  <div class='form-group'>\n";
		echo "    <div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>" . $form->input('text', 'question_add', @$_POST['question_add'], false, '|Question', 'form-control', null, 255) . "</div>\n";
		echo "  </div>\n";
		echo "  <div class='form-group'>\n";
		echo "    <div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>" . $form->input('textarea', 'answer_add', @$_POST['answer_add'], false, '|Answer', 'form-control', null, null, array('rows'=>'5')) . "</div>\n";
		echo "  </div>\n";
		echo "  <div class='form-group'>\n";
		echo "    <div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>" . $form->input('submit', 'submit_button', null, false, 'Save', 'btn btn-info') . "</div>\n";
		echo "  </div>\n";

		echo "  " . $form->end() . "\n";
		echo "</div>\n";

	// FAQ sections	
		echo "<div class='pageModule'>\n";
		echo "  <h2>FAQ Sections</h2>\n";
		echo "  " . $form->start('editFaqSectionForm', '', 'post') . "\n";
		echo "  " . $form->input('hidden', 'faqSectionToDelete') . "\n";
		for ($counter = 0; $counter < count($faqSections); $counter++) {
			echo "  <div class='form-group'>\n";
			echo "    <div class='col-lg-2 col-md-2 col-sm-2 col-xs-2'>\n";
			echo "      " . $form->input('number', 'position_' . $faqSections[$counter]['section_id'], $operators->firstTrue(@$_POST['position_' . $faqSections[$counter]['section_id']], $faqSections[$counter]['position']), false, '|#', 'form-control') . "\n";
			echo "    </div>\n";
			echo "    <div class='col-lg-8 col-md-8 col-sm-8 col-xs-8'>\n";
			echo "      " . $form->input('text', 'name_' . $faqSections[$counter]['section_id'], $operators->firstTrue(@$_POST['name_' . $faqSections[$counter]['section_id']], $faqSections[$counter]['name']), true, '|Section name', 'form-control', null, 100) . "\n";
			echo "    </div>\n";
			echo "    <div class='col-lg-2 col-md-2 col-sm-2 col-xs-2'>\n";
			echo "      " . $form->input('button', null, null, false, 'Delete', 'btn btn-link', null, null, null, null, array('onClick'=>'validateDeleteFaqSection("' . $faqSections[$counter]['section_id'] . '");')) . "\n";
			echo "    </div>\n";
			echo "  </div>\n";
			echo "  <hr />";
		}
		echo "  <div class='form-group'>\n";
		echo "    <div class='col-lg-2 col-md-2 col-sm-2 col-xs-2'>\n";
		echo "      " . $form->input('number', 'position_add', @$_POST['position_add'], false, '|#', 'form-control') . "\n";
		echo "    </div>\n";
		echo "    <div class='col-lg-10 col-md-10 col-sm-10 col-xs-10'>\n";
		echo "      " . $form->input('text', 'name_add', @$_POST['name_add'], false, '|Section name', 'form-control', null, 100) . "\n";
		echo "    </div>\n";
		echo "  </div>\n";
		echo "  <div class='form-group'>\n";
		echo "    <div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>" . $form->input('submit', 'submit_button', null, false, 'Save', 'btn btn-info') . "</div>\n";
		echo "  </div>\n";

		echo "  " . $form->end() . "\n";
		echo "</div>\n";
	
	include 'includes/views/desktop/shared/page_bottom.php';
?>