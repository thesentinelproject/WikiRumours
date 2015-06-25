<?php

		session_start();
		parse_str($_SERVER['QUERY_STRING']);

		// Identify template
		
			if (!@$isCron) {

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

			}

		// Initialize application
		
			// Define connection
				if (@$isCron) $connectionType = 'R';
				else $connectionType = 'U';
				
			// Autoload database queries
				if ($handle = opendir(__DIR__ . '/includes/models/autoload/.')) {
					while (false !== ($file = readdir($handle))) {
						if (substr_count($file, '.php') > 0) include __DIR__ . '/includes/models/autoload/' . $file;
					}
					closedir($handle);
				}

			// Load third-party PHP libraries
				$thirdPartyLibraries = array(
					'phpmailer/phpmailer_5-1/class.phpmailer.php',
					'phpass/phpass_0-3/PasswordHash.php',
					'mobile_detect/Mobile-Detect-2.8.12/Mobile_Detect.php',
					'phpinfo_array/index.php',
				);

				foreach ($thirdPartyLibraries as $path) {
					if ($path && file_exists(__DIR__ . '/../libraries_(protected)/' . $path)) include __DIR__ . '/../libraries_(protected)/' . $path;
					else die("Unable to locate " . $path);
				}
				
			// Load Tidal Lock PHP libraries and instantiate classes
				if ($handle = opendir(__DIR__ . '/../libraries_(protected)/tidal_lock/php/.')) {
					while (false !== ($file = readdir($handle))) {
						if (substr_count($file, '.php') > 0) {
							// include file
								include __DIR__ . '/../libraries_(protected)/tidal_lock/php/' . $file;
							// instantiate class
								$instance = str_replace('.php', '', str_replace('class.', '', $file));
								$class = $instance . '_TL';
								${$instance} = new $class();
						}
					}
					closedir($handle);
				}
				
			// Autoload configuration files
				if ($handle = opendir(__DIR__ . '/../config/autoload/.')) {
					while (false !== ($file = readdir($handle))) {
						if (substr_count($file, '.php') > 0) include __DIR__ . '/../config/autoload/' . $file;
					}
					closedir($handle);
				}
				
			// Autoload shared controllers
				if ($handle = opendir(__DIR__ . '/includes/controllers/autoload/.')) {
					while (false !== ($file = readdir($handle))) {
						if (substr_count($file, '.php') > 0) include __DIR__ . '/includes/controllers/autoload/' . $file;
					}
					closedir($handle);
				}

			// Autoload shared views
				if ($handle = opendir(__DIR__ . '/includes/views/autoload/.')) {
					while (false !== ($file = readdir($handle))) {
						if (substr_count($file, '.php') > 0) include __DIR__ . '/includes/views/autoload/' . $file;
					}
					closedir($handle);
				}

			// Load environmentals
				$environmentals = retrieveEnvironmentals();
				
			// Confirm setup
				$db_TL = $databases[$currentDatabase];
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
				
			// Autoload global arrays
				if ($handle = opendir(__DIR__ . '/../global_arrays/.')) {
					while (false !== ($file = readdir($handle))) {
						if (substr_count($file, '.php') > 0) include __DIR__ . '/../global_arrays/' . $file;
					}
					closedir($handle);
				}

			// Populate global arrays from database
				populateCountriesAndRegions();
				populateLanguages();
				populateStatusesTagsAndSources();
						
			// Load system preferences
				$systemPreferences = array();
				$result = retrieveFromDb('preferences', null, array('user_id'=>'0'));
				for ($counter = 0; $counter < count($result); $counter++) {
					$systemPreferences[$result[$counter]['preference']] = $result[$counter]['value'];
				}

			// Determine pseudonym
				$url = $environmentals['absoluteRoot'];
				if (substr($url, 0, 4) == 'www.') $url = substr($url, 4); // ensures that www.mydomain.com and mydomain.com aren't treated as separate pseudonyms

				$result = retrievePseudonyms(array('url'=>$url), null, null, null, 1);
				if (count($result)) $pseudonym = $result[0];

			// Verify that root path of application is in database
				if (!$systemPreferences['Root URL']) {
					$systemPreferences['Root URL'] = $environmentals['protocol'] . $environmentals['absoluteRoot'];
					updateOrInsertIntoDb('preferences', array('value'=>$systemPreferences['Root URL'], 'input_type'=>'text', 'is_mandatory'=>'1'), array('preference'=>'Root URL'), null, null, null, null, 1);
				}
												
			// Mobile and tablet redirection
				if ($systemPreferences['Redirect for mobile'] && !@$isCron && $environmentals['subdomain'] != 'm') {

					$detect = new Mobile_Detect;

					if ($detect->isMobile()) {
						if (file_exists('includes/views/mobile/custom/' . $templateName . '.php') || file_exists('includes/views/mobile/default/' . $templateName . '.php')) $url = $environmentals['protocol'] . 'm.' . $environmentals['domain'] . '/' . $templateName . '/' . trim($parameter1 . '/' . $parameter2 . '/' . $parameter3, '/');
						else $url = $environmentals['protocol'] . 'm.' . $environmentals['domain'];
						
						header('Location: ' . $url);
						exit();
					}

				}

				if ($systemPreferences['Redirect for tablet'] && !@$isCron && $environmentals['subdomain'] != 't') {

					$detect = new Mobile_Detect;

					if ($detect->isTablet()) {
						if (file_exists('includes/views/tablet/custom/' . $templateName . '.php') || file_exists('includes/views/tablet/default/' . $templateName . '.php')) $url = $environmentals['protocol'] . 't.' . $environmentals['domain'] . '/' . $templateName . '/' . trim($parameter1 . '/' . $parameter2 . '/' . $parameter3, '/');
						else $url = $environmentals['protocol'] . 't.' . $environmentals['domain'];
						
						header('Location: ' . $url);
						exit();
					}

				}
		
			// Set timezone
				if (!$systemPreferences['Home timezone']) $systemPreferences['Home timezone'] = 'US/Eastern';
				date_default_timezone_set($systemPreferences['Home timezone']);
				putenv('TZ=' . $systemPreferences['Home timezone']);
				
			// Verify salts have been provided
				if ($salts_TL) foreach ($salts_TL as $name => $salt) {
					if (!$salt) die ("Please configure encrypton options by providing valid salts.");
				}
		
			// Verify cron connection
				if (@$currentDatabase == 'production' && !@$isCron) {
					
					if ($systemPreferences['Enable cron connections'] && $systemPreferences['Interval between cron connections'] > 0) {
						$cronError = null;
						$numberOfMinutesSincePreviousCronConnection = null;
						$previousCronConnection = retrieveSingleFromDb('logs', null, array('connection_type'=>'R'), null, null, null, null, null, 'connected_on DESC');
	
						if (count($previousCronConnection) < 1) $cronError = "Cron does not appear to have attempted a connection yet";
						else {
							$numberOfMinutesSincePreviousCronConnection = round((strtotime(date('Y-m-d H:i:s')) - strtotime($previousCronConnection[0]['connected_on'])) / 60, 0);
							if ($numberOfMinutesSincePreviousCronConnection >= ($systemPreferences['Interval between cron connections'] * 2)) {
								$cronError = "Cron has stopped connecting for some reason (perhaps a server outage?)";
							}
						}
						
						if ($cronError) {
							$logger->logItInDb($cronError, null, null, array('error'=>'1', 'resolved'=>'0'), true);
							emailSystemNotification($cronError, 'Critical error');
						}
					}
					else {
						$runHousekeeping = false;
						$numberOfHoursSincePreviousHousekeeping = null;
						$previousHousekeeping = retrieveSingleFromDb('logs', null, array('activity'=>'Housekeeping manually initiated'), null, null, null, null, null, 'connected_on DESC');
						
						if (count($previousHousekeeping) < 1) $runHousekeeping = true;
						else {
							$numberOfHoursSincePreviousHousekeeping = round((strtotime(date('Y-m-d H:i:s')) - strtotime($previousHousekeeping[0]['connected_on'])) / 60 / 60, 0);
							if ($numberOfHoursSincePreviousHousekeeping >= 24) {
								$runHousekeeping = true;
							}
						}
						
						if ($runHousekeeping) {
							$logger->logItInDb('Housekeeping manually initiated');
							include __DIR__ . '/housekeeping.php';
						}
						
						include __DIR__ . '/housekeeping.php';
					}
					
				}
								
			// Authentication
				$logged_in = checkLogin();
				
				if (@$logged_in['is_moderator']) {
					$otherCriteria = "(is_closed = '0' OR is_closed IS NULL)";
					if (@$pseudonym['pseudonym_id']) $otherCriteria .= " AND pseudonym_id = '" . $pseudonym['pseudonym_id'] . "'";
					$result = retrieveRumours(array('assigned_to'=>''), null, $otherCriteria);
					@$logged_in['rumours_assigned'] += count($result);
					unset($otherCriteria);
				}

			// Clear any garbage in database which wasn't deleted due to inappropriate closure
				
			// Initialize page variables
				$pageError = null;
				$pageWarning = null;
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
				
			// Execute page-specific controllers
				if (!@$isCron && file_exists(__DIR__ . '/includes/controllers/custom/' . $templateName . '.php')) include __DIR__ . '/includes/controllers/custom/' . $templateName . '.php';
				elseif (!@$isCron && file_exists(__DIR__ . '/includes/controllers/default/' . $templateName . '.php')) include __DIR__ . '/includes/controllers/default/' . $templateName . '.php';

			// Execute page-specific models
				if(!@$isCron && file_exists(__DIR__ . '/includes/models/' . $templateName . '.php')) include __DIR__ . '/includes/models/' . $templateName . '.php';
						
			// Confirm database has been imported
				$tables = retrieveTables_TL();
				if (count($tables) < 1) die ("Please import database before proceeding.");

		// Load view
			if (!@$isCron) {
				if (@$environmentals['subdomain'] == 'm') $view = 'mobile';
				elseif (@$environmentals['subdomain'] == 't')  $view = 'tablet';
				elseif (@$environmentals['subdomain'] == 'api') $view = 'api';
				else $view = 'desktop';

				if ($view == 'api' && file_exists(__DIR__ . '/includes/views/api/' . $templateName . ".php")) include __DIR__ . '/includes/views/api/' . $templateName . ".php";
				else {

					if (file_exists(__DIR__ . '/includes/views/' . $view . '/custom/' . $templateName . ".php")) {
						include __DIR__ . '/includes/views/desktop/shared/page_top.php';
						include __DIR__ . '/includes/views/' . $view . '/custom/' . $templateName . ".php";
						include __DIR__ . '/includes/views/desktop/shared/page_bottom.php';
					}
					elseif (file_exists(__DIR__ . '/includes/views/' . $view . '/default/' . $templateName . ".php")) {
						include __DIR__ . '/includes/views/desktop/shared/page_top.php';
						include __DIR__ . '/includes/views/' . $view . '/default/' . $templateName . ".php";
						include __DIR__ . '/includes/views/desktop/shared/page_bottom.php';
					}
					else {
						$cms = retrieveContent(array('slug'=>$templateName, 'cms_type'=>'p', $tablePrefix . 'cms.language_id'=>$operators->firstTrue(@$pseudonym['language_id'], @$systemPreferences['Default language']), $tablePrefix . 'cms.pseudonym_id'=>@$pseudonym['pseudonym_id']));
						if (!count(@$cms)) $cms = retrieveContent(array('slug'=>$templateName, 'cms_type'=>'p', $tablePrefix . 'cms.language_id'=>$operators->firstTrue(@$pseudonym['language_id'], @$systemPreferences['Default language']), $tablePrefix . 'cms.pseudonym_id'=>'0'));
						if (count(@$cms) == 1) {
							if (@$cms[0]['login_required'] && !$logged_in) forceLoginThenRedirectHere();
							else {
								$pageTitle = $cms[0]['title'];
								$pageCss = $cms[0]['content_css'];
								include __DIR__ . '/includes/views/' . $view . '/shared/page_top.php';
								include __DIR__ . '/includes/views/shared/cms_page.php';
								$pageJavaScript = $cms[0]['content_js'];
								include __DIR__ . '/includes/views/' . $view . '/shared/page_bottom.php';
							}
						}
						else {
							include __DIR__ . '/includes/views/' . $view . '/shared/page_top.php';
							include __DIR__ . '/includes/views/' . $view . '/default/404.php';
							include __DIR__ . '/includes/views/' . $view . '/shared/page_bottom.php';
						}
					}

				}

			}

?>
