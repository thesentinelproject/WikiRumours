<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$key = $parameter1;
		
	// validate query string
		$doesKeyExist = retrieveSingleFromDb('user_keys', null, array('user_key'=>'Reset Email', 'hash'=>$key));
		if (!count($doesKeyExist)) $pageError = 'bad_key';
		else {
			$doesEmailAlreadyBelongToUser = retrieveSingleFromDb('users', 'email', array('email'=>$doesKeyExist[0]['value']));
			if (count($doesEmailAlreadyBelongToUser)) $pageError = 'duplicate_email';
			else {

				// force logout if user is logged in under another account
					if (@$logged_in && @$logged_in['user_id'] != $doesKeyExist[0]['user_id']) {
						header('Location: /logout/redirect/' . urlencode('reset_email/' . $key));
						exit();
					}

				// retrieve user
					$user = retrieveUsers(array($tablePrefix . 'users.user_id'=>$doesKeyExist[0]['user_id']), null, null, null, 1);
				// update user
					updateDb('users', array('email'=>$doesKeyExist[0]['value']), array('user_id'=>$user[0]['user_id']), null, null, null, null, 1);
				// update log
					$activity = $user[0]['full_name'] . " (user_id " . $user[0]['user_id'] . ") has successfully updated his/her email address";
					$logger->logItInDb($activity, null, array('user_id=' . $user[0]['user_id']));
				// remove key
					deleteFromDb('user_keys', array('user_key'=>'Reset Email', 'hash'=>$key));
				// update session variable if set
					if (@$_SESSION['email']) {
						$_SESSION['email'] = $doesKeyExist[0]['value'];
						$cookieExpiryDate = time()+60*60*24 * floatval($systemPreferences['Keep users logged in for']);
						setcookie("email", $_SESSION['email'], $cookieExpiryDate, '', '', 0);
					}

			}
				
		}

		// redirect
			header('Location: /login_register/' . ($pageError ? $pageError : 'email_reset_successful'));
			exit();

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