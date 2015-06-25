<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in) forceLoginThenRedirectHere();

		if (!$logged_in['is_tester']) {
			header ('Location: /404');
			exit();
		}
		
	$pageTitle = "Sandbox";
	$sectionTitle = "Administration";
		
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