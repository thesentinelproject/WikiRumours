<?php

	class logs_widget_TL {

		public $numberOfLogs = null;
		public $logs = null;
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
				  (includes page, sort, keywords)
				columns: array(field_name => Bootstrap_icon_suffix)
				limit: int
			*/

			global $tablePrefix;
			global $connectionTypes;
			$keyvalue_array = new keyvalue_array_TL();
			$parser = new parser_TL();
			$form = new form_TL();

			// check for errors
				if ($params && !is_array($params)) {
					$console .= __CLASS__ . "->" . __FUNCTION__ . ": Incorrectly formatted parameters.\n";
					return false;
				}

				if (!trim(@$params['template_name'])) {
					$console .= __CLASS__ . "->" . __FUNCTION__ . ": Unknown URI.\n";
					return false;
				}

				if (!@$params['columns'] || !is_array(@$params['columns'])) {
					$console .= __CLASS__ . "->" . __FUNCTION__ . ": Unclear which columns to be displayed.\n";
					return false;
				}

			// clean input
				$allowableParameters = array('filterable', 'connection_type_filter', 'exportable', 'paginate', 'rows_per_page', 'template_name', 'query_string', 'columns', 'resortable', 'limit');
				if ($params) {
					foreach ($params as $param=>$value) {
						$foundIt = false;
						foreach ($allowableParameters as $allowable) {
							if ($param == $allowable) $foundIt = true;
						}
						if (!$foundIt) unset($params[$param]);
					}
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
						elseif ($explodedPair[0] == 'keywords') $keywords = $explodedPair[1];
					}
				}
				if (@$params['paginate'] && !@$currentPage) $currentPage = 1;
				if (!@$sort) $sort = 'connected_on DESC';

			// build query
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
					$result = countInDb('logs', 'log_id', (@$connection_type ? [$tablePrefix . 'logs.connection_type'=>$connection_type] : false), null, null, null, $otherCriteria);
					$this->numberOfLogs = floatval($result[0]['count']);

					$numberOfPages = max(1, ceil($this->numberOfLogs / $params['rows_per_page']));
					if ($currentPage > $numberOfPages) $currentPage = $numberOfPages;
					
					$this->logs = retrieveLogs((@$connection_type ? [$tablePrefix . 'logs.connection_type'=>$connection_type] : false), null, @$otherCriteria, @$sort, floatval(($currentPage * $params['rows_per_page']) - $params['rows_per_page']) . ',' . $params['rows_per_page']);
				}
				elseif (@$params['limit']) {
					$result = countInDb('logs', 'log_id', (@$connection_type ? [$tablePrefix . 'logs.connection_type'=>$connection_type] : false), null, null, null, $otherCriteria);
					$this->numberOfLogs = floatval($result[0]['count']);

					$this->logs = retrieveLogs((@$connection_type ? [$tablePrefix . 'logs.connection_type'=>$connection_type] : false), null, @$otherCriteria, @$sort, $params['limit']);
				}
				else {
					$this->logs = retrieveLogs((@$connection_type ? [$tablePrefix . 'logs.connection_type'=>$connection_type] : false), null, @$otherCriteria, @$sort);
					$this->numberOfLogs = count($this->logs);
				}

			// create view
				$title = "<h2><span class='label label-default'>" . number_format(floatval($this->numberOfLogs)) . "</span> Logs</h2>";

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
					$this->html .= "<tr" . ($this->logs[$counter]['is_error'] === '1' && $this->logs[$counter]['is_resolved'] === '0' ? " class='danger'" : false) . ">\n";
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
						$this->html .= "    </div>\n";
						$this->html .= "  </div>\n";
						$this->html .= "</div>\n\n";
					}

				// filters and export
					if (@$params['filterable'] || @$params['exportable']) {
						$this->html .= "<div class='row'>\n";
						if (@$params['filterable']) {
							if (@$params['connection_type_filter']) {
								$this->html .= "  <div class='col-lg-6 col-md-6 col-sm-5 col-xs-6'>" . $form->input('text', 'keywords', @$keywords, false, null, 'form-control', null, 60) . "</div>\n";
								$this->html .= "  <div class='col-lg-4 col-md-4 col-sm-5 col-xs-6'>" . $form->input('select', 'connection_type', @$connection_type, false, "Initiated by", 'form-control', $connectionTypes) . "</div>\n";
							}
							else {
								$this->html .= "  <div class='col-lg-10 col-md-10 col-sm-10 col-xs-8'>" . $form->input('text', 'keywords', @$keywords, false, null, 'form-control', null, 60) . "</div>\n";
							}
						}
						if (@$params['exportable']) {
							$this->html .= "  <div class='col-lg-1 col-md-1 col-sm-1 col-xs-2'>" . $form->input('submit', 'submitButton', null, false, 'Filter', 'btn btn-primary btn-block') . "</div>\n";
							$this->html .= "  <div class='col-lg-1 col-md-1 col-sm-1 col-xs-2'>" . $form->input('button', 'exportButton', null, false, 'Export', 'btn btn-default btn-block', null, null, null, null, ['onClick'=>'document.logsForm.exportData.value="Y"; rebuildURL(); return false;']) . "</div>\n";
						}
						elseif (@$params['filterable']) {
							$this->html .= "  <div class='col-lg-2 col-md-2 col-sm-2 col-xs-4'>" . $form->input('submit', 'submitButton', null, false, 'Filter', 'btn btn-primary btn-block') . "</div>\n";
						}
						$this->html .= "</div>\n";
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
					$this->js .= "  if (document.logsForm.exportData.value == 'Y') document.location.href = '/export/' + encodeURI('report=logs|' + queryString);\n";
					$this->js .= "  else {\n";
					$this->js .= "    var queryString = '';\n";
					if (@$params['resortable']) $this->js .= "    if (document.logsForm.sort.value) queryString += '|sort=' + document.logsForm.sort.value;\n";
					if (@$params['filterable']) $this->js .= "    if (document.logsForm.keywords.value) queryString += '|keywords=' + document.logsForm.keywords.value;\n";
					if (@$params['filterable'] && @$params['connection_type_filter']) $this->js .= "    if (document.logsForm.connection_type.value) queryString += '|connection_type=' + document.logsForm.connection_type.value;\n";
					$this->js .= "    queryString = queryString.substring(1);\n";
					$this->js .= "    document.location.href = path + '/' + queryString;\n";
					$this->js .= "  }\n";
					$this->js .= "}\n\n";

				$this->html .= $form->end() . "\n";

		}

	}

?>
