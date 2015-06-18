<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$id = floatval($parameter1);
		if (!$id) {
			header('Location: /404');
			exit();
		}
		
		$pageStatus = $parameter2;

	// queries
		$sighting = retrieveSightings(array('sighting_id'=>$id), null, null, null, 1);
		if (!count($sighting)) {
			header('Location: /404');
			exit();
		}

	// authenticate user
		if ((!$logged_in['is_administrator'] || !$logged_in['can_edit_content']) && $sighting[0]['created_by'] != $logged_in['user_id']  && $sighting[0]['entered_by'] != $logged_in['user_id']) forceLoginThenRedirectHere();

	// remaining queries
		$allUsers = array();
		$result = retrieveUsers(array('enabled'=>1), null, null, 'rumours_created DESC, anonymous ASC');
		for ($counter = 0; $counter < count($result); $counter++) {
			if ($result[$counter]['anonymous']) $allUsers[$result[$counter]['user_id']] = "Anonymous (" . $result[$counter]['username'] . ")";
			else {
				$allUsers[$result[$counter]['user_id']] = $result[$counter]['username'];
				if ($result[$counter]['full_name']) $allUsers[$result[$counter]['user_id']] .= " (" . $result[$counter]['full_name'] . ")";
			}
		}

/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */

	if (count($_POST) > 0) {

		$pageError = null;
		
		if ($_POST['formName'] == 'addSightingForm' && $_POST['deleteThisSighting'] == 'Y') {
			
			// remove sighting
				deleteFromDb('rumour_sightings', array('sighting_id'=>$sighting[0]['sighting_id']), null, null, null, null, 1);

			// update log
				$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has deleted a sighting (sighting_id " . $sighting[0]['sighting_id'] . ") from the rumour &quot;" . $sighting[0]['description'] . "&quot; (public_id " . $sighting[0]['public_id'] . ")";
				$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'sighting_id=' . $sighting[0]['sighting_id'], 'rumour_id=' . $sighting[0]['rumour_id']));
				
			// redirect
				header('Location: /rumour/' . $sighting[0]['public_id'] . '/' . $parser->seoFriendlySuffix($sighting[0]['description']) . '/view=sightings/sighting_removed');
				exit();
				
		}
		elseif ($_POST['formName'] == 'addSightingForm' && $logged_in) {
			
			// clean input
				$_POST = $parser->trimAll($_POST);
				$_POST['latitude'] = floatval($_POST['latitude']);
				$_POST['longitude'] = floatval($_POST['longitude']);
				$checkboxesToParse = array('newuser_ok_to_contact', 'newuser_anonymous');
				foreach ($checkboxesToParse as $checkbox) {
					if (isset($_POST[$checkbox])) $_POST[$checkbox] = 1;
					else $_POST[$checkbox] = 0;
				}
				
			// check for errors
				if (!@$_POST['country']) $pageError .= "Please specify a country. ";
				if (!@$_POST['heard_on']) $pageError .= "Please specify a date. ";
				if (!@$_POST['source_id']) $pageError .= "Please specify a source. ";
				if ($logged_in['is_proxy']) {
					if (!@$_POST['created_by']) $pageError .= "Please specify who heard the rumour. ";
					if (@$_POST['created_by'] == 'add') {
						if (!@$_POST['newuser_username']) $pageError .= "Please choose a username for the new user. ";
						else {
							$existingUsers = countInDb('users', 'user_id', array('username'=>$_POST['newuser_username']));
							$existingRegistrants = countInDb('registrations', 'registration_id', array('username'=>$_POST['newuser_username']));
							if ($existingUsers[0]['count'] || $existingRegistrants[0]['count'] > 0) $pageError .= "The username you've specified for a new user already belongs to another user. ";
						}
						if (!@$_POST['newuser_country']) $pageError .= "Please specify the new user's country. ";
						if ($_POST['newuser_email']) {
							if (!$input_validator->validateEmailRobust($_POST['newuser_email'])) $pageError .= "Please specify a valid email address for the new user. ";
							else {
								$existingUsers = countInDb('users', 'user_id', array('email'=>$_POST['newuser_email']));
								$existingRegistrants = countInDb('registrations', 'registration_id', array('email'=>$_POST['newuser_email']));
								if ($existingUsers[0]['count'] || $existingRegistrants[0]['count'] > 0) $pageError .= "The email address you've specified for a new user already belongs to another user. ";
							}
						}
					}
				}

			if (!$pageError) {
				// create encoded IP
					if (strlen($_SERVER['REMOTE_ADDR']) > 15) $ipv6 = $parser->encodeIP($_SERVER['REMOTE_ADDR'], 'ipv6');
					elseif (strlen($_SERVER['REMOTE_ADDR']) > 0) $ipv4 = $parser->encodeIP($_SERVER['REMOTE_ADDR'], 'ipv4');
				
				// determine attribution
					if ($logged_in['is_proxy']) {
						if ($_POST['created_by'] != 'add') $createdBy = $_POST['created_by'];
						else $createdBy = insertIntoDb('users', array('first_name'=>$_POST['newuser_first_name'], 'last_name'=>$_POST['newuser_last_name'], 'username'=>$_POST['newuser_username'], 'email'=>$_POST['newuser_email'], 'primary_phone'=>$_POST['newuser_primary_phone'], 'primary_phone_sms'=>$_POST['newuser_primary_phone_sms'], 'secondary_phone'=>$_POST['newuser_secondary_phone'], 'secondary_phone_sms'=>$_POST['newuser_secondary_phone_sms'], 'country_id'=>$_POST['newuser_country'], 'ok_to_contact'=>$_POST['newuser_ok_to_contact'], 'anonymous'=>$_POST['newuser_anonymous'], 'registered_on'=>date('Y-m-d H:i:s'), 'registered_by'=>$logged_in['user_id']));
					}
					else $createdBy = $logged_in['user_id'];

				// faux geocode
					if (!$_POST['latitude'] || !$_POST['longitude']) {
						$latLong = retrieveSingleFromDB('rumour_sightings', null, array('country_id'=>@$_POST['country'], 'city'=>@$_POST['city']), null, null, null, "latitude <> 0 AND longitude <> 0");
						if (!count($latLong)) $latLong = retrieveSingleFromDB('rumours', null, array('country_id'=>@$_POST['country'], 'city'=>@$_POST['city']), null, null, null, "latitude <> 0 AND longitude <> 0");

						if ($latLong[0]['latitude'] && $latLong[0]['longitude']) {
							$_POST['latitude'] = $latLong[0]['latitude'];
							$_POST['longitude'] = $latLong[0]['longitude'];
						}
					}

				// save sighting
					$sightingID = updateDb('rumour_sightings', array('created_by'=>$createdBy, 'entered_by'=>$logged_in['user_id'], 'entered_on'=>date('Y-m-d H:i:s'), 'source_id'=>$_POST['source_id'], 'ipv4'=>@$ipv4, 'ipv6'=>@$ipv6, 'country_id'=>$_POST['country'], 'city'=>@$_POST['city'], 'location_type'=>@$_POST['location_type'], 'latitude'=>@$_POST['latitude'], 'longitude'=>@$_POST['longitude'], 'heard_on'=>$_POST['heard_on']), array('sighting_id'=>$sighting[0]['sighting_id']), null, null, null, null, 1);
										
				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated a sighting (sighting_id " . $sighting[0]['sighting_id'] . ") for the rumour &quot;" . $sighting[0]['description'] . "&quot; (public_id " . $sighting[0]['public_id'] . ")";
					$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'sighting_id=' . $sighting[0]['sighting_id'], 'rumour_id=' . $sighting[0]['rumour_id']));
					
				// redirect
					header('Location: /rumour/' . $sighting[0]['public_id'] . '/' . $parser->seoFriendlySuffix($sighting[0]['description']) . '/view=sightings/sighting_updated');
					exit();
			}
			
		}
		
	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>