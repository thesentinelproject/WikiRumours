<?php

	if ($logged_in) {
		// My Rumours
			echo "            <li class='hideBullets";
			if ($templateName == 'my_rumours') echo " verticalPillActive";
			echo "'><a href='/my_rumours' class='";
			if ($templateName == 'my_rumours') echo "pillActive";
			else echo "pillInactive";
			echo "'>My Rumours</a></li>\n";
		// My Watchlist
			echo "            <li class='hideBullets";
			if ($templateName == 'watchlist') echo " verticalPillActive";
			echo "'><a href='/watchlist' class='";
			if ($templateName == 'watchlist') echo "pillActive";
			else echo "pillInactive";
			echo "'>My Watchlist</a></li>\n";
		// Profile
			echo "            <li class='dropdown hideBullets";
			if ($sectionTitle == 'Profile' || $sectionTitle == 'Administration') echo " verticalPillActive";
			echo "'>\n";
			echo "              <a class='dropdown-toggle ";
			if ($sectionTitle == 'Profile' || $sectionTitle == 'Administration') echo "pillActive";
			else echo "pillInactive";
			echo "' data-toggle='dropdown' href='#'>My Profile <b class='caret'></b></a>\n";
			echo "              <ul class='dropdown-menu'>\n";
		// Me
			echo "                <li class='hideBullets";
			if ($templateName == 'profile') echo " pillActive";
			else echo " class='pillInactive'";
			echo "'><a href='/profile/" . $logged_in['username'] . "'><span class='glyphicon glyphicon-user transluscent'></span> &nbsp; Me</a></li>\n";
		// Account
			echo "                <li class='hideBullets";
			if ($templateName == 'account') echo " pillActive";
			else echo " class='pillInactive'";
			echo "'><a href='/account/" . $logged_in['username'] . "'><span class='glyphicon glyphicon-pencil transluscent'></span> &nbsp; My Account</a></li>\n";
		// Password
			echo "                <li class='hideBullets";
			if ($templateName == 'update_password') echo " pillActive";
			echo "'><a href='/update_password/" . $logged_in['username'] . "'><span class='glyphicon glyphicon-lock transluscent'></span> &nbsp; My Password</a></li>\n";
		// Obtain API key
			echo "                <li class='hideBullets";
			if ($templateName == 'obtain_api_key') echo " pillActive";
			echo "'><a href='/obtain_api_key/" . $logged_in['username'] . "'><span class='glyphicon glyphicon-signal transluscent'></span> &nbsp; My API key</a></li>\n";
			
			if ($logged_in['is_administrator']) {
				// Administration
					echo "                  <li role='presentation' class='divider'></li>\n";
					echo "                  <li role='presentation' class='dropdown-header'>Administration</li>\n";
					// Dashboard
						echo "                <li class='hideBullets";
						if ($templateName == 'admin_dashboard') echo " pillActive";
						echo "'><a href='/admin_dashboard'><span class='glyphicon glyphicon-dashboard transluscent'></span> &nbsp; Dashboard</a></li>\n";
					// Settings
						if ($logged_in['can_update_settings'] || $logged_in['can_edit_settings']) {
							echo "                <li class='hideBullets";
							if ($templateName == 'admin_settings') echo " pillActive";
							echo "'><a href='/admin_settings'><span class='glyphicon glyphicon-cog transluscent'></span> &nbsp; Settings</a></li>\n";
						}
					// Users
						if ($logged_in['can_edit_users']) {
							echo "                  <li role='presentation' class='hideBullets";
							if ($templateName == 'admin_users') echo " active";
							echo "'><a href='/admin_users'><span class='glyphicon glyphicon-hand-up transluscent'></span> &nbsp; Users</a></li>\n";
						}
					// Email
						echo "                  <li role='presentation' class='hideBullets";
						if ($templateName == 'admin_email') echo " active";
						echo "'><a href='/admin_email'><span class='glyphicon glyphicon-envelope transluscent'></span> &nbsp; Email</a></li>\n";
					// Pseudonyms
						echo "                <li class='hideBullets";
						if ($templateName == 'admin_pseudonyms') echo " pillActive";
						echo "'><a href='/admin_pseudonyms'><span class='glyphicon glyphicon-globe transluscent'></span> &nbsp; Pseudonyms</a></li>\n";
					// Logs
						echo "                <li class='hideBullets";
						if ($templateName == 'admin_logs') echo " pillActive";
						echo "'><a href='/admin_logs'><span class='glyphicon glyphicon-align-justify transluscent'></span> &nbsp; Logs</a></li>\n";
					// Housekeeping
						if ($logged_in['can_run_housekeeping']) {
							echo "                <li class='hideBullets";
							if ($templateName == 'housekeeping') echo " pillActive";
							echo "'><a href='/housekeeping'><span class='glyphicon glyphicon-time transluscent'></span> &nbsp; Housekeeping</a></li>\n";
						}
				// Content
					if ($logged_in['can_edit_content']) {
						echo "                  <li role='presentation' class='divider'></li>\n";
						echo "                  <li role='presentation' class='dropdown-header'>Content</li>\n";
						// CMS
							echo "                <li class='hideBullets";
							if ($templateName == 'admin_content') echo " pillActive";
							echo "'><a href='/admin_content'><span class='glyphicon glyphicon-edit transluscent'></span> &nbsp; CMS</a></li>\n";
						// FAQs
							echo "                  <li role='presentation' class='hideBullets";
							if ($templateName == 'admin_faqs') echo " active";
							echo "'><a href='/admin_faqs'><span class='glyphicon glyphicon-th-list transluscent'></span> &nbsp; FAQs</a></li>\n";
					}
				// Rumour management
					echo "                  <li role='presentation' class='divider'></li>\n";
					echo "                  <li role='presentation' class='dropdown-header'>Rumour management</li>\n";
					// Rumours
						echo "                <li class='hideBullets";
						if ($templateName == 'admin_rumours') echo " pillActive";
						echo "'><a href='/admin_rumours'><span class='glyphicon glyphicon-fire transluscent'></span> &nbsp; Rumours</a></li>\n";

					if ($logged_in['can_edit_settings']) {
						// Priorities
							echo "                <li class='hideBullets";
							if ($templateName == 'admin_priorities') echo " pillActive";
							echo "'><a href='/admin_priorities'><span class='glyphicon glyphicon-sort transluscent'></span> &nbsp; Priorities</a></li>\n";
						// Statuses
							echo "                <li class='hideBullets";
							if ($templateName == 'admin_statuses') echo " pillActive";
							echo "'><a href='/admin_statuses'><span class='glyphicon glyphicon-transfer transluscent'></span> &nbsp; Statuses</a></li>\n";
					}
				// Testing
					if ($logged_in['is_tester']) {
						echo "                <li role='presentation' class='divider'></li>\n";
						echo "                <li role='presentation' class='dropdown-header'>Testing</li>\n";
						// Sandbox
							echo "                <li class='hideBullets";
							if ($templateName == 'sandbox') echo " pillActive";
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
		if ($templateName == 'login_register') echo " class='pillActive'";
		echo ">Log in or register</a></li>\n";
	}

?>