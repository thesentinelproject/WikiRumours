<?php

	class user_report_widget_TL {

		public $numberOfUsers = null;
		public $allUsers = null;
		public $users = null;
		public $html = null;
		public $js = null;

		public function initialize($params) {

			/*
				ALLOWABLE PARAMETERS
				--
				filterable: true/false
				country_filter: true/false
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
			$keyvalue_array = new keyvalue_array_TL();
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
				$allowableParameters = array('filterable', 'country_filter', 'exportable', 'paginate', 'rows_per_page', 'template_name', 'query_string', 'columns', 'resortable', 'limit');
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
						elseif ($explodedPair[0] == 'country_id') $country_id = $explodedPair[1];
						elseif ($explodedPair[0] == 'keywords') $keywords = $explodedPair[1];
					}
				}
				if (@$params['paginate'] && !@$currentPage) $currentPage = 1;

			// build query
				$otherCriteria = '';
				if (@$keywords) {
					$keywordExplode = explode(' ', $keywords);
					foreach ($keywordExplode as $keyword) {
						if ($keyword) {
							if ($otherCriteria) $otherCriteria .= " AND";
							$otherCriteria .= " (" . $tablePrefix . "users.first_name LIKE '%" . addSlashes($keyword) . "%' OR " . $tablePrefix . "users.last_name LIKE '%" . addSlashes($keyword) . "%' OR " . $tablePrefix . "users.email LIKE '%" . addSlashes($keyword) . "%' OR " . $tablePrefix . "users.username LIKE '%" . addSlashes($keyword) . "%')";
						}
					}
					$otherCriteria = trim($otherCriteria);
				}

				if (@$params['paginate']) {
					$this->allUsers = retrieveUsers((@$country_id ? [$tablePrefix . 'users.country_id'=>$country_id] : false), null, null, null, $otherCriteria);
					$this->numberOfUsers = count($this->allUsers);

					$numberOfPages = max(1, ceil($this->numberOfUsers / $params['rows_per_page']));
					if ($currentPage > $numberOfPages) $currentPage = $numberOfPages;
					
					$this->users = retrieveUsers((@$country_id ? [$tablePrefix . 'users.country_id'=>$country_id] : false), null, @$otherCriteria, @$sort, floatval(($currentPage * $params['rows_per_page']) - $params['rows_per_page']) . ',' . $params['rows_per_page']);
				}
				elseif (@$params['limit']) {
					$result = retrieveUsers((@$country_id ? [$tablePrefix . 'users.country_id'=>$country_id] : false), null, null, null, $otherCriteria);
					$this->numberOfUsers = count($this->allUsers);

					$this->users = retrieveUsers((@$country_id ? [$tablePrefix . 'users.country_id'=>$country_id] : false), null, @$otherCriteria, @$sort, $params['limit']);
				}
				else {
					$this->users = retrieveUsers((@$country_id ? [$tablePrefix . 'users.country_id'=>$country_id] : false), null, @$otherCriteria, @$sort);
					$this->allUsers = $this->allUsers;
					$this->numberOfUsers = count($this->allUsers);
				}

			// create export
				$export = null;

				foreach ($params['columns'] as $column => $icon) {
					$export.= $column . ",";
				}
				$export = substr($export, 0, strlen($export) - 1) . "\n";

				for ($counter = 0; $counter < count($this->allUsers); $counter++) {
					foreach ($params['columns'] as $column => $icon) {
						$export .= '"' . $this->allUsers[$counter][$column] . '"' . ",";
					}
					$export = substr($export, 0, strlen($export) - 1) . "\n";
				}

			// create view
				$title = "<h2><span class='label label-default'>" . number_format(floatval($this->numberOfUsers)) . "</span> Users</h2>";

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

				$this->html .= $form->start('userReportForm', null, 'post', null, null, ['onSubmit'=>'rebuildURL(); return false;']) . "\n";
				$this->html .= $form->input('hidden', 'sort', @$sort) . "\n";
				$this->html .= $form->input('hidden', 'exportData') . "\n";

				$this->html .= "<table class='table table-condensed'>\n";
				$this->html .= "<thead>\n";
				$this->html .= "<tr>\n";
				foreach ($params['columns'] as $column => $icon) {
					$label = null;
					if (substr($column, 0, 3) == 'is_') $label = substr($column, 3);
					if (!@$label) $label = $column;

					$this->html .= "<th>";
					if (@$params['resortable']) $this->html .= "<a href='javascript:void(0);' onClick='resortUserReport(" . '"' . $column . '"' . "); return false;'>";
					$this->html .= ($icon ? "<span class='tooltips' data-toggle='tooltip' title='" . ucwords(str_replace('_', ' ', $label)) . "'><span class='glyphicon glyphicon-" . $icon . "' aria-hidden='true'></span></span>" : ucwords(str_replace('_', ' ', $label)));
					if (@$params['resortable']) $this->html .= "</a>";
					$this->html .= "</th>\n";
				}
				$this->html .= "<th></th>\n";
				$this->html .= "</tr>\n";
				$this->html .= "</thead>\n";
				$this->html .= "<tbody>\n";
				for ($counter = 0; $counter < count($this->users); $counter++) {
					$this->html .= "<tr" . ($this->users[$counter]['enabled'] === '0' ? " class='danger'" : false) . ">\n";
					foreach ($params['columns'] as $column => $icon) {
						if (@$this->users[$counter][$column] === '') $this->html .= "<td></td>\n"; // empty value or nonexistent column
						elseif ($column == 'username') $this->html .= "<td><a href='/profile/" . $this->users[$counter]['username'] . "' data-toggle='tooltip' title='" . htmlspecialchars($this->users[$counter]['full_name'], ENT_QUOTES). "'>" . $this->users[$counter]['username'] . "</a></td>\n";
						elseif ($column == 'full_name') $this->html .= "<td><a href='/profile/" . (@$this->users[$counter]['username'] ? $this->users[$counter]['username'] : $this->users[$counter]['public_id']) . "'" . (@$this->users[$counter]['username'] ? " data-toggle='tooltip' title='" . htmlspecialchars($this->users[$counter]['username'], ENT_QUOTES). "'" : false) . ">" . $this->users[$counter]['full_name'] . "</a></td>\n";
						elseif ($column == 'email') $this->html .= "<td><a href='mailto:" . $this->users[$counter]['email'] . "'>" . $this->users[$counter]['email'] . "</a></td>\n";
						elseif (strtotime($this->users[$counter][$column])) $this->html .= "<td>" . date('j-M-Y', strtotime($this->users[$counter][$column])) . "</td>\n";
						elseif (substr($column, 0, 3) == 'is_' && $this->users[$counter][$column] === '1') $this->html .= "<td><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></td>\n";
						elseif (substr($column, 0, 3) == 'is_' && $this->users[$counter][$column] === '0') $this->html .= "<td></td>\n";
						else $this->html .= "<td>" . $this->users[$counter][$column] . "</td>\n";
					}
					$this->html .= "<td class='text-right'><a href='/profile/" . (@$this->users[$counter]['username'] ? $this->users[$counter]['username'] : $this->users[$counter]['public_id']) . "'>&raquo;</a></td>\n";
					$this->html .= "</tr>\n";
				}
				$this->html .= "</tbody>\n";
				$this->html .= "</table>\n\n";

				if (!count($this->users)) $this->html .= "<p class='text-muted'>No results</p>\n\n";

				// filters and export
					if (@$params['filterable'] || @$params['exportable']) {
						$this->html .= "<div class='row'>\n";
						if (@$params['filterable']) {
							if (@$params['country_filter']) {
								$this->html .= "  <div class='col-lg-6 col-md-6 col-sm-5 col-xs-6'>" . $form->input('text', 'keywords', @$keywords, false, null, 'form-control', null, 60) . "</div>\n";
								$this->html .= "  <div class='col-lg-4 col-md-4 col-sm-5 col-xs-6'>" . $form->input('country', 'country_id', @$country_id, false, "Country", 'form-control') . "</div>\n";
							}
							else {
								$this->html .= "  <div class='col-lg-10 col-md-8 col-sm-8 col-xs-8'>" . $form->input('text', 'keywords', @$keywords, false, null, 'form-control', null, 60) . "</div>\n";
							}
						}
						if (@$params['exportable']) {
							$this->html .= "  <div class='col-lg-1 col-md-2 col-sm-2 col-xs-2'>" . $form->input('submit', 'submitButton', null, false, 'Filter', 'btn btn-primary btn-block') . "</div>\n";
							$this->html .= "  <div class='col-lg-1 col-md-2 col-sm-2 col-xs-2'>" . $form->input('button', 'exportButton', null, false, 'Export', 'btn btn-default btn-block', null, null, array('data-toggle'=>'modal', 'data-target'=>'#exportModal')) . "</div>\n";
						}
						elseif (@$params['filterable']) {
							$this->html .= "  <div class='col-lg-2 col-md-2 col-sm-2 col-xs-4'>" . $form->input('submit', 'submitButton', null, false, 'Filter', 'btn btn-primary btn-block') . "</div>\n";
						}
						$this->html .= "</div>\n\n";
					}

				//export modal
					$this->html .= "<!- export modal ->\n";
					$this->html .= "  <div class='modal fade' id='exportModal' tabindex='-1' role='dialog' aria-labelledby='exportModalLabel' aria-hidden='true'>\n";
					$this->html .= "    <div class='modal-dialog'>\n";
					$this->html .= "      <div class='modal-content'>\n";
					$this->html .= "        <div class='modal-header'>\n";
					$this->html .= "          <button type='button' class='close' data-dismiss='modal'><span aria-hidden='true'>&times;</span><span class='sr-only'>Close</span></button>\n";
					$this->html .= "          <h4 class='modal-title' id='exportModalLabel'>Export as CSV</h4>\n";
					$this->html .= "        </div>\n";
					$this->html .= "        <div class='modal-body'>\n";
					$this->html .= "          <div>" . $form->input('textarea', 'export', $export, false, null, 'form-control', null, null, array('rows'=>'7')) . "</div>\n";
					$this->html .= "          <br /><div>" . $form->input('button', 'clipboardButton', null, false, "Copy to clipboard", 'btn btn-primary') . "</div>\n";
					$this->html .= "        </div>\n";
					$this->html .= "      </div>\n";
					$this->html .= "    </div>\n";
					$this->html .= "  </div>\n\n";

					$this->js .= "// copy to clipboard\n";
					$this->js .= "	document.querySelector('#clipboardButton').addEventListener('click', function(event) {\n";
					$this->js .= "		// select text\n";
					$this->js .= "			document.querySelector('#export').select();\n";
					$this->js .= "		// copy text\n";
					$this->js .= "			try {\n";
					$this->js .= "				if (document.execCommand('copy')) {\n";
					$this->js .= "					alert('Successfully copied to clipboard');\n";
					$this->js .= "				}\n";
					$this->js .= "			} catch (err) {\n";
					$this->js .= "				alert(err);\n";
					$this->js .= "			}\n";
					$this->js .= "	})\n\n";

				// column resort
					if (@$params['resortable']) {
						$this->js .= "function resortUserReport(column) {\n";
						$this->js .= "  if (document.userReportForm.sort.value == column + ' ASC') document.userReportForm.sort.value = column + ' DESC';\n";
						$this->js .= "  else document.userReportForm.sort.value = column + ' ASC';\n";
						$this->js .= "  rebuildURL();\n";
						$this->js .= "}\n\n";
					}

				// URL processing
					$this->js .= "function rebuildURL() {\n";
					$this->js .= "  var path = '/" . $params['template_name'] . "';\n";
					$this->js .= "  var queryString = '" . @$params['query_string'] . "';\n";
					$this->js .= "  if (document.userReportForm.exportData.value == 'Y') document.location.href = '/export/' + encodeURI('report=users|' + queryString);\n";
					$this->js .= "  else {\n";
					$this->js .= "    var queryString = '';\n";
					if (@$params['resortable']) $this->js .= "    if (document.userReportForm.sort.value) queryString += '|sort=' + document.userReportForm.sort.value;\n";
					if (@$params['filterable']) $this->js .= "    if (document.userReportForm.keywords.value) queryString += '|keywords=' + document.userReportForm.keywords.value;\n";
					if (@$params['filterable'] && @$params['country_filter']) $this->js .= "    if (document.userReportForm.country_id.value) queryString += '|country_id=' + document.userReportForm.country_id.value;\n";
					$this->js .= "    queryString = queryString.substring(1);\n";
					$this->js .= "    document.location.href = path + '/' + queryString;\n";
					$this->js .= "  }\n";
					$this->js .= "}\n\n";

				$this->html .= $form->end() . "\n";

		}

	}

?>
