<?php
	include 'includes/views/desktop/shared/page_top.php';

	if ($pageError) echo "<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $pageError . "</div>\n";
	
	echo "    <h2>Edit Rumour</h2>\n";
	
	echo $form->start('editRumourForm', null, 'post', null, null, array('onSubmit'=>'validateEditRumourForm(); return false;')) . "\n";
	echo $form->input('hidden', 'deleteThisRumour') . "\n";

	/* Description */	if ($logged_in['is_administrator'] && $logged_in['can_edit_content']) echo $form->row('textarea', 'description', $operators->firstTrue(@$_POST['description'], @$rumour[0]['description']), true, 'Rumour|Please be as concise as possible', 'form-control', null, null, array('rows'=>'7')) . "\n";
						else echo $form->row('uneditable', 'description', $rumour[0]['description'], false, 'Rumour', 'form-control-static') . "\n";
	/* Priority */		echo $form->row('select', 'priority', $operators->firstTrue(@$_POST['priority'], @$rumour[0]['priority']), true, 'Priority', 'form-control', array(''=>'Please select') + $priorityLevels) . "\n";
	/* Status */		echo $form->row('select', 'status', $operators->firstTrue(@$_POST['status'], @$rumour[0]['status']), true, 'Status', 'form-control', $rumourStatuses) . "\n";
	/* Findings */		echo $form->row('textarea', 'findings', $operators->firstTrue(@$_POST['findings'], @$rumour[0]['findings']), false, 'Findings', 'form-control', null, null, array('rows'=>'5')) . "\n";
	/* Country */		if ($logged_in['is_administrator'] && $logged_in['can_edit_content']) echo $form->row('country', 'country', $operators->firstTrue(@$_POST['country'], @$rumour[0]['country']), false, 'Country', 'form-control') . "\n";
						else echo $form->row('uneditable', 'country_readable', $operators->firstTrue($countries[@$rumour[0]['country']], '-'), false, 'Country where occurred', 'form-control-static') . "\n";
	/* Region */		if ($logged_in['is_administrator'] && $logged_in['can_edit_content']) echo $form->row('text', 'region', $operators->firstTrue(@$_POST['region'], @$rumour[0]['region']), false, 'City / region', 'form-control') . "\n";
						else echo $form->row('uneditable', 'region_readable', $operators->firstTrue(@$rumour[0]['region'], '-'), false, 'Region where occurred', 'form-control-static') . "\n";
	/* Occurred on */	if ($logged_in['is_administrator'] && $logged_in['can_edit_content']) {
							echo "  <div class='formLabel'>Occurred on</div>\n";
							echo "  <div class='formField'>\n";
							echo "    <div id='occurred_on' class='input-group date form_datetime' data-date-format='yyyy-mm-dd hh:ii:ss' data-link-format='yyyy-mm-dd hh:ii:ss' data-link-field='occurred_on'>\n";
							echo "      " . $form->input('text', 'occurred_on', $operators->firstTrue(@$_POST['occurred_on'], @$rumour[0]['occurred_on']), false, null, 'form-control', null, 19) . "\n";
							echo "      <span class='input-group-addon'>&nbsp;<span class='glyphicon glyphicon-calendar'></span></span>\n";
							echo "    </div>\n";
							echo "  </div>\n";
						}
						elseif ($rumour[0]['occurred_on'] != '0000-00-00') echo $form->row('uneditable', 'occurred_on_readable', date('F j, Y', strtotime(@$rumour[0]['occurred_on'])), false, 'Date occurred', 'form-control-static') . "\n";
						else echo $form->row('uneditable', 'occurred_on_readable', '-', false, 'Date occurred', 'form-control-static') . "\n";
	/* On behalf of */	if ($logged_in['is_administrator'] && $logged_in['can_edit_content']) {
							echo "  <div class='formLabel'>Reported on behalf of</div>\n";
							echo "  <div class='formField'>\n";
							echo $form->input('select', 'created_by', $operators->firstTrue(@$_POST['created_by'], @$rumour[0]['created_by_user_id']), false, 'Created by', 'form-control', $allUsers) . "\n";
							echo "    " . $form->input('button', 'add_user', null, false, '...or create new user', 'btn btn-link') . "\n";
							echo "  </div>\n";
							$pageJavaScript .= "// add new user to rumour\n";
							$pageJavaScript .= "  $('#add_user').click(function () {\n";
							$pageJavaScript .= "    if ($('#addNewUserToRumour').is(':hidden')) $('#created_by').val('');\n";
							$pageJavaScript .= "    $('#addNewUserToRumour').slideToggle();\n";
							$pageJavaScript .= "  });\n\n";
							echo "  <div class='floatClear'></div>\n";
							echo "  <div id='addNewUserToRumour'>\n";
							echo "    <div id='addNewUserToRumourBorder'>\n";
							/* Name */			echo "<!-- Name -->\n";
												echo "  <div class='formLabel'>&nbsp; Name</div>\n";
												echo "  <div class='formField row'>\n";
												echo "    <div class='col-md-6'>" . $form->input('text', 'newuser_first_name', @$_POST['newuser_first_name'], false, '|First', 'form-control', '', 30) . "</div>\n";
												echo "    <div class='col-md-6'>" . $form->input('text', 'newuser_last_name', @$_POST['newuser_last_name'], false, '|Last', 'form-control', '', 30) . "</div>\n";
												echo "  </div>\n";
												echo "  <div class='floatClear'></div>\n";
							/* Email */			echo $form->row('email', 'newuser_email', @$_POST['newuser_email'], false, '&nbsp; Email', 'form-control', '', 100);
							/* Phone */			echo $form->row('tel', 'newuser_phone', @$_POST['newuser_phone'], false, '&nbsp; Phone', 'form-control', '', 12);
							/* 2nd Phone */		echo $form->row('tel', 'newuser_secondary_phone', @$_POST['newuser_secondary_phone'], false, '&nbsp; Secondary phone', 'form-control', '', 12);
							/* SMS */			echo $form->row('tel', 'newuser_sms_notifications', @$_POST['newuser_sms_notifications'], false, '&nbsp; SMS notifications', 'form-control', '', 12);
							/* Country */		echo $form->row('country', 'newuser_country', @$_POST['newuser_country'], false, "&nbsp; Country", 'form-control') . "\n";
							/* Anonymized */	echo $form->row('yesno_bootstrap_switch', 'newuser_ok_to_contact', @$_POST['newuser_ok_to_contact'], false, '&nbsp; OK to contact?');
												echo $form->row('yesno_bootstrap_switch', 'newuser_ok_to_show_profile', @$_POST['newuser_ok_to_show_profile'], false, '&nbsp; OK to show profile?');
							echo "    </div>\n";
							echo "  </div>\n";
						}
						else echo $form->row('uneditable', 'created_by_readable', @$rumour[0]['created_by_full_name'], false, 'Reported by', 'form-control-static') . "\n";
	/* Assigned to */	echo $form->row('select', 'assigned_to', $operators->firstTrue(@$_POST['assigned_to'], @$rumour[0]['assigned_to_user_id']), false, 'Assigned to', 'form-control', $allModeratorsAndCommunityLiaisons) . "\n";
						
	/* Actions */		echo "<!-- Actions -->\n";
						echo "  <div class='formLabel'></div>\n";
						echo "  <div class='formField'>\n";
						echo "    " . $form->input('submit', null, null, false, 'Update this rumour', 'btn btn-info') . "\n";
						if ($logged_in['is_administrator'] && $logged_in['can_edit_content']) echo "    " . $form->input('button', null, null, false, 'Delete this rumour', 'btn btn-link', null, null, null, null, array('onClick'=>'deleteRumour(); return false;')) . "\n";
						echo "    " . $form->input('cancel_and_return', null, null, false, null, 'btn btn-link') . "\n";
						echo "  </div>\n";
						echo "  <div class='floatClear'></div>\n";
						
	echo $form->end() . "\n";

	include 'includes/views/desktop/shared/page_bottom.php';
?>