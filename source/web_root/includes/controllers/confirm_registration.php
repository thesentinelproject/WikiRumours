<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */
	
	$key = $parameter1;

	$registration = retrieveSingleFromDb('registrations', null, array('registration_key'=>$key));
	if (count($registration) < 1) $pageError = "There's something wrong with the link that brought you here. Please check that the link is complete or rekey it by hand; sometimes mail readers cut a link in two by inserting an inopportune line break. ";
	else $confirmed = approveRegistration($registration[0]['registration_id']);

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