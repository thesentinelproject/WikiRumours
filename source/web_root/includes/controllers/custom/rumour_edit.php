<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$publicID = $parameter1;
		if (!$publicID) {
			header('Location: /404');
			exit();
		}
		$pageStatus = $parameter2;
		
	// query
		$rumour = retrieveRumours(array('public_id'=>$publicID), null, null, null, 1);
		if (count($rumour) < 1) {
			header('Location: /404');
			exit();
		}
		
	// authenticate user
		if (!$logged_in['is_administrator'] || !$logged_in['can_edit_content']) {
			if (!$logged_in['is_moderator']) {
				if (!$logged_in['is_community_liaison'] || $rumour[0]['assigned_to'] != $logged_in['user_id']) {
					header('Location: /404');
					exit();
				}
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

		if (@$rumour[0]['photo_evidence_file_ext']) {
			$photoEvidence = 'assets/photo_evidence/' . $rumour[0]['rumour_id'] . '.' . $rumour[0]['photo_evidence_file_ext'];
			if (!file_exists($photoEvidence)) $photoEvidence = null;
		}

		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
		
	if (count($_POST) > 0) {
		
		if ($_POST['formName'] == 'addEditRumourForm' && $_POST['deleteThisRumour'] == 'Y' && $logged_in['is_administrator'] && $logged_in['can_edit_content']) {
			
			// delete
				$success = deleteFromDb('rumours', array('public_id'=>$publicID), null, null, null, null, 1);
				
			// redirect
				if (!$success) $pageError .= "Unable to delete rumour for some reason. ";
				else {
					deleteFromDb('rumour_sightings', array('rumour_id'=>$rumour[0]['rumour_id']));
					deleteFromDb('rumours_x_tags', array('rumour_id'=>$rumour[0]['rumour_id']));
					deleteFromDb('watchlist', array('rumour_id'=>$rumour[0]['rumour_id']));

					// update logs
						$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has deleted the rumour &quot;" . $rumour[0]['description'] . "&quot; (rumour_id " . $rumour[0]['rumour_id'] . ")";
						$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'rumour_id=' . $rumour[0]['rumour_id']));

					// redirect
						header ('Location: /index/rumour_removed');
						exit();
				}
			
		}
		if ($_POST['formName'] == 'addEditRumourForm' && $_POST['deleteThisPhoto'] == 'Y') {
			
			// check for errors
				if (!@$photoEvidence) $pageError = "Unable to locate photo to delete. ";
				else {

					// remove from server
						if (@$photoEvidence) $success = unlink ($photoEvidence);
						if (!$success || file_exists($photoEvidence)) $pageError .= "Unable to delete photo for some reason. ";
						else {

							// update logs
								$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has removed the photographic evidence from the rumour &quot;" . $rumour[0]['description'] . "&quot; (rumour_id " . $rumour[0]['rumour_id'] . ")";
								$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'rumour_id=' . $rumour[0]['rumour_id']));

							// redirect
								header('Location: /rumour_edit/' . $publicID . '/photographic_evidence_deleted');
								exit();

						}

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
					if (!@$_POST['description']) $pageError .= "Please enter a rumour. ";
					if (!$_POST['country']) $pageError .= "Please specify a country. ";
				}

				if (!@$_POST['priority_id']) $pageError .= "Please specify a priority for this rumour. ";
				$result = retrieveStatuses(array('status_id'=>@$_POST['status_id']), null, null, false, 1);
				if (!count($result)) $pageError .= "Please specify a status. ";
				elseif ($result[0]['is_closed'] && !@$_POST['findings']) $pageError .= "Please specify findings before finalizing status on this rumour. ";

				if ($_FILES['photo_evidence']['tmp_name']) {
					$fileExtension = strtolower($file_manager->isImage($_FILES['photo_evidence']['tmp_name']));
					if (!$fileExtension) $pageError .= "An invalid image was uploaded; please upload a JPG, PNG or GIF. ";
					else {
						$dimensions = getimagesize($_FILES['photo_evidence']['tmp_name']);
						if ($dimensions[0] > $maxWidthForPhotographicEvidence || $dimensions[1] > $maxWidthForPhotographicEvidence) $pageError .= "Your uploaded photo is too big. Please make sure that the width or height is no more than 3000 pixels. ";
						else {
							$filesize = $file_manager->getFilesize($_FILES['photo_evidence']['tmp_name']);
							if ($filesize > $maxFilesizeForPhotographicEvidence) $pageError .= "Your uploaded photo is too big. Please make sure that the file is no larger than 2 MB. ";
						}
					}
				}
				
			// edit rumour
				if (!$pageError) {

					// update rumour					
						if ($logged_in['is_administrator'] && $logged_in['can_edit_content']) {

							if (!$_POST['latitude'] || !$_POST['longitude']) { // faux geocode
								$latLong = retrieveSingleFromDB('rumour_sightings', null, array('country_id'=>@$_POST['country'], 'city'=>@$_POST['city']), null, null, null, "latitude <> 0 AND longitude <> 0");
								if (!count($latLong)) $latLong = retrieveSingleFromDB('rumours', null, array('country_id'=>@$_POST['country'], 'city'=>@$_POST['city']), null, null, null, "latitude <> 0 AND longitude <> 0");
							}

							updateDb('rumours', array('description'=>$_POST['description'], 'enabled'=>$_POST['enabled'], 'status_id'=>$_POST['status_id'], 'findings'=>$_POST['findings'], 'verified_with'=>$_POST['verified_with'], 'priority_id'=>$_POST['priority_id'], 'assigned_to'=>$_POST['assigned_to'], 'country_id'=>$_POST['country'], 'city'=>$_POST['city'], 'latitude'=>(@$_POST['latitude'] <> 0 ? $_POST['latitude'] : $latLong[0]['latitude']), 'longitude'=>(@$_POST['longitude'] <> 0 ? $_POST['longitude'] : $latLong[0]['longitude']), 'unable_to_geocode'=>'0', 'occurred_on'=>$_POST['occurred_on'], 'updated_on'=>date('Y-m-d H:i:s'), 'updated_by'=>$logged_in['user_id']), array('public_id'=>$publicID), null, null, null, null, 1);

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

					// update photo
						if ($_FILES['photo_evidence']['tmp_name']) {
							$destination = 'assets/photo_evidence/' . $rumour[0]['rumour_id'] . '.' . $fileExtension;
							// delete old
								@unlink ($destination);
								if (file_exists($destination)) $pageError .= "Unable to delete previous photo evidence for some reason. ";
								else {
									// save new
										$success = move_uploaded_file($_FILES['photo_evidence']['tmp_name'], $destination);
										if (!$success || !file_exists($destination)) $pageError .= "Unable to save photo evidence for some reason. ";
										else updateDb('rumours', array('photo_evidence_file_ext'=>$fileExtension), array('public_id'=>$publicID), null, null, null, null, 1);
								}
						}
						
				}

			// watchlist notifications (email)
				if (!$pageError) {
					if ($_POST['status_id'] != $rumour[0]['status_id']) {
						$notify = retrieveWatchlist(array($tablePrefix . 'watchlist.rumour_id'=>$rumour[0]['rumour_id'], 'notify_of_updates'=>'1'), null, $tablePrefix . "users.email != '' AND " . $tablePrefix . "users.ok_to_contact = '1'");
						for ($counter = 0; $counter < count($notify); $counter++) {
							notifyUserOfRumourStatusUpdate($notify[$counter]['full_name'], $notify[$counter]['email'], $rumour[0]['public_id'], $_POST['description'], $rumourStatuses[$_POST['status_id']]);
						}
					}
				}

			// notify assignee
				if (!$pageError) {
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
				if (!$pageError) {
					header ('Location: /rumour/' . $publicID . '/' . $parser->seoFriendlySuffix($_POST['description']) . '/page=1/rumour_updated');
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