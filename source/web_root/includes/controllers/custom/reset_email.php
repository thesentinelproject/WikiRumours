<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$key = $tl->page['parameter1'];
		
	// validate query string
		$doesKeyExist = retrieveSingleFromDb('user_keys', null, array('user_key'=>'Reset Email', 'hash'=>$key));
		if (!count($doesKeyExist)) $tl->page['error'] = 'bad_key';
		else {
			$doesEmailAlreadyBelongToUser = retrieveSingleFromDb('users', 'email', array('email'=>$doesKeyExist[0]['value']));
			if (count($doesEmailAlreadyBelongToUser)) $tl->page['error'] = 'duplicate_email';
			else {

				// force logout if user is logged in under another account
					if (@$logged_in && @$logged_in['user_id'] != $doesKeyExist[0]['user_id']) $authentication_manager->forceRedirect('/logout/redirect/' . urlencode('reset_email/' . $key));

				// retrieve user
					$user = retrieveUsers(array($tablePrefix . 'users.user_id'=>$doesKeyExist[0]['user_id']), null, null, null, 1);
				// update user
					updateDb('users', array('email'=>$doesKeyExist[0]['value']), array('user_id'=>$user[0]['user_id']), null, null, null, null, 1);
				// update log
					$activity = $user[0]['full_name'] . " has successfully updated his/her email address";
					$logger->logItInDb($activity, null, array('user_id=' . $user[0]['user_id']));

					$attributableOutput = $attributable->capture($activity, null, ['user_id'=>$user[0]['user_id'], 'first_name'=>$user[0]['first_name'], 'last_name'=>$user[0]['last_name'], 'email'=>$user[0]['email'], 'phone'=>$user[0]['primary_phone']], ['domain_alias_id'=>@$tl->page['domain_alias']['cms_id']]);
					if (!@count($attributableOutput['content']['success'])) emailSystemNotification(__FILE__ . ": " . (is_array($attributableOutput) ? print_r($attributableOutput, true) : $attributableOutput) . (@$logged_in ? " [" . $logged_in['username'] . "]" : false), 'Attributable failure');
				// remove key
					deleteFromDb('user_keys', array('user_key'=>'Reset Email', 'hash'=>$key));
				// update session variable if set
					if (@$_SESSION['email']) {
						$_SESSION['email'] = $doesKeyExist[0]['value'];
						$cookieExpiryDate = time()+60*60*24 * floatval($tl->settings['Keep users logged in for']);
						setcookie("email", $_SESSION['email'], $cookieExpiryDate, '', '', 0);
					}

			}
				
		}

		// redirect
			$authentication_manager->forceRedirect('/login_register/' . ($tl->page['error'] ? 'error=' . $tl->page['error'] : 'success=email_reset_successful'));
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