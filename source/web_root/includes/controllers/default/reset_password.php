<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$key = $parameter1;

	// validate query string
		$doesKeyExist = retrieveSingleFromDb('user_keys', null, array('user_key'=>'Reset Password', 'hash'=>$key));
		if (!count($doesKeyExist)) {
			header('Location: /login_register/bad_key');
			exit();
		}
		$doesUserExist = retrieveUsers(array($tablePrefix . 'users.user_id'=>$doesKeyExist[0]['user_id'], $tablePrefix . 'users.enabled'=>'1'), null, null, null, 1);
		if (!count($doesUserExist)) {
			header('Location: /login_register/bad_user');
			exit();
		}

/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
				
	if (count($_POST) > 0) {

		// clean input
			$_POST = $parser->trimAll($_POST);
									
		// check for errors
			if (!$_POST['password']) $pageError .= "Please provide your password. ";
			elseif ($_POST['password'] != $_POST['confirm']) $pageError .= "Your password doesn't match the confirmation password. ";
			elseif (strlen($_POST['password']) > 72 || strlen($_POST['confirm']) > 72) $pageError .= "Please use a shorter password. ";
			else {
				$hasher = new PasswordHash(8, false);
				$hash = $hasher->HashPassword($_POST['password']);
				if (strlen($hash) < 20) $pageError .= "There was a problem securing your password, so rather than save it without appropriate security, we've cancelled this operation. Please try again. ";
			}
			
		// retrieve user details and reset password if appropriate
			if (!$pageError) {
				// update user
					updateDbSingle('users', array('password_hash'=>$hash), array('user_id'=>$doesKeyExist[0]['user_id']));
				// update log
					$activity = $doesUserExist[0]['full_name'] . " (user_id " . $doesKeyExist[0]['user_id'] . ") has successfully updated his/her password";
					$logger->logItInDb($activity, null, array('user_id=' . $doesKeyExist[0]['user_id']));
				// remove key
					deleteFromDb('user_keys', array('user_key'=>'Reset Password', 'user_id'=>$doesKeyExist[0]['user_id']));
				// redirect
					header('Location: /login_register/password_reset_successful');
					exit();
			}


	}
	
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
			
	else {
	}
		
?>