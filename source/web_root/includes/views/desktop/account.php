<?php
	include 'includes/views/desktop/shared/page_top.php';

	if ($pageError) echo "<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $pageError . "</div>\n";
	
	echo "    <h2>Update Account</h2>\n";
			
	echo $form->start('profileForm', null, 'post', null, array('enctype'=>'multipart/form-data'), array('onSubmit'=>'validateProfileForm(); return false;')) . "\n";
	echo $form->input('hidden', 'deleteCurrentProfileImage') . "\n";
	if ($logged_in['can_edit_users'] && $logged_in['username'] != $user[0]['username']) {
		/* Enabled */	echo $form->row('yesno_bootstrap_switch', 'enabled', $operators->firstTrueStrict(@$_POST['enabled'], @$user[0]['enabled']), false, 'Enabled?', null, null, null, null, null, array('onChange'=>'revealOrHideTerminationReason();'));
						echo "  <div id='termination_notes' class='collapse";
						if (!$operators->firstTrueStrict(@$_POST['enabled'], @$user[0]['enabled'])) echo " in";
						echo "'>\n";
						echo $form->row('textarea', 'reason', $operators->firstTrueStrict(@$_POST['reason'], @$termination[0]['reason']), false, 'Reason', 'form-control');
						echo "  </div>\n";
	}
	/* Username */		echo $form->row('text', 'username', $operators->firstTrue(@$_POST['username'], @$user[0]['username']), true, 'Username', 'form-control', '', 30);
	/* Email */			echo $form->row('email', 'email', $operators->firstTrue(@$_POST['email'], @$user[0]['email']), false, 'Email', 'form-control', '', 100);
	/* Phone */			echo $form->row('tel', 'phone', $operators->firstTrue(@$_POST['phone'], @$user[0]['phone']), false, 'Phone', 'form-control', '', 12);
	/* 2nd Phone */		echo $form->row('tel', 'secondary_phone', $operators->firstTrue(@$_POST['secondary_phone'], @$user[0]['secondary_phone']), false, 'Secondary phone', 'form-control', '', 12);
	/* SMS */			echo $form->row('tel', 'sms_notifications', $operators->firstTrue(@$_POST['sms_notifications'], @$user[0]['sms_notifications']), false, 'Phone for SMS notifications', 'form-control', '', 12);
	/* Anonymized */	echo "<!-- OK to contact -->\n";
						echo "  <div class='formLabel'>OK to <a href='javascript:void(0)' class='tooltips' onClick='return false' data-toggle='tooltip' title='Choosing NO will prevent WikiRumours from contacting you, regardless of other profile settings. Use with caution.'>contact</a>?</div>\n";
						echo "  <div class='formField'>\n";
						echo "    " . $form->input('yesno_bootstrap_switch', 'ok_to_contact', $operators->firstTrueStrict(@$_POST['ok_to_contact'], @$user[0]['ok_to_contact']), false, 'OK to contact?') . "\n";
						echo "  </div>\n";
						echo "  <div class='floatClear'></div>\n";
						echo "<!-- OK to show profile -->\n";
						echo "  <div class='formLabel'>OK to <a href='javascript:void(0)' class='tooltips' onClick='return false' data-toggle='tooltip' title='WikiRumours is a community-based platform. Please avoid hiding your profile unless you have significant security or privacy concerns.'>show profile</a>?</div>\n";
						echo "  <div class='formField'>\n";
						echo "    " . $form->input('yesno_bootstrap_switch', 'ok_to_show_profile', $operators->firstTrueStrict(@$_POST['ok_to_show_profile'], @$user[0]['ok_to_show_profile']), false, 'OK to show profile?') . "\n";
						echo "  </div>\n";
						echo "  <div class='floatClear'></div>\n";
						$pageJavaScript .= "// tooltips\n";
						$pageJavaScript .= "  $('.tooltips').tooltip({\n";
						$pageJavaScript .= "    placement: 'right',\n\n";
						$pageJavaScript .= "    delay: 250\n\n";
						$pageJavaScript .= "  });\n\n";
	/* Name */			echo "<!-- Name -->\n";
						echo "  <div class='formLabel'>Name</div>\n";
						echo "  <div class='formField row'>\n";
						echo "    <div class='col-md-6'>" . $form->input('text', 'first_name', $operators->firstTrue(@$_POST['first_name'], @$user[0]['first_name']), false, '|First', 'form-control', '', 30) . "</div>\n";
						echo "    <div class='col-md-6'>" . $form->input('text', 'last_name', $operators->firstTrue(@$_POST['last_name'], @$user[0]['last_name']), false, '|Last', 'form-control', '', 30) . "</div>\n";
						echo "  </div>\n";
						echo "  <div class='floatClear'></div>\n";
	/* Country */		echo $form->row('country', 'country', $operators->firstTrue(@$_POST['country'], @$user[0]['country']), true, 'Country', 'form-control') . "\n";
	/* ProvinceState */	echo $form->row('provinceState', 'province_state', $operators->firstTrue(@$_POST['province_state'], @$user[0]['province_state']), false, 'Province/State', 'form-control') . "\n";
	/* Other */			echo $form->row('text', 'other_province_state', $operators->firstTrue(@$_POST['other_province_state'], @$user[0]['other_province_state']), false, 'Other province/state', 'form-control', '', 50);
	/* CityRegion */	echo $form->row('text', 'region', $operators->firstTrue(@$_POST['region'], @$user[0]['region']), false, 'City/Region', 'form-control', '', 50) . "\n";
	/* Profile Image */	echo "<!-- Current profile image -->\n";
						echo "  <div class='formLabel'>Current profile image</div>\n";
						echo "  <div class='formField'>\n";
						$image = $profileImage->retrieveProfileImage($user[0]['username']);
						if (@$image['sizes']['small']) echo "    <img src='/" . $image['sizes']['small'] . "' border='0' class='img-thumbnail' alt='Current profile image' />\n";
						else echo "  <img src='../libraries/tidal_lock/php/dynamic_thumbnailer.php?source=../../../resources/img/default_profile_image.jpg&desired_width=" . $profileImageSizes_TL['small'] . "' border='0' class='img-thumbnail' alt='" . htmlspecialchars($user[0]['full_name'], ENT_QUOTES) . "' />\n";
						echo "  </div>\n";
						echo "  <div class='floatClear'></div>\n";
						echo $form->row('file', 'profile_image', '', false, 'New profile image') . "\n";
	if ($logged_in['can_edit_users']) {
		/* Proxy */			echo $form->row('yesno_bootstrap_switch', 'is_proxy', $operators->firstTrueStrict(@$_POST['is_proxy'], @$user[0]['is_proxy']), false, 'Is proxy?');
		/* Moderation */	echo $form->row('yesno_bootstrap_switch', 'is_moderator', $operators->firstTrueStrict(@$_POST['is_moderator'], @$user[0]['is_moderator']), false, 'Is moderator?');
		/* Liaison */		echo $form->row('yesno_bootstrap_switch', 'is_community_liaison', $operators->firstTrueStrict(@$_POST['is_community_liaison'], @$user[0]['is_community_liaison']), false, 'Is community liaison?');
		/* Administrator */	echo $form->row('yesno_bootstrap_switch', 'is_administrator', $operators->firstTrueStrict(@$_POST['is_administrator'], @$user[0]['is_administrator']), false, 'Is administrator?', null, null, null, null, null, array('onChange'=>'revealOrHidePermissions();'));
		/* Permissions */	echo "  <div id='admin_permissions' class='collapse";
							if ($operators->firstTrueStrict(@$_POST['is_administrator'], @$user[0]['is_administrator'])) echo " in";
							echo "'>\n";
							echo $form->row('checkbox', 'can_edit_content', $operators->firstTrueStrict(@$_POST['can_edit_content'], @$user[0]['can_edit_content']), false, 'Can edit content?');
							echo $form->row('checkbox', 'can_edit_settings', $operators->firstTrueStrict(@$_POST['can_edit_settings'], @$user[0]['can_edit_settings']), false, 'Can edit settings?');
							echo $form->row('checkbox', 'can_edit_users', $operators->firstTrueStrict(@$_POST['can_edit_users'], @$user[0]['can_edit_users']), false, 'Can edit users?');
							echo $form->row('checkbox', 'can_send_email', $operators->firstTrueStrict(@$_POST['can_send_email'], @$user[0]['can_send_email']), false, 'Can send email?');
							echo $form->row('checkbox', 'can_run_housekeeping', $operators->firstTrueStrict(@$_POST['can_run_housekeeping'], @$user[0]['can_run_housekeeping']), false, 'Can run housekeeping?');
							echo "  </div>\n";
		/* Test mode */		echo $form->row('yesno_bootstrap_switch', 'is_tester', $operators->firstTrueStrict(@$_POST['is_tester'], @$user[0]['is_tester']), false, 'Is tester?');
	}
	/* Actions */		echo "<br />\n";
						echo "<!-- Actions -->\n";
						echo "  <div class='formLabel'></div>\n";
						echo "  <div class='formField'>\n";
						echo "    " . $form->input('submit', 'update', null, false, 'Update', 'btn btn-medium btn-info') . "\n";
						if (@$image['sizes']['small']) echo "    " . $form->input('button', 'delete', null, false, 'Delete current profile image', 'btn btn-medium btn-link', '', '', '', '', array('onClick'=>'deleteProfileImage(); return false;')) . "\n";
						echo "    " . $form->input('button', 'cancel', null, false, 'Cancel', 'btn btn-link', '', '', '', '', array('onClick'=>'document.location.href="/profile/' . $username . '"; return false;')) . "\n";
						echo "  </div>\n";
						echo "  <div class='floatClear'></div>\n";

	echo $form->end() . "\n";

	include 'includes/views/desktop/shared/page_bottom.php';
?>