<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		if (@$tl->page['parameter1'] == 'add') {
			$rumourPublicID = @$tl->page['parameter2'];
			$rumour = retrieveRumours(array($tablePrefix . 'rumours.public_id'=>$rumourPublicID, $tablePrefix . 'rumours.enabled'=>'1'), null, null, null, 1);
			if (!count($rumour)) $authentication_manager->forceRedirect('/404');

		}
		else $id = floatval(@$tl->page['parameter1']);

	if (@$id) {
		// authenticate user
			if (!$logged_in) $authentication_manager->forceLoginThenRedirectHere();
			elseif ((!$logged_in['is_administrator'] || !$logged_in['can_edit_content']) && $sighting[0]['created_by'] != $logged_in['user_id']  && $sighting[0]['entered_by'] != $logged_in['user_id']) $authentication_manager->forceRedirect('/404');

		// query
			$sighting = retrieveSightings(array('sighting_id'=>$id), null, null, null, 1);
			if (!count($sighting)) $authentication_manager->forceRedirect('/404');

		$tl->page['title'] = "Edit Sighting";
	}
	else {
		// authenticate user
			if (!$logged_in) $authentication_manager->forceLoginThenRedirectHere();

		$tl->page['title'] = "Add Sighting";
	}

	// remaining queries
		$allUsers = array();
		$allUsersStructured = retrieveUsers(array('enabled'=>1, 'rumours_created DESC, anonymous ASC'));
		for ($counter = 0; $counter < count($allUsersStructured); $counter++) {
			$allUsers[$allUsersStructured[$counter]['user_id']] = $allUsersStructured[$counter]['username'];
			if ($allUsersStructured[$counter]['full_name'] || $allUsersStructured[$counter]['email'] || $allUsersStructured[$counter]['primary_phone'] || $allUsersStructured[$counter]['secondary_phone']) $allUsers[$allUsersStructured[$counter]['user_id']] .= " (" . $operators->firstTrue($allUsersStructured[$counter]['full_name'], $allUsersStructured[$counter]['email'], $allUsersStructured[$counter]['primary_phone'], $allUsersStructured[$counter]['secondary_phone']) . ")";
		}

/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */

	if (count($_POST) > 0) {

		$tl->page['error'] = null;
		
		if ($_POST['formName'] == 'addSightingForm' && $_POST['deleteThisSighting'] == 'Y' && @$id) {
			
			// remove sighting
				deleteFromDb('rumour_sightings', array('sighting_id'=>$sighting[0]['sighting_id']), null, null, null, null, 1);

			// update log
				$activity = $logged_in['full_name'] . " has deleted a sighting from the rumour &quot;" . $sighting[0]['description'] . "&quot;";
				$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'sighting_id=' . $sighting[0]['sighting_id'], 'rumour_id=' . $sighting[0]['rumour_id']));

			// redirect
				$authentication_manager->forceRedirect('/rumour/' . $sighting[0]['rumour_public_id'] . '/' . $parser->seoFriendlySuffix($sighting[0]['description']) . '/' . urlencode('view=sightings|success=sighting_removed'));
				
		}
		elseif ($_POST['formName'] == 'addSightingForm' && $logged_in) {
			
			// clean input
				$_POST = $parser->trimAll($_POST);
				$_POST['heard_at_latitude'] = floatval($_POST['heard_at_latitude']);
				$_POST['heard_at_longitude'] = floatval($_POST['heard_at_longitude']);
				$checkboxesToParse = array('newuser_ok_to_contact', 'newuser_anonymous');
				foreach ($checkboxesToParse as $checkbox) {
					if (isset($_POST[$checkbox])) $_POST[$checkbox] = 1;
					else $_POST[$checkbox] = 0;
				}
				
			// check for errors
				if (!@$_POST['country_id']) $tl->page['error'] .= "Please specify a country. ";
				if (!@$_POST['heard_on']) $tl->page['error'] .= "Please specify a date. ";
				if (!@$_POST['source_id']) $tl->page['error'] .= "Please specify a source. ";
				if ($logged_in['is_proxy']) {
					if (!@$_POST['created_by']) $tl->page['error'] .= "Please specify who heard the rumour. ";
					if (@$_POST['created_by'] == 'add') {
						if (!@$_POST['newuser_username']) $tl->page['error'] .= "Please choose a username for the new user. ";
						else {
							$existingUsers = countInDb('users', 'user_id', array('username'=>$_POST['newuser_username']));
							$existingRegistrants = countInDb('registrations', 'registration_id', array('username'=>$_POST['newuser_username']));
							if ($existingUsers[0]['count'] || $existingRegistrants[0]['count'] > 0) $tl->page['error'] .= "The username you've specified for a new user already belongs to another user. ";
						}
						if (!@$_POST['newuser_country']) $tl->page['error'] .= "Please specify the new user's country. ";
						if ($_POST['newuser_email']) {
							if (!$input_validator->validateEmailRobust($_POST['newuser_email'])) $tl->page['error'] .= "Please specify a valid email address for the new user. ";
							else {
								$existingUsers = countInDb('users', 'user_id', array('email'=>$_POST['newuser_email']));
								$existingRegistrants = countInDb('registrations', 'registration_id', array('email'=>$_POST['newuser_email']));
								if ($existingUsers[0]['count'] || $existingRegistrants[0]['count'] > 0) $tl->page['error'] .= "The email address you've specified for a new user already belongs to another user. ";
							}
						}
					}
				}

			if (!$tl->page['error']) {
				// create encoded IP
					if (strlen($_SERVER['REMOTE_ADDR']) > 15) $ipv6 = $parser->encodeIP($_SERVER['REMOTE_ADDR'], 'ipv6');
					elseif (strlen($_SERVER['REMOTE_ADDR']) > 0) $ipv4 = $parser->encodeIP($_SERVER['REMOTE_ADDR'], 'ipv4');
				
				// create publicID
					if (!@$id) {
						$sightingPublicID = null;
						while ($sightingPublicID == null) {
							$sightingPublicID = $link->customAlphaID('a', 6, null, true, true);
							$doesPublicIdExist = countInDb('rumour_sightings', 'public_id', array('public_id'=>$sightingPublicID));
							if ($doesPublicIdExist[0]['count'] > 0) $sightingPublicID = null;
						}
					}

				// determine attribution
					if ($logged_in['is_proxy']) {
						if ($_POST['created_by'] != 'add') $createdBy = $_POST['created_by'];
						else {
							// check for existing email or phone
								$result = retrieveUsers(null, null, "(email != '' AND email = '" . $_POST['newuser_email'] . "') OR (primary_phone != '' AND (primary_phone = '" . $_POST['newuser_primary_phone'] . "' OR primary_phone = '" . $_POST['newuser_secondary_phone'] . "')) OR (secondary_phone != '' AND (secondary_phone = '" . $_POST['newuser_primary_phone'] . "' OR secondary_phone = '" . $_POST['newuser_secondary_phone'] . "'))", "registered_on DESC", 1);
								if (count($result)) $createdBy = $result[0]['user_id'];
								else {
									// no match, so create
										$createdBy = insertIntoDb('users', array('first_name'=>$_POST['newuser_first_name'], 'last_name'=>$_POST['newuser_last_name'], 'username'=>$_POST['newuser_username'], 'email'=>$_POST['newuser_email'], 'primary_phone'=>$_POST['newuser_primary_phone'], 'primary_phone_sms'=>$_POST['newuser_primary_phone_sms'], 'secondary_phone'=>$_POST['newuser_secondary_phone'], 'secondary_phone_sms'=>$_POST['newuser_secondary_phone_sms'], 'country_id'=>$_POST['newuser_country'], 'ok_to_contact'=>$_POST['newuser_ok_to_contact'], 'anonymous'=>$_POST['newuser_anonymous'], 'registered_on'=>date('Y-m-d H:i:s'), 'registered_by'=>$logged_in['user_id']));
								}
						}
					}
					else $createdBy = $logged_in['user_id'];

				// faux geocode
/*
					REMOVING FAUX GEOCODING:
					-----------------------

					if (!$_POST['latitude'] || !$_POST['longitude']) {
						$latLong = retrieveSingleFromDB('rumour_sightings', null, array('country_id'=>@$_POST['country'], 'city'=>@$_POST['city']), null, null, null, "latitude <> 0 AND longitude <> 0");
						if (!count($latLong)) $latLong = retrieveSingleFromDB('rumours', null, array('country_id'=>@$_POST['country'], 'city'=>@$_POST['city']), null, null, null, "latitude <> 0 AND longitude <> 0");

						if ($latLong[0]['latitude'] && $latLong[0]['longitude']) {
							$_POST['latitude'] = $latLong[0]['latitude'];
							$_POST['longitude'] = $latLong[0]['longitude'];
						}
					}
*/

				// save sighting
					if (!@$id) $id = insertIntoDb('rumour_sightings', array('public_id'=>$sightingPublicID, 'rumour_id'=>$rumour[0]['rumour_id']));
					updateDb('rumour_sightings', array('created_by'=>$createdBy, 'entered_by'=>$logged_in['user_id'], 'entered_on'=>date('Y-m-d H:i:s'), 'source_id'=>$_POST['source_id'], 'details'=>$_POST['details'], 'ipv4'=>@$ipv4, 'ipv6'=>@$ipv6, 'country_id'=>$_POST['country_id'], 'city'=>@$_POST['city'], 'location_type'=>@$_POST['location_type'], 'latitude'=>@$_POST['heard_at_latitude'], 'longitude'=>@$_POST['heard_at_longitude'], 'heard_on'=>$_POST['heard_on']), array('sighting_id'=>$id), null, null, null, null, 1);
										
				// update log
					$activity = $logged_in['full_name'] . " has saved a sighting for the rumour &quot;" . $operators->firstTrue(@$sighting[0]['description'], @$rumour[0]['description']) . "&quot;";
					$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'sighting_id=' . $id, 'rumour_id=' . $operators->firstTrue(@$sighting[0]['rumour_id'], @$rumour[0]['rumour_id'])));

					$sightings = retrieveSightings(['domain_alias_id'=>@$tl->page['domain_alias']['cms_id']]);

				// redirect
					$authentication_manager->forceRedirect('/rumour/' . (@$sighting[0]['rumour_public_id'] ? $sighting[0]['rumour_public_id'] : $rumour[0]['public_id']) . '/' . $parser->seoFriendlySuffix((@$sighting[0]['description'] ? $sighting[0]['description'] : $rumour[0]['description'])) . '/' . urlencode('success=sighting_updated'));
					
			}
			
		}
		
	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>