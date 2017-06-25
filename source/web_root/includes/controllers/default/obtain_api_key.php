<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$username = $tl->page['parameter1'];
		if (!$username) $authentication_manager->forceRedirect('/obtain_api_key/' . $logged_in['username']);
				
	// authenticate user
		if (!$logged_in) $authentication_manager->forceLoginThenRedirectHere();
		
		if ($username != $logged_in['username']) {
			if (!$logged_in['is_administrator'] || !$logged_in['can_edit_users']) $authentication_manager->forceRedirect('/404');
		}

	// queries
		if ($username == $logged_in['username']) {
			$user = array();
			$user[0] = $logged_in;
		}
		else {
			$user = retrieveUsers(array('username'=>$username, 'enabled'=>'1'), null, null, null, 1);
			if (count($user) < 1) $authentication_manager->forceRedirect('/404');
		}
		
		$apiKey = retrieveSingleFromDb('user_keys', null, array('user_id'=>$user[0]['user_id'], 'user_key'=>'API'));
		if (count($apiKey) > 0) {
			$allQueries = countInDb('api_calls_internal', 'id', array('api_key'=>$apiKey[0]['hash']));
			$expiry = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') - 1, date('Y')));
			$recentQueries = countInDb('api_calls_internal', 'id', array('api_key'=>$apiKey[0]['hash']), null, null, null, "queried_on > '" . $expiry . "'");
		}
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
		
	if (count($_POST) > 0) {

		if ($_POST['formName'] == 'apiForm' && @$_POST['allowUnlimited'] == 'Y') {
			$success = updateDb('user_keys', array('value'=>'u'), array('user_key'=>'API', 'user_id'=>$user[0]['user_id']));
			if (!$success) $tl->page['error'] .= "Unable to update API query threshold. ";
			else {
				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has provided unlimited API downloads for " . $username;
					$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id']));
				// redirect
					$authentication_manager->forceRedirect('/obtain_api_key/' . $username . '/success=query_threshold_updated');
			}
		}
		elseif ($_POST['formName'] == 'apiForm' && @$_POST['removeUnlimited'] == 'Y') {
			$success = updateDb('user_keys', array('value'=>''), array('user_key'=>'API', 'user_id'=>$user[0]['user_id']));
			if (!$success) $tl->page['error'] .= "Unable to update API query threshold. ";
			else {
				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has terminated unlimited API downloads for " . $username;
					$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id']));
				// redirect
					$authentication_manager->forceRedirect('/obtain_api_key/' . $username . '/success=query_threshold_updated');
			}
		}
		elseif ($_POST['formName'] == 'apiForm') {

			// delete old API key
				deleteFromDb('user_keys', array('user_id'=>$user[0]['user_id'], 'user_key'=>'API'), null, null, null, null, 1);

			// create API key
				$newApiKey = $encrypter->quickEncrypt($user[0]['user_id'] . rand(100000, 999999));

			// save API key
				insertIntoDb('user_keys', array('user_id'=>$user[0]['user_id'], 'user_key'=>'API', 'hash'=>$newApiKey, 'saved_on'=>date('Y-m-d H:i:s')));
				
			// update record of previous API calls (if an API was previously assigned)
				if ($apiKey[0]['hash']) updateDb('api_calls_internal', array('api_key'=>$newApiKey), array('api_key'=>$apiKey[0]['hash']));

			// update log
				if ($logged_in['user_id'] != $user[0]['user_id']) {
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has obtained an API key on behalf of " . trim($user[0]['full_name']) . " (user_id " . $user[0]['user_id'] . ")";
					$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'user_id=' . $user[0]['user_id']));
				}
				else {
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has obtained an API key";
					$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id']));
				}

			// redirect
				$authentication_manager->forceRedirect('/obtain_api_key/' . $username . '/success=key_generated');
				
		}

	}
	
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>