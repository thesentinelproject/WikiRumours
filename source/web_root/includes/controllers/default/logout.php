<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// log user out
		$cookieExpiryDate = time()-60*60*24 * floatval($systemPreferences['Keep users logged in for']);
		setcookie("username", "", $cookieExpiryDate, '/', '.' . $environmentals['domain'], 0);
		setcookie("password_hash", "", $cookieExpiryDate, '/', '.' . $environmentals['domain'], 0);
		unset($_SESSION['username']);
		unset($_SESSION['password_hash']);
		$_SESSION = array();
		session_destroy();
		header ('Location: /login_register');
		exit();

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