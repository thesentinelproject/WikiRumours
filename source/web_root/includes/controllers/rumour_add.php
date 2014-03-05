<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in) forceLoginThenRedirectHere();
		
	// initialize
		$step = max(@$_POST['step'], 1);
		$matchingRumour = @$_POST['rumour_id'];

	// instantiate required class(es)
		$operators = new operators_TL();
		$parser = new parser_TL();
		$validator = new inputValidator_TL();
		$urlShortener = new urlShortener_TL();
		
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
			$suggestedTags = null;
			$result = retrieveFromDb('tags', null, null, null, null, $otherCriteria, 'tag ASC');
			for ($counter = 0; $counter < count($result); $counter++) {
				$suggestedTags[$result[$counter]['tag_id']] = $result[$counter]['tag'];
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
				if (!$countriesShort_TL[@$_POST['country_occurred']]) $pageError .= "Please specify a country. ";

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
					$matches = retrieveFromDb('rumours', array('country'=>$_POST['country_occurred']), null, null, null, $otherCriteria, 'updated_on DESC', $numberOfResultsToReturn);

					if (count($matches) > 0) $step = 2;
					else $step = 3;
					
				}
			
		}
		elseif ($_POST['formName'] == 'addRumourForm' && $_POST['step'] == 2) {
			
			if ($matchingRumour) {
				$rumour = retrieveFromDb('rumours', array('public_id'=>$matchingRumour, 'enabled'=>1), null, null, null, null, null, 1);
				if (count($rumour) == 1) $step = 3;
			}
			else $step = 3;
			
		}
		elseif ($_POST['formName'] == 'addRumourForm' && $_POST['step'] == 3) {
			
			// clean input
				$_POST = $parser->trimAll($_POST);
				$_POST['description'] = htmlspecialchars_decode(@$_POST['description'], ENT_QUOTES);
				$_POST['description'] = $parser->removeHTML($_POST['description']);
				$checkboxesToParse = array('newuser_ok_to_contact', 'newuser_ok_to_show_profile');
				foreach ($checkboxesToParse as $checkbox) {
					if (isset($_POST[$checkbox])) $_POST[$checkbox] = 1;
					else $_POST[$checkbox] = 0;
				}
				$_POST['additional_tags'] = $parser->removeHTML($_POST['additional_tags']);
				$_POST['additional_tags'] = str_replace('"', '', $_POST['additional_tags']);
				$_POST['additional_tags'] = str_replace(',', ' ', $_POST['additional_tags']);
				$_POST['additional_tags'] = str_replace(';', ' ', $_POST['additional_tags']);
				$_POST['additional_tags'] = str_replace('  ', ' ', $_POST['additional_tags']);
				$_POST['additional_tags'] = $parser->includeOrExcludeCharacters($_POST['additional_tags'], 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_ ');
				if (!@$_POST['source']) $_POST['source'] = 'w';
				
			// check for errors
				if (!$_POST['description']) $pageError .= "Please enter a rumour. ";
				if (!$countriesShort_TL[$_POST['country_occurred']]) $pageError .= "Please specify the country where occurred. ";
				if (!$countriesShort_TL[$_POST['country_heard']]) $pageError .= "Please specify the country where heard. ";
				if (!$_POST['heard_on']) $pageError .= "Please specify the date heard. ";
				
				if ($logged_in['is_proxy']) {
					if ($_POST['created_by']) {
						if ($_POST['newuser_first_name'] || $_POST['newuser_last_name'] || $_POST['newuser_email'] || $_POST['newuser_phone'] || $_POST['newuser_secondary_phone'] || $_POST['newuser_sms_notifications'] || $_POST['newuser_country']) $pageError .= "Please either select an existing user or add a new users; you cannot do both. ";
					}
					else {
						if ($_POST['newuser_email'] && !$validator->validateEmailRobust($_POST['newuser_email'])) $pageError .= "Please specify a valid email address for the new user. ";
					}
				}
								
				if ($matchingRumour) {
					$rumour = retrieveFromDb('rumours', array('public_id'=>$matchingRumour, 'enabled'=>1), null, null, null, null, null, 1);
					if (count($rumour) <> 1) $pageError .= "Unable to retrieve existing rumour. ";
				}					
				
			// add rumour
				if (!$pageError) {
					
					// create encoded IP
						if (strlen($_SERVER['REMOTE_ADDR']) > 15) $ipv6 = $parser->encodeIP($_SERVER['REMOTE_ADDR'], 'ipv6');
						elseif (strlen($_SERVER['REMOTE_ADDR']) > 0) $ipv4 = $parser->encodeIP($_SERVER['REMOTE_ADDR'], 'ipv4');
						
					// determine creator of rumour and sighting
						if ($logged_in['is_proxy']) {
							if ($_POST['created_by']) $createdBy = $_POST['created_by'];
							else {
								// create new user
									$newUsername = null;
									while ($newUsername == null) {
										$newUsername = rand(1000000,9999999);
										$doesUsernameExist = countInDb('users', 'username', array('username'=>$newUsername));
										if ($doesUsernameExist[0]['count'] > 0) $newUsername = null;
									}
									$createdBy = insertIntoDb('users', array('username'=>$newUsername, 'first_name'=>$_POST['newuser_first_name'], 'last_name'=>$_POST['newuser_last_name'], 'country'=>$_POST['newuser_country'], 'email'=>$_POST['newuser_email'], 'phone'=>$_POST['newuser_phone'], 'secondary_phone'=>$_POST['newuser_secondary_phone'], 'sms_notifications'=>$_POST['newuser_sms_notifications'], 'ok_to_contact'=>$_POST['newuser_ok_to_contact'], 'ok_to_show_profile'=>$_POST['newuser_ok_to_show_profile'], 'registered_on'=>date('Y-m-d H:i:s'), 'registered_by'=>$logged_in['user_id']));
							}
						}
						else $createdBy = $logged_in['user_id'];
						
					// add rumour to database, if necessary
						if (!$matchingRumour) {
							// create publicID
								$newPublicID = null;
								while ($newPublicID == null) {
									$newPublicID = $urlShortener->customAlphaID('a', 6, null, true, true);
									$doesPublicIdExist = countInDb('rumours', 'public_id', array('public_id'=>$newPublicID));
									if ($doesPublicIdExist[0]['count'] > 0) $newPublicID = null;
								}

							// add rumour
								$rumourID = insertIntoDb('rumours', array('public_id'=>$newPublicID, 'description'=>$_POST['description'], 'country'=>$_POST['country_occurred'], 'region'=>$_POST['region_occurred'], 'occurred_on'=>$_POST['occurred_on'], 'created_by'=>$createdBy, 'created_on'=>date('Y-m-d H:i:s'), 'updated_on'=>date('Y-m-d H:i:s'), 'entered_by'=>$logged_in['user_id'], 'status'=>'NU'));
								if (!$rumourID) $pageError .= "Unable to add rumour for some reason. ";
								else {
									
									// add existing tags
										if ($_POST['tags']) {
											foreach ($_POST['tags'] as $tagID) {
												if ($tagID) insertIntoDb('rumours_x_tags', array('rumour_id'=>$rumourID, 'tag_id'=>$tagID, 'added_by'=>$logged_in['user_id'], 'added_on'=>date('Y-m-d H:i:s')));
											}
										}
									// add additional tags
										$additionalTags = explode(' ', $_POST['additional_tags']);
										foreach ($additionalTags as $tag) {
											$tag = trim($tag);
											if ($tag) {
												$doesTagExist = retrieveFromDb('tags', array('tag'=>$tag), null, null, null, null, null, 1);
												if (count($doesTagExist)) $tagID = $doesTagExist[0]['tag_id'];
												else $tagID = insertIntoDb('tags', array('tag'=>$tag, 'created_by'=>$logged_in['user_id'], 'created_on'=>date('Y-m-d H:i:s')));
												insertIntoDb('rumours_x_tags', array('rumour_id'=>$rumourID, 'tag_id'=>$tagID, 'added_by'=>$logged_in['user_id'], 'added_on'=>date('Y-m-d H:i:s')));
											}
										}
										
								}
										
						}

					// add rumour sighting to database
						insertIntoDb('rumour_sightings', array('created_by'=>$createdBy, 'rumour_id'=>$operators->firstTrue(@$rumourID, $rumour[0]['rumour_id']), 'entered_by'=>$logged_in['user_id'], 'entered_on'=>date('Y-m-d H:i:s'), 'heard_on'=>$_POST['heard_on'], 'country'=>@$_POST['country_heard'], 'region'=>@$_POST['region_heard'], 'source'=>@$_POST['source'], 'ipv4'=>@$ipv4, 'ipv6'=>@$ipv6));
						
				}
				
			// notify moderator
				if (!$pageError) {
					$moderators = retrieveUsers(array('is_moderator'=>'1', 'ok_to_contact'=>'1'), null, $tablePrefix . "users.email != ''");
					if (count($moderators) < 1) {
						$activity = "Added a rumour, but no moderator has been designated to assign it.";
						$logger->logItInDb($activity, null, array('error'=>'1', 'resolved'=>'0'));
					}
					else {
						for ($counter = 0; $counter < count($moderators); $counter++) {
							$success = notifyOfRumour($moderators[$counter]['full_name'], $moderators[$counter]['email'], $operators->firstTrue(@$newPublicID, @$rumour[0]['public_id']), $_POST['description']);
							if (!$success) {
								$activity = "Unable to email " . $moderators[$counter]['full_name'] . " (" . $moderators[$counter]['email'] . ") of a new rumour with rumour_id " . $rumourID;
								$logger->logItInDb($activity);
							}
						}
					}
				}

			// update log
				if ($matchingRumour) $activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has added a sighting for rumour_id " . $rumour[0]['rumour_id'] . ": " . $rumour[0]['description'];
				else $activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has added rumour_id " . $rumourID . ": " . $_POST['description'];
				$logger->logItInDb($activity);
				
			// redirect
				if (!$pageError) {
					header ('Location: /rumour/' . $operators->firstTrue(@$newPublicID, @$rumour[0]['public_id']) . '/' . $parser->seoFriendlySuffix($_POST['description']));
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