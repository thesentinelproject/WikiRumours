<?php

	class cms_widget_TL {

		public $html = null;
		public $js = null;
		public $file_path = 'uploads/cms_files';

		private $screen = null;
		private $tab = null;
		private $cms_id = null;
		private $content_type = null;

		public $cms_index = null;
		public $cms = null;
		public $domain_aliases = null;
		public $http_statuses = null;
		public $file_metadata = null;

		public $content_types = array(
			'p' => 'page',
			'b' => 'content block',
			'e' => 'email',
			'c' => 'confirmation',
			'f' => 'file',
			'r' => 'redirect',
			'a' => 'alias',
			'd' => 'domain alias'
		);

		public $confirmation_types = array(
			's' => 'success',
			'w' => 'warning',
			'e' => 'error'
		);

		public function initialize() {

			global $tl;
			global $tablePrefix;

			$file_manager = new file_manager_TL();
			$authentication_manager = new authentication_manager_TL();

			// parse query string
				$this->cms_id = floatval(@$tl->page['filters']['cms_id']);
				$this->content_type = @$tl->page['filters']['content_type'];
				$this->tab = (@$tl->page['filters']['tab'] ? $tl->page['filters']['tab'] : "page");
				$this->filePath = trim($this->filePath, ' /');

				if (@$tl->page['filters']['screen'] == 'edit_content' && $this->cms_id) $this->screen = 'edit_content'; 
				elseif (@$tl->page['filters']['screen'] == 'add_content') $this->screen = 'add_content'; 
				else $this->screen = 'index';

			// query database
				if ($this->screen == 'index') {

					$this->cms_index = array();
					foreach ($this->content_types as $key=>$value) {
						$this->cms_index[$key] = $this->retrieveContent([$tablePrefix . 'cms.content_type'=>$key], null, $tablePrefix . 'cms.title ASC, ' . $tablePrefix . 'cms.public_id ASC, ' . $tablePrefix . 'cms.source_url ASC, ' . $tablePrefix . 'cms.destination_url ASC');
					}

				}
				elseif ($this->screen == 'edit_content') {

					$this->cms = $this->retrieveContent([$tablePrefix . 'cms.cms_id'=>$this->cms_id]);

					if (!count($this->cms)) {
						$authentication_manager->forceRedirect('/' . $tl->page['template']);
					}

					$this->content_type = $this->cms[0]['content_type'];

					$this->domain_aliases = array();
					$result = $this->retrieveContent([$tablePrefix . 'cms.content_type'=>'d']);
					for ($counter = 0; $counter < count($result); $counter++) {
						$this->domain_aliases[$result[$counter]['cms_id']] = $result[$counter]['title'];
					}

					if ($this->cms[0]['content_type'] == 'f') {
						if (!file_exists(__DIR__ . '/../../../web_root/' . $this->cms[0]['destination_url'])) $tl->page['error'] .= "Unable to locate the file " . $this->cms[0]['destination_url'] . ". ";
						else $this->file_metadata = $file_manager->extractFileMetadata(__DIR__ . '/../../../web_root/' . $this->cms[0]['destination_url']);
					}
					elseif ($this->cms[0]['content_type'] == 'r') {
						$this->http_statuses = array();
						$result = $this->retrieveHttpStatuses();
						for ($counter = 0; $counter < count($result); $counter++) {
							$this->http_statuses[$result[$counter]['code_id']] = $result[$counter]['status'];
						}
					}
					elseif ($this->cms[0]['content_type'] == 'd') {
						if (!file_exists(__DIR__ . '/../../../web_root/' . $this->cms[0]['destination_url'])) $tl->page['error'] .= "Unable to locate the logo " . $this->cms[0]['destination_url'] . ". ";
					}

				}
				elseif ($this->screen == 'add_content') {

					$this->domain_aliases = array();
					$result = $this->retrieveContent([$tablePrefix . 'cms.content_type'=>'d']);
					for ($counter = 0; $counter < count($result); $counter++) {
						$this->domain_aliases[$result[$counter]['cms_id']] = $result[$counter]['title'];
					}

					if ($this->content_type == 'r') {
						$this->http_statuses = array();
						$result = $this->retrieveHttpStatuses();
						for ($counter = 0; $counter < count($result); $counter++) {
							$this->http_statuses[$result[$counter]['code_id']] = $result[$counter]['status'];
						}
					}

				}

			// call controllers
				if (count($_POST)) $this->controllers();

			// call views
				$this->createView();

		}

		public function retrieveContent($matching = null, $otherCriteria = null, $sort = null, $mustMatchCurrentLanguage = false, $mustMatchCurrentDomainAlias = false) {

			global $dbConnection;
			global $tablePrefix;

			$parser = new parser_TL();
			
			$query = "SELECT " . $tablePrefix . "cms.cms_id,";
			$query .= " " . $tablePrefix . "cms.public_id,";
			$query .= " " . $tablePrefix . "cms.title,";
			$query .= " " . $tablePrefix . "cms.content,";
			$query .= " " . $tablePrefix . "cms.content_plain,";
			$query .= " " . $tablePrefix . "cms.content_js,";
			$query .= " " . $tablePrefix . "cms.content_css,";
			$query .= " " . $tablePrefix . "cms.source_url,";
			$query .= " " . $tablePrefix . "cms.source_url_subdomain,";
			$query .= " " . $tablePrefix . "cms.destination_url,";
			$query .= " " . $tablePrefix . "cms.http_status,";
			$query .= " " . $tablePrefix . "http_statuses.status as http_status_label,";
			$query .= " " . $tablePrefix . "cms.confirmation_type,";
			$query .= " " . $tablePrefix . "cms.is_login_required,";
			$query .= " " . $tablePrefix . "cms.content_type,";
			$query .= " " . $tablePrefix . "cms.language_id,";
			$query .= " " . $tablePrefix . "languages.language as language,";
			$query .= " " . $tablePrefix . "languages.native as native_language,";
			$query .= " " . $tablePrefix . "cms.country_id,";
			$query .= " " . $tablePrefix . "countries.country,";
			$query .= " " . $tablePrefix . "cms.latitude,";
			$query .= " " . $tablePrefix . "cms.longitude,";
			$query .= " " . $tablePrefix . "cms.domain_alias_id,";
			$query .= " " . $tablePrefix . "domain_aliases.title as domain_alias_title,";
			$query .= " " . $tablePrefix . "cms.google_analytics_id,";
			$query .= " " . $tablePrefix . "cms.deletable,";
			$query .= " " . $tablePrefix . "cms.updated_on,";
			$query .= " " . $tablePrefix . "cms.updated_by,";
			$query .= " " . $tablePrefix . "users.first_name,";
			$query .= " " . $tablePrefix . "users.last_name,";
			$query .= " TRIM(CONCAT(" . $tablePrefix . "users.first_name, ' ', " . $tablePrefix . "users.last_name)) as full_name";
			$query .= " FROM " . $tablePrefix . "cms";
			$query .= " LEFT JOIN " . $tablePrefix . "http_statuses ON " . $tablePrefix . "cms.http_status = " . $tablePrefix . "http_statuses.code_id";
			$query .= " LEFT JOIN " . $tablePrefix . "languages ON " . $tablePrefix . "cms.language_id = " . $tablePrefix . "languages.language_id";
			$query .= " LEFT JOIN " . $tablePrefix . "countries ON " . $tablePrefix . "cms.country_id = " . $tablePrefix . "countries.country_id";
			$query .= " LEFT JOIN " . $tablePrefix . "users ON " . $tablePrefix . "cms.updated_by = " . $tablePrefix . "users.user_id";
			$query .= " LEFT JOIN " . $tablePrefix . "cms as " . $tablePrefix . "domain_aliases ON " . $tablePrefix . "cms.domain_alias_id = " . $tablePrefix . "domain_aliases.cms_id";
			$query .= " WHERE 1=1";
			if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
			if ($otherCriteria) $query .= " AND (" . $otherCriteria . ")";
			if ($sort) $query .= " ORDER BY " . addSlashes($sort);

			$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . ': ' . $dbConnection->error . '<br /><br />' . $query);

			$items = $parser->mySqliResourceToArray($result);

			$result->free();

			return $items;

		}

		public function retrieveHttpStatuses($matching = null, $sort = "status ASC") {

			global $dbConnection;
			global $tablePrefix;

			$parser = new parser_TL();
			
			$query = "SELECT " . $tablePrefix . "http_statuses.code_id,";
			$query .= " " . $tablePrefix . "http_statuses.status";
			$query .= " FROM " . $tablePrefix . "http_statuses";
			$query .= " WHERE 1=1";
			if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
			if ($sort) $query .= " ORDER BY " . addSlashes($sort);

			$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . ': ' . $dbConnection->error . '<br /><br />' . $query);

			$items = $parser->mySqliResourceToArray($result);
			
			$result->free();

			return $items;

		}

		private function controllers() {

			global $tl;
			global $logged_in;
			global $tablePrefix;

			$authentication_manager = new authentication_manager_TL();
			$input_validator = new input_validator_TL();
			$logger = new logger_TL();
			$operators = new operators_TL();
			$directory_manager = new directory_manager_TL();
			$parser = new parser_TL();

			if ($_POST['formName'] == 'addEditContentForm' && @$this->cms_id && @$this->content_type && $_POST['deleteContent'] == 'Y') {

				// delete file, if required
					if ($this->content_type == 'f' || $this->content_type == 'd') {
						$success = $directory_manager->remove(__DIR__ . '/../../../web_root/' . str_replace(basename($this->cms[0]['destination_url']), '', $this->cms[0]['destination_url']));
						if (!$success) $tl->page['error'] .= "Unable to delete associated file(s) for some reason. ";
					}

					if (!$tl->page['error']) {

						// delete from DB
							deleteFromDbSingle('cms', ['cms_id'=>$this->cms_id]);

						// update log
							$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has deleted the CMS " . $this->content_types[$this->content_type] . " &quot;" . $operators->firstTrue($this->cms[0]['title'], $this->cms[0]['public_id'], $this->cms[0]['source_url'], $this->cms[0]['destination_url']) . "&quot;";
							$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'cms_id=' . $this->cms_id));
							
						// redirect
							$authentication_manager->forceRedirect('/' . $tl->page['template'] . '/' . urlencode('tab=' . str_replace(' ', '_', $this->content_types[$this->content_type]) . '|success=content_deleted'));

					}

			}
			elseif ($_POST['formName'] == 'addEditContentForm') {
				// clean input
					$_POST = $parser->trimAll($_POST);
					$parser->checkboxesToBinary(array('is_login_required'));
					if (@$_POST['source_url_subdomain'] == 'www') $_POST['source_url_subdomain'] = '';
					
				// check for errors
					if ($this->content_type == 'p') {
						if (!$_POST['title']) $tl->page['error'] .= "Please specify a title for this page. ";
						if (!$_POST['destination_url']) $tl->page['error'] .= "Please specify a URL for this page. ";
						else {
							$exists = $this->retrieveContent([$tablePrefix . 'cms.destination_url'=>$_POST['destination_url'], $tablePrefix . 'cms.language_id'=>$_POST['language_id'], $tablePrefix . 'cms.domain_alias_id'=>$_POST['domain_alias_id']], $tablePrefix . "cms.cms_id != '" . $this->cms_id . "'");
							if (count($exists)) $tl->page['error'] .= "The page URL you've specified already exists. ";
						}
						if (!$_POST['content']) $tl->page['error'] .= "Please provide some content for this page. ";
					}
					elseif ($this->content_type == 'b') {
						if (!$_POST['public_id']) $tl->page['error'] .= "Please specify a unique descriptor for this content block. ";
						else {
							$exists = $this->retrieveContent([$tablePrefix . 'cms.public_id'=>$_POST['public_id'], $tablePrefix . 'cms.language_id'=>$_POST['language_id'], $tablePrefix . 'cms.domain_alias_id'=>$_POST['domain_alias_id']], $tablePrefix . "cms.cms_id != '" . $this->cms_id . "'");
							if (count($exists)) $tl->page['error'] .= "The unique descriptor you've specified already exists. ";
						}
						if (!$_POST['content']) $tl->page['error'] .= "Please provide some content for this content block. ";
					}
					elseif ($this->content_type == 'e') {
						if (!$_POST['public_id']) $tl->page['error'] .= "Please specify a subject for this email. ";
						if (!$_POST['content']) $tl->page['error'] .= "Please provide some HTML content for this email. ";
						if (!$_POST['content_plain']) $tl->page['error'] .= "Please provide some text content for this email. ";
					}
					elseif ($this->content_type == 'c') {
						if (!$_POST['type']) $tl->page['error'] .= "Please specify the type of confirmation. ";
						if (!$_POST['public_id']) $tl->page['error'] .= "Please specify a slug. ";
						else {
							$exists = $this->retrieveContent([$tablePrefix . 'cms.public_id'=>$_POST['public_id'], $tablePrefix . 'cms.confirmation_type'=>$_POST['confirmation_type'], $tablePrefix . 'cms.language_id'=>$_POST['language_id'], $tablePrefix . 'cms.domain_alias_id'=>$_POST['domain_alias_id']], $tablePrefix . "cms.cms_id != '" . $this->cms_id . "'");
							if (count($exists)) $tl->page['error'] .= "The slug you've specified already exists. ";
						}
						if (!$_POST['content']) $tl->page['error'] .= "Please provide some content for this confirmation. ";
					}
					elseif ($this->content_type == 'f') {
						if (!$_POST['title']) $tl->page['error'] .= "Please specify a title for this file. ";
					}
					elseif ($this->content_type == 'r') {
						if (!$_POST['source_url']) $tl->page['error'] .= "Please specify the page you wish to redirect. ";
						else {
							$exists = $this->retrieveContent([$tablePrefix . 'cms.source_url'=>$_POST['source_url'], $tablePrefix . 'cms.language_id'=>$_POST['language_id'], $tablePrefix . 'cms.domain_alias_id'=>$_POST['domain_alias_id']], $tablePrefix . "cms.cms_id != '" . $this->cms_id . "'");
							if (count($exists)) $tl->page['error'] .= "The URL you're attempting to redirect already exists. ";
						}
						if (!$_POST['destination_url']) $tl->page['error'] .= "Please specify where you wish to redirect to. ";
					}
					elseif ($this->content_type == 'a') {
						if (!$_POST['source_url']) $tl->page['error'] .= "Please specify the page you wish to alias. ";
						else {
							$exists = $this->retrieveContent([$tablePrefix . 'cms.source_url'=>$_POST['source_url'], $tablePrefix . 'cms.language_id'=>$_POST['language_id'], $tablePrefix . 'cms.domain_alias_id'=>$_POST['domain_alias_id']], $tablePrefix . "cms.cms_id != '" . $this->cms_id . "'");
							if (count($exists)) $tl->page['error'] .= "The URL you're attempting to alias already exists. ";
						}
						if (!$_POST['destination_url']) $tl->page['error'] .= "Please specify where you wish to alias to. ";
					}
					elseif ($this->content_type == 'd') {
						if (!$_POST['title']) $tl->page['error'] .= "Please specify a title for this domain alias. ";
						if (!$_POST['source_url']) $tl->page['error'] .= "Please specify the URL of this domain alias. ";
						else {
							$exists = $this->retrieveContent([$tablePrefix . 'cms.source_url'=>$_POST['source_url'], $tablePrefix . 'cms.language_id'=>$_POST['language_id'], $tablePrefix . 'cms.domain_alias_id'=>$_POST['domain_alias_id']], $tablePrefix . "cms.cms_id != '" . $this->cms_id . "'");
							if (count($exists)) $tl->page['error'] .= "The domain you're attempting to alias already exists. ";
						}
					}

					if (!$tl->page['error']) {

						// save file(s)
							$timestamp = date('Y-m-d H:i:s');
							if (@end(@$_POST['file_cms_upload'])) {
								$new_path = __DIR__ . '/../../../web_root/' . $this->file_path . '/' . date('YmdHis', strtotime($timestamp));
								$success = @mkdir($new_path);
								if (!$success || !file_exists($new_path)) $tl->page['error'] .= "Unable to create subdirectory for this upload. ";
								else {
									$new_filename = basename(end($_POST['file_cms_upload']));
									$success = rename(__DIR__ . '/../../../' . end($_POST['file_cms_upload']), $new_path . '/' . $new_filename);
									if (!$success || !file_exists($new_path . '/' . $new_filename)) $tl->page['error'] .= "Unable to save uploaded file for some reason. ";
									else $_POST['destination_url'] = $this->file_path . '/' . date('YmdHis', strtotime($timestamp)) . '/' . $new_filename;
								}
							}

							if (($this->content_type == 'f' || $this->content_type == 'd') && !$_POST['destination_url']) $_POST['destination_url'] = $this->cms[0]['destination_url'];


						// update DB
							if ($this->screen == 'add_content') $this->cms_id = insertIntoDb('cms', ['content_type'=>$this->content_type]);

							updateDbSingle('cms', [
								'public_id'=>@$_POST['public_id'],
								'title'=>@$_POST['title'],
								'content'=>@$_POST['content'],
								'content_plain'=>@$_POST['content_plain'],
								'content_js'=>@$_POST['content_js'],
								'content_css'=>@$_POST['content_css'],
								'source_url'=>@$_POST['source_url'],
								'source_url_subdomain'=>@$_POST['source_url_subdomain'],
								'destination_url'=>@$_POST['destination_url'],
								'http_status'=>@$_POST['http_status'],
								'confirmation_type'=>@$_POST['confirmation_type'],
								'is_login_required'=>@$_POST['is_login_required'],
								'language_id'=>@$_POST['language_id'],
								'country_id'=>@$_POST['country_id'],
								'latitude'=>@$_POST['map_center_latitude'],
								'longitude'=>@$_POST['map_center_longitude'],
								'domain_alias_id'=>@$_POST['domain_alias_id'],
								'google_analytics_id'=>@$_POST['google_analytics_id'],
								'updated_on'=>$timestamp,
								'updated_by'=>$logged_in['user_id']
							], ['cms_id'=>$this->cms_id]);

						// update log
							$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has " . ($this->screen == 'add_content' ? "added" : "updated") . " the CMS " . $content_types[$this->content_type] . " &quot;" . $operators->firstTrue(@$_POST['title'], @$_POST['public_id'], @$_POST['source_url'], @$_POST['destination_url']) . "&quot;";
							$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'cms_id=' . $this->cms_id));

						// redirect
							$authentication_manager->forceRedirect('/' . $tl->page['template'] . '/' . urlencode('tab=' . str_replace(' ', '_', $this->content_types[$this->content_type]) . '|success=content_' . ($this->screen == 'add_content' ? "added" : "updated")));
					}

			}

		}

		private function createView() {

			global $tl;
			global $logged_in;

			$form = new form_TL();
			$file_manager = new file_manager_TL();

			if ($this->screen == 'index') {

				// title
					$this->html .= "<h2>Content Management</h2>\n";

				// nav
					$this->html .= "<ul class='nav nav-tabs' role='tablist'>\n";
					foreach ($this->content_types as $key=>$value) {
						$this->html .= "  <li role='presentation'" . (str_replace('_', ' ', $this->tab) == $value ? " class='active'" : false) . "><a href='#" . str_replace(' ', '_', $value) . "' aria-controls='" . str_replace(' ', '_', $value) . "' role='tab' data-toggle='tab'>" . ucwords($value) . (count($this->content[$key]) ? " (" . count($this->content[$key]) . ")" : false) . "</a></li>\n";
					}
					$this->html .= "</ul>\n";

				// content
					$this->html .= "<br />\n";
					$this->html .= "<div class='tab-content'>\n";

					// page
						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab == 'page' ? " active" : false) . "' id='page'>\n";

						if (!count($this->cms_index['p'])) $this->html .= "<p>None yet</p>\n";
						else {

							$this->html .= "    <table class='table table-condensed'>\n";
							$this->html .= "    <thead>\n";
							$this->html .= "    <tr>\n";
							$this->html .= "    <th>Title</th>\n";
							$this->html .= "    <th>URI</th>\n";
							$this->html .= "    <th>Login</th>\n";
							$this->html .= "    <th>Language</th>\n";
							$this->html .= "    <th></th>\n";
							$this->html .= "    </tr>\n";
							$this->html .= "    </thead>\n";
							$this->html .= "    <tbody>\n";

							for ($counter = 0; $counter < count($this->cms_index['p']); $counter++) {
								$url = "/" . $tl->page['template'] . "/" . urlencode("screen=edit_content|content_type=p|cms_id=" .  $this->cms_index['p'][$counter]['cms_id']);
								$this->html .= "    <tr>\n";
								$this->html .= "    <td><a href='" . $url . "'>" . $this->cms_index['p'][$counter]['title'] . "</a></td>\n";
								$this->html .= "    <td><a href='/" . $this->cms_index['p'][$counter]['destination_url'] . "'>/" . $this->cms_index['p'][$counter]['destination_url'] . "</a></td>\n";
								$this->html .= "    <td>" . ($this->cms_index['p'][$counter]['is_login_required'] ? "Yes" : "No") . "</td>\n";
								$this->html .= "    <td>" . $this->cms_index['p'][$counter]['language'] . "</td>\n";
								$this->html .= "    <td class='text-right'><a href='" . $url . "' class='btn btn-default btn-sm'>Edit</a></td>\n";
								$this->html .= "    </tr>\n";
							}

							$this->html .= "    </tbody>\n";
							$this->html .= "    </table>\n";

						}

						$this->html .= "    <p><a href='/" . $tl->page['template'] . "/" . urlencode("screen=add_content|content_type=p") . "' class='btn btn-primary'>Add Page</a></p>\n";

						$this->html .= "  </div>\n";

					// content block
						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab == 'content_block' ? " active" : false) . "' id='content_block'>\n";

						if (!count($this->cms_index['b'])) $this->html .= "<p>None yet</p>\n";
						else {

							$this->html .= "    <table class='table table-condensed'>\n";
							$this->html .= "    <thead>\n";
							$this->html .= "    <tr>\n";
							$this->html .= "    <th>Unique Descriptor</th>\n";
							$this->html .= "    <th>Language</th>\n";
							$this->html .= "    <th></th>\n";
							$this->html .= "    </tr>\n";
							$this->html .= "    </thead>\n";
							$this->html .= "    <tbody>\n";

							for ($counter = 0; $counter < count($this->cms_index['b']); $counter++) {
								$url = "/" . $tl->page['template'] . "/" . urlencode("screen=edit_content|content_type=b|cms_id=" .  $this->cms_index['b'][$counter]['cms_id']);
								$this->html .= "    <tr>\n";
								$this->html .= "    <td><a href='" . $url . "'>" . $this->cms_index['b'][$counter]['public_id'] . "</a></td>\n";
								$this->html .= "    <td>" . $this->cms_index['b'][$counter]['language'] . "</td>\n";
								$this->html .= "    <td class='text-right'><a href='" . $url . "' class='btn btn-default btn-sm'>Edit</a></td>\n";
								$this->html .= "    </tr>\n";
							}

							$this->html .= "    </tbody>\n";
							$this->html .= "    </table>\n";

						}

						$this->html .= "    <p><a href='/" . $tl->page['template'] . "/" . urlencode("screen=add_content|content_type=b") . "' class='btn btn-primary'>Add Content Block</a></p>\n";

						$this->html .= "  </div>\n";

					// email
						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab == 'email' ? " active" : false) . "' id='email'>\n";

						if (!count($this->cms_index['e'])) $this->html .= "<p>None yet</p>\n";
						else {

							$this->html .= "    <table class='table table-condensed'>\n";
							$this->html .= "    <thead>\n";
							$this->html .= "    <tr>\n";
							$this->html .= "    <th>Subject</th>\n";
							$this->html .= "    <th>Language</th>\n";
							$this->html .= "    <th></th>\n";
							$this->html .= "    </tr>\n";
							$this->html .= "    </thead>\n";
							$this->html .= "    <tbody>\n";

							for ($counter = 0; $counter < count($this->cms_index['e']); $counter++) {
								$url = "/" . $tl->page['template'] . "/" . urlencode("screen=edit_content|content_type=e|cms_id=" .  $this->cms_index['e'][$counter]['cms_id']);
								$this->html .= "    <tr>\n";
								$this->html .= "    <td><a href='" . $url . "'>" . $this->cms_index['e'][$counter]['public_id'] . "</a></td>\n";
								$this->html .= "    <td>" . $this->cms_index['e'][$counter]['language'] . "</td>\n";
								$this->html .= "    <td class='text-right'><a href='" . $url . "' class='btn btn-default btn-sm'>Edit</a></td>\n";
								$this->html .= "    </tr>\n";
							}

							$this->html .= "    </tbody>\n";
							$this->html .= "    </table>\n";

						}

						$this->html .= "    <p><a href='/" . $tl->page['template'] . "/" . urlencode("screen=add_content|content_type=e") . "' class='btn btn-primary'>Add Email</a></p>\n";

						$this->html .= "  </div>\n";

					// confirmation
						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab == 'confirmation' ? " active" : false) . "' id='confirmation'>\n";

						if (!count($this->cms_index['c'])) $this->html .= "<p>None yet</p>\n";
						else {

							$this->html .= "    <table class='table table-condensed'>\n";
							$this->html .= "    <thead>\n";
							$this->html .= "    <tr>\n";
							$this->html .= "    <th>URI Slug</th>\n";
							$this->html .= "    <th>Type</th>\n";
							$this->html .= "    <th>Language</th>\n";
							$this->html .= "    <th></th>\n";
							$this->html .= "    </tr>\n";
							$this->html .= "    </thead>\n";
							$this->html .= "    <tbody>\n";

							for ($counter = 0; $counter < count($this->cms_index['c']); $counter++) {
								$url = "/" . $tl->page['template'] . "/" . urlencode("screen=edit_content|content_type=c|cms_id=" .  $this->cms_index['c'][$counter]['cms_id']);
								$this->html .= "    <tr>\n";
								$this->html .= "    <td><a href='" . $url . "'>" . $this->cms_index['c'][$counter]['public_id'] . "</a></td>\n";
								$this->html .= "    <td>" . @$this->confirmation_types[$this->cms_index['c'][$counter]['confirmation_type']] . "</td>\n";
								$this->html .= "    <td>" . $this->cms_index['c'][$counter]['language'] . "</td>\n";
								$this->html .= "    <td class='text-right'><a href='" . $url . "' class='btn btn-default btn-sm'>Edit</a></td>\n";
								$this->html .= "    </tr>\n";
							}

							$this->html .= "    </tbody>\n";
							$this->html .= "    </table>\n";

						}

						$this->html .= "    <p><a href='/" . $tl->page['template'] . "/" . urlencode("screen=add_content|content_type=c") . "' class='btn btn-primary'>Add Confirmation</a></p>\n";

						$this->html .= "  </div>\n";

					// file
						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab == 'file' ? " active" : false) . "' id='file'>\n";

						if (!count($this->cms_index['f'])) $this->html .= "<p>None yet</p>\n";
						else {

							$this->html .= "    <table class='table table-condensed'>\n";
							$this->html .= "    <thead>\n";
							$this->html .= "    <tr>\n";
							$this->html .= "    <th>Title</th>\n";
							$this->html .= "    <th>Filename</th>\n";
							$this->html .= "    <th></th>\n";
							$this->html .= "    </tr>\n";
							$this->html .= "    </thead>\n";
							$this->html .= "    <tbody>\n";

							for ($counter = 0; $counter < count($this->cms_index['f']); $counter++) {
								$editUrl = "/" . $tl->page['template'] . "/" . urlencode("screen=edit_content|content_type=f|cms_id=" .  $this->cms_index['f'][$counter]['cms_id']);
								$fileUrl = "/" . $this->cms_index['f'][$counter]['destination_url'];
								$this->html .= "    <tr>\n";
								$this->html .= "    <td><a href='" . $editUrl . "'>" . $this->cms_index['f'][$counter]['title'] . "</a></td>\n";
								$this->html .= "    <td><a href='" . $fileUrl . "'>" . $fileUrl . "</a></td>\n";
								$this->html .= "    <td class='text-right'><a href='" . $editUrl . "' class='btn btn-default btn-sm'>Edit</a></td>\n";
								$this->html .= "    </tr>\n";
							}

							$this->html .= "    </tbody>\n";
							$this->html .= "    </table>\n";

						}

						$this->html .= "    <p><a href='/" . $tl->page['template'] . "/" . urlencode("screen=add_content|content_type=f") . "' class='btn btn-primary'>Upload File</a></p>\n";

						$this->html .= "  </div>\n";

					// redirect
						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab == 'redirect' ? " active" : false) . "' id='redirect'>\n";

						if (!count($this->cms_index['r'])) $this->html .= "<p>None yet</p>\n";
						else {

							$this->html .= "    <table class='table table-condensed'>\n";
							$this->html .= "    <thead>\n";
							$this->html .= "    <tr>\n";
							$this->html .= "    <th>Type</th>\n";
							$this->html .= "    <th>Source</th>\n";
							$this->html .= "    <th>Destination</th>\n";
							$this->html .= "    <th></th>\n";
							$this->html .= "    </tr>\n";
							$this->html .= "    </thead>\n";
							$this->html .= "    <tbody>\n";

							for ($counter = 0; $counter < count($this->cms_index['r']); $counter++) {
								$url = "/" . $tl->page['template'] . "/" . urlencode("screen=edit_content|content_type=r|cms_id=" .  $this->cms_index['r'][$counter]['cms_id']);
								$this->html .= "    <tr>\n";
								$this->html .= "    <td><a href='" . $url . "'>" . $this->cms_index['r'][$counter]['http_status'] . "</a></td>\n";
								$this->html .= "    <td>/" . $this->cms_index['r'][$counter]['source_url'] . "</td>\n";
								$this->html .= "    <td>" . $this->cms_index['r'][$counter]['destination_url'] . "</td>\n";
								$this->html .= "    <td class='text-right'><a href='" . $url . "' class='btn btn-default btn-sm'>Edit</a></td>\n";
								$this->html .= "    </tr>\n";
							}

							$this->html .= "    </tbody>\n";
							$this->html .= "    </table>\n";

						}

						$this->html .= "    <p><a href='/" . $tl->page['template'] . "/" . urlencode("screen=add_content|content_type=r") . "' class='btn btn-primary'>Add Redirect</a></p>\n";

						$this->html .= "  </div>\n";

					// alias
						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab == 'alias' ? " active" : false) . "' id='alias'>\n";

						if (!count($this->cms_index['a'])) $this->html .= "<p>None yet</p>\n";
						else {

							$this->html .= "    <table class='table table-condensed'>\n";
							$this->html .= "    <thead>\n";
							$this->html .= "    <tr>\n";
							$this->html .= "    <th>Source</th>\n";
							$this->html .= "    <th>Destination</th>\n";
							$this->html .= "    <th></th>\n";
							$this->html .= "    </tr>\n";
							$this->html .= "    </thead>\n";
							$this->html .= "    <tbody>\n";

							for ($counter = 0; $counter < count($this->cms_index['a']); $counter++) {
								$url = "/" . $tl->page['template'] . "/" . urlencode("screen=edit_content|content_type=a|cms_id=" .  $this->cms_index['a'][$counter]['cms_id']);
								$this->html .= "    <tr>\n";
								$this->html .= "    <td><a href='" . $url . "'>" . $this->cms_index['a'][$counter]['source_url'] . "</a></td>\n";
								$this->html .= "    <td>" . $this->cms_index['a'][$counter]['destination_url'] . "</td>\n";
								$this->html .= "    <td class='text-right'><a href='" . $url . "' class='btn btn-default btn-sm'>Edit</a></td>\n";
								$this->html .= "    </tr>\n";
							}

							$this->html .= "    </tbody>\n";
							$this->html .= "    </table>\n";

						}

						$this->html .= "    <p><a href='/" . $tl->page['template'] . "/" . urlencode("screen=add_content|content_type=a") . "' class='btn btn-primary'>Add Alias</a></p>\n";

						$this->html .= "  </div>\n";

					// domain alias
						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab == 'domain_alias' ? " active" : false) . "' id='domain_alias'>\n";

						if (!count($this->cms_index['d'])) $this->html .= "<p>None yet</p>\n";
						else {

							$this->html .= "    <table class='table table-condensed'>\n";
							$this->html .= "    <thead>\n";
							$this->html .= "    <tr>\n";
							$this->html .= "    <th>Title</th>\n";
							$this->html .= "    <th>URL</th>\n";
							$this->html .= "    <th>Location</th>\n";
							$this->html .= "    <th>Language</th>\n";
							$this->html .= "    <th></th>\n";
							$this->html .= "    </tr>\n";
							$this->html .= "    </thead>\n";
							$this->html .= "    <tbody>\n";

							for ($counter = 0; $counter < count($this->cms_index['d']); $counter++) {
								$url = "/" . $tl->page['template'] . "/" . urlencode("screen=edit_content|content_type=d|cms_id=" .  $this->cms_index['d'][$counter]['cms_id']);
								$this->html .= "    <tr>\n";
								$this->html .= "    <td><a href='" . $url . "'>" . $this->cms_index['d'][$counter]['title'] . "</a></td>\n";
								$this->html .= "    <td>" . trim($this->cms_index['d'][$counter]['source_url_subdomain'] . '.' . $this->cms_index['d'][$counter]['source_url'], '. ') . "</td>\n";
								$this->html .= "    <td>" . $this->cms_index['d'][$counter]['country'] . ($this->cms_index['d'][$counter]['latitude'] <> 0 || $this->cms_index['d'][$counter]['longitude'] <> 0 ? " (" . $this->cms_index['d'][$counter]['latitude'] . ", " . $this->cms_index['d'][$counter]['longitude'] . ")" : false) . "</td>\n";
								$this->html .= "    <td>" . $this->cms_index['d'][$counter]['language'] . "</td>\n";
								$this->html .= "    <td class='text-right'><a href='" . $url . "' class='btn btn-default btn-sm'>Edit</a></td>\n";
								$this->html .= "    </tr>\n";
							}

							$this->html .= "    </tbody>\n";
							$this->html .= "    </table>\n";

						}

						$this->html .= "    <p><a href='/" . $tl->page['template'] . "/" . urlencode("screen=add_content|content_type=d") . "' class='btn btn-primary'>Add Domain Alias</a></p>\n";

						$this->html .= "  </div>\n";

					$this->html .= "</div>\n";

			}
			elseif ($this->screen == 'edit_content' || $this->screen == 'add_content') {

					$this->html .= "<h2>" . ucwords(str_replace('content', $this->content_types[$this->content_type], str_replace('_', ' ', $this->screen))) . "</h2>\n";

					$this->html .= $form->start('addEditContentForm') . "\n";
					$this->html .= $form->input('hidden', 'deleteContent') . "\n";
					$this->html .= $form->input('hidden', 'deleteLogo') . "\n";

					$this->js .= "$('[name=" . '"popover"' . "]').popover({ trigger: 'focus', placement: 'top', html: true, animation: true });\n";

					$mergeFieldStart = "<span class='label label-default'>";
					$mergeFieldEnd = "</span>";
					$this->js .= "function highlightMergeFields(input) {\n";
					$this->js .= "  output = input.replace(/{{/g, " . '"' . $mergeFieldStart . '"' . ");\n";
					$this->js .= "  output = output.replace(/}}/g, " . '"' . $mergeFieldEnd . '"' . ");\n";
					$this->js .= "  return output;\n";
					$this->js .= "}\n\n";

					if ($this->content_type == 'p') {

						$this->html .= "<ul class='nav nav-tabs' role='tablist'>\n";
						$this->html .= "  <li role='presentation'" . ($this->tab != 'preview' ? " class='active'" : false) . "><a href='#details' aria-controls='details' role='tab' data-toggle='tab'>Details</a></li>\n";
						$this->html .= "  <li role='presentation'" . ($this->tab == 'preview' ? " class='active'" : false) . "><a href='#preview' aria-controls='preview' role='tab' data-toggle='tab'>Preview</a></li>\n";
						$this->html .= "</ul>\n";
						$this->html .= "<br />\n";

						$this->html .= "<div class='tab-content'>\n";
						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab != 'preview' ? " active" : false) . "' id='details'>\n";
						
						/* Title */ 		$this->html .= $form->row('text', 'title', (@$_POST['title'] ? $_POST['title'] : @$this->cms[0]['title']), true, 'Title', 'form-control', '', 150);
						/* URL */ 			$this->html .= $form->rowStart('destination_url', 'URL');
											$this->html .= "  <div class='input-group'><span class='input-group-addon'>" . (@$tl->page['protocol'] ? $tl->page['protocol'] : 'http://') . (@$tl->page['subdomain'] ? $tl->page['subdomain'] . "." : false) . $tl->page['domain'] . "/</span>\n";
											$this->html .= "    " . $form->input('text', 'destination_url', (@$_POST['destination_url'] ? $_POST['destination_url'] : @$this->cms[0]['destination_url']), true, '|No spaces or slashes, please', 'form-control', null, 150) . "\n";
											$this->html .= "  </div>\n";
											$this->html .= $form->rowEnd();
						/* Language */ 		$this->html .= $form->row('language', 'language_id', (@$_POST['language_id'] ? $_POST['language_id'] : @$this->cms[0]['language_id']), false, 'Language', 'form-control');
						/* Domain Alias */ 	if (count($this->domain_aliases)) $this->html .= $form->row('select', 'domain_alias_id', (@$_POST['domain_alias_id'] ? $_POST['domain_alias_id'] : @$this->cms[0]['domain_alias_id']), false, 'Domain Alias', 'form-control', $this->domain_aliases);
						/* Content */		$this->html .= $form->row('textarea', 'content', (@$_POST['content'] ? $_POST['content'] : @$this->cms[0]['content']), true, 'Content|Plain text or HTML', 'form-control', null, null, ['rows'=>'10'], null, ['onChange'=>'document.getElementById("preview").innerHTML=this.value;']);
										
						$this->html .= "  <div id='more-fields-container' class='collapse" . (@$_POST['is_login_required'] || @$this->cms[0]['is_login_required'] || @$_POST['content_js'] || @$this->cms[0]['content_js'] || @$_POST['content_css'] || @$this->cms[0]['content_css'] ? " in" : false) . "'>\n";
						
						/* Login */			$this->html .= $form->row('yesno_bootstrap_switch', 'is_login_required', (@$_POST['is_login_required'] ? $_POST['is_login_required'] : @$this->cms[0]['is_login_required']), false, 'Login required?');
						/* JavaScript */	$this->html .= $form->row('textarea', 'content_js', (@$_POST['content_js'] ? $_POST['content_js'] : @$this->cms[0]['content_js']), false, 'JavaScript|Runs at pageload', 'form-control', null, null, array('rows'=>'7'));
						/* CSS */			$this->html .= $form->row('textarea', 'content_css', (@$_POST['content_css'] ? $_POST['content_css'] : @$this->cms[0]['content_css']), false, 'CSS', 'form-control', null, null, array('rows'=>'7'));
						
						$this->html .= "  </div>\n";

						/* Actions */		$this->html .= "  <div class='row'>\n";
											$this->html .= "    <div class='col-md-10 col-sm-10 col-xs-8'>\n";
											/* Save */		$this->html .= "      " . $form->input('submit', 'save_button', null, false, 'Save', 'btn btn-primary') . "\n";
											/* More */		$this->html .= "      " . $form->input('button', 'more_button', null, false, 'More', 'btn btn-link', null, null, array('data-target'=>'#more-fields-container', 'data-toggle'=>'collapse')) . "\n";
											/* Cancel */	$this->html .= "      " . $form->input('cancel_and_return', 'cancel_button', null, false, "Cancel", 'btn btn-link') . "\n";
											$this->html .= "      </div>\n";
											/* Delete */	if (@$this->cms[0]['deletable']) $this->html .= "    <div class='col-md-2 col-sm-2 col-xs-4 text-right'>" . $form->input('button', 'delete_button', null, false, 'Delete', 'btn btn-danger', null, null, null, null, ['onClick'=>'if (confirm("Are you sure?")) { document.addEditContentForm.deleteContent.value = "Y"; document.addEditContentForm.submit(); }']) . "</div>\n";
											$this->html .= "  </div>\n";

						$this->html .= "  </div>\n";
						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab == 'preview' ? " active" : false) . "' id='preview'>\n";
						$this->html .= (@$_POST['content'] ? $_POST['content'] : @$this->cms[0]['content']);
						$this->html .= "  </div>\n";
						$this->html .= "</div>\n";

					}
					elseif ($this->content_type == 'b') {
						
						$this->html .= "<ul class='nav nav-tabs' role='tablist'>\n";
						$this->html .= "  <li role='presentation'" . ($this->tab != 'preview' ? " class='active'" : false) . "><a href='#details' aria-controls='details' role='tab' data-toggle='tab'>Details</a></li>\n";
						$this->html .= "  <li role='presentation'" . ($this->tab == 'preview' ? " class='active'" : false) . "><a href='#preview' aria-controls='preview' role='tab' data-toggle='tab'>Preview</a></li>\n";
						$this->html .= "</ul>\n";
						$this->html .= "<br />\n";

						$this->html .= "<div class='tab-content'>\n";
						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab != 'preview' ? " active" : false) . "' id='details'>\n";
						
						/* Public ID */ 	$this->html .= $form->row('text', 'public_id', (@$_POST['public_id'] ? $_POST['public_id'] : @$this->cms[0]['public_id']), true, "<a href='javascript:void(0);' name='popover' data-toggle='popover' data-placement='left' data-content='This is used to reference the content block within the source code of the application. If changing this descriptor, please update the source code accordingly.'><span class='glyphicon glyphicon-question-sign translucent'></span></a> &nbsp; Unique Descriptor|The shorter the better", 'form-control', null, 100) . "\n";
						/* Language */ 		$this->html .= $form->row('language', 'language_id', (@$_POST['language_id'] ? $_POST['language_id'] : @$this->cms[0]['language_id']), false, 'Language', 'form-control');
						/* Domain Alias */ 	if (count($this->domain_aliases)) $this->html .= $form->row('select', 'domain_alias_id', (@$_POST['domain_alias_id'] ? $_POST['domain_alias_id'] : @$this->cms[0]['domain_alias_id']), false, 'Domain Alias', 'form-control', $this->domain_aliases);
						/* Content */		$this->html .= $form->row('textarea', 'content', (@$_POST['content'] ? $_POST['content'] : @$this->cms[0]['content']), true, 'Content|Plain text or HTML', 'form-control', null, null, ['rows'=>'10'], null, ['onChange'=>'document.getElementById("preview").innerHTML=highlightMergeFields(this.value);']);
										
						$this->html .= "  <div id='more-fields-container' class='collapse" . (@$_POST['is_login_required'] || @$this->cms[0]['is_login_required'] || @$_POST['content_js'] || @$this->cms[0]['content_js'] || @$_POST['content_css'] || @$this->cms[0]['content_css'] ? " in" : false) . "'>\n";
						
						/* JavaScript */	$this->html .= $form->row('textarea', 'content_js', (@$_POST['content_js'] ? $_POST['content_js'] : @$this->cms[0]['content_js']), false, 'JavaScript|Runs at pageload', 'form-control', null, null, array('rows'=>'7'));
						/* CSS */			$this->html .= $form->row('textarea', 'content_css', (@$_POST['content_css'] ? $_POST['content_css'] : @$this->cms[0]['content_css']), false, 'CSS', 'form-control', null, null, array('rows'=>'7'));
						
						$this->html .= "  </div>\n";

						/* Actions */		$this->html .= "  <div class='row'>\n";
											$this->html .= "    <div class='col-md-10 col-sm-10 col-xs-8'>\n";
											/* Save */		$this->html .= "      " . $form->input('submit', 'save_button', null, false, 'Save', 'btn btn-primary') . "\n";
											/* More */		$this->html .= "      " . $form->input('button', 'more_button', null, false, 'More', 'btn btn-link', null, null, array('data-target'=>'#more-fields-container', 'data-toggle'=>'collapse')) . "\n";
											/* Cancel */	$this->html .= "      " . $form->input('cancel_and_return', 'cancel_button', null, false, "Cancel", 'btn btn-link') . "\n";
											$this->html .= "      </div>\n";
											/* Delete */	if (@$this->cms[0]['deletable']) $this->html .= "    <div class='col-md-2 col-sm-2 col-xs-4 text-right'>" . $form->input('button', 'delete_button', null, false, 'Delete', 'btn btn-danger', null, null, null, null, ['onClick'=>'if (confirm("Are you sure?")) { document.addEditContentForm.deleteContent.value = "Y"; document.addEditContentForm.submit(); }']) . "</div>\n";
											$this->html .= "  </div>\n";

						$this->html .= "  </div>\n";

						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab == 'preview' ? " active" : false) . "' id='preview'>\n";
						$this->html .= str_replace("{{", $mergeFieldStart, str_replace("}}", $mergeFieldEnd, (@$_POST['content'] ? $_POST['content'] : @$this->cms[0]['content'])));
						$this->html .= "  </div>\n";
						$this->html .= "</div>\n";

					}
					elseif ($this->content_type == 'e') {

						$this->html .= "<ul class='nav nav-tabs' role='tablist'>\n";
						$this->html .= "  <li role='presentation'" . ($this->tab != 'preview_html' && $this->tab != 'preview_text' ? " class='active'" : false) . "><a href='#details' aria-controls='details' role='tab' data-toggle='tab'>Details</a></li>\n";
						$this->html .= "  <li role='presentation'" . ($this->tab == 'preview_html' ? " class='active'" : false) . "><a href='#preview_html' aria-controls='preview_html' role='tab' data-toggle='tab'>HTML Preview</a></li>\n";
						$this->html .= "  <li role='presentation'" . ($this->tab == 'preview_text' ? " class='active'" : false) . "><a href='#preview_text' aria-controls='preview_text' role='tab' data-toggle='tab'>Text Preview</a></li>\n";
						$this->html .= "</ul>\n";
						$this->html .= "<br />\n";

						$this->html .= "<div class='tab-content'>\n";
						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab != 'preview' ? " active" : false) . "' id='details'>\n";
						
						/* Subject */ 		$this->html .= $form->row('text', 'public_id', (@$_POST['public_id'] ? $_POST['public_id'] : @$this->cms[0]['public_id']), true, "Subject", 'form-control', null, 100) . "\n";
						/* Language */ 		$this->html .= $form->row('language', 'language_id', (@$_POST['language_id'] ? $_POST['language_id'] : @$this->cms[0]['language_id']), false, 'Language', 'form-control');
						/* Domain Alias */ 	if (count($this->domain_aliases)) $this->html .= $form->row('select', 'domain_alias_id', (@$_POST['domain_alias_id'] ? $_POST['domain_alias_id'] : @$this->cms[0]['domain_alias_id']), false, 'Domain Alias', 'form-control', $this->domain_aliases);
						/* Content HTML */	$this->html .= $form->row('textarea', 'content', (@$_POST['content'] ? $_POST['content'] : @$this->cms[0]['content']), true, "<a href='javascript:void(0);' name='popover' data-toggle='popover' data-placement='left' data-content='Desktop mail readers will generally show the HTML version, while mobile mail readers may show only the text version.'><span class='glyphicon glyphicon-question-sign translucent'></span></a> &nbsp; Body|HTML Version", 'form-control', null, null, ['rows'=>'10'], null, ['onChange'=>'document.getElementById("preview_html").innerHTML=highlightMergeFields(this.value);']);
						/* Content Text */	$this->html .= $form->row('textarea', 'content_plain', (@$_POST['content_plain'] ? $_POST['content_plain'] : @$this->cms[0]['content_plain']), true, "|Text Version", 'form-control', null, null, ['rows'=>'10'], null, ['onChange'=>'document.getElementById("preview_text").innerHTML=highlightMergeFields(this.value);']);
										
						/* Actions */		$this->html .= "  <div class='row'>\n";
											$this->html .= "    <div class='col-md-10 col-sm-10 col-xs-8'>\n";
											/* Save */		$this->html .= "      " . $form->input('submit', 'save_button', null, false, 'Save', 'btn btn-primary') . "\n";
											/* Cancel */	$this->html .= "      " . $form->input('cancel_and_return', 'cancel_button', null, false, "Cancel", 'btn btn-link') . "\n";
											$this->html .= "      </div>\n";
											/* Delete */	if (@$this->cms[0]['deletable']) $this->html .= "    <div class='col-md-2 col-sm-2 col-xs-4 text-right'>" . $form->input('button', 'delete_button', null, false, 'Delete', 'btn btn-danger', null, null, null, null, ['onClick'=>'if (confirm("Are you sure?")) { document.addEditContentForm.deleteContent.value = "Y"; document.addEditContentForm.submit(); }']) . "</div>\n";
											$this->html .= "  </div>\n";

						$this->html .= "  </div>\n";

						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab == 'preview_html' ? " active" : false) . "' id='preview_html'>\n";
						$this->html .= str_replace("{{", $mergeFieldStart, str_replace("}}", $mergeFieldEnd, (@$_POST['content'] ? $_POST['content'] : @$this->cms[0]['content'])));
						$this->html .= "  </div>\n";

						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab == 'preview_text' ? " active" : false) . "' id='preview_text'>\n";
						$this->html .= nl2br(str_replace("{{", $mergeFieldStart, str_replace("}}", $mergeFieldEnd, (@$_POST['content_plain'] ? $_POST['content_plain'] : @$this->cms[0]['content_plain']))));
						$this->html .= "  </div>\n";
						$this->html .= "</div>\n";
						
					}
					elseif ($this->content_type == 'c') {

						/* Type */			$this->html .= $form->row('select', 'message_type', (@$_POST['message_type'] ? $_POST['message_type'] : @$this->cms[0]['message_type']), true, 'Type', 'form-control', $this->confirmation_types);
						/* Public ID */ 	$this->html .= $form->row('text', 'public_id', (@$_POST['public_id'] ? $_POST['public_id'] : @$this->cms[0]['public_id']), true, "URI Slug", 'form-control', null, 100) . "\n";
						/* Language */ 		$this->html .= $form->row('language', 'language_id', (@$_POST['language_id'] ? $_POST['language_id'] : @$this->cms[0]['language_id']), false, 'Language', 'form-control');
						/* Domain Alias */ 	if (count($this->domain_aliases)) $this->html .= $form->row('select', 'domain_alias_id', (@$_POST['domain_alias_id'] ? $_POST['domain_alias_id'] : @$this->cms[0]['domain_alias_id']), false, 'Domain Alias', 'form-control', $this->domain_aliases);
						/* Content */		$this->html .= $form->row('textarea', 'content', (@$_POST['content'] ? $_POST['content'] : @$this->cms[0]['content']), true, 'Content|Plain text or HTML', 'form-control', null, null, ['rows'=>'3']);
										
						/* Actions */		$this->html .= "  <div class='row'>\n";
											$this->html .= "    <div class='col-md-10 col-sm-10 col-xs-8'>\n";
											/* Save */		$this->html .= "      " . $form->input('submit', 'save_button', null, false, 'Save', 'btn btn-primary') . "\n";
											/* Cancel */	$this->html .= "      " . $form->input('cancel_and_return', 'cancel_button', null, false, "Cancel", 'btn btn-link') . "\n";
											$this->html .= "      </div>\n";
											/* Delete */	if (@$this->cms[0]['deletable']) $this->html .= "    <div class='col-md-2 col-sm-2 col-xs-4 text-right'>" . $form->input('button', 'delete_button', null, false, 'Delete', 'btn btn-danger', null, null, null, null, ['onClick'=>'if (confirm("Are you sure?")) { document.addEditContentForm.deleteContent.value = "Y"; document.addEditContentForm.submit(); }']) . "</div>\n";
											$this->html .= "  </div>\n";
						
					}
					elseif ($this->content_type == 'f' && $this->screen == 'add_content') {

						/* Title */ 		$this->html .= $form->row('text', 'title', @$_POST['title'], false, 'Title', 'form-control', '', 150);
						/* Upload */		$this->html .= $form->row('file_dropzone', 'cms_upload', null, false, null, 'form-control', null, null, array('message'=>"Drag or click here to upload...", 'max_files'=>1, 'destination_path'=>'trash/' . date('Y-m-d_H-i-s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') + 1, date('Y'))), 'events'=>['success'=>'$("#hidden_submit").collapse("show"); $("#visible_cancel").collapse("hide");']));
						/* Actions */		$this->html .= "<div id='hidden_submit' class='collapse'>\n";
											/* Save */		$this->html .= "  " . $form->input('submit', 'save_button', null, false, 'Save', 'btn btn-info') . "\n";
											/* Cancel */	$this->html .= "  " . $form->input('cancel_and_return', 'cancel_button', null, false, "Cancel", 'btn btn-link') . "\n";
											$this->html .= "</div>\n";
											$this->html .= "<div id='visible_cancel' class='collapse in'>\n";
											/* Cancel */	$this->html .= "  " . $form->input('cancel_and_return', 'cancel_button', null, false, "Cancel", 'btn btn-link') . "\n";
											$this->html .= "</div>\n";
						
					}
					elseif ($this->content_type == 'f' && $this->screen == 'edit_content') {
						
						$this->html .= "<ul class='nav nav-tabs' role='tablist'>\n";
						$this->html .= "  <li role='presentation'" . ($this->tab != 'preview' ? " class='active'" : false) . "><a href='#details' aria-controls='details' role='tab' data-toggle='tab'>Details</a></li>\n";
						$this->html .= "  <li role='presentation'" . ($this->tab == 'preview' ? " class='active'" : false) . "><a href='#preview' aria-controls='preview' role='tab' data-toggle='tab'>Preview</a></li>\n";
						$this->html .= "</ul>\n";
						$this->html .= "<br />\n";

						$this->html .= "<div class='tab-content'>\n";
						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab != 'preview' ? " active" : false) . "' id='details'>\n";
						
						/* Title */ 		$this->html .= $form->row('text', 'title', (@$_POST['title'] ? $_POST['title'] : @$this->cms[0]['title']), false, 'Title', 'form-control', '', 150);
						/* Link */			$link = (@$tl->page['protocol'] ? $tl->page['protocol'] : 'http://') . (@$tl->page['subdomain'] ? $tl->page['subdomain'] . "." : false) . $tl->page['domain'] . "/" . $this->cms[0]['destination_url'];
											$this->html .= $form->row('uneditable_static', 'link', "<a href='" . $link . "'>" . $link . "</a>", false, "Link") . "\n";
						/* Metadata */		if (@$this->file_metadata) {
												foreach ($this->file_metadata as $key=>$value) {
													if ($value) $this->html .= $form->row('uneditable_static', $key, $value, false, $key) . "\n";
												}
											}
										
						/* Actions */		$this->html .= "  <div class='row'>\n";
											$this->html .= "    <div class='col-md-10 col-sm-10 col-xs-8'>\n";
											/* Save */		$this->html .= "      " . $form->input('submit', 'save_button', null, false, 'Save', 'btn btn-primary') . "\n";
											/* Cancel */	$this->html .= "      " . $form->input('cancel_and_return', 'cancel_button', null, false, "Cancel", 'btn btn-link') . "\n";
											$this->html .= "      </div>\n";
											/* Delete */	if (@$this->cms[0]['deletable']) $this->html .= "    <div class='col-md-2 col-sm-2 col-xs-4 text-right'>" . $form->input('button', 'delete_button', null, false, 'Delete', 'btn btn-danger', null, null, null, null, ['onClick'=>'if (confirm("Are you sure?")) { document.addEditContentForm.deleteContent.value = "Y"; document.addEditContentForm.submit(); }']) . "</div>\n";
											$this->html .= "  </div>\n";

						$this->html .= "  </div>\n";

						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab == 'preview' ? " active" : false) . "' id='preview'>\n";
						if ($file_manager->isImage(__DIR__ . '/../../../web_root/' . $this->cms[0]['destination_url'])) {
							$this->html .= "<center><img src='/" . $this->cms[0]['destination_url'] . "' class='img-responsive' /></center>\n";
						}
						elseif ($file_manager->isPDF(__DIR__ . '/../../../web_root/' . $this->cms[0]['destination_url'])) {
							$this->html .= "<iframe src='http://docs.google.com/gview?url=" . urlencode($tl->page['protocol'] . $tl->page['root'] . "/" . $this->cms[0]['destination_url']) . "&embedded=true' width='100%' height='500' frameborder='0'>Attempting to display...</iframe>\n";
						}
						elseif (@$metadata['File extension'] == 'docx' || @$metadata['File extension'] == 'doc' || @$metadata['File extension'] == 'xlsx' || @$metadata['File extension'] == 'xls' || @$metadata['File extension'] == 'pptx' || @$metadata['File extension'] == 'ppt') {
							$this->html .= "<iframe src='https://view.officeapps.live.com/op/embed.aspx?src=" . urlencode($tl->page['protocol'] . $tl->page['root'] . "/" . $this->cms[0]['destination_url']) . "' width='100%' height='500' frameborder='0'>Attempting to display...</iframe>\n";
						}
						else {
							$this->html .= "<br /><br /><br /><br /><br /><center>No preview available</center>\n";
						}
						$this->html .= "  </div>\n";

						$this->html .= "</div>\n";

					}
					elseif ($this->content_type == 'r') {

						/* Source */ 		$this->html .= $form->rowStart('source_url', "Redirect From");
											$this->html .= "  <div class='input-group'><span class='input-group-addon'>" . (@$tl->page['protocol'] ? $tl->page['protocol'] : 'http://') . (@$tl->page['subdomain'] ? $tl->page['subdomain'] . "." : false) . $tl->page['domain'] . "/</span>\n";
											$this->html .= "    " . $form->input('text', 'source_url', (@$_POST['source_url'] ? $_POST['source_url'] : @$this->cms[0]['source_url']), true, null, 'form-control', null, 150) . "\n";
											$this->html .= "  </div>\n";
											$this->html .= $form->rowEnd();
						/* Destination */	$this->html .= $form->row('text', 'destination_url', (@$_POST['destination_url'] ? $_POST['destination_url'] : @$this->cms[0]['destination_url']), true, "To", 'form-control', null, 150) . "\n";
						/* Header */ 		$this->html .= $form->row('select', 'http_status', (@$_POST['http_status'] ? $_POST['http_status'] : @$this->cms[0]['http_status']), false, "Header", 'form-control', $this->http_statuses);
										
						/* Actions */		$this->html .= "  <div class='row'>\n";
											$this->html .= "    <div class='col-md-10 col-sm-10 col-xs-8'>\n";
											/* Save */		$this->html .= "      " . $form->input('submit', 'save_button', null, false, 'Save', 'btn btn-primary') . "\n";
											/* Cancel */	$this->html .= "      " . $form->input('cancel_and_return', 'cancel_button', null, false, "Cancel", 'btn btn-link') . "\n";
											$this->html .= "      </div>\n";
											/* Delete */	if (@$this->cms[0]['deletable']) $this->html .= "    <div class='col-md-2 col-sm-2 col-xs-4 text-right'>" . $form->input('button', 'delete_button', null, false, 'Delete', 'btn btn-danger', null, null, null, null, ['onClick'=>'if (confirm("Are you sure?")) { document.addEditContentForm.deleteContent.value = "Y"; document.addEditContentForm.submit(); }']) . "</div>\n";
											$this->html .= "  </div>\n";
						
					}
					elseif ($this->content_type == 'a') {

						/* Source */ 		$this->html .= $form->rowStart('source_url', "URL");
											$this->html .= "  <div class='input-group'><span class='input-group-addon'>" . (@$tl->page['protocol'] ? $tl->page['protocol'] : 'http://') . (@$tl->page['subdomain'] ? $tl->page['subdomain'] . "." : false) . $tl->page['domain'] . "/</span>\n";
											$this->html .= "    " . $form->input('text', 'source_url', (@$_POST['source_url'] ? $_POST['source_url'] : @$this->cms[0]['source_url']), true, null, 'form-control', null, 150) . "\n";
											$this->html .= "  </div>\n";
											$this->html .= $form->rowEnd();
						/* Destination */ 	$this->html .= $form->rowStart('destination_url', "Aliases To");
											$this->html .= "  <div class='input-group'><span class='input-group-addon'>" . (@$tl->page['protocol'] ? $tl->page['protocol'] : 'http://') . (@$tl->page['subdomain'] ? $tl->page['subdomain'] . "." : false) . $tl->page['domain'] . "/</span>\n";
											$this->html .= "    " . $form->input('text', 'destination_url', (@$_POST['destination_url'] ? $_POST['destination_url'] : @$this->cms[0]['destination_url']), true, null, 'form-control', null, 150) . "\n";
											$this->html .= "  </div>\n";
											$this->html .= $form->rowEnd();
										
						/* Actions */		$this->html .= "  <div class='row'>\n";
											$this->html .= "    <div class='col-md-10 col-sm-10 col-xs-8'>\n";
											/* Save */		$this->html .= "      " . $form->input('submit', 'save_button', null, false, 'Save', 'btn btn-primary') . "\n";
											/* Cancel */	$this->html .= "      " . $form->input('cancel_and_return', 'cancel_button', null, false, "Cancel", 'btn btn-link') . "\n";
											$this->html .= "      </div>\n";
											/* Delete */	if (@$this->cms[0]['deletable']) $this->html .= "    <div class='col-md-2 col-sm-2 col-xs-4 text-right'>" . $form->input('button', 'delete_button', null, false, 'Delete', 'btn btn-danger', null, null, null, null, ['onClick'=>'if (confirm("Are you sure?")) { document.addEditContentForm.deleteContent.value = "Y"; document.addEditContentForm.submit(); }']) . "</div>\n";
											$this->html .= "  </div>\n";
						
					}
					elseif ($this->content_type == 'd') {

						/* Alias */ 		$this->html .= $form->rowStart('source_url', "<a href='javascript:void(0);' name='popover' data-toggle='popover' data-placement='left' data-content='There is no need to specify www for the subdomain'><span class='glyphicon glyphicon-question-sign translucent'></span></a> &nbsp; Alias");
											$this->html .= "  <div class='row'>\n";
											$this->html .= "    <div class='col-md-3'>" . $form->input('text', 'source_url_subdomain', (@$_POST['source_url_subdomain'] ? $_POST['source_url_subdomain'] : @$this->cms[0]['source_url_subdomain']), false, '|subdomain', 'form-control', '', 50) . "</div>\n";
											$this->html .= "    <div class='col-md-1 text-center'>.</div>\n";
											$this->html .= "    <div class='col-md-8'>" . $form->input('text', 'source_url', (@$_POST['source_url'] ? $_POST['source_url'] : @$this->cms[0]['source_url']), true, '|domain.com', 'form-control', '', 150) . "</div>\n";
											$this->html .= "  </div>\n";
											$this->html .= $form->rowEnd();
						/* Title */ 		$this->html .= $form->row('text', 'title', (@$_POST['title'] ? $_POST['title'] : @$this->cms[0]['title']), true, 'Title', 'form-control', '', 150);
						/* Description */ 	$this->html .= $form->row('text', 'content', (@$_POST['content'] ? $_POST['content'] : @$this->cms[0]['content']), false, "<a href='javascript:void(0);' name='popover' data-toggle='popover' data-placement='left' data-content='Will be used for the META DESCRIPTION tag'><span class='glyphicon glyphicon-question-sign translucent'></span></a> &nbsp; Description", 'form-control', '', 255);
						/* Country */ 		$this->html .= $form->row('country', 'country_id', (@$_POST['country_id'] ? $_POST['country_id'] : @$this->cms[0]['country_id']), false, 'Default Country', 'form-control');
						/* Language */ 		$this->html .= $form->row('language', 'language_id', (@$_POST['language_id'] ? $_POST['language_id'] : @$this->cms[0]['language_id']), false, 'Default Language', 'form-control');
						/* G Analytics */ 	$this->html .= $form->row('text', 'google_analytics_id', (@$_POST['google_analytics_id'] ? $_POST['google_analytics_id'] : @$this->cms[0]['google_analytics_id']), false, 'Google Analytics ID', 'form-control', '', 15);
						/* Logo */ 			$this->html .= $form->rowStart('logo', 'Logo');
											if ($this->cms[0]['destination_url']) {
												 $this->html .= "  <div><img src='/" . $this->cms[0]['destination_url'] . "' border='0' class='img-responsive' alt='Current logo' /></div><br />\n";
											}
											$this->html .= "  " . $form->input('file_dropzone', 'cms_upload', null, false, null, 'form-control', null, null, array('message'=>"Drag or click here to upload...", 'max_files'=>1, 'destination_path'=>'trash/' . date('Y-m-d_H-i-s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') + 1, date('Y')))));
											$this->html .= $form->rowEnd();
						/* Map Center */	if (floatval(@$_POST['map_center_latitude']) <> 0 || floatval(@$_POST['map_center_longitude']) <> 0) $coords = floatval(@$_POST['map_center_latitude']) . "," . floatval(@$_POST['map_center_longitude']);
											elseif (floatval(@$this->cms[0]['latitude']) <> 0 && floatval(@$this->cms[0]['longitude']) <> 0) $coords = floatval($this->cms[0]['latitude']) . "," . floatval($this->cms[0]['longitude']);
											$this->html .= $form->row('latlongmap', 'map_center', @$coords, false, "Map Center");
						
						/* Actions */		$this->html .= "  <div class='row'>\n";
											$this->html .= "    <div class='col-md-10 col-sm-10 col-xs-8'>\n";
											/* Save */		$this->html .= "      " . $form->input('submit', 'save_button', null, false, 'Save', 'btn btn-primary') . "\n";
											/* Cancel */	$this->html .= "      " . $form->input('cancel_and_return', 'cancel_button', null, false, "Cancel", 'btn btn-link') . "\n";
											$this->html .= "      </div>\n";
											$this->html .= "      <div class='col-md-2 col-sm-2 col-xs-4 text-right'>\n";
											/* Delete */	if (file_exists(__DIR__ . '/../../../web_root/' . $this->file_path . '/' . date('YmdHis', strtotime($this->cms[0]['updated_on'])) . '/' . $this->cms[0]['destination_url'])) $this->html .= "    " . $form->input('button', 'delete_logo_button', null, false, 'Delete Logo', 'btn btn-danger', null, null, null, null, ['onClick'=>'if (confirm("Are you sure?")) { document.addEditContentForm.deleteLogo.value = "Y"; document.addEditContentForm.submit(); }']) . "\n";
															if (@$this->cms[0]['deletable']) $this->html .= "    " . $form->input('button', 'delete_button', null, false, 'Delete', 'btn btn-danger', null, null, null, null, ['onClick'=>'if (confirm("Are you sure?")) { document.addEditContentForm.deleteContent.value = "Y"; document.addEditContentForm.submit(); }']) . "\n";
											$this->html .= "      </div>\n";
											$this->html .= "  </div>\n";

					}

					$this->html .= $form->end();
				
			}

		}

	}

?>
