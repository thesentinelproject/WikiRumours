<?php

	function checkLogin($usernameOrEmail) {

		if ($usernameOrEmail != 'username' && $usernameOrEmail != 'email') return false;
		
		if (isset($_COOKIE[$usernameOrEmail]) && isset($_COOKIE['password_hash'])) {
			if (!@$_SESSION[$usernameOrEmail]) $_SESSION[$usernameOrEmail] = @$_COOKIE[$usernameOrEmail];
			if (!@$_SESSION['password_hash']) $_SESSION['password_hash'] = @$_COOKIE['password_hash'];
		}

		if (isset($_SESSION[$usernameOrEmail]) && isset($_SESSION['password_hash'])) {

			if ($usernameOrEmail == 'username') $logged_in = confirmUser($_SESSION['username'], null, $_SESSION['password_hash'], null);
			else $logged_in = confirmUser(null, $_SESSION['email'], $_SESSION['password_hash'], null);

			if (!@$logged_in['error']) return $logged_in;
			else {
				unset($_SESSION[$usernameOrEmail]);
				unset($_SESSION['password_hash']);
				return false;
			}
		}
		else return false;
		
	}
	
	function confirmUser($username, $email, $hashedPassword, $unhashedPassword) {

		// check for errors
			if ((!$username && !$email) || (!$hashedPassword && !$unhashedPassword)) return false;

		// check username and/or email
			if ($username) $user = retrieveUsers(array('username'=>$username, 'enabled'=>'1'), null, null, null, 1);
			elseif ($email) $user = retrieveUsers(array('email'=>$email, 'enabled'=>'1'), null, null, null, 1);

			if (count($user) < 1) return array('error'=>"Invalid login. Please try again.");  // username not found
			
		// check password
			if ($hashedPassword && $hashedPassword != $user[0]['password_hash']) return array('error'=>"Invalid login. Please try again.");  // password hash is incorrect
			
			if ($unhashedPassword) {
				$hasher = new PasswordHash(8, false);
				$check = $hasher->CheckPassword($unhashedPassword, $user[0]['password_hash']);
				if (!$check) return array('error'=>"Invalid login. Please try again.");  // password hash is incorrect
			}
			
		// update last login and return user credentials
			if ($user) {
				updateDb('users', array('last_login'=>date('Y-m-d H:i:s')), array('user_id'=>$user[0]['user_id']), null, null, null, null, 1);
				return $user[0];
			}

	}
	
	function forceLoginThenRedirectHere() {
		
		global $templateName;
		global $parameter1;
		global $parameter2;
		global $parameter3;
		global $parameter4;
		
		header ('Location: /login_register/redirect/' . urlencode(trim($templateName . '|' . $parameter1 . '|' . $parameter2 . '|' . $parameter3 . '|' . $parameter4 . '|', '|')));
		exit;
		
	}
	
?>