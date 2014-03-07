<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$username = $parameter1;
		if (!$username) {
			header('Location: /profile/' . $logged_in['username']);
			exit();
		}
		
		$pageSuccess = $parameter2;
		
	// queries
		$countries = array();
		$result = retrieveFromDb('countries', null, null, null, null, null, 'country ASC');
		for ($counter = 0; $counter < count($result); $counter++) {
			$countries[$result[$counter]['country_id']] = $result[$counter]['country'];
		}		
		
		if ($logged_in['is_proxy'] || $logged_in['is_moderator'] || $logged_in['is_administrator']) $user = retrieveUsers(array('username'=>$username), null, null, null, 1);
		else $user = retrieveUsers(array('username'=>$username, 'enabled'=>'1'), null, null, null, 1);
		if (count($user) < 1) {
			// can't find user
				header('Location: /404');
				exit();
		}
		elseif (!$user[0]['ok_to_show_profile'] && $user[0]['username'] != $logged_in['username'] && !$logged_in['is_proxy'] && !$logged_in['is_moderator'] && !$logged_in['is_administrator']) {
			// not allowed to see profile
				header('Location: /404');
				exit();
		}
		
		$termination = retrieveFromDb('user_terminations', array('user_id'=>$user[0]['user_id']), null, null, null, null, null, 1);
		$rumours = retrieveRumours(array('created_by'=>$user[0]['user_id'], $tablePrefix . 'rumours.enabled'=>'1'), null ,null, $tablePrefix . 'rumours.updated_on DESC', '0,50');
		$comments = retrieveComments(array($tablePrefix . 'comments.created_by'=>$user[0]['user_id'], $tablePrefix . 'comments.enabled'=>'1', $tablePrefix . 'rumours.enabled'=>'1'), null ,null, $tablePrefix . 'comments.created_on DESC', '0,50');
		
	// instantiate required class(es)
		$profileImage = new avatarManager_TL();
		$parser = new parser_TL();
			
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