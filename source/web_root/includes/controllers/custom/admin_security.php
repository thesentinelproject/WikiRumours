<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in['is_administrator']) $authentication_manager->forceLoginThenRedirectHere(true);

	// initialize
		$ip_monitor_widget = new ip_monitor_widget_TL();
		$ip_monitor_widget->initialize();
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