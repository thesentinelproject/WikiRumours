<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$key = $parameter1;
		$pageSuccess = $parameter2;

	// validate query string
		$doesKeyExist = retrieveFromDb('user_keys', array('name'=>'Reset Password', 'hash'=>$key), null, null, null, null, null, 1);
		
	// instantiate required class(es)
		$parser = new parser_TL();
		
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
			
			$doesKeyExist = retrieveFromDb('user_keys', array('name'=>'Reset Password', 'user_id'=>$doesKeyExist[0]['user_id']), null, null, null, null, null, 1);
			if (count($doesKeyExist) < 1) $pageError .= "Invalid key. Please check the link which brought you here. ";
			else {
				$logged_in = retrieveUsers(array($tablePrefix . 'users.user_id'=>$doesKeyExist[0]['user_id'], $tablePrefix . 'users.enabled'=>'1'), null, null, null, 1);
				if (count($logged_in) < 1) $pageError = "There's something wrong with the user account you're trying to modify; it may have been inactivated. ";
			}
			
		// retrieve user details and reset password if appropriate
			if (!$pageError) {
				// update user
					updateDb('users', array('password_hash'=>$hash), array('user_id'=>$doesKeyExist[0]['user_id']), null, null, null, null, 1);
				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $doesKeyExist[0]['user_id'] . ") has successfully updated his/her password";
					$logger->logItInDb($activity);
				// remove key
					deleteFromDb('user_keys', array('name'=>'Reset Password', 'user_id'=>$doesKeyExist[0]['user_id']), null, null, null);
				// redirect
					header('Location: /reset_password/' . $key . '/success');
					exit();
			}

	}
	
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
			
	else {
	}
		
?>