<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$key = $parameter1;
		$pageStatus = $parameter2;
		
	// validate query string
		$doesKeyExist = retrieveSingleFromDb('user_keys', null, array('name'=>'Reser Email', 'hash'=>$key));
		if (count($doesKeyExist) < 1) $pageError = "There's something wrong with the link that brought you here. Please check that the link is complete or rekey it by hand; sometimes mail readers cut a link in two by inserting an inopportune line break. ";
		else {
			// retrieve user
				$user = retrieveUsers(array('user_id'=>$doesKeyExist[0]['user_id']), null, null, null, 1);
			// update user
				updateDb('users', array('email'=>$doesKeyExist[0]['value']), array('user_id'=>$user[0]['user_id']), null, null, null, null, 1);
			// update log
				$activity = $user[0]['full_name'] . " (user_id " . $user[0]['user_id'] . ") has successfully updated his/her email address";
				$logger->logItInDb($activity, null, array('user_id=' . $user[0]['user_id']));
			// remove key
				deleteFromDb('user_keys', array('name'=>'Reset Email', 'hash'=>$key), null, null, null);
			// redirect
				header('Location: /reset_email/' . $key . '/success');
				exit();
				
		}

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