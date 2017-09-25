<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$publicID = $tl->page['parameter1'];
		if (!$publicID) $authentication_manager->forceRedirect('/404');
		
		if ($tl->page['parameter3']) $filters = $keyvalue_array->keyValueToArray(urldecode($tl->page['parameter3']), '|');
		
	// clean input
		if (@$filters) {
			$allowableFilters = array('page', 'view');
			foreach ($filters as $key=>$value) {
				if (!in_array($value, $allowableFilters)) unset($filters[$value]);
			}
		}
		
		if (@$filters['view'] != 'sightings' && @$filters['view'] != 'comments') $filters['view'] = 'rumour';
		
	// queries
		if ($logged_in['is_moderator'] || $logged_in['is_administrator']) $rumour = retrieveRumours(array('public_id'=>$publicID), null, null, null, 1);
		else $rumour = retrieveRumours(array('public_id'=>$publicID, $tablePrefix . 'rumours.enabled'=>1), null, null, null, 1);
		if (count($rumour) < 1) $authentication_manager->forceRedirect('/404');
		
		// tags
			$tags = retrieveTags(array('rumour_id'=>$rumour[0]['rumour_id']), null, null, $tablePrefix . 'tags.tag ASC');

		// sightings		
			$sightings = retrieveSightings(array($tablePrefix . 'rumour_sightings.rumour_id'=>$rumour[0]['rumour_id']), null, null, $tablePrefix . 'rumour_sightings.heard_on DESC');

		// watchlisted?
			$result = countInDb('watchlist', 'created_by', array('rumour_id'=>$rumour[0]['rumour_id'], 'created_by'=>$logged_in['user_id']));
			if ($result[0]['count'] > 0) $hasBeenWatchlisted = true;
			else $hasBeenWatchlisted = false;

		// comments				
			$result = countInDb('comments', 'comment_id', array('rumour_id'=>$rumour[0]['rumour_id']));
			$numberOfComments = $result[0]['count'];
			$numberOfCommentsPerPage = 15;
			$numberOfPages = max(1, ceil($numberOfComments / $numberOfCommentsPerPage));
			if (@$filters['page'] < 1) $filters['page'] = 1;
			elseif ($filters['page'] > $numberOfPages) $filters['page'] = $numberOfPages;

			$comments = retrieveComments(array($tablePrefix . 'comments.rumour_id'=>$rumour[0]['rumour_id']), null, null, $tablePrefix . 'comments.created_on DESC', floatval(($filters['page'] * $numberOfCommentsPerPage) - $numberOfCommentsPerPage) . ',' . $numberOfCommentsPerPage);
	
		// misc		
			$allUsers = array();
			$result = retrieveUsers(array('enabled'=>1), null, null, 'rumours_created DESC, anonymous ASC');
			for ($counter = 0; $counter < count($result); $counter++) {
				if ($result[$counter]['anonymous']) $allUsers[$result[$counter]['user_id']] = "Anonymous (" . $result[$counter]['username'] . ")";
				else {
					$allUsers[$result[$counter]['user_id']] = $result[$counter]['username'];
					if ($result[$counter]['full_name']) $allUsers[$result[$counter]['user_id']] .= " (" . $result[$counter]['full_name'] . ")";
				}
			}

			$allTags = array();
			foreach ($rumourTags as $id=>$tag) {
				if (trim($id) && trim($tag)) $allTags[$tag] = $tag;
			}

			if (!$rumour[0]['enabled']) $tl->page['warning'] = "This rumour is disabled.";

			if (file_exists('uploads/rumour_attachments/' . $publicID)) $attachments = $directory_manager->read('uploads/rumour_attachments/' . $publicID, false, false, true);
			
	if (@$filters['view'] == 'sightings') $tl->page['events'] = "populateMap();";
	$tl->page['description'] = $rumour[0]['description'];

/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */

	if (count($_POST) > 0) {

		$tl->page['error'] = null;
		
		if ($_POST['formName'] == 'editTagsForm' && $_POST['tagToRemove'] && $logged_in) {
			
			// validate tag
				$tag = retrieveSingleFromDb('tags', null, array('tag_id'=>$_POST['tagToRemove']));
				if (count($tag) <> 1) $tl->page['error'] .= "Unknown error attempting to remove tag. ";
				else {

					// delete association
						deleteFromDb('rumours_x_tags', array('tag_id'=>$_POST['tagToRemove'], 'rumour_id'=>$rumour[0]['rumour_id']), null, null, null, null, 1);
						
					// check if tag still used, and if not remove it
						$anyOtherRumours = retrieveSingleFromDb('rumours_x_tags', null, array('tag_id'=>$_POST['tagToRemove']));
						if (count($anyOtherRumours) < 1) deleteFromDb('tags', array('tag_id'=>$_POST['tagToRemove']), null, null, null, null, 1);
		
					// update log
						$activity = $logged_in['full_name'] . " has removed the tag &quot;" . $tag[0]['tag'] . "&quot; from the rumour &quot;" . $rumour[0]['description'] . "&quot;";
						$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'rumour_id=' . $rumour[0]['rumour_id'], 'tag_id=' . $_POST['tagToRemove']));

						$attributableOutput = $attributable->capture($activity, null, ['user_id'=>$logged_in['user_id'], 'first_name'=>$logged_in['first_name'], 'last_name'=>$logged_in['last_name'], 'email'=>$logged_in['email'], 'phone'=>$logged_in['primary_phone']], ['rumour_id'=>$rumour[0]['rumour_id'], 'tag_id'=>$_POST['tagToRemove']]);
						if (!@count($attributableOutput['content']['success'])) emailSystemNotification(__FILE__ . ": " . (is_array($attributableOutput) ? print_r($attributableOutput, true) : $attributableOutput) . (@$logged_in ? " [" . $logged_in['username'] . "]" : false), 'Attributable failure');
						
					// redirect
						$authentication_manager->forceRedirect('/rumour/' . $publicID . '/' . $parser->seoFriendlySuffix($rumour[0]['description']) . '/' . $keyvalue_array->updateKeyValue($tl->page['parameter3'], 'view', 'rumour', '|') . '/success=tag_removed');
					
				}
				
		}
		elseif ($_POST['formName'] == 'editTagsForm' && $logged_in) {
			
			// clean input
				$_POST = $parser->trimAll($_POST);
				for ($counter = 0; $counter < count($_POST['new_tags']); $counter++) {
					$_POST['new_tags'][$counter] = $parser->removeHTML($_POST['new_tags'][$counter]);
					$_POST['new_tags'][$counter] = $parser->includeOrExcludeCharacters($_POST['new_tags'][$counter], 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_ ');
				}
				
			// check for errors
				if (!$_POST['new_tags']) $tl->page['error'] .= "Please specify one or more tags. ";
				else {

					for ($counter = 0; $counter < count($_POST['new_tags']); $counter++) {

						// retrieve tagID and add any tags which are unique
							$result = retrieveSingleFromDb('tags', null, array('tag'=>$_POST['new_tags'][$counter]));
							if (count($result)) $tagID = $result[0]['tag_id'];
							else $tagID = insertIntoDb('tags', array('tag'=>$_POST['new_tags'][$counter], 'created_by'=>$logged_in['user_id'], 'created_on'=>date('Y-m-d H:i:s')));

						// associate tag
							deleteFromDb('rumours_x_tags', array('tag_id'=>$tagID, 'rumour_id'=>$rumour[0]['rumour_id']), null, null, null, null, 1);
							insertIntoDb('rumours_x_tags', array('tag_id'=>$tagID, 'rumour_id'=>$rumour[0]['rumour_id'], 'added_by'=>$logged_in['user_id'], 'added_on'=>date('Y-m-d H:i:s')));

						// update log
							$activity = $logged_in['full_name'] . " has added the tag &quot;" . $_POST['new_tags'][$counter] . "&quot; to the rumour &quot;" . $rumour[0]['description'] . "&quot;";
							$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'rumour_id=' . $rumour[0]['rumour_id'], 'tag_id=' . $tagID));

							$attributableOutput = $attributable->capture($activity, null, ['user_id'=>$logged_in['user_id'], 'first_name'=>$logged_in['first_name'], 'last_name'=>$logged_in['last_name'], 'email'=>$logged_in['email'], 'phone'=>$logged_in['primary_phone']], ['rumour_id'=>$rumour[0]['rumour_id'], 'tag_id'=>$tagID]);
							if (!@count($attributableOutput['content']['success'])) emailSystemNotification(__FILE__ . ": " . (is_array($attributableOutput) ? print_r($attributableOutput, true) : $attributableOutput) . (@$logged_in ? " [" . $logged_in['username'] . "]" : false), 'Attributable failure');
							
					}

					// redirect
						$authentication_manager->forceRedirect('/rumour/' . $publicID . '/' . $parser->seoFriendlySuffix($rumour[0]['description']) . '/' . $keyvalue_array->updateKeyValue($tl->page['parameter3'], 'view', 'rumour', '|') . '/success=tags_updated');

				}

		}
		elseif ($_POST['formName'] == 'moderateSightingsForm' && $_POST['sightingToRemove']) {
			
			// authenticate
				$sighting = retrieveSightings(array('sighting_id'=>$_POST['sightingToRemove']), null, null, null, 1);
				if (count($sighting) <> 1) $tl->page['error'] .= "Unknown error attempting to remove sighting. ";
				elseif (($logged_in['is_administrator'] && $logged_in['can_edit_content']) || ($sighting[0]['created_by'] == $logged_in['user_id'] || $sighting[0]['entered_by'] == $logged_in['user_id'])) {

					// remove sighting
						deleteFromDb('rumour_sightings', array('sighting_id'=>$_POST['sightingToRemove']), null, null, null, null, 1);
		
					// update log
						$activity = $logged_in['full_name'] . " has removed a sighting from the rumour &quot;" . $rumour[0]['description'] . "&quot;";
						$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'rumour_id=' . $rumour[0]['rumour_id'], 'sighting_id=' . $_POST['sightingToRemove']));
						
						$attributableOutput = $attributable->capture($activity, null, ['user_id'=>$logged_in['user_id'], 'first_name'=>$logged_in['first_name'], 'last_name'=>$logged_in['last_name'], 'email'=>$logged_in['email'], 'phone'=>$logged_in['primary_phone']], ['rumour_id'=>$rumour[0]['rumour_id'], 'sighting_id'=>$_POST['sightingToRemove']]);
						if (!@count($attributableOutput['content']['success'])) emailSystemNotification(__FILE__ . ": " . (is_array($attributableOutput) ? print_r($attributableOutput, true) : $attributableOutput) . (@$logged_in ? " [" . $logged_in['username'] . "]" : false), 'Attributable failure');

					// redirect
						$authentication_manager->forceRedirect('/rumour/' . $publicID . '/' . $parser->seoFriendlySuffix($rumour[0]['description']) . '/' . $keyvalue_array->updateKeyValue($tl->page['parameter3'], 'view', 'sightings', '|') . '/success=sighting_removed');

				}
				else $tl->page['error'] .= "You don't appear to be authorized to delete a sighting. ";
				
		}
		elseif ($_POST['formName'] == 'addSightingForm' && $logged_in) {
			
			// clean input
				$_POST = $parser->trimAll($_POST);
				$checkboxesToParse = array('newuser_ok_to_contact', 'newuser_anonymous');
				foreach ($checkboxesToParse as $checkbox) {
					if (isset($_POST[$checkbox])) $_POST[$checkbox] = 1;
					else $_POST[$checkbox] = 0;
				}
				
			// check for errors
				if (!@$_POST['country']) $tl->page['error'] .= "Please specify a country. ";
				if (!@$_POST['heard_on']) $tl->page['error'] .= "Please specify a date. ";
				if (!$_POST['source_id']) $_POST['source_id'] = 1; // set source to "Internet"
				if ($logged_in['is_proxy']) {
					if (!@$_POST['created_by']) $tl->page['error'] .= "Please specify who heard the rumour. ";
					elseif (@$_POST['created_by'] == 'add') {
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
					$sightingPublicID = null;
					while ($sightingPublicID == null) {
						$sightingPublicID = $link->customAlphaID('a', 6, null, true, true);
						$doesPublicIdExist = countInDb('rumour_sightings', 'public_id', array('public_id'=>$sightingPublicID));
						if ($doesPublicIdExist[0]['count'] > 0) $sightingPublicID = null;
					}

				// determine attribution
					if ($logged_in['is_proxy']) {
						if ($_POST['created_by'] != 'add') $createdBy = $_POST['created_by'];
						else $createdBy = insertIntoDb('users', array('first_name'=>$_POST['newuser_first_name'], 'last_name'=>$_POST['newuser_last_name'], 'username'=>$_POST['newuser_username'], 'email'=>$_POST['newuser_email'], 'primary_phone'=>$_POST['newuser_primary_phone'], 'primary_phone_sms'=>$_POST['newuser_primary_phone_sms'], 'secondary_phone'=>$_POST['newuser_secondary_phone'], 'secondary_phone_sms'=>$_POST['newuser_secondary_phone_sms'], 'country_id'=>$_POST['newuser_country'], 'ok_to_contact'=>$_POST['newuser_ok_to_contact'], 'anonymous'=>$_POST['newuser_anonymous'], 'registered_on'=>date('Y-m-d H:i:s'), 'registered_by'=>$logged_in['user_id']));
					}
					else $createdBy = $logged_in['user_id'];

				// faux geocode
					$latLong = retrieveSingleFromDB('rumour_sightings', null, array('country_id'=>@$_POST['country'], 'city'=>@$_POST['city']), null, null, null, "latitude <> 0 AND longitude <> 0");
					if (!count($latLong)) $latLong = retrieveSingleFromDB('rumours', null, array('country_id'=>@$_POST['country'], 'city'=>@$_POST['city']), null, null, null, "latitude <> 0 AND longitude <> 0");

				// save sighting
					$sightingID = insertIntoDb('rumour_sightings', array('public_id'=>$sightingPublicID, 'rumour_id'=>$rumour[0]['rumour_id'], 'created_by'=>$createdBy, 'entered_by'=>$logged_in['user_id'], 'entered_on'=>date('Y-m-d H:i:s'), 'source_id'=>$_POST['source_id'], 'ipv4'=>@$ipv4, 'ipv6'=>@$ipv6, 'country_id'=>$_POST['country'], 'city'=>@$_POST['city'], 'location_type'=>@$_POST['location_type'], 'latitude'=>@$latLong[0]['latitude'], 'longitude'=>@$latLong[0]['longitude'], 'heard_on'=>$_POST['heard_on']));
										
				// update log
					$activity = $logged_in['full_name'] . " has added a sighting to the rumour &quot;" . $rumour[0]['description'] . "&quot;";
					$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'rumour_id=' . $rumour[0]['rumour_id'], 'sighting_id=' . $sightingID));

					$attributableOutput = $attributable->capture($activity, null, ['user_id'=>$logged_in['user_id'], 'first_name'=>$logged_in['first_name'], 'last_name'=>$logged_in['last_name'], 'email'=>$logged_in['email'], 'phone'=>$logged_in['primary_phone']], ['rumour_id'=>$rumour[0]['rumour_id'], 'sighting_id'=>$sightingID, 'domain_alias_id'=>@$tl->page['domain_alias']['cms_id']]);
					if (!@count($attributableOutput['content']['success'])) emailSystemNotification(__FILE__ . ": " . (is_array($attributableOutput) ? print_r($attributableOutput, true) : $attributableOutput) . (@$logged_in ? " [" . $logged_in['username'] . "]" : false), 'Attributable failure');
					
					$sightings = retrieveSightings(['domain_alias_id'=>@$tl->page['domain_alias']['cms_id']]);
					$attributableOutput = $attributable->measure(trim(@$tl->page['domain_alias']['title'] . " Sightings"), "=" . count($sightings));

				// redirect
					$authentication_manager->forceRedirect('/rumour/' . $publicID . '/' . $parser->seoFriendlySuffix($rumour[0]['description']) . '/' . $keyvalue_array->updateKeyValue($tl->page['parameter3'], 'view', 'sightings', '|') . '/success=sighting_added');
			}
			
		}
		elseif ($_POST['formName'] == 'rumourActionsForm' && $_POST['addToWatchlist'] == 'Y' && $logged_in) {
			
			$alreadyWatchlisted = retrieveSingleFromDb('watchlist', null, array('rumour_id'=>$rumour[0]['rumour_id'], 'created_by'=>$logged_in['user_id']));
			if (count($alreadyWatchlisted) > 0) $tl->page['error'] .= "This rumour is already in your watchlist. ";
			else {
				insertIntoDb('watchlist', array('rumour_id'=>$rumour[0]['rumour_id'], 'notify_of_updates'=>'1', 'created_by'=>$logged_in['user_id'], 'created_on'=>date('Y-m-d H:i:s')));
				$authentication_manager->forceRedirect('/rumour/' . $publicID . '/' . $parser->seoFriendlySuffix($rumour[0]['description']) . '/' . $tl->page['parameter3'] . '/success=added_to_watchlist');
			}
			
		}
		elseif ($_POST['formName'] == 'rumourActionsForm' && $_POST['removeFromWatchlist'] == 'Y' && $logged_in) {
			
			$success = deleteFromDb('watchlist', array('rumour_id'=>$rumour[0]['rumour_id'], 'created_by'=>$logged_in['user_id']), null, null, null, null, 1);
			if (!$success) $tl->page['error'] .= "This rumour wasn't found in your watchlist. ";
			else $authentication_manager->forceRedirect('/rumour/' . $publicID . '/' . $parser->seoFriendlySuffix($rumour[0]['description']) . '/' . $keyvalue_array->updateKeyValue($tl->page['parameter3'], 'view', 'rumour', '|') . '/success=removed_from_watchlist');
			
		}
		elseif ($_POST['formName'] == 'addCommentForm' && $logged_in) {

			// clean input
				$_POST = $parser->trimAll($_POST);
				$_POST['new_comment'] = $parser->removeHTML($_POST['new_comment']);
				
			// check for errors
				if (!$_POST['new_comment']) $tl->page['error'] .= "Please provide a comment. ";
				
			if (!$tl->page['error']) {
				
				// add to database
					$commentID = insertIntoDb('comments', array('rumour_id'=>$rumour[0]['rumour_id'], 'comment'=>$_POST['new_comment'], 'created_by'=>$logged_in['user_id'], 'created_on'=>date('Y-m-d H:i:s')));
					if (!$commentID) $tl->page['error'] .= "Unable to add comment for some reason. ";
					else {

						// update rumour
							updateDb('rumours', array('updated_on'=>date('Y-m-d H:i:s')), array('rumour_id'=>$rumour[0]['rumour_id']), null, null, null, null, 1);

						// watchlist notifications (email)
							$notify = retrieveWatchlist(array($tablePrefix . 'watchlist.rumour_id'=>$rumour[0]['rumour_id'], 'notify_of_updates'=>'1'), null, $tablePrefix . "users.email != '' AND " . $tablePrefix . "users.ok_to_contact = '1'");
							for ($counter = 0; $counter < count($notify); $counter++) {
								$success = notifyUserOfRumourComment($notify[$counter]['full_name'], $notify[$counter]['email'], $rumour[0]['public_id'], $rumour[0]['description'], $_POST['new_comment'], $logged_in['username']);
								if (!$success) {
									$activity = "Unable to email " . $notify[$counter]['full_name'] . " (" . $notify[$counter]['email'] . ") of a new comment on rumour_id " . $rumour[0]['rumour_id'];
									$logger->logItInDb($activity);
								}
							}

						// update log
							$activity = $logged_in['full_name'] . " has added a comment to the rumour &quot;" . $rumour[0]['description'] . "&quot;";
							$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'rumour_id=' . $rumour[0]['rumour_id'], 'comment_id=' . $commentID));

							$attributableOutput = $attributable->capture($activity, null, ['user_id'=>$logged_in['user_id'], 'first_name'=>$logged_in['first_name'], 'last_name'=>$logged_in['last_name'], 'email'=>$logged_in['email'], 'phone'=>$logged_in['primary_phone']], ['rumour_id'=>$rumour[0]['rumour_id'], 'comment_id'=>$commentID, 'domain_alias_id'=>@$tl->page['domain_alias']['cms_id']]);
							if (!@count($attributableOutput['content']['success'])) emailSystemNotification(__FILE__ . ": " . (is_array($attributableOutput) ? print_r($attributableOutput, true) : $attributableOutput) . (@$logged_in ? " [" . $logged_in['username'] . "]" : false), 'Attributable failure');
							
						// redirect
							$authentication_manager->forceRedirect('/rumour/' . $publicID . '/' . $parser->seoFriendlySuffix($rumour[0]['description']) . '/' . $keyvalue_array->updateKeyValue($tl->page['parameter3'], 'view', 'comments', '|') . '/success=comment_added');
						
					}

			}
				
				
		}
		
		elseif ($_POST['formName'] == 'moderateCommentsForm' && $_POST['commentToFlag'] && $logged_in) {
			
			// flag
				deleteFromDb('comment_flags', array('comment_id'=>$_POST['commentToFlag'], 'flagged_by'=>$logged_in['user_id']), null, null, null, null, 1);
				insertIntoDb('comment_flags', array('comment_id'=>$_POST['commentToFlag'], 'flagged_by'=>$logged_in['user_id'], 'flagged_on'=>date('Y-m-d H:i:s')));

			// update log
				$activity = $logged_in['full_name'] . " has flagged a comment on the rumour &quot;" . $rumour[0]['description'] . "&quot;";
				$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'rumour_id=' . $rumour[0]['rumour_id'], 'comment_id=' . $_POST['commentToFlag']));

				$attributableOutput = $attributable->capture($activity, null, ['user_id'=>$logged_in['user_id'], 'first_name'=>$logged_in['first_name'], 'last_name'=>$logged_in['last_name'], 'email'=>$logged_in['email'], 'phone'=>$logged_in['primary_phone']], ['rumour_id'=>$rumour[0]['rumour_id'], 'comment_id'=>$_POST['commentToFlag'], 'domain_alias_id'=>@$tl->page['domain_alias']['cms_id']]);
				if (!@count($attributableOutput['content']['success'])) emailSystemNotification(__FILE__ . ": " . (is_array($attributableOutput) ? print_r($attributableOutput, true) : $attributableOutput) . (@$logged_in ? " [" . $logged_in['username'] . "]" : false), 'Attributable failure');
				
			// redirect
				$authentication_manager->forceRedirect('/rumour/' . $publicID . '/' . $parser->seoFriendlySuffix($rumour[0]['description']) . '/' . $keyvalue_array->updateKeyValue($tl->page['parameter3'], 'view', 'comments', '|') . '/success=comment_flagged');
				
		}

		elseif ($_POST['formName'] == 'moderateCommentsForm' && $_POST['commentToDisable'] && $logged_in) {
			
			// disable
				updateDb('comments', array('enabled'=>'0'), array('comment_id'=>$_POST['commentToDisable']), null, null, null, null, 1);

			// update log
				$activity = $logged_in['full_name'] . " has disabled a comment on the rumour &quot;" . $rumour[0]['description'] . "&quot;";
				$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'rumour_id=' . $rumour[0]['rumour_id'], 'comment_id=' . $_POST['commentToDisable']));

				$attributableOutput = $attributable->capture($activity, null, ['user_id'=>$logged_in['user_id'], 'first_name'=>$logged_in['first_name'], 'last_name'=>$logged_in['last_name'], 'email'=>$logged_in['email'], 'phone'=>$logged_in['primary_phone']], ['rumour_id'=>$rumour[0]['rumour_id'], 'comment_id'=>$_POST['commentToDisable'], 'domain_alias_id'=>@$tl->page['domain_alias']['cms_id']]);
				if (!@count($attributableOutput['content']['success'])) emailSystemNotification(__FILE__ . ": " . (is_array($attributableOutput) ? print_r($attributableOutput, true) : $attributableOutput) . (@$logged_in ? " [" . $logged_in['username'] . "]" : false), 'Attributable failure');
				
			// redirect
				$authentication_manager->forceRedirect('/rumour/' . $publicID . '/' . $parser->seoFriendlySuffix($rumour[0]['description']) . '/' . $keyvalue_array->updateKeyValue($tl->page['parameter3'], 'view', 'comments', '|') . '/success=comment_disabled');
				
		}
		
		elseif ($_POST['formName'] == 'moderateCommentsForm' && $_POST['commentToEnable'] && $logged_in) {
			
			// enable
				updateDb('comments', array('enabled'=>'1'), array('comment_id'=>$_POST['commentToEnable']), null, null, null, null, 1);

			// update log
				$activity = $logged_in['full_name'] . " has re-enabled a comment on the rumour &quot;" . $rumour[0]['description'] . "&quot;";
				$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'rumour_id=' . $rumour[0]['rumour_id'], 'comment_id=' . $_POST['commentToEnable']));

				$attributableOutput = $attributable->capture($activity, null, ['user_id'=>$logged_in['user_id'], 'first_name'=>$logged_in['first_name'], 'last_name'=>$logged_in['last_name'], 'email'=>$logged_in['email'], 'phone'=>$logged_in['primary_phone']], ['rumour_id'=>$rumour[0]['rumour_id'], 'comment_id'=>$_POST['commentToEnable'], 'domain_alias_id'=>@$tl->page['domain_alias']['cms_id']]);
				if (!@count($attributableOutput['content']['success'])) emailSystemNotification(__FILE__ . ": " . (is_array($attributableOutput) ? print_r($attributableOutput, true) : $attributableOutput) . (@$logged_in ? " [" . $logged_in['username'] . "]" : false), 'Attributable failure');
				
			// redirect
				$authentication_manager->forceRedirect('/rumour/' . $publicID . '/' . $parser->seoFriendlySuffix($rumour[0]['description']) . '/' . $keyvalue_array->updateKeyValue($tl->page['parameter3'], 'view', 'comments', '|') . '/success=comment_enabled');
				
		}
		
	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>