<?php

	class faqs_widget_TL {

		// input
			public $tagID = 0;
			public $faqID = 0;
			public $keywords = null;

		// output
			public $faqs = null;

				/*	Structure for $faqs is:
					--
					$faqs[tag_position]['tag_id']
					$faqs[tag_position]['tag']
					$faqs[tag_position]['faqs'][counter]['question']
				*/

			public $numberOfFaqs = 0;
			public $tags = null;
			public $allTags = null;

			public $html = null;
			public $js = null;
			public $css = null;
			public $searchFormHtml = null;

		/*	----------------------------------------------
			FAQs
			----------------------------------------------	*/

			public function retrieveFAQ() {

				global $tablePrefix;
				global $tl;

				$authentication_manager = new authentication_manager_TL();

				// clean input
					$this->faqID = floatval($this->faqID);

				// check for errors
					if (!$this->faqID) {
						$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Missing FAQ ID.\n";
						return false;
					}

				// retrieve FAQ
					$this->faqs = array();
					$this->faqs[0] = array();
					$this->faqs[0]['faqs'] = retrieveFromDb('faqs', null, ['faq_id'=>$this->faqID]);

					if (count($this->faqs[0]['faqs']) <> 1) $authentication_manager->forceRedirect('/' . $tl->page['template'] . "/screen/error=invalid_faq");
					else {
						$this->numberOfFaqs = 1;
						$this->retrieveTags();
					}

			}

			public function retrieveFAQs() {

				global $tl;
				global $tablePrefix;

				// build query
					if ($this->keywords) { // retrieve search results regardless of tagging

						$otherCriteria = '';
						$keywordExplode = explode(' ', urldecode($this->keywords));
						foreach ($keywordExplode as $keyword) {
							if ($keyword) {
								if ($otherCriteria) $otherCriteria .= " AND";
								$otherCriteria .= " (question LIKE '%" . addSlashes($keyword) . "%' OR answer LIKE '%" . addSlashes($keyword) . "%')";
							}
						}
						$otherCriteria = trim($otherCriteria);

						$this->faqs = array();
						$this->faqs[0] = array();
						$this->faqs[0]['faqs'] = retrieveFromDb('faqs', null, null, null, null, null, $otherCriteria);

						$this->numberOfFaqs = count($this->faqs[0]['faqs']);

					}
					else { // retrieve all FAQs, ordered by tags

						$query = "SELECT " . $tablePrefix . "faqs.faq_id AS faq_id,";
						$query .= " " . $tablePrefix . "faqs.question AS question,";
						$query .= " " . $tablePrefix . "faqs.answer AS answer,";
						$query .= " " . $tablePrefix . "faqs_x_faqtags.tag_id AS tag_id,";
						$query .= " " . $tablePrefix . "faqtags.tag AS tag,";
						$query .= " " . $tablePrefix . "faqtags.position AS tag_position";
						$query .= " FROM " . $tablePrefix . "faqs_x_faqtags";
						$query .= " LEFT JOIN " . $tablePrefix . "faqs ON " . $tablePrefix . "faqs_x_faqtags.faq_id = " . $tablePrefix . "faqs.faq_id";
						$query .= " LEFT JOIN " . $tablePrefix . "faqtags ON " . $tablePrefix . "faqs_x_faqtags.tag_id = " . $tablePrefix . "faqtags.tag_id";
						$query .= " ORDER BY " . $tablePrefix . "faqs.question ASC";
						
						$result = directlyQueryDb($query);
						$numberOfFaqs = count($result);

						if ($numberOfFaqs) {

							$this->faqs = array();

							for ($counter = 0; $counter < $numberOfFaqs; $counter++) {

								$tagPosition = str_pad(floatval($result[$counter]['tag_position']), 2, '0', STR_PAD_LEFT) . '_' . floatval($result[$counter]['tag_id']);
								if ($tagPosition == '0_0') $tagPosition = 0;

								if (!@$this->faqs[$tagPosition]) $this->faqs[$tagPosition] = array();
								$this->faqs[$tagPosition]['tag_id'] = floatval(@$result[$counter]['tag_id']);
								$this->faqs[$tagPosition]['tag'] = @$result[$counter]['tag'];
								if (!@$this->faqs[$tagPosition]['faqs']) $this->faqs[$tagPosition]['faqs'] = array();
								$this->faqs[$tagPosition]['faqs'][$counter] = array(
									'question'=>$result[$counter]['question'],
									'answer'=>$result[$counter]['answer'],
									'faq_id'=>$result[$counter]['faq_id']
								);

							}

							ksort($this->faqs); // sort tags

							$result = countInDb('faqs');
							$this->numberOfFaqs = $result[0]['count'];

						}

					}

			}

			public function displayFAQs() {

				global $tl;

				// check for errors
					if (!count(@$this->faqs)) {
						$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No FAQs found.\n";
						return false;
					}

				foreach ($this->faqs as $tag) {
					if (@$tag['tag']) $this->html .= "<div class='tagName'>" . $tag['tag'] . "</div>\n\n";
					foreach ($tag['faqs'] as $faq) {
						$this->html .= "<div class='faqQuestion'><a href='javascript:void(0);' data-toggle='collapse' data-target='#faq_" . $faq['faq_id']. "'>" . $faq['question'] . "</a></div>\n";
						$this->html .= "<div id='faq_" . $faq['faq_id'] . "' class='collapse'><div class='faqAnswer'>" . $faq['answer'] . "</div></div>\n\n";
					}
				}

			}

			public function displayEditableFAQsAndTags() {

				global $_POST;
				global $logged_in;
				global $tl;

				$form = new form_TL();
				$logger = new logger_TL();
				$operators = new operators_TL();
				$parser = new parser_TL();
				$authentication_manager = new authentication_manager_TL();


				$this->retrieveTags();

				// evaluate $_POST
					if (@$_POST) {

						if (@$_POST['formName'] == 'editTagsForm' && @$_POST['tagToDelete']) {

							// clean input
								$_POST['tagToDelete'] = floatval($_POST['tagToDelete']);

							// check for errors
								if (!$_POST['tagToDelete']) $tl->page['error'] .= "Unable to determine which tag to delete. ";

								if (!$tl->page['error']) {

									// update database
										deleteFromDbSingle('faqtags', ['tag_id'=>$_POST['tagToDelete']]);
										updateDb('faqs_x_faqtags', ['tag_id'=>'0'], ['tag_id'=>$_POST['tagToDelete']]);

									// update log
										$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated FAQs";
										$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id']));
									
									// redirect
										$authentication_manager->forceRedirect('/' . $tl->page['template'] . '/index/success=faqs_updated');

								}

						}
						elseif (@$_POST['formName'] == 'editTagsForm') {

							// clean input
								$_POST = $parser->trimAll($_POST);

							// check for errors
								for ($counter = 0; $counter < count($this->allTags); $counter++) {
									if (!$_POST['tag_' . $this->allTags[$counter]['tag_id']]) {
										$tl->page['error'] .= "Please specify a tag name. ";
										break;
									}
								}

								if (!$tl->page['error']) {

									// update database
										for ($counter = 0; $counter < count($this->allTags); $counter++) {
											updateDbSingle('faqtags', ['tag'=>$_POST['tag_' . $this->allTags[$counter]['tag_id']], 'position'=>$_POST['position_' . $this->allTags[$counter]['tag_id']]], ['tag_id'=>$this->allTags[$counter]['tag_id']]);
										}

									// update log
										$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated FAQs";
										$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id']));
									
									// redirect
										$authentication_manager->forceRedirect('/' . $tl->page['template'] . '/index/success=faqs_updated');

								}

						}

					}

				// draw page

					$this->html .= "    <h2>Edit FAQs</h2>\n";
					// tabs
						$this->html .= "<ul class='nav nav-tabs' role='tablist'>\n";
						$this->html .= "  <li role='presentation' class='active'><a href='#faqs' aria-controls='faqs' role='tab' data-toggle='tab'>FAQs" . ($this->numberOfFaqs ? " (" . $this->numberOfFaqs . ")" : false) . "</a></li>\n";
						$this->html .= "  <li role='presentation'><a href='#faqTags' aria-controls='faqTags' role='tab' data-toggle='tab'>FAQ Tags" . (count($this->allTags) ? " (" . count($this->allTags) . ")" : false) . "</a></li>\n";
						$this->html .= "</ul>\n";
						$this->html .= "<br />\n";

					// FAQs
						$this->html .= "<div class='tab-content'>\n";
						$this->html .= "  <div role='tabpanel' class='tab-pane active' id='faqs'>\n";

						if (!count($this->faqs)) $this->html .= "    <p>No FAQs yet.</p>\n";
						else {

							$this->html .= "    " . $form->start('editFaqsForm', null, 'post', null, null, array('onSubmit'=>'return false;')) . "\n";

							foreach ($this->faqs as $tag) {
								if (@$tag['tag']) $this->html .= "      <div class='tagName'>" . $tag['tag'] . "</div>\n\n";
								foreach ($tag['faqs'] as $faq) {
									$this->html .= "    <div class='form-group'>\n";
									$this->html .= "      <div class='col-lg-11 col-md-10 col-sm-8 col-xs-8'>\n";
									$this->html .= "        <a href='/" . $tl->page['template'] . "/edit_faq/" . $faq['faq_id'] . "'>" . $faq['question'] . "</a>\n";
									$this->html .= "      </div>\n";
									$this->html .= "      <div class='col-lg-1 col-md-2 col-sm-4 col-xs-4'>\n";
									$this->html .= "        <a href='/" . $tl->page['template'] . "/edit_faq/" . $faq['faq_id'] . "' class='btn btn-primary btn-block'>Edit</a>\n";
									$this->html .= "      </div>\n";
									$this->html .= "    </div>\n";
								}
							}

							$this->html .= "    " . $form->end() . "\n";

						}

						$this->html .= "    <br /><a href='/" . $tl->page['template'] . "/add_faq' class='btn btn-primary'>Add an FAQ</a>\n";

						$this->html .= "  </div>\n";

					// Tags
						$this->html .= "  <div role='tabpanel' class='tab-pane' id='faqTags'>\n";

						if (!count($this->allTags)) $this->html .= "    <p>No tags yet.</p>\n";
						else {

							$this->html .= "    " . $form->start('editTagsForm', null, 'post', null, null, array('onSubmit'=>'validateEditTagsForm(); return false;')) . "\n";
							$this->html .= $form->input('hidden', 'tagToDelete') . "\n";

							for ($counter = 0; $counter < count($this->allTags); $counter++) {
								$this->html .= "    <div class='form-group'>\n";
								$this->html .= "      <div class='col-lg-3 col-md-3 col-sm-3 col-xs-3'>\n";
								$this->html .= "        " . $form->input('number', 'position_' . $this->allTags[$counter]['tag_id'], $operators->firstTrue(@$_POST['position_' . $this->allTags[$counter]['tag_id']], $this->allTags[$counter]['position']), false, "|#", 'form-control', null, 2) . "\n";
								$this->html .= "      </div>\n";
								$this->html .= "      <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6'>\n";
								$this->html .= "        " . $form->input('text', 'tag_' . $this->allTags[$counter]['tag_id'], $operators->firstTrue(@$_POST['tag_' . $this->allTags[$counter]['tag_id']], $this->allTags[$counter]['tag']), true, "|Tag", 'form-control', null, 50) . "\n";
								$this->html .= "      </div>\n";
								$this->html .= "      <div class='col-lg-3 col-md-3 col-sm-3 col-xs-3'>\n";
								$this->html .= "        " . $form->input('button', 'deleteButton_' . $this->allTags[$counter]['tag_id'], null, false, "Delete", 'btn btn-danger btn-block', null, null, null, null, ['onClick'=>'if (confirm("Are you sure?")) { document.editTagsForm.tagToDelete.value="' . $this->allTags[$counter]['tag_id'] . '"; document.editTagsForm.submit(); } else { return false; };']) . "\n";
								$this->html .= "      </div>\n";
								$this->html .= "    </div>\n";
							}

							$this->html .= "    " . $form->input('submit', 'submitButton', null, false, "Save", 'btn btn-info') . "\n";
							$this->html .= "    " . $form->end();

						}

					$this->html .= "  </div>\n";
					$this->html .= "</div>\n";

			}

			public function editFAQ() {

				global $tl;
				global $_POST;
				global $logged_in;
				global $tablePrefix;

				$form = new form_TL();
				$operators = new operators_TL();
				$logger = new logger_TL();
				$parser = new parser_TL();
				$authentication_manager = new authentication_manager_TL();

				// check for errors
					if ($this->faqID && count($this->faqs[0]['faqs']) <> 1) $authentication_manager->forceRedirect('/' . $tl->page['template'] . "/screen/error=invalid_faq");

				// create selectable tags
					$this->retrieveTags();

					$allTags = array();
					for ($counter = 0; $counter < count($this->allTags); $counter++) {
						$allTags[$this->allTags[$counter]['tag_id']] = $this->allTags[$counter]['tag'];
					}

					$existingTags = array();
					for ($counter = 0; $counter < count($this->tags); $counter++) {
						$existingTags[$this->tags[$counter]['tag_id']] = $this->tags[$counter]['tag'];
					}

				// evaluate $_POST
					if (@$_POST) {

						if (@$_POST['formName'] == 'editFaqForm' && @$_POST['faqToDelete']) { // delete

							// clean input
								$_POST['faqToDelete'] = floatval($_POST['faqToDelete']);

							// check for errors
								if (!$_POST['faqToDelete']) $tl->page['error'] .= "Unable to retrieve FAQ to delete. ";

								if (!$tl->page['error']) {

									// delete FAQ
										$success = deleteFromDbSingle('faqs', ['faq_id'=>$_POST['faqToDelete']]);
										if (!$success) $tl->page['error'] .= "There was a problem deleting this FAQ. ";
										else {

											// delete tag associations
												deleteFromDb('faqs_x_faqtags', ['faq_id'=>$_POST['faqToDelete']]);

											// delete unused tags
												$this->deleteUnusedTags();
												
											// update log
												$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has deleted the FAQ &quot;" . $_POST['question_' . $_POST['faqToDelete']] . "&quot; (faq_id " . $_POST['faqToDelete'] . ")";
												$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'faq_id=' . $_POST['faqToDelete']));
												
											// redirect
												$authentication_manager->forceRedirect('/' . $tl->page['template'] . '/index/success=faq_deleted');

										}

								}

						}
						elseif (@$_POST['formName'] == 'editFaqForm') { // add or edit

							// clean input
								$_POST = $parser->trimAll($_POST);
								
							// check for errors
								if (!$_POST['question']) $tl->page['error'] .= "Please specify a question. ";
								if (!$_POST['answer']) $tl->page['error'] .= "Please specify an answer. ";

								if (!$tl->page['error']) {
									
									// save FAQ
										if ($this->faqID) updateDbSingle('faqs', ['question'=>$_POST['question'], 'answer'=>$_POST['answer']], ['faq_id'=>$this->faqID]);
										else $this->faqID = insertIntoDb('faqs', ['question'=>$_POST['question'], 'answer'=>$_POST['answer']]);

									// update tags
										deleteFromDb('faqs_x_faqtags', ['faq_id'=>$this->faqID]);
										for ($counter = 0; $counter < count($_POST['tags']); $counter++) {
											if (@$allTags[$_POST['tags'][$counter]]) { // tag already exists
												insertIntoDb('faqs_x_faqtags', ['faq_id'=>$this->faqID, 'tag_id'=>$_POST['tags'][$counter]]);
											}
											else { // tag is new
												$tagID = insertIntoDb('faqtags', ['tag'=>$_POST['tags'][$counter], 'position'=>count($allTags) + 1]);
												insertIntoDb('faqs_x_faqtags', ['faq_id'=>$this->faqID, 'tag_id'=>$tagID]);
											}
										}

									// delete unused tags
										$this->deleteUnusedTags();

									// update log
										$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated FAQs";
										$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id']));
									
									// redirect
										$authentication_manager->forceRedirect('/' . $tl->page['template'] . '/index/success=faqs_updated');

								}

						}

					}

				// draw form
					$this->html .= "    <h2>Edit FAQ</h2>\n";

					$this->html .= $form->start('editFaqForm', null, 'post', null, null, array('onSubmit'=>'validateEditFaqForm(); return false;'));
					$this->html .= $form->input('hidden', 'faqToDelete') . "\n";
					/* Question */		$this->html .= $form->row('text', 'question', $operators->firstTrue(@$_POST['question'], @$this->faqs[0]['faqs'][0]['question']), true, "Question", 'form-control', 255);
					/* Answer */		$this->html .= $form->row('textarea', 'answer', $operators->firstTrue(@$_POST['answer'], @$this->faqs[0]['faqs'][0]['answer']), true, "Answer", 'form-control');
					/* Tags */			$this->html .= $form->row('select', 'tags', $operators->firstTrue(@$_POST['tags'], $existingTags), false, "Tags", 'form-control js-example-basic-multiple', $allTags, null, array('multiple'=>'multiple', 'data-tags'=>'true'));
					/* Actions */		$this->html .= $form->rowStart('actions');
										$this->html .= "  <div class='row'>\n";
										$this->html .= "    <div class='col-lg-10 col-md-9 col-sm-9 col-xs-6'>\n";
										$this->html .= "      " . $form->input('submit', 'submit_button', null, false, "Save", 'btn btn-primary') . "\n";
										$this->html .= "      " . $form->input('cancel_and_return', 'cancel_button', null, false, "Cancel", 'btn btn-link') . "\n";
										$this->html .= "    </div>\n";
										if ($this->faqID) {
											$this->html .= "    <div class='col-lg-2 col-md-3 col-sm-3 col-xs-6 text-right'>\n";
											$this->html .= "      " . $form->input('button', 'delete_button', null, false, "Delete", 'btn btn-danger', null, null, null, null, ['onClick'=>'if (confirm("Are you sure?")) { document.editFaqForm.faqToDelete.value = "' . $this->faqs[0]['faqs'][0]['faq_id'] . '"; document.editFaqForm.submit(); } else { return false; };']) . "\n";
											$this->html .= "    </div>\n";
										}
										$this->html .= "  </div>\n";
										$this->html .= $form->rowEnd();
					$this->html .= $form->end();

					$this->js .= "// initialize Select2\n";
					$this->js .= "  $('.js-example-basic-multiple').select2();\n";

					$this->js .= "// form validation\n";
					$this->js .= "  var errorMessage = '';\n";
					$this->js .= "  function validateEditFaqForm() {\n";
					$this->js .= "    if (!document.editFaqForm.question.value) errorMessage += 'Please specify a question. ';\n";
					$this->js .= "    if (!document.editFaqForm.answer.value) errorMessage += 'Please specify an answer. ';\n";
					$this->js .= "    if (errorMessage) alert(errorMessage);\n";
					$this->js .= "    else document.editFaqForm.submit();\n";
					$this->js .= "  }\n";

					$this->css .= ".js-example-basic-multiple > li { list-style-type: none; }\n";
			}

		/*	----------------------------------------------
			Tags
			----------------------------------------------	*/

			public function retrieveTags() {

				global $tl;
				global $tablePrefix;

				if ($this->faqID) {
					// retrieve tags associated with a specific FAQ
						$query = "SELECT " . $tablePrefix . "faqs_x_faqtags.tag_id AS tag_id,";
						$query .= " " . $tablePrefix . "faqtags.tag AS tag";
						$query .= " FROM " . $tablePrefix . "faqs_x_faqtags";
						$query .= " LEFT JOIN " . $tablePrefix . "faqtags ON " . $tablePrefix . "faqs_x_faqtags.tag_id = " . $tablePrefix . "faqtags.tag_id";
						$query .= " WHERE " . $tablePrefix . "faqs_x_faqtags.faq_id = '" . $this->faqID . "'";
						$query .= " ORDER BY " . $tablePrefix . "faqtags.position ASC";
						
						$this->tags = directlyQueryDb($query);
				}

				// retrieve all tags
					$this->allTags = retrieveFromDb('faqtags', null, null, null, null, null, null, null, 'position ASC');

			}

			public function deleteUnusedTags() {

				global $tablePrefix;


				$query = "SELECT " . $tablePrefix . "faqtags.tag_id AS tag_id";
				$query .= " FROM " . $tablePrefix . "faqtags";
				$query .= " WHERE (SELECT COUNT(*) FROM " . $tablePrefix . "faqs_x_faqtags WHERE " . $tablePrefix . "faqs_x_faqtags.tag_id = " . $tablePrefix . "faqtags.tag_id) < 1";
				
				$result = directlyQueryDb($query);

				for ($counter = 0; $counter < count($result); $counter++) {
					deleteFromDbSingle('faqtags', ['tag_id'=>$result[$counter]['tag_id']]);
				}

			}

		/*	----------------------------------------------
			Search
			----------------------------------------------	*/

			public function displaySearchForm() {

				global $tl;

				$form = new form_TL();

				// display form
					$form->styleForm('inline');
					$this->searchFormHtml .= $form->start('searchFaqForm', null, 'post', null, null, array('onSubmit'=>'sanitizeSearchFaqForm(); return false;')) . "\n";
					/* Keywords */		$this->searchFormHtml .= $form->input('search', 'keywords', urldecode($this->keywords), false, null, 'form-control') . "\n";
					/* Actions */		$this->searchFormHtml .= $form->input('submit', 'search', null, false, 'Search', 'btn btn-primary') . "\n";
										$this->searchFormHtml .= "<a href='/" . $tl->page['template'] . "' class='btn btn-link'>Reset</a>\n";
					$this->searchFormHtml .= $form->end() . "\n";

					$this->js .= "function sanitizeSearchFaqForm() {\n";
					$this->js .= "  var acceptableCharacters = " . '"' . "abcdefghijklmnopqrstuvwxyz0123456789'- " . '"' . ";\n";
					$this->js .= "  var unsanitized = document.getElementById('keywords').value.toLowerCase();\n";
					$this->js .= "  var sanitized = '';\n";
					$this->js .= "  for (counter = 0; counter < unsanitized.length; counter++) {\n";
					$this->js .= "    if (acceptableCharacters.indexOf(unsanitized.substring(counter, counter + 1)) >= 0) sanitized += unsanitized.substring(counter, counter + 1);\n";
					$this->js .= "  }\n";
					$this->js .= "  document.location.href = '/" . $tl->page['template'] . "/' + encodeURIComponent(sanitized);\n";
					$this->js .= "}\n";


			}

	}

?>
