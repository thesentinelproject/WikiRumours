<?php

	function retrieveEnvironmentals() {

		$environmentals = array();
		
		// Client environment
			$environmentals['client'] = trim($_SERVER['HTTP_USER_AGENT'] . " / " . $_SERVER['REMOTE_ADDR'] . " / " . @$_SERVER['REMOTE_HOST'], '/ ');
			
		// URLs
			$environmentals['absoluteRoot'] = trim($_SERVER['SERVER_NAME'], '/');
	
			if (substr_count(trim($_SERVER['SERVER_NAME'], '/'), '.') < 2) { // e.g. mydomain.com
				$environmentals['domain'] = trim($_SERVER['SERVER_NAME'], '/');
			}
			else {
				$environmentals['subdomain'] = substr(trim($_SERVER['SERVER_NAME'], '/'), 0, strpos(trim($_SERVER['SERVER_NAME'], '/'), '.')); // e.g. www
				$environmentals['domain'] = substr(trim($_SERVER['SERVER_NAME'], '/'), strpos(trim($_SERVER['SERVER_NAME'], '/'), '.') + 1); // e.g. mydomain.com
			}
			
			if (@$_SERVER['HTTPS']) $environmentals['protocol'] = 'https://';
			else $environmentals['protocol'] = 'http://';
				/*
				 	This isn't a definitive test, and is dependent on proper server configuration. If it's failing, you should
				 	override this setting manually after this function has executed.
				 */
			
			$environmentals['folder'] = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/")) . "/"; // e.g. /public/application/
			
			$environmentals['page'] = substr($_SERVER['REQUEST_URI'], strrpos($_SERVER['REQUEST_URI'], "/") + 1); // e.g. index.php
			
			$environmentals['queryString'] = $_SERVER['QUERY_STRING'];
			
		// Test cookies
			$cookieExpiryDate = time()+60*60*24;
			setCookie('TestCookie', 'test', $cookieExpiryDate, '/', '.' . $environmentals['domain'], 0);
			if (isset($_COOKIE['TestCookie'])) {
				$environmentals['acceptsCookies'] = true;
				$cookieExpiryDate = time()-60*60*24;
				setcookie('TestCookie', '', $cookieExpiryDate, '/', '.' . $environmentals['domain'], 0);
			}
			else $environmentals['acceptsCookies'] = false;
			
		return $environmentals;
		
	}
	
?>
