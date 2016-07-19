<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in['is_administrator'] || !$logged_in['can_update_settings']) $authentication_manager->forceLoginThenRedirectHere(true);

	// parse query string
		$screen = @$tl->page['filters']['screen'];
		$id = floatval(@$tl->page['filters']['id']);

		if (!$screen) $screen = 'all';
		elseif ($screen && $screen != 'edit_banned_ip' && $screen != 'add_banned_ip') $authentication_manager->forceRedirect('/admin_security');
		elseif ($screen == 'edit_banned_ip' && !$id) $authentication_manager->forceRedirect('/admin_security');

	// queries
		if ($screen == 'edit_banned_ip') {
			$banned = retrieveSingleFromDb('banned_ips', null, ['banned_id'=>$id]);
			if (!count($banned)) $authentication_manager->forceRedirect('/admin_security');
			$tl->page['title'] = "Edit a blocked IP";
		}
		elseif ($screen == 'add_banned_ip') {
			$tl->page['title'] = "Block an IP";
		}
		else {
			$banned = retrieveFromDb('banned_ips', null, null, null, null, null, null, null, "attempts DESC, country_id ASC, city ASC, banned_on ASC");
			$tl->page['title'] = "Blocked IPs";
		}

	$localization_manager->populateCountries();

	$tl->page['section'] = "Administration";
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {
		
		$tl->page['error'] = '';

		if ($_POST['formName'] == 'updateBannedIpForm' && @$_POST['unblockRequested'] == 'Y' && $id) {

			// delete
				$success = deleteFromDbSingle('banned_ips', ['banned_id'=>$id]);
				if (!$success) $tl->page['error'] .= "There was a problem unblocking this IP. ";
				else {

					// update log
						$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has unblocked IP " . $id;
						$logger->logItInDb($activity, null, ['user_id=' . $logged_in['user_id'], 'banned_id=' . $id]);

					// redirect
						$authentication_manager->forceRedirect('/admin_security/success=ip_unblocked');

				}

		}
		elseif ($_POST['formName'] == 'updateBannedIpForm') {

			// clean input
				$_POST = $parser->trimAll($_POST);

			// check for errors
				if ($screen == 'add_banned_ip') {
					if (!$_POST['ip']) $tl->page['error'] .= "Please specify an IP number.\n";
					else {
						$exists = retrieveSingleFromDb('banned_ips', null, ['ip'=>$_POST['ip']]);
						if ($exists) $tl->page['error'] .= "This IP is already being blocked.\n";
					}
				}

				if (!$tl->page['error']) {

					// update DB

						if ($screen == 'add_banned_ip') {
							$detector->connection['ip'] = $_POST['ip'];
							$detector->connection();
							$id = insertIntoDb('banned_ips', ['ip'=>$_POST['ip'], 'country_id'=>$detector->connection['country'], 'city'=>$detector->connection['city']]);
						}

						updateDbSingle('banned_ips', ['notes'=>@$_POST['notes'], 'banned_by'=>$logged_in['user_id'], 'banned_on'=>date('Y-m-d H:i:s')], ['banned_id'=>$id]);

					// update log
						$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has " . ($screen == 'add_banned_ip' ? "started blocking the IP " . $_POST['ip'] : "updated blocked IP " . $banned[0]['ip']);
						$logger->logItInDb($activity, null, ['user_id=' . $logged_in['user_id'], 'banned_id=' . $id]);
					
					// redirect
						$authentication_manager->forceRedirect('/admin_security/success=ip_blocked');

				}

		}

	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>