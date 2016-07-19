<?php

	// tabs
		echo "<ul class='nav nav-tabs'>\n";
		echo "  <li role='presentation' class='active'><a href='#profile' data-toggle='tab'><span" . (!$user[0]['enabled'] || $user[0]['anonymous'] ? " class='muted'" : false) . "><strong>" . $user[0]['username'] . "</strong>" . (!$user[0]['enabled'] ? " (account disabled)" : false) . ($user[0]['anonymous'] ? " (profile hidden)" : false) . "</span></a></li>\n";
		echo "  <li role='presentation'><a href='#rumours' data-toggle='tab'>Rumours reported" . (count($rumours) ? " (" . count($rumours) . ")" : false) . "</a></li>\n";
		echo "  <li role='presentation'><a href='#comments' data-toggle='tab'>Comments" . (count($comments) ? " (" . count($comments) . ")" : false) . "</a></li>\n";
		if ($logged_in['is_administrator']) {
			echo "  <li role='presentation'><a href='#logins' aria-controls='logins' role='tab' data-toggle='tab'>Logins" . (count($logins) ? " (" . count($logins) . ")" : false) . "</a></li>\n";
			echo "  <li role='presentation'><a href='#activities' data-toggle='tab'>Recent activities" . (count($recentActivities) ? " (" . count($recentActivities) . ")" : false) . "</a></li>\n";
		}
		echo "</ul><br />\n\n";

	echo "<div class='tab-content'>\n";
	echo "  <div role='tabpanel' class='tab-pane active' id='profile'>\n";

/*	--------------------------------------
	Profile tab
	-------------------------------------- */

	// Photo
		echo "<div class='row'>\n";
		echo "  <div id='profilePhoto' class='col-md-4 col-md-push-8 col-sm-4 col-sm-push-8'>\n";
		$image = $avatar_manager->retrieveProfileImage($user[0]['username']);
		echo "    <img src='" . (@$image['sizes']['large'] ? "/" . $image['sizes']['large'] : "/resources/img/default_profile_image.jpg") . "' border='0' class='img-thumbnail' alt='" . htmlspecialchars($user[0]['full_name'], ENT_QUOTES) . "' />\n";
		echo "  </div>\n";
	
	// User data
		echo "  <div class='col-md-8 col-md-pull-4 col-sm-8 col-sm-pull-4'>\n";
		echo "    " . $form->start() . "\n";
		/* Name */				if ($user[0]['full_name']) echo $form->row('uneditable_static', 'full_name', $user[0]['full_name'], false, 'Name');
		/* Password */			if ($user[0]['user_id'] == $logged_in['user_id'] || @$logged_in['is_administrator']) {
									echo $form->rowStart('password_score', "Password Score");
									echo "  " . $form->input('uneditable_static', 'password_score_' . $user[0]['public_id'], $user[0]['password_score'] . "%" . (floatval($user[0]['password_score']) < 70 && $user[0]['user_id'] == $logged_in['user_id'] ? "  <small><a href='/update_password'>Strengthen now</a></small>" : false)) . "\n";
									echo $form->rowEnd();
								}
		/* Location */			$locationMap = trim(trim(@$user[0]['city'] . ', ' . @$user[0]['other_region'], ', ') . ', ' . @$user[0]['country'], ',- ');
								$locationLabel = trim(@$user[0]['city'] . ', ' . @$user[0]['country'], ', ');
								echo $form->row('uneditable_static', 'location', "<a href='https://maps.google.com/maps?q=" . urlencode($locationMap) . "' target='_blank'>" . $locationLabel . "</a>", false, 'Location');
		/* Registered */		echo $form->row('uneditable_static', 'registered_on', date('F j, Y', strtotime($user[0]['registered_on'])), false, 'Registered');
		/* Last login */		if ($user[0]['last_login'] != '0000-00-00 00:00:00' && ($logged_in['is_administrator'] || $logged_in['is_moderator'])) echo $form->row('uneditable_static', 'last_login', date('F j, Y', strtotime($user[0]['last_login'])), false, 'Last login');
		/* Termination */		if (count($termination)) {
									echo $form->row('uneditable_static', 'disabled_on', date('F j, Y', strtotime($termination[0]['disabled_on'])), false, 'Account disabled');
									echo $form->row('uneditable_static', 'reason', $termination[0]['reason'], false, 'Reason');
								}
		/* Responsibilities */	if ($user[0]['is_administrator'] || $user[0]['is_community_liaison'] || $user[0]['is_moderator'] || $user[0]['is_proxy'] || $user[0]['is_tester']) {
									echo $form->rowStart('responsibilities', 'Responsibilities', null, 'form-control-static');
									if ($user[0]['is_administrator']) echo "  <span class='tooltips' data-toggle='tooltip' title='Administrator'><a href='/admin_users/user_type=is_administrator'><span class='glyphicon glyphicon-certificate' aria-hidden='true'></span></a></span>\n";
									if ($user[0]['is_moderator']) echo "  <span class='tooltips' data-toggle='tooltip' title='Moderator'><a href='/admin_users/user_type=is_moderator'><span class='glyphicon glyphicon-phone' aria-hidden='true'></span></a></span>\n";
									if ($user[0]['is_community_liaison']) echo "  <span class='tooltips' data-toggle='tooltip' title='Community Liaison'><a href='/admin_users/user_type=is_community_liaison'><span class='glyphicon glyphicon-bullhorn' aria-hidden='true'></span></a></span>\n";
									if ($user[0]['is_proxy']) echo "  <span class='tooltips' data-toggle='tooltip' title='Proxy'><a href='/admin_users/user_type=is_proxy'><span class='glyphicon glyphicon-adjust' aria-hidden='true'></span></a></span>\n";
									if ($user[0]['is_tester']) echo "  <span class='tooltips' data-toggle='tooltip' title='Tester'><a href='/admin_users/user_type=is_tester'><span class='glyphicon glyphicon-scale' aria-hidden='true'></span></a></span>\n";
									echo $form->rowEnd();
								}
		/* Email */				if ($user[0]['email'] && ($logged_in['is_administrator'] || $logged_in['is_moderator'])) echo $form->row('uneditable_static', 'email', "<a href='mailto:" . $user[0]['email'] . "'>" . $user[0]['email'] . "</a>" . ($user[0]['ok_to_contact'] ? false : " <span class='label label-danger'>DO NOT CONTACT</span>"), false, 'Email');
		/* Phone */				if ($user[0]['primary_phone'] && ($logged_in['is_administrator'] || $logged_in['is_moderator'])) echo $form->row('uneditable_static', 'primary_phone', $parser->sanitizePhoneNumber($user[0]['primary_phone']) . ($user[0]['ok_to_contact'] ? false : " <span class='label label-danger'>DO NOT CONTACT</span>"), false, ($user[0]['primary_phone_sms'] ? "Voice / SMS" : "Voice"));
								if ($user[0]['secondary_phone'] && ($logged_in['is_administrator'] || $logged_in['is_moderator'])) echo $form->row('uneditable_static', 'secondary_phone', $parser->sanitizePhoneNumber($user[0]['secondary_phone']) . ($user[0]['ok_to_contact'] ? false : " <span class='label label-danger'>DO NOT CONTACT</span>"), false, ($user[0]['secondary_phone_sms'] ? "Voice / SMS" : "Voice"));
		/* Rumours */			echo $form->rowStart('rumours', 'Rumours', null, 'form-control-static');
								echo "  <div><span class='badge'>" . floatval($user[0]['rumours_created']) . "</span> reported</div>\n";
								echo "  <div><span class='badge'>" . floatval($user[0]['sightings']) . "</span> sighted</div>\n";
								echo "  <div><span class='badge'>" . floatval($user[0]['comments_left']) . "</span> comments</div>\n";
								echo "  <div><span class='badge'>" . floatval($user[0]['rumours_assigned']) . "</span> currently handling</div>\n";
								echo $form->rowEnd();
		/* Actions */			echo "<br />\n";
								echo "<!-- Actions -->\n";
								// viewing user's profile?
									if ($user[0]['username'] == $logged_in['username'] || ($logged_in['is_administrator'] && $logged_in['can_edit_users'])) {
										echo "  " . $form->input('button', 'edit_account_button', null, false, 'Edit account', 'btn btn-info', '', '', '', '', array('onClick'=>'document.location.href="/account/' . $user[0]['username'] . '"; return false;')) . "\n";
										echo "  " . $form->input('button', 'edit_password_password', null, false, 'Edit password', 'btn btn-link', '', '', '', '', array('onClick'=>'document.location.href="/update_password/' . $user[0]['username'] . '"; return false;')) . "\n";
										echo "  " . $form->input('button', 'edit_api_button', null, false, 'Edit API key', 'btn btn-link', '', '', '', '', array('onClick'=>'document.location.href="/obtain_api_key/' . $user[0]['username'] . '"; return false;')) . "\n";
									}
		echo "    " . $form->end() . "\n";
			
		echo "  </div>\n";
		echo "</div>\n";
		
	echo "  </div>\n";
	echo "  <div role='tabpanel' class='tab-pane' id='rumours'>\n";

/*	--------------------------------------
	Rumours tab
	-------------------------------------- */

	if (!count($rumours)) echo "<p>None yet.</p>\n";
	else {
		echo "  <table class='table table-hover table-condensed'>\n";
		echo "  <tr>\n";
		echo "  <th>Updated</th>\n";
		echo "  <th>Rumour</th>\n";
		echo "  <th>Status</th>\n";
		echo "  </tr>\n";
		for ($counter = 0; $counter < count($rumours); $counter++) {
			echo "  <tr>\n";
			echo "  <td class='nowrap'>" . date('j-M-Y', strtotime($rumours[$counter]['updated_on'])) . "</td>\n";
			echo "  <td><a href='/rumour/" . $rumours[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($rumours[$counter]['description']) . "'>" . $parser->truncate($rumours[$counter]['description'], 'c', 60) . "</a></td>\n";
			echo "  <td>" . $rumours[$counter]['status'] . "</td>\n";
			echo "  </tr>\n";
		}
		echo "  </table>\n";
	}

	echo "  </div>\n";
	echo "  <div class='tab-pane' id='comments'>\n";

/*	--------------------------------------
	Comments tab
	-------------------------------------- */

	if (!count($comments)) echo "<p>None yet.</p>\n";
	else {
		echo "  <table class='table table-hover table-condensed'>\n";
		echo "  <tr>\n";
		echo "  <th>Date</th>\n";
		echo "  <th>Comment</th>\n";
		echo "  <th>Rumour</th>\n";
		echo "  </tr>\n";
		for ($counter = 0; $counter < count($comments); $counter++) {
			echo "  <tr>\n";
			echo "  <td class='nowrap'>" . date('j-M-Y', strtotime($comments[$counter]['updated_on'])) . "</td>\n";
			echo "  <td>" . $parser->truncate($comments[$counter]['comment'], 'c', 40) . "</td>\n";
			echo "  <td><a href='/rumour/" . $comments[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($comments[$counter]['description']) . "'>" . $parser->truncate($comments[$counter]['description'], 'c', 40) . "</a></td>\n";
			echo "  </tr>\n";
		}
		echo "  </table>\n";
	}
		
	echo "  </div>\n";

	if ($logged_in['is_administrator']) {

		echo "  <div role='tabpanel' class='tab-pane' id='logins'>\n\n";

/*	--------------------------------------
	Logins tab
	-------------------------------------- */

		if (!count($logins)) echo "None yet\n";
		else {

			echo "    <table class='table table-condensed'>\n";
			echo "    <thead>\n";
			echo "    <tr>\n";
			echo "    <th>Date</th>\n";
			echo "    <th>Country</th>\n";
			echo "    <th>Browser</th>\n";
			echo "    </tr>\n";
			echo "    </thead>\n";
			echo "    <tbody>\n";
			for ($counter = 0; $counter < count($logins); $counter++) {
				echo "    <tr>\n";
				// connected on
					echo "    <td>" . date('j-M-Y', strtotime($logins[$counter]['connected_on'])) . " <small class='text-muted'>" . str_replace(' ', '&nbsp;', date('g:i:s A', strtotime($logins[$counter]['connected_on']))) . "</small></td>\n";
				// country
					echo "    <td>" . @$localization_manager->countries[$logins[$counter]['country_id']] . "</td>\n";
				// browser
					$agent = new detector_TL();
					$result = $agent->parseUserAgent($logins[$counter]['user_agent']);
					echo "    <td>" . $result['browser'] . " " . $result['browser_version'] . " on " . $result['os'] . " " . $result['os_version'] . "</td>\n";
				echo "    </tr>\n";
			}
			echo "    </tbody>\n";
			echo "    </table>\n\n";

		}

		echo "  </div>\n";
		echo "  <div role='tabpanel' class='tab-pane' id='activities'>\n";

/*		--------------------------------------
		Recent activities tab
		-------------------------------------- */

		if (!count($recentActivities)) echo "<p>None yet.</p>\n";
		else {
			echo "  <table class='table table-hover table-condensed'>\n";
			echo "  <thead>\n";
			echo "  <tr>\n";
			echo "  <th>Date</th>\n";
			echo "  <th>Activity</th>\n";
			echo "  </tr>\n";
			echo "  </thead>\n";
			echo "  <tbody>\n";
			for ($counter = 0; $counter < count($recentActivities); $counter++) {
				echo "  <tr>\n";
				echo "  <td class='nowrap'>\n";
				echo "    " . str_replace(' ', '&nbsp;', date('j-M-Y', strtotime($recentActivities[$counter]['connected_on']))) . "\n";
				echo "    <small><span class='text-muted'>" . date('g:i:s A', strtotime($recentActivities[$counter]['connected_on'])) . "</span></small>\n";
				echo "  </td>\n";
				echo "  <td>" . $recentActivities[$counter]['activity'] . "</td>\n";
				echo "  </tr>\n";
			}
			echo "  </tbody>\n";
			echo "  </table>\n";
		}
			
		echo "  </div>\n";

	}


	echo "</div>\n";

?>