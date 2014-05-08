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
		
		$page = floatval($parameter2);
		
		$pageSuccess = $parameter3;
		
	// queries
		$countries = array();
		$result = retrieveFromDb('countries', null, null, null, null, null, 'country ASC');
		for ($counter = 0; $counter < count($result); $counter++) {
			$countries[$result[$counter]['country_id']] = $result[$counter]['country'];
		}		
		
		if ($logged_in['is_proxy'] || $logged_in['is_moderator'] || $logged_in['is_administrator']) $rumour = retrieveRumours(array('public_id'=>$publicID), null, null, null, 1);
		else $rumour = retrieveRumours(array('public_id'=>$publicID, $tablePrefix . 'rumours.enabled'=>1), null, null, null, 1);
		if (count($rumour) < 1) {
			header('Location: /404');
			exit();
		}
		
		$allUsers = array();
		$result = retrieveUsers(array('enabled'=>1));
		for ($counter = 0; $counter < count($result); $counter++) {
			$allUsers[$result[$counter]['user_id']] = $result[$counter]['username'];
			if ($result[$counter]['full_name']) $allUsers[$result[$counter]['user_id']] .= " (" . $result[$counter]['full_name'] . ")";
		}
		
		$tags = retrieveTags(array('rumour_id'=>$rumour[0]['rumour_id']), null, null, $tablePrefix . 'tags.tag ASC');
		
		$sightings = retrieveSightings(array($tablePrefix . 'rumour_sightings.rumour_id'=>$rumour[0]['rumour_id']), null, null, $tablePrefix . 'rumour_sightings.entered_on ASC');
		
		$result = countInDb('watchlist', 'created_by', array('rumour_id'=>$rumour[0]['rumour_id'], 'created_by'=>$logged_in['user_id']));
		if ($result[0]['count'] > 0) $hasBeenWatchlisted = true;
		else $hasBeenWatchlisted = false;
				
		$result = countInDb('comments', 'comment_id', array('rumour_id'=>$rumour[0]['rumour_id']));
		$numberOfComments = $result[0]['count'];
		$numberOfCommentsPerPage = 15;
		$numberOfPages = max(1, ceil($numberOfComments / $numberOfCommentsPerPage));
		if ($page < 1) $page = 1;
		elseif ($page > $numberOfPages) $page = $numberOfPages;

		$comments = retrieveComments(array($tablePrefix . 'comments.rumour_id'=>$rumour[0]['rumour_id']), null, null, $tablePrefix . 'comments.created_on DESC', floatval(($page * $numberOfCommentsPerPage) - $numberOfCommentsPerPage) . ',' . $numberOfCommentsPerPage);
		
	// instantiate required class(es)
		$profileImage = new avatarManager_TL();
		$parser = new parser_TL();
		$operators = new operators_TL();
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {
		
		if ($_POST['formName'] == 'attributionForm' && $_POST['sightingToRemove'] && $logged_in) {
			
			// validate sighting
				$sighting = retrieveFromDb('rumour_sightings', array('sighting_id'=>$_POST['sightingToRemove']), null, null, null, null, null, 1);
				if (count($sighting) <> 1) $pageError .= "Unknown error attempting to remove sighting. ";
				else {

					// remove sighting
						deleteFromDb('rumour_sightings', array('sighting_id'=>$_POST['sightingToRemove']), null, null, null, null, 1);
		
					// update log
						$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has removed a sighting from the rumour &quot;" . $rumour[0]['description'] . "&quot; (public_id " . $publicID . ")";
						$logger->logItInDb($activity);
						
					// redirect
						header('Location: /rumour/' . $publicID . '/' . floatval($page) . '/sighting_removed');
						exit();
					
				}
				
		}
		elseif ($_POST['formName'] == 'editTagsForm' && $_POST['tagToRemove'] && $logged_in) {
			
			// validate tag
				$tag = retrieveFromDb('tags', array('tag_id'=>$_POST['tagToRemove']), null, null, null, null, null, 1);
				if (count($tag) <> 1) $pageError .= "Unknown error attempting to remove tag. ";
				else {

					// delete association
						deleteFromDb('rumours_x_tags', array('tag_id'=>$_POST['tagToRemove'], 'rumour_id'=>$rumour[0]['rumour_id']), null, null, null, null, 1);
						
					// check if tag still used, and if not remove it
						$anyOtherRumours = retrieveFromDb('rumours_x_tags', array('tag_id'=>$_POST['tagToRemove']), null, null, null, 1);
						if (count($anyOtherRumours) < 1) deleteFromDb('tags', array('tag_id'=>$_POST['tagToRemove']), null, null, null, null, 1);
		
					// update log
						$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has removed the tag &quot;" . $tag[0]['tag'] . "&quot; (tag_id " . $_POST['tagToRemove'] . ") from rumour_id " . $rumour[0]['rumour_id'] . ": " . $rumour[0]['description'];
						$logger->logItInDb($activity);
						
					// redirect
						header('Location: /rumour/' . $publicID . '/' . floatval($page) . '/tag_removed');
						exit();
					
				}
				
		}
		elseif ($_POST['formName'] == 'editTagsForm' && $logged_in) {
			
			// clean input
				$_POST = $parser->trimAll($_POST);
				$_POST['new_tag'] = $parser->removeHTML($_POST['new_tag']);
				$_POST['new_tag'] = str_replace('"', '', $_POST['new_tag']);
				$_POST['new_tag'] = str_replace(',', ' ', $_POST['new_tag']);
				$_POST['new_tag'] = str_replace(';', ' ', $_POST['new_tag']);
				$_POST['new_tag'] = str_replace('  ', ' ', $_POST['new_tag']);
				$_POST['new_tag'] = $parser->includeOrExcludeCharacters($_POST['new_tag'], 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_ ');
				
			// check for errors
				if (!$_POST['new_tag']) $pageError .= "Please specify one or more tags. ";
				
			// split into individual tags
				$newTags = explode(' ', $_POST['new_tag']);
				foreach ($newTags as $newTag) {
					$newTag = trim($newTag);
					if ($newTag) {
						// retrieve tagID and add any tags which are unique
							$existingTag = retrieveFromDb('tags', array('tag'=>$newTag), null, null, null, null, null, 1);
							if (count($existingTag) == 1) $tagID = $existingTag[0]['tag_id'];
							else $tagID = insertIntoDb('tags', array('tag'=>$newTag, 'created_by'=>$logged_in['user_id'], 'created_on'=>date('Y-m-d H:i:s')));
						// associate tag
							deleteFromDb('rumours_x_tags', array('tag_id'=>$tagID, 'rumour_id'=>$rumour[0]['rumour_id']), null, null, null, null, 1);
							insertIntoDb('rumours_x_tags', array('tag_id'=>$tagID, 'rumour_id'=>$rumour[0]['rumour_id'], 'added_by'=>$logged_on['user_id'], 'added_on'=>date('Y-m-d H:i:s')));
					}
				}

			// update log
				$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has added the tag &quot;" . $newTag . "&quot; (tag_id " . $tagID . ") to rumour_id " . $rumour[0]['rumour_id'] . ": " . $rumour[0]['description'];
				$logger->logItInDb($activity);
				
			// redirect
				header('Location: /rumour/' . $publicID . '/' . floatval($page) . '/tags_added');
				exit();
				
		}
		elseif ($_POST['formName'] == 'rumourActionsForm' && $_POST['addThisSighting'] == 'Y' && $logged_in) {
			
			if (!$sightingAlreadyRecorded) {
				// clean input
					$_POST = $parser->trimAll($_POST);
					
				// check for errors
					if (!@$_POST['country']) $pageError .= "Please specify a country. ";
					if (!@$_POST['heard_on']) $pageError .= "Please specify a date. ";

				if (!$pageError) {
					// create encoded IP
						if (strlen($_SERVER['REMOTE_ADDR']) > 15) $ipv6 = $parser->encodeIP($_SERVER['REMOTE_ADDR'], 'ipv6');
						elseif (strlen($_SERVER['REMOTE_ADDR']) > 0) $ipv4 = $parser->encodeIP($_SERVER['REMOTE_ADDR'], 'ipv4');
					
					// determine attribution
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
									$createdBy = insertIntoDb('users', array('username'=>$newUsername, 'ok_to_contact'=>'0', 'ok_to_show_profile'=>'0', 'registered_on'=>date('Y-m-d H:i:s'), 'registered_by'=>$logged_in['user_id']));
							}
						}
						else $createdBy = $logged_in['user_id'];
						
					// save sighting
						$sightingID = insertIntoDb('rumour_sightings', array('rumour_id'=>$rumour[0]['rumour_id'], 'created_by'=>$createdBy, 'entered_by'=>$logged_in['user_id'], 'entered_on'=>date('Y-m-d H:i:s'), 'source'=>$operators->firstTrue(@$_POST['source'], 'w'), 'ipv4'=>@$ipv4, 'ipv6'=>@$ipv6, 'country'=>$_POST['country'], 'region'=>@$_POST['region'], 'heard_on'=>$_POST['heard_on']));
											
					// update log
						$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has added a sighting (sighting_id " . $sightingID . ") of rumour_id " . $rumour[0]['rumour_id'] . ": " . $rumour[0]['description'];
						$logger->logItInDb($activity);
						
					// redirect
						header('Location: /rumour/' . $publicID . '/' . floatval($page) . '/sighting_added');
						exit();
				}
			}
			else $pageError .= "You've already added your sighting to this rumour. ";
			
		}
		elseif ($_POST['formName'] == 'rumourActionsForm' && $_POST['addToWatchlist'] == 'Y' && $logged_in) {
			
			$alreadyWatchlisted = retrieveFromDb('watchlist', array('rumour_id'=>$rumour[0]['rumour_id'], 'created_by'=>$logged_in['user_id']), null, null, null, null, null, 1);
			if (count($alreadyWatchlisted) > 0) $pageError .= "This rumour is already in your watchlist. ";
			else {
				insertIntoDb('watchlist', array('rumour_id'=>$rumour[0]['rumour_id'], 'created_by'=>$logged_in['user_id'], 'created_on'=>date('Y-m-d H:i:s')));
				header('Location: /rumour/' . $publicID . '/' . floatval($page) . '/added_to_watchlist');
				exit();
			}
			
		}
		elseif ($_POST['formName'] == 'rumourActionsForm' && $_POST['removeFromWatchlist'] == 'Y' && $logged_in) {
			
			$success = deleteFromDb('watchlist', array('rumour_id'=>$rumour[0]['rumour_id'], 'created_by'=>$logged_in['user_id']), null, null, null, null, 1);
			if (!$success) $pageError .= "This rumour wasn't found in your watchlist. ";
			else {
				header('Location: /rumour/' . $publicID . '/' . floatval($page) . '/removed_from_watchlist');
				exit();
			}
			
		}
		elseif ($_POST['formName'] == 'rumourActionsForm' && $logged_in) {

			// clean input
				$_POST = $parser->trimAll($_POST);
				$_POST['new_comment'] = $parser->removeHTML($_POST['new_comment']);
				
			// check for errors
				if (!$_POST['new_comment']) $pageError .= "Please provide a comment. ";
				
			if (!$pageError) {
				
				// add to database
					$commentID = insertIntoDb('comments', array('rumour_id'=>$rumour[0]['rumour_id'], 'comment'=>$_POST['new_comment'], 'created_by'=>$logged_in['user_id'], 'created_on'=>date('Y-m-d H:i:s')));

				// watchlist notifications (email)
					$notify = retrieveWatchlist(array($tablePrefix . 'watchlist.rumour_id'=>$rumour[0]['rumour_id'], 'notify_of_updates'=>'1'), null, $tablePrefix . "users.email != '' AND " . $tablePrefix . "users.ok_to_contact = '1'");
					for ($counter = 0; $counter < count($notify); $counter++) {
						$success = notifyUserOfRumourComment($notify[$counter]['full_name'], $notify[$counter]['email'], $rumour[0]['public_id'], $_POST['description'], $_POST['new_comment'], $logged_in['username']);
						if (!$success) {
							$activity = "Unable to email " . $notify[$counter]['full_name'] . " (" . $notify[$counter]['email'] . ") of a new comment on rumour_id " . $rumour[0]['rumour_id'];
							$logger->logItInDb($activity);
						}
					}

				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has added a comment (comment_id " . $commentID . ") to rumour_id " . $rumour[0]['rumour_id'] . ": " . $rumour[0]['description'];
					$logger->logItInDb($activity);
					
				// redirect
					header('Location: /rumour/' . $publicID . '/' . floatval($page) . '/comment_added');
					exit();
				
			}
				
				
		}
		
		elseif ($_POST['formName'] == 'moderateCommentsForm' && $_POST['commentToFlag'] && $logged_in) {
			
			// flag
				deleteFromDb('comment_flags', array('comment_id'=>$_POST['commentToFlag'], 'flagged_by'=>$logged_in['user_id']), null, null, null, null, 1);
				insertIntoDb('comment_flags', array('comment_id'=>$_POST['commentToFlag'], 'flagged_by'=>$logged_in['user_id'], 'flagged_on'=>date('Y-m-d H:i:s')));

			// update log
				$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has flagged a comment (comment_id " . $_POST['commentToFlag'] . ") to rumour_id " . $rumour[0]['rumour_id'] . ": " . $rumour[0]['description'];
				$logger->logItInDb($activity);
				
			// redirect
				header('Location: /rumour/' . $publicID . '/' . floatval($page) . '/comment_flagged');
				exit();
				
		}

		elseif ($_POST['formName'] == 'moderateCommentsForm' && $_POST['commentToDisable'] && $logged_in) {
			
			// disable
				updateDb('comments', array('enabled'=>'0'), array('comment_id'=>$_POST['commentToDisable']), null, null, null, null, 1);

			// update log
				$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has disabled a comment (comment_id " . $_POST['commentToDisable'] . ") to rumour_id " . $rumour[0]['rumour_id'] . ": " . $rumour[0]['description'];
				$logger->logItInDb($activity);
				
			// redirect
				header('Location: /rumour/' . $publicID . '/' . floatval($page) . '/comment_disabled');
				exit();
				
		}
		
		elseif ($_POST['formName'] == 'moderateCommentsForm' && $_POST['commentToEnable'] && $logged_in) {
			
			// enable
				updateDb('comments', array('enabled'=>'1'), array('comment_id'=>$_POST['commentToEnable']), null, null, null, null, 1);

			// update log
				$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has re-enabled a comment (comment_id " . $_POST['commentToEnable'] . ") to rumour_id " . $rumour[0]['rumour_id'] . ": " . $rumour[0]['description'];
				$logger->logItInDb($activity);
				
			// redirect
				header('Location: /rumour/' . $publicID . '/' . floatval($page) . '/comment_enabled');
				exit();
				
		}
		
	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>