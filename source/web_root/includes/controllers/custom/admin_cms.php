<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in['is_administrator'] || !$logged_in['can_update_settings']) $authentication_manager->forceLoginThenRedirectHere(true);

	// initialize
		$cms_widget = new cms_widget_TL();
		$cms_widget->initialize();
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