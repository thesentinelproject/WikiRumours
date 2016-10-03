<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse URL
		if ($tl->page['parameter1'] == 'redirect') $destination = $tl->page['parameter2'];

	// delete cookies
		$cookieExpiryDate = time()-60*60*24 * floatval($tl->settings['Keep users logged in for']);
		if (isset($_COOKIE['username'])) setcookie("username", "", $cookieExpiryDate, '/', '.' . $tl->page['domain'], 0);
		if (isset($_COOKIE['email'])) setcookie("email", "", $cookieExpiryDate, '/', '.' . $tl->page['domain'], 0);
		if (isset($_COOKIE['password_hash'])) setcookie("password_hash", "", $cookieExpiryDate, '/', '.' . $tl->page['domain'], 0);

	// clear sessions
		if (@$_SESSION['username']) unset($_SESSION['username']);
		if (@$_SESSION['email']) unset($_SESSION['email']);
		if (@$_SESSION['password_hash']) unset($_SESSION['password_hash']);
		$_SESSION = array();
		session_destroy();

	// redirect
		if ($tl->page['success'] == 'redirect' && $destination) $authentication_manager->forceRedirect('/' . trim(str_replace('|', '/', urldecode($destination))), '/');
		else $authentication_manager->forceRedirect('/login_register/' . trim($tl->page['parameter1'] . '/' . $tl->page['parameter2'] . '/' . $tl->page['parameter3'] . '/' . $tl->page['parameter4'], '/ '));

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