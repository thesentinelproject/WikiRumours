<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$key = $tl->page['parameter1'];

	// validate query string
		$doesKeyExist = retrieveSingleFromDb('user_keys', null, array('user_key'=>'Reset Password', 'hash'=>$key));
		if (!count($doesKeyExist)) $authentication_manager->forceRedirect('/login_register/error=bad_key');

		$doesUserExist = retrieveUsers(array($tablePrefix . 'users.user_id'=>$doesKeyExist[0]['user_id'], $tablePrefix . 'users.enabled'=>'1'), null, null, null, 1);
		if (!count($doesUserExist)) $authentication_manager->forceRedirect('/login_register/error=bad_user');

/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
				
	if (count($_POST) > 0) {

		// clean input
			$_POST = $parser->trimAll($_POST);
									
		// check for errors
			if (!$_POST['password']) $tl->page['error'] .= "Please provide your password. ";
			elseif ($_POST['password'] != $_POST['confirm']) $tl->page['error'] .= "Your password doesn't match the confirmation password. ";
			elseif (strlen($_POST['password']) > 72 || strlen($_POST['confirm']) > 72) $tl->page['error'] .= "Please use a shorter password. ";
			else {
				$hasher = new PasswordHash(8, false);
				$hash = $hasher->HashPassword($_POST['password']);
				if (strlen($hash) < 20) $tl->page['error'] .= "There was a problem securing your password, so rather than save it without appropriate security, we've cancelled this operation. Please try again. ";
			}
			
		// retrieve user details and reset password if appropriate
			if (!$tl->page['error']) {

				// calculate password score
					$score = floatval($security_manager->checkPasswordStrength($_POST['password'])) * 100;

				// update user
					updateDbSingle('users', array('password_hash'=>$hash, 'password_score'=>$score), array('user_id'=>$doesKeyExist[0]['user_id']));

				// update log
					$activity = $doesUserExist[0]['full_name'] . " has successfully updated his/her password";
					$logger->logItInDb($activity, null, array('user_id=' . $doesKeyExist[0]['user_id']));

					$attributableOutput = $attributable->capture($activity, null, ['user_id'=>$doesUserExist[0]['user_id'], 'first_name'=>$doesUserExist[0]['first_name'], 'last_name'=>$doesUserExist[0]['last_name'], 'email'=>$doesUserExist[0]['email'], 'phone'=>$doesUserExist[0]['primary_phone']], ['domain_alias_id'=>@$tl->page['domain_alias']['cms_id']]);
					if (!@count($attributableOutput['content']['success'])) emailSystemNotification(__FILE__ . ": " . (is_array($attributableOutput) ? print_r($attributableOutput, true) : $attributableOutput) . (@$logged_in ? " [" . $logged_in['username'] . "]" : false), 'Attributable failure');

				// remove key
					deleteFromDb('user_keys', array('user_key'=>'Reset Password', 'user_id'=>$doesKeyExist[0]['user_id']));

				// redirect
					$authentication_manager->forceRedirect('/login_register/success=password_reset_successful');

			}


	}
	
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
			
	else {
	}
		
?>