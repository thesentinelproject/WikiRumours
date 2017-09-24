<?php

	if (@$logged_in) {
		// My Rumours
			echo "            <li class='hideBullets" . ($tl->page['template'] == 'my_rumours' ? " verticalPillActive" : false) . "'><a href='/my_rumours' class='". ($tl->page['template'] == 'my_rumours' ? "pillActive" : "pillInactive") . "'>My Rumours</a></li>\n";
		// My Watchlist
			echo "            <li class='hideBullets" . ($tl->page['template'] == 'watchlist' ? " verticalPillActive" : false) . "'><a href='/watchlist' class='" . ($tl->page['template'] == 'watchlist' ? "pillActive" : "pillInactive") . "'>My Watchlist</a></li>\n";
		// Profile
			echo "            <li class='dropdown hideBullets" . ($tl->page['section'] == 'Profile' || $tl->page['section'] == 'Administration' ? " verticalPillActive" : false) . "'>\n";
			echo "              <a class='dropdown-toggle " . ($tl->page['section'] == 'Profile' || $tl->page['section'] == 'Administration' ? "pillActive" : "pillInactive") . "' data-toggle='dropdown' href='#'>My Profile <b class='caret'></b></a>\n";
			echo "              <ul class='dropdown-menu'>\n";
		// Me
			echo "                <li class='hideBullets" . ($tl->page['template'] == 'profile' ? " pillActive" : " class='pillInactive'") . "'><a href='/profile/" . $logged_in['username'] . "'><span class='glyphicon glyphicon-user transluscent'></span> &nbsp; Me</a></li>\n";
		// Account
			echo "                <li class='hideBullets" . ($tl->page['template'] == 'account' ? " pillActive" : " class='pillInactive'") . "'><a href='/account/" . $logged_in['username'] . "'><span class='glyphicon glyphicon-pencil transluscent'></span> &nbsp; My Account</a></li>\n";
		// Password
			echo "                <li class='hideBullets" . ($tl->page['template'] == 'update_password' ? " pillActive" : false) . "'><a href='/update_password/" . $logged_in['username'] . "'><span class='glyphicon glyphicon-lock transluscent'></span> &nbsp; My Password</a></li>\n";
		// Obtain API key
			echo "                <li class='hideBullets" . ($tl->page['template'] == 'obtain_api_key' ? " pillActive" : false) . "'><a href='/obtain_api_key/" . $logged_in['username'] . "'><span class='glyphicon glyphicon-signal transluscent'></span> &nbsp; My API key</a></li>\n";
			
			if ($logged_in['is_administrator']) {
				// Administration
					echo "                  <li role='presentation' class='divider'></li>\n";
					echo "                  <li role='presentation' class='dropdown-header'>Administration</li>\n";
					// Dashboard
						echo "                <li class='hideBullets" . ($tl->page['template'] == 'admin_dashboard' ? " pillActive" : false) . "'><a href='/admin_dashboard'><span class='glyphicon glyphicon-dashboard transluscent'></span> &nbsp; Dashboard</a></li>\n";
					// Settings
						if ($logged_in['can_update_settings'] || $logged_in['can_edit_settings']) {
							echo "                <li class='hideBullets" . ($tl->page['template'] == 'admin_settings' ? " pillActive" : false) . "'><a href='/admin_settings'><span class='glyphicon glyphicon-cog transluscent'></span> &nbsp; Settings</a></li>\n";
						}
					// Security
						if ($logged_in['can_update_settings'] || $logged_in['can_edit_settings']) {
							echo "                <li class='hideBullets" . ($tl->page['template'] == 'admin_security' ? " pillActive" : false) . "'><a href='/admin_security'><span class='glyphicon glyphicon-tower transluscent'></span> &nbsp; Security</a></li>\n";
						}
					// Users
						if ($logged_in['can_edit_users']) {
							echo "                  <li role='presentation' class='hideBullets" . ($tl->page['template'] == 'admin_users' ? " active" : false) . "'><a href='/admin_users'><span class='glyphicon glyphicon-hand-up transluscent'></span> &nbsp; Users</a></li>\n";
							echo "                  <li role='presentation' class='hideBullets" . ($tl->page['template'] == 'admin_duplicate_users' ? " active" : false) . "'><a href='/admin_duplicate_users'><span class='glyphicon glyphicon-duplicate transluscent'></span> &nbsp; Duplicate Users</a></li>\n";
						}
					// Notifications
						echo "                  <li role='presentation' class='hideBullets" . ($tl->page['template'] == 'admin_notifications' ? " active" : false) . "'><a href='/admin_notifications'><span class='glyphicon glyphicon-envelope transluscent'></span> &nbsp; Notifications</a></li>\n";
					// Backups
						echo "                <li class='hideBullets" . ($tl->page['template'] == 'admin_backups' ? " pillActive" : false) . "'><a href='/admin_backups'><span class='glyphicon glyphicon-floppy-disk transluscent'></span> &nbsp; Backups</a></li>\n";
					// Logs
						echo "                <li class='hideBullets" . ($tl->page['template'] == 'admin_logs' ? " pillActive" : false) . "'><a href='/admin_logs'><span class='glyphicon glyphicon-align-justify transluscent'></span> &nbsp; Logs</a></li>\n";
					// Housekeeping
						if ($logged_in['can_run_housekeeping']) {
							echo "                <li class='hideBullets" . ($tl->page['template'] == 'housekeeping' ? " pillActive" : false) . "'><a href='/housekeeping'><span class='glyphicon glyphicon-time transluscent'></span> &nbsp; Housekeeping</a></li>\n";
						}
				// Content
					if ($logged_in['can_edit_content']) {
						echo "                  <li role='presentation' class='divider'></li>\n";
						echo "                  <li role='presentation' class='dropdown-header'>Content</li>\n";
						// CMS
							echo "                <li class='hideBullets" . ($tl->page['template'] == 'admin_cms' ? " pillActive" : false) . "'><a href='/admin_cms'><span class='glyphicon glyphicon-edit transluscent'></span> &nbsp; CMS</a></li>\n";
						// FAQs
							echo "                  <li role='presentation' class='hideBullets" . ($tl->page['template'] == 'admin_faqs' ? " active" : false) . "'><a href='/admin_faqs'><span class='glyphicon glyphicon-th-list transluscent'></span> &nbsp; FAQs</a></li>\n";
					}
				// Rumour management
					echo "                  <li role='presentation' class='divider'></li>\n";
					echo "                  <li role='presentation' class='dropdown-header'>Rumour management</li>\n";
					// Rumours
						echo "                <li class='hideBullets" . ($tl->page['template'] == 'admin_rumours' ? " pillActive" : false) . "'><a href='/admin_rumours'><span class='glyphicon glyphicon-fire transluscent'></span> &nbsp; Rumours</a></li>\n";

					if ($logged_in['can_edit_settings']) {
						// Priorities
							echo "                <li class='hideBullets" . ($tl->page['template'] == 'admin_priorities'? " pillActive" : false) . "'><a href='/admin_priorities'><span class='glyphicon glyphicon-sort transluscent'></span> &nbsp; Priorities</a></li>\n";
						// Statuses
							echo "                <li class='hideBullets" . ($tl->page['template'] == 'admin_statuses' ? " pillActive" : false) . "'><a href='/admin_statuses'><span class='glyphicon glyphicon-transfer transluscent'></span> &nbsp; Statuses</a></li>\n";
					}
				// Testing
					if ($logged_in['is_tester']) {
						echo "                <li role='presentation' class='divider'></li>\n";
						echo "                <li role='presentation' class='dropdown-header'>Testing</li>\n";
						// Sandbox
							echo "                <li class='hideBullets" . ($tl->page['template'] == 'sandbox' ? " pillActive" : false) . "'><a href='/sandbox'><span class='glyphicon glyphicon-wrench transluscent'></span> &nbsp; Sandbox</a></li>\n";
					}
			}
		// Log out
			echo "                <li class='divider'></li>\n";
			echo "                <li class='hideBullets'><a href='/logout'><span class='glyphicon glyphicon-off transluscent'></span> &nbsp; Log out</a></li>\n";
			echo "              </ul>\n";
			echo "            </li>\n";
	}
	else {
		echo "            <li><a href='/login_register'" . ($tl->page['template'] == 'login_register' ? " class='pillActive'" : false) . ">Log in or register</a></li>\n";
	}

?>