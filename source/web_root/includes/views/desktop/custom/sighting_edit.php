<?php
	include 'includes/views/desktop/shared/page_top.php';

	echo "<h2>Edit Sighting</h2>\n";

	echo $form->start('addSightingForm', '', 'post', null, null, array('onSubmit'=>'validateAddSightingForm(); return false;')) . "\n";
	echo $form->input('hidden', 'deleteThisSighting') . "\n";

	/* Rumour */			echo $form->row('uneditable_static', 'description', "<a href='/rumour/" . $sighting[0]['public_id'] . "/" . $parser->seoFriendlySuffix($sighting[0]['description']) . "'>" . $sighting[0]['description'] . "</a>", false, 'Rumour');
	/* Country */			echo $form->row('country', 'country', $operators->firstTrue(@$_POST['country'], $sighting[0]['sighting_country_id']), true, 'Country where heard', 'form-control');
	/* Community */			echo $form->row('text', 'city', $operators->firstTrue(@$_POST['city'], $sighting[0]['sighting_city']), false, 'Community', 'form-control');
							echo $form->rowStart('latLong');
							echo "  <div class='row'>\n";
							echo "    <div class='col-md-6'>" . $form->input('text', 'latitude', $operators->firstTrue(@$_POST['latitude'], (@$sighting[0]['sighting_latitude'] <> 0 ? $sighting[0]['sighting_latitude'] : false)), false, '|Latitude', 'form-control') . "</div>\n";
							echo "    <div class='col-md-6'>" . $form->input('text', 'longitude', $operators->firstTrue(@$_POST['longitude'], (@$sighting[0]['sighting_longitude'] <> 0 ? $sighting[0]['sighting_longitude'] : false)), false, '|Longitude', 'form-control') . "</div>\n";
							echo "  </div>\n";
							echo $form->rowEnd();
	/* Location type */		echo $form->row('select', 'location_type', $operators->firstTrue(@$_POST['location_type'], $sighting[0]['location_type']), false, 'Overheard at', 'select2', (@$locationTypes[$sighting[0]['location_type']] ? $locationTypes : $locationTypes + array($sighting[0]['location_type']=>$sighting[0]['location_type'])), null, array('data-placeholder'=>'Overheard at', 'data-tags'=>'true'));
	/* Date heard */		echo $form->row('datetime_with_picker', 'heard_on', $operators->firstTrue(@$_POST['heard_on'], $sighting[0]['heard_on'], date('Y-m-d H:i:s')), true, 'Heard on', 'form-control', null, 19);
	/* Source */			echo $form->row('select', 'source_id', $operators->firstTrue(@$_POST['source_id'], $sighting[0]['source_id']), true, 'Reported via', 'form-control', $rumourSources);
	/* On behalf of */		if ($logged_in['is_proxy']) {
								echo $form->rowStart('on_behalf_of', "Reported on behalf of");
								echo $form->input('select', 'created_by', $operators->firstTrue(@$_POST['created_by'], $sighting[0]['heard_by'], $logged_in['user_id']), false, '|Heard by', 'form-control', $allUsers + array(''=>'---', 'add'=>'New user')) . "\n";
								echo "    " . $form->input('button', 'add_user', null, false, '...or create new user', 'btn btn-link', null, null, array('data-toggle'=>'collapse', 'data-target'=>'#newuser_container', 'aria-expanded'=>'false', 'aria-controls'=>'newuser_container'), null, array('onClick'=>'document.getElementById("created_by").value="add"; return false;')) . "\n";
								
								include 'shared/add_new_user.php';
								
								echo $form->rowEnd();
							}

	/* Action */			echo $form->rowStart('actions');
							echo "  " . $form->input('submit', 'save_sighting_button', null, false, 'Update', 'btn btn-info') . "\n";
							echo "  " . $form->input('button', 'delete_sighting_button', null, false, 'Delete', 'btn btn-link', null, null, null, null, array('onClick'=>'removeSighting(); return false;')) . "\n";
							echo "  " . $form->input('cancel_and_return', 'cancel_button', null, false, 'Cancel', 'btn btn-link') . "\n";
							echo $form->rowEnd();

	echo $form->end() . "\n";
		
	include 'includes/views/desktop/shared/page_bottom.php';
?>