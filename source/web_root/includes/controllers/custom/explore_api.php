<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	if ($logged_in) {
		$apiKey = retrieveSingleFromDb('user_keys', null, array('user_id'=>$logged_in['user_id'], 'name'=>'API'));
	}

	$pageTitle = "Explore API";
		
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