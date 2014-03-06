<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$pageSuccess = $parameter1;
		
	// instantiate required class(es)
		$validator = new inputValidator_TL();
		$parser = new parser_TL();
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {

		// clean input
			$_POST = $parser->trimAll($_POST);
									
		// check for errors
			if (!$validator->validateEmailRobust($_POST['email'])) $pageError .= "There appears to be a problem with your email address. ";
			
		// retrieve user details and send reset email if appropriate
			if (!$pageError) {
				$doesUserExist = retrieveUsers(array('email'=>$_POST['email'], 'enabled'=>'1'), null, null, null, 1);
				if (count($doesUserExist) > 0) {
					// create key and expiry
						$encryption = new encrypter_TL();
						$key = $encryption->quickEncrypt($_POST['email'], $salts_TL['public_keys']);
						$expiryDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + $numberOfDaysToPreservePasswordKey, date('Y'))); // one week
					// save key to database with expiry
						deleteFromDb('user_keys', array('name'=>'Reset Password', 'user_id'=>$doesUserExist[0]['user_id']), null, null, null);
						insertIntoDb('user_keys', array('user_id'=>$doesUserExist[0]['user_id'], 'name'=>'Reset Password', 'hash'=>$key, 'expiry'=>$expiryDate));
					// update log
						$activity = trim($doesUserExist[0]['first_name'] . ' ' . $doesUserExist[0]['last_name']) . " (" . $doesUserExist[0]['user_id'] . ") has requested a password reset";
						$logger->logItInDb($activity);
					// email user
						$emailSent = emailPasswordResetKey(trim($doesUserExist[0]['first_name'] . ' ' . $doesUserExist[0]['last_name']), addSlashes($_POST['email']), $key);
						if (!$emailSent) $pageError = "Unable to email your password reset link. Please try again, and if you continue to encounter difficulties, <a href='/contact' class='errorMessage'>let us know</a>. ";
					
				}
				else {
					// do nothing;
					// an invalid email address isn't acknowledged, since this would indicate
					// to a hacker that a portion of the login credentials are correct
				}
				
			}

			if (!$pageError) {
				header ('Location: /forgot_password/success');
				exit();
			}

	}
	
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>