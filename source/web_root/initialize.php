<?php

		session_start();
		parse_str($_SERVER['QUERY_STRING']);

		$tl = new stdClass();

		$tl->page = array();
		$tl->settings = array();
		$tl->db = array();
		$tl->mail = array();
		$tl->initialize = array();

		// autoload configuration files
			if ($tl->initialize['handle'] = opendir(__DIR__ . '/../config/autoload/.')) {
				while (false !== ($tl->initialize['file'] = readdir($tl->initialize['handle']))) {
					if (substr_count($tl->initialize['file'], '.php') > 0) include __DIR__ . '/../config/autoload/' . $tl->initialize['file'];
				}
				closedir($tl->initialize['handle']);
			}
			
		// identify if cron
			if (substr_count($_SERVER['SCRIPT_FILENAME'], '/cron/cron.php') > 0) $tl->page['is_cron'] = true;
			else $tl->page['is_cron'] = false;

		// identify if AJAX
			if (substr_count($_SERVER['SCRIPT_FILENAME'], '/controllers/ajax/') > 0) $tl->page['is_ajax'] = true;
			else $tl->page['is_ajax'] = false;

		// hide site chrome, if required
			if ($tl->page['is_cron'] || $tl->page['is_ajax']) $tl->page['hide_page_chrome'] = true;
			else $tl->page['hide_page_chrome'] = false;

		// Identify template
			if (!@$tl->page['is_cron'] && !@$tl->page['is_ajax']) {

				// sanitize URL
					$tl->page['template'] = (isset($templateName) ? preg_replace('/[^A-Za-z0-9-_]/', '', urldecode($templateName)) : null);
					$tl->page['parameter1'] = (isset($parameter1) ? urldecode($parameter1) : null);
					$tl->page['parameter2'] = (isset($parameter2) ? urldecode($parameter2) : null);
					$tl->page['parameter3'] = (isset($parameter3) ? urldecode($parameter3) : null);
					$tl->page['parameter4'] = (isset($parameter4) ? urldecode($parameter4) : null);
				
				// if no page is specified in the URL, assume home
					if (!$tl->page['template']) $tl->page['template'] = 'index';
				
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

				$tl->page['events'] = null;
				$tl->page['css'] = null;
				$tl->page['javascript'] = null;
				
		// Initialize application
		
			// Autoload database queries
				if ($tl->initialize['handle'] = opendir(__DIR__ . '/includes/models/autoload/.')) {
					while (false !== ($tl->initialize['file'] = readdir($tl->initialize['handle']))) {
						if (substr_count($tl->initialize['file'], '.php') > 0) include __DIR__ . '/includes/models/autoload/' . $tl->initialize['file'];
					}
					closedir($tl->initialize['handle']);
				}

			// Load third-party PHP libraries
				if (count(@$tl->backEndLibraries)) {
					foreach ($tl->backEndLibraries as $key => $value) {
						if ($value['local_path'] && file_exists(__DIR__ . '/../libraries_(protected)/' . $value['local_path'])) include __DIR__ . '/../libraries_(protected)/' . $value['local_path'];
						else die("Unable to locate " . $key . " at " . $value['local_path']);
					}
				}
				
			// Load Tidal Lock PHP libraries and instantiate classes
				if ($tl->initialize['handle'] = opendir(__DIR__ . '/../libraries_(protected)/tidal_lock/0-5/helpers/.')) {
					while (false !== ($tl->initialize['file'] = readdir($tl->initialize['handle']))) {
						if (substr_count($tl->initialize['file'], '.php') > 0) {
							// include file
								include __DIR__ . '/../libraries_(protected)/tidal_lock/0-5/helpers/' . $tl->initialize['file'];
							// instantiate class
								$tl->initialize['instance'] = str_replace('.php', '', str_replace('class.', '', $tl->initialize['file']));
								$tl->initialize['class'] = $tl->initialize['instance'] . '_TL';
								${$tl->initialize['instance']} = new $tl->initialize['class']();
						}
					}
					closedir($tl->initialize['handle']);
				}

				if ($tl->initialize['handle'] = opendir(__DIR__ . '/../libraries_(protected)/tidal_lock/0-5/widgets/.')) {
					while (false !== ($tl->initialize['file'] = readdir($tl->initialize['handle']))) {
						if (substr_count($tl->initialize['file'], '.php') > 0) include __DIR__ . '/../libraries_(protected)/tidal_lock/0-5/widgets/' . $tl->initialize['file'];
					}
					closedir($tl->initialize['handle']);
				}
				
			// Autoload shared controllers
				if ($tl->initialize['handle'] = opendir(__DIR__ . '/includes/controllers/autoload/.')) {
					while (false !== ($tl->initialize['file'] = readdir($tl->initialize['handle']))) {
						if (substr_count($tl->initialize['file'], '.php') > 0) include __DIR__ . '/includes/controllers/autoload/' . $tl->initialize['file'];
					}
					closedir($tl->initialize['handle']);
				}

			// Autoload shared views
				if ($tl->initialize['handle'] = opendir(__DIR__ . '/includes/views/autoload/.')) {
					while (false !== ($tl->initialize['file'] = readdir($tl->initialize['handle']))) {
						if (substr_count($tl->initialize['file'], '.php') > 0) include __DIR__ . '/includes/views/autoload/' . $tl->initialize['file'];
					}
					closedir($tl->initialize['handle']);
				}

			// Confirm setup
				$tl->db = $databases[$currentDatabase];
				if (!$tl->db['Server'] || !$tl->db['Name']) die ("Please configure database settings before proceeding.");
				if (!$tl->mail['Host'] || !$tl->mail['User'] || !$tl->mail['Password'] || !$tl->mail['OutgoingAddress'] || !$tl->mail['IncomingAddress']) die ("Please configure email settings before proceeding.");
				
				foreach ($salts_TL as $key => $value) {
					if (!$value) die ("Please configure encryption settings before proceeding.");
				}
				
			// Open database
				$dbConnection = new mysqli($tl->db['Server'], $tl->db['User'], $tl->db['Password'], $tl->db['Name'], @$tl->db['Port']);
				if($dbConnection->connect_errno > 0){
		    		die ("Unable to connect to database [" . $dbConnection->connect_error . "]");
				}
				$dbConnection->set_charset('utf8');

			// initialize CMS
				$cms = new cms_widget_TL();

			// check for redirect
				if (!@$tl->page['is_cron'] && !@$tl->page['is_ajax']) {
					$result = $cms->retrieveContent([$tablePrefix . 'cms.content_type'=>'r', $tablePrefix . 'cms.source_url'=>$tl->page['template']]);
					if (count($result) == 1) {
						if ($result[0]['http_status']) header("HTTP/1.1 " . $result[0]['http_status'] . " " . $result[0]['http_status_label']);
						$authentication_manager->forceRedirect($result[0]['destination_url']);
					}
				}

			// check for alias
				if (!@$tl->page['is_cron'] && !@$tl->page['is_ajax']) {
					$result = $cms->retrieveContent([$tablePrefix . 'cms.content_type'=>'a', $tablePrefix . 'cms.source_url'=>$tl->page['template']]);
					if (count($result) == 1) {
						$tl->page['aliased_from'] = $tl->page['template'];
						$tl->page['template'] = $result[0]['destination_url'];
					}
				}

			// Load environmentals
				$detector->page();
				$tl->page = array_merge($tl->page, $detector->page);
				
			// Autoparse any available filters
				for ($counter = 1; $counter <= 4; $counter++) {
					if (!@$tl->page['filters'] && substr_count(@$tl->page['parameter' . $counter], '=')) $tl->page['filters'] = $keyvalue_array->keyValueToArray($tl->page['parameter' . $counter], '|');
				}

			// expand confirmation messages, if required
				foreach ($cms->confirmation_types as $id => $type) {
					if (@$tl->page['filters'][$type]) {
						$result = $cms->retrieveContent([$tablePrefix . 'cms.public_id'=>@$tl->page['filters'][$type], $tablePrefix . 'cms.content_type'=>'c', $tablePrefix . 'cms.confirmation_type'=>$id]);
						if (count($result) == 1) $tl->page[$type] = $result[0]['content'];
						else $tl->page[$type] = ucfirst(strtolower(str_replace('_', ' ', $tl->page['filters'][$type])));
					}
				}
				unset ($id);
				unset ($type);
				
			// Load settings
				$result = retrieveFromDb('settings');
				for ($counter = 0; $counter < count($result); $counter++) {
					$tl->settings[$result[$counter]['setting']] = $result[$counter]['value'];
				}

			// Autoload global arrays
				if ($handle = opendir(__DIR__ . '/../global_arrays/.')) {
					while (false !== ($file = readdir($handle))) {
						if (substr_count($file, '.php') > 0) include __DIR__ . '/../global_arrays/' . $file;
					}
					closedir($handle);
				}

			// Determine domain alias
				$result = $cms->retrieveContent([$tablePrefix . 'cms.content_type'=>'d', $tablePrefix . 'cms.source_url_subdomain'=>str_replace('www', '', @$tl->page['subdomain']), $tablePrefix . 'cms.source_url'=>@$tl->page['domain']]);
				if (count($result) == 1) $tl->page['domain_alias'] = $result[0];
				if (@$tl->page['domain_alias']['title']) $tl->settings['Name of this application'] = $tl->page['domain_alias']['title'];
				if (@$tl->page['domain_alias']['content']) $tl->settings['Describe this application'] = $tl->page['domain_alias']['content'];

			// Verify that root path of application is in database
				if (!$tl->settings['Root URL']) {
					$tl->settings['Root URL'] = $tl->page['protocol'] . $tl->page['root'];
					updateOrInsertIntoDb('preferences', array('value'=>$tl->settings['Root URL'], 'input_type'=>'text', 'is_mandatory'=>'1'), array('preference'=>'Root URL'), null, null, null, null, 1);
				}
		
			// Set timezone
				if (!$tl->settings['Home timezone']) $tl->settings['Home timezone'] = 'US/Eastern';
				date_default_timezone_set($tl->settings['Home timezone']);
				putenv('TZ=' . $tl->settings['Home timezone']);
				
			// Verify salts have been provided
				if ($salts_TL) foreach ($salts_TL as $name => $salt) {
					if (!$salt) die ("Please configure encrypton options by providing valid salts.");
				}
		
			// Verify cron connection
				if (@$currentDatabase == 'production') {

					if (@$tl->page['is_cron']) {

						$previousUnresolvedAlert = retrieveSingleFromDb('logs', null, array('activity'=>"Cron has stopped connecting for some reason (perhaps a server outage?)", 'is_error'=>'1', 'is_resolved'=>'0'), null, null, null, null, null, 'connected_on DESC');
						if (count($previousUnresolvedAlert)) {
							$previousCronConnection = retrieveSingleFromDb('logs', null, array('connection_type'=>'R'), null, null, null, null, null, 'connected_on DESC');
							if (!count($previousCronConnection) || $previousCronConnection[0]['connected_on'] < $previousUnresolvedAlert[0]['connected_on']) {
								emailSystemNotification("Cron is now successfully connecting. The error has been left unresolved for diagnostic purposes.", 'Cron resumed');
							}
						}

					}
					else {
					
						if ($tl->settings['Enable cron connections'] && $tl->settings['Interval between cron connections'] > 0) {
							$cronError = null;
							$numberOfMinutesSincePreviousCronConnection = null;
							$previousCronConnection = retrieveSingleFromDb('logs', null, array('connection_type'=>'R'), null, null, null, null, null, 'connected_on DESC');
		
							if (count($previousCronConnection) < 1) $cronError = "Cron does not appear to have attempted a connection yet";
							else {
								$numberOfMinutesSincePreviousCronConnection = round((time() - strtotime($previousCronConnection[0]['connected_on'])) / 60, 0);
								if ($numberOfMinutesSincePreviousCronConnection >= ($tl->settings['Interval between cron connections'] * 2)) {
									$cronError = "As of " . date('Y-m-d H:i A') . ", cron has stopped connecting for some reason (perhaps a server outage?)";
								}
							}
							
							if ($cronError) {
								$logger->logItInDb($cronError, null, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);
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
					if (@$tl->page['domain_alias']['cms_id']) $otherCriteria .= " AND domain_alias_id = '" . $tl->page['domain_alias']['cms_id'] . "'";
					$result = retrieveRumours(array('assigned_to'=>'', $tablePrefix . 'rumours.enabled'=>'1'), null, $otherCriteria);
					@$logged_in['rumours_assigned'] += count($result);
					unset($otherCriteria);
				}

			// terminate session for banned IPs
				$blacklistedOrSuspicious = retrieveSingleFromDb('ips', null, ['ip'=>$operators->firstTrue(@$_SERVER['REMOTE_ADDR'], @$_SERVER['REMOTE_HOST'])], null, null, null, "status = 'b' OR status = 's'");
				if (count($blacklistedOrSuspicious)) {
					updateDbSingle('ips', ['user_id'=>@$logged_in['user_id'], 'attempts'=>(floatval($blacklistedOrSuspicious[0]['attempts']) + 1), 'updated_on'=>date('Y-m-d H:i:s')], ['ip_id'=>$blacklistedOrSuspicious[0]['ip_id']]);
					if ($blacklistedOrSuspicious[0]['status'] == 'b') $tl->page['template'] = 'blacklisted';
				}
				unset ($blacklistedOrSuspicious);
				
			// Log session in DB
				$sessionID = rand(1,999999999);
				insertIntoDb('sessions', array('session_id'=>$sessionID, 'connected_on'=>date('Y-m-d H:i:s'), 'template'=>$_SERVER['REQUEST_URI'], 'user_id'=>@$logged_in['user_id'], 'user_agent'=>$_SERVER['HTTP_USER_AGENT'], 'ip'=>(@$_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : @$_SERVER['REMOTE_HOST'])));

			// Clear any garbage in database which wasn't deleted due to inappropriate closure
				
			// Execute page-specific controllers
				if (!@$tl->page['is_cron'] && file_exists(__DIR__ . '/includes/controllers/custom/' . $tl->page['template'] . '.php')) include __DIR__ . '/includes/controllers/custom/' . $tl->page['template'] . '.php';
				elseif (!@$tl->page['is_cron'] && file_exists(__DIR__ . '/includes/controllers/default/' . $tl->page['template'] . '.php')) include __DIR__ . '/includes/controllers/default/' . $tl->page['template'] . '.php';

			// Execute page-specific models
				if(!@$tl->page['is_cron'] && file_exists(__DIR__ . '/includes/models/' . $tl->page['template'] . '.php')) include __DIR__ . '/includes/models/' . $tl->page['template'] . '.php';
						
			// Confirm database has been imported
				$tables = retrieveTables_TL();
				if (count($tables) < 1) die ("Please import database before proceeding.");

		// Load view
			if (!@$tl->page['is_cron'] && !@$tl->page['is_ajax']) {
				if (@$tl->page['subdomain'] == 'm') $view = 'mobile';
				elseif (@$tl->page['subdomain'] == 't')  $view = 'tablet';
				elseif (@$tl->page['subdomain'] == 'api') $view = 'api';
				else $view = 'desktop';

				if ($tl->page['template'] != 'maintenance' && @$tl->settings['Maintenance Mode'] == 'On' && !@$logged_in['is_administrator']) $authentication_manager->forceRedirect('/maintenance');

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
					else { //search for CMS page

						// look for matching domain alias and language
							$result = $cms->retrieveContent([$tablePrefix . 'cms.destination_url'=>$tl->page['template'], $tablePrefix . 'cms.content_type'=>'p', $tablePrefix . 'cms.language_id'=>$operators->firstTrue(@$tl->page['domain_alias']['language_id'], @$tl->settings['Default language']), $tablePrefix . 'cms.domain_alias_id'=>@$tl->page['domain_alias']['cms_id']]);

							if (!count(@$result)) {
								// look for matching page, ideally with matching language, but no domain alias
									$result = $cms->retrieveContent([$tablePrefix . 'cms.destination_url'=>$tl->page['template'], $tablePrefix . 'cms.content_type'=>'p', $tablePrefix . 'cms.language_id'=>$operators->firstTrue(@$tl->page['domain_alias']['language_id'], @$tl->settings['Default language']), $tablePrefix . 'cms.domain_alias_id'=>'0']);
							}

							if (!count(@$result)) {
								// look for matching page with default site language, but no domain alias
									$result = $cms->retrieveContent([$tablePrefix . 'cms.destination_url'=>$tl->page['template'], $tablePrefix . 'cms.content_type'=>'p', $tablePrefix . 'cms.language_id'=>@$tl->settings['Default language'], $tablePrefix . 'cms.domain_alias_id'=>'0']);
							}

							if (!count(@$result)) {
								// look for matching page, with no language and no domain alias
									$result = $cms->retrieveContent([$tablePrefix . 'cms.destination_url'=>$tl->page['template'], $tablePrefix . 'cms.content_type'=>'p', $tablePrefix . 'cms.language_id'=>'', $tablePrefix . 'cms.domain_alias_id'=>'0']);
							}

							if (count($result)) {

								if ($result[0]['login_required'] && !$logged_in) {
									$authentication_manager->forceLoginThenRedirectHere();
								}
								else {

									$tl->page['title'] = $result[0]['title'];
									$tl->page['css'] = $result[0]['content_css'];
									include __DIR__ . '/includes/views/' . $view . '/shared/page_top.php';

									$otherLanguages = $cms->retrieveContent([$tablePrefix . 'cms.destination_url'=>$tl->page['template'], $tablePrefix . 'cms.content_type'=>'p', $tablePrefix . 'cms.domain_alias_id'=>$result[0]['domain_alias_id']], $tablePrefix . "cms.cms_id != '" . $result[0]['cms_id'] . "'");

									if (!count($otherLanguages)) {
										echo $result[0]['content'] . "\n";
									}
									else {
										echo "<div class='tab-content'>\n";
										echo "  <div class='tab-pane active' id='" . $result[0]['language_id'] . "'>\n";
										echo "    " . $result[0]['content'] . "\n";
									  	echo "  </div>\n";
										for ($counter = 0; $counter < count($otherLanguages); $counter++) {
											echo "  <div class='tab-pane' id='" . $otherLanguages[$counter]['language_id'] . "'>\n";
											echo "    " . $otherLanguages[$counter]['content'] . "\n";
										  	echo "  </div>\n";
										}
									  	echo "</div>\n";

										if (count($otherLanguages) || $logged_in['can_edit_content']) {
											if (count($otherLanguages)) {
												echo "    <ul class='nav nav-pills mutedPills'>\n";
												echo "      <li class='active'><a href='#" . $result[0]['language_id'] . "' data-toggle='tab'>" . (@$result[0]['native_language'] ? $result[0]['native_language'] : $result[0]['language']) . "</a></li>\n";
												for ($counter = 0; $counter < count($otherLanguages); $counter++) {
													echo "      <li><a href='#" . $otherLanguages[$counter]['language_id'] . "' data-toggle='tab'>" . (@$otherLanguages[$counter]['native_language'] ? $otherLanguages[$counter]['native_language'] : $otherLanguages[$counter]['language']) . "</a></li>\n";
												}
												echo "    </ul>\n";
											}
										}
									}

									if ($logged_in['can_edit_content']) echo "    <div class='text-right'><a href='/admin_cms/" . urlencode("screen=edit_content|cms_id=" . $result[0]['cms_id']) . "' class='btn btn-link'>Edit</a></div>\n";

									$tl->page['javascript'] = $result[0]['content_js'];
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
