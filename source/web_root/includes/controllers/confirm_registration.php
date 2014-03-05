<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */
	
	$key = $parameter1;
	$confirmed = false;

	// check whether ID is valid
		$registration = retrieveRegistrants(array('registration_key'=>$key), null, null, null, 1);
		if (count($registration) < 1) $pageError = "There's something wrong with the link that brought you here. Please check that the link is complete or rekey it by hand; sometimes mail readers cut a link in two by inserting an inopportune line break. ";
		else {
			// add registrant to user table
				$user_id = insertIntoDb('users', array('username'=>$registration[0]['username'], 'password_hash'=>$registration[0]['password_hash'], 'first_name'=>$registration[0]['first_name'], 'last_name'=>$registration[0]['last_name'], 'email'=>$registration[0]['email'], 'phone'=>$registration[0]['phone'], 'secondary_phone'=>$registration[0]['secondary_phone'], 'sms_notifications'=>$registration[0]['sms_notifications'], 'country'=>$registration[0]['country'], 'province_state'=>$registration[0]['province_state'], 'other_province_state'=>$registration[0]['other_province_state'], 'region'=>$registration[0]['region'], 'registered_on'=>$registration[0]['registered_on'], 'ok_to_contact'=>'1', 'enabled'=>'1', 'referred_by'=>$registration[0]['referred_by']));
				if (!$user_id) $pageError = "Unable to activate your registration. Please <a href='/contact'>let us know</a> so that we can sort this out. ";
				else {
					// add registered by
						updateDb('users', array('registered_by'=>$user_id), array('user_id'=>$user_id), null, null, null, null, 1);
					// retrieve Gravatar
						$largestWidth = 0;
						foreach ($profileImageSizes_TL as $type=>$width) {
							if ($width > $largestWidth) $largestWidth = $width;
						}
						$avatar = new avatarManager_TL();
						$gravatarPath = $avatar->retrieveGravatar($registration[0]['email'], $largestWidth);
						if ($gravatarPath) $avatar->createProfileImage($registration[0]['username'], $gravatarPath);
					// check if this is the first or only (enabled) user, and if so, make admin with all priveleges
						$existingUsers = retrieveUsers(null, null);
						if (count($existingUsers) == 1) {
							updateDb('users', array('is_administrator'=>'1'), array('user_id'=>$user_id), null, null, null, null, 1);
							insertIntoDb('user_permissions', array('user_id'=>$user_id, 'can_edit_content'=>'1', 'can_edit_settings'=>'1', 'can_edit_users'=>'1', 'can_send_email'=>'1', 'can_run_housekeeping'=>'1'));
						}
					
					// delete registrant data
						deleteFromDb('registrations', array('registration_key'=>$key), null, null, null, null, 1);

					// update log
						$activity = $registration[0]['full_name'] . " has completed registration at " . $systemPreferences['appName'];
						$logger->logItInDb($activity);
						
					// send welcome email
						emailNewUser($registration[0]['full_name'], $registration[0]['email']);

					// notify admins, if necessary
						$notifications = retrieveFromDb('notifications', array('new_registrations'=>'1'), null, null, null);
						
						for ($counter = 0; $counter < count($notifications); $counter++) {
							$emailSent = emailAdministratorAboutNewUser($notifications[$counter]['email'], $registration[0]['full_name']);
						}
						
					// update status
						$confirmed = true;
				}
		}

/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {
	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */

	else {
	}
	
?>