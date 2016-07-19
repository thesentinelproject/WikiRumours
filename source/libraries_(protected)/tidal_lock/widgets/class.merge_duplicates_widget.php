<?php

	class merge_duplicates_widget_TL {

		public $duplicates = array();
		public $primaryKey = null;
		public $html = null;
		public $js = null;

		public function initialize($params) {

			// determine primary key for identifying individual records
				$primaryKeyAttributes = retrieveDbKeys($params['table'], ['Key_name'=>'PRIMARY']);
				$this->primaryKey = $primaryKeyAttributes[0]['Column_name'];

			// make sure the desired table has an enabled field
				if (!@$params['matching']) $params['matching'] = array('enabled'=>'1');
				else $params['matching'] = array_merge(array('enabled'=>'1'), $params['matching']);

			// identify duplicates
				$this->findDuplicates(@$params);

			// provide appropriate UI
				if (@$params['view'] == 'merge' and count(@$params['ids_to_merge'])) $this->mergeDuplicates(@$params);
				else $this->displayDuplicates(@$params);

		}

		private function findDuplicates($params) {

			global $tl;

			if (!@$params['table']) {
				$tl->page['console'] .= "No table specified.\n";
				return false;
			}

			if (@$params['view'] == 'merge') {

				if (!@$params['ids_to_merge']) {
					$tl->page['console'] .= "No IDs to merge.\n";
					return false;
				}
				else $ids = explode(',', $params['ids_to_merge']);

				if (!@$params['this_template']) {
					$tl->page['console'] .= "No template URL to build link.\n";
					return false;
				}

				foreach ($ids as $id) {
					$result = retrieveFromDb($params['table'], null, array_merge([$this->primaryKey=>$id], $params['matching']));
					if (count($result)) $this->duplicates[] = $result[0];
				}

			}
			else {

				if (!count(@$params['compare'])) {
					$tl->page['console'] .= "No fields to compare.\n";
					return false;
				}

				$result = retrieveFromDb($params['table'], null, $params['matching']);

				for ($counter = 0; $counter < count($result) - 1; $counter++) {

					for ($matchCounter = $counter + 1; $matchCounter < count($result); $matchCounter++) {

						$match = false;
						
						foreach ($params['compare'] as $field) {
							if ($result[$counter][$field] && $result[$counter][$field] == $result[$matchCounter][$field]) $match = true;
						}

						if ($match) {
							if (!@$result[$counter]['_match_found_']) {
								$result[$counter]['_match_found_'] = true;
								$i = count($this->duplicates);
								$this->duplicates[$i] = array();
								$this->duplicates[$i]['primary'] = $result[$counter];
								$this->duplicates[$i]['secondary'] = array();
							}
							else $i = count($this->duplicates) - 1;

							$this->duplicates[$i]['secondary'][] = $result[$matchCounter];
						}

					}

				}

			}

		}


		private function displayDuplicates($params) {

			global $tl;

			if (!count(@$params['compare'])) {
				$tl->page['console'] .= "No fields to compare.\n";
				return false;
			}

			if (!@$params['this_template']) {
				$tl->page['console'] .= "No template URL to build link.\n";
				return false;
			}

			if (!count($this->duplicates)) {
				$this->html .= "<p>None found</p>\n";
			}
			else {

				$this->html .= "<table class='table table-condensed table-hover'>\n";
				$this->html .= "<thead>\n";
				$this->html .= "<tr>\n";
				$this->html .= "<th>" . ucwords(str_replace('_', ' ', str_replace('-', ' ', $this->primaryKey))) . "</th>\n";
				foreach (@$params['compare'] as $field) {
					$this->html .= "<th>" . ucwords(str_replace('_', ' ', str_replace('-', ' ', $field))) . "</th>\n";
				}
				$this->html .= "<th></th>\n";
				$this->html .= "</tr>\n";
				$this->html .= "</thead>\n";
				$this->html .= "<tbody>\n";
				for ($counter = 0; $counter < count($this->duplicates); $counter++) {
					$this->html .= "<tr>\n";
					$this->html .= "<td>\n";
					$this->html .= "  <div>" . $this->duplicates[$counter]['primary'][$this->primaryKey] . "</div>\n";
					foreach ($this->duplicates[$counter]['secondary'] as $secondary) {
						$this->html .= "  <div>" . $secondary[$this->primaryKey] . "</div>\n";
					}
					$this->html .= "</td>\n";
					foreach ($params['compare'] as $field) {
						$this->html .= "<td>\n";
						$this->html .= "  <div>" . ($this->duplicates[$counter]['primary'][$field] ? $this->duplicates[$counter]['primary'][$field] : "-") . "</div>\n";
						foreach ($this->duplicates[$counter]['secondary'] as $secondary) {
							$this->html .= "  <div>" . ($secondary[$field] ? $secondary[$field] : "-") . "</div>\n";
						}
						$this->html .= "</td>\n";
					}
					if ($this->primaryKey) {
						$keys = $this->duplicates[$counter]['primary'][$this->primaryKey];
						foreach ($this->duplicates[$counter]['secondary'] as $secondary) {
							$keys .= "," . $secondary[$this->primaryKey];
						}
					}
					$this->html .= "<td><a href='/" . $params['this_template'] . "/" . urlencode("view=merge|ids_to_merge=" . $keys) . "'>&raquo;</a></td>\n";
					$this->html .= "</tr>\n";
				}
				$this->html .= "</tbody>\n";
				$this->html .= "</table>\n";

			}
		}

		private function mergeDuplicates($params) {

			global $logged_in;
			global $tl;

			$authentication_manager = new authentication_manager_TL();

			if (!count($this->duplicates)) {
				$tl->page['console'] .= "No duplicates to merge.\n";
				return false;
			}

			if (!@$params['this_template']) {
				$tl->page['console'] .= "No template URL to build link.\n";
				return false;
			}

			if (@$params['select_to_merge']) {

				// disable all accounts except selected
					for ($counter = 0; $counter < count($this->duplicates); $counter++) {
						if ($this->duplicates[$counter][$this->primaryKey] != $params['select_to_merge']) {
							updateDbSingle($params['table'], ['enabled'=>'0'], [$this->primaryKey=>$this->duplicates[$counter][$this->primaryKey]]);
						}
					}

				// change related tables
					if (@$params['foreign_keys']) {
						foreach ($params['foreign_keys'] as $table => $columns) {
							foreach ($columns as $column) {
								for ($counter = 0; $counter < count($this->duplicates); $counter++) {
									updateDb($table, [$column=>$params['select_to_merge']], [$column=>$this->duplicates[$counter][$this->primaryKey]]);
								}
							}
						}
					}

				// update logs
					$logger = new logger_TL();
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has merged duplicate records, impacting " . (1 + count(@$params['foreign_keys'])) . " database table(s)";
					$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id']));

				// redirect to index
					$authentication_manager->forceRedirect("/" . $params['this_template'] . "/" . urlencode("success=successfully_merged_duplicates"));

			}
			else {

				if (@$params['also_display']) $fields = array_merge($params['compare'], $params['also_display']);
				else $fields = $params['compare'];

				$this->html .= "<p>Please consolidate any information below and then select the record you want to remain active. All other records will be disabled. Further, any related data, if specified below, will be reassigned to the selected active record. <strong>It is recommended that you exercise extreme caution. There is no undo for this action.</strong></p>";

				$form = new form_TL();
				$this->html .= $form->start('mergeDuplicatesForm', null, 'post', null, null, ['onSubmit'=>'validateMergeDuplicatesForm(); return false;']);

				$this->html .= "<br /><table class='table table-condensed table-hover table-bordered'>\n";
				$this->html .= "<thead>\n";
				$this->html .= "<tr>\n";
				$this->html .= "<th></th>\n";
				for ($counter = 0; $counter < count($this->duplicates); $counter++) {
					$this->html .= "<th><center>" . ucwords($this->primaryKey) . "<br />" . $this->duplicates[$counter][$this->primaryKey] . "</center></th>\n";
				}
				$this->html .= "</tr>\n";
				$this->html .= "</thead>\n";
				$this->html .= "<tbody>\n";
				foreach ($fields as $field) {
					$this->html .= "<tr>\n";
					$this->html .= "<td>" . ucwords(str_replace('_', ' ', str_replace('-', ' ', $field))) . "</td>\n";
					for ($counter = 0; $counter < count($this->duplicates); $counter++) {
						$this->html .= "<td><center>" . $this->duplicates[$counter][$field] . "</center></td>\n";
					}
					$this->html .= "</tr>\n";
				}
				$this->html .= "<tr>\n";
				$this->html .= "<td></td>\n";
				for ($counter = 0; $counter < count($this->duplicates); $counter++) {
					$this->html .= "<td><center>" . $form->input('button', 'select_record_button', null, false, "Select", 'btn btn-default', null, null, null, null, ['onClick'=>'validateMergeDuplicatesForm("' . $this->duplicates[$counter][$this->primaryKey] . '"); return false;']) . "</center></td>\n";
				}
				$this->html .= "</tr>\n";
				$this->html .= "</tbody>\n";
				$this->html .= "</table>\n\n";

				if (@$params['foreign_keys']) {

					$this->html .= "<p>The following related records will changed to reflect the selected user above:</p>\n\n";
					$this->html .= "<ul>\n";

					foreach ($params['foreign_keys'] as $table => $columns) {
						foreach ($columns as $column) {
							$this->html .= "<li>" . $table . "." . $column. "</li>\n";
						}
					}

					$this->html .= "</ul>\n";
				}
				else $this->html .= "<p>No related records have been specified for updates.</p>\n\n";

				$this->html .= "<p><br /><a href='/" . $params['this_template'] . "' class='btn btn-default'>Cancel</a></p>\n";

				$this->js .= "// validate merge duplicates form\n";
				$this->js .= "  function validateMergeDuplicatesForm(id) {\n";
				$this->js .= "    areYouSure = confirm('Are you sure?');\n";
				$this->js .= "    if (areYouSure) document.location.href = '/" . $params['this_template'] . "/" . urlencode("view=merge|ids_to_merge=" . $params['ids_to_merge'] . "|select_to_merge=") . "'+id;\n";
				$this->js .= "    else return false;\n";
				$this->js .= "  }\n\n";

			}

		}

	}

?>
