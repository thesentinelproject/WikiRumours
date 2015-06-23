<?php

	echo $form->start('addEditRumourForm', null, 'post', null, array('enctype'=>'multipart/form-data'), array('onSubmit'=>'validateAddEditRumourForm(); return false;')) . "\n";
	echo $form->input('hidden', 'step', @$step) . "\n";
	echo $form->input('hidden', 'rumour_id', @$matchingRumour) . "\n";
	echo $form->input('hidden', 'deleteThisPhoto') . "\n";
	echo $form->input('hidden', 'deleteThisRumour') . "\n";
	echo $form->input('hidden', 'templateName', $templateName) . "\n";

	/* Description */			if (($templateName == 'rumour_add' && !$matchingRumour) || ($templateName == 'rumour_edit' && $logged_in['is_administrator'] && $logged_in['can_edit_content'])) echo $form->row('textarea', 'description', $operators->firstTrue(@$_POST['description'], @$rumour[0]['description']), true, 'Rumour|Please be as concise as possible', 'form-control', null, null, array('rows'=>'7'));
								else echo $form->row('uneditable_static', 'description', "<a href='/rumour/" . $rumour[0]['public_id'] . "/" . $parser->seoFriendlySuffix($rumour[0]['description']) . "' target='_blank'>" . $rumour[0]['description'] . "</a>", false, 'Rumour');

	if ($templateName == 'rumour_edit') {

		/* The following fields are editable by anyone who can see this page
		   (admins, moderators, community liaisons) */

		/* Enabled */		echo $form->row('yesno_bootstrap_switch', 'enabled', $operators->firstTrueStrict(@$_POST['enabled'], @$rumour[0]['enabled']), false, "<a href='javascript:void(0)' class='tooltips' onClick='return false' data-toggle='tooltip' title='Disabling this rumour hides it from public access, acting as a form of soft delete.'>Enabled</a>?", null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default'));
		/* Status */		echo $form->row('select', 'status_id', $operators->firstTrue(@$_POST['status_id'], @$rumour[0]['status_id']), true, 'Status', 'form-control', $rumourStatuses);
		/* Findings */		echo $form->row('textarea', 'findings', $operators->firstTrue(@$_POST['findings'], @$rumour[0]['findings']), false, 'Findings', 'form-control', null, null, array('rows'=>'5'));
		/* Priority */		echo $form->row('select', 'priority_id', $operators->firstTrue(@$_POST['priority_id'], @$rumour[0]['priority_id']), true, 'Priority', 'form-control', $rumourPriorities);
		/* Assigned to */	echo $form->row('select', 'assigned_to', $operators->firstTrue(@$_POST['assigned_to'], @$rumour[0]['assigned_to']), false, 'Assigned to', 'form-control', $allModeratorsAndCommunityLiaisons);
		/* Verified with */	echo $form->row('text', 'verified_with', $operators->firstTrue(@$_POST['verified_with'], @$rumour[0]['verified_with']), false, "<a href='javascript:void(0)' class='tooltips' onClick='return false' data-toggle='tooltip' title='Information entered in this field will never be visible to anyone other than moderators, community liaisons and administrators.'>Verified with</a>", 'form-control', null, 255);
		/* Photo */			echo $form->rowStart('file', 'Photographic evidence');
							if (@$photoEvidence) echo "<div><img src='/" . $photoEvidence . "' border='0' class='img-responsive' alt='Photo evidence' /></div><br />\n";
							echo "  <div>" . $form->input('file', 'photo_evidence') . "</div>\n";
							echo $form->rowEnd();

	}
	elseif ($templateName == 'rumour_add') {
		/* Status */		if (!$matchingRumour) {
								if ($logged_in['is_moderator'] || ($logged_in['is_administrator'] && $logged_in['can_edit_content'])) echo $form->row('select', 'status_id', $operators->firstTrue(@$_POST['status_id'], @$rumour[0]['status_id']), true, 'Status', 'form-control', $rumourStatuses);
							}
							else echo $form->row('uneditable_static', 'status', $operators->firstTrue(@$rumour[0]['status'], '-'), true, 'Status');
		/* Priority */		if (!$matchingRumour) {
								if ($logged_in['is_moderator'] || ($logged_in['is_administrator'] && $logged_in['can_edit_content'])) echo $form->row('select', 'priority_id', $operators->firstTrue(@$_POST['priority_id'], @$rumour[0]['priority_id']), true, 'Priority', 'form-control', $rumourPriorities);
							}
							else echo $form->row('uneditable_static', 'priority', $operators->firstTrue(@$rumour[0]['priority'], '-'), true, 'Priority');
		/* Assigned to */	if (!$matchingRumour) {
								if ($logged_in['is_moderator'] || ($logged_in['is_administrator'] && $logged_in['can_edit_content'])) echo $form->row('select', 'assigned_to', $operators->firstTrue(@$_POST['assigned_to'], @$rumour[0]['assigned_to']), false, 'Asigned to', 'form-control', $allModeratorsAndCommunityLiaisons);
							}
							else echo $form->row('uneditable_static', 'assigned_to', $operators->firstTrue(@$rumour[0]['assigned_to_full_name'], '-'), true, 'Assigned to');
	}

	/* Tags */				if (!@$matchingRumour) echo $form->row('select', 'tags', $operators->firstTrue(@$currentTags, @$suggestedTags), false, 'Tags', 'select2', $allTags, null, array('multiple'=>'multiple', 'data-tags'=>'true'));

	/* Country occurred */		if (($templateName == 'rumour_add' && !$matchingRumour) || ($templateName == 'rumour_edit' && $logged_in['is_administrator'] && $logged_in['can_edit_content'])) echo $form->row('country', 'country', $operators->firstTrue(@$_POST['country'], @$rumour[0]['country_id']), false, 'Country occurred', 'form-control');
								else echo $form->row('uneditable_static', 'country', @$rumour[0]['country'], false, 'Country occurred');
	/* Area occurred */			if (($templateName == 'rumour_add' && !$matchingRumour) || ($templateName == 'rumour_edit' && $logged_in['is_administrator'] && $logged_in['can_edit_content'])) {
									echo $form->row('text', 'city', $operators->firstTrue(@$_POST['city'], @$rumour[0]['city']), false, 'Area occurred', 'form-control');
									if ($templateName == 'rumour_edit') {
										echo $form->rowStart('latLong');
										echo "  <div class='row'>\n";
										echo "    <div class='col-md-6'>" . $form->input('text', 'latitude', $operators->firstTrue(@$_POST['latitude'], (@$rumour[0]['latitude'] <> 0 ? $rumour[0]['latitude'] : false)), false, '|Latitude', 'form-control') . "</div>\n";
										echo "    <div class='col-md-6'>" . $form->input('text', 'longitude', $operators->firstTrue(@$_POST['longitude'], (@$rumour[0]['longitude'] <> 0 ? $rumour[0]['longitude'] : false)), false, '|Longitude', 'form-control') . "</div>\n";
										echo "  </div>\n";
										echo $form->rowEnd();
									}
								}
								else echo $form->row('uneditable_static', 'city', @$rumour[0]['city'], false, 'Area occurred');
	/* Occurred on */			if (($templateName == 'rumour_add' && !$matchingRumour) || ($templateName == 'rumour_edit' && $logged_in['is_administrator'] && $logged_in['can_edit_content'])) echo $form->row('datetime_with_picker', 'occurred_on', $operators->firstTrue(@$_POST['occurred_on'], @$rumour[0]['occurred_on']), false, 'Occurred on', 'form-control');
								elseif (@$rumour[0]['occurred_on'] == '0000-00-00 00:00:00') echo $form->row('uneditable_static', 'occurred_on', 'Unknown', false, 'Occurred on');
								else echo $form->row('uneditable_static', 'occurred_on', date('F j, Y', strtotime(@$rumour[0]['occurred_on'])), false, 'Occurred on');
								
								if ($templateName == 'rumour_add') {
									/* Country heard */		echo $form->row('country', 'country_heard', $operators->firstTrue(@$_POST['country_heard'], @$_POST['country']), false, 'Country heard', 'form-control');
									/* Area heard */		echo $form->row('text', 'city_heard', @$_POST['city_heard'], false, 'Area heard', 'form-control');
									/* Heard on */			echo $form->row('datetime_with_picker', 'heard_on', @$_POST['heard_on'], false, 'Heard on', 'form-control');
									/* Location type */		echo $form->row('select', 'location_type', @$_POST['location_type'], false, 'Overheard at', 'select2', $locationTypes, null, array('data-placeholder'=>'Overheard at', 'data-tags'=>'true'));
								}

	if ($templateName == 'rumour_add' && $logged_in['is_proxy']) {
		/* Source */			echo $form->row('select', 'source_id', @$_POST['source_id'], true, 'Reported via', 'form-control', $rumourSources) . "\n";
		/* On behalf of */		echo $form->rowStart('on_behalf_of', "Reported on behalf of");
								echo $form->input('select', 'heard_by', $operators->firstTrue(@$_POST['heard_by'], $logged_in['user_id']), false, '|Heard by', 'form-control', $allUsers + array(''=>'---', 'add'=>'New user')) . "\n";
								echo "    " . $form->input('button', 'add_user', null, false, '...or create new user', 'btn btn-link', null, null, array('data-toggle'=>'collapse', 'data-target'=>'#newuser_container', 'aria-expanded'=>'false', 'aria-controls'=>'newuser_container'), null, array('onClick'=>'document.getElementById("heard_by").value="add"; return false;')) . "\n";
								include 'add_new_user.php';
								echo $form->rowEnd();
	}
	
	/* Actions */		echo $form->rowStart('actions');
						if ($templateName == 'rumour_add') {
							if ($matchingRumour) echo $form->input('submit', 'add_rumour', 'Report hearing this rumour', false, false, 'btn btn-inverse') . "\n";
							else echo $form->input('submit', 'add_rumour', 'Report this rumour', false, false, 'btn btn-info') . "\n";
						}
						elseif ($templateName == 'rumour_edit') {
							echo $form->input('submit', null, null, false, 'Update this rumour', 'btn btn-info') . "\n";
							if (@$photoEvidence) echo $form->input('button', null, null, false, 'Delete photographic evidence', 'btn btn-link', null, null, null, null, array('onClick'=>'deletePhoto(); return false;')) . "\n";
							if ($logged_in['is_administrator'] && $logged_in['can_edit_content']) echo $form->input('button', null, null, false, 'Delete this rumour', 'btn btn-link', null, null, null, null, array('onClick'=>'deleteRumour(); return false;')) . "\n";
							echo $form->input('button', null, null, false, 'Cancel', 'btn btn-link', null, null, null, null, array('onClick'=>'document.location.href="/rumour/' . $publicID . '"; return false;')) . "\n";

						}
						echo $form->rowEnd();

	echo $form->end() . "\n";

?>
