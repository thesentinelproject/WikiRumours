<?php

	// specify XHTML compliance and begin document header
		echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>\n";
		echo "<html lang='en' xmlns='http://www.w3.org/1999/xhtml'>\n";

		echo "<head>\n";

	// define character set
		echo "  <meta charset='UTF-8'>\n";
		echo "  <meta http-equiv='content-type' content='text/html; charset=UTF-8' />\n";
		
	// set IE compatibility
		echo "  <meta http-equiv='X-UA-Compatible' content='IE=9; IE=8;' />\n";

	// define viewport
		echo "  <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
		
	// define description for search engine indexing
		echo "  <meta name='description' content=" . '"' . $systemPreferences['appDescription'] . '"' . ">\n";
		
	// load Bootstrap stylesheet
		echo "  <!-- Bootstrap --><link href='/libraries/bootstrap/3-0-0/css/bootstrap.min.css' rel='stylesheet' media='screen' type='text/css' />\n";

	// load Bootstrap Switch stylesheet
		echo "  <!-- Bootstrap Switch --><link href='/libraries/bootstrap-switch-master/static/stylesheets/bootstrap-switch.css' rel='stylesheet' />\n";
		
	// load Select2 stylesheet
		echo "  <!-- Select2 --><link href='/libraries/select2/select2-release-3.2/select2.css' rel='stylesheet' />\n";

	// load TidalLock stylesheets
		if ($handle = opendir('libraries/tidal_lock/css/.')) {
			while (false !== ($file = readdir($handle))) {
				if (substr_count($file, ".css") > 0) echo "  <!-- Tidal Lock --><link href='/libraries/tidal_lock/css/" . $file . "' rel='stylesheet' media='screen' type='text/css' />\n";
			}
			closedir($handle);
		}

	// load Google stylesheets
		for ($counter = 0; $counter < count($fonts_TL); $counter++) {
			$url = "http://fonts.googleapis.com/css?family=" . $fonts_TL[$counter];
			if (fileManager_TL::doesUrlExist($url)) echo "  <!-- Google Font --><link rel='stylesheet' type='text/css' href='" . $url . "'>\n";
		}

	// load base stylesheets
		echo "  <link href='/resources/css/desktop.css' rel='stylesheet' media='screen' type='text/css' />\n";
		echo "  <link href='/resources/css/print.css' rel='stylesheet' media='print' type='text/css' />\n";
		
	// load page-specific CSS
		if ($pageCss) {
			echo "  <style type='text/css'>\n";
			echo $pageCss . "\n";
			echo "  </style>\n";
		}

	// specify favicon
		if (file_exists('resources/img/icons/favicon.ico')) echo "  <link href='/resources/img/icons/favicon.ico' rel='SHORTCUT ICON' />\n";
		
	// specify canonical, if provided
		if ($canonicalUrl) echo "  <link rel='canonical' href='" . $canonicalUrl . "' />\n\n";

	// load Google Analytics
		if ($googleAnalyticsID && !$devMode) {
			echo $analytics->insertGoogleAnalytics($googleAnalyticsID, $googleAnalyticsDomain, $googleAnalyticsAccommodateMultipleTopLevelDomains); 
		}
		
	// load MixPanel
		if ($mixPanelToken) echo "  " . $analytics->insertMixPanel($mixPanelToken);
		
	// specify page title
		echo "  <title>";
		if (!$pageTitle && !$noPageTitle) $pageTitle = ucwords(str_replace('_', ' ', $templateName));
		if ($pageTitle) echo $pageTitle . " - ";
		if ($sectionTitle) echo $sectionTitle . " - ";
		echo $systemPreferences['appName'];
		echo "</title>\n";

		echo "</head>\n\n";

	// start body
		echo "<body onLoad='";
		if ($pageLoadEvents) echo $pageLoadEvents;
		echo "'>\n\n";

	if (!$hideSiteChrome) {
		
		// start header content
			echo "  <div id='pageContainer'>\n\n";
			
		// console
			if ($devMode) {
				echo "    <div id='console'>\n";
				if ($console) echo "      " . nl2br(str_replace(' ', '&nbsp;', $console)) . "\n";
				if (errorManager_TL::$errorArray) {
					echo "      <pre>\n";
					print_r(errorManager_TL::$errorArray);
					echo "      </pre>\n";
				}
				echo "    </div><!-- console -->\n";
			}

		echo "    <div class='container'>\n";
		echo "      <div id='header' class='row'>\n";
		
		// logo
			echo "        <div id='logo' class='col-xs-12 col-sm-3 col-md-3'>\n";
			echo "          <h1 class='hidden'>" . $systemPreferences['appName'] . "</h1>\n";
			echo "          <a href='/'><img src='/" . $logo . "' border='0' alt='" . $systemPreferences['appName'] . "' /></a>\n";
			echo "        </div><!-- logo -->\n\n";
	
		// header
			echo "        <div id='userNav' class='col-xs-12 col-sm-9 col-md-9'>\n";
			
			// non-mobile experience
				echo "          <ul class='nav nav-pills pull-right hidden-xs'>\n";
				if ($logged_in) {
					// My Rumours
						echo "            <li><a href='/my_rumours'";
						if ($templateName == 'my_rumours') echo " class='pillFix'";
						echo ">My Rumours</a></li>\n";
					// My Profile
						echo "            <li><a href='/profile'";
						if ($templateName == 'profile' && (!$parameter1 || $parameter1 == $logged_in['username'])) echo " class='pillFix'";
						echo ">My Profile</a></li>\n";
					// My Watchlist
						echo "            <li><a href='/watchlist'";
						if ($templateName == 'watchlist') echo " class='pillFix'";
						echo ">My Watchlist</a></li>\n";
					// Admin
						echo "            <li class='dropdown'>\n";
						echo "              <a class='dropdown-toggle' data-toggle='dropdown' href='#'>Admin <b class='caret'></b></a>\n";
						echo "              <ul class='dropdown-menu pull-right'>\n";
					// Account
						echo "                <li class='hideBullets";
						if ($templateName == 'account') echo " pillFix";
						echo "'><a href='/account/" . $logged_in['username'] . "'><span class='glyphicon glyphicon-user transluscent'></span> &nbsp; Account</a></li>\n";
					// Password
						echo "                <li class='hideBullets";
						if ($templateName == 'update_password') echo " pillFix";
						echo "'><a href='/update_password/" . $logged_in['username'] . "'><span class='glyphicon glyphicon-lock transluscent'></span> &nbsp; Password</a></li>\n";
					// Obtain API key
						echo "                <li class='hideBullets";
						if ($templateName == 'obtain_api_key') echo " pillFix";
						echo "'><a href='/obtain_api_key/" . $logged_in['username'] . "'><span class='glyphicon glyphicon-signal transluscent'></span> &nbsp; Obtain API key</a></li>\n";
						
						if ($logged_in['is_administrator']) {
							echo "                <li class='divider'></li>\n";
							// Dashboard
								echo "                <li class='hideBullets";
								if ($templateName == 'admin_dashboard') echo " pillFix";
								echo "'><a href='/admin_dashboard'><span class='glyphicon glyphicon-dashboard transluscent'></span> &nbsp; Dashboard</a></li>\n";
							// Settings
								if ($logged_in['can_edit_settings']) echo "                <li class='hideBullets";
								if ($templateName == 'settings') echo " pillFix";
								echo "'><a href='/settings'><span class='glyphicon glyphicon-cog transluscent'></span> &nbsp; Settings</a></li>\n";
							// Logs
								echo "                <li class='hideBullets";
								if ($templateName == 'logs') echo " pillFix";
								echo "'><a href='/logs'><span class='glyphicon glyphicon-align-justify transluscent'></span> &nbsp; Logs</a></li>\n";
							// Content Management
								if ($logged_in['can_edit_content']) echo "                <li class='hideBullets";
								if ($templateName == 'content') echo " pillFix";
								echo "'><a href='/content'><span class='glyphicon glyphicon-edit transluscent'></span> &nbsp; CMS</a></li>\n";
							// Housekeeping
								if ($logged_in['can_run_housekeeping']) echo "                <li class='hideBullets";
								if ($templateName == 'housekeeping') echo " pillFix";
								echo "'><a href='/housekeeping'><span class='glyphicon glyphicon-time transluscent'></span> &nbsp; Housekeeping</a></li>\n";
							// Sandbox
								if ($logged_in['is_tester']) {
									echo "                <li class='divider'></li>\n";
									echo "                <li class='hideBullets";
									if ($templateName == 'sandbox') echo " pillFix";
									echo "'><a href='/sandbox'><span class='glyphicon glyphicon-wrench transluscent'></span> &nbsp; Sandbox</a></li>\n";
								}
						}
					// Log out
						echo "                <li class='divider'></li>\n";
						echo "                <li class='hideBullets'><a href='/logout'><span class='glyphicon glyphicon-off transluscent'></span> &nbsp; Log out</a></li>\n";
						echo "              </ul>\n";
						echo "            </li>\n";
				}
				else {
					echo "            <li><a href='/login_register'";
					if ($templateName == 'login_register') echo " class='pillFix'";
					echo ">Log in or register</a></li>\n";
				}
		
				echo "          </ul>\n";
				
			// mobile experience
				echo "          <div class='visible-xs'>\n";
				if ($logged_in) {
					// My Rumours
						if ($templateName == 'my_rumours') echo "            " . $form->input('button', null, null, false, 'My Rumours', 'btn btn-block btn-info', null, null, null, null, array('onClick'=>'document.location.href="/my_rumours"')) . "\n";
						else echo "            " . $form->input('button', null, null, false, 'My Rumours', 'btn btn-block btn-default', null, null, null, null, array('onClick'=>'document.location.href="/my_rumours"')) . "\n";
					// My Profile
						if ($templateName == 'profile' && (!$parameter1 || $parameter1 == $logged_in['username'])) echo "            " . $form->input('button', null, null, false, 'My Profile', 'btn btn-block btn-info', null, null, null, null, array('onClick'=>'document.location.href="/profile/' . $logged_in['username'] . '"')) . "\n";
						else echo "            " . $form->input('button', null, null, false, 'My Profile', 'btn btn-block btn-default', null, null, null, null, array('onClick'=>'document.location.href="/profile/' . $logged_in['username'] . '"')) . "\n";
					// My Watchlist
						if ($templateName == 'watchlist') echo "            " . $form->input('button', null, null, false, 'My Watchlist', 'btn btn-block btn-info', null, null, null, null, array('onClick'=>'document.location.href="/watchlist"')) . "\n";
						else echo "            " . $form->input('button', null, null, false, 'My Watchlist', 'btn btn-block btn-default', null, null, null, null, array('onClick'=>'document.location.href="/watchlist"')) . "\n";
					// Admin
						echo "            <div class='btn-group btn-block'>\n";
						echo "              <button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' style='width: 100%;'>Admin <span class='caret'></span></button>\n";
						echo "              <ul class='dropdown-menu' role='menu'>\n";
					// Account
						echo "                <li class='hideBullets";
						if ($templateName == 'account') echo " pillFix";
						echo "'><a href='/account/" . $logged_in['username'] . "'><span class='glyphicon glyphicon-user transluscent'></span> &nbsp; Account</a></li>\n";
					// Password
						echo "                <li class='hideBullets";
						if ($templateName == 'update_password') echo " pillFix";
						echo "'><a href='/update_password/" . $logged_in['username'] . "'><span class='glyphicon glyphicon-lock transluscent'></span> &nbsp; Password</a></li>\n";
					// Obtain API key
						echo "                <li class='hideBullets";
						if ($templateName == 'obtain_api_key') echo " pillFix";
						echo "'><a href='/obtain_api_key/" . $logged_in['username'] . "'><span class='glyphicon glyphicon-signal transluscent'></span> &nbsp; Obtain API key</a></li>\n";
						
						if ($logged_in['is_administrator']) {
							echo "                <li class='divider'></li>\n";
							// Dashboard
								echo "                <li class='hideBullets";
								if ($templateName == 'admin_dashboard') echo " pillFix";
								echo "'><a href='/admin_dashboard'><span class='glyphicon glyphicon-dashboard transluscent'></span> &nbsp; Dashboard</a></li>\n";
							// Settings
								if ($logged_in['can_edit_settings']) echo "                <li class='hideBullets";
								if ($templateName == 'settings') echo " pillFix";
								echo "'><a href='/settings'><span class='glyphicon glyphicon-cog transluscent'></span> &nbsp; Settings</a></li>\n";
							// Logs
								echo "                <li class='hideBullets";
								if ($templateName == 'logs') echo " pillFix";
								echo "'><a href='/logs'><span class='glyphicon glyphicon-align-justify transluscent'></span> &nbsp; Logs</a></li>\n";
							// Content Management
								if ($logged_in['can_edit_content']) echo "                <li class='hideBullets";
								if ($templateName == 'content') echo " pillFix";
								echo "'><a href='/content'><span class='glyphicon glyphicon-edit transluscent'></span> &nbsp; CMS</a></li>\n";
							// Housekeeping
								if ($logged_in['can_run_housekeeping']) echo "                <li class='hideBullets";
								if ($templateName == 'housekeeping') echo " pillFix";
								echo "'><a href='/housekeeping'><span class='glyphicon glyphicon-time transluscent'></span> &nbsp; Housekeeping</a></li>\n";
							// Sandbox
								if ($logged_in['is_tester']) {
									echo "                <li class='divider'></li>\n";
									echo "                <li class='hideBullets";
									if ($templateName == 'sandbox') echo " pillFix";
									echo "'><a href='/sandbox'><span class='glyphicon glyphicon-wrench transluscent'></span> &nbsp; Sandbox</a></li>\n";
								}
						}
					// Log out
						echo "                <li class='divider'></li>\n";
						echo "                <li class='hideBullets'><a href='/logout'><span class='glyphicon glyphicon-off transluscent'></span> &nbsp; Log out</a></li>\n";
						echo "              </ul>\n";
						echo "            </div>\n";
				}
				else {
					if ($templateName == 'login_register') echo "            " . $form->input('button', null, null, false, 'Login or Register', 'btn btn-block btn-info', null, null, null, null, array('onClick'=>'document.location.href="/login_register"')) . "\n";
					else echo "            " . $form->input('button', null, null, false, 'Login or Register', 'btn btn-block btn-default', null, null, null, null, array('onClick'=>'document.location.href="/login_register"')) . "\n";
				}
				echo "            <hr />\n";
				echo "          </div>\n";
				
		echo "        </div><!-- userNav -->\n";
		echo "      </div><!-- header -->\n";
		echo "    </div>\n";
		
		echo "    <div class='container'>\n";
		
		// begin page content
			echo "      <div id='pageContent' class='row'>\n";
			echo "        <div class='col-xs-12 col-sm-9 col-sm-push-3 col-md-9 col-md-push-3'>\n";
			
	}
?>

	<!-- PAGE CONTENT BEGINS -->
