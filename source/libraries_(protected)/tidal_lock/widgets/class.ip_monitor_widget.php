<?php

	class ip_monitor_widget_TL {

		public $html = null;
		public $js = null;
		public $profile_template = 'profile';

		public $screen = null;
		public $id = null;

		private $blacklisted = null;
		private $whitelisted = null;
		private $suspicious = null;
		private $editIP = null;

		private $statuses = ['b'=>'Blacklisted', 'w'=>'Whitelisted', 's'=>'Suspicious'];

		public function initialize() {

			global $tl;
			global $tablePrefix;

			$authentication_manager = new authentication_manager_TL();

			// parse query string
				$this->screen = @$tl->page['filters']['screen'];
				$this->id = floatval(@$tl->page['filters']['id']);

				if (!$this->screen) $this->screen = 'all';
				elseif ($this->screen && $this->screen != 'edit_ip' && $this->screen != 'add_ip') {
					$authentication_manager->forceRedirect('/' . $tl->page['template']);
				}
				elseif ($this->screen == 'edit_ip' && !$this->id) {
					$authentication_manager->forceRedirect('/' . $tl->page['template']);
				}

			// queries
				if ($this->screen == 'edit_ip') {
					$this->editIP = $this->retrieveIPs(['ip_id'=>$this->id]);
					if (!count($this->editIP)) $authentication_manager->forceRedirect('/' . $tl->page['template']);
					$tl->page['title'] = "Edit an IP";
				}
				elseif ($screen == 'add_ip') {
					$tl->page['title'] = "Add an IP";
				}
				else {
					$this->blacklisted = $this->retrieveIPs(['status'=>'b'], null, null, $tablePrefix . "ips.attempts DESC, " . $tablePrefix . "ips.country_id ASC, " . $tablePrefix . "ips.city ASC, " . $tablePrefix . "ips.updated_on ASC");
					$this->whitelisted = $this->retrieveIPs(['status'=>'w'], null, null, $tablePrefix . "ips.country_id ASC, " . $tablePrefix . "ips.city ASC, " . $tablePrefix . "ips.updated_on ASC");
					$this->suspicious = $this->retrieveIPs(['status'=>'s'], null, null, $tablePrefix . "ips.attempts DESC, " . $tablePrefix . "ips.country_id ASC, " . $tablePrefix . "ips.city ASC, " . $tablePrefix . "ips.updated_on ASC");
					$tl->page['title'] = "Security";
				}

			// call controllers
				if (count($_POST)) $this->controllers();

			// call views
				$this->createView();

		}

		private function retrieveIPs($matching = null, $containing = null, $otherCriteria = null, $sortBy = null, $limit = false) {
			
			global $dbConnection;
			global $tablePrefix;
			
			// build query
				$query = "SELECT " . $tablePrefix . "ips.*,";
				$query .= " " . $tablePrefix . "users.username as username,";
				$query .= " TRIM(CONCAT(" . $tablePrefix . "users.first_name, ' ', " . $tablePrefix . "users.last_name)) as full_name";
				$query .= " FROM " . $tablePrefix . "ips";
				$query .= " LEFT JOIN " . $tablePrefix . "users ON " . $tablePrefix . "ips.user_id = " . $tablePrefix . "users.user_id";
				$query .= " WHERE 1=1";
				if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
				if ($containing) foreach ($containing as $field => $value) $query .= " AND " . $field . " LIKE '%" . $dbConnection->escape_string($value) . "%'";
				if ($otherCriteria) $query .= " AND (" . $otherCriteria . ")";
				if ($sortBy) $query .= " ORDER BY " . $sortBy;
				if ($limit) $query .= " LIMIT " . $dbConnection->escape_string($limit);
				
				$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . ': ' . $dbConnection->error . '<br /><br />' . $query);

			// create array
				$parser = new parser_TL();
				$items = $parser->mySqliResourceToArray($result);
				
			// clear memory
				$result->free();

			// return array
				return $items;
			
		}

		private function controllers() {

			global $logged_in;
			global $tl;

			$authentication_manager = new authentication_manager_TL();
			$parser = new parser_TL();
			$detector = new detector_TL();
			$logger = new logger_TL();

			$tl->page['error'] = '';

			if ($_POST['formName'] == 'updateIpForm' && @$_POST['forgetRequested'] == 'Y' && $this->id) {

				// delete
					$success = deleteFromDbSingle('ips', ['ip_id'=>$this->id]);
					if (!$success) $tl->page['error'] .= "There was a problem deleting this IP from the database. ";
					else {

						// update log
							$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has stopped tracking the IP " . $this->id;
							$logger->logItInDb($activity, null, ['user_id=' . $logged_in['user_id'], 'ip_id=' . $this->id]);

						// redirect
							$authentication_manager->forceRedirect('/admin_security/success=ip_forgotten');

					}

			}
			elseif ($_POST['formName'] == 'updateIpForm') {

				// clean input
					$_POST = $parser->trimAll($_POST);

				// check for errors
					if (!$_POST['status']) $tl->page['error'] .= "Please specify a status.\n";
					if ($this->screen == 'add_ip') {
						if (!$_POST['ip']) $tl->page['error'] .= "Please specify an IP number.\n";
						else {
							$exists = retrieveSingleFromDb('ips', null, ['ip'=>$_POST['ip']]);
							if ($exists) $tl->page['error'] .= "This IP is already being monitored.\n";
						}
					}

					if (!$tl->page['error']) {

						// update DB

							if ($this->screen == 'add_ip') {
								$detector->connection['ip'] = $_POST['ip'];
								$detector->connection();
								$id = insertIntoDb('ips', ['ip'=>$_POST['ip'], 'status'=>$_POST['status'], 'country_id'=>$detector->connection['country'], 'city'=>$detector->connection['city']]);
							}

							updateDbSingle('ips', ['status'=>$_POST['status'], 'notes'=>@$_POST['notes'], 'updated_by'=>$logged_in['user_id'], 'updated_on'=>date('Y-m-d H:i:s')], ['ip_id'=>$this->id]);

						// update log
							$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has " . ($this->screen == 'add_ip' ? "added the IP " . $_POST['ip'] : "updated the IP " . $this->id);
							$logger->logItInDb($activity, null, ['user_id=' . $logged_in['user_id'], 'ip_id=' . $this->id]);
						
						// redirect
							$authentication_manager->forceRedirect('/' . $tl->page['template'] . '/success=ip_saved');

					}

			}

		}

		private function createView() {

			global $tl;

			$form = new form_TL();
			$operators = new operators_TL();
			$localization_manager = new localization_manager_TL();
			$localization_manager->populateCountries();

			$this->html .= "<h2>" . $tl->page['title'] . "</h2>\n";

			$this->js .= "$('.bootstrap_tooltip').tooltip();\n\n";

			if ($this->screen == 'all') {

				$this->html .= "<ul class='nav nav-tabs' role='tablist'>\n";
				$this->html .= "  <li role='presentation'" . (!@$tl->page['filters']['tab'] || @$tl->page['filters']['tab'] == 'blacklisted' ? " class='active'" : false) . "><a href='#blacklisted' aria-controls='blacklisted' role='tab' data-toggle='tab'>Blacklisted IPs" . (count($this->blacklisted) ? " (" . (count($this->blacklisted)) . ")" : false) . "</a></li>\n";
				$this->html .= "  <li role='presentation'" . (@$tl->page['filters']['tab'] == 'whitelisted' ? " class='active'" : false) . "><a href='#whitelisted' aria-controls='whitelisted' role='tab' data-toggle='tab'>Whitelisted IPs" . (count($this->whitelisted) ? " (" . (count($this->whitelisted)) . ")" : false) . "</a></li>\n";
				$this->html .= "  <li role='presentation'" . (@$tl->page['filters']['tab'] == 'suspicious' ? " class='active'" : false) . "><a href='#suspicious' aria-controls='suspicious' role='tab' data-toggle='tab'>Suspicious IPs" . (count($this->suspicious) ? " (" . (count($this->suspicious)) . ")" : false) . "</a></li>\n";
				$this->html .= "</ul>\n";

				$this->html .= "<div class='tab-content'>\n";
				$this->html .= "  <div role='tabpanel' class='tab-pane" . (!@$tl->page['filters']['tab'] || @$tl->page['filters']['tab'] == 'blacklisted' ? " active" : false) . "' id='blacklisted'><br />\n";

				// blacklisted
					if (!count($this->blacklisted)) $this->html .= "<p>None yet.</p>\n";
					else {

						$this->html .= "<table class='table table-hover table-condensed'>\n";
						$this->html .= "<thead>\n";
						$this->html .= "<tr>\n";
						$this->html .= "<th>IP</th>\n";
						$this->html .= "<th>Location</th>\n";
						$this->html .= "<th>Attempts</th>\n";
						$this->html .= "<th>Updated</th>\n";
						$this->html .= "<th></th>\n";
						$this->html .= "</tr>\n";
						$this->html .= "</thead>\n";
						$this->html .= "<tbody>\n";

						for ($counter = 0; $counter < count($this->blacklisted); $counter++) {

							$this->html .= "<tr>\n";
							// ip
								$this->html .= "<td>\n";
								$this->html .= "  <span class='bootstrap_tooltip' data-toggle='tooltip' data-placement='left' title=" . '"' . addSlashes($this->blacklisted[$counter]['notes']) .'"' . ">" . $this->blacklisted[$counter]['ip'] . "</span>\n";
								if ($this->blacklisted[$counter]['user_id']) {
									$user = "<span class='bootstrap_tooltip' data-toggle='tooltip' data-placement='top' title=" . '"' . addSlashes($this->blacklisted[$counter]['full_name']) .'"' . "><span class='glyphicon glyphicon-user'></span></span>";
									if ($this->profile_template) $user = "<a href='/" . $this->profile_template . "/" . $operators->firstTrue(@$this->blacklisted[$counter]['username'], @$this->blacklisted[$counter]['public_id'], @$this->blacklisted[$counter]['user_id']) . "'>" . $user . "</a>";
									$this->html .= "  &nbsp; " . $user . "\n";
								}
								$this->html .= "</td>\n";
							// location
								$city = $this->blacklisted[$counter]['city'];
								if (@$localization_manager->countries[$this->blacklisted[$counter]['country_id']]) $country = $localization_manager->countries[$this->blacklisted[$counter]['country_id']];
								else $country = "Unknown";
								$this->html .= "<td>" . trim($city . ", " . $country, ", ") . "</td>\n";
							// attempts
								$this->html .= "<td>" . number_format(floatval($this->blacklisted[$counter]['attempts'])) . "</td>\n";
							// date
								$this->html .= "<td>" . date('F j, Y', strtotime($this->blacklisted[$counter]['updated_on'])) . "</td>\n";
							// actions
								$this->html .= "<td class='right'>\n";
								$this->html .= "  <a href='/" . $tl->page['template'] . "/screen=edit_ip|id=" . $this->blacklisted[$counter]['ip_id'] . "' class='btn btn-default btn-sm'>Edit</a>\n";
								$this->html .= "  <a href='http://whatismyipaddress.com/ip/" . $this->blacklisted[$counter]['ip'] . "' target='_blank' class='btn btn-link btn-sm'>Whois</a>\n";
								$this->html .= "</td>\n";
							$this->html .= "</tr>\n";

						}

						$this->html .= "</tbody>\n";
						$this->html .= "</table>\n";

					}

				$this->html .= "  </div>\n";
				$this->html .= "  <div role='tabpanel' class='tab-pane" . (@$tl->page['filters']['tab'] == 'whitelisted' ? " active" : false) . "' id='whitelisted'><br />\n";

				// whitelisted
					if (!count($this->whitelisted)) $this->html .= "<p>None yet.</p>\n";
					else {

						$this->html .= "<table class='table table-hover table-condensed'>\n";
						$this->html .= "<thead>\n";
						$this->html .= "<tr>\n";
						$this->html .= "<th>IP</th>\n";
						$this->html .= "<th>Location</th>\n";
						$this->html .= "<th>Attempts</th>\n";
						$this->html .= "<th>Updated</th>\n";
						$this->html .= "<th></th>\n";
						$this->html .= "</tr>\n";
						$this->html .= "</thead>\n";
						$this->html .= "<tbody>\n";

						for ($counter = 0; $counter < count($this->whitelisted); $counter++) {

							$this->html .= "<tr>\n";
							// ip
								$this->html .= "<td>\n";
								$this->html .= "  <span class='bootstrap_tooltip' data-toggle='tooltip' data-placement='left' title=" . '"' . addSlashes($this->whitelisted[$counter]['notes']) .'"' . ">" . $this->whitelisted[$counter]['ip'] . "</span>\n";
								if ($this->whitelisted[$counter]['user_id']) {
									$user = "<span class='bootstrap_tooltip' data-toggle='tooltip' data-placement='top' title=" . '"' . addSlashes($this->whitelisted[$counter]['full_name']) .'"' . "><span class='glyphicon glyphicon-user'></span></span>";
									if ($this->profile_template) $user = "<a href='/" . $this->profile_template . "/" . $operators->firstTrue(@$this->whitelisted[$counter]['username'], @$this->whitelisted[$counter]['public_id'], @$this->whitelisted[$counter]['user_id']) . "'>" . $user . "</a>";
									$this->html .= "  &nbsp; " . $user . "\n";
								}
								$this->html .= "</td>\n";
							// location
								$city = $this->whitelisted[$counter]['city'];
								if (@$localization_manager->countries[$this->whitelisted[$counter]['country_id']]) $country = $localization_manager->countries[$this->whitelisted[$counter]['country_id']];
								else $country = "Unknown";
								$this->html .= "<td>" . trim($city . ", " . $country, ", ") . "</td>\n";
							// attempts
								$this->html .= "<td>" . number_format(floatval($this->whitelisted[$counter]['attempts'])) . "</td>\n";
							// date
								$this->html .= "<td>" . date('F j, Y', strtotime($this->whitelisted[$counter]['updated_on'])) . "</td>\n";
							// actions
								$this->html .= "<td class='right'>\n";
								$this->html .= "  <a href='/" . $tl->page['template'] . "/screen=edit_ip|id=" . $this->whitelisted[$counter]['ip_id'] . "' class='btn btn-default btn-sm'>Edit</a>\n";
								$this->html .= "  <a href='http://whatismyipaddress.com/ip/" . $this->whitelisted[$counter]['ip'] . "' target='_blank' class='btn btn-link btn-sm'>Whois</a>\n";
								$this->html .= "</td>\n";
							$this->html .= "</tr>\n";

						}

						$this->html .= "</tbody>\n";
						$this->html .= "</table>\n";

					}

				$this->html .= "  </div>\n";
				$this->html .= "  <div role='tabpanel' class='tab-pane" . (@$tl->page['filters']['tab'] == 'suspicious' ? " active" : false) . "' id='suspicious'><br />\n";

				// suspicious
					if (!count($this->suspicious)) $this->html .= "<p>None yet.</p>\n";
					else {

						$this->html .= "<table class='table table-hover table-condensed'>\n";
						$this->html .= "<thead>\n";
						$this->html .= "<tr>\n";
						$this->html .= "<th>IP</th>\n";
						$this->html .= "<th>Location</th>\n";
						$this->html .= "<th>Attempts</th>\n";
						$this->html .= "<th>Updated</th>\n";
						$this->html .= "<th></th>\n";
						$this->html .= "</tr>\n";
						$this->html .= "</thead>\n";
						$this->html .= "<tbody>\n";

						for ($counter = 0; $counter < count($this->suspicious); $counter++) {

							$this->html .= "<tr>\n";
							// ip
								$this->html .= "<td>\n";
								$this->html .= "  <span class='bootstrap_tooltip' data-toggle='tooltip' data-placement='left' title=" . '"' . addSlashes($this->suspicious[$counter]['notes']) .'"' . ">" . $this->suspicious[$counter]['ip'] . "</span>\n";
								if ($this->suspicious[$counter]['user_id']) {
									$user = "<span class='bootstrap_tooltip' data-toggle='tooltip' data-placement='top' title=" . '"' . addSlashes($this->suspicious[$counter]['full_name']) .'"' . "><span class='glyphicon glyphicon-user'></span></span>";
									if ($this->profile_template) $user = "<a href='/" . $this->profile_template . "/" . $operators->firstTrue(@$this->suspicious[$counter]['username'], @$this->suspicious[$counter]['public_id'], @$this->suspicious[$counter]['user_id']) . "'>" . $user . "</a>";
									$this->html .= "  &nbsp; " . $user . "\n";
								}
								$this->html .= "</td>\n";
							// location
								$city = $this->suspicious[$counter]['city'];
								if (@$localization_manager->countries[$this->suspicious[$counter]['country_id']]) $country = $localization_manager->countries[$this->suspicious[$counter]['country_id']];
								else $country = "Unknown";
								$this->html .= "<td>" . trim($city . ", " . $country, ", ") . "</td>\n";
							// attempts
								$this->html .= "<td>" . number_format(floatval($this->suspicious[$counter]['attempts'])) . "</td>\n";
							// date
								$this->html .= "<td>" . date('F j, Y', strtotime($this->suspicious[$counter]['updated_on'])) . "</td>\n";
							// actions
								$this->html .= "<td class='right'>\n";
								$this->html .= "  <a href='/" . $tl->page['template'] . "/screen=edit_ip|id=" . $this->suspicious[$counter]['ip_id'] . "' class='btn btn-default btn-sm'>Edit</a>\n";
								$this->html .= "  <a href='http://whatismyipaddress.com/ip/" . $this->suspicious[$counter]['ip'] . "' target='_blank' class='btn btn-link btn-sm'>Whois</a>\n";
								$this->html .= "</td>\n";
							$this->html .= "</tr>\n";

						}

						$this->html .= "</tbody>\n";
						$this->html .= "</table>\n";

					}

				$this->html .= "  </div>\n";
				$this->html .= "</div>\n";

				$this->html .= $form->input('button', 'add_button', null, false, 'Add', 'btn btn-info', '', '', '', '', array('onClick'=>'document.location.href="/' . $tl->page['template'] . '/screen=add_ip"; return false;')) . "\n";

			}
			elseif ($this->screen == 'edit_ip' || $this->screen == 'add_ip') {

				// form
					$this->html .= $form->start('updateIpForm', '', 'post', null, null, ['onSubmit'=>'validateIpForm(); return false;']) . "\n";
					$this->html .= $form->input('hidden', 'screen', $this->screen) . "\n";
					$this->html .= $form->input('hidden', 'forgetRequested') . "\n";

					// IP
						if ($this->screen == 'add_ip') $this->html .= $form->row('text', 'ip', $operators->firstTrue(@$_POST['ip'], $this->editIP[0]['ip']), true, "IP", 'form-control', null, 50);
						else $this->html .= $form->row('uneditable_static', 'ip', "<a href='http://whatismyipaddress.com/ip/" . $this->editIP[0]['ip'] . "'>" . $this->editIP[0]['ip'] . "</a>", false, "IP");
					// User
						if ($this->screen == 'edit_ip' && $this->editIP[0]['full_name']) {
							$user = $this->editIP[0]['full_name'];
							if ($this->profile_template) $user = "<a href='/" . $this->profile_template . "/" . $operators->firstTrue(@$this->editIP[0]['username'], @$this->editIP[0]['public_id'], @$this->editIP[0]['user_id']) . "'>" . $user . "</a>";
							$this->html .= $form->row('uneditable_static', 'ip', $user, false, "User");
						}
					// Status
						$this->html .= $form->row('select', 'status', $operators->firstTrue(@$_POST['status'], $this->editIP[0]['status']), true, "Status", 'form-control', $this->statuses);
					// Notes
						$this->html .= $form->row('text', 'notes', $operators->firstTrue(@$_POST['notes'], $this->editIP[0]['notes']), false, "Notes", 'form-control', null, 255);
					// Actions
						$this->html .= $form->rowStart('actions');
						$this->html .= "  <div class='row'>\n";
						$this->html .= "    <div class='col-lg-9 col-md-9 col-sm-9 col-xs-9'>\n";
						$this->html .= "      " . $form->input('submit', 'submit_button', null, true, "Save", 'btn btn-info') . "\n";
						$this->html .= "      " . $form->input('cancel_and_return', 'cancel_button', null, true, "Cancel", 'btn btn-link') . "\n";
						$this->html .= "    </div>\n";
						$this->html .= "    <div class='col-lg-3 col-md-3 col-sm-3 col-xs-3 text-right'>\n";
						if ($this->screen == 'edit_ip') $this->html .= "      " . $form->input('button', 'forget_button', null, true, "Forget", 'btn btn-danger', null, null, null, null, ['onClick'=>'forgetThis(); return false;']) . "\n";
						$this->html .= "    </div>\n";
						$this->html .= "  </div>\n";
						$this->html .= $form->rowEnd();

					$this->html .= $form->end() . "\n";

				// form validation
					$this->js .= "var errorMessage = '';\n\n";

					$this->js .= "function validateIpForm() {\n\n";

					$this->js .= "  errorMessage = '';\n\n";

					$this->js .= "  if (document.updateIpForm.screen.value == 'add_ip' && !document.updateIpForm.ip.value) errorMessage += 'Please specify an IP number. ';\n";
					$this->js .= "    if (errorMessage) alert(errorMessage);\n";
					$this->js .= "    else document.updateIpForm.submit();\n";
					$this->js .= "  }\n\n";

					$this->js .= "function forgetThis() {\n";
					$this->js .= "  areYouSure = confirm('Are you sure?');\n";
					$this->js .= "  if (areYouSure) {\n";
					$this->js .= "    document.updateIpForm.forgetRequested.value = 'Y';\n";
					$this->js .= "    document.updateIpForm.submit();\n";
					$this->js .= "  }\n";
					$this->js .= "}\n\n";

			}

		}

	}

?>
