<?php

		session_start();
		parse_str($_SERVER['QUERY_STRING']);
		$tl = new stdClass();

		// Identify template
			$tl->page = array();
		
			if (!@$isCron && !@$isAjax) {

				// sanitize URL
					$tl->page['template'] = (isset($templateName) ? preg_replace('/[^A-Za-z0-9-_]/', '', urldecode($templateName)) : null);
					$tl->page['parameter1'] = (isset($parameter1) ? urldecode($parameter1) : null);
					$tl->page['parameter2'] = (isset($parameter2) ? urldecode($parameter2) : null);
					$tl->page['parameter3'] = (isset($parameter3) ? urldecode($parameter3) : null);
					$tl->page['parameter4'] = (isset($parameter4) ? urldecode($parameter4) : null);
				
				// substitute page aliases (note that an alias means that an actual page of the same name won't be accessible)
					
				// if no page is specified in the URL, assume home
					if (!$tl->page['template']) $tl->page['template'] = 'index';
				
				// prohibit hitting certain pages
					$prohibitedUrls = array();
					
					if ($prohibitedUrls) foreach ($prohibitedUrls as $key => $value) {
						if ($value == $tl->page['template']) $tl->page['template'] = '404';
					}

			}
			else $tl->page['template'] = null;

			// Initialize page variables
				$tl->page['error'] = null;
				$tl->page['warning'] = null;
				$tl->page['success'] = null;
				$tl->page['console'] = null;

				$tl->page['title'] = ucwords(str_replace('_', ' ', $tl->page['template']));
				$tl->page['section'] = null;
				$tl->page['canonical_url'] = null;
				$tl->page['hide_page_chrome'] = false;

				$pageLoadEvents = null;
				$pageCss = null;
				$pageJavaScript = null;
				
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
				@include __DIR__ . '/../libraries_(protected)/import_these_paths.php';

				if (count(@$thirdPartyLibraries)) {
					foreach ($thirdPartyLibraries as $path) {
						if ($path && file_exists(__DIR__ . '/../libraries_(protected)/' . $path)) include __DIR__ . '/../libraries_(protected)/' . $path;
						else die("Unable to locate " . $path);
					}
				}
				
			// Load Tidal Lock PHP libraries and instantiate classes
				if ($handle = opendir(__DIR__ . '/../libraries_(protected)/tidal_lock/helpers/.')) {
					while (false !== ($file = readdir($handle))) {
						if (substr_count($file, '.php') > 0) {
							// include file
								include __DIR__ . '/../libraries_(protected)/tidal_lock/helpers/' . $file;
							// instantiate class
								$instance = str_replace('.php', '', str_replace('class.', '', $file));
								$class = $instance . '_TL';
								${$instance} = new $class();
						}
					}
					closedir($handle);
				}

				if ($handle = opendir(__DIR__ . '/../libraries_(protected)/tidal_lock/widgets/.')) {
					while (false !== ($file = readdir($handle))) {
						if (substr_count($file, '.php') > 0) include __DIR__ . '/../libraries_(protected)/tidal_lock/widgets/' . $file;
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

			// terminate session for banned IPs
				$banned = retrieveFromDb('banned_ips');
				for ($counter = 0; $counter < count($banned); $counter++) {
					if ($banned[$counter]['ip'] == $operators->firstTrue(@$_SERVER['REMOTE_ADDR'], @$_SERVER['REMOTE_HOST'])) {
						updateDbSingle('banned_ips', ['attempts'=>(floatval($banned[$counter]['attempts']) + 1)], ['banned_id'=>$banned[$counter]['banned_id']]);
						deleteFromDbSingle('sessions', array('session_id'=>$sessionID));
						die();
					}
				}
				unset ($banned);
				
			// CMS redirect
				if (!@$isCron && !@$isAjax) {
					$cms = retrieveRedirect(array('slug'=>$tl->page['template']));
					if (count($cms)) {
						if ($cms[0]['status']) header("HTTP/1.1 " . $cms[0]['code_id'] . " " . $cms[0]['status']);
						$authentication_manager->forceRedirect($cms[0]['redirect_to']);
					}
				}

			// Load environmentals
				$detector->page();
				$tl->page = array_merge($tl->page, $detector->page);
				
			// Autoparse any available filters
				for ($counter = 1; $counter <= 4; $counter++) {
					if (!@$tl->page['filters'] && substr_count(@$tl->page['parameter' . $counter], '=')) $tl->page['filters'] = $keyvalue_array->keyValueToArray($tl->page['parameter' . $counter], '|');
				}

				$messageTypes = array('e'=>'error', 'w'=>'warning', 's'=>'success');
				foreach ($messageTypes as $id => $type) {
					if (@$tl->page['filters'][$type]) {
						$result = retrieveSingleFromDb('cms', null, array('slug'=>@$tl->page['filters'][$type], 'content_type'=>'m', 'message_type'=>$id));
						if (count($result)) $tl->page[$type] = $result[0]['content'];
						else $tl->page[$type] = ucfirst(strtolower(str_replace('_', ' ', $tl->page['filters'][$type])));
					}
				}
				unset ($id);
				unset ($type);
				
			// Load system preferences
				$systemPreferences = array();
				$result = retrieveFromDb('preferences', null, array('user_id'=>'0'));
				for ($counter = 0; $counter < count($result); $counter++) {
					$systemPreferences[$result[$counter]['preference']] = $result[$counter]['value'];
				}

			// Autoload global arrays
				if ($handle = opendir(__DIR__ . '/../global_arrays/.')) {
					while (false !== ($file = readdir($handle))) {
						if (substr_count($file, '.php') > 0) include __DIR__ . '/../global_arrays/' . $file;
					}
					closedir($handle);
				}

			// Determine pseudonym
				$url = $tl->page['root'];
				if (substr($url, 0, 4) == 'www.') $url = substr($url, 4); // ensures that www.mydomain.com and mydomain.com aren't treated as separate pseudonyms

				$result = retrievePseudonyms(null, null, "url = '" . $url . "' OR url = '" . $tl->page['root'] . "'", null, 1);
				if (count($result)) $pseudonym = $result[0];

			// Verify that root path of application is in database
				if (!$systemPreferences['Root URL']) {
					$systemPreferences['Root URL'] = $tl->page['protocol'] . $tl->page['root'];
					updateOrInsertIntoDb('preferences', array('value'=>$systemPreferences['Root URL'], 'input_type'=>'text', 'is_mandatory'=>'1'), array('preference'=>'Root URL'), null, null, null, null, 1);
				}
												
			// Mobile and tablet redirection
				if ($systemPreferences['Redirect for mobile'] && !@$isCron && $tl->page['subdomain'] != 'm') {

					$detect = new Mobile_Detect;

					if ($detect->isMobile()) {
						if (file_exists('includes/views/mobile/custom/' . $tl->page['template'] . '.php') || file_exists('includes/views/mobile/default/' . $tl->page['template'] . '.php')) $url = $tl->page['protocol'] . 'm.' . $tl->page['domain'] . '/' . $tl->page['template'] . '/' . trim($tl->page['parameter1'] . '/' . $tl->page['parameter2'] . '/' . $tl->page['parameter3'] . '/' . $tl->page['parameter4'], '/');
						else $url = $tl->page['protocol'] . 'm.' . $tl->page['domain'];
						
						$authentication_manager->forceRedirect($url);
					}

				}

				if ($systemPreferences['Redirect for tablet'] && !@$isCron && $tl->page['subdomain'] != 't') {

					$detect = new Mobile_Detect;

					if ($detect->isTablet()) {
						if (file_exists('includes/views/tablet/custom/' . $tl->page['template'] . '.php') || file_exists('includes/views/tablet/default/' . $tl->page['template'] . '.php')) $url = $tl->page['protocol'] . 't.' . $tl->page['domain'] . '/' . $tl->page['template'] . '/' . trim($tl->page['parameter1'] . '/' . $tl->page['parameter2'] . '/' . $tl->page['parameter3'] . '/' . $tl->page['parameter4'], '/');
						else $url = $tl->page['protocol'] . 't.' . $tl->page['domain'];
						
						$authentication_manager->forceRedirect($url);
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
				if (@$currentDatabase == 'production') {

					if (@$isCron) {

						$previousUnresolvedAlert = retrieveSingleFromDb('logs', null, array('activity'=>"Cron has stopped connecting for some reason (perhaps a server outage?)", 'is_error'=>'1', 'is_resolved'=>'0'), null, null, null, null, null, 'connected_on DESC');
						if (count($previousUnresolvedAlert)) {
							$previousCronConnection = retrieveSingleFromDb('logs', null, array('connection_type'=>'R'), null, null, null, null, null, 'connected_on DESC');
							if (!count($previousCronConnection) || $previousCronConnection[0]['connected_on'] < $previousUnresolvedAlert[0]['connected_on']) {
								emailSystemNotification("Cron is now successfully connecting. The error has been left unresolved for diagnostic purposes.", 'Cron resumed');
							}
						}

					}
					else {
					
						if ($systemPreferences['Enable cron connections'] && $systemPreferences['Interval between cron connections'] > 0) {
							$cronError = null;
							$numberOfMinutesSincePreviousCronConnection = null;
							$previousCronConnection = retrieveSingleFromDb('logs', null, array('connection_type'=>'R'), null, null, null, null, null, 'connected_on DESC');
		
							if (count($previousCronConnection) < 1) $cronError = "Cron does not appear to have attempted a connection yet";
							else {
								$numberOfMinutesSincePreviousCronConnection = round((time() - strtotime($previousCronConnection[0]['connected_on'])) / 60, 0);
								if ($numberOfMinutesSincePreviousCronConnection >= ($systemPreferences['Interval between cron connections'] * 2)) {
									$cronError = "Cron has stopped connecting for some reason (perhaps a server outage?)";
								}
							}
							
							if ($cronError) {
								$logger->logItInDb($cronError, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);
								emailSystemNotification($cronError, 'Critical error');
							}
						}
						else {
							$runHousekeeping = false;
							$numberOfHoursSincePreviousHousekeeping = null;
							$previousHousekeeping = retrieveSingleFromDb('logs', null, array('activity'=>'Housekeeping manually initiated'), null, null, null, null, null, 'connected_on DESC');
							
							if (count($previousHousekeeping) < 1) $runHousekeeping = true;
							else {
								$numberOfHoursSincePreviousHousekeeping = round((time() - strtotime($previousHousekeeping[0]['connected_on'])) / 60 / 60, 0);
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

				}
								
			// Authentication
				$logged_in = $authentication_manager->checkLogin('username');
				
				if (@$logged_in['is_moderator']) {
					$otherCriteria = "(is_closed = '0' OR is_closed IS NULL)";
					if (@$pseudonym['pseudonym_id']) $otherCriteria .= " AND pseudonym_id = '" . $pseudonym['pseudonym_id'] . "'";
					$result = retrieveRumours(array('assigned_to'=>'', $tablePrefix . 'rumours.enabled'=>'1'), null, $otherCriteria);
					@$logged_in['rumours_assigned'] += count($result);
					unset($otherCriteria);
				}

			// Log session in DB
				$sessionID = rand(1,999999999);
				insertIntoDb('sessions', array('session_id'=>$sessionID, 'connected_on'=>date('Y-m-d H:i:s'), 'template'=>$_SERVER['REQUEST_URI'], 'user_id'=>@$logged_in['user_id'], 'user_agent'=>$_SERVER['HTTP_USER_AGENT'], 'ip'=>(@$_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : @$_SERVER['REMOTE_HOST'])));

			// Clear any garbage in database which wasn't deleted due to inappropriate closure
				
			// Execute page-specific controllers
				if (!@$isCron && file_exists(__DIR__ . '/includes/controllers/custom/' . $tl->page['template'] . '.php')) include __DIR__ . '/includes/controllers/custom/' . $tl->page['template'] . '.php';
				elseif (!@$isCron && file_exists(__DIR__ . '/includes/controllers/default/' . $tl->page['template'] . '.php')) include __DIR__ . '/includes/controllers/default/' . $tl->page['template'] . '.php';

			// Execute page-specific models
				if(!@$isCron && file_exists(__DIR__ . '/includes/models/' . $tl->page['template'] . '.php')) include __DIR__ . '/includes/models/' . $tl->page['template'] . '.php';
						
			// Confirm database has been imported
				$tables = retrieveTables_TL();
				if (count($tables) < 1) die ("Please import database before proceeding.");

		// Load view
			if (!@$isCron && !@$isAjax) {
				if (@$tl->page['subdomain'] == 'm') $view = 'mobile';
				elseif (@$tl->page['subdomain'] == 't')  $view = 'tablet';
				elseif (@$tl->page['subdomain'] == 'api') $view = 'api';
				else $view = 'desktop';

				if ($tl->page['template'] != 'maintenance' && @$systemPreferences['Maintenance Mode'] == 'On' && !@$logged_in['is_administrator']) $authentication_manager->forceRedirect('/maintenance');

				if ($view == 'api') {
					if (file_exists(__DIR__ . '/includes/views/api/' . $tl->page['template'] . ".php")) include __DIR__ . '/includes/views/api/' . $tl->page['template'] . ".php";
				}
				else {

					if (file_exists(__DIR__ . '/includes/views/' . $view . '/custom/' . $tl->page['template'] . ".php")) {
						include __DIR__ . '/includes/views/desktop/shared/page_top.php';
						include __DIR__ . '/includes/views/' . $view . '/custom/' . $tl->page['template'] . ".php";
						include __DIR__ . '/includes/views/desktop/shared/page_bottom.php';
					}
					elseif (file_exists(__DIR__ . '/includes/views/' . $view . '/default/' . $tl->page['template'] . ".php")) {
						include __DIR__ . '/includes/views/desktop/shared/page_top.php';
						include __DIR__ . '/includes/views/' . $view . '/default/' . $tl->page['template'] . ".php";
						include __DIR__ . '/includes/views/desktop/shared/page_bottom.php';
					}
					else {
						$cms = retrieveContent(array('slug'=>$tl->page['template'], 'content_type'=>'p', $tablePrefix . 'cms.language_id'=>$operators->firstTrue(@$pseudonym['language_id'], @$systemPreferences['Default language']), $tablePrefix . 'cms.pseudonym_id'=>@$pseudonym['pseudonym_id']));
						if (!count(@$cms)) $cms = retrieveContent(array('slug'=>$tl->page['template'], 'content_type'=>'p', $tablePrefix . 'cms.language_id'=>$operators->firstTrue(@$pseudonym['language_id'], @$systemPreferences['Default language']), $tablePrefix . 'cms.pseudonym_id'=>'0'));
						if (count(@$cms) == 1) {
							if (@$cms[0]['login_required'] && !$logged_in) $authentication_manager->forceLoginThenRedirectHere();
							else {
								$tl->page['title'] = $cms[0]['title'];
								$pageCss = $cms[0]['content_css'];
								include __DIR__ . '/includes/views/' . $view . '/shared/page_top.php';
								include __DIR__ . '/includes/views/shared/cms_page.php';
								$pageJavaScript = $cms[0]['content_js'];
								include __DIR__ . '/includes/views/' . $view . '/shared/page_bottom.php';
							}
						}
						else $authentication_manager->forceRedirect('/404');
					}

				}

			}

		// clear session in DB
			deleteFromDbSingle('sessions', array('session_id'=>$sessionID));

?>
