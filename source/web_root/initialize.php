<?php

		session_start();
		parse_str($_SERVER['QUERY_STRING']);
		if (!isset($pathToWebRoot)) $pathToWebRoot = '';

		// Identify template
		
			// sanitize URL
				if(!isset($templateName)) $templateName = null;
				if(!isset($parameter1)) $parameter1 = null;
				if(!isset($parameter2)) $parameter2 = null;
				if(!isset($parameter3)) $parameter3 = null;
				if(!isset($parameter4)) $parameter4 = null;
				$templateName = preg_replace('/[^A-Za-z0-9-_]/', '', urldecode($templateName));
			
			// if no page is specified in the URL, assume home
				if (!$templateName) $templateName = 'index';
			
			// prohibit hitting certain pages (like this one)
				$prohibitedUrls = array("cms");
				
				if ($prohibitedUrls) foreach ($prohibitedUrls as $key => $value) {
					if ($value == $templateName) $templateName = '404';
				}

		// Initialize application
		
			// Define connection
				if (!isset($connectionType) || !$connectionType) $connectionType = 'U';
				
			// Autoload database queries
				if ($handle = opendir($pathToWebRoot . 'includes/models/.')) {
					while (false !== ($file = readdir($handle))) {
						if (substr_count($file, '.php') > 0) include $pathToWebRoot . 'includes/models/' . $file;
					}
					closedir($handle);
				}
				
			// Load PHP libraries (disable any which aren't needed)
				include '../libraries_(protected)/light_open_ID/light_open_ID_0-6/index.php';
				include '../libraries_(protected)/phpmailer/phpmailer_5-1/class.phpmailer.php';
				include '../libraries_(protected)/simplepie/simplepie_1-3-1/autoloader.php';
				include '../libraries_(protected)/simplepie/simplepie_1-3-1/idn/idna_convert.class.php';
				include '../libraries_(protected)/phpass/phpass_0-3/PasswordHash.php';
				include '../libraries_(protected)/get_id3/getid3_v1-9-6/getid3/getid3.php';
				if ($handle = opendir('../libraries_(protected)/tidal_lock/php/.')) {
					while (false !== ($file = readdir($handle))) {
						if (substr_count($file, '.php') > 0) include '../libraries_(protected)/tidal_lock/php/' . $file;
					}
					closedir($handle);
				}
				
			// Proactively instantiate ubiquitous classes
				$logger = new logger_TL();
				$phpmailerWrapper = new phpmailerWrapper_TL();
				$form = new formBuilder_TL;
				$analytics = new analytics_TL();
				
			// Autoload configuration files
				if ($handle = opendir('../config/.')) {
					while (false !== ($file = readdir($handle))) {
						if (substr_count($file, '.php') > 0) include '../config/' . $file;
					}
					closedir($handle);
				}
				
			// Set timezone
				if (!$timezone) $timezone = 'US/Eastern';
				date_default_timezone_set($timezone);
				putenv('TZ=' . $timezone);
				
			// Autoload shared controllers
				if ($handle = opendir($pathToWebRoot . 'includes/controllers/shared/.')) {
					while (false !== ($file = readdir($handle))) {
						if (substr_count($file, '.php') > 0) include $pathToWebRoot . 'includes/controllers/shared/' . $file;
					}
					closedir($handle);
				}

			// Autoload global arrays
				if ($handle = opendir('../global_arrays/.')) {
					while (false !== ($file = readdir($handle))) {
						if (substr_count($file, '.php') > 0) include '../global_arrays/' . $file;
					}
					closedir($handle);
				}
						
			// Load environmentals
				$environmentals = retrieveEnvironmentals();
				
			// Mobile detection
				if ($redirectForMobile) redirectForMobile('/' . trim($templateName . '/' . $parameter1 . '/' . $parameter2 . '/' . $parameter3, '/'));
		
			// Confirm setup
				if (@$devMode) $db_TL = $devDB_TL;
				else $db_TL = $prodDB_TL;
				if (!$db_TL['Server'] || !$db_TL['Name']) die ("Please configure database settings before proceeding.");
				if (!$mail_TL['Host'] || !$mail_TL['User'] || !$mail_TL['Password'] || !$mail_TL['OutgoingAddress'] || !$mail_TL['IncomingAddress']) die ("Please configure email settings before proceeding.");
				
				foreach ($salts_TL as $key => $value) {
					if (!$value) die ("Please configure encryption settings before proceeding.");
				}
				
			// Open database
				$dbConnection = new mysqli($db_TL['Server'], $db_TL['User'], $db_TL['Password'], $db_TL['Name']);
				if($dbConnection->connect_errno > 0){
		    		die ("Unable to connect to database [" . $dbConnection->connect_error . "]");
				}
				$dbConnection->set_charset('utf8');
				
			// Load system preferences
				$systemPreferences = array();
				$result = retrieveFromDb('preferences', array('user_id'=>''));
				for ($counter = 0; $counter < count($result); $counter++) {
					$systemPreferences[$result[$counter]['preference']] = $result[$counter]['value'];
				}

				$userLogo = 'assets/preferences/logo.png';
				if (file_exists($userLogo)) $logo = $userLogo;
				else {
					$userLogo = null;
					$logo = 'resources/img/logo.png';
				}
				
			// Verify salts have been provided
				if ($salts_TL) foreach ($salts_TL as $name => $salt) {
					if (!$salt) die ("Please configure encrypton options by providing valid salts.");
				}
		
			// Verify cron connection
				if (!@$devMode && !@$isCron) {
					
					if ($cronConnectionIntervalInMinutes > 0) {
						$cronError = null;
						$numberOfMinutesSincePreviousCronConnection = null;
						$previousCronConnection = retrieveFromDb('logs', array('connection_type'=>'R'), null, null, null, null, 'connected_on DESC', 1);
	
						if (count($previousCronConnection) < 1) $cronError = "Cron does not appear to have attempted a connection yet";
						else {
							$numberOfMinutesSincePreviousCronConnection = round((strtotime(date('Y-m-d H:i:s')) - strtotime($previousCronConnection[0]['connected_on'])) / 60, 0);
							if ($numberOfMinutesSincePreviousCronConnection >= ($cronConnectionIntervalInMinutes * 2)) {
								$cronError = "Cron has stopped connecting for some reason (perhaps a server outage?)";
							}
						}
						
						if ($cronError) {
							// has error already been logged?
								$alreadyLogged = retrieveFromDb('logs', array('activity'=>$cronError, 'error'=>'1', 'resolved'=>'0'));
							// if not, log it
								if (count($alreadyLogged) < 1) {
									$logger->logItInDb($cronError, null, array('error'=>'1', 'resolved'=>'0'));
									emailSystemNotification($cronError, 'Critical error');
								}
						}
					}
					else {
						$runHousekeeping = false;
						$numberOfHoursSincePreviousHousekeeping = null;
						$previousHousekeeping = retrieveFromDb('logs', array('activity'=>'Housekeeping manually initiated'), null, null, null, null, 'connected_on DESC', 1);
						
						if (count($previousHousekeeping) < 1) $runHousekeeping = true;
						else {
							$numberOfHoursSincePreviousHousekeeping = round((strtotime(date('Y-m-d H:i:s')) - strtotime($previousHousekeeping[0]['connected_on'])) / 60 / 60, 0);
							if ($numberOfHoursSincePreviousHousekeeping >= 24) {
								$runHousekeeping = true;
							}
						}
						
						if ($runHousekeeping) {
							$logger->logItInDb('Housekeeping manually initiated');
							include 'housekeeping.php';
						}
						
						include "housekeeping.php";
					}
					
				}
								
			// Authentication
				$logged_in = checkLogin();
				
				if (@$logged_in['is_moderator']) {
					$result = countInDb('rumours', 'rumour_id', array('assigned_to'=>''), null, null, null, "status = 'NU' OR status = 'UI'");
					@$logged_in['rumours_assigned'] += $result[0]['count'];
				}


			// Clear any garbage in database which wasn't deleted due to inappropriate closure
				
			// Initialize page variables
				$pageError = null;
				$pageSuccess = null;
				$console = null;
				$pageLoadEvents = null;
				$hideSiteChrome = false;
				$pageCss = null;
				$pageJavaScript = null;
				$canonicalUrl = null;
				$pageTitle = null;
				$sectionTitle = null;
				$noPageTitle = false;
				
			// Execute page-specific form post(s), if required
				if(file_exists($pathToWebRoot . 'includes/controllers/' . $templateName . '.php')) include $pathToWebRoot . 'includes/controllers/' . $templateName . '.php';
						
			// Confirm database has been imported
				$tables = retrieveTables_TL();
				if (count($tables) < 1) die ("Please import database before proceeding.");
		
		// Load view
			if (@$environmentals['subdomain'] == 'api') {
				if (file_exists('includes/views/api/' . $templateName . ".php")) include 'includes/views/api/' . $templateName . ".php";
			}
			else {
				if (file_exists('includes/views/desktop/' . $templateName . ".php")) include 'includes/views/desktop/' . $templateName . ".php";
				elseif (!@$isCron) {
					$cms = retrieveFromDb('cms', array('slug'=>$templateName, 'page_or_block'=>'p'), null, null, null);
					if (count($cms) == 1) include 'includes/views/desktop/cms.php';
					else include 'includes/views/desktop/404.php';
				}
			}

?>
