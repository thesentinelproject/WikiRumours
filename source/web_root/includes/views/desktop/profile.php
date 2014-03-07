<?php
	include 'includes/views/desktop/shared/page_top.php';

	if ($pageError) echo "<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $pageError . "</div>\n";
	elseif ($pageSuccess == 'password_updated') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Password has been updated.</div>\n";
	elseif ($pageSuccess == 'account_updated') {
		echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Account has been updated.";
		if (!$logged_in['is_administrator']) echo " If you've changed your email address, please check your email to reconfirm your account.";
		echo "</div>\n";
	}
	
	echo "    <h2";
	if (!$user[0]['enabled'] || !$user[0]['ok_to_show_profile']) echo " class='muted'";
	echo ">" . htmlspecialchars($user[0]['username'], ENT_QUOTES);
	if (!$user[0]['enabled']) echo " (account disabled)";
	if (!$user[0]['ok_to_show_profile']) echo " (profile hidden)";
	echo "</h2>\n";
		
	echo "<div class='row pageModule'>\n";
	
	// Photo
		$image = $profileImage->retrieveProfileImage($user[0]['username']);
		if (@$image['sizes']['large']) $url = "/" . $image['sizes']['large'];
		else $url = "/libraries/tidal_lock/php/dynamic_thumbnailer.php?source=../../../resources/img/default_profile_image.jpg&desired_width=" . $profileImageSizes_TL['large'];
		
		for ($counter = 0; $counter < 2; $counter++) {
			if ($counter == 0) { // non-mobile experience
				echo "  <div class='col-md-3 col-md-push-9 col-sm-5 col-sm-push-7 text-right hidden-xs'>\n";
				echo "    <img src='" . $url . "' border='0' class='img-thumbnail' alt='" . htmlspecialchars($user[0]['full_name'], ENT_QUOTES) . "' />\n";
				echo "  </div>\n";
			}
			else { // mobile experience
				echo "  <div id='profilePhotoMobile' class='col-md-3 col-md-push-9 visible-xs'>\n";
				echo "    <img src='" . $url . "' border='0' class='img-thumbnail' alt='" . htmlspecialchars($user[0]['full_name'], ENT_QUOTES) . "' />\n";
				echo "  </div>\n";
			}
		}
	
	// User data
		echo "  <div class='col-md-9 col-md-pull-3 col-sm-7 col-sm-pull-5'>\n";
		echo "    " . $form->start() . "\n";
		/* Name */				echo "<!-- Name --><div class='indexApprox_TL'>Name</div><div class='indexedContentApprox_TL'>" . htmlspecialchars($user[0]['full_name'], ENT_QUOTES) . "</div><div class='floatClear'></div>\n";
		/* Responsibilities */	if ($user[0]['is_administrator'] || $user[0]['is_community_liaison'] || $user[0]['is_moderator'] || $user[0]['is_proxy'] || $user[0]['is_tester']) {
									echo "<!-- Responsibilities -->\n";
									echo "  <div class='indexApprox_TL'>Responsibilities</div>\n";
									echo "  <div class='indexedContentApprox_TL'>\n";
									if ($user[0]['is_administrator']) echo "  <span class='label label-default'>ADMINISTRATOR</span>\n";
									if ($user[0]['is_community_liaison']) echo "  <span class='label label-default'>COMMUNITY LIAISON</span>\n";
									if ($user[0]['is_moderator']) echo "  <span class='label label-default'>MODERATOR</span>\n";
									if ($user[0]['is_proxy']) echo "  <span class='label label-default'>PROXY / SUPPORT</span>\n";
									if ($user[0]['is_tester']) echo "  <span class='label label-default'>TESTER</span>\n";
									echo "  &nbsp;</div>\n";
									echo "  <div class='floatClear'></div>\n";
								}
		$location = trim(@$user[0]['region'] . ', ' . trim(@$user[0]['province_state'] . ', ' . @$user[0]['other_province_state'], ', ') . ', ' . @$countries[$user[0]['country']], ',- ');
		/* Location */			echo "<!-- Location --><div class='indexApprox_TL'>Location</div><div class='indexedContentApprox_TL'><a href='https://maps.google.com/maps?q=" . urlencode($location) . "' target='_blank'>" . $location . "</a></div><div class='floatClear'></div>\n";
		/* Email */				if ($logged_in['is_administrator'] && $user[0]['email'] && $user[0]['ok_to_contact']) echo "<!-- Email --><div class='indexApprox_TL'>Email</div><div class='indexedContentApprox_TL'><a href='mailto:" . $user[0]['email'] . "'>" . $user[0]['email'] . "</a></div><div class='floatClear'></div>\n";
		/* Phone */				if ($logged_in['is_administrator'] && $user[0]['phone'] && $user[0]['ok_to_contact']) echo "<!-- Phone --><div class='indexApprox_TL'>Phone</div><div class='indexedContentApprox_TL'><a href='tel:" . trim($user[0]['phone'], '- ') . "'>" . $user[0]['phone'] . "</a></div><div class='floatClear'></div>\n";
		/* Secondary */			if ($logged_in['is_administrator'] && $user[0]['secondary_phone'] && $user[0]['ok_to_contact']) echo "<!-- Secondary phone --><div class='indexApprox_TL'>Secondary phone</div><div class='indexedContentApprox_TL'><a href='tel:" . trim($user[0]['secondary_phone'], '- ') . "'>" . $user[0]['secondary_phone'] . "</a></div><div class='floatClear'></div>\n";
		/* SMS */				if ($logged_in['is_administrator'] && $user[0]['sms_notifications'] && $user[0]['ok_to_contact']) echo "<!-- SMS --><div class='indexApprox_TL'>Phone for SMS</div><div class='indexedContentApprox_TL'><a href='sms:" . trim($user[0]['sms_notifications'], '- ') . "'>" . $user[0]['sms_notifications'] . "</a></div><div class='floatClear'></div>\n";
		/* Rumours created */	if ($logged_in['is_administrator']) echo "<!-- Rumours created --><div class='indexApprox_TL'>Rumours reported</div><div class='indexedContentApprox_TL'>" . floatval($user[0]['rumours_created']) . "</div><div class='floatClear'></div>\n";
		/* Rumours assigned */	if ($logged_in['is_administrator']) echo "<!-- Rumours assigned --><div class='indexApprox_TL'>Rumours assigned</div><div class='indexedContentApprox_TL'>" . floatval($user[0]['rumours_assigned']) . "</div><div class='floatClear'></div>\n";
		/* Comments left */		if ($logged_in['is_administrator']) echo "<!-- Comments left --><div class='indexApprox_TL'>Comments</div><div class='indexedContentApprox_TL'>" . floatval($user[0]['comments_left']) . "</div><div class='floatClear'></div>\n";
		/* Registered */		if ($logged_in['is_administrator'] && $user[0]['registered_on'] != '0000-00-00 00:00:00') echo "<!-- Date registered --><div class='indexApprox_TL'>Date registered</div><div class='indexedContentApprox_TL'>" . date('F j, Y', strtotime($user[0]['registered_on'])) . "</div><div class='floatClear'></div>\n";
		/* Last login */		if ($logged_in['is_administrator'] && $user[0]['last_login'] != '0000-00-00 00:00:00') echo "<!-- Last login --><div class='indexApprox_TL'>Last login</div><div class='indexedContentApprox_TL'>" . date('F j, Y', strtotime($user[0]['last_login'])) . "</div><div class='floatClear'></div>\n";
		/* Termination */		if ($logged_in['is_administrator'] && !$user[0]['enabled'] && count($termination) > 0) {
									echo "<!-- Terminated on --><div class='indexApprox_TL'>Terminated on</div><div class='indexedContentApprox_TL'>" . date('F j, Y', strtotime($termination[0]['disabled_on'])) . "</div><div class='floatClear'></div>\n";
									echo "<!-- Reason --><div class='indexApprox_TL'>Reason</div><div class='indexedContentApprox_TL'>" . $termination[0]['reason'] . "</div><div class='floatClear'></div>\n";
		}
		/* Actions */			echo "<br />\n";
								echo "<!-- Actions -->\n";
								// viewing user's profile?
									if ($user[0]['username'] == $logged_in['username'] || ($logged_in['is_administrator'] && $logged_in['can_edit_users'])) {
										echo "  " . $form->input('button', 'update_account', null, false, 'Update account', 'btn btn-info', '', '', '', '', array('onClick'=>'document.location.href="/account/' . $user[0]['username'] . '"; return false;')) . "\n";
										echo "  " . $form->input('button', 'update_password', null, false, 'Update password', 'btn btn-link', '', '', '', '', array('onClick'=>'document.location.href="/update_password/' . $user[0]['username'] . '"; return false;')) . "\n";
										if ($logged_in['is_administrator'] && $logged_in['can_edit_users']) echo "  " . $form->input('button', 'update_api', null, false, 'Obtain API key', 'btn btn-link', '', '', '', '', array('onClick'=>'document.location.href="/obtain_api_key/' . $user[0]['username'] . '"; return false;')) . "\n";
								}
		echo "    " . $form->end() . "\n";
			
		echo "  </div>\n";
		
	echo "</div>\n";
	
	// Rumours created
		if (count($rumours) > 0) {
			echo "<div class='pageModule'>\n";
			echo "  <h2>Rumours reported</h2>\n";
			echo "  <table class='table table-hover table-condensed'>\n";
			echo "  <tr>\n";
			echo "  <th colspan='2'>Rumour</th>\n";
			echo "  <th>Status</th>\n";
			echo "  </tr>\n";
			for ($counter = 0; $counter < count($rumours); $counter++) {
				echo "  <tr>\n";
				echo "  <td>" . $parser->bubbleDate(date('d-M-Y', strtotime($rumours[$counter]['updated_on']))) . "</td>\n";
				echo "  <td><a href='/rumour/" . $rumours[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($rumours[$counter]['description']) . "'>" . $parser->truncate($rumours[$counter]['description'], 'c', 60) . "</a></td>\n";
				echo "  <td>" . $rumourStatuses[$rumours[$counter]['status']] . "</td>\n";
				echo "  </tr>\n";
			}
			echo "  </table>\n";
			echo "</div>\n";
		}
	
	// Comments
		if (count($comments) > 0) {
			echo "<div>\n";
			echo "  <h2>Comments</h2>\n";
			echo "  <table class='table table-hover table-condensed'>\n";
			echo "  <tr>\n";
			echo "  <th colspan='2'>Comment</th>\n";
			echo "  <th>Rumour</th>\n";
			echo "  </tr>\n";
			for ($counter = 0; $counter < count($comments); $counter++) {
				echo "  <tr>\n";
				echo "  <td>" . $parser->bubbleDate(date('d-M-Y', strtotime($comments[$counter]['updated_on']))) . "</td>\n";
				echo "  <td>" . $parser->truncate($comments[$counter]['comment'], 'c', 40) . "</td>\n";
				echo "  <td><a href='/rumour/" . $comments[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($comments[$counter]['description']) . "'>" . $parser->truncate($comments[$counter]['description'], 'c', 40) . "</a></td>\n";
				echo "  </tr>\n";
			}
			echo "  </table>\n";
			echo "</div>\n";
		}
		
	include 'includes/views/desktop/shared/page_bottom.php';
?>