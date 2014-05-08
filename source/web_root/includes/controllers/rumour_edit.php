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
		
	// query
		$rumour = retrieveRumours(array('public_id'=>$publicID), null, null, null, 1);
		if (count($rumour) < 1) {
			header('Location: /404');
			exit();
		}
		
		$countries = array();
		$result = retrieveFromDb('countries', null, null, null, null, null, 'country ASC');
		for ($counter = 0; $counter < count($result); $counter++) {
			$countries[$result[$counter]['country_id']] = $result[$counter]['country'];
		}		
		
	// authenticate user
		if (!$logged_in) forceLoginThenRedirectHere();
		
		if (!$logged_in['is_administrator'] || !$logged_in['can_edit_content']) {
			if (!$logged_in['is_moderator']) {
				if (!$logged_in['is_community_liaison'] || $rumour[0]['assigned_to'] != $logged_in['user_id']) {
					header('Location: /404');
					exit();
				}
			}
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
		if (!$allModeratorsAndCommunityLiaisons[$rumour[0]['created_by']]) $allModeratorsAndCommunityLiaisons[$rumour[0]['created_by']] = $rumour[0]['username'];
			
	// instantiate required class(es)
		$operators = new operators_TL();
		$parser = new parser_TL();
		$validator = new inputValidator_TL();
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
		
	if (count($_POST) > 0) {
		
		if ($_POST['formName'] == 'editRumourForm' && $_POST['deleteThisRumour'] == 'Y' && $logged_in['is_administrator'] && $logged_in['can_edit_content']) {
			
			// delete
				$success = deleteFromDb('rumours', array('public_id'=>$publicID), null, null, null, null, 1);
				
			// redirect
				if (!$success) $pageError .= "Unable to delete rumour for some reason. ";
				else {
					deleteFromDb('rumour_sightings', array('rumour_id'=>$rumour[0]['rumour_id']));
					deleteFromDb('rumours_x_tags', array('rumour_id'=>$rumour[0]['rumour_id']));
					deleteFromDb('watchlist', array('rumour_id'=>$rumour[0]['rumour_id']));
					header ('Location: /');
					exit();
				}
			
		}
		elseif ($_POST['formName'] == 'editRumourForm') {

			// clean input
				$_POST = $parser->trimAll($_POST);
				$_POST['description'] = htmlspecialchars_decode(@$_POST['description'], ENT_QUOTES);
				$_POST['description'] = $parser->removeHTML($_POST['description']);
				$checkboxesToParse = array('newuser_ok_to_contact', 'newuser_ok_to_show_profile');
				foreach ($checkboxesToParse as $checkbox) {
					if (isset($_POST[$checkbox])) $_POST[$checkbox] = 1;
					else $_POST[$checkbox] = 0;
				}
				
			// check for errors
				if ($logged_in['is_administrator'] && $logged_in['can_edit_content']) {
					if (!@$_POST['description']) $pageError .= "Please enter a rumour. ";
					if (!$_POST['country']) $pageError .= "Please specify a country. ";
					if ((@$_POST['status'] != 'NU' && @$_POST['status'] != 'UI') && !@$_POST['findings']) $pageError .= "Please specify findings before finalizing status on this rumour. ";
					if (@$_POST['created_by'] && (@$_POST['newuser_first_name'] || @$_POST['newuser_last_name'])) $pageError .= "Please either select an existing user or add a new users; you cannot do both. ";
					if (@$_POST['created_by'] && @$_POST['newuser_email']) {
						if (!$validator->validateEmailRobust(@$_POST['newuser_email'])) $pageError .= "Please specify a valid email address. ";
					}
					if (!@$_POST['created_by'] && !@$_POST['newuser_country']) $pageError .= "Please specify the new user's country. ";
				}
				elseif ($logged_in['is_moderator'] || $logged_in['is_community_liaison']) {
					if ((@$_POST['status'] != 'NU' && @$_POST['status'] != 'UI') && !@$_POST['findings']) $pageError .= "Please specify findings before finalizing status on this rumour. ";
				}
				
			// edit rumour
				if (!$pageError) {
					
					if ($logged_in['is_administrator'] && $logged_in['can_edit_content']) {
						
						// determine creator of rumour and sighting
							if (@$_POST['created_by']) $createdBy = $_POST['created_by'];
							else {
								// create new user
									$newUsername = null;
									while ($newUsername == null) {
										$newUsername = rand(1000000,9999999);
										$doesUsernameExist = countInDb('users', 'username', array('username'=>$newUsername));
										if ($doesUsernameExist[0]['count'] > 0) $newUsername = null;
									}
									$createdBy = insertIntoDb('users', array('username'=>$newUsername, 'first_name'=>$_POST['newuser_first_name'], 'last_name'=>$_POST['newuser_last_name'], 'country'=>$_POST['newuser_country'], 'email'=>$_POST['newuser_email'], 'phone'=>$_POST['newuser_phone'], 'secondary_phone'=>$_POST['newuser_secondary_phone'], 'sms_notifications'=>$_POST['newuser_sms_notifications'], 'ok_to_contact'=>$_POST['newuser_ok_to_contact'], 'ok_to_show_profile'=>$_POST['newuser_ok_to_show_profile'], 'date_registered'=>date('Y-m-d H:i:s')));
							}
							
						// update rumour
							updateDb('rumours', array('description'=>$_POST['description'], 'findings'=>$_POST['findings'], 'country'=>$_POST['country'], 'region'=>$_POST['region'], 'occurred_on'=>$_POST['occurred_on'], 'created_by'=>$createdBy, 'assigned_to'=>$_POST['assigned_to'], 'updated_on'=>date('Y-m-d H:i:s'), 'updated_by'=>$logged_in['user_id'], 'status'=>$_POST['status']), array('public_id'=>$publicID), null, null, null, null, 1);

					}
					elseif ($logged_in['is_moderator'] || $logged_in['is_community_liaison']) {
							updateDb('rumours', array('findings'=>$_POST['findings'], 'assigned_to'=>$_POST['assigned_to'], 'updated_on'=>date('Y-m-d H:i:s'), 'updated_by'=>$logged_in['user_id'], 'status'=>$_POST['status']), array('public_id'=>$publicID), null, null, null, null, 1);
					}
						
				}

			// watchlist notifications (email)
				if (!$pageError) {
					if ($_POST['status'] != $rumour[0]['status']) {
						$notify = retrieveWatchlist(array($tablePrefix . 'watchlist.rumour_id'=>$rumour[0]['rumour_id'], 'notify_of_updates'=>'1'), null, $tablePrefix . "users.email != '' AND " . $tablePrefix . "users.ok_to_contact = '1'");
						for ($counter = 0; $counter < count($notify); $counter++) {
							$success = notifyUserOfRumourStatusUpdate($notify[$counter]['full_name'], $notify[$counter]['email'], $rumour[0]['public_id'], $_POST['description'], $rumourStatuses[$_POST['status']]);
							if (!$success) {
								$activity = "Unable to email " . $notify[$counter]['full_name'] . " (" . $notify[$counter]['email'] . ") of a status update to rumour_id " . $rumour[0]['rumour_id'];
								$logger->logItInDb($activity);
							}
						}
					}
				}

			// notify assignee
				if (!$pageError) {
					if ($_POST['assigned_to'] != $rumour[0]['assigned_to']) {
						$assignedTo = retrieveUsers(array($tablePrefix . 'users.user_id'=>$_POST['assigned_to'], 'ok_to_contact'=>'1'), null, $tablePrefix . "users.email != ''", null, 1);
						if (count($assignedTo) == 1) {
							$success = notifyOfRumour($assignedTo[0]['full_name'], $assignedTo[0]['email'], $publicID, $_POST['description'], true);
							if (!$success) {
								$activity = "Unable to email " . $assignedTo[0]['full_name'] . " (" . $assignedTo[0]['email'] . ") upon assignment of rumour_id " . $rumour[0]['rumour_id'];
								$logger->logItInDb($activity);
							}
						}
					}
				}

			// update log
				$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated rumour_id " . $rumour[0]['rumour_id'] . ": " . $rumour[0]['description'];
				$logger->logItInDb($activity);
				
			// redirect
				if (!$pageError) {
					header ('Location: /rumour/' . $publicID . '/' . $parser->seoFriendlySuffix($_POST['description']) . '/rumour_updated');
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