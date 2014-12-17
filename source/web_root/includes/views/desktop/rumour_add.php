<?php
	include 'includes/views/desktop/shared/page_top.php';

	if ($pageError) echo "<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $pageError . "</div>\n";
	
	echo $form->start('addRumourForm', null, 'post', null, null, array('onSubmit'=>'validateAddRumourForm(); return false;')) . "\n";
	echo $form->input('hidden', 'step', $step) . "\n";
	echo $form->input('hidden', 'rumour_id', $matchingRumour) . "\n";
	
	if ($step == 1) {
		
			echo "<h2>Report a rumour</h2>\n";
			echo "<div class='row form-group'>\n";
			/* Description */	echo "  <div class='col-md-12'>" . $form->input('textarea', 'description', @$_POST['description'], true, 'Rumour|Please be as concise as possible', 'form-control', null, null, array('rows'=>'10')) . "</div>\n";
			echo "</div>\n";
			echo "<div class='row form-group'>\n";
			/* Country */		echo "  <div class='col-md-6 col-xs-9'>" . $form->input('country', 'country_occurred', @$_POST['country_occurred'], false, 'In which country did this occur?', 'form-control') . "</div>\n";
			/* Actions */		echo "  <div class='col-md-3 col-md-offset-3 col-xs-3 text-right'>" . $form->input('submit', 'add_rumour', null, false, 'Continue', 'btn btn-info btn-block') . "</div>\n";
			echo "</div>\n";
			
	}
	elseif ($step == 2) {
		
			echo "<h2>Do you see your rumour here?</h2>\n";
			for ($counter = 0; $counter < count($matches); $counter++) {
				echo "<div class='matchingRumourList'>\n";
				echo "<p>" . $matches[$counter]['description'] . "</p>\n";
				echo "<p><button class='btn btn-default' onClick='matchRumour(" . '"' . $matches[$counter]['public_id'] . '"' . "); return false;'><span class='glyphicon glyphicon-ok'></span> This is the same rumour</button></p>\n";
				echo "</div>\n";
			}
			echo $form->input('hidden', 'description', htmlspecialchars($_POST['description'], ENT_QUOTES)) . "\n";
			echo $form->input('hidden', 'country_occurred', $_POST['country_occurred']) . "\n";
			echo $form->input('submit', 'new_rumour', null, false, "My rumour isn't listed here", 'btn btn-info') . "\n";
			echo $form->input('button', 'search_again', null, false, 'Start over', 'btn btn-link', null, null, null, null, array('onClick'=>'document.location.href = "/rumour_add"; return false;')) . "</p>\n";
			
	}
	elseif ($step == 3) {
		
			echo "<h2>Almost done reporting this rumour...</h2>\n";
			if ($matchingRumour) {
				/* Description */		echo $form->row('uneditable', 'description', @$rumour[0]['description'], false, 'Rumour') . "\n";
				/* Country occurred */	echo $form->row('uneditable', 'country_readable', $countries[@$rumour[0]['country']], false, 'Country where occurred') . "\n";
										echo $form->input('hidden', 'country_occurred', @$rumour[0]['country']) . "\n";
				/* Region occurred */	echo $form->row('uneditable', 'region_readable', @$rumour[0]['region'], false, 'City/region where occurred') . "\n";
										echo $form->input('hidden', 'region_occurred', @$rumour[0]['region']) . "\n";
				/* Date occurred */		echo $form->row('uneditable', 'occurred_on_readable', date('F j, Y', strtotime(@$rumour[0]['occurred_on'])), false, 'Date occurred') . "\n";
										echo $form->input('hidden', 'occurred_on', @$rumour[0]['occurred_on']) . "\n";
				/* Country heard */		echo $form->row('country', 'country_heard', @$_POST['country_heard'], false, 'Country where heard', 'form-control') . "\n";
				/* Region heard */		echo $form->row('text', 'region_heard', @$_POST['region_heard'], false, 'City/region where heard', 'form-control') . "\n";
				/* Date heard */		echo "  <div class='formLabel'>Occurred on</div>\n";
										echo "  <div class='formField'>\n";
										echo "    <div id='heard_on' class='input-group date form_datetime' data-date-format='yyyy-mm-dd hh:ii:ss' data-link-format='yyyy-mm-dd hh:ii:ss' data-link-field='heard_on'>\n";
										echo "      " . $form->input('text', 'heard_on', @$_POST['heard_on'], false, null, 'form-control', null, 19) . "\n";
										echo "      <span class='input-group-addon'>&nbsp;<span class='glyphicon glyphicon-calendar'></span></span>\n";
										echo "    </div>\n";
										echo "  </div>\n";
			}
			else {
				/* Description */		echo $form->row('textarea', 'description', @$_POST['description'], true, 'Rumour|Please be as concise as possible', 'form-control', null, null, array('rows'=>'7')) . "\n";
				/* Country occurred */	echo $form->row('country', 'country_occurred', @$_POST['country_occurred'], false, 'Country where occurred', 'form-control') . "\n";
				/* Region occurred */	echo $form->row('text', 'region_occurred', @$_POST['region_occurred'], false, 'City/region where occurred', 'form-control') . "\n";
				/* Date occurred */		echo "  <div class='formLabel'>Occurred on</div>\n";
										echo "  <div class='formField'>\n";
										echo "    <div id='occurred_on' class='input-group date form_datetime' data-date-format='yyyy-mm-dd hh:ii:ss' data-link-format='yyyy-mm-dd hh:ii:ss' data-link-field='occurred_on'>\n";
										echo "      " . $form->input('text', 'occurred_on', @$_POST['occurred_on'], false, null, 'form-control', null, 19) . "\n";
										echo "      <span class='input-group-addon'>&nbsp;<span class='glyphicon glyphicon-calendar'></span></span>\n";
										echo "    </div>\n";
										echo "  </div>\n";
				/* Country heard */		echo $form->row('country', 'country_heard', @$_POST['country_heard'], false, 'Country where heard', 'form-control') . "\n";
				/* Region heard */		echo $form->row('text', 'region_heard', @$_POST['region_heard'], false, 'City/region where heard', 'form-control') . "\n";
				/* Date heard */		echo "  <div class='formLabel'>Occurred on</div>\n";
										echo "  <div class='formField'>\n";
										echo "    <div id='heard_on' class='input-group date form_datetime' data-date-format='yyyy-mm-dd hh:ii:ss' data-link-format='yyyy-mm-dd hh:ii:ss' data-link-field='heard_on'>\n";
										echo "      " . $form->input('text', 'heard_on', @$_POST['heard_on'], false, null, 'form-control', null, 19) . "\n";
										echo "      <span class='input-group-addon'>&nbsp;<span class='glyphicon glyphicon-calendar'></span></span>\n";
										echo "    </div>\n";
										echo "  </div>\n";
				/* Tags */				if (count(@$allTags) > 0) {
											echo "  <div class='formLabel'>Tags</div>\n";
											echo "  <div class='formField'>\n";
											echo "    <select id='tags' name='tags[]' class='form-control' multiple>\n";
											foreach ($allTags as $id=>$tag) {
												echo "      <option value='" . $id . "'";
												if (@$suggestedTags[$id]) echo " selected";
												echo ">" . $tag . "</option>\n";
											}
											echo "      <option value=''>--</option>\n";
											echo "      <option value=''>None of the above</option>\n";
											echo "    </select>\n";
											echo "  </div>\n";
											echo "  <div class='floatClear'></div>\n";
											echo $form->row('text', 'additional_tags', @$_POST['additional_tags'], false, '|Additional tags (separate with spaces)', 'form-control') . "\n";
										}
										else {
											echo $form->row('text', 'additional_tags', @$_POST['additional_tags'], false, 'Tags|Separate with spaces', 'form-control') . "\n";
										}
			}
						
			if ($logged_in['is_proxy']) {
				/* On behalf of */		echo "  <div class='formLabel'>Reported on behalf of</div>\n";
										echo "  <div class='formField'>\n";
										echo $form->input('select', 'created_by', $operators->firstTrue(@$_POST['created_by'], $logged_in['user_id']), false, 'Created by', 'form-control', $allUsers) . "\n";
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
				/* Source */			echo $form->row('select', 'source', @$_POST['source'], false, 'Reported via', 'form-control', $externalRumourSources) . "\n";
			}
			else {
				/* Source */			echo $form->input('hidden', 'source', 'w') . "\n";
			}
		
		/* Actions */		if ($matchingRumour) echo $form->row('submit', 'add_rumour', 'Report hearing this rumour', false, false, 'btn btn-medium btn-inverse') . "\n";
							else echo $form->row('submit', 'add_rumour', 'Report this rumour', false, false, 'btn btn-medium btn-info') . "\n";
		
	}
	
	echo $form->end() . "\n";

	include 'includes/views/desktop/shared/page_bottom.php';
?>