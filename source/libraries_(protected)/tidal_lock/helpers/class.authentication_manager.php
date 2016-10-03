<?php

	class authentication_manager_TL {

		public function checkLogin($key) {

			if (!$key) return false;
			
			if (isset($_COOKIE[$key]) && isset($_COOKIE['password_hash'])) {
				if (!@$_SESSION[$key]) $_SESSION[$key] = @$_COOKIE[$key];
				if (!@$_SESSION['password_hash']) $_SESSION['password_hash'] = @$_COOKIE['password_hash'];
			}

			if (isset($_SESSION[$key]) && isset($_SESSION['password_hash'])) {

				$logged_in = $this->confirmUser($key, $_SESSION[$key], $_SESSION['password_hash']);

				if (!@$logged_in['error']) return $logged_in;
				else {
					unset($_SESSION[$key]);
					unset($_SESSION['password_hash']);
					return false;
				}
			}
			else return false;
			
		}
	
		public function confirmUser($key, $value, $hashedPassword, $unhashedPassword = false) {

			// check for errors
				if (!$key || !$value || (!$hashedPassword && !$unhashedPassword)) return false;

			// check user & password
				$user = retrieveUsers(array($key=>$value, 'enabled'=>'1'), null, null, null, 1);

				if (!count($user)) return array('error'=>"Invalid login. Please try again.");  // user not found
				if ($hashedPassword && $hashedPassword != $user[0]['password_hash']) return array('error'=>"Invalid login. Please try again.");  // password hash is incorrect
				if ($unhashedPassword) {
					$hasher = new PasswordHash(8, false);
					$check = $hasher->CheckPassword($unhashedPassword, $user[0]['password_hash']);
					if (!$check) return array('error'=>"Invalid login. Please try again.");  // password hash is incorrect
				}

			// update last login and return user credentials
				updateDbSingle('users', array('last_login'=>date('Y-m-d H:i:s')), array('user_id'=>$user[0]['user_id']));
				return $user[0];

		}
	
		public function forceLoginThenRedirectHere($alert = false) {
			
			global $tl;
			global $logged_in;

			$detector = new detector_TL();

			if ($alert) {

				// detect IP
					$ip = (@$_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : @$_SERVER['REMOTE_HOST']);

				// IP is known?
					if ($ip) $exists = retrieveSingleFromDb('ips', null, ['ip'=>$ip]);
					if (!count(@$exists)) {
						// analyze IP
							$detector->connection();
						// add to DB
							insertIntoDb('ips', ['ip'=>$ip, 'user_id'=>@$logged_in['user_id'], 'status'=>'s', 'attempts'=>'1', 'country_id'=>@$detector->connection['country'], 'city'=>@$detector->connection['city'], 'updated_on'=>date('Y-m-d H:i:s')]);

					}

			}
			
			$this->forceRedirect('/login_register/redirect/' . urlencode(trim($tl->page['template'] . '|' . $tl->page['parameter1'] . '|' . $tl->page['parameter2'] . '|' . $tl->page['parameter3'] . '|' . $tl->page['parameter4'] . '|', '| ')));
			
		}

		public function forceRedirect($url) {

			global $sessionID;

			// check for errors
				if (!$url) return false;

			// clear session in DB
				if (@$sessionID) deleteFromDbSingle('sessions', ['session_id'=>$sessionID]);

			// redirect
				header ('Location: ' . $url);
				exit;

		}

	}

/*
		Authentication Manager

	::	DESCRIPTION
	
		Handles issues relating to login and permissioning

	::	DEPENDENT ON

	::	VERSION HISTORY

	::	LICENSE
	
		Copyright (C) Timothy Quinn / Tidal Lock / Consolidated Biro
		
		Permission is hereby granted, free of charge, to any person
		obtaining a copy of this software and associated documentation
		files (the "Software"), to deal in the Software without
		restriction, including without limitation the rights to use,
		copy, modify, merge, publish, distribute, sublicense, and/or
		sell copies of the Software, and to permit persons to whom the
		Software is furnished to do so, subject to the following
		conditions:
		
		The above copyright notice and this permission notice shall be
		included in all copies or substantial portions of the Software.
		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
		EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
		OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
		NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
		HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
		WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
		FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
		OTHER DEALINGS IN THE SOFTWARE.
*/
	
?>
