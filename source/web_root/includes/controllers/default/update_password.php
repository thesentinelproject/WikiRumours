<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$username = $tl->page['parameter1'];
		if (!$username) $authentication_manager->forceRedirect('/update_password/' . $logged_in['username']);
				
	// authenticate user
		if (!$logged_in) $authentication_manager->forceLoginThenRedirectHere();
		
		if ($username != $logged_in['username']) {
			if (!$logged_in['is_administrator'] || !$logged_in['can_edit_users']) $authentication_manager->forceRedirect('/404');
		}

	// queries
		$user = retrieveUsers(array('username'=>$username, 'enabled'=>'1'), null, null, null, 1);
		if (count($user) < 1) $authentication_manager->forceRedirect('/404');
		
	$tl->page['section'] = 'Administration';
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
		
	if (count($_POST) > 0) {

		if ($_POST['formName'] == 'passwordForm') {

			// clean input
				$_POST = $parser->trimAll($_POST);
								
			// check for errors
				if (!$logged_in['is_administrator']) {
					$hasher = new PasswordHash(8, false);
					$check = $hasher->CheckPassword($_POST['old_password'], $user[0]['password_hash']);
					if (!$check) $tl->page['error'] .= "Your old password is incorrect. ";
					unset($hasher);
				}

				if (!$_POST['password']) $tl->page['error'] .= "Please provide a new password. ";
				elseif (strlen($_POST['password']) > 72 || strlen($_POST['confirm_new_password']) > 72) $tl->page['error'] .= "Please use a shorter password. ";
				elseif ($_POST['password'] != $_POST['confirm_new_password']) $tl->page['error'] .= "Your new password doesn't match the confirmation password. ";
				else {
					$hasher = new PasswordHash(8, false);
					$hash = $hasher->HashPassword($_POST['password']);
					if (strlen($hash) < 20) $tl->page['error'] .= "There was a problem securing your password, so rather than save it without appropriate security, we've cancelled this operation. Please try again. ";
				}
									
			// update password and cookies
				if (!$tl->page['error']) {

					$score = floatval($security_manager->checkPasswordStrength($_POST['password'])) * 100;

					updateDb('users', array('password_hash'=>$hash, 'password_score'=>$score), array('user_id'=>$user[0]['user_id']), null, null, null, null, 1);

					if ($user[0]['user_id'] == $logged_in['user_id']) {
						$_SESSION['password_hash'] = $hash;
						$cookieExpiryDate = time()+60*60*24 * floatval($systemPreferences['Keep users logged in for']);
						setcookie("password_hash", $_SESSION['password_hash'], $cookieExpiryDate, '', '', 0);
					}
					
				}
				
			if (!$tl->page['error']) {
					// update log
						if ($logged_in['user_id'] != $user[0]['user_id']) $activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated the password of " . $user[0]['full_name'] . " (" . $user[0]['user_id'] . ")";
						else $activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated his/her own password";
						$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id']));
					// redirect
						$authentication_manager->forceRedirect('/profile/' . $user[0]['username'] . '/success=password_updated');
			}
			
		}

	}
	
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>