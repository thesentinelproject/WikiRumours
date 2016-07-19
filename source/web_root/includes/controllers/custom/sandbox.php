<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in) $authentication_manager->forceLoginThenRedirectHere();
		if (!$logged_in['is_tester']) $authentication_manager->forceRedirect('/404');
		
	$tl->page['title'] = "Sandbox";
	$tl->page['section'] = "Administration";
		
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