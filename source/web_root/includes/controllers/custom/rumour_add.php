<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in) forceLoginThenRedirectHere();
		
	// initialize
		$step = max(@$_POST['step'], 1);
		$matchingRumour = @$_POST['rumour_id'];

	// queries
		// retrieve all users
			$allUsers = array();
			$result = retrieveUsers(array('enabled'=>1));
			for ($counter = 0; $counter < count($result); $counter++) {
				$allUsers[$result[$counter]['user_id']] = $result[$counter]['username'];
				if ($result[$counter]['full_name']) $allUsers[$result[$counter]['user_id']] .= " (" . $result[$counter]['full_name'] . ")";
			}
		// retrieve suggested tags
			$rumourKeywords = $parser->retrieveMeaningfulKeywords(@$_POST['description']);
			$otherCriteria = "1=2";
			for ($counter = 0; $counter < count($rumourKeywords); $counter++) {
				$otherCriteria .= " OR REPLACE(LOWER(tag), " . '"' . "'" . '"' . ", " . '""' . ") = '" . addSlashes(trim(str_replace("'", "", strtolower($rumourKeywords[$counter])))) . "'";
			}

			$allTags = array();
			foreach ($rumourTags as $id=>$tag) {
				$allTags[$tag] = $tag;
			}

			$suggestedTags = null;
			$result = retrieveFromDb('tags', null, null, null, null, null, $otherCriteria, null, 'tag ASC');
			for ($counter = 0; $counter < count($result); $counter++) {
				$suggestedTags[$result[$counter]['tag']] = $result[$counter]['tag'];
			}

			$allModeratorsAndCommunityLiaisons = array();
			$result = retrieveUsers(array('enabled'=>1), null, "is_moderator = 1 OR is_community_liaison = 1");
			for ($counter = 0; $counter < count($result); $counter++) {
				$allModeratorsAndCommunityLiaisons[$result[$counter]['user_id']] = $result[$counter]['username'];
				if ($result[$counter]['full_name']) $allModeratorsAndCommunityLiaisons[$result[$counter]['user_id']] .= " (" . $result[$counter]['full_name'] . ")";
			}

/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
		
	if (count($_POST) > 0) {
		
		if ($_POST['formName'] == 'addRumourForm' && $_POST['step'] == 1) {
			
			// clean input
				$_POST = $parser->trimAll($_POST);
				$_POST['description'] = $parser->removeHTML(@$_POST['description']);
				
			// check for errors
				if (!$_POST['description']) $pageError .= "Please enter a rumour. ";
				if (!$_POST['country']) $pageError .= "Please specify a country. ";

			// search for matches
				if (!$pageError) {

					$numberOfKeywordsToCheck = 5;
					$numberOfResultsToReturn = 10;
					$matches = array();
					
					$rumourKeywords = $parser->retrieveMeaningfulKeywords($_POST['description'], $numberOfKeywordsToCheck);

					$otherCriteria = "1=2";
					for ($counter = 0; $counter < count($rumourKeywords); $counter++) {
						$otherCriteria .= " OR REPLACE(LOWER(description), " . '"' . "'" . '"' . ", " . '""' . ") LIKE '%" . str_replace("'", "", strtolower($rumourKeywords[$counter])) . "%'";
					}
					$matches = retrieveFromDb('rumours', null, array('country_id'=>$_POST['country']), null, null, null, $otherCriteria, null, 'updated_on DESC', $numberOfResultsToReturn);

					if (count($matches) > 0) $step = 2;
					else $step = 3;
					
				}
			
		}
		
		elseif ($_POST['formName'] == 'matchRumourForm' && $_POST['step'] == 2) {
			
			if ($matchingRumour) {
				$rumour = retrieveRumours(array('public_id'=>$matchingRumour, $tablePrefix . 'rumours.enabled'=>'1'));
				if (count($rumour)) {
					$step = 3;
					if (!@$allModeratorsAndCommunityLiaisons[$rumour[0]['created_by']]) $allModeratorsAndCommunityLiaisons[$rumour[0]['created_by']] = $rumour[0]['created_by_full_name'];
				}
			}
			else $step = 3;
			
		}
		
		elseif ($_POST['formName'] == 'addEditRumourForm' && $_POST['step'] == 3) {
			
			// clean input
				$_POST = $parser->trimAll($_POST);
				$_POST['description'] = htmlspecialchars_decode(@$_POST['description'], ENT_QUOTES);
				$_POST['description'] = $parser->removeHTML($_POST['description']);
				$checkboxesToParse = array('newuser_primary_phone_sms', 'newuser_secondary_phone_sms', 'newuser_ok_to_contact', 'newuser_anonymous');
				foreach ($checkboxesToParse as $checkbox) {
					if (isset($_POST[$checkbox])) $_POST[$checkbox] = 1;
					else $_POST[$checkbox] = 0;
				}
				if (!@$_POST['status_id']) $_POST['status_id'] = 1; // set status to "New / uninvestigated"
				if (!@$_POST['source_id']) $_POST['source_id'] = 1; // set source to "Internet"
				for ($counter = 0; $counter < count(@$_POST['tags']); $counter++) {
					$_POST['tags'][$counter] = $parser->removeHTML($_POST['tags'][$counter]);
					$_POST['tags'][$counter] = $parser->includeOrExcludeCharacters($_POST['tags'][$counter], 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_ ');
				}
				
			// check for errors
				if (!$_POST['description']) $pageError .= "Please enter a rumour. ";
				if (!$_POST['country']) $pageError .= "Please specify the country where occurred. ";
				if (!$_POST['country_heard']) $pageError .= "Please specify the country where heard. ";
				if (!$_POST['heard_on']) $pageError .= "Please specify the date heard. ";
				
				if ($logged_in['is_proxy']) {
					if (!$_POST['heard_by']) $pageError .= "On whose behalf is this rumour created? ";
					elseif ($_POST['heard_by'] == 'add') {
						if (!$_POST['newuser_username']) $pageError .= "Please specify a username for the new user. ";
						else {
							$existingUsers = countInDb('users', 'user_id', array('username'=>$_POST['newuser_username']));
							$existingRegistrants = countInDb('registrations', 'registration_id', array('username'=>$_POST['newuser_username']));
							if ($existingUsers[0]['count'] || $existingRegistrants[0]['count'] > 0) $pageError .= "The username you've specified for a new user already belongs to another user. ";
						}
						if (!$_POST['newuser_country']) $pageError .= "Please specify a country for the new user. ";
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
								
				if ($matchingRumour) {
					$rumour = retrieveSingleFromDb('rumours', null, array('public_id'=>$matchingRumour, 'enabled'=>1));
					if (count($rumour) <> 1) $pageError .= "Unable to retrieve existing rumour. ";
				}					
				
			// add rumour
				if (!$pageError) {
					
					// create encoded IP
						if (strlen($_SERVER['REMOTE_ADDR']) > 15) $ipv6 = $parser->encodeIP($_SERVER['REMOTE_ADDR'], 'ipv6');
						elseif (strlen($_SERVER['REMOTE_ADDR']) > 0) $ipv4 = $parser->encodeIP($_SERVER['REMOTE_ADDR'], 'ipv4');
						
					// determine creator of rumour and sighting
						if ($logged_in['is_proxy']) {
							if ($_POST['heard_by'] != 'add') $heardBy = $_POST['heard_by'];
							else $heardBy = insertIntoDb('users', array('first_name'=>$_POST['newuser_first_name'], 'last_name'=>$_POST['newuser_last_name'], 'username'=>$_POST['newuser_username'], 'email'=>$_POST['newuser_email'], 'primary_phone'=>$_POST['newuser_primary_phone'], 'primary_phone_sms'=>$_POST['newuser_primary_phone_sms'], 'secondary_phone'=>$_POST['newuser_secondary_phone'], 'secondary_phone_sms'=>$_POST['newuser_secondary_phone_sms'], 'country_id'=>$_POST['newuser_country'], 'ok_to_contact'=>$_POST['newuser_ok_to_contact'], 'anonymous'=>$_POST['newuser_anonymous'], 'registered_on'=>date('Y-m-d H:i:s'), 'registered_by'=>$logged_in['user_id']));
						}
						else $heardBy = $logged_in['user_id'];
						
					// add rumour to database, if necessary
						if (!$matchingRumour) {
							// create publicID
								$newPublicID = null;
								while ($newPublicID == null) {
									$newPublicID = $url_shortener->customAlphaID('a', 6, null, true, true);
									$doesPublicIdExist = countInDb('rumours', 'public_id', array('public_id'=>$newPublicID));
									if ($doesPublicIdExist[0]['count'] > 0) $newPublicID = null;
								}

							// add rumour
								$rumourID = insertIntoDb('rumours', array('public_id'=>$newPublicID, 'description'=>$_POST['description'], 'country_id'=>$_POST['country'], 'city'=>$_POST['city'], 'occurred_on'=>$_POST['occurred_on'], 'created_by'=>$heardBy, 'created_on'=>date('Y-m-d H:i:s'), 'updated_by'=>$logged_in['user_id'], 'updated_on'=>date('Y-m-d H:i:s'), 'entered_by'=>$logged_in['user_id'], 'status_id'=>@$_POST['status_id'], 'priority_id'=>@$_POST['priority_id'], 'assigned_to'=>@$_POST['assigned_to'], 'pseudonym_id'=>@$pseudonym['pseudonym_id']));
								if (!$rumourID) $pageError .= "Unable to add rumour for some reason. ";
								else {
			
									// faux geocode						
										$latLong = retrieveSingleFromDB('rumour_sightings', null, array('country_id'=>@$_POST['country'], 'city'=>@$_POST['city']), null, null, null, "latitude <> 0 AND longitude <> 0");
										if (!count($latLong)) $latLong = retrieveSingleFromDB('rumours', null, array('country_id'=>@$_POST['country'], 'city'=>@$_POST['city']), null, null, null, "latitude <> 0 AND longitude <> 0");

										if (count($latLong)) updateDb('rumours', array('latitude'=>$latLong[0]['latitude'], 'longitude'=>$latLong[0]['longitude']), array('rumour_id'=>$rumourID), null, null, null, null, 1);

									// update tags
										for ($counter = 0; $counter < count(@$_POST['tags']); $counter++) {

											// retrieve tagID and add any tags which are unique
												$result = retrieveSingleFromDb('tags', null, array('tag'=>$_POST['tags'][$counter]));
												if (count($result)) $tagID = $result[0]['tag_id'];
												else $tagID = insertIntoDb('tags', array('tag'=>$_POST['tags'][$counter], 'created_by'=>$logged_in['user_id'], 'created_on'=>date('Y-m-d H:i:s')));

											// associate tag
												deleteFromDb('rumours_x_tags', array('tag_id'=>$tagID, 'rumour_id'=>$rumourID), null, null, null, null, 1);
												insertIntoDb('rumours_x_tags', array('tag_id'=>$tagID, 'rumour_id'=>$rumourID, 'added_by'=>$logged_in['user_id'], 'added_on'=>date('Y-m-d H:i:s')));

										}
										
								}
										
						}

					// add rumour sighting to database
						$latLong = retrieveSingleFromDB('rumour_sightings', null, array('country_id'=>@$_POST['country_heard'], 'city'=>@$_POST['city_heard']), null, null, null, "latitude <> 0 AND longitude <> 0");
						if (!count($latLong)) $latLong = retrieveSingleFromDB('rumours', null, array('country_id'=>@$_POST['country_heard'], 'city'=>@$_POST['city_heard']), null, null, null, "latitude <> 0 AND longitude <> 0");
						
						$sightingID = insertIntoDb('rumour_sightings', array('created_by'=>$heardBy, 'rumour_id'=>$operators->firstTrue(@$rumourID, @$rumour[0]['rumour_id']), 'entered_by'=>$logged_in['user_id'], 'entered_on'=>date('Y-m-d H:i:s'), 'heard_on'=>$_POST['heard_on'], 'country_id'=>@$_POST['country_heard'], 'city'=>@$_POST['city_heard'], 'latitude'=>@$latLong[0]['latitude'], 'longitude'=>@$latLong[0]['longitude'], 'location_type'=>$_POST['location_type'], 'source_id'=>@$_POST['source_id'], 'ipv4'=>@$ipv4, 'ipv6'=>@$ipv6));
						
				}
				
			// notify moderator
				if (!$pageError) {
					$moderators = retrieveUsers(array('is_moderator'=>'1', 'ok_to_contact'=>'1'), null, $tablePrefix . "users.email != ''");
					if (count($moderators) < 1) {
						$activity = "Added a rumour, but no moderator has been designated to assign it.";
						$logger->logItInDb($activity, null, null, array('error'=>'1', 'resolved'=>'0'));
					}
					else {
						for ($counter = 0; $counter < count($moderators); $counter++) {
							$success = notifyOfRumour($moderators[$counter]['full_name'], $moderators[$counter]['email'], $operators->firstTrue(@$newPublicID, @$rumour[0]['public_id']), $_POST['description']);
							if (!$success) {
								$activity = "Unable to email " . $moderators[$counter]['full_name'] . " (" . $moderators[$counter]['email'] . ") of a new rumour with rumour_id " . $rumourID . " (" . $operators->firstTrue(@$newPublicID, @$rumour[0]['public_id']) . "):" . $_POST['description'];
								$logger->logItInDb($activity);
							}
						}
					}
				}

			// notify assignee
				if (!$pageError && @$_POST['assigned_to']) {
					if ($_POST['assigned_to'] != $rumour[0]['assigned_to']) {
						$assignedTo = retrieveUsers(array($tablePrefix . 'users.user_id'=>$_POST['assigned_to'], 'ok_to_contact'=>'1'), null, $tablePrefix . "users.email != ''", null, 1);
						if (count($assignedTo)) notifyOfRumour($assignedTo[0]['full_name'], $assignedTo[0]['email'], $operators->firstTrue(@$newPublicID, @$rumour[0]['public_id']), $_POST['description'], true);
					}
				}

			// update log
				if (!$pageError) {
					if ($matchingRumour) {
						$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has added a sighting for rumour_id " . $rumour[0]['rumour_id'] . ": " . $rumour[0]['description'];
						$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'user_id=' . $heardBy, 'rumour_id=' . $rumour[0]['rumour_id'], 'sighting_id=' . $sightingID));
					}
					else {
						$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has added rumour_id " . $rumourID . ": " . $_POST['description'];
						$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'user_id=' . $heardBy, 'rumour_id=' . $rumourID, 'sighting_id=' . $sightingID));
					}
				}
				
			// redirect
				if (!$pageError) {
					header ('Location: /rumour/' . $operators->firstTrue(@$newPublicID, @$rumour[0]['public_id']) . '/' . $parser->seoFriendlySuffix($_POST['description']) . '/page=1/rumour_added');
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