<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$username = $parameter1;
		if (!$username) {
			header('Location: /account/' . $logged_in['username']);
			exit();
		}
				
		$status = $parameter2;
		
	// authenticate user
		if (!$logged_in) forceLoginThenRedirectHere();
		
		if ($username != $logged_in['username']) {
			if (!$logged_in['can_edit_users']) {
				header ('Location: /404');
				exit;
			}
		}

	// queries
		if ($logged_in['is_administrator']) $user = retrieveUsers(array('username'=>$username), null, null, null, 1);
		else $user = retrieveUsers(array('username'=>$username, 'enabled'=>'1'), null, null, null, 1);
		if (count($user) < 1) {
			header('Location: /404');
			exit();
		}
		
		$termination = retrieveFromDb('user_terminations', array('user_id'=>$user[0]['user_id']), null, null, null, null, null, 1);
		
		$minimumProfileImageWidth = 0;
		foreach ($profileImageSizes_TL as $size => $width) {
			if ($width > $minimumProfileImageWidth) $minimumProfileImageWidth = $width;
		}
		
	// instantiate required class(es)
		$profileImage = new avatarManager_TL();
		$fileManager = new fileManager_TL();
		$validator = new inputValidator_TL();
		$operators = new operators_TL();
		$parser = new parser_TL();
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
		
	if (count($_POST) > 0) {
		
		if ($_POST['formName'] == 'profileForm' && $_POST['deleteCurrentProfileImage'] == 'Y') {

			// delete profile images (& verify deletion)
				$success = $profileImage->deleteProfileImage($user[0]['username']);
				if (!$success) $pageError .= "There was a problem deleting this profile image. ";
									
		}
		elseif ($_POST['formName'] == 'profileForm') {

			// clean input
				$_POST = $parser->trimAll($_POST);
				$checkboxesToParse = array('enabled', 'ok_to_contact', 'ok_to_show_profile', 'is_proxy', 'is_moderator', 'is_community_liaison', 'is_administrator', 'is_tester', 'can_edit_content', 'can_edit_settings', 'can_edit_users', 'can_send_email', 'can_run_housekeeping');
				foreach ($checkboxesToParse as $checkbox) {
					if (isset($_POST[$checkbox])) $_POST[$checkbox] = 1;
					else $_POST[$checkbox] = 0;
				}
				
			// check for errors
				if (!$_POST['username']) $pageError .= "Please provide a username. ";
				if (!$validator->isStringValid($_POST['username'], 'abcdefghijklmnopqrstuvwxyz0123456789-_', '')) $pageError .= "Your username can only contain alphanumeric characters. ";
				if ($_POST['email'] && !$validator->validateEmailRobust($_POST['email'])) $pageError .= "There appears to be a problem with your email address. ";
				if (!$_POST['first_name']) $pageError .= "Please provide a first name. ";
				if (!$_POST['last_name']) $pageError .= "Please provide a last name. ";
				if ($_POST['username'] != $user[0]['username']) {
					$numberOfExistingUsers = countInDb('users', 'user_id', array('username'=>$_POST['username']), null, null, null);
					$numberOfExistingRegistrants = countInDb('registrations', 'registration_id', array('username'=>$_POST['username']), null, null, null);
					if ($numberOfExistingUsers[0]['count'] + $numberOfExistingRegistrants[0]['count'] > 0) $pageError .= "The username you've specified already belongs to another user. ";
				}
				if ($_POST['email'] && ($_POST['email'] != $user[0]['email'])) {
					$numberOfExistingUsers = countInDb('users', 'user_id', array('email'=>$_POST['email']), null, null, null);
					$numberOfExistingRegistrants = countInDb('registrations', 'registration_id', array('email'=>$_POST['email']), null, null, null);
					if ($numberOfExistingUsers[0]['count'] + $numberOfExistingRegistrants[0]['count'] > 0) $pageError .= "The email address you've specified already belongs to another user. ";
				}
				if (!$_POST['country']) $pageError .= "Please provide a country. ";
				
				if ($_FILES['profile_image']['tmp_name']) {
					if (!$fileManager->isImage($_FILES['profile_image']['tmp_name'])) $pageError .= "An invalid image was uploaded; please upload a JPG, PNG or GIF. ";
					else {
						$dimensions = getimagesize($_FILES['profile_image']['tmp_name']);
						if ($dimensions[0] < $minimumProfileImageWidth) $pageError .= "Your uploaded profile image appears to be too small. Please make sure that the width is no less than " . floatval($minimumProfileImageWidth) . " pixels. ";
					}
				}
								
			// update profile
				if (!$pageError) {

					// update username and real name
						updateDb('users', array('username'=>$_POST['username'], 'first_name'=>$_POST['first_name'], 'last_name'=>$_POST['last_name']), array('user_id'=>$user[0]['user_id']), null, null, null, null, 1);

						if ($user[0]['user_id'] == $logged_in['user_id']) {
							$_SESSION['username'] = $_POST['username'];
							$cookieExpiryDate = time()+60*60*24 * floatval($numberOfDaysToPreserveLogin);
							setcookie("username", $_SESSION['username'], $cookieExpiryDate, '', '', 0);
						}
						
					// update country, province/state, city, etc.
						updateDb('users', array('country'=>$_POST['country'], 'province_state'=>$_POST['province_state'], 'other_province_state'=>$_POST['other_province_state'], 'region'=>$_POST['region'], 'phone'=>$_POST['phone'], 'secondary_phone'=>$_POST['secondary_phone'], 'sms_notifications'=>$_POST['sms_notifications'], 'ok_to_contact'=>$_POST['ok_to_contact'], 'ok_to_show_profile'=>$_POST['ok_to_show_profile']), array('user_id'=>$user[0]['user_id']), null, null, null, null, 1);
						
					// update test mode, etc.
						if ($logged_in['can_edit_users']) {
							updateDb('users', array('is_moderator'=>$_POST['is_moderator'], 'is_proxy'=>$_POST['is_proxy'], 'is_administrator'=>$_POST['is_administrator'], 'is_community_liaison'=>$_POST['is_community_liaison'], 'is_tester'=>$_POST['is_tester']), array('user_id'=>$user[0]['user_id']), null, null, null, null, 1);
						}
						
					// update permissions
						if ($logged_in['can_edit_users']) {
							deleteFromDb('user_permissions', array('user_id'=>$user[0]['user_id']), null, null, null, null, 1);
							if ($_POST['is_administrator']) insertIntoDb('user_permissions', array('user_id'=>$user[0]['user_id'], 'can_edit_content'=>$_POST['can_edit_content'], 'can_edit_settings'=>$_POST['can_edit_settings'], 'can_edit_users'=>$_POST['can_edit_users'], 'can_send_email'=>$_POST['can_send_email'], 'can_run_housekeeping'=>$_POST['can_run_housekeeping']));
						}

					// update enabled
						if ($logged_in['can_edit_users'] && $logged_in['username'] != $user[0]['username']) {
							updateDb('users', array('enabled'=>$_POST['enabled']), array('user_id'=>$user[0]['user_id']), null, null, null, null, 1);
							if (!$_POST['enabled']) {
								deleteFromDb('user_terminations', array('user_id'=>$user[0]['user_id']), null, null, null, null, 1);
								insertIntoDb('user_terminations', array('reason'=>$_POST['reason']), array('user_id'=>$user[0]['user_id'], 'disabled_by'=>$logged_in['user_id'], 'disabled_on'=>date('Y-m-d H:i:s')));
							}
						}

					// update email, if different
						if ($_POST['email'] != $user[0]['email']) {
							if ($logged_in['is_administrator'] || !$_POST['email']) updateDb('users', array('email'=>$_POST['email']), array('user_id'=>$user[0]['user_id']), null, null, null, null, 1);
							else {
								$encryption = new encrypter_TL();
								$emailKey = $encryption->quickEncrypt($_POST['email'], rand(10000,99999));
								$expiryDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + $numberOfDaysToPreserveEmailKey, date('Y'))); // one week
								
								deleteFromDb('user_keys', array('name'=>'Reset Email', 'user_id'=>$user_in[0]['user_id']), null, null, null);
								insertIntoDb('user_keys', array('name'=>'Reset Email', 'user_id'=>$user_in[0]['user_id'], 'hash'=>$emailKey, 'value'=>$_POST['email'], 'saved_on'=>$expiryDate));
								
								$emailSent = emailNewEmailKey($logged_in['full_name'], addSlashes($_POST['email']), $emailKey);
								if (!$emailSent) $pageError = "Unable to send your email reset link. Please try updating again, and if you continue to encounter difficulties, <a href='/contact' class='errorMessage'>let us know</a>. ";
							}
						}

					// update profile image
						if ($_FILES['profile_image']['tmp_name']) {

							// delete old image
								$success = $profileImage->deleteProfileImage($user[0]['username']);
								if (!$success) $pageError .= "There was a problem deleting this profile image. ";
								
							// save new image
								$success = $profileImage->createProfileImage($user[0]['username'], $_FILES['profile_image']['tmp_name']);
								if (!$success) $pageError .= "There was a problem saving this profile image. ";
							
						}
				}
				
				if (!$pageError) {
					// update log
						if ($logged_in['user_id'] != $user[0]['user_id']) $activity = $logged_in['full_name'] . " (" . $logged_in['user_id'] . ") has updated the profile of " . $user[0]['full_name'] . " (" . $user[0]['user_id'] . ")";
						else $activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated his/her own profile";
						$logger->logItInDb($activity);
						
					// redirect
						header('Location: /profile/' . $_POST['username'] . '/account_updated');
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