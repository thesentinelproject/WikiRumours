<?php
	include 'includes/views/desktop/shared/page_top.php';

	if ($pageError) echo "      <div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $pageError . "</div>\n";
	elseif ($pageSuccess == 'preferences_updated') echo "      <div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully updated system preferences.</div>\n";
	elseif ($pageSuccess == 'logo_deleted') echo "      <div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully deleted custom logo.</div>\n";
	elseif ($pageSuccess == 'notification_updated') echo "      <div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully updated notification.</div>\n";
	elseif ($pageSuccess == 'notification_deleted') echo "      <div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully deleted notification.</div>\n";
	elseif ($pageSuccess == 'notification_added') echo "      <div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully added notification.</div>\n";
	elseif ($pageSuccess == 'faq_updated') echo "      <div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully updated FAQ.</div>\n";
	elseif ($pageSuccess == 'faq_deleted') echo "      <div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully deleted FAQ.</div>\n";
	elseif ($pageSuccess == 'faq_added') echo "      <div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully added FAQ.</div>\n";
	elseif ($pageSuccess == 'faq_chapter_updated') echo "      <div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully updated FAQ chapter.</div>\n";
	elseif ($pageSuccess == 'faq_chapter_deleted') echo "      <div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully deleted FAQ chapter.</div>\n";
	elseif ($pageSuccess == 'faq_chapter_added') echo "      <div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully added FAQ chapter.</div>\n";
	
	// misc
		echo "<div class='pageModule'>\n";
		echo "  " . $form->start('editPreferencesForm', '', 'post', 'form-group', array('enctype'=>'multipart/form-data')) . "\n";
		echo "  " . $form->input('hidden', 'logoToDelete') . "\n";
		echo "  <h2>System preferences</h2>\n";
		echo "  <div class='well'>\n";
		echo $form->row('text', 'appName', $operators->firstTrue(@$_POST['appName'], $systemPreferences['appName']), false, 'Website name', 'form-control', null, 255);
		echo $form->row('text', 'appDescription', $operators->firstTrue(@$_POST['appDescription'], $systemPreferences['appDescription']), false, 'Description', 'form-control', null, 255);
		echo $form->rowStart('currentLogo', 'Current logo');
		echo "<div class='rowPadding'><img src='/" . $logo . "' /></div>\n";
		echo $form->rowEnd();
		echo $form->row('file', 'changeLogo', null, false, "Change logo", 'rowPadding');
		echo $form->rowStart('actions');
		echo "  " . $form->input('submit', 'update', null, false, 'Update', 'btn btn-default') . "\n";
		if ($userLogo) echo "  " . $form->input('button', 'delete', null, false, 'Delete current logo', 'btn btn-link', null, null, null, null, array('onClick'=>'validateDeleteLogo(); return false;')) . "\n";
		echo $form->rowEnd();
		echo "  </div>\n";
		echo "  " . $form->end() . "\n";
		echo "</div>\n";
		
	// notifications
		echo "<div class='pageModule'>\n";
		echo "  " . $form->start('editNotificationsForm', '', 'post', null, null, array('onSubmit'=>'return false;')) . "\n";
		echo "  " . $form->input('hidden', 'notificationEmailToUpdate') . "\n";
		echo "  " . $form->input('hidden', 'notificationEmailToDelete') . "\n";
		echo "  " . $form->input('hidden', 'addNotificationEmail') . "\n";
		echo "  <h2>System notifications</h2>\n";
		for ($counter = 0; $counter < count($notifications); $counter++) {
			echo "  <div class='well'>\n";
			echo "    <div class='row'>\n";
			echo "      <div class='col-sm-6'>" . $form->input('email', 'notification_email_' . $notifications[$counter]['notification_id'], $notifications[$counter]['email'], true, 'Email|Email', 'form-control') . "</div>\n";
			echo "      <div class='col-sm-3'>\n";
			echo "        " . $form->input('checkbox', 'new_registrations_' . $notifications[$counter]['notification_id'], $notifications[$counter]['new_registrations'], false, 'New registrations') . "\n";
			echo "        <br />\n";
			echo "        " . $form->input('checkbox', 'contact_form_' . $notifications[$counter]['notification_id'], $notifications[$counter]['contact_form'], false, 'Contact form') . "\n";
			echo "      </div>\n";
			echo "      <div class='col-sm-3'>\n";
			echo "        " . $form->input('button', null, null, false, 'Update', 'btn btn-default btn-sm', null, null, null, null, array('onClick'=>'validateUpdateNotification("' . $notifications[$counter]['notification_id'] . '");')) . "\n";
			echo "        " . $form->input('button', null, null, false, 'Delete', 'btn btn-default btn-sm btn-link', null, null, null, null, array('onClick'=>'validateDeleteNotification("' . $notifications[$counter]['notification_id'] . '");')) . "\n";
			echo "      </div>\n";
			echo "    </div>\n";
			echo "  </div>\n";
		}
		echo "  <div class='well'>\n";
		echo "    <div class='row'>\n";
		echo "      <div class='col-sm-6'>" . $form->input('email', 'notification_email_add', @$_POST['email_add'], true, 'Email|Email', 'form-control') . "</div>\n";
		echo "      <div class='col-sm-3'>\n";
		echo "        " . $form->input('checkbox', 'new_registrations_add', @$_POST['new_registrations_add'], false, 'New registrations') . "\n";
		echo "        <br />\n";
		echo "        " . $form->input('checkbox', 'contact_form_add', @$_POST['contact_form_add'], false, 'Contact form') . "\n";
		echo "      </div>\n";
		echo "      <div class='col-sm-3'>\n";
		echo "        " . $form->input('button', null, null, false, 'Add', 'btn btn-default btn-sm', null, null, null, null, array('onClick'=>'validateAddNotification();')) . "\n";
		echo "      </div>\n";
		echo "    </div>\n";
		echo "  </div>\n";
		echo "  " . $form->end() . "\n";
		echo "</div>\n";
		
	// FAQs
		echo "<div class='pageModule'>\n";
		echo "  " . $form->start('editFaqForm', '', 'post', null, null, array('onSubmit'=>'return false;')) . "\n";
		echo "  " . $form->input('hidden', 'faqToUpdate') . "\n";
		echo "  " . $form->input('hidden', 'faqToDelete') . "\n";
		echo "  " . $form->input('hidden', 'addFaq') . "\n";
		echo "  <h2>FAQs</h2>\n";
		for ($counter = 0; $counter < count($faqs); $counter++) {
			echo "  <div class='well'>\n";
			echo "    <div class='row form-group'>\n";
			echo "      <div class='col-sm-5'>" . $form->input('textarea', 'question_' . $faqs[$counter]['faq_id'], $faqs[$counter]['question'], true, 'Question|Question', 'form-control') . "</div>\n";
			echo "      <div class='col-sm-5'>" . $form->input('textarea', 'answer_' . $faqs[$counter]['faq_id'], $faqs[$counter]['answer'], true, 'Answer|Answer', 'form-control') . "</div>\n";
			echo "      <div class='col-sm-2'>\n";
			echo "        " . $form->input('button', null, null, false, 'Update', 'btn btn-default btn-sm', null, null, null, null, array('onClick'=>'validateUpdateFaq("' . $faqs[$counter]['faq_id'] . '");')) . "\n";
			echo "        " . $form->input('button', null, null, false, 'Delete', 'btn btn-sm btn-link', null, null, null, null, array('onClick'=>'validateDeleteFaq("' . $faqs[$counter]['faq_id'] . '");')) . "\n";
			echo "      </div>\n";
			echo "    </div>\n";
			echo "    <div class='row'>\n";
			echo "      <div class='col-sm-5'>" . $form->input('select', 'chapter_id_' . $faqs[$counter]['faq_id'], $faqs[$counter]['chapter_id'], false, 'Chapter', 'form-control', $allChapters) . "</div>\n";
			echo "      <div class='col-sm-5'>" . $form->input('select', 'faq_position_' . $faqs[$counter]['faq_id'], $faqs[$counter]['faq_position'], false, 'Position', 'form-control', $allPositions) . "</div>\n";
			echo "    </div>\n";
			echo "  </div>\n";
		}
		
		echo "  <div class='well'>\n";
		echo "    <div class='row form-group'>\n";
		echo "      <div class='col-sm-5'>" . $form->input('textarea', 'question_add', null, true, 'Question|Question', 'form-control') . "</div>\n";
		echo "      <div class='col-sm-5'>" . $form->input('textarea', 'answer_add', null, true, 'Answer|Answer', 'form-control') . "</div>\n";
		echo "      <div class='col-sm-2'>\n";
		echo "        " . $form->input('button', null, null, false, 'Add', 'btn btn-default btn-sm', null, null, null, null, array('onClick'=>'validateAddFaq();')) . "\n";
		echo "      </div>\n";
		echo "    </div>\n";
		echo "    <div class='row'>\n";
		echo "      <div class='col-sm-5'>" . $form->input('select', 'chapter_id_add', null, false, 'Chapter', 'form-control', $allChapters) . "</div>\n";
		echo "      <div class='col-sm-5'>" . $form->input('select', 'faq_position_add', null, false, 'Position', 'form-control', $allPositions) . "</div>\n";
		echo "    </div>\n";
		echo "  </div>\n";
		
		echo "  " . $form->end() . "\n";
		echo "</div>\n";
		
	// FAQ chapters
		echo "<div class='pageModule'>\n";
		echo "  " . $form->start('editFaqChapterForm', '', 'post', 'form-inline', null, array('onSubmit'=>'return false;')) . "\n";
		echo "  " . $form->input('hidden', 'faqChapterToUpdate') . "\n";
		echo "  " . $form->input('hidden', 'faqChapterToDelete') . "\n";
		echo "  " . $form->input('hidden', 'addFaqChapter') . "\n";
		echo "  <h2>FAQ chapters</h2>\n";
		for ($counter = 0; $counter < count($faqChapters); $counter++) {
			echo "  <div class='well'>\n";
			echo "    <div class='row'>\n";
			echo "      <div class='col-sm-4'>" . $form->input('select', 'chapter_position_' . $faqChapters[$counter]['chapter_id'], $faqChapters[$counter]['chapter_position'], false, 'Position', 'form-control', $allPositions) . "</div>\n";
			echo "      <div class='col-sm-5'>" . $form->input('text', 'name_' . $faqChapters[$counter]['chapter_id'], $faqChapters[$counter]['name'], true, 'Name|Chapter name', 'form-control') . "</div>\n";
			echo "      <div class='col-sm-3'>\n";
			echo "        " . $form->input('button', null, null, false, 'Update', 'btn btn-default btn-sm', null, null, null, null, array('onClick'=>'validateUpdateFaqChapter("' . $faqChapters[$counter]['chapter_id'] . '");')) . "\n";
			echo "        " . $form->input('button', null, null, false, 'Delete', 'btn btn-sm btn-link', null, null, null, null, array('onClick'=>'validateDeleteFaqChapter("' . $faqChapters[$counter]['chapter_id'] . '");')) . "\n";
			echo "      </div>\n";
			echo "    </div>\n";
			echo "  </div>\n";
		}
		echo "  <div class='well'>\n";
		echo "    <div class='row'>\n";
		echo "      <div class='col-sm-4'>" . $form->input('select', 'chapter_position_add', null, false, 'Position', 'form-control', $allPositions) . "</div>\n";
		echo "      <div class='col-sm-5'>" . $form->input('text', 'name_add', null, true, 'Name|Chapter name', 'form-control') . "</div>\n";
		echo "      <div class='col-sm-3'>\n";
		echo "        " . $form->input('button', null, null, false, 'Add', 'btn btn-default btn-sm', null, null, null, null, array('onClick'=>'validateAddFaqChapter();')) . "\n";
		echo "      </div>\n";
		echo "    </div>\n";
		echo "  </div>\n";
		echo "  " . $form->end() . "\n";
		echo "</div>\n";

	include 'includes/views/desktop/shared/page_bottom.php';
?>