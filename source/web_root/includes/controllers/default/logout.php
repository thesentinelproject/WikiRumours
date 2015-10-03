<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse URL
		$pageStatus = $parameter1;
		if ($pageStatus == 'redirect') $destination = $parameter2;

	// delete cookies
		$cookieExpiryDate = time()-60*60*24 * floatval($systemPreferences['Keep users logged in for']);
		if (isset($_COOKIE['username'])) setcookie("username", "", $cookieExpiryDate, '/', '.' . $environmentals['domain'], 0);
		if (isset($_COOKIE['email'])) setcookie("email", "", $cookieExpiryDate, '/', '.' . $environmentals['domain'], 0);
		if (isset($_COOKIE['password_hash'])) setcookie("password_hash", "", $cookieExpiryDate, '/', '.' . $environmentals['domain'], 0);

	// clear sessions
		if (@$_SESSION['username']) unset($_SESSION['username']);
		if (@$_SESSION['email']) unset($_SESSION['email']);
		if (@$_SESSION['password_hash']) unset($_SESSION['password_hash']);
		$_SESSION = array();
		session_destroy();

	// redirect
		if ($pageStatus == 'redirect' && $destination) {
			header ('Location: /' . trim(str_replace('|', '/', urldecode($destination))), '/');
			exit();
		}
		else {
			header ('Location: /login_register/' . trim($parameter1 . '/' . $parameter2 . '/' . $parameter3 . '/' . $parameter4, '/ '));
			exit();
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