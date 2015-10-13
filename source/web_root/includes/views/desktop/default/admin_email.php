<?php

	// notifications
		echo "<div class='pageModule'>\n";
		echo "  <h2>Email notifications</h2>\n";
		echo "  " . $form->start('editNotificationsForm', '', 'post') . "\n";
		echo "  " . $form->input('hidden', 'notificationEmailToDelete') . "\n";

		for ($counter = 0; $counter < count($notifications); $counter++) {
			echo $form->rowStart('recipient', 'Recipient');
			echo "  <div class='row'>\n";
			echo "    <div class='col-lg-5 col-md-5 col-sm-5 col-xs-5'>\n";
			echo "      " . $form->input('text', 'recipient_name_' . $notifications[$counter]['notification_id'], $operators->firstTrue(@$_POST['recipient_name_' . $notifications[$counter]['notification_id']], @$notifications[$counter]['recipient_name']), true, '|Name', 'form-control') . "\n";
			echo "    </div>\n";
			echo "    <div class='col-lg-5 col-md-5 col-sm-5 col-xs-5'>\n";
			echo "      " . $form->input('email', 'recipient_email_' . $notifications[$counter]['notification_id'], $operators->firstTrue(@$_POST['recipient_email_' . $notifications[$counter]['notification_id']], @$notifications[$counter]['recipient_email']), true, '|Email', 'form-control') . "\n";
			echo "    </div>\n";
			echo "    <div class='col-lg-2 col-md-2 col-sm-2 col-xs-2'>\n";
			echo "      " . $form->input('button', null, null, false, 'Delete', 'btn btn-link', null, null, null, null, array('onClick'=>'validateDeleteNotification("' . $notifications[$counter]['notification_id'] . '"); return false;')) . "\n";
			echo "    </div>\n";
			echo "  </div>\n";
			echo $form->rowEnd();
			echo $form->row('yesno_bootstrap_switch', 'new_registrations_' . $notifications[$counter]['notification_id'], $notifications[$counter]['new_registrations'], false, 'New registrations', null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default'));
			echo $form->row('yesno_bootstrap_switch', 'contact_form_' . $notifications[$counter]['notification_id'], $notifications[$counter]['contact_form'], false, 'Contact form', null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default'));
			echo "  <hr />";
		}

		echo $form->rowStart('recipient', 'Recipient');
		echo "  <div class='row'>\n";
		echo "    <div class='col-lg-5 col-md-5 col-sm-5 col-xs-5'>\n";
		echo "      " . $form->input('text', 'recipient_name_add', @$_POST['recipient_name_add'], false, '|Name', 'form-control') . "\n";
		echo "    </div>\n";
		echo "    <div class='col-lg-5 col-md-5 col-sm-5 col-xs-5'>\n";
		echo "      " . $form->input('email', 'recipient_email_add', @$_POST['recipient_email_add'], false, '|Email', 'form-control') . "\n";
		echo "    </div>\n";
		echo "  </div>\n";
		echo $form->rowEnd();
		echo $form->row('yesno_bootstrap_switch', 'new_registrations_add', null, false, 'New registrations', null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default'));
		echo $form->row('yesno_bootstrap_switch', 'contact_form_add', null, false, 'Contact form', null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default'));

		echo $form->rowStart('actions');
		echo "  " . $form->input('submit', null, null, false, 'Save', 'btn btn-info') . "\n";
		echo $form->rowEnd();

		echo "  " . $form->end() . "\n";
		echo "</div>\n";
		
	// send email
		if ($logged_in['can_send_email']) {
			echo "<div class='pageModule'>\n";
			echo "  <h2>Send email from " . $systemPreferences['Name of this application'] . "</h2>\n";
			echo "  " . $form->start('emailUserForm', null, 'post', null, null, array('onSubmit'=>'validateEmailUserForm(); return false;')) . "\n";
			/* From */		echo $form->row('uneditable_static', 'from', $systemPreferences['Name of this application'] . " <" . $mail_TL['OutgoingAddress'] . ">", false, 'From') . "\n";
			/* To */		echo $form->rowStart('recipient', 'To');
							echo "  <div class='row'>\n";
							echo "    <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6'>\n";
							echo "  " . $form->input('text', 'name', @$_POST['name'], true, '|Name', 'form-control') . "\n";
							echo "    </div>\n";
							echo "    <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6'>\n";
							echo "  " . $form->input('email', 'email', @$_POST['email'], true, '|Email', 'form-control') . "\n";
							echo "    </div>\n";
							echo "  </div>\n";
							echo $form->rowEnd();
			/* Reply to */	echo $form->rowStart('replyTo', 'Reply to');
							echo "  <div class='row'>\n";
							echo "    <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6'>\n";
							echo "  " . $form->input('text', 'reply_to_name', $operators->firstTrue(@$_POST['reply_to_name'], $logged_in['full__name']), false, '|Name', 'form-control') . "\n";
							echo "    </div>\n";
							echo "    <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6'>\n";
							echo "  " . $form->input('email', 'reply_to_email', $operators->firstTrue(@$_POST['reply_to_email'], $logged_in['email']), false, '|Email', 'form-control') . "\n";
							echo "    </div>\n";
							echo "  </div>\n";
							echo $form->rowEnd();
			/* Subject */	echo $form->row('text', 'subject', $operators->firstTrue(@$_POST['subject'], '[' . $systemPreferences['Name of this application'] . '] '), true, 'Subject', 'form-control') . "\n";
			/* Message */	echo $form->row('textarea', 'message', @$_POST['message'], true, 'Message', 'form-control', null, null, null, null, array('rows'=>'5')) . "\n";
			/* Actions */	echo $form->row('submit', 'Send', null, false, 'Send', 'btn btn-info') . "\n";
			echo "  " . $form->end() . "\n";
			echo "</div>\n";
		}
	
?>