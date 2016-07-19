<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// don't allow redundant login
		if ($logged_in) $authentication_manager->forceRedirect('/logout/' . trim($tl->page['parameter1'] . '/' . $tl->page['parameter2'] . '/' . $tl->page['parameter3'] . '/' . $tl->page['parameter4'], '/ '));

	// parse query string
		if ($tl->page['parameter1'] == 'redirect') $destination = trim(str_replace('|', '/', $tl->page['parameter2']), '/');
		elseif ($tl->page['parameter1'] == 'invitation') $referredBy = $tl->page['parameter2'];

	$tl->page['title'] = 'Login or Register';

/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {

		if ($_POST['formName'] == 'loginForm') {

			// clean input
				$_POST = $parser->trimAll($_POST);
							
			// check for errors
				if (!$_POST['loginUsername']) $tl->page['error'] .= "Please provide your username. ";
				if (!$_POST['loginPassword']) $tl->page['error'] .= "Please provide your password. ";
				
			// check login
				if (!$tl->page['error']) {

					$logged_in = $authentication_manager->confirmUser('username', $_POST['loginUsername'], null, $_POST['loginPassword']);

					if (!@$logged_in['error']) {

						// set cookies
							$_SESSION['username'] = $_POST['loginUsername'];
							$_SESSION['password_hash'] = $logged_in['password_hash'];
							$cookieExpiryDate = time()+60*60*24 * floatval($systemPreferences['Keep users logged in for']);
							setcookie("username", $_SESSION['username'], $cookieExpiryDate, '/', '.' . $tl->page['domain'], 0);
							setcookie("password_hash", $_SESSION['password_hash'], $cookieExpiryDate, '/', '.' . $tl->page['domain'], 0);

						// capture user agent metadata
							$detector->browser();
							$detector->connection();
							insertIntoDb('browser_connections', array('connected_on'=>date('Y-m-d H:i:s'), 'user_id'=>$logged_in['user_id'], 'user_agent'=>$detector->browser['user_agent'], 'ip'=>$detector->connection['ip'], 'country_id'=>$detector->connection['country']));

						// calculate password score, if doesn't already exist
							if (!$logged_in['password_score']) {
								$score = floatval($security_manager->checkPasswordStrength($_POST['loginPassword'])) * 100;
								if ($score) updateDbSingle('users', ['password_score'=>$score], ['user_id'=>$logged_in['user_id']]);
							}

						// redirect
							if (@$destination) $authentication_manager->forceRedirect('/' . $destination);
							else $authentication_manager->forceRedirect('/profile/' . $_SESSION['username']);

					}
					else {
						$tl->page['error'] = $logged_in['error'];
						unset ($logged_in);
					}

				}
				
		}

		if ($_POST['formName'] == 'registrationForm') {

			// clean input
				$_POST = $parser->trimAll($_POST);
				$checkboxesToParse = array('primary_phone_sms', 'secondary_phone_sms');
				foreach ($checkboxesToParse as $checkbox) {
					if (isset($_POST[$checkbox])) $_POST[$checkbox] = 1;
					else $_POST[$checkbox] = 0;
				}
											
			// check for errors
				if (!$_POST['registerUsername']) $tl->page['error'] .= "Please provide a username. ";
				if (!$input_validator->isStringValid($_POST['registerUsername'], '0123456789abcdefghijklmnopqrstuvwxyz-_', '')) $tl->page['error'] .= "Your username can only contain alphanumeric characters. ";
				if ($input_validator->validateEmailRobust($_POST['email']) == false) $tl->page['error'] .= "There appears to be a problem with your email address. ";
				if (!$_POST['first_name']) $tl->page['error'] .= "Please provide a first name. ";
				if (!$_POST['last_name']) $tl->page['error'] .= "Please provide a last name. ";
				if (!$_POST['country_id']) $tl->page['error'] .= "Please provide a country. ";
				
				if (!$_POST['password']) $tl->page['error'] .= "Please provide your password. ";
				elseif ($_POST['password'] != $_POST['confirm']) $tl->page['error'] .= "Your password doesn't match the confirmation password. ";
				elseif (strlen($_POST['password']) > 72) $tl->page['error'] .= "Please use a shorter password. ";
				else {
					$hasher = new PasswordHash(8, false);
					$hash = $hasher->HashPassword($_POST['password']);
					if (strlen($hash) < 20) $tl->page['error'] .= "There was a problem securing your password, so rather than save it without appropriate security, we've cancelled this operation. Please try again. ";
				}

				$isUserAlreadyRegistered = retrieveUsers(array('email'=>$_POST['email']), null, null, null, 1);
				if (count($isUserAlreadyRegistered) > 0) $tl->page['error'] .= "You appear to have already registered with this email address; please try to retrieve your password rather than registering a new account. ";
				$isUsernameAlreadyTaken = retrieveUsers(array('username'=>$_POST['registerUsername']), null, null, null, 1);
				if (count($isUsernameAlreadyTaken) > 0) $tl->page['error'] .= "The username you've specified already belongs to another user. ";
				$isUsernameAlreadyTaken = retrieveRegistrants(array('username'=>$_POST['registerUsername']), null, null, null, 1);
				if (count($isUsernameAlreadyTaken) > 0) $tl->page['error'] .= "The username you've specified already belongs to another user. ";

				if (@$referredBy) {
					$referral = retrieveUsers(array('username'=>$referredBy), null, null, null, 1);
					if (count($referral) < 1) {
						$tl->page['error'] .= "You appear to have been referred here by another user, but that user profile is invalid. Please check the link you were provided. ";
					}
				}
				
			// register
				if ($tl->page['error']) $status = '';
				else {
		
					// notify admins of possible duplicate user
						if ($_POST['primary_phone']) {
							$isPhoneNumberAlreadyRegistered = retrieveUsers(null, null, "(primary_phone != '' AND REPLACE(primary_phone, '-', '') LIKE '%" . substr(str_replace('-','', $_POST['primary_phone']), -7) . "') OR (secondary_phone != '' AND REPLACE(secondary_phone, '-', '') LIKE '%" . substr(str_replace('-','', $_POST['primary_phone']), -7) . "')", null, 1);
						}
						if (!count(@$isPhoneNumberAlreadyRegistered) && $_POST['secondary_phone']) {
							$isPhoneNumberAlreadyRegistered = retrieveUsers(null, null, "(primary_phone != '' AND REPLACE(primary_phone, '-', '') LIKE '%" . substr(str_replace('-','', $_POST['secondary_phone']), -7) . "') OR (secondary_phone != '' AND REPLACE(secondary_phone, '-', '') LIKE '%" . substr(str_replace('-','', $_POST['secondary_phone']), -7) . "')", null, 1);
						}
						if (count(@$isPhoneNumberAlreadyRegistered)) {
							$admins = retrieveFromDb('notifications', null, ['duplicate_users'=>'1']);
							for ($counter = 0; $counter < count($admins); $counter++) {
								warnAdministratorOfDuplicateUserPhoneNumber($admins[$counter]['notification_email'], $_POST['registerUsername'], $isPhoneNumberAlreadyRegistered[0]['username']);
							}
						}
					// save registration
						$encryption = new encrypter_TL();
						$key = $encryption->quickEncrypt($_POST['email'], rand(10000,99999));
						
						$doesRegistrationAlreadyExist = retrieveRegistrants(array('email'=>$_POST['email']), null, null, null, 1);

						$score = floatval($security_manager->checkPasswordStrength($_POST['password'])) * 100;
						
						if (count($doesRegistrationAlreadyExist) > 0) updateDb('registrations', array('username'=>$_POST['registerUsername'], 'first_name'=>@$_POST['first_name'], 'last_name'=>@$_POST['last_name'], 'country_id'=>$_POST['country_id'], 'region_id'=>$_POST['region_id'], 'other_region'=>$_POST['region_other'], 'city'=>$_POST['city'], 'primary_phone'=>@$_POST['primary_phone'], 'primary_phone_sms'=>@$_POST['primary_phone_sms'], 'secondary_phone'=>@$_POST['secondary_phone'], 'secondary_phone_sms'=>@$_POST['secondary_phone_sms'], 'password_hash'=>$hash, 'password_score'=>$score, 'registered_on'=>date('Y-m-d H:i:s'), 'registration_key'=>$key, 'referred_by'=>@$referral[0]['user_id']), array('email'=>@$_POST['email']), null, null, null, null, 1);
						else insertIntoDb('registrations', array('username'=>$_POST['registerUsername'], 'first_name'=>@$_POST['first_name'], 'last_name'=>@$_POST['last_name'], 'email'=>@$_POST['email'], 'country_id'=>$_POST['country_id'], 'region_id'=>$_POST['region_id'], 'other_region'=>$_POST['region_other'], 'city'=>$_POST['city'], 'primary_phone'=>@$_POST['primary_phone'], 'primary_phone_sms'=>@$_POST['primary_phone_sms'], 'secondary_phone'=>@$_POST['secondary_phone'], 'secondary_phone_sms'=>@$_POST['secondary_phone_sms'], 'password_hash'=>$hash, 'password_score'=>$score, 'registered_on'=>date('Y-m-d H:i:s'), 'registration_key'=>$key, 'referred_by'=>@$referral[0]['user_id']));
					
					// send confirmation request email
						$emailSent = emailRegistrationKey(trim($_POST['first_name'] . ' ' . $_POST['last_name']), $_POST['email'], $key);

					// confirmation
						if ($emailSent) $authentication_manager->forceRedirect('/login_register/success=registration_received');
						else $tl->page['error'] .= "Unable to email your registration confirmation. Please try registering again, and if you continue to encounter difficulties, <a href='/contact' class='errorMessage'>let us know</a>. ";
				}
					
		}

	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
			
	else {
	}
		
?>