<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// don't allow redundant login
		if ($logged_in) {
			header('Location: /logout');
			exit();
		}

	// parse query string
		$pageSuccess = $parameter1;
		if ($pageSuccess == 'redirect') $destination = $parameter2;
		elseif ($pageSuccess == 'invitation') $referredBy = $parameter2;
		elseif ($pageSuccess == 'gmail_login_cancelled') $pageError = "Your Gmail login was unsuccessful.";
		elseif ($pageSuccess == 'user_disabled') $pageError = "Unable to login via Gmail because account is disabled.";

	// instantiate required class(es)
		$validator = new inputValidator_TL();
		$operators = new operators_TL();
		$parser = new parser_TL();
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {

		if ($_POST['formName'] == 'loginForm') {

			// clean input
				$_POST = $parser->trimAll($_POST);
							
			// check for errors
				if (!$_POST['loginUsername']) $pageError .= "Please provide your username. ";
				if (!$_POST['loginPassword']) $pageError .= "Please provide your password. ";
				
			// check login
				if (!$pageError) {
					$error = '';
					$logged_in = confirmUser($_POST['loginUsername'], null, null, $_POST['loginPassword'], true);

					if ($logged_in) {
						$_SESSION['username'] = $_POST['loginUsername'];
						$_SESSION['password_hash'] = $logged_in['password_hash'];
						$cookieExpiryDate = time()+60*60*24 * floatval($numberOfDaysToPreserveLogin);
						setcookie("username", $_SESSION['username'], $cookieExpiryDate, '/', '.' . $environmentals['domain'], 0);
						setcookie("password_hash", $_SESSION['password_hash'], $cookieExpiryDate, '/', '.' . $environmentals['domain'], 0);
						if ($pageSuccess == 'redirect' && $destination) header ('Location: /' . trim(str_replace('|', '/', urldecode($destination))), '/');
						else header ('Location: /profile/' . $_SESSION['username']);
						exit();
					}
					else $pageError = $error;

				}
				
		}

		if ($_POST['formName'] == 'registrationForm') {

			// clean input
				$_POST = $parser->trimAll($_POST);
											
			// check for errors
				if (!$_POST['registerUsername']) $pageError .= "Please provide a username. ";
				if (!$validator->isStringValid($_POST['registerUsername'], '0123456789abcdefghijklmnopqrstuvwxyz-_', '')) $pageError .= "Your username can only contain alphanumeric characters. ";
				if ($validator->validateEmailRobust($_POST['email']) == false) $pageError .= "There appears to be a problem with your email address. ";
				if (!$_POST['first_name']) $pageError .= "Please provide a first name. ";
				if (!$_POST['last_name']) $pageError .= "Please provide a last name. ";
				if (!$_POST['country']) $pageError .= "Please provide a country. ";
				
				if (!$_POST['password']) $pageError .= "Please provide your password. ";
				elseif ($_POST['password'] != $_POST['confirm']) $pageError .= "Your password doesn't match the confirmation password. ";
				elseif (strlen($_POST['password']) > 72) $pageError .= "Please use a shorter password. ";
				else {
					$hasher = new PasswordHash(8, false);
					$hash = $hasher->HashPassword($_POST['password']);
					if (strlen($hash) < 20) $pageError .= "There was a problem securing your password, so rather than save it without appropriate security, we've cancelled this operation. Please try again. ";
				}

				$isUserAlreadyRegistered = retrieveUsers(array('email'=>$_POST['email']), null, null, null, 1);
				if (count($isUserAlreadyRegistered) > 0) $pageError .= "You appear to have already registered with this email address; please try to retrieve your password rather than registering a new account. ";
				$isUsernameAlreadyTaken = retrieveUsers(array('username'=>$_POST['registerUsername']), null, null, null, 1);
				if (count($isUsernameAlreadyTaken) > 0) $pageError .= "The username you've specified already belongs to another user. ";
				$isUsernameAlreadyTaken = retrieveRegistrants(array('username'=>$_POST['registerUsername']), null, null, null, 1);
				if (count($isUsernameAlreadyTaken) > 0) $pageError .= "The username you've specified already belongs to another user. ";

				if (@$referredBy) {
					$referral = retrieveUsers(array('username'=>$referredBy), null, null, null, 1);
					if (count($referral) < 1) {
						$pageError .= "You appear to have been referred here by another user, but that user profile is invalid. Please check the link you were provided. ";
					}
				}
				
			// register
				if ($pageError) $status = '';
				else {
					
					// save registration
						$encryption = new encrypter_TL();
						$key = $encryption->quickEncrypt($_POST['email'], rand(10000,99999));
						
						$doesRegistrationAlreadyExist = retrieveRegistrants(array('email'=>$_POST['email']), null, null, null, 1);
						
						if (count($doesRegistrationAlreadyExist) > 0) updateDb('registrations', array('username'=>$_POST['registerUsername'], 'first_name'=>@$_POST['first_name'], 'last_name'=>@$_POST['last_name'], 'country'=>@$_POST['country'], 'province_state'=>@$_POST['province_state'], 'other_province_state'=>@$_POST['other_province_state'], 'region'=>@$_POST['region'], 'phone'=>@$_POST['phone'], 'secondary_phone'=>@$_POST['secondary_phone'], 'sms_notifications'=>@$_POST['sms_notifications'], 'password_hash'=>$hash, 'registered_on'=>date('Y-m-d H:i:s'), 'registration_key'=>$key, 'referred_by'=>@$referral[0]['user_id']), array('email'=>@$_POST['email']), null, null, null, null, 1);
						else insertIntoDb('registrations', array('username'=>$_POST['registerUsername'], 'first_name'=>@$_POST['first_name'], 'last_name'=>@$_POST['last_name'], 'email'=>@$_POST['email'], 'country'=>@$_POST['country'], 'province_state'=>@$_POST['province_state'], 'other_province_state'=>@$_POST['other_province_state'], 'region'=>@$_POST['region'], 'phone'=>@$_POST['phone'], 'secondary_phone'=>@$_POST['secondary_phone'], 'sms_notifications'=>@$_POST['sms_notifications'], 'password_hash'=>$hash, 'registered_on'=>date('Y-m-d H:i:s'), 'registration_key'=>$key, 'referred_by'=>@$referral[0]['user_id']));
					
					// send confirmation request email
						$emailSent = emailRegistrationKey(trim($_POST['first_name'] . ' ' . $_POST['last_name']), $_POST['email'], $key);

					// confirmation
						if ($emailSent) {
							header ('Location: /login_register/registration_success');
							exit();
						}
						else $pageError .= "Unable to email your registration confirmation. Please try registering again, and if you continue to encounter difficulties, <a href='/contact' class='errorMessage'>let us know</a>. ";
				}
					
		}

	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
			
	else {
	}
		
?>