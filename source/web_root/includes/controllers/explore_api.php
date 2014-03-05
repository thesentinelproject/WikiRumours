<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	if ($logged_in) {
		$apiKey = retrieveFromDb('user_keys', array('user_id'=>$logged_in['user_id'], 'name'=>'API'), null, null, null, null, null, 1);
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