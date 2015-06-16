<?php
	if (@$filters['view'] == 'sightings') $pageLoadEvents = "populateMap();";
	$pageDescription = $rumour[0]['description'];
	include 'includes/views/desktop/shared/page_top.php';

	// tabs
		echo "<ul id='rumourTabs' class='nav nav-tabs'>\n";
		echo "  <li" . ($filters['view'] == 'rumour' ? " class='active'" : false) . "><a href='#rumour' data-toggle='tab'>Rumour</a></li>\n";
		echo "  <li" . ($filters['view'] == 'sightings' ? " class='active'" : false) . "><a href='#sightings' data-toggle='tab' onClick='if (!mapLoaded) populateMap();'>Sightings (" . count($sightings) . ")</a></li>\n";
		echo "  <li" . ($filters['view'] == 'comments' ? " class='active'" : false) . "><a href='#comments' data-toggle='tab'>Comments" . (@$numberOfComments ? " (" . $numberOfComments . ")" : false) . "</a></li>\n";
		echo "</ul><br />\n\n";

	echo "<div class='tab-content'>\n";
	echo "  <div class='tab-pane" . ($filters['view'] == 'rumour' ? " active" : false) . "' id='rumour'>\n";

/*	--------------------------------------
	Rumour tab
	-------------------------------------- */

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
		echo "<div id='rumourAttribution'>\n";
		echo "  <div>\n";
		echo "    Occurred in " . trim($rumour[0]['city'] . ', ' . $rumour[0]['country'], ' ,') . "\n";
		if ($rumour[0]['occurred_on'] != '0000-00-00 00:00:00') {
			echo " on " . date('F j, Y', strtotime($rumour[0]['occurred_on']));
			if (substr($rumour[0]['occurred_on'], 11) != '00:00:00') echo ", at " . date('G:i A', strtotime($rumour[0]['occurred_on']));
		}
		echo "  </div>\n";
		echo "  <div>\n";
		echo "    Reported by " . ($sightings[0]['anonymous'] ? "<b>anonymous</b>" : "<a href='/profile/" . $sightings[0]['username'] . "'>" . $sightings[0]['username'] . "</a>") . " via " . $sightings[0]['source'] . "\n";
		if ($sightings[0]['heard_on'] != '0000-00-00 00:00:00') {
			echo "    on " . date('F j, Y', strtotime($sightings[0]['heard_on']));
			if (substr($sightings[0]['heard_on'], 11) != '00:00:00') echo ", at " . date('G:i A', strtotime($sightings[0]['heard_on']));
			echo "\n";
		}
		if ($sightings[0]['sighting_country']) echo "    in " . trim($sightings[0]['sighting_city'] . ', ' . $sightings[0]['sighting_country'], ' ,') . "\n";
		if ($rumour[0]['update_by'] && $rumour[0]['update_by'] < date('Y-m-d H:i:s')) echo "    <span class='glyphicon glyphicon-time transluscent' title='This rumour is overdue an update!'></span>\n";
		echo "  </div>\n";
		if (count($sightings) > 1) echo "  <div>Also heard by <a href='#sightings' id='jumpToSightingsTab'>" . (count($sightings) - 1) . " other(s)</a></div>\n";
		echo "</div>\n";

	// tags
		echo $form->start('editTagsForm', '', 'post', null, null, array('onSubmit'=>'return false;')) . "\n";
		echo $form->input('hidden', 'tagToRemove') . "\n";
		echo "<div id='rumourTags'>\n";
		echo "  <div id='rumourTagList'>\n";
		if (count($tags)) {
			for ($counter = 0; $counter < count($tags); $counter++) {
				echo "    <span class='badge'>\n";
				echo "      <a href='/search_results/" . urlencode("tag_id=" . strtolower($tags[$counter]['tag_id'])) . "' class='tagLink'>" . $tags[$counter]['tag'] . "</a>\n";
				if ($logged_in) echo "      &nbsp; <a href='javascript:void(0)' onClick='removeTag(" . '"' . $tags[$counter]['tag_id'] . '"' . "); return false;'><span class='glyphicon glyphicon-remove glyphicon-white'></span></a>\n";
				echo "    </span>\n";
			}
		}
		if ($logged_in) echo "    " . $form->input('button', 'addTagsButton', null, false, 'Add a tag', 'btn btn-sm btn-link', null, null, array('data-toggle'=>'collapse', 'href'=>'#addTags')) . "\n";
		echo "  </div>\n";
		if ($logged_in) {
			echo "  <div id='addTags' class='collapse'>\n";
			echo "    <div class='row'>\n";
			echo "      <div class='col-lg-9 col-md-9 col-sm-8 col-xs-6'>" . $form->input('select', 'new_tags', null, false, null, 'select2', $allTags, null, array('multiple'=>'multiple', 'data-placeholder'=>'Additional tags', 'data-tags'=>'true')) . "</div>\n";
			echo "      <div class='col-lg-3 col-md-3 col-sm-4 col-xs-6 text-right'>\n";
			echo "        " . $form->input('submit', 'submit_add_tags_button', null, false, 'Add', 'btn btn-info', null, null, null, null, array('onClick'=>'validateAddTags(); return false;')) . "\n";
			echo "        " . $form->input('button', 'cancel_add_tags_button', null, false, 'Cancel', 'btn btn-link', null, null, array('data-toggle'=>'collapse', 'href'=>'#addTags')) . "\n";
			echo "      </div>\n";
			echo "    </div>\n";
			echo "  </div>\n";
		}
		echo "</div>\n";
		echo $form->end() . "\n";


	echo "<hr />\n";

	// photo evidence
		if ($photoEvidence) {
			echo "<div id='photoEvidenceModal' class='modal fade'>\n";
			echo "  <div class='modal-dialog'>\n";
			echo "    <div class='modal-content'>\n";
			echo "      <div class='modal-header'>\n";
			echo "        <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>\n";
			echo "        <h4 class='modal-title'>Photographic evidence</h4>\n";
			echo "      </div>\n";
			echo "      <div class='modal-body'>\n";
			echo "        <img src='/" . $photoEvidence . "?random=" . rand(10000,99999) . "' class='img-responsive' alt='Photo evidence' />\n";
			echo "      </div>\n";
			echo "    </div><!-- /.modal-content -->\n";
			echo "  </div><!-- /.modal-dialog -->\n";
			echo "</div><!-- /.modal -->\n";

			echo "<div class='row'>\n";
			echo "  <div id='photoEvidence' class='col-lg-2 col-md-3 col-sm-4 col-xs-6'><a href='javascript:void(0)' data-toggle='modal' data-target='#photoEvidenceModal' onClick='return false'><img src='/" . $photoEvidence . "?random=" . rand(10000,99999) . "' class='img-responsive' alt='Photo evidence' /></a></div>\n";
			echo "  <div class='col-lg-10 col-md-9 col-sm-8 col-xs-6'>\n";
		}

	// status
		if ($rumour[0]['status']) {
			echo "<div id='rumourStatus'>\n";
			echo "  This rumour is <b>" . strtolower($rumour[0]['status']) . "</b>\n";
			if ($rumour[0]['assigned_to_username'] && !$rumour[0]['is_closed']) echo "  and assigned to <a href='/profile/" . $rumour[0]['assigned_to_username'] . "'>" . $rumour[0]['assigned_to_username'] . "</a>\n";
			if (@$rumour[0]['priority']) echo "  and is of <b>" . strtolower($rumour[0]['priority']) . "</b> priority\n";
			if ($rumour[0]['findings']) echo "  &#8211; here's why:\n";
			echo "</div>\n";
		}

	// findings
		echo "<div id='rumourFindings'>\n";
		if ($rumour[0]['findings']) echo "  " . nl2br($parser->activateURLs($rumour[0]['findings'])) . "\n";
		elseif (!$hasBeenWatchlisted) echo "  Add this page to your watchlist to keep track of our progress.\n";
		echo "</div>\n";

		if ($photoEvidence) {
			echo "  </div>\n";
			echo "</div>\n";
		}
		
	// actions
		if ($logged_in) {
			echo $form->start('rumourActionsForm', '', 'post', '', null, array('onSubmit'=>'return false;')) . "\n";
			echo $form->input('hidden', 'addToWatchlist') . "\n";
			echo $form->input('hidden', 'removeFromWatchlist') . "\n";
			echo "<div id='rumourActions'>\n";
			// add sighting
				echo "  " . $form->input('button', 'addSightingButton', null, false, 'I have also heard this rumour', 'btn btn-info', null, null, array('data-toggle'=>'collapse', 'href'=>'#addSighting')) . "\n";
			// watchlist
				if (!$hasBeenWatchlisted) echo "  " . $form->input('button', 'addToWatchlistButton', null, false, "<span class='glyphicon glyphicon-plus'></span> Watchlist", 'btn btn-default', null, null, null, null, array('onClick'=>'validateAddToWatchlist(); return false;')) . "\n";
				else echo "  " . $form->input('button', 'removeFromWatchlistButton', null, false, "<span class='glyphicon glyphicon-minus'></span> Stop watching", 'btn btn-default', null, null, null, null, array('onClick'=>'validateRemoveFromWatchlist(); return false;')) . "\n";
			// add comment
				echo "  " . $form->input('button', 'addCommentButton', null, false, "<span class='glyphicon glyphicon-comment'></span> Comment", 'btn btn-default', null, null, array('data-toggle'=>'collapse', 'href'=>'#addComment')) . "\n";
			// edit
				if ($logged_in['is_administrator'] && $logged_in['can_edit_content']) $buttonText = 'Edit';
				elseif ($logged_in['is_moderator'] && $rumour[0]['assigned_to']) $buttonText = 'Reassign';
				elseif ($logged_in['is_moderator'] && !$rumour[0]['assigned_to']) $buttonText = 'Assign';
				elseif ($logged_in['is_community_liaison'] && $rumour[0]['assigned_to'] == $logged_in['user_id']) $buttonText = 'Reassign';
				if (@$buttonText) echo "  " . $form->input('button', 'editRumourButton', null, false, "<span class='glyphicon glyphicon-edit'></span> " . $buttonText, 'btn btn-default', null, null, null, null, array('onClick'=>'document.location.href="/rumour_edit/' . $publicID . '"; return false;'));
			echo "</div>\n";
			echo $form->end() . "\n";
		}

	// add sighting
		if ($logged_in) {
			echo $form->start('addSightingForm', '', 'post', null, null, array('onSubmit'=>'validateAddSightingForm(); return false;')) . "\n";
			echo "<div id='addSighting' class='collapse'>\n";
			echo "  <div class='row form-group'>\n";
			/* Country */			echo "    <div class='col-md-4'>" . $form->input('country', 'country', $operators->firstTrue(@$_POST['country'], @$pseudonym['country_id']), false, 'Country where heard', 'form-control') . "</div>\n";
			/* Community */			echo "    <div class='col-md-4'>" . $form->input('text', 'city', @$_POST['city'], false, '|Community', 'form-control') . "</div>\n";
			/* Location type */		echo "    <div class='col-md-4'>" . $form->input('select', 'location_type', @$_POST['location_type'], false, null, 'select2', $locationTypes, null, array('data-placeholder'=>'Overheard at', 'data-tags'=>'true')) . "</div>\n";
			echo "  </div>\n";
			echo "  <div class='row form-group'>\n";
			/* Date heard */		echo "    <div class='col-md-6'>\n";
									echo "      <div id='heard_on' class='input-group date form_datetime' data-date-format='yyyy-mm-dd hh:ii:ss' data-link-format='yyyy-mm-dd hh:ii:ss' data-link-field='heard_on'>\n";
									echo "        " . $form->input('text', 'heard_on', $operators->firstTrue(@$_POST['heard_on'], date('Y-m-d H:i:s')), false, null, 'form-control', null, 19) . "\n";
									echo "        <span class='input-group-addon'>&nbsp;<span class='glyphicon glyphicon-calendar'></span></span>\n";
									echo "      </div>\n";
									echo "    </div>\n";
			/* Source */			if ($logged_in['is_proxy']) echo "    <div class='col-md-6'>" . $form->input('select', 'source_id', null, false, '|Reported via', 'form-control', $rumourSources) . "</div>\n";
									echo "  </div>\n";
			if ($logged_in['is_proxy']) {
				/* Attributed to */	echo "  <div class='row form-group'>\n";
									echo "    <div class='col-md-6'>" . $form->input('select', 'created_by', $operators->firstTrue(@$_POST['created_by'], $logged_in['user_id']), false, '|Heard by', 'form-control', $allUsers + array(''=>'--', 'add'=>'A new user?')) . "</div>\n";
									echo "    <div class='col-md-6'>" . $form->input('button', 'add_user', null, false, '...or create new user', 'btn btn-link', null, null, array('data-toggle'=>'collapse', 'data-target'=>'#newuser_container', 'aria-expanded'=>'false', 'aria-controls'=>'newuser_container'), null, array('onClick'=>'document.getElementById("created_by").value="add"; return false;')) . "</div>\n";
									echo "  </div>\n";

									include 'includes/views/desktop/shared/add_new_user.php';
			}
			else echo $form->input('hidden', 'created_by', $logged_in['user_id']) . "\n";
			echo "  <div class='row form-group'>\n";
			/* Action */			echo "    <div class='col-md-2'>" . $form->input('submit', 'submitSighting', null, false, 'Submit', 'btn btn-info') . "</div>\n";
			echo "  </div>\n";
			echo "</div>\n";
			echo $form->end() . "\n";
		}

	// add comment
		if ($logged_in) {
			echo $form->start('addCommentForm', '', 'post', null, null, array('onSubmit'=>'return false;')) . "\n";
			echo "<div id='addComment' class='collapse'>\n";
			echo "  <div class='row form-group'>\n";
			echo "    <div class='col-md-10'>" . $form->input('textarea', 'new_comment', @$_POST['new_comment'], true, '|Add your thoughts...', 'form-control') . "</div>\n";
			echo "    <div class='col-md-2'>" . $form->input('submit', 'submitComment', null, false, 'Submit', 'btn btn-info', null, null, null, null, array('onClick'=>'validateAddCommentForm(); return false;')) . "</div>\n";
			echo "  </div>\n";
			echo "</div>\n";
			echo $form->end() . "\n";
		}

	echo "  </div>\n";
	echo "  <div class='tab-pane" . ($filters['view'] == 'sightings' ? " active" : false) . "' id='sightings'>\n";

/*	--------------------------------------
	Sightings tab
	-------------------------------------- */

	echo $form->start('moderateSightingsForm', '', 'post', null, null, array('onSubmit'=>'return false;')) . "\n";
	echo $form->input('hidden', 'sightingToRemove') . "\n";
	if (count($sightings)) {
		echo "<ul class='nav nav-pills mutedPills'>\n";
		echo "  <li class='active'><a href='#sightingsMap' data-toggle='tab'>View as map</a></li>\n";
		echo "  <li><a href='#sightingsTable' data-toggle='tab'>View as table</a></li>\n";
		echo "</ul>\n";

		echo "<div class='tab-content'>\n";

		// map
			echo "  <div class='tab-pane active' id='sightingsMap'>\n";
			echo "    <div id='rumourMapCanvas' class='img-rounded img-thumbnail'>Loading...</div>\n";
			echo "    <p class='text-muted'>This heatmap depicts the spread of rumours over time. Hotter areas of the map denote earlier sightings, while cooler areas indicate delayed dispersion.</p>\n";
			echo "  </div>\n";

			$pageJavaScript .= "// Populate map\n";
			$pageJavaScript .= "  var mapLoaded = false;\n";
			$pageJavaScript .= "  function populateMap() {\n";
			$pageJavaScript .= "    var heatMapData = [\n";
			if ($rumour[0]['latitude'] <> 0 && $rumour[0]['longitude'] <> 0) $mapCenter = $rumour[0]['latitude'] . "," . $rumour[0]['longitude'];
			for ($counter = count($sightings) - 1; $counter >= 0; $counter--) {
				if ($sightings[$counter]['sighting_latitude'] <> 0 && $sightings[$counter]['sighting_longitude'] <> 0) {
					$pageJavaScript .= "      {location: new google.maps.LatLng(" . $sightings[$counter]['sighting_latitude'] . "," . $sightings[$counter]['sighting_longitude'] . "), weight: " . strtotime($sightings[$counter]['heard_on']) . "}" . ($counter > 0 ? "," : false) . "\n";
					if (!@$mapCenter) $mapCenter = $sightings[$counter]['sighting_latitude'] . "," . $sightings[$counter]['sighting_longitude'];
				}
			}
			$pageJavaScript .= "    ];\n\n";

			if (!@$mapCenter) $mapCenter = '0,0';


			$pageJavaScript .= "    var mapCenter = new google.maps.LatLng(" . ($sightings[0]['latitude_occurred'] <> 0 && $sightings[0]['longitude_occurred'] <> 0 ? $sightings[0]['latitude_occurred'] . "," . $sightings[0]['longitude_occurred'] : $mapCenter) . ");\n";

			$pageJavaScript .= "    map = new google.maps.Map(document.getElementById('rumourMapCanvas'), {\n";
			$pageJavaScript .= "      center: mapCenter,\n";
			$pageJavaScript .= "      zoom: 7,\n";
			$pageJavaScript .= "      mapTypeId: google.maps.MapTypeId.HYBRID\n";
			$pageJavaScript .= "    });\n\n";

			$pageJavaScript .= "    var heatmap = new google.maps.visualization.HeatmapLayer({\n";
			$pageJavaScript .= "      data: heatMapData\n";
			$pageJavaScript .= "    });\n";
			$pageJavaScript .= "    heatmap.setMap(map);\n";
			$pageJavaScript .= "    mapLoaded = true;\n";
			$pageJavaScript .= "  }\n";

		// table
			echo "  <div class='tab-pane' id='sightingsTable'>\n";

			echo "    <table class='table table-hover table-condensed'>\n";
			echo "    <thead>\n";
			echo "    <tr>\n";
			echo "    <th>Location</th>\n";
			echo "    <th>Reported by</th>\n";
			echo "    <th>Via</th>\n";
			echo "    <th>Date</th>\n";
			echo "    <th></th>\n";
			echo "    </tr>\n";
			echo "    </thead>\n";
			echo "    <tbody>\n";
			for ($counter = 0; $counter < count($sightings); $counter++) {
				echo "    <tr>\n";
				// Location
					$locationLabel = trim($sightings[$counter]['sighting_city'] . ', ' . $sightings[$counter]['sighting_country'], ' ,');
					if ($sightings[$counter]['sighting_latitude'] <> 0 && $sightings[$counter]['sighting_longitude'] <> 0) $locationMap = "https://www.google.com/maps/?q=" . $sightings[$counter]['sighting_latitude'] . "," . $sightings[$counter]['sighting_longitude'];
					echo "    <td>" . (@$locationMap ? "<a href='" . $locationMap . "' target='_blank'>" : false) . $locationLabel . (@$locationMap ? "</a>" : false) . "</td>\n";
				// Username
					echo "    <td>" . ($sightings[$counter]['anonymous'] ? "anonymous" : "<a href='/profile/" . $sightings[$counter]['username'] . "'>" . $sightings[$counter]['username'] . "</a>") . "</td>\n";
				// Source
					echo "    <td>" . $sightings[$counter]['source'] . "</td>\n";
				// Date
					echo "    <td>";
					echo date('j-M-Y', strtotime($sightings[$counter]['heard_on']));
					if (substr($sightings[$counter]['heard_on'], 11) != '00:00:00') echo " @ " . date('G:i A', strtotime($sightings[$counter]['heard_on']));
					echo "    </td>\n";
				// Actions
					echo "    <td>\n";
					if (($logged_in['is_administrator'] && $logged_in['can_edit_content']) || $sightings[$counter]['created_by'] == $logged_in['user_id']  || $sightings[$counter]['entered_by'] == $logged_in['user_id']) {
						echo "    <a href='/sighting_edit/" . $sightings[$counter]['sighting_id'] . "'><span class='glyphicon glyphicon-edit transluscent' title='Edit'></span></a>\n";
						echo "    <a href='' onClick='removeSighting(" . '"' . $sightings[$counter]['sighting_id'] . '"' . "); return false;'><span class='glyphicon glyphicon-trash transluscent' title='Remove'></span></a>\n";
					}
					echo "    </td>\n";
				echo "    </tr>\n";
			}
			echo "    </tbody>\n";
			echo "    </table>\n";
			echo "  </div>\n";

		echo "</div>\n";
	}
	echo $form->end() . "\n";
		
	echo "  </div>\n";
	echo "  <div class='tab-pane" . ($filters['view'] == 'comments' ? " active" : false) . "' id='comments'>\n";

/*	--------------------------------------
	Comments tab
	-------------------------------------- */

	if (!count($comments)) echo "<p>None yet.</p>\n";
	else {

		echo $form->start('moderateCommentsForm', '', 'post', null, null, array('onSubmit'=>'return false;')) . "\n";
		echo $form->input('hidden', 'commentToFlag') . "\n";
		echo $form->input('hidden', 'commentToDisable') . "\n";
		echo $form->input('hidden', 'commentToEnable') . "\n";

		echo "<div id='commentList'>\n";
			
		for ($counter = 0; $counter < count($comments); $counter++) {
			if ($comments[$counter]['comment_enabled'] || $logged_in['is_moderator'] || $logged_in['is_administrator']) {
				echo "  <div class='row'>\n";
				// photo
					echo "    <div class='col-md-1 commentPhoto'>\n";
					$image = $avatar_manager->retrieveProfileImage($comments[$counter]['username']);
					if (@$image['sizes']['verysmall']) echo "        <img src='/" . $image['sizes']['verysmall'] . "' border='0' class='img-rounded' alt='" . htmlspecialchars($comments[$counter]['username'], ENT_QUOTES) . "' />\n";
					else echo "        <img src='/resources/img/default_profile_image.jpg' border='0' class='img-responsive img-rounded' alt='" . htmlspecialchars($comments[$counter]['username'], ENT_QUOTES) . "' />\n";
					echo "      </div>\n";
				echo "      <div class='col-md-11 commentText'>\n";
				echo "        <div class='row'>\n";
				// date and author
					if ($comments[$counter]['comment_enabled']) echo "          <div class='col-md-7 commentDateAuthor'>\n";
					else echo "          <div class='col-md-7 commentDateAuthorHidden'>\n";
					echo "            " . date('F j, Y | g:i A', strtotime($comments[$counter]['comment_created_on'])) . " |\n";
					if (!$comments[$counter]['anonymous'] || $logged_in['is_proxy'] || $logged_in['is_moderator'] || $logged_in['is_administrator']) {
						if (!$comments[$counter]['comment_enabled']) $username = "<a href='/profile/" . $comments[$counter]['username'] . "' class='errorMessage'>" . $comments[$counter]['username'] . "</a>";
						else $username = "<a href='/profile/" . $comments[$counter]['username'] . "'>" . $comments[$counter]['username'] . "</a>";
					}
					else $username = "<b>anonymous</b>";
					echo "            " . $username . "\n";
					if (!$comments[$counter]['comment_enabled']) echo "            <span class='label label-danger'>HIDDEN</span>\n";
					echo "          </div>\n";
				// moderation options
					echo "          <div class='col-md-5 commentFlag'>\n";
					if ($logged_in['is_moderator'] || $logged_in['is_administrator']) {
					echo "            <span class='tooltips' data-toggle='tooltip' title='Flag this comment for moderation'><a href='javascript:void(0)' onClick='validateModerateCommentsForm(" . '"flag", "' . $comments[$counter]['comment_id'] . '"' . "); return false;'><span class='glyphicon glyphicon-alert transluscent'></span></a></span>\n";
						if ($comments[$counter]['comment_enabled']) echo "            <span class='tooltips' data-toggle='tooltip' title='Hide this comment'><a href='javascript:void(0)' onClick='validateModerateCommentsForm(" . '"disable", "' . $comments[$counter]['comment_id'] . '"' . "); return false;'><span class='glyphicon glyphicon-eye-close transluscent'></span></a></span>\n";
						else echo "            <span class='tooltips' data-toggle='tooltip' title='Show this comment'><a href='javascript:void(0)' onClick='validateModerateCommentsForm(" . '"enable", "' . $comments[$counter]['comment_id'] . '"' . "); return false;'><span class='glyphicon glyphicon-eye-open'></span></a></span>\n";
					}
					echo "          </div>\n";
				echo "        </div>\n";
				// comment
					echo "        <div>\n";
					if ($comments[$counter]['is_moderator']) echo "          <span class='label label-default'>MODERATOR</span>\n";
					elseif ($comments[$counter]['is_administrator']) echo "          <span class='label label-default'>ADMINISTRATOR</span>\n";
					echo "          " . nl2br($parser->activateURLs($comments[$counter]['comment'])) . "\n";
					echo "        </div>\n";
				echo "      </div>\n";
				echo "  </div>\n";
				// divider
					echo "<div class='row'>\n";
					echo "<div class='col-md-12'><hr></div>\n";
					echo "</div>\n";
			}
			echo $form->end() . "\n";

		}

		echo "</div>\n";

	}


	// pagination
		if ($numberOfPages > 1) {
			echo $form->paginate($page, $numberOfPages, '/rumour/' . $publicID . $parser->seoFriendlySuffix($rumour[0]['description']) . '/' . $keyvalue_array->updateKeyValue($parameter3, 'page', '#', '|'));
		}

	echo "  </div>\n";
	echo "</div>\n";
		
	include 'includes/views/desktop/shared/page_bottom.php';
?>