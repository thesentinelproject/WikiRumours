<?php

	echo "    <h2>Update Account</h2>\n";
			
	echo $form->start('profileForm', null, 'post', null, array('enctype'=>'multipart/form-data'), array('onSubmit'=>'validateProfileForm(); return false;')) . "\n";
	echo $form->input('hidden', 'deleteCurrentProfileImage') . "\n";
	if ($logged_in['can_edit_users'] && $logged_in['username'] != $user[0]['username']) {
		/* Enabled */	echo $form->row('yesno_bootstrap_switch', 'enabled', $operators->firstTrueStrict(@$_POST['enabled'], @$user[0]['enabled']), false, 'Enabled?', null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default'), null, array('onChange'=>'revealOrHideTerminationReason();'));
						echo "  <div id='termination_notes' class='collapse";
						if (!$operators->firstTrueStrict(@$_POST['enabled'], @$user[0]['enabled'])) echo " in";
						echo "'>\n";
						echo $form->row('textarea', 'reason', $operators->firstTrueStrict(@$_POST['reason'], @$termination[0]['reason']), false, 'Reason', 'form-control');
						echo "  </div>\n";
	}
	/* Username */		echo $form->row('text', 'username', $operators->firstTrue(@$_POST['username'], @$user[0]['username']), true, 'Username', 'form-control', '', 30);
	/* Email */			echo $form->row('email', 'email', $operators->firstTrue(@$_POST['email'], @$user[0]['email']), false, 'Email', 'form-control', '', 100);
	/* Phone */			echo $form->rowStart('primary_phone', 'Primary Phone');
						echo "  <div class='row'>\n";
						echo "    <div class='col-lg-6 col-md-4 col-sm-4 col-xs-4'>" . $form->input('tel', 'primary_phone', $operators->firstTrue(@$_POST['primary_phone'], @$user[0]['primary_phone']), false, null, 'form-control', '', 12) . "</div>\n";
						echo "    <div class='col-lg-3 col-md-4 col-sm-4 col-xs-4 text-right'><label class='control-label'>SMS?</label></div>\n";
						echo "    <div class='col-lg-3 col-md-4 col-sm-4 col-xs-4 text-right'>" . $form->input('yesno_bootstrap_switch', 'primary_phone_sms', $operators->firstTrueStrict(@$_POST['primary_phone_sms'], @$user[0]['primary_phone_sms']), false, null, null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default')) . "</div>\n";
						echo "  </div>\n";
						echo $form->rowEnd();
	/* 2nd Phone */		echo $form->rowStart('secondary_phone', 'Secondary Phone');
						echo "  <div class='row'>\n";
						echo "    <div class='col-lg-6 col-md-4 col-sm-4 col-xs-4'>" . $form->input('tel', 'secondary_phone', $operators->firstTrue(@$_POST['secondary_phone'], @$user[0]['secondary_phone']), false, null, 'form-control', '', 12) . "</div>\n";
						echo "    <div class='col-lg-3 col-md-4 col-sm-4 col-xs-4 text-right'><label class='control-label'>SMS?</label></div>\n";
						echo "    <div class='col-lg-3 col-md-4 col-sm-4 col-xs-4 text-right'>" . $form->input('yesno_bootstrap_switch', 'secondary_phone_sms', $operators->firstTrueStrict(@$_POST['secondary_phone_sms'], @$user[0]['secondary_phone_sms']), false, null, null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default')) . "</div>\n";
						echo "  </div>\n";
						echo $form->rowEnd();
	/* Anonymized */	echo $form->row('yesno_bootstrap_switch', 'ok_to_contact', $operators->firstTrueStrict(@$_POST['ok_to_contact'], @$user[0]['ok_to_contact']), false, "OK to <a href='javascript:void(0)' class='tooltips' onClick='return false' data-toggle='tooltip' title='Choosing NO will prevent WikiRumours from contacting you, regardless of other profile settings. Use with caution.'>contact</a>?", null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default'));
						echo $form->row('yesno_bootstrap_switch', 'anonymous', $operators->firstTrueStrict(@$_POST['anonymous'], @$user[0]['anonymous']), false, "<a href='javascript:void(0)' class='tooltips' onClick='return false' data-toggle='tooltip' title='WikiRumours is a community-based platform. Please avoid hiding your profile unless you have significant security or privacy concerns.'>Anonymous</a>?", null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default'));
	/* Name */			echo $form->rowStart('name', 'Name');
						echo "  <div class='row'>\n";
						echo "    <div class='col-md-6'>" . $form->input('text', 'first_name', $operators->firstTrue(@$_POST['first_name'], @$user[0]['first_name']), false, '|First', 'form-control', '', 30) . "</div>\n";
						echo "    <div class='col-md-6'>" . $form->input('text', 'last_name', $operators->firstTrue(@$_POST['last_name'], @$user[0]['last_name']), false, '|Last', 'form-control', '', 30) . "</div>\n";
						echo "  </div>\n";
						echo $form->rowEnd();
	/* Country */		echo $form->row('country', 'country_id', $operators->firstTrue(@$_POST['country_id'], @$user[0]['country_id']), false, "Country", 'form-control') . "\n";
	/* Region */		echo $form->row('region', 'region', ['country_id'=>$operators->firstTrue(@$_POST['country_id'], @$user[0]['country_id']), 'region_id'=>$operators->firstTrue(@$_POST['region_id'], @$user[0]['region_id']), 'region_other'=>$operators->firstTrue(@$_POST['region_other'], @$user[0]['other_region'])], false, "Region", 'form-control', null, null, ['link-to'=>'country_id']) . "\n";
	/* Community */		echo $form->row('text', 'city', $operators->firstTrue(@$_POST['city'], @$user[0]['city']), false, 'Community', 'form-control', '', 50) . "\n";
	/* Profile Image */	echo $form->rowStart('profile_image', "Profile image");
						$profileImage = 'uploads/profile_images/' . $encrypter->quickEncrypt($user[0]['username'], $tl->salts['public_keys']) . '_small.jpg';
						if (!file_exists($profileImage)) $profileImage = "libraries/tidal_lock/php/dynamic_thumbnailer.php?source=../../../resources/img/default_profile_image.jpg&desired_width=" . $profileImageSizes['small'];
						echo "  <div><img src='/" . $profileImage . "' border='0' class='img-thumbnail' alt='" . htmlspecialchars($user[0]['full_name'], ENT_QUOTES) . "' /></div>\n";
						echo "  <div>" . $form->input('file', 'profile_image', '', false, 'New profile image') . "</div>\n";
						echo $form->rowEnd();
	if ($logged_in['can_edit_users']) {
		/* Proxy */			echo $form->row('yesno_bootstrap_switch', 'is_proxy', $operators->firstTrueStrict(@$_POST['is_proxy'], @$user[0]['is_proxy']), false, 'Is proxy?', null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default'));
		/* Moderation */	echo $form->row('yesno_bootstrap_switch', 'is_moderator', $operators->firstTrueStrict(@$_POST['is_moderator'], @$user[0]['is_moderator']), false, 'Is moderator?', null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default'));
		/* Liaison */		echo $form->row('yesno_bootstrap_switch', 'is_community_liaison', $operators->firstTrueStrict(@$_POST['is_community_liaison'], @$user[0]['is_community_liaison']), false, 'Is community liaison?', null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default'));
		/* Administrator */	echo $form->row('yesno_bootstrap_switch', 'is_administrator', $operators->firstTrueStrict(@$_POST['is_administrator'], @$user[0]['is_administrator']), false, 'Is administrator?', null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default'), null, array('onChange'=>'revealOrHidePermissions();'));
		/* Permissions */	echo "  <div id='admin_permissions' class='collapse";
							if ($operators->firstTrueStrict(@$_POST['is_administrator'], @$user[0]['is_administrator'])) echo " in";
							echo "'>\n";
							echo $form->row('checkbox', 'can_edit_content', $operators->firstTrueStrict(@$_POST['can_edit_content'], @$user[0]['can_edit_content']), false, 'Can edit content?');
							echo $form->row('checkbox', 'can_update_settings', $operators->firstTrueStrict(@$_POST['can_update_settings'], @$user[0]['can_update_settings']), false, 'Can update settings?');
							echo $form->row('checkbox', 'can_edit_settings', $operators->firstTrueStrict(@$_POST['can_edit_settings'], @$user[0]['can_edit_settings']), false, 'Can edit settings?');
							echo $form->row('checkbox', 'can_edit_users', $operators->firstTrueStrict(@$_POST['can_edit_users'], @$user[0]['can_edit_users']), false, 'Can edit users?');
							echo $form->row('checkbox', 'can_send_email', $operators->firstTrueStrict(@$_POST['can_send_email'], @$user[0]['can_send_email']), false, 'Can send email?');
							echo $form->row('checkbox', 'can_run_housekeeping', $operators->firstTrueStrict(@$_POST['can_run_housekeeping'], @$user[0]['can_run_housekeeping']), false, 'Can run housekeeping?');
							echo "  </div>\n";
		/* Test mode */		echo $form->row('yesno_bootstrap_switch', 'is_tester', $operators->firstTrueStrict(@$_POST['is_tester'], @$user[0]['is_tester']), false, 'Is tester?', null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default'));
	}
	/* Actions */		echo "<br />\n";
						echo $form->rowStart('actions');
						echo "    " . $form->input('submit', 'update', null, false, 'Update', 'btn btn-medium btn-info') . "\n";
						if (@$image['sizes']['small']) echo "    " . $form->input('button', 'delete', null, false, 'Delete current profile image', 'btn btn-medium btn-link', '', '', '', '', array('onClick'=>'deleteProfileImage(); return false;')) . "\n";
						echo "    " . $form->input('button', 'cancel', null, false, 'Cancel', 'btn btn-link', '', '', '', '', array('onClick'=>'document.location.href="/profile/' . $username . '"; return false;')) . "\n";
						echo $form->rowEnd();

	echo $form->end() . "\n";

?>