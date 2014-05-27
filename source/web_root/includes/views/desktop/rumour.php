<?php
	include 'includes/views/desktop/shared/page_top.php';

	if ($pageError) echo "<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $pageError . "</div>\n";
	elseif ($pageSuccess == 'sighting_added') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully added sighting.</div>\n";
	elseif ($pageSuccess == 'sighting_removed') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully removed sighting.</div>\n";
	elseif ($pageSuccess == 'tags_added') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully added tag(s).</div>\n";
	elseif ($pageSuccess == 'tag_removed') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully removed tag.</div>\n";
	elseif ($pageSuccess == 'comment_added') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully added comment.</div>\n";
	elseif ($pageSuccess == 'comment_flagged') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully flagged comment.</div>\n";
	elseif ($pageSuccess == 'added_to_watchlist') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully added to watchlist.</div>\n";
	elseif ($pageSuccess == 'removed_from_watchlist') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully removed from watchlist.</div>\n";
	elseif ($pageSuccess == 'rumour_updated') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully updated rumour.</div>\n";
	
	// description
		echo "<div id='rumourTitle'";
		if (!$rumour[0]['enabled']) echo " class='muted'";
		echo ">\n";
		echo "  <div class='blockquoteOpen'>\n";
		echo "    <div class='blockquoteClose'>\n";
		echo "      " . $rumour[0]['description'] . "\n";
		if (!$rumour[0]['enabled']) echo "  (rumour disabled)\n";
		echo "      <span class='blockquoteCloseOrphan'>&nbsp;</span>\n";
		echo "    </div>\n";
		echo "  </div>\n";
		echo "</div>\n";

	// attribution
		echo $form->start('addAttributionForm', '', 'post', null, null, array('onSubmit'=>'return false;')) . "\n";
		echo $form->input('hidden', 'sightingToRemove') . "\n";
		echo "<div id='rumourAttribution'>\n";
		if ($sightings[0]['ok_to_show_profile']) $username = "<a href='/profile/" . $sightings[0]['username'] . "'>" . $sightings[0]['username'] . "</a>";
		else $username = "<b>anonymous</b>";
		echo "  <div>\n";
		echo "    Reported by " . $username . " via " . strtolower($rumourSources[$sightings[0]['source']]) . "\n";
		if ($sightings[0]['heard_on'] != '0000-00-00 00:00:00') "    on " . date('F j, Y', strtotime($sightings[0]['heard_on'])) . "\n";
		if ($sightings[0]['sighting_country']) echo "    in " . trim($sightings[0]['sighting_region'] . ', ' . $countries[$sightings[0]['sighting_country']], ' ,') . "\n";
		echo "  </div>\n";
		if (count($sightings) > 1) {
			echo "  <div>Also heard by <a href='' id='revealSightingsLink' onClick='return false;'>" . (count($sightings) - 1) . " other(s)</a></div>\n";
			$pageJavaScript .= "// add more tags\n";
			$pageJavaScript .= "  $('#revealSightingsLink').click(function () {\n";
			$pageJavaScript .= "    $('#rumourRemoveSightings').slideToggle();\n";
			$pageJavaScript .= "  });\n\n";
			echo "  <div id='rumourRemoveSightings'>\n";
			echo "    <table class='table table-hover table-condensed'>\n";
			for ($counter = 1; $counter < count($sightings); $counter++) {
				echo "    <tr>\n";
				// Username
					if ($sightings[$counter]['ok_to_show_profile']) $username = "<a href='/profile/" . $sightings[$counter]['username'] . "'>" . $sightings[$counter]['username'] . "</a>";
					else $username = "<b>anonymous</b>";
					echo "    <td><small>" . $username . "</small></td>\n";
				// Location & source
					echo "    <td><small>\n";
					echo "      " . trim($sightings[$counter]['sighting_region'] . ', ' . $countries[$sightings[$counter]['sighting_country']], ' ,') . "\n";
					if ($sightings[$counter]['source']) echo "      via " . strtolower($rumourSources[$sightings[$counter]['source']]) . "\n";
					echo "    </small></td>\n";
				// Date
					echo "    <td><small>" . date('F j, Y', strtotime($sightings[$counter]['heard_on'])) . "</small></td>\n";
				// Actions
					if (($logged_in['is_administrator'] && $logged_in['can_edit_content']) || $sightings[$counter]['created_by'] == $logged_in['user_id']  || $sightings[$counter]['entered_by'] == $logged_in['user_id']) {
						echo "    <td><a href='' onClick='removeSighting(" . '"' . $sightings[$counter]['sighting_id'] . '"' . "); return false;'><span class='glyphicon glyphicon-trash transluscent' title='Remove'></span></a></td>\n";
					}
					else {
						echo "    <td></td>\n";
					}
				echo "    </tr>\n";
			}
			echo "    </table>\n";
			echo "  </div>\n";
		}
		echo "</div>\n";
		echo $form->end() . "\n";
		
	// tags
		echo $form->start('editTagsForm', '', 'post', 'form-inline', null, array('onSubmit'=>'return false;')) . "\n";
		echo $form->input('hidden', 'tagToRemove') . "\n";
		echo "<div id='rumourTags'>\n";
		echo "  <div id='rumourTagList'>\n";
		if (count($tags)) {
			for ($counter = 0; $counter < count($tags); $counter++) {
				echo "    <span class='badge'>\n";
				echo "      <a href='/search_results/" . urlencode("tag_id=" . strtolower($tags[$counter]['tag_id'])) . "' class='tagLink'>" . $tags[$counter]['tag'] . "</a>\n";
				echo "      &nbsp; <a href='javascript:void(0)' onClick='removeTag(" . '"' . $tags[$counter]['tag_id'] . '"' . "); return false;'><span class='glyphicon glyphicon-remove glyphicon-white'></span></a>\n";
				echo "    </span>\n";
			}
		}
		if ($logged_in) echo "    " . $form->input('button', 'addTagsButton', null, false, 'Add a tag', 'btn btn-sm btn-link') . "\n";
		echo "  </div>\n";
		if ($logged_in) {
			echo "  <div id='rumourAddTags'>\n";
			echo "    <div class='row'>\n";
			echo "      <div class='col-md-10'>" . $form->input('text', 'new_tag', @$_POST['new_tag'], true, '|Additional tags (separate with spaces)', 'form-control') . "</div>\n";
			echo "      <div class='col-md-2'>" . $form->input('submit', 'submitTags', null, false, 'Submit', 'btn btn-info', null, null, null, null, array('onClick'=>'validateeditTagsForm(); return false;')) . "</div>\n";
			echo "    </div>\n";
			echo "  </div>\n";
			$pageJavaScript .= "// add more tags\n";
			$pageJavaScript .= "  $('#addTagsButton').click(function () {\n";
			$pageJavaScript .= "    $('#rumourAddTags').slideToggle();\n";
			$pageJavaScript .= "  });\n\n";
		}
		echo "</div>\n";
		echo $form->end() . "\n";

	// status and findings
		echo "<div id='rumourStatusAndFindings'>\n";
		echo "  <div id='rumourStatus'>This rumour is ";
		echo "<b>" . strtolower($rumourStatuses[$rumour[0]['status']]) . "</b>";
		if ($rumour[0]['assigned_to_username'] && ($rumour[0]['status'] == 'NU' || $rumour[0]['status'] == 'UI')) echo " and assigned to <a href='/profile/" . $rumour[0]['assigned_to_username'] . "'>" . $rumour[0]['assigned_to_username'] . "</a>";
		if (@$priorityLevels[$rumour[0]['priority']]) echo " and is of <b>" . strtolower($priorityLevels[$rumour[0]['priority']]) . "</b> priority\n";
		echo ".";
		if ($rumour[0]['findings']) echo " Here's why:";
		echo "\n";
		echo "  </div>\n";
		echo "  <div>\n";
		if ($rumour[0]['findings']) echo "    " . $rumour[0]['findings'] . "\n";
		elseif (!$hasBeenWatchlisted) echo "    Add this page to your watchlist to keep track of our progress.\n";
		echo "  </div>\n";
		echo "</div>\n";
		
	// actions
		echo "<div id='rumourActions'>\n";
		if ($logged_in) {
			echo "  " . $form->start('rumourActionsForm', '', 'post', '', null, array('onClick'=>'return false;')) . "\n";
			echo "  " . $form->input('hidden', 'addThisSighting') . "\n";
			echo "  " . $form->input('hidden', 'addToWatchlist') . "\n";
			echo "  " . $form->input('hidden', 'removeFromWatchlist') . "\n";
			echo "  <div id='rumourActionsButtons'>\n";
			if ($logged_in) echo "  " . $form->input('button', 'addSightingButton', null, false, 'I have also heard this rumour', 'btn btn-info') . "\n";
			if (!$hasBeenWatchlisted) echo "    <button id='addToWatchlistButton' name='addToWatchlistButton' class='btn btn-default' onClick='validateAddToWatchlist(); return false;'><span class='glyphicon glyphicon-plus'></span> Watchlist</button>\n";
			else echo "    <button id='removeFromWatchlistButton' name='removeFromWatchlistButton' class='btn btn-default' onClick='validateRemoveFromWatchlist(); return false;'><span class='glyphicon glyphicon-minus'></span> Stop watching</button>\n";
			echo "    <button id='leaveCommentButton' name='leaveCommentButton' class='btn btn-default'><span class='glyphicon glyphicon-comment'></span> Comment</button>\n";
			if ($logged_in['is_administrator'] && $logged_in['can_edit_content']) echo "  <button class='btn btn-default' onClick='document.location.href=" . '"/rumour_edit/' . $publicID . '"' . "; return false;'><span class='glyphicon glyphicon-edit'></span> Edit</button>\n";
			elseif ($logged_in['is_moderator']) {
				if (!$rumour[0]['assigned_to']) echo "  <button class='btn btn-default' onClick='document.location.href=" . '"/rumour_edit/' . $publicID . '"' . "; return false;'><span class='glyphicon glyphicon-edit'></span> Assign</button>\n";
				else echo "  <button class='btn btn-default' onClick='document.location.href=" . '"/rumour_edit/' . $publicID . '"' . "; return false;'><span class='glyphicon glyphicon-edit'></span> Reassign</button>\n";
			}
			elseif ($logged_in['is_community_liaison'] && $rumour[0]['assigned_to'] == $logged_in['user_id']) {
				echo "  <button class='btn btn-default' onClick='document.location.href=" . '"/rumour_edit/' . $publicID . '"' . "; return false;'><span class='glyphicon glyphicon-edit'></span> Reassign</button>\n";
			}
			echo "  </div>\n";
			$pageJavaScript .= "// open add comment / add sighting forms\n";
			$pageJavaScript .= "  $('#addSightingButton').click(function () {\n";
			$pageJavaScript .= "    $('#addSighting').slideToggle();\n";
			$pageJavaScript .= "  });\n\n";
			$pageJavaScript .= "  $('#leaveCommentButton').click(function () {\n";
			$pageJavaScript .= "    $('#leaveComment').slideToggle();\n";
			$pageJavaScript .= "  });\n\n";
			echo "  <div id='addSighting'>\n";
			echo "    <div class='row form-group'>\n";
			/* Country heard */		echo "      <div class='col-md-4'>" . $form->input('country', 'country', @$_POST['country'], false, 'Country where heard', 'form-control') . "</div>\n";
			/* Region heard */		echo "      <div class='col-md-3'>" . $form->input('text', 'region', @$_POST['region'], false, '|City/region', 'form-control') . "</div>\n";
			/* Date heard */		echo "      <div class='col-md-3'>" . $form->input('date', 'heard_on', $operators->firstTrue(@$_POST['heard_on'], date('Y-m-d')), false, null, 'form-control') . "</div>\n";
			/* Action */			echo "      <div class='col-md-2'>" . $form->input('submit', 'submitSighting', null, false, 'Submit', 'btn btn-info', null, null, null, null, array('onClick'=>'validateAddSightingForm(); return false;')) . "</div>\n";
			echo "    </div>\n";
			if ($logged_in['is_proxy']) {
				echo "    <div class='row form-group'>\n";
				/* Source */		echo "      <div class='col-md-4'>" . $form->input('select', 'source', null, false, 'Reported via', 'form-control', $rumourSources) . "</div>\n";
				/* Attributed to */	echo "      <div class='col-md-5'>" . $form->input('select', 'created_by', $operators->firstTrue(@$_POST['created_by'], $logged_in['user_id']), false, 'Heard by', 'form-control', $allUsers + array(''=>'A new user?')) . "</div>\n";
				echo "    </div>\n";
			}
			echo "  </div>\n";
			echo "  <div id='leaveComment'>\n";
			echo "    <div class='row form-group'>\n";
			echo "      <div class='col-md-10'>" . $form->input('textarea', 'new_comment', @$_POST['new_comment'], true, '|Add your thoughts...', 'form-control') . "</div>\n";
			echo "      <div class='col-md-2'>" . $form->input('submit', 'submitComment', null, false, 'Submit', 'btn btn-info', null, null, null, null, array('onClick'=>'validateAddCommentForm(); return false;')) . "</div>\n";
			echo "    </div>\n";
			echo "  </div>\n";
			echo "  " . $form->end() . "\n";
		}
		echo "</div>\n";
		
	// comments
		echo "<div id='commentList'>\n";
		if ($numberOfComments < 1) {
			echo "<div class='row'>\n";
			echo "  <div class='col-md-12'><hr /></div>\n";
			echo "</div>\n";
			echo "<div class='row'>\n";
			echo "  <div class='col-md-12'>No comments yet.</div>\n";
			echo "</div>\n";
			echo "<div class='row'>\n";
			echo "  <div class='col-md-12'><hr /></div>\n";
			echo "</div>\n";
		}
		else {
			echo "  " . $form->start('moderateCommentsForm', '', 'post', null, null, array('onClick'=>'return false;')) . "\n";
			echo "  " . $form->input('hidden', 'commentToFlag') . "\n";
			echo "  " . $form->input('hidden', 'commentToDisable') . "\n";
			echo "  " . $form->input('hidden', 'commentToEnable') . "\n";
			
			// comments
				echo "<div class='row'>\n";
				echo "  <div class='col-md-12'><hr /></div>\n";
				echo "</div>\n";
				for ($counter = 0; $counter < count($comments); $counter++) {
					if ($comments[$counter]['comment_enabled'] || $logged_in['is_moderator'] || $logged_in['is_administrator']) {
						echo "  <div class='row'>\n";
						// photo
							echo "    <div class='col-md-1 commentPhoto'>\n";
							$image = $profileImage->retrieveProfileImage($comments[$counter]['username']);
							if (@$image['sizes']['verysmall']) echo "        <img src='/" . $image['sizes']['verysmall'] . "' border='0' class='img-rounded' alt='" . htmlspecialchars($comments[$counter]['username'], ENT_QUOTES) . "' />\n";
							else echo "        <img src='/libraries/tidal_lock/php/dynamic_thumbnailer.php?source=../../../resources/img/default_profile_image.jpg&desired_width=" . $profileImageSizes_TL['verysmall'] . "' border='0' class='img-rounded' alt='" . htmlspecialchars($comments[$counter]['username'], ENT_QUOTES) . "' />\n";
							echo "      </div>\n";
						echo "      <div class='col-md-11 commentText'>\n";
						echo "        <div class='row'>\n";
						// date and author
							if ($comments[$counter]['comment_enabled']) echo "          <div class='col-md-7 commentDateAuthor'>\n";
							else echo "          <div class='col-md-7 commentDateAuthorHidden'>\n";
							echo "            " . date('F j, Y | g:i A', strtotime($comments[$counter]['comment_created_on'])) . " |\n";
							if ($comments[$counter]['ok_to_show_profile'] || $logged_in['is_proxy'] || $logged_in['is_moderator'] || $logged_in['is_administrator']) {
								if (!$comments[$counter]['comment_enabled']) $username = "<a href='/profile/" . $comments[$counter]['username'] . "' class='errorMessage'>" . $comments[$counter]['username'] . "</a>";
								else $username = "<a href='/profile/" . $comments[$counter]['username'] . "'>" . $comments[$counter]['username'] . "</a>";
							}
							else $username = "<b>anonymous</b>";
							echo "            " . $username . "\n";
							if (!$comments[$counter]['comment_enabled']) echo "            | HIDDEN\n";
							echo "          </div>\n";
						// moderation options
							if (!$comments[$counter]['is_moderator'] && !$comments[$counter]['is_administrator']) {
								echo "          <div class='col-md-5 commentFlag'>\n";
								if ($logged_in['is_moderator'] || $logged_in['is_administrator']) {
									if ($comments[$counter]['comment_enabled']) echo "            <span class='glyphicon glyphicon-trash transluscent'></span> <a href='javascript:void(0)' onClick='validateModerateCommentsForm(" . '"disable", "' . $comments[$counter]['comment_id'] . '"' . "); return false;'>Disable</a>\n";
									else echo "            <span class='glyphicon glyphicon-ok transluscent'></span> <a href='javascript:void(0)' onClick='validateModerateCommentsForm(" . '"enable", "' . $comments[$counter]['comment_id'] . '"' . "); return false;'>Enable</a>\n";
								}
								else echo "            <span class='glyphicon glyphicon-flag transluscent'></span> <a href='javascript:void(0)' onClick='validateModerateCommentsForm(" . '"flag", "' . $comments[$counter]['comment_id'] . '"' . "); return false;'>Flag for moderation</a>\n";
								echo "          </div>\n";
							}
						echo "        </div>\n";
						// comment
							echo "        <div>\n";
							if ($comments[$counter]['is_moderator']) echo "          <span class='label label-default'>MODERATOR</span>\n";
							elseif ($comments[$counter]['is_administrator']) echo "          <span class='label label-default'>ADMINISTRATOR</span>\n";
							echo "          " . $comments[$counter]['comment']. "\n";
							echo "        </div>\n";
						echo "      </div>\n";
						echo "  </div>\n";
						// divider
							echo "<div class='row'>\n";
							echo "<div class='col-md-12'><hr></div>\n";
							echo "</div>\n";
					}
				}

			// paginate
				if ($numberOfPages > 1) {
					echo $form->paginate($page, $numberOfPages, '/rumour/' . $publicID . '/#');
				}
					
			echo "  " . $form->end() . "\n";
		}
		echo "</div>\n";
		
	include 'includes/views/desktop/shared/page_bottom.php';
?>