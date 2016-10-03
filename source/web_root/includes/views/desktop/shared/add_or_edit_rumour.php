<?php

	echo $form->start('addEditRumourForm', null, 'post', null, array('enctype'=>'multipart/form-data'), array('onSubmit'=>'validateAddEditRumourForm(); return false;')) . "\n";
	echo $form->input('hidden', 'step', @$step) . "\n";
	echo $form->input('hidden', 'rumour_id', @$matchingRumour) . "\n";
	echo $form->input('hidden', 'deleteThisPhoto') . "\n";
	echo $form->input('hidden', 'deleteThisRumour') . "\n";
	echo $form->input('hidden', 'templateName', $tl->page['template']) . "\n";

	/* Description */			if (($tl->page['template'] == 'rumour_add' && !$matchingRumour) || ($tl->page['template'] == 'rumour_edit' && $logged_in['is_administrator'] && $logged_in['can_edit_content'])) echo $form->row('textarea', 'description', $operators->firstTrue(@$_POST['description'], @$rumour[0]['description']), true, 'Rumour|Please be as concise as possible', 'form-control', null, 255, array('rows'=>'7'));
								else echo $form->row('uneditable_static', 'description', "<a href='/rumour/" . $rumour[0]['public_id'] . "/" . $parser->seoFriendlySuffix($rumour[0]['description']) . "' target='_blank'>" . $rumour[0]['description'] . "</a>", false, 'Rumour');

	if ($tl->page['template'] == 'rumour_edit') {

		/* The following fields are editable by anyone who can see this page
		   (admins, moderators, community liaisons) */

		/* Enabled */		echo $form->row('yesno_bootstrap_switch', 'enabled', $operators->firstTrueStrict(@$_POST['enabled'], @$rumour[0]['enabled']), false, "<a href='javascript:void(0)' class='tooltips' onClick='return false' data-toggle='tooltip' title='Disabling this rumour hides it from public access, acting as a form of soft delete.'>Enabled</a>?", null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default'));
		/* Status */		echo $form->row('select', 'status_id', $operators->firstTrue(@$_POST['status_id'], @$rumour[0]['status_id']), true, 'Status', 'form-control', $rumourStatuses);
		/* Findings */		echo $form->row('textarea', 'findings', $operators->firstTrue(@$_POST['findings'], @$rumour[0]['findings']), false, 'Findings', 'form-control', null, null, array('rows'=>'5'));
		/* Priority */		echo $form->row('select', 'priority_id', $operators->firstTrue(@$_POST['priority_id'], @$rumour[0]['priority_id']), true, 'Priority', 'form-control', $rumourPriorities);
		/* Assigned to */	echo $form->row('select', 'assigned_to', $operators->firstTrue(@$_POST['assigned_to'], @$rumour[0]['assigned_to']), false, 'Assigned to', 'form-control', $allModeratorsAndCommunityLiaisons);
		/* Verified with */	echo $form->row('text', 'verified_with', $operators->firstTrue(@$_POST['verified_with'], @$rumour[0]['verified_with']), false, "<a href='javascript:void(0)' class='tooltips' onClick='return false' data-toggle='tooltip' title='Information entered in this field will never be visible to anyone other than moderators, community liaisons and administrators.'>Verified with</a>", 'form-control', null, 255);
		/* Attachments */	echo $form->rowStart('evidence', "Photographic evidence and other file attachments");
							echo "  " . $form->input('file_dropzone', 'evidence', null, false, null, 'form-control', null, null, array('message'=>"Drag or click here to upload...", 'destination_path'=>'trash/' . date('Y-m-d_H-i-s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') + 1, date('Y'))), 'acceptable_mime_types'=>'image/jpg,image/jpeg,image/png,image/gif,application/pdf', 'thumbnail_width'=>100, 'thumbnail_height'=>100));
							echo $form->rowEnd();
							if (count($attachments)) {
								echo $form->rowStart('attachments');
								for ($counter = 0; $counter < count($attachments); $counter++) {
									$filePath = $attachments[$counter];
									if (substr_count($filePath, '\\')) $filename = substr($filePath, strrpos($filePath, '\\') + 1);
									else $filename = substr($filePath, strrpos($filePath, '/') + 1);

									echo $form->input('checkbox', 'delete_' . $counter, null, false, "Delete " . ($file_manager->isImage($filePath) ? "<a href='javascript:void(0)' data-toggle='modal' data-target='#modal_" . $counter . "'>" : "<a href='/" . $filePath . "' target='_blank'>") . $filename . "</a>") . "<br />\n";
									echo $form->input('hidden', 'filepath_' . $counter, $filePath) . "\n";

									if ($file_manager->isImage($filePath)) {
										echo "<div id='modal_" . $counter . "' class='modal fade'>\n";
										echo "  <div class='modal-dialog'>\n";
										echo "    <div class='modal-content'>\n";
										echo "      <div class='modal-header'>\n";
										echo "        <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>\n";
										echo "        <h4 class='modal-title'>Image preview</h4>\n";
										echo "      </div>\n";
										echo "      <div class='modal-body'>\n";
										echo "        <a href='/" . $filePath . "' target='_blank'><img src='/" . $filePath . "?random=" . rand(10000,99999) . "' class='img-responsive' /></a>\n";
										echo "      </div>\n";
										echo "    </div><!-- /.modal-content -->\n";
										echo "  </div><!-- /.modal-dialog -->\n";
										echo "</div><!-- /.modal -->\n";
									}
								}
								echo $form->rowEnd();
							}

	}
	elseif ($tl->page['template'] == 'rumour_add') {
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

	/* Country occurred */		if (($tl->page['template'] == 'rumour_add' && !$matchingRumour) || ($tl->page['template'] == 'rumour_edit' && $logged_in['is_administrator'] && $logged_in['can_edit_content'])) echo $form->row('country', 'country', $operators->firstTrue(@$_POST['country'], @$rumour[0]['country_id']), false, 'Country occurred', 'form-control');
								else echo $form->row('uneditable_static', 'country', @$rumour[0]['country'], false, 'Country occurred');
	/* Area occurred */			if (($tl->page['template'] == 'rumour_add' && !$matchingRumour) || ($tl->page['template'] == 'rumour_edit' && $logged_in['is_administrator'] && $logged_in['can_edit_content'])) {
									echo $form->row('text', 'city', $operators->firstTrue(@$_POST['city'], @$rumour[0]['city']), false, 'Area occurred', 'form-control', null, 50);
									echo $form->row('latlongmap', 'occurred_at', $operators->firstTrue((floatval(@$_POST['occurred_at_latitude']) <> 0 || floatval(@$_POST['occurred_at_longitude']) <> 0 ? floatval(@$_POST['occurred_at_latitude']) . "," . floatval(@$_POST['occurred_at_longitude']) : false), (floatval(@$rumour[0]['latitude']) <> 0 && floatval(@$rumour[0]['longitude']) <> 0 ? $rumour[0]['latitude'] . "," . $rumour[0]['longitude'] : false), ($tl->page['template'] == 'rumour_add' && @$tl->page['domain_alias']['latitude'] <> 0 && @$tl->page['domain_alias']['longitude'] <> 0 ? @$tl->page['domain_alias']['latitude'] . ',' . @$tl->page['domain_alias']['longitude']: false)));
								}
								else echo $form->row('uneditable_static', 'city', @$rumour[0]['city'], false, 'Area occurred');
	/* Occurred on */			if (($tl->page['template'] == 'rumour_add' && !$matchingRumour) || ($tl->page['template'] == 'rumour_edit' && $logged_in['is_administrator'] && $logged_in['can_edit_content'])) echo $form->row('datetime_with_picker', 'occurred_on', $operators->firstTrue(@$_POST['occurred_on'], @$rumour[0]['occurred_on']), false, 'Occurred on', 'form-control');
								elseif (@$rumour[0]['occurred_on'] == '0000-00-00 00:00:00') echo $form->row('uneditable_static', 'occurred_on', 'Unknown', false, 'Occurred on');
								else echo $form->row('uneditable_static', 'occurred_on', date('F j, Y', strtotime(@$rumour[0]['occurred_on'])), false, 'Occurred on');
								
								if ($tl->page['template'] == 'rumour_add') {
									/* Country heard */		echo $form->row('country', 'country_heard', $operators->firstTrue(@$_POST['country_heard'], @$_POST['country']), false, 'Country heard', 'form-control');
									/* Area heard */		echo $form->row('text', 'city_heard', @$_POST['city_heard'], false, 'Area heard', 'form-control', null, 50);
															echo $form->row('latlongmap', 'heard_at', $operators->firstTrue((floatval(@$_POST['heard_at_latitude']) <> 0 || floatval(@$_POST['heard_at_longitude']) <> 0 ? floatval(@$_POST['heard_at_latitude']) . "," . floatval(@$_POST['heard_at_longitude']) : false), (@$tl->page['domain_alias']['latitude'] <> 0 && @$tl->page['domain_alias']['longitude'] <> 0 ? @$tl->page['domain_alias']['latitude'] . ',' . @$tl->page['domain_alias']['longitude']: false)));
									/* Heard on */			echo $form->row('datetime_with_picker', 'heard_on', @$_POST['heard_on'], false, 'Heard on', 'form-control');
									/* Location type */		echo $form->row('select', 'location_type', @$_POST['location_type'], false, 'Overheard at', 'select2', $locationTypes, null, array('data-placeholder'=>'Overheard at', 'data-tags'=>'true'));
								}

	if ($tl->page['template'] == 'rumour_add' && $logged_in['is_proxy']) {
		/* Source */			echo $form->row('select', 'source_id', @$_POST['source_id'], true, 'Reported via', 'form-control', $rumourSources) . "\n";
		/* On behalf of */		echo $form->rowStart('on_behalf_of', "Reported on behalf of");
								echo "  <div class='row'>\n";
								echo "    <div class='col-lg-9 col-md-9 col-sm-9 col-xs-9'>" . $form->input('select', 'heard_by', $operators->firstTrue(@$_POST['heard_by'], $logged_in['user_id']), false, '|Heard by', 'form-control', $allUsers + array(''=>'---', 'add'=>'New user')) . "</div>\n";
								echo "    <div class='col-lg-3 col-md-3 col-sm-3 col-xs-3'>" . $form->input('button', 'find_button', null, false, 'Search', 'btn btn-default btn-block', null, null, array('data-toggle'=>'modal', 'data-target'=>'#search_users')) . "</div>\n";
								echo "  </div>\n";
								echo "  <div class='row'>\n";
								echo "    <div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>" . $form->input('button', 'add_user', null, false, '...or create new user', 'btn btn-link', null, null, array('data-toggle'=>'collapse', 'data-target'=>'#newuser_container', 'aria-expanded'=>'false', 'aria-controls'=>'newuser_container'), null, array('onClick'=>'document.getElementById("heard_by").value="add"; return false;')) . "</div>\n";
								echo "  </div>\n";
								include 'add_new_user.php';
								echo $form->rowEnd();

								echo "<div class='modal fade' id='search_users' tabindex='-1' role='dialog' aria-labelledby='search_usersLabel'>\n";
								echo "  <div class='modal-dialog' role='document'>\n";
								echo "    <div class='modal-content'>\n";
								echo "      <div class='modal-header'>\n";
								echo "        <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>\n";
								echo "        <h4 class='modal-title' id='search_usersLabel'>Search</h4>\n";
								echo "      </div>\n";
								echo "      <div class='modal-body'>\n";
								echo "        <table class='table table-condensed'>\n";
								echo "        <thead>\n";
								echo "        <th>Name</th>\n";
								echo "        <th>Email</th>\n";
								echo "        <th>Phone</th>\n";
								echo "        </thead>\n";
								echo "        <tbody>\n";
								for ($counter = 0; $counter < count($allUsersStructured); $counter++) {
									echo "        <tr>\n";
									echo "        <td><div><a href='javascript:void(0);' onClick='document.getElementById(" . '"heard_by"' . ").value=" . '"' . $allUsersStructured[$counter]['user_id'] . '"' . "' data-dismiss='modal' aria-label='Close'>" . $allUsersStructured[$counter]['username'] . "</a></div><div>" . $allUsersStructured[$counter]['full_name'] . "</div></td>\n";
									echo "        <td>" . $allUsersStructured[$counter]['email'] . "</td>\n";
									echo "        <td><div>" . $allUsersStructured[$counter]['primary_phone'] . "</div><div>" . $allUsersStructured[$counter]['secondary_phone'] . "</div></td>\n";
									echo "        </tr>\n";
								}
								echo "        </tbody>\n";
								echo "        </table>\n";
								echo "      </div>\n";
								echo "    </div>\n";
								echo "  </div>\n";
								echo "</div>\n";

	}
	
	/* Actions */		echo $form->rowStart('actions');
						if ($tl->page['template'] == 'rumour_add') {
							if ($matchingRumour) echo $form->input('submit', 'add_rumour', 'Report hearing this rumour', false, false, 'btn btn-inverse') . "\n";
							else echo $form->input('submit', 'add_rumour', 'Report this rumour', false, false, 'btn btn-info') . "\n";
						}
						elseif ($tl->page['template'] == 'rumour_edit') {
							echo $form->input('submit', null, null, false, 'Update this rumour', 'btn btn-info') . "\n";
							if (@$photoEvidence) echo $form->input('button', null, null, false, 'Delete photographic evidence', 'btn btn-link', null, null, null, null, array('onClick'=>'deletePhoto(); return false;')) . "\n";
							if ($logged_in['is_administrator'] && $logged_in['can_edit_content']) echo $form->input('button', null, null, false, 'Delete this rumour', 'btn btn-link', null, null, null, null, array('onClick'=>'deleteRumour(); return false;')) . "\n";
							echo $form->input('button', null, null, false, 'Cancel', 'btn btn-link', null, null, null, null, array('onClick'=>'document.location.href="/rumour/' . $publicID . '"; return false;')) . "\n";

						}
						echo $form->rowEnd();

	echo $form->end() . "\n";

?>
