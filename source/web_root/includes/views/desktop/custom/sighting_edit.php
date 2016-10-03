<?php

	echo "<h2>" . $tl->page['title'] . "</h2>\n";

	echo $form->start('addSightingForm', '', 'post', null, null, array('onSubmit'=>'validateAddSightingForm(); return false;')) . "\n";
	echo $form->input('hidden', 'deleteThisSighting') . "\n";

	/* Rumour */			echo $form->row('uneditable_static', 'description', "<a href='/rumour/" . $operators->firstTrue(@$sighting[0]['rumour_public_id'], @$rumour[0]['public_id']) . "/" . $parser->seoFriendlySuffix($operators->firstTrue(@$sighting[0]['description'], @$rumour[0]['description'])) . "'>" . $operators->firstTrue(@$sighting[0]['description'], @$rumour[0]['description']) . "</a>", false, 'Rumour');
	/* Country */			echo $form->row('country', 'country_id', $operators->firstTrue(@$_POST['country_id'], @$sighting[0]['sighting_country_id'], (@!$id && @$tl->page['domain_alias']['country_id'] ? $tl->page['domain_alias']['country_id'] : false)), true, 'Country where heard', 'form-control');
	/* Community */			echo $form->row('text', 'city', $operators->firstTrue(@$_POST['city'], @$sighting[0]['sighting_city']), false, 'Community', 'form-control', null, 50);
							echo $form->row('latlongmap', 'heard_at', $operators->firstTrue((floatval(@$_POST['heard_at_latitude']) <> 0 || floatval(@$_POST['heard_at_longitude']) <> 0 ? floatval(@$_POST['heard_at_latitude']) . "," . floatval(@$_POST['heard_at_longitude']) : false), (floatval(@$sighting[0]['sighting_latitude']) <> 0 && floatval(@$sighting[0]['sighting_longitude']) <> 0 ? @$sighting[0]['sighting_latitude'] . "," . @$sighting[0]['sighting_longitude'] : false), (@!$id && @$tl->page['domain_alias']['latitude'] && @$tl->page['domain_alias']['longitude'] ? $tl->page['domain_alias']['latitude'] . ',' . @$tl->page['domain_alias']['longitude'] : false)));
	/* Location type */		echo $form->row('select', 'location_type', $operators->firstTrue(@$_POST['location_type'], @$sighting[0]['location_type']), false, 'Overheard at', 'select2', (@$locationTypes[@$sighting[0]['location_type']] ? $locationTypes : $locationTypes + array(@$sighting[0]['location_type']=>@$sighting[0]['location_type'])), null, array('data-placeholder'=>'Overheard at', 'data-tags'=>'true'));
	/* Date heard */		echo $form->row('datetime_with_picker', 'heard_on', $operators->firstTrue(@$_POST['heard_on'], @$sighting[0]['heard_on'], date('Y-m-d H:i:s')), true, 'Heard on', 'form-control', null, 19);
	/* Source */			echo $form->row('select', 'source_id', $operators->firstTrue(@$_POST['source_id'], @$sighting[0]['source_id']), true, 'Reported via', 'form-control', $rumourSources);
	/* On behalf of */		if ($logged_in['is_proxy']) {
								echo $form->rowStart('on_behalf_of', "Reported on behalf of");
								echo "  <div class='row'>\n";
								echo "    <div class='col-lg-9 col-md-9 col-sm-9 col-xs-9'>" .  $form->input('select', 'created_by', $operators->firstTrue(@$_POST['created_by'], @$sighting[0]['heard_by'], $logged_in['user_id']), false, '|Heard by', 'form-control', $allUsers + array(''=>'---', 'add'=>'New user')) . "</div>\n";
								echo "    <div class='col-lg-3 col-md-3 col-sm-3 col-xs-3'>" . $form->input('button', 'find_button', null, false, 'Search', 'btn btn-default btn-block', null, null, array('data-toggle'=>'modal', 'data-target'=>'#search_users')) . "</div>\n";
								echo "  </div>\n";
								echo "  <div class='row'>\n";
								echo "    <div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>" . $form->input('button', 'add_user', null, false, '...or create new user', 'btn btn-link', null, null, array('data-toggle'=>'collapse', 'data-target'=>'#newuser_container', 'aria-expanded'=>'false', 'aria-controls'=>'newuser_container'), null, array('onClick'=>'document.getElementById("created_by").value="add"; return false;')) . "</div>\n";
								echo "  </div>\n";
								
								include 'includes/views/desktop/shared/add_new_user.php';
								
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
									echo "        <td><div><a href='javascript:void(0);' onClick='document.getElementById(" . '"created_by"' . ").value=" . '"' . $allUsersStructured[$counter]['user_id'] . '"' . "' data-dismiss='modal' aria-label='Close'>" . $allUsersStructured[$counter]['username'] . "</a></div><div>" . $allUsersStructured[$counter]['full_name'] . "</div></td>\n";
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

	/* Action */			echo $form->rowStart('actions');
							echo "  " . $form->input('submit', 'save_sighting_button', null, false, 'Save', 'btn btn-info') . "\n";
							if (@$id) echo "  " . $form->input('button', 'delete_sighting_button', null, false, 'Delete', 'btn btn-link', null, null, null, null, array('onClick'=>'removeSighting(); return false;')) . "\n";
							echo "  " . $form->input('cancel_and_return', 'cancel_button', null, false, 'Cancel', 'btn btn-link') . "\n";
							echo $form->rowEnd();

	echo $form->end() . "\n";
		
?>