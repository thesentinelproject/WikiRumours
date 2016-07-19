<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$publicID = $tl->page['parameter1'];
		if (!$publicID) $authentication_manager->forceRedirect('/404');
		
	// query
		$rumour = retrieveRumours(array('public_id'=>$publicID), null, null, null, 1);
		if (count($rumour) < 1) $authentication_manager->forceRedirect('/404');
		
	// authenticate user
		if (!$logged_in['is_administrator'] || !$logged_in['can_edit_content']) {
			if (!$logged_in['is_moderator']) {
				if (!$logged_in['is_community_liaison'] || $rumour[0]['assigned_to'] != $logged_in['user_id']) $authentication_manager->forceRedirect('/404');
			}
		}
		
	// tags
		$currentTags = array();
		$result = retrieveTags(array('rumour_id'=>$rumour[0]['rumour_id']), null, null, $tablePrefix . 'tags.tag ASC');
		for ($counter = 0; $counter < count($result); $counter++) {
			$currentTags[$result[$counter]['tag']] = $result[$counter]['tag'];
		}

		$allTags = array();
		foreach ($rumourTags as $id=>$tag) {
			$allTags[$tag] = $tag;
		}
		
	// more queries
	
		$allUsers = array();
		$result = retrieveUsers(array('enabled'=>1));
		for ($counter = 0; $counter < count($result); $counter++) {
			$allUsers[$result[$counter]['user_id']] = $result[$counter]['username'];
			if ($result[$counter]['full_name']) $allUsers[$result[$counter]['user_id']] .= " (" . $result[$counter]['full_name'] . ")";
		}

		$allModeratorsAndCommunityLiaisons = array();
		$result = retrieveUsers(array('enabled'=>1), null, "is_moderator = 1 OR is_community_liaison = 1");
		for ($counter = 0; $counter < count($result); $counter++) {
			$allModeratorsAndCommunityLiaisons[$result[$counter]['user_id']] = $result[$counter]['username'];
			if ($result[$counter]['full_name']) $allModeratorsAndCommunityLiaisons[$result[$counter]['user_id']] .= " (" . $result[$counter]['full_name'] . ")";
		}
		if (!@$allModeratorsAndCommunityLiaisons[$rumour[0]['created_by']]) $allModeratorsAndCommunityLiaisons[$rumour[0]['created_by']] = $rumour[0]['created_by_full_name'];

		if (file_exists('assets/rumour_attachments/' . $publicID)) $attachments = $directory_manager->read('assets/rumour_attachments/' . $publicID, false, false, true);

		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
		
	if (count($_POST) > 0) {
		
		if ($_POST['formName'] == 'addEditRumourForm' && $_POST['deleteThisRumour'] == 'Y' && $logged_in['is_administrator'] && $logged_in['can_edit_content']) {
			
			// delete
				$success = deleteFromDb('rumours', array('public_id'=>$publicID), null, null, null, null, 1);
				
			// redirect
				if (!$success) $tl->page['error'] .= "Unable to delete rumour for some reason. ";
				else {
					deleteFromDb('rumour_sightings', array('rumour_id'=>$rumour[0]['rumour_id']));
					deleteFromDb('rumours_x_tags', array('rumour_id'=>$rumour[0]['rumour_id']));
					deleteFromDb('watchlist', array('rumour_id'=>$rumour[0]['rumour_id']));

					// update logs
						$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has deleted the rumour &quot;" . $rumour[0]['description'] . "&quot; (rumour_id " . $rumour[0]['rumour_id'] . ")";
						$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'rumour_id=' . $rumour[0]['rumour_id']));

					// redirect
						$authentication_manager->forceRedirect('/index/success=rumour_removed');
				}
			
		}
		elseif ($_POST['formName'] == 'addEditRumourForm') {

			// clean input
				$_POST = $parser->trimAll($_POST);
				$_POST['description'] = htmlspecialchars_decode(@$_POST['description'], ENT_QUOTES);
				$_POST['description'] = $parser->removeHTML($_POST['description']);
				$_POST['latitude'] = floatval(@$_POST['latitude']);
				$_POST['longitude'] = floatval(@$_POST['longitude']);
				$checkboxesToParse = array('enabled', 'newuser_ok_to_contact', 'newuser_anonymous');
				foreach ($checkboxesToParse as $checkbox) {
					if (isset($_POST[$checkbox])) $_POST[$checkbox] = 1;
					else $_POST[$checkbox] = 0;
				}
				for ($counter = 0; $counter < count(@$_POST['tags']); $counter++) {
					$_POST['tags'][$counter] = $parser->removeHTML($_POST['tags'][$counter]);
					$_POST['tags'][$counter] = $parser->includeOrExcludeCharacters($_POST['tags'][$counter], 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_ ');
				}
				
			// check for errors
				if ($logged_in['is_administrator'] && $logged_in['can_edit_content']) {
					if (!@$_POST['description']) $tl->page['error'] .= "Please enter a rumour. ";
					if (!$_POST['country']) $tl->page['error'] .= "Please specify a country. ";
				}

				if (!@$_POST['priority_id']) $tl->page['error'] .= "Please specify a priority for this rumour. ";
				$result = retrieveStatuses(array('status_id'=>@$_POST['status_id']), null, null, false, 1);
				if (!count($result)) $tl->page['error'] .= "Please specify a status. ";
				elseif ($result[0]['is_closed'] && !@$_POST['findings']) $tl->page['error'] .= "Please specify findings before finalizing status on this rumour. ";

			// edit rumour
				if (!$tl->page['error']) {

					// update rumour					
						if ($logged_in['is_administrator'] && $logged_in['can_edit_content']) {

/*
						REMOVING FAUX GEOCODING:
						-----------------------

							if (!$_POST['occurred_at_latitude'] || !$_POST['occurred_at_longitude']) { // faux geocode
								$latLong = retrieveSingleFromDB('rumour_sightings', null, array('country_id'=>@$_POST['country'], 'city'=>@$_POST['city']), null, null, null, "latitude <> 0 AND longitude <> 0");
								if (!count($latLong)) $latLong = retrieveSingleFromDB('rumours', null, array('country_id'=>@$_POST['country'], 'city'=>@$_POST['city']), null, null, null, "latitude <> 0 AND longitude <> 0");
							}
*/
							updateDb('rumours', array('description'=>$_POST['description'], 'enabled'=>$_POST['enabled'], 'status_id'=>$_POST['status_id'], 'findings'=>$_POST['findings'], 'verified_with'=>$_POST['verified_with'], 'priority_id'=>$_POST['priority_id'], 'assigned_to'=>$_POST['assigned_to'], 'country_id'=>$_POST['country'], 'city'=>$_POST['city'], 'latitude'=>@$_POST['occurred_at_latitude'], 'longitude'=>@$_POST['occurred_at_longitude'], 'unable_to_geocode'=>'0', 'occurred_on'=>$_POST['occurred_on'], 'updated_on'=>date('Y-m-d H:i:s'), 'updated_by'=>$logged_in['user_id']), array('public_id'=>$publicID), null, null, null, null, 1);

						}
						else updateDb('rumours', array('enabled'=>$_POST['enabled'], 'status_id'=>$_POST['status_id'], 'findings'=>$_POST['findings'], 'verified_with'=>$_POST['verified_with'], 'priority_id'=>$_POST['priority_id'], 'assigned_to'=>$_POST['assigned_to'], 'updated_on'=>date('Y-m-d H:i:s'), 'updated_by'=>$logged_in['user_id']), array('public_id'=>$publicID), null, null, null, null, 1);

					// update tags
						for ($counter = 0; $counter < count($_POST['tags']); $counter++) {

							// retrieve tagID and add any tags which are unique
								$result = retrieveSingleFromDb('tags', null, array('tag'=>$_POST['tags'][$counter]));
								if (count($result)) $tagID = $result[0]['tag_id'];
								else $tagID = insertIntoDb('tags', array('tag'=>$_POST['tags'][$counter], 'created_by'=>$logged_in['user_id'], 'created_on'=>date('Y-m-d H:i:s')));
								
							// associate tag
								deleteFromDbSingle('rumours_x_tags', array('tag_id'=>$tagID, 'rumour_id'=>$rumour[0]['rumour_id']));
								insertIntoDb('rumours_x_tags', array('tag_id'=>$tagID, 'rumour_id'=>$rumour[0]['rumour_id'], 'added_by'=>$logged_in['user_id'], 'added_on'=>date('Y-m-d H:i:s')));

						}

					// remove expired tags
						$result = retrieveTags(null, null, "(SELECT COUNT(rumour_id) FROM " . $tablePrefix . "rumours_x_tags WHERE " . $tablePrefix . "rumours_x_tags.tag_id = " . $tablePrefix . "tags.tag_id) < 1");
						for ($counter = 0; $counter < count($result); $counter++) {
							deleteFromDbSingle('tags', array('tag_id'=>$result[$counter]['tag_id']));
						}

					// evidence and other attachments
						if (@$_POST['file_evidence']) {
							foreach ($_POST['file_evidence'] as $uploadedFile) {
								$filename = substr($uploadedFile, strrpos($uploadedFile, '/') + 1);
								$uploadedFile = __DIR__ . '/../../../../' . $uploadedFile;
								$destinationPath = 'assets/rumour_attachments/' . $publicID;
								if (!file_exists($destinationPath)) mkdir($destinationPath);
								$success = rename($uploadedFile, $destinationPath . '/' . $filename);
								if (!$success || !file_exists($destinationPath . '/' . $filename)) $tl->page['error'] .= "Unable to retrieve uploaded file for some reason. ";
							}
						}

						if (count($attachments)) {
							for ($counter = 0; $counter < count($attachments); $counter++) {
								if (isset($_POST['delete_' . $counter])) {
									$success = @unlink ($_POST['filepath_' . $counter]);
									if (!$success || file_exists($_POST['filepath_' . $counter])) $tl->page['error'] .= "Unable to delete an attachment. ";
								}
							}
						}

				}

			// watchlist notifications (email)
				if (!$tl->page['error']) {
					if ($_POST['status_id'] != $rumour[0]['status_id']) {
						$notify = retrieveWatchlist(array($tablePrefix . 'watchlist.rumour_id'=>$rumour[0]['rumour_id'], 'notify_of_updates'=>'1'), null, $tablePrefix . "users.email != '' AND " . $tablePrefix . "users.ok_to_contact = '1'");
						for ($counter = 0; $counter < count($notify); $counter++) {
							notifyUserOfRumourStatusUpdate($notify[$counter]['full_name'], $notify[$counter]['email'], $rumour[0]['public_id'], $_POST['description'], $rumourStatuses[$_POST['status_id']]);
						}
					}
				}

			// notify assignee
				if (!$tl->page['error']) {
					if ($_POST['assigned_to'] != $rumour[0]['assigned_to']) {
						$assignedTo = retrieveUsers(array($tablePrefix . 'users.user_id'=>$_POST['assigned_to'], 'ok_to_contact'=>'1'), null, $tablePrefix . "users.email != ''", null, 1);
						if (count($assignedTo) == 1) {
							notifyOfRumour($assignedTo[0]['full_name'], $assignedTo[0]['email'], $publicID, $_POST['description'], true);
						}
					}
				}

			// update log
				$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated rumour_id " . $rumour[0]['rumour_id'] . ": " . $rumour[0]['description'];
				$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'rumour_id=' . $rumour[0]['rumour_id']));
				
			// redirect
				if (!$tl->page['error']) $authentication_manager->forceRedirect('/rumour/' . $publicID . '/' . $parser->seoFriendlySuffix($_POST['description']) . '/' . urlencode('page=1|success=rumour_updated'));
								
		}

	}
	
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>