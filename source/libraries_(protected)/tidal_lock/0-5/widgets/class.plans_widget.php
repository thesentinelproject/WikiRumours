<?php

	class plans_widget_TL {

		public $view = null;
		public $plan_public_id = null;
		public $attribute_id = null;

		function __construct() {

			global $tl;

			// validate view
				$this->view = @$tl->page['filters']['view'];

				$views = ['add_plan', 'edit_plan', 'add_attribute', 'edit_attribute', 'all_plans'];
				$views = array_fill_keys($views, true);

				if (@!$views[$this->view]) $this->view = 'all_plans';

				if ($this->view == 'all_plans') $tl->page['title'] = 'Plans';
				else $tl->page['title'] = ucwords(str_replace('_', ' ', $this->view));

				$this->plan_public_id = @$tl->page['filters']['plan_public_id'];
				$this->attribute_id = @$tl->page['filters']['attribute_id'];

			// process POST
				if (@$_POST['formName'] == 'addEditPlanForm' && @$_POST['deleteThisPlan'] == 'Y') $this->deletePlan();
				elseif (@$_POST['formName'] == 'addEditPlanForm') $this->updatePlan();
				elseif (@$_POST['formName'] == 'addEditAttributeForm' && @$_POST['deleteThisAttribute'] == 'Y') $this->deleteAttribute();
				elseif (@$_POST['formName'] == 'addEditAttributeForm') $this->updateAttribute();

		}

		/*	--------------------------------------
			Controllers
			-------------------------------------- */

			private function deletePlan() {

				global $tl;
				global $logged_in;

				$authentication_manager = new authentication_manager_TL();
				$logger = new logger_TL();

				// retrieve plan
					$plan = $this->retrievePlans(['plan_public_id'=>$this->plan_public_id], null, null, null, 1);
					if (!count($plan)) $tl->page['error'] .= "Unable to retrieve plan. ";
					else {

						// delete from DB
							$success = deleteFromDbSingle('plans', ['plan_id'=>$plan[0]['plan_id']]);
							if (!$success) $tl->page['error'] .= "Unable to delete plan. ";
							else {

								// delete relationships
									deleteFromDb('plans_x_attributes', ['plan_id'=>$plan[0]['plan_id']]);

								// update logs
									$activity = $logged_in['full_name'] . " has deleted a plan.";
									$logger->logItInDb($activity, null, ['user_id=' . $logged_in['user_id'], 'plan_id=' . $plan[0]['plan_id']]);


								// redirect
									$authentication_manager->forceRedirect('/' . $tl->page['template'] . '/success=plan_deleted');

							}

					}

			}

			private function updatePlan() {

				global $tl;
				global $logged_in;

				$parser = new parser_TL();
				$authentication_manager = new authentication_manager_TL();
				$logger = new logger_TL();

				// clean input
					$_POST = $parser->trimAll($_POST);
					$checkboxes = ['is_recommended', 'is_enabled'];
					foreach ($checkboxes as $checkbox) {
						if (isset($_POST[$checkbox])) $_POST[$checkbox] = 1;
						else $_POST[$checkbox] = 0;
					}

				// check for errors
					if ($this->view == 'edit_plan') {
						$plan = $this->retrievePlans(['plan_public_id'=>$this->plan_public_id], null, null, null, 1);
						if (!count($plan)) $tl->page['error'] .= "Unable to retrieve plan. ";
						else $planID = $plan[0]['plan_id'];
					}
					if (!$_POST['name']) $tl->page['error'] .= "Please provide a name. ";
					if (!$_POST['plan_public_id']) $tl->page['error'] .= "Please provide an ID. ";
					else {
						$exists = $this->retrievePlans(['plan_public_id'=>$_POST['plan_public_id']], null, "plan_public_id != '" . $this->plan_public_id . "'", null, 1);
						if ($exists) $tl->page['error'] .= "ID already exists; please choose something unique. ";
					}

					if (!$tl->page['error']) {

						// update plan in DB
							if ($this->view == 'add_plan') {
								$planID = insertIntoDb('plans', ['plan_public_id'=>$_POST['plan_public_id'], 'name'=>$_POST['name'], 'description'=>$_POST['description'], 'price_per_month'=>$_POST['price_per_month'], 'price_per_year'=>$_POST['price_per_year'], 'is_recommended'=>$_POST['is_recommended'], 'is_enabled'=>$_POST['is_enabled']]);
								if ($planID) $this->plan_public_id = $_POST['plan_public_id'];
							}
							elseif ($this->view == 'edit_plan') {
								updateDbSingle('plans', ['plan_public_id'=>$_POST['plan_public_id'], 'name'=>$_POST['name'], 'description'=>$_POST['description'], 'price_per_month'=>$_POST['price_per_month'], 'price_per_year'=>$_POST['price_per_year'], 'is_recommended'=>$_POST['is_recommended'], 'is_enabled'=>$_POST['is_enabled']], ['plan_public_id'=>$this->plan_public_id]);
							}

						// update plan attributes in DB
							$attributes = $this->retrievePlanAttributes();

							deleteFromDb('plans_x_attributes', ['plan_id'=>$planID]);
							foreach ($attributes as $attribute) {
								if ($_POST['attribute_id_' . $attribute['attribute_id']]) {
									insertIntoDb('plans_x_attributes', ['plan_id'=>$planID, 'attribute_id' =>$attribute['attribute_id'], 'value'=>$_POST['attribute_id_' . $attribute['attribute_id']]]);
								}
							}

						// update logs
							$activity = $logged_in['full_name'] . " has " . (@$plan ? "updated" : "added") . " the plan &quot;" . $_POST['name'] . "&quot;";
							$logger->logItInDb($activity, null, ['user_id=' . $logged_in['user_id'], 'plan_id=' . $planID]);


						// redirect
							$authentication_manager->forceRedirect('/' . $tl->page['template'] . '/success=plan_saved');

					}

			}

			private function deleteAttribute() {

				global $tl;
				global $logged_in;

				$authentication_manager = new authentication_manager_TL();
				$logger = new logger_TL();

				// delete from DB
					$success = deleteFromDbSingle('plan_attributes', ['attribute_id'=>$this->attribute_id]);
					if (!$success) $tl->page['error'] .= "Unable to delete attribute. ";
					else {

						// delete relationships
							deleteFromDb('plans_x_attributes', ['attribute_id'=>$this->attribute_id]);

						// update logs
							$activity = $logged_in['full_name'] . " has deleted an attribute.";
							$logger->logItInDb($activity, null, ['user_id=' . $logged_in['user_id'], 'attribute_id=' . $this->attribute_id]);


						// redirect
							$authentication_manager->forceRedirect('/' . $tl->page['template'] . '/success=attribute_deleted');

					}
				
			}

			private function updateAttribute() {

				global $tl;
				global $logged_in;
				global $tablePrefix;

				$parser = new parser_TL();
				$authentication_manager = new authentication_manager_TL();
				$logger = new logger_TL();

				// clean input
					$_POST = $parser->trimAll($_POST);

				// check for errors
					if ($this->view == 'edit_attribute') {
						$attribute = $this->retrievePlanAttributes([$tablePrefix . 'plan_attributes.attribute_id'=>$this->attribute_id], null, null, null, 1);
						if (!count($attribute)) $tl->page['error'] .= "Unable to retrieve attribute. ";
						else $attributeID = $attribute[0]['attribute_id'];
					}
					if (!$_POST['attribute_name']) $tl->page['error'] .= "Please provide a name. ";
					else {
						$exists = $this->retrievePlanAttributes(['attribute_name'=>$_POST['attribute_name']], null, $tablePrefix . "plan_attributes.attribute_id != '" . $this->attribute_id . "'", null, 1);
						if ($exists) $tl->page['error'] .= "This attribute already exists. ";
					}

					if (!$tl->page['error']) {

						// update attribute in DB
							if ($this->view == 'add_attribute') {
								$attributeID = insertIntoDb('plan_attributes', ['attribute_name'=>$_POST['attribute_name']]);
							}
							elseif ($this->view == 'edit_attribute') {
								updateDbSingle('plan_attributes', ['attribute_name'=>$_POST['attribute_name']], ['attribute_id'=>$this->attribute_id]);
							}

						// update logs
							$activity = $logged_in['full_name'] . " has " . (@$attribute ? "updated" : "added") . " the attribute &quot;" . $_POST['attribute_name'] . "&quot;";
							$logger->logItInDb($activity, null, ['user_id=' . $logged_in['user_id'], 'attribute_id=' . $attributeID]);


						// redirect
							$authentication_manager->forceRedirect('/' . $tl->page['template'] . '/success=attribute_saved');

					}

			}

		/*	--------------------------------------
			Models
			-------------------------------------- */

			public function retrievePlans($matching = null, $containing = null, $otherCriteria = null, $sortBy = false, $limit = false) {
				
				global $dbConnection;
				global $tablePrefix;
				
				// build query
					$query = "SELECT " . $tablePrefix . "plans.*";
					$query .= " FROM " . $tablePrefix . "plans";
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

				// retrieveAttributes
					for ($counter = 0; $counter < count($items); $counter++) {
						$items[$counter]['attributes'] = $this->retrievePlanAttributes(['plan_id'=>$items[$counter]['plan_id']]);
					}
					
				// clear memory
					$result->free();

				// return array
					return $items;
				
			}

			public function retrievePlanAttributes($matching = null, $containing = null, $otherCriteria = null, $sortBy = false, $limit = false) {
				
				global $dbConnection;
				global $tablePrefix;

				// build query
					$query = "SELECT " . $tablePrefix . "plan_attributes.*,";
					$query .= " " . $tablePrefix . "plans_x_attributes.value";
					$query .= " FROM " . $tablePrefix . "plan_attributes";
					$query .= " LEFT JOIN " . $tablePrefix . "plans_x_attributes ON " . $tablePrefix . "plans_x_attributes.attribute_id = " . $tablePrefix . "plan_attributes.attribute_id AND " . $tablePrefix . "plans_x_attributes.plan_id = '" . @$matching['plan_id'] . "'";
					unset ($matching['plan_id']);
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

		/*	--------------------------------------
			Views
			-------------------------------------- */

			public function createView() {

				if ($this->view == 'all_plans') $this->viewAllPlans();
				elseif ($this->view == 'add_plan' || $this->view == 'edit_plan') $this->viewAddEditPlan();
				elseif ($this->view == 'add_attribute' || $this->view == 'edit_attribute') $this->viewAddEditAttribute();
				
			}

			private function viewAllPlans() {

				global $tl;

				$plans = $this->retrievePlans(null, null, null, 'price_per_month ASC, price_per_year ASC, name ASC');

				if (!count($plans)) {

					echo "<h2>No plans found</h2>\n";

				}
				else {

					echo "<h2>" . $tl->page['title'] . "</h2>\n";

					echo "<table class='table table-condensed'>\n";
					echo "<thead>\n";
					echo "<tr>\n";
					echo "<th>ID</th>\n";
					echo "<th>Name</th>\n";
					echo "<th>Monthly</th>\n";
					echo "<th>Annual</th>\n";
					echo "<th></th>\n";
					echo "</tr>\n";
					echo "</thead>\n";
					echo "<tbody>\n";

					for ($counter = 0; $counter < count($plans); $counter++) {
						echo "<tr" . (!$plans[$counter]['is_enabled'] ? " class='danger'" : false) . ">\n";
						echo "<td>" . $plans[$counter]['plan_public_id'] . "</td>\n";
						echo "<td><a href='/" . $tl->page['template'] . "/" . urlencode("view=edit_plan|plan_public_id=" . $plans[$counter]['plan_public_id']) . "'>" . $plans[$counter]['name'] . "</a>" . ($plans[$counter]['is_recommended'] ? " &nbsp; <span class='glyphicon glyphicon-thumbs-up tooltips translucent' data-toggle='tooltip' data-placement='top' title='Recommended'></span>" : false) . "</td>\n";
						echo "<td>" . number_format($plans[$counter]['price_per_month'], 2) . "</td>\n";
						echo "<td>" . number_format($plans[$counter]['price_per_year'], 2) . "</td>\n";
						echo "<td><a href='/" . $tl->page['template'] . "/" . urlencode("view=edit_plan|plan_public_id=" . $plans[$counter]['plan_public_id']) . "'>&raquo;</a></td>\n";
						echo "</tr>\n";
					}

					echo "</tbody>\n";
					echo "</table>\n";

				}

				echo "<p>\n";
				echo "  <a href='/" . $tl->page['template'] . "/" . urlencode("view=add_plan") . "' class='btn btn-primary'>Add a Plan</a>\n";
				echo "</p>\n";

			}

			private function viewAddEditPlan() {

				global $tl;

				$authentication_manager = new authentication_manager_TL();
				$form = new form_TL();
				$operators = new operators_TL();

				if ($this->view == 'edit_plan') {
					if (!$this->plan_public_id) $authentication_manager->forceRedirect('/404');
					$plan = $this->retrievePlans(['plan_public_id'=>$this->plan_public_id], null, null, null, 1);
					if (!count($plan)) $authentication_manager->forceRedirect('/404');
				}

				if (!@$plan[0]['attributes']) $plan = [0 => ['attributes' => $this->retrievePlanAttributes()]];

				echo "<h2>" . $tl->page['title'] . "</h2>\n";

				echo $form->start('addEditPlanForm') . "\n";
				echo $form->input('hidden', 'deleteThisPlan') . "\n";

				// name
					echo $form->row('text', 'name', (@$_POST['name'] ? @$_POST['name'] : @$plan[0]['name']), true, "Name", 'form-control', null, 50, null, null, ['onChange'=>'generateID();']);
				// public_id
					echo $form->row('text', 'plan_public_id', (@$_POST['plan_public_id'] ? @$_POST['plan_public_id'] : @$plan[0]['plan_public_id']), true, "ID", 'form-control', null, 50);
				// description
					echo $form->row('textarea', 'description', (@$_POST['description'] ? @$_POST['description'] : @$plan[0]['description']), true, "Description", 'form-control');
				// price per month
					echo $form->row('number', 'price_per_month', (@$_POST['price_per_month'] ? @$_POST['price_per_month'] : @$plan[0]['price_per_month']), false, "Price per month", 'form-control', null, 50, ['step'=>'0.01']);
				// price per year
					echo $form->row('number', 'price_per_year', (@$_POST['price_per_year'] ? @$_POST['price_per_year'] : @$plan[0]['price_per_year']), false, "Price per year", 'form-control', null, 50, ['step'=>'0.01']);
				// recommended?
					echo $form->row('yesno_bootstrap_switch', 'is_recommended', (@$_POST['is_recommended'] ? $_POST['is_recommended'] : @$plan[0]['is_recommended']), false, 'Recommended?', null, null, null, ['data-on-color'=>'default', 'data-off-color'=>'default']);
				// attributes
					foreach ($plan[0]['attributes'] as $attribute) {
						echo $form->rowStart('label_attribute_id_' . $attribute['attribute_id'], $attribute['attribute_name']);
						echo "  <div class='row'>\n";
						echo "    <div class='col-lg-10 col-md-8 col-sm-8 col-xs-6'>\n";
						echo "      " . $form->input('text', 'attribute_id_' . $attribute['attribute_id'], (@$_POST['attribute_id_' . $attribute['attribute_id']] ? @$_POST['attribute_id_' . $attribute['attribute_id']] : @$attribute['value']), false, null, 'form-control', null, 255) . "\n";
						echo "    </div>\n";
						echo "    <div class='col-lg-2 col-md-4 col-sm-4 col-xs-6'>\n";
						echo "      <a href='/" . $tl->page['template'] . "/" . urlencode('view=edit_attribute|attribute_id=' . $attribute['attribute_id']) . "' class='btn btn-default btn-block'>Edit Attribute</a>\n";
						echo "    </div>\n";
						echo "  </div>\n";
						echo $form->rowEnd();
					}
				// enabled?
					echo $form->row('yesno_bootstrap_switch', 'is_enabled', $operators->firstTrueStrict(@$_POST['is_enabled'], @$plan[0]['is_enabled'], 1), false, 'Enabled?', null, null, null, ['data-on-color'=>'default', 'data-off-color'=>'default']);
				// actions
						echo $form->rowStart('actions');
						echo "  " . $form->input('submit', 'submit_button', null, false, "Save", 'btn btn-primary') . "\n";
						echo "  " . $form->input('button', 'delete_button', null, false, "Delete", 'btn btn-link', null, null, null, null, ['onClick'=>'deletePlan();']) . "\n";
						echo "  <a href='/" . $tl->page['template'] . "/" . urlencode('view=add_attribute') . "' class='btn btn-link'>Add Attribute</a>\n";
						echo "  " . $form->input('cancel_and_return', 'cancel_button', null, false, "Cancel", 'btn btn-link') . "\n";
						echo $form->rowEnd();

				echo $form->end();

				$tl->page['javascript'] .= "function generateID() {\n";
				$tl->page['javascript'] .= "  if (document.addEditPlanForm.name.value && !document.addEditPlanForm.plan_public_id.value) {\n";
				$tl->page['javascript'] .= "    document.addEditPlanForm.plan_public_id.value = document.addEditPlanForm.name.value.toLowerCase().replace(/'/g," . '""' . ").replace(/ /g," . '"-"' . ").substring(0, 50);\n";
				$tl->page['javascript'] .= "  }\n";
				$tl->page['javascript'] .= "}\n";

				$tl->page['javascript'] .= "function deletePlan() {\n";
				$tl->page['javascript'] .= "  if (confirm('Are you sure?')) {\n";
				$tl->page['javascript'] .= "    document.addEditPlanForm.deleteThisPlan.value = 'Y';\n";
				$tl->page['javascript'] .= "    document.addEditPlanForm.submit();\n";
				$tl->page['javascript'] .= "  }\n";
				$tl->page['javascript'] .= "}\n";

			}

			private function viewAddEditAttribute() {

				global $tl;
				global $tablePrefix;

				$form = new form_TL();

				if ($this->attribute_id) $attribute = $this->retrievePlanAttributes([$tablePrefix . 'plan_attributes.attribute_id'=>$this->attribute_id]);

				echo "<h2>" . $tl->page['title'] . "</h2>\n";

				echo $form->start('addEditAttributeForm') . "\n";
				echo $form->input('hidden', 'deleteThisAttribute') . "\n";

				// name
					echo $form->row('text', 'attribute_name', (@$_POST['attribute_name'] ? @$_POST['attribute_name'] : @$attribute[0]['attribute_name']), true, "Name", 'form-control', null, 255);
				// actions
						echo $form->rowStart('actions');
						echo "  " . $form->input('submit', 'submit_button', null, false, "Save", 'btn btn-primary') . "\n";
						echo "  " . $form->input('button', 'delete_button', null, false, "Delete", 'btn btn-link', null, null, null, null, ['onClick'=>'deleteAttribute();']) . "\n";
						echo "  " . $form->input('cancel_and_return', 'cancel_button', null, false, "Cancel", 'btn btn-link') . "\n";
						echo $form->rowEnd();

				echo $form->end();

				$tl->page['javascript'] .= "function deleteAttribute() {\n";
				$tl->page['javascript'] .= "  if (confirm('Are you sure?')) {\n";
				$tl->page['javascript'] .= "    document.addEditAttributeForm.deleteThisAttribute.value = 'Y';\n";
				$tl->page['javascript'] .= "    document.addEditAttributeForm.submit();\n";
				$tl->page['javascript'] .= "  }\n";
				$tl->page['javascript'] .= "}\n";

			}

	}

?>
