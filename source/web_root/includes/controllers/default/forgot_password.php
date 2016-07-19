<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {

		// clean input
			$_POST = $parser->trimAll($_POST);
									
		// check for errors
			if (!$input_validator->validateEmailRobust($_POST['email'])) $tl->page['error'] .= "There appears to be a problem with your email address. ";
			
		// retrieve user details and send reset email if appropriate
			if (!$tl->page['error']) {
				$doesUserExist = retrieveUsers(array('email'=>$_POST['email'], 'enabled'=>'1'), null, null, null, 1);
				if (count($doesUserExist) > 0) {
					// create key and expiry
						$encryption = new encrypter_TL();
						$key = $encryption->quickEncrypt($_POST['email'], $salts_TL['public_keys']);
						$expiryDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + $systemPreferences['Password reset link active for'], date('Y'))); // one week
					// save key to database with expiry
						deleteFromDb('user_keys', array('user_key'=>'Reset Password', 'user_id'=>$doesUserExist[0]['user_id']));
						insertIntoDb('user_keys', array('user_id'=>$doesUserExist[0]['user_id'], 'user_key'=>'Reset Password', 'hash'=>$key, 'saved_on'=>date('Y-m-d H:i:s'), 'expiry'=>$expiryDate));
					// update log
						$activity = trim($doesUserExist[0]['first_name'] . ' ' . $doesUserExist[0]['last_name']) . " (user_id " . $doesUserExist[0]['user_id'] . ") has requested a password reset";
						$logger->logItInDb($activity, null, array('user_id=' . $doesUserExist[0]['user_id']));
					// email user
						$emailSent = emailPasswordResetKey(trim($doesUserExist[0]['first_name'] . ' ' . $doesUserExist[0]['last_name']), addSlashes($_POST['email']), $key);
						if (!$emailSent) $tl->page['error'] = "Unable to email your password reset link. Please try again, and if you continue to encounter difficulties, <a href='/contact' class='errorMessage'>let us know</a>. ";
					
				}
				else {
					// do nothing;
					// an invalid email address isn't acknowledged, since this would indicate
					// to a hacker that a portion of the login credentials are correct
				}
				
			}

			if (!$tl->page['error']) $authentication_manager->forceRedirect('/forgot_password/success=password_reset_link_sent');

	}
	
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>