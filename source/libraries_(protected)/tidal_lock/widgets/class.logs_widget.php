<?php

	class logs_widget_TL {

		public $numberOfLogs = null;
		public $logs = null;
		public $allLogs = null;
		public $html = null;
		public $js = null;

		public function initialize($params) {

			/*
				ALLOWABLE PARAMETERS
				--
				filterable: true/false
				connection_type_filter: true/false
				exportable: true/false
				paginate: true/false
				rows_per_page: int
				template_name: URI
				query_string: string of pairs (delimited with =), each pair delimited with a |
				  (includes page, sort, keywords, alerts_only)
				columns: array(field_name => Bootstrap_icon_suffix)
				limit: int
			*/

			global $tablePrefix;
			global $connectionTypes;
			global $tl;

			$keyvalue_array = new keyvalue_array_TL();
			$parser = new parser_TL();
			$form = new form_TL();
			$file_manager = new file_manager_TL();
			$operators = new operators_TL();
			$authentication_manager = new authentication_manager_TL();

			// clean data
				$params['rows_per_page'] = floatval(@$params['rows_per_page']);
				$params['limit'] = floatval(@$params['limit']);

			// check for errors
				if ($params && !is_array($params)) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Incorrectly formatted parameters.\n";
					return false;
				}

				if (!trim(@$params['template_name'])) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unknown URI.\n";
					return false;
				}

				if (!@$params['columns'] || !is_array(@$params['columns'])) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unclear which columns to be displayed.\n";
					return false;
				}

				$params['rows_per_page'] = floatval(@$params['rows_per_page']);
				if (@$params['paginate'] && !@$params['rows_per_page']) $params['rows_per_page'] = 50;

				if (@$params['query_string']) {
					$queryArray = explode('|', $params['query_string']);
					foreach ($queryArray as $pair) {
						$explodedPair = explode('=', $pair);
						if ($explodedPair[0] == 'page') $currentPage = floatval($explodedPair[1]);
						elseif ($explodedPair[0] == 'sort') $sort = $explodedPair[1];
						elseif ($explodedPair[0] == 'connection_type') $connection_type = $explodedPair[1];
						elseif ($explodedPair[0] == 'alerts_only') $alerts_only = $explodedPair[1];
						elseif ($explodedPair[0] == 'alert_to_resolve') $alert_to_resolve = $explodedPair[1];
						elseif ($explodedPair[0] == 'keywords') $keywords = $explodedPair[1];
						elseif ($explodedPair[0] == 'output') $output = $explodedPair[1];
					}
				}
				if (@$params['paginate'] && !@$currentPage) $currentPage = 1;
				if (!@$sort) $sort = 'connected_on DESC';
				if (!@$alerts_only === 'false') $alerts_only = false;

			// resolve alert, if required
				if (floatval(@$alert_to_resolve)) {
					$success = updateDbSingle('logs', ['is_resolved'=>'1'], ['log_id'=>$alert_to_resolve]);
					if ($success) $authentication_manager->forceRedirect('/' . $params['template_name'] . '/' . $keyvalue_array->updateKeyValue($keyvalue_array->updateKeyValue(@$params['query_string'], 'success', 'alert_resolved', '|'), 'alert_to_resolve', null, '|'));
					else $tl->page['error'] .= "Unable to resolve alert. ";
				}

			// build query
				if(@$connection_type) $matching[$tablePrefix . 'logs.connection_type'] = $connection_type;
				if(@$alerts_only) {
					$matching[$tablePrefix . 'logs.is_error'] = '1';
					$matching[$tablePrefix . 'logs.is_resolved'] = '0';
				}

				$otherCriteria = '';
				if (@$keywords) {
					$keywordExplode = explode(' ', $keywords);
					foreach ($keywordExplode as $keyword) {
						if ($keyword) {
							if ($otherCriteria) $otherCriteria .= " AND";
							$otherCriteria .= " (" . $tablePrefix . "logs.activity LIKE '%" . addSlashes($keyword) . "%' OR " . $tablePrefix . "logs.error_message LIKE '%" . addSlashes($keyword) . "%')";
						}
					}
					$otherCriteria = trim($otherCriteria);
				}

				if (@$params['paginate']) {
					$this->allLogs = $this->retrieveLogs(@$matching, null, $otherCriteria);
					$this->numberOfLogs = count($this->allLogs);

					$numberOfPages = max(1, ceil($this->numberOfLogs / $params['rows_per_page']));
					if ($currentPage > $numberOfPages) $currentPage = $numberOfPages;
					
					$this->logs = $this->retrieveLogs(@$matching, null, @$otherCriteria, @$sort, floatval(($currentPage * $params['rows_per_page']) - $params['rows_per_page']) . ',' . $params['rows_per_page']);
				}
				elseif (@$params['limit']) {
					$this->allLogs = $this->retrieveLogs(@$matching, null, $otherCriteria);
					$this->numberOfLogs = count($this->allLogs);

					$this->logs = $this->retrieveLogs(@$matching, null, @$otherCriteria, @$sort, $params['limit']);
				}
				else {
					$this->logs = $this->retrieveLogs(@$matching, null, @$otherCriteria, @$sort);
					$this->allLogs = $this->logs;
					$this->numberOfLogs = count($this->logs);
				}

			// create export
				if (@$output == 'csv') {
					// create content
						$export = null;
						// header
							foreach ($params['columns'] as $column => $icon) {
								$export.= $column . ",";
							}
							$export = substr($export, 0, strlen($export) - 1) . "\n";
						// rows
							for ($logCounter = 0; $logCounter < count($this->allLogs); $logCounter++) {
								$increment = 0;
								foreach ($params['columns'] as $column => $icon) {
									$export .= '"' . $this->allLogs[$logCounter][$column] . '"';
									if ($increment < count($params['columns']) - 1) $export .= ",";
									else $export .= "\n";
									$increment++;
								}
							}
					// create folder
						$path = 'downloads/' . date('Y-m-d_H-i-s');
						mkdir($path);
						if (!file_exists($path)) $tl->page['error'] .= "Unable to create download directory. ";
						else {
							// create file
								$file = 'logs.csv';
								$file_manager->writeTextFile($path . '/' . $file, $export);
								if (!file_exists($path . '/' . $file)) $tl->page['error'] .= "Unable to create file export. ";
								else $exportPath = $path . '/' . $file;
						}
				}

			// create view
				$title = "<h2><span class='label label-default'>" . number_format(floatval($this->numberOfLogs)) . "</span> " . (@$alerts_only ? "Alerts" : "Logs") . "</h2>";

				if (!@$numberOfPages) {
					$this->html .= $title . "\n";
				}
				else {
					$this->html .= "<div class='row'>\n";
					$this->html .= "  <div class='col-lg-8 col-md-8 col-sm-6 col-xs-12'>" . $title . "</div>\n";
					$this->html .= "  <div class='col-lg-4 col-md-4 col-sm-6 col-xs-12'>\n";
					// pagination
						$this->html .= $form->paginate($currentPage, $numberOfPages, "/" . $params['template_name'] . "/" . (@$params['query_string'] ? $keyvalue_array->updateKeyValue(@$params['query_string'], 'page', '#', '|') : 'page=#'));
					$this->html .= "  </div>\n";
					$this->html .= "</div>\n";
				}

				$this->html .= $form->start('logsForm', null, 'post', null, null, ['onSubmit'=>'rebuildURL(); return false;']) . "\n";
				$this->html .= $form->input('hidden', 'sort', @$sort) . "\n";
				$this->html .= $form->input('hidden', 'exportData') . "\n";
				$this->html .= $form->input('hidden', 'alert_to_resolve') . "\n";

				$this->html .= "<table class='table table-condensed'>\n";
				$this->html .= "<thead>\n";
				$this->html .= "<tr>\n";
				foreach ($params['columns'] as $column => $icon) {
					$label = null;
					if ($column == 'connected_on') $label = 'connected';
					if ($column == 'connection_type') $label = 'type';
					if (substr($column, 0, 3) == 'is_') $label = substr($column, 3);
					if (!@$label) $label = $column;

					$this->html .= "<th>";
					if (@$params['resortable']) $this->html .= "<a href='javascript:void(0);' onClick='resortLogs(" . '"' . $column . '"' . "); return false;'>";
					$this->html .= ($icon ? "<span class='tooltips' data-toggle='tooltip' title='" . ucwords(str_replace('_', ' ', $label)) . "'><span class='glyphicon glyphicon-" . $icon . "' aria-hidden='true'></span></span>" : ucwords(str_replace('_', ' ', $label)));
					if (@$params['resortable']) $this->html .= "</a>";
					$this->html .= "</th>\n";
				}
				$this->html .= "<th></th>\n";
				$this->html .= "</tr>\n";
				$this->html .= "</thead>\n";
				$this->html .= "<tbody>\n";
				for ($counter = 0; $counter < count($this->logs); $counter++) {
					$this->html .= "<tr" . (!@$alerts_only && $this->logs[$counter]['is_error'] === '1' && $this->logs[$counter]['is_resolved'] === '0' ? " class='danger'" : false) . ">\n";
					foreach ($params['columns'] as $column => $icon) {
						if (@$this->logs[$counter][$column] === '') $this->html .= "<td></td>\n"; // empty value or nonexistent column
						elseif ($column == 'connection_type') $this->html .= "<td>" . $connectionTypes[$this->logs[$counter][$column]] . "</td>\n";
						elseif ($column == 'connection_length_in_seconds' && $this->logs[$counter]['is_released'] === '0') $this->html .= "<td>Timed out</td>\n";
						elseif ($column == 'activity') {
							$activity = nl2br($this->logs[$counter]['activity']);
							$lineBreak = strpos($activity, '<br />');
							if (!$lineBreak) $lineBreak = strlen($activity);
							$preview = substr($activity, 0, $lineBreak);
							$preview = $parser->truncate($preview, 'c', 45);
							$this->html .= "<td>" . $preview . "</td>\n";
						}
						elseif (strtotime($this->logs[$counter][$column])) $this->html .= "<td>" . str_replace(' ', '&nbsp;', date('g:i:s A', strtotime($this->logs[$counter][$column]))) . " <small class='text-muted'>" . date('j-M-Y', strtotime($this->logs[$counter][$column])) . "</small></td>\n";
						elseif (substr($column, 0, 3) == 'is_' && $this->logs[$counter][$column] === '1') $this->html .= "<td><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></td>\n";
						elseif (substr($column, 0, 3) == 'is_' && $this->logs[$counter][$column] === '0') $this->html .= "<td></td>\n";
						else $this->html .= "<td>" . $this->logs[$counter][$column] . "</td>\n";
					}
					$this->html .= "<td class='text-right'><a href='javascript:void(0);' data-toggle='modal' data-target='#modal_" . $this->logs[$counter]['log_id'] . "'>&raquo;</a></td>\n";
					$this->html .= "</tr>\n";
				}
				$this->html .= "</tbody>\n";
				$this->html .= "</table>\n\n";

				if (!count($this->logs)) $this->html .= "<p class='text-muted'>No results</p>\n";

				// modals
					for ($counter = 0; $counter < count($this->logs); $counter++) {
						$this->html .= "<div class='modal fade' id='modal_" . $this->logs[$counter]['log_id'] . "' tabindex='-1' role='dialog' aria-labelledby='modal_" . $this->logs[$counter]['log_id'] . "_Label' aria-hidden='true'>\n";
						$this->html .= "  <div class='modal-dialog'>\n";
						$this->html .= "    <div class='modal-content'>\n";
						$this->html .= "      <div class='modal-header'>\n";
						$this->html .= "        <button type='button' class='close' data-dismiss='modal'><span aria-hidden='true'>&times;</span><span class='sr-only'>Close</span></button>\n";
						$this->html .= "        <h4 class='modal-title' id='modal_" . $this->logs[$counter]['log_id'] . "_Label'>Details</h4>\n";
						$this->html .= "      </div>\n";
						$this->html .= "      <div class='modal-body'>\n";
						$this->html .= "        " . str_replace('|', '<br />', nl2br($this->logs[$counter]['activity'])) . "\n";
						if ($this->logs[$counter]['error_message']) $this->html .= "        <hr />" . $this->logs[$counter]['error_message'] . "\n";
						$this->html .= "      </div>\n";
						if ($this->logs[$counter]['is_error'] && !$this->logs[$counter]['is_resolved']) {
							$this->html .= "      <div class='modal-footer'>\n";
							$this->html .= "        " . $form->input('button', 'resolve_button', null, false, "Resolve", 'btn btn-primary', null, null, null, null, ['onClick'=>'document.getElementById("alert_to_resolve").value = "' . $this->logs[$counter]['log_id'] . '"; rebuildURL();']) . "</td>\n";
							$this->html .= "      </div>\n";
						}
						$this->html .= "    </div>\n";
						$this->html .= "  </div>\n";
						$this->html .= "</div>\n\n";
					}

				// filters and export
					if (@$params['filterable'] || @$params['exportable']) {
						$this->html .= "<div class='row'>\n";
						if (@$params['filterable']) {
							if (@$params['connection_type_filter']) {
								$this->html .= "  <div class='col-lg-5 col-md-3 col-sm-3 col-xs-3'>" . $form->input('text', 'keywords', @$keywords, false, null, 'form-control', null, 60) . "</div>\n";
								$this->html .= "  <div class='col-lg-3 col-md-3 col-sm-3 col-xs-3'>" . $form->input('select', 'connection_type', @$connection_type, false, "Initiated by", 'form-control', $connectionTypes) . "</div>\n";
							}
							else {
								$this->html .= "  <div class='col-lg-8 col-md-6 col-sm-6 col-xs-6'>" . $form->input('text', 'keywords', @$keywords, false, null, 'form-control', null, 60) . "</div>\n";
							}
						}

						if (@$params['exportable']) {
							$this->html .= "  <div class='col-lg-2 col-md-3 col-sm-3 col-xs-3'>" . $form->input('submit', 'submitButton', null, false, 'Filter', 'btn btn-primary btn-block') . "</div>\n";
							$this->html .= "  <div class='col-lg-2 col-md-3 col-sm-3 col-xs-3'>" . $form->input('button', 'exportButton', null, false, 'Export', 'btn btn-default btn-block', null, null, null, null, array('onClick'=>'document.getElementById("exportData").value = "Y"; rebuildURL();')) . "</div>\n";
						}
						elseif (@$params['filterable']) {
							$this->html .= "  <div class='col-lg-4 col-md-6 col-sm-6 col-xs-6'>" . $form->input('submit', 'submitButton', null, false, 'Filter', 'btn btn-primary btn-block') . "</div>\n";
						}
						$this->html .= "</div>\n";
					}

				//export modal
					if (@$exportPath) {
						$this->html .= "<!- export modal ->\n";
						$this->html .= "  <div class='modal fade' id='exportModal' tabindex='-1' role='dialog' aria-labelledby='exportModalLabel' aria-hidden='true'>\n";
						$this->html .= "    <div class='modal-dialog'>\n";
						$this->html .= "      <div class='modal-content'>\n";
						$this->html .= "        <div class='modal-header'>\n";
						$this->html .= "          <button type='button' class='close' data-dismiss='modal'><span aria-hidden='true'>&times;</span><span class='sr-only'>Close</span></button>\n";
						$this->html .= "          <h4 class='modal-title' id='exportModalLabel'>Your export is complete!</h4>\n";
						$this->html .= "        </div>\n";
						$this->html .= "        <div class='modal-body'>\n";
						if (@$tl->settings['Delete downloadables after']) $this->html .= "          <p>This download will be automatically deleted from the server within " . ($tl->settings['Delete downloadables after'] == 1 ? "1 day" : $tl->settings['Delete downloadables after'] . " days") . ".</p>\n";
						$this->html .= "          <a href='/" . $exportPath . "' target='_blank' class='btn btn-primary'>Download now</a>\n";
						$this->html .= "        </div>\n";
						$this->html .= "      </div>\n";
						$this->html .= "    </div>\n";
						$this->html .= "  </div>\n\n";

						$this->js .= "// autoload modal window\n";
						$this->js .= "  $(window).load(function(){\n";
						$this->js .= "    $('#exportModal').modal('show');\n";
						$this->js .= "  });\n\n";
 					}

				// column resort
					if (@$params['resortable']) {
						$this->js .= "function resortLogs(column) {\n";
						$this->js .= "  if (document.logsForm.sort.value == column + ' ASC') document.logsForm.sort.value = column + ' DESC';\n";
						$this->js .= "  else document.logsForm.sort.value = column + ' ASC';\n";
						$this->js .= "  rebuildURL();\n";
						$this->js .= "}\n\n";
					}

				// URL processing
					$this->js .= "function rebuildURL() {\n";
					$this->js .= "  var path = '/" . $params['template_name'] . "';\n";
					$this->js .= "  var queryString = '" . @$params['query_string'] . "';\n";
					if (@$params['resortable']) $this->js .= "  if (document.logsForm.sort.value) queryString = updateKeyValue_TL(queryString, 'sort', document.logsForm.sort.value, '|');\n";
					if (@$params['filterable']) $this->js .= "  if (document.logsForm.keywords.value) queryString = updateKeyValue_TL(queryString, 'keywords', document.logsForm.keywords.value, '|');\n";
					if (@$params['filterable'] && @$params['connection_type_filter']) $this->js .= "  if (document.logsForm.connection_type.value) queryString = updateKeyValue_TL(queryString, 'connection_type', document.logsForm.connection_type.value, '|');\n";
					if (@$params['exportable']) $this->js .= "  if (document.logsForm.exportData.value == 'Y') queryString = updateKeyValue_TL(queryString, 'output', 'csv', '|');\n";
					$this->js .= "  queryString = updateKeyValue_TL(queryString, 'alert_to_resolve', document.logsForm.alert_to_resolve.value, '|');\n";
					$this->js .= "  document.location.href = path + '/' + encodeURI(queryString);\n";
					$this->js .= "}\n\n";

				$this->html .= $form->end() . "\n";

		}

		public function retrieveLogs($matching = null, $containing = null, $otherCriteria = null, $sortBy = false, $limit = false) {
			
			global $tablePrefix;
			
			if (@$matching['relationship_name'] || @$matching['relationship_value']) {
				$query = "SELECT " . $tablePrefix . "log_relationships.*,";
				$query .= " " . $tablePrefix . "logs.*";
				$query .= " FROM " . $tablePrefix . "log_relationships";
				$query .= " LEFT JOIN " . $tablePrefix . "logs ON " . $tablePrefix . "log_relationships.log_id = " . $tablePrefix . "logs.log_id";
				$query .= " WHERE 1=1";
				if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . addSlashes($value) . "'";
				if ($containing) foreach ($containing as $field => $value) $query .= " AND " . $field . " LIKE '%" . addSlashes($value) . "%'";
				if ($otherCriteria) $query .= " AND (" . $otherCriteria . ")";
				if ($sortBy) $query .= " ORDER BY " . $sortBy;
				if ($limit) $query .= " LIMIT " . addSlashes($limit);

				return directlyQueryDb($query);

			}
			else return retrieveFromDb('logs', null, $matching, $containing, null, null, $otherCriteria, null, $sortBy, $limit);

		}

	}

?>
