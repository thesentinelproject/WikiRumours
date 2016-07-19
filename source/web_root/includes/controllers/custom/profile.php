<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$username = $tl->page['parameter1'];
		if (!$username) $authentication_manager->forceRedirect('/profile/' . $logged_in['username']);
		
	// queries
		$countries = array();
		$result = retrieveFromDb('countries', null, null, null, null, null, null, null, 'country ASC');
		for ($counter = 0; $counter < count($result); $counter++) {
			$countries[$result[$counter]['country_id']] = $result[$counter]['country'];
		}		
		
		if ($logged_in['is_proxy'] || $logged_in['is_moderator'] || $logged_in['is_administrator']) $user = retrieveUsers(array('username'=>$username), null, null, null, 1);
		else $user = retrieveUsers(array('username'=>$username, 'enabled'=>'1'), null, null, null, 1);
		if (count($user) < 1) {
			// can't find user
				$authentication_manager->forceRedirect('/404');
		}
		elseif ($user[0]['anonymous'] && $user[0]['username'] != $logged_in['username'] && !$logged_in['is_proxy'] && !$logged_in['is_moderator'] && !$logged_in['is_administrator']) {
			// not allowed to see profile
				$authentication_manager->forceRedirect('/404');
		}
		
		$termination = retrieveSingleFromDb('user_terminations', null, array('user_id'=>$user[0]['user_id']));
		$rumours = retrieveRumours(array('created_by'=>$user[0]['user_id'], $tablePrefix . 'rumours.enabled'=>'1'), null ,null, $tablePrefix . 'rumours.updated_on DESC', '0,50');
		$comments = retrieveComments(array($tablePrefix . 'comments.created_by'=>$user[0]['user_id'], $tablePrefix . 'comments.enabled'=>'1', $tablePrefix . 'rumours.enabled'=>'1'), null ,null, $tablePrefix . 'comments.created_on DESC', '0,50');
		$recentActivities = retrieveLogs(array('relationship_name'=>'user_id', 'relationship_value'=>$user[0]['user_id']), null, null, 'connected_on DESC');
		$logins = retrieveFromDb('browser_connections', null, array('user_id'=>$user[0]['user_id']), null, null, null, null, null, "connected_on DESC");

		$localization_manager->populateCountries();

	$tl->page['title'] = null;
	$tl->page['section'] = "Profile";

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