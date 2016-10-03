<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in['is_administrator'] || !$logged_in['can_update_settings']) $authentication_manager->forceLoginThenRedirectHere(true);

	// initialize
		$notifications_widget = new notifications_widget_TL();
		$notifications_widget->initialize();
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