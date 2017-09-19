<?php

		session_start();
		parse_str($_SERVER['QUERY_STRING']);

		$tl = new stdClass();

		$tl->page =					[];
		$tl->settings =				[];
		$tl->session =				[];
		$tl->db =					[];
		$tl->mail =					[];
		$tl->salts =				[];
		$tl->api =					[];
		$tl->backEndLibraries =		[];
		$tl->frontEndLibraries =	[];
		$tl->initialize =			[];

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
			$tl->page['error'] =				null;
			$tl->page['warning'] =				null;
			$tl->page['success'] =				null;
			$tl->page['console'] =				null;

			$tl->page['title'] =				ucwords(str_replace('_', ' ', $tl->page['template']));
			$tl->page['section'] =				null;
			$tl->page['canonical_url'] =		null;
			$tl->page['hide_page_chrome'] =		false;

			$tl->page['events'] =				null;
			$tl->page['css'] =					null;
			$tl->page['javascript'] =			null;
				
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
				
				if ($tl->salts) {
					foreach ($tl->salts as $key => $value) {
						if (!$value) die ("Please configure encrypton options by providing valid salts.");
					}
				}
				
			// Open database
				$dbConnection = new mysqli($tl->db['Server'], $tl->db['User'], $tl->db['Password'], $tl->db['Name'], @$tl->db['Port']);
				if($dbConnection->connect_errno > 0){
		    		die ("Unable to connect to database [" . $dbConnection->connect_error . "]");
				}
				$dbConnection->set_charset('utf8');

			// check for redirect
				if (!@$tl->page['is_cron'] && !@$tl->page['is_ajax']) {
					$tl->initialize['redirect'] = $database_manager->retrieveSingle('cms', null, ['content_type'=>'r', 'source_url'=>$tl->page['template']]);
					if (count($tl->initialize['redirect'])) {
						if ($tl->initialize['redirect'][0]['http_status']) header("HTTP/1.1 " . $tl->initialize['redirect'][0]['http_status'] . " " . $tl->initialize['redirect'][0]['http_status_label']);
						$authentication_manager->forceRedirect($tl->initialize['redirect'][0]['destination_url']);
					}
				}

			// check for alias
				if (!@$tl->page['is_cron'] && !@$tl->page['is_ajax']) {
					$tl->initialize['alias'] = $database_manager->retrieveSingle('cms', null, ['content_type'=>'a', 'source_url'=>$tl->page['template']]);
					if (count($tl->initialize['alias'])) {
						$tl->page['aliased_from'] = $tl->page['template'];
						$tl->page['template'] = $tl->initialize['alias'][0]['destination_url'];
					}
				}

			// Load environmentals
				$detector->page();
				$tl->page = array_merge($tl->page, $detector->page);
				
			// Autoparse any available filters
				for ($counter = 1; $counter <= 4; $counter++) { // there are 4 potential parameters specified in Apache's RewriteRule
					if (!@$tl->page['filters'] && substr_count(@$tl->page['parameter' . $counter], '=')) $tl->page['filters'] = $keyvalue_array->keyValueToArray($tl->page['parameter' . $counter], '|');
				}

			// expand confirmation messages, if required
				$result = new cms_widget_TL();
				$tl->initialize['confirmation_types'] = $result->confirmation_types;
				foreach ($tl->initialize['confirmation_types'] as $key => $value) {
					if (@$tl->page['filters'][$value]) {
						$tl->initialize['confirmation'] = $database_manager->retrieveSingle('cms', null, ['public_id'=>@$tl->page['filters'][$value], 'content_type'=>'c', 'confirmation_type'=>$key]);
						if (count($tl->initialize['confirmation'])) $tl->page[$value] = $tl->initialize['confirmation'][0]['content'];
						else $tl->page[$value] = ucfirst(strtolower(str_replace('_', ' ', $tl->page['filters'][$value])));
					}
				}
				
			// Load settings
				$tl->initialize['settings'] = $database_manager->retrieve('settings');
				for ($counter = 0; $counter < count($tl->initialize['settings']); $counter++) {
					$tl->settings[$tl->initialize['settings'][$counter]['setting']] = $tl->initialize['settings'][$counter]['value'];
				}

			// Autoload global arrays
				if ($tl->initialize['handle'] = opendir(__DIR__ . '/../global_arrays/.')) {
					while (false !== ($tl->initialize['file'] = readdir($tl->initialize['handle']))) {
						if (substr_count($tl->initialize['file'], '.php') > 0) include __DIR__ . '/../global_arrays/' . $tl->initialize['file'];
					}
					closedir($tl->initialize['handle']);
				}

			// Determine domain alias
				$tl->initialize['domain_alias'] = $database_manager->retrieveSingle('cms', null, ['content_type'=>'d', 'source_url_subdomain'=>str_replace('www', '', @$tl->page['subdomain']), 'source_url'=>@$tl->page['domain']]);
				if (count($tl->initialize['domain_alias'])) $tl->page['domain_alias'] = $tl->initialize['domain_alias'][0];
				if (@$tl->page['domain_alias']['title']) $tl->settings['Name of this application'] = $tl->page['domain_alias']['title'];
				if (@$tl->page['domain_alias']['content']) $tl->settings['Describe this application'] = $tl->page['domain_alias']['content'];

			// Verify that root path of application is in database
				if (!$tl->settings['Root URL']) {
					$tl->settings['Root URL'] = $tl->page['protocol'] . $tl->page['root'];
					$database_manager->updateOrInsert('preferences', ['value'=>$tl->settings['Root URL'], 'input_type'=>'text', 'is_mandatory'=>'1'], ['preference'=>'Root URL'], null, null, null, null, 1);
				}
		
			// Set timezone
				if (!$tl->settings['Home timezone']) $tl->settings['Home timezone'] = 'US/Eastern';
				date_default_timezone_set($tl->settings['Home timezone']);
				putenv('TZ=' . $tl->settings['Home timezone']);
				
			// Initialize Attributable
				if (@$attributableConfig[$currentAttributableCredentials]['API']) {
					$attributable = new attributable();
					$attributable->key = $attributableConfig[$currentAttributableCredentials]['API'];
				}

			// Terminate session for blacklisted IPs
				if ($tl->settings['Block blacklisted users']) {

					$tl->initialize['connecting_ip'] = (@$_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : @$_SERVER['REMOTE_HOST']);
					if (strlen($tl->initialize['connecting_ip']) < 16) $tl->initialize['blacklisted'] = $database_manager->retrieveSingle('blacklisted_ips', null, ['ipv4'=>$parser->encodeIP($tl->initialize['connecting_ip'])]);
					elseif ($tl->initialize['connecting_ip']) $tl->initialize['blacklisted'] = $database_manager->retrieveSingle('blacklisted_ips', null, ['ipv6'=>$parser->encodeIP($tl->initialize['connecting_ip'])]);

					if (count(@$tl->initialize['blacklisted'])) {
						$activity = "Attempted connection by blacklisted IP " . $tl->initialize['connecting_ip'] . " intercepted";
						$attributableOutput = $attributable->capture($activity, null, null, ['domain_alias_id'=>@$tl->page['domain_alias']['cms_id']]);
						if (!@count($attributableOutput['content']['success'])) emailSystemNotification(__FILE__ . ": " . (is_array($attributableOutput) ? print_r($attributableOutput, true) : $attributableOutput) . (@$logged_in ? " [" . $logged_in['username'] . "]" : false), 'Attributable failure');

						$tl->page['template'] = 'blacklisted';
					}

				}

			// Verify cron connection
				if (@$currentDatabase == 'production') {

					if (@$tl->page['is_cron']) {

						$tl->initialize['previous_unresolved_alert'] = $database_manager->retrieveSingle('logs', null, ['activity'=>"Cron has stopped connecting for some reason (perhaps a server outage?)", 'is_error'=>'1', 'is_resolved'=>'0'], null, null, null, null, null, 'connected_on DESC');
						if (count($tl->initialize['previous_unresolved_alert'])) {
							$tl->initialize['previous_cron'] = $database_manager->retrieveSingle('logs', null, array('connection_type'=>'R'), null, null, null, null, null, 'connected_on DESC');
							if (!count($tl->initialize['previous_cron']) || $tl->initialize['previous_cron'][0]['connected_on'] < $tl->initialize['previous_unresolved_alert'][0]['connected_on']) {
								emailSystemNotification("Cron is now successfully connecting. The error has been left unresolved for diagnostic purposes.", 'Cron resumed');
							}
						}

					}
					else {
					
						if ($tl->settings['Enable cron connections'] && $tl->settings['Interval between cron connections'] > 0) {
							$tl->initialize['cron_error'] = null;
							$tl->initialize['minutes_since_previous_cron'] = null;
							$tl->initialize['previous_cron'] = $database_manager->retrieveSingle('logs', null, array('connection_type'=>'R'), null, null, null, null, null, 'connected_on DESC');
		
							if (count($tl->initialize['previous_cron']) < 1) $tl->initialize['cron_error'] = "Cron does not appear to have attempted a connection yet";
							else {
								$tl->initialize['minutes_since_previous_cron'] = round((time() - strtotime($tl->initialize['previous_cron'][0]['connected_on'])) / 60, 0);
								if ($tl->initialize['minutes_since_previous_cron'] >= ($tl->settings['Interval between cron connections'] * 2)) {
									$tl->initialize['cron_error'] = "As of " . date('Y-m-d H:i A') . ", cron has stopped connecting for some reason (perhaps a server outage?)";
								}
							}
							
							if ($tl->initialize['cron_error']) {
								$activity = $tl->initialize['cron_error'];
								$logger->logItInDb($activity, null, null, array('is_error'=>'1', 'is_resolved'=>'0'), true);

								$attributableOutput = $attributable->capture($activity, null, ['user_id'=>$logged_in['user_id'], 'first_name'=>$logged_in['first_name'], 'last_name'=>$logged_in['last_name'], 'email'=>$logged_in['email'], 'phone'=>$logged_in['primary_phone']], ['user_id'=>@$user[0]['user_id'], 'domain_alias_id'=>@$tl->page['domain_alias']['cms_id']], 1);
								if (!@count($attributableOutput['content']['success'])) emailSystemNotification(__FILE__ . ": " . (is_array($attributableOutput) ? print_r($attributableOutput, true) : $attributableOutput) . (@$logged_in ? " [" . $logged_in['username'] . "]" : false), 'Attributable failure');

								emailSystemNotification($tl->initialize['cron_error'], 'Critical error');
							}
						}
						else {
							$tl->initialize['run_housekeeping'] = false;
							$tl->initialize['hours_since_previous_housekeeping'] = null;
							$tl->initialize['previous_housekeeping'] = $database_manager->retrieveSingle('logs', null, array('activity'=>'Housekeeping manually initiated'), null, null, null, null, null, 'connected_on DESC');
							
							if (count($tl->initialize['previous_housekeeping']) < 1) $tl->initialize['run_housekeeping'] = true;
							else {
								$tl->initialize['hours_since_previous_housekeeping'] = round((time() - strtotime($tl->initialize['previous_housekeeping'][0]['connected_on'])) / 60 / 60, 0);
								if ($tl->initialize['hours_since_previous_housekeeping'] >= 24) {
									$tl->initialize['run_housekeeping'] = true;
								}
							}
							
							if ($tl->initialize['run_housekeeping']) {
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

			// Log session in DB
				$tl->session['id'] = rand(1,999999999);
				$database_manager->insert('sessions', ['session_id'=>$tl->session['id'], 'connected_on'=>date('Y-m-d H:i:s'), 'template'=>$_SERVER['REQUEST_URI'], 'user_id'=>@$logged_in['user_id'], 'user_agent'=>$_SERVER['HTTP_USER_AGENT'], 'ip'=>(@$_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : @$_SERVER['REMOTE_HOST'])]);

			// Execute page-specific controllers
				if (!@$tl->page['is_cron'] && file_exists(__DIR__ . '/includes/controllers/custom/' . $tl->page['template'] . '.php')) include __DIR__ . '/includes/controllers/custom/' . $tl->page['template'] . '.php';
				elseif (!@$tl->page['is_cron'] && file_exists(__DIR__ . '/includes/controllers/default/' . $tl->page['template'] . '.php')) include __DIR__ . '/includes/controllers/default/' . $tl->page['template'] . '.php';

			// Execute page-specific models
				if(!@$tl->page['is_cron'] && file_exists(__DIR__ . '/includes/models/' . $tl->page['template'] . '.php')) include __DIR__ . '/includes/models/' . $tl->page['template'] . '.php';
						
			// Confirm database has been imported
				if (count($database_manager->tables()) < 1) die ("Please import database before proceeding.");

		// Load view
			if (!@$tl->page['is_cron'] && !@$tl->page['is_ajax']) {
				if (@$tl->page['subdomain'] == 'm') $tl->initialize['view'] = 'mobile';
				elseif (@$tl->page['subdomain'] == 't')  $tl->initialize['view'] = 'tablet';
				elseif (@$tl->page['subdomain'] == 'api') $tl->initialize['view'] = 'api';
				elseif (@$tl->page['subdomain'] == 'iframe') $tl->initialize['view'] = 'iframe';
				else $tl->initialize['view'] = 'desktop';

				if ($tl->page['template'] != 'maintenance' && @$tl->settings['Maintenance Mode'] == 'On' && !@$logged_in['is_administrator']) $authentication_manager->forceRedirect('/maintenance');

				if ($tl->initialize['view'] == 'api') {
					if (file_exists(__DIR__ . '/includes/views/api/' . $tl->page['template'] . ".php")) include __DIR__ . '/includes/views/api/' . $tl->page['template'] . ".php";
				}
				elseif ($tl->initialize['view'] == 'iframe') {
					if (file_exists(__DIR__ . '/includes/views/iframe/' . $tl->page['template'] . ".php")) include __DIR__ . '/includes/views/iframe/' . $tl->page['template'] . ".php";
				}
				else {

					if (file_exists(__DIR__ . '/includes/views/' . $tl->initialize['view'] . '/custom/' . $tl->page['template'] . ".php")) {
						include __DIR__ . '/includes/views/desktop/shared/page_top.php';
						include __DIR__ . '/includes/views/' . $tl->initialize['view'] . '/custom/' . $tl->page['template'] . ".php";
						include __DIR__ . '/includes/views/desktop/shared/page_bottom.php';
					}
					elseif (file_exists(__DIR__ . '/includes/views/' . $tl->initialize['view'] . '/default/' . $tl->page['template'] . ".php")) {
						include __DIR__ . '/includes/views/desktop/shared/page_top.php';
						include __DIR__ . '/includes/views/' . $tl->initialize['view'] . '/default/' . $tl->page['template'] . ".php";
						include __DIR__ . '/includes/views/desktop/shared/page_bottom.php';
					}
					else { //search for CMS page

						// look for matching domain alias and language
							$result = $database_manager->retrieve('cms', null, ['destination_url'=>$tl->page['template'], 'content_type'=>'p', 'language_id'=>(@$tl->page['domain_alias']['language_id'] ? $tl->page['domain_alias']['language_id'] : @$tl->settings['Default language']), 'domain_alias_id'=>@$tl->page['domain_alias']['cms_id']]);

							if (!count(@$result)) {
								// look for matching page, ideally with matching language, but no domain alias
									$result = $database_manager->retrieve('cms', null, ['destination_url'=>$tl->page['template'], 'content_type'=>'p', 'language_id'=>(@$tl->page['domain_alias']['language_id'] ? $tl->page['domain_alias']['language_id'] : @$tl->settings['Default language']), 'domain_alias_id'=>'0']);
							}

							if (!count(@$result)) {
								// look for matching page with default site language, but no domain alias
									$result = $database_manager->retrieve('cms', null, ['destination_url'=>$tl->page['template'], 'content_type'=>'p', 'language_id'=>@$tl->settings['Default language'], 'domain_alias_id'=>'0']);
							}

							if (!count(@$result)) {
								// look for matching page, with no language and no domain alias
									$result = $database_manager->retrieve('cms', null, ['destination_url'=>$tl->page['template'], 'content_type'=>'p', 'language_id'=>'', 'domain_alias_id'=>'0']);
							}

							if (count($result)) {

								if ($result[0]['is_login_required'] && !$logged_in) {
									$authentication_manager->forceLoginThenRedirectHere();
								}
								else {

									$tl->page['title'] = $result[0]['title'];
									$tl->page['css'] = $result[0]['content_css'];
									include __DIR__ . '/includes/views/' . $tl->initialize['view'] . '/shared/page_top.php';

									$tl->initialize['other_languages'] = $database_manager->retrieve('cms', null, ['destination_url'=>$tl->page['template'], 'content_type'=>'p', 'domain_alias_id'=>$result[0]['domain_alias_id']], null, null, null, "cms_id != '" . $result[0]['cms_id'] . "'");

									if (!count($tl->initialize['other_languages'])) {
										echo $result[0]['content'] . "\n";
									}
									else {
										echo "<div class='tab-content'>\n";
										echo "  <div class='tab-pane active' id='" . $result[0]['language_id'] . "'>\n";
										echo "    " . $result[0]['content'] . "\n";
									  	echo "  </div>\n";
										for ($counter = 0; $counter < count($tl->initialize['other_languages']); $counter++) {
											echo "  <div class='tab-pane' id='" . $tl->initialize['other_languages'][$counter]['language_id'] . "'>\n";
											echo "    " . $tl->initialize['other_languages'][$counter]['content'] . "\n";
										  	echo "  </div>\n";
										}
									  	echo "</div>\n";

										if (count($tl->initialize['other_languages']) || $logged_in['can_edit_content']) {
											if (count($tl->initialize['other_languages'])) {
												echo "    <ul class='nav nav-pills mutedPills'>\n";
												echo "      <li class='active'><a href='#" . $result[0]['language_id'] . "' data-toggle='tab'>" . (@$result[0]['native_language'] ? $result[0]['native_language'] : $result[0]['language']) . "</a></li>\n";
												for ($counter = 0; $counter < count($tl->initialize['other_languages']); $counter++) {
													echo "      <li><a href='#" . $tl->initialize['other_languages'][$counter]['language_id'] . "' data-toggle='tab'>" . (@$tl->initialize['other_languages'][$counter]['native_language'] ? $tl->initialize['other_languages'][$counter]['native_language'] : $tl->initialize['other_languages'][$counter]['language']) . "</a></li>\n";
												}
												echo "    </ul>\n";
											}
										}
									}

									if ($logged_in['can_edit_content']) echo "    <div class='text-right'><a href='/admin_cms/" . urlencode("screen=edit_content|cms_id=" . $result[0]['cms_id']) . "' class='btn btn-link'>Edit</a></div>\n";

									$tl->page['javascript'] = $result[0]['content_js'];
									include __DIR__ . '/includes/views/' . $tl->initialize['view'] . '/shared/page_bottom.php';

								}

							}
							else $authentication_manager->forceRedirect('/404');

					}

				}

			}

		// clear session in DB
			$database_manager->deleteSingle('sessions', array('session_id'=>$tl->session['id']));

?>
