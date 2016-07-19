<?php

	if (@$filters['screen'] == 'edit') {

		echo "<h2>" . (@$content ? "Edit" : "Add") . " " . ucwords($types[$filters['type']]) . "</h2>\n";

		echo $form->start('editContentForm', null, 'post', null, array('enctype'=>'multipart/form-data'), array('onSubmit'=>'validateEditContentForm(); return false;')) . "\n";
		echo $form->input('hidden', 'deleteContent') . "\n";
		echo $form->input('hidden', 'contentType', @$filters['type']) . "\n";
		echo $form->input('hidden', 'id', @$filters['id']) . "\n";

		if ($filters['type'] == 'p') {
		
			echo "<ul class='nav nav-tabs' role='tablist'>\n";
			echo "  <li role='presentation' class='active'><a href='#details' aria-controls='details' role='tab' data-toggle='tab'>Details</a></li>\n";
			echo "  <li role='presentation'><a href='#preview' aria-controls='preview' role='tab' data-toggle='tab'>Preview</a></li>\n";
			echo "</ul>\n";
			echo "<br />\n";

			echo "<div class='tab-content'>\n";
			echo "  <div role='tabpanel' class='tab-pane active' id='details'>\n";
			
			/* Title */ 		echo $form->row('text', 'title', $operators->firstTrue(@$_POST['title'], @$content[0]['title']), false, 'Title', 'form-control', '', 150);
			/* Slug */ 			echo $form->rowStart('slug', 'URL');
								echo "  <div class='input-group'><span class='input-group-addon'>http://" . $tl->page['domain'] . "/</span>\n";
								echo "    " . $form->input('text', 'slug', $operators->firstTrue(@$_POST['slug'], @$content[0]['slug']), true, '|Short unique descriptor (no spaces)', 'form-control', null, 255) . "\n";
								echo "  </div>\n";
								echo $form->rowEnd();
			/* Language */ 		echo $form->row('language', 'language_id', $operators->firstTrue(@$_POST['language_id'], @$content[0]['language_id'], $systemPreferences['Default language']), true, 'Language', 'form-control');
			/* Pseudonym */ 	if (count($allPseudonyms)) echo $form->row('select', 'pseudonym_id', $operators->firstTrue(@$_POST['pseudonym_id'], @$content[0]['pseudonym_id'], @$pseudonym['pseudonym_id']), false, 'Pseudonym', 'form-control', $allPseudonyms);
			/* Content */		echo $form->row('textarea', 'content', $operators->firstTrue(@$_POST['content'], @$content[0]['content']), false, 'Content|Plain text or HTML', 'form-control', null, null, array('rows'=>'10'), null, array('onChange'=>'document.getElementById("preview").innerHTML=this.value;'));
							
			echo "  <div id='more-fields-container' class='collapse" . (@$_POST['login_required'] || @$content[0]['login_required'] || @$_POST['content_js'] || @$content[0]['content_js'] || @$_POST['content_css'] || @$content[0]['content_css'] ? " in" : false) . "'>\n";
			
			/* Login */			echo $form->row('yesno_bootstrap_switch', 'login_required', $operators->firstTrue(@$_POST['login_required'], @$content[0]['login_required']), false, 'Login required');
			/* JavaScript */	echo $form->row('textarea', 'content_js', $operators->firstTrue(@$_POST['content_js'], @$content[0]['content_js']), false, 'JavaScript|Runs at pageload', 'form-control', null, null, array('rows'=>'7'));
			/* CSS */			echo $form->row('textarea', 'content_css', $operators->firstTrue(@$_POST['content_css'], @$content[0]['content_css']), false, 'CSS', 'form-control', null, null, array('rows'=>'7'));
			
			echo "  </div>\n";

			/* Actions */		echo "  <div class='row'>\n";
								echo "    <div class='col-md-10 col-sm-10 col-xs-8'>\n";
								/* Save */		echo "      " . $form->input('submit', 'save_button', null, false, 'Save', 'btn btn-info') . "\n";
								/* More */		if ($filters['type'] == 'p') echo "      " . $form->input('button', 'more', null, false, 'More', 'btn btn-link', null, null, array('data-target'=>'#more-fields-container', 'data-toggle'=>'collapse')) . "\n";
								/* Cancel */	echo "      <a href='/admin_content' class='btn btn-link'>Cancel</a>\n";
								echo "      </div>\n";
								/* Delete */	if (@$content[0]['deletable']) echo "    <div class='col-md-2 col-sm-2 col-xs-4 text-right'>" . $form->input('button', 'delete_button', null, false, 'Delete', 'btn btn-danger', '', '', '', '', array('onClick'=>'deleteThisContent(); return false;')) . "</div>\n";
								echo "  </div>\n";

			echo "  </div>\n";
			echo "  <div role='tabpanel' class='tab-pane' id='preview'>\n";
			echo $operators->firstTrue(@$_POST['content'], @$content[0]['content']);
			echo "  </div>\n";
			echo "</div>\n";

		}
		elseif ($filters['type'] == 'b') {

			echo "<ul class='nav nav-tabs' role='tablist'>\n";
			echo "  <li role='presentation' class='active'><a href='#details' aria-controls='details' role='tab' data-toggle='tab'>Details</a></li>\n";
			echo "  <li role='presentation'><a href='#preview' aria-controls='preview' role='tab' data-toggle='tab'>Preview</a></li>\n";
			echo "</ul>\n";
			echo "<br />\n";

			echo "<div class='tab-content'>\n";
			echo "  <div role='tabpanel' class='tab-pane active' id='details'>\n";
			
			/* Slug */		echo "  " . $form->row('text', 'slug', $operators->firstTrue(@$_POST['slug'], @$content[0]['slug']), true, 'Slug|Short unique descriptor', 'form-control', null, 150) . "\n";
			/* Language */ 	echo $form->row('language', 'language_id', $operators->firstTrue(@$_POST['language_id'], @$content[0]['language_id'], $systemPreferences['Default language']), true, 'Language', 'form-control');
			/* Pseudonym */ 	if (count($allPseudonyms)) echo $form->row('select', 'pseudonym_id', $operators->firstTrue(@$_POST['pseudonym_id'], @$content[0]['pseudonym_id'], @$pseudonym['pseudonym_id']), false, 'Pseudonym', 'form-control', $allPseudonyms);
			/* Content */	echo $form->row('textarea', 'content', $operators->firstTrue(@$_POST['content'], @$content[0]['content']), false, 'Content|Plain text or HTML', 'form-control', null, null, array('rows'=>'10'), null, array('onChange'=>'document.getElementById("preview").innerHTML=this.value;'));
			/* Actions */		echo "  <div class='row'>\n";
								echo "    <div class='col-md-10 col-sm-10 col-xs-8'>\n";
								/* Save */		echo "      " . $form->input('submit', 'save_button', null, false, 'Save', 'btn btn-info') . "\n";
								/* Cancel */	echo "      <a href='/admin_content' class='btn btn-link'>Cancel</a>\n";
								echo "      </div>\n";
								/* Delete */	if (@$content[0]['deletable']) echo "    <div class='col-md-2 col-sm-2 col-xs-4 text-right'>" . $form->input('button', 'delete_button', null, false, 'Delete', 'btn btn-danger', '', '', '', '', array('onClick'=>'deleteThisContent(); return false;')) . "</div>\n";
								echo "  </div>\n";

			echo "  </div>\n";
			echo "  <div role='tabpanel' class='tab-pane' id='preview'>\n";
			echo $operators->firstTrue(@$_POST['content'], @$content[0]['content']);
			echo "  </div>\n";
			echo "</div>\n";

		}
		elseif ($filters['type'] == 'f' && !@$content) {
			/* Upload */	echo "  <div>" . $form->input('file_dropzone', 'cmsFileUpload', null, false, null, 'form-control', null, null, array('message'=>"Drag or click here to upload...", 'max_files'=>1, 'destination_path'=>'trash/' . date('Y-m-d_H-i-s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') + 1, date('Y'))), 'events'=>['success'=>'$("#hidden_submit").collapse("show"); $("#visible_cancel").collapse("hide");'])) . "</div><br />";
			/* Actions */	echo "  <div id='hidden_submit' class='collapse'>\n";
							/* Save */		echo "    " . $form->input('submit', 'save_button', null, false, 'Save', 'btn btn-info') . "\n";
							/* Cancel */	echo "      <a href='/admin_content' class='btn btn-link'>Cancel</a>\n";
							echo "  </div>\n";
							echo "  <div id='visible_cancel' class='collapse in'>\n";
							/* Cancel */	echo "      <a href='/admin_content' class='btn btn-link'>Cancel</a>\n";
							echo "  </div>\n";
		}
		elseif ($filters['type'] == 'f' && @$content) {

			echo "<ul class='nav nav-tabs' role='tablist'>\n";
			echo "  <li role='presentation' class='active'><a href='#details' aria-controls='details' role='tab' data-toggle='tab'>Details</a></li>\n";
			echo "  <li role='presentation'><a href='#preview' aria-controls='preview' role='tab' data-toggle='tab'>Preview</a></li>\n";
			echo "</ul>\n";
			echo "<br />\n";

			echo "<div class='tab-content'>\n";
			echo "  <div role='tabpanel' class='tab-pane active' id='details'>\n";
			
			/* Slug */		echo $form->row('text', 'slug', $operators->firstTrue(@$_POST['slug'], $content[0]['slug']), true, 'Filename', 'form-control', null, 255) . "\n";
			/* Metadata */	if (@$metadata) {
								foreach ($metadata as $key=>$value) {
									if ($value) echo "  " . $form->row('uneditable_static', $key, $value, false, $key) . "\n";
								}
							}
			/* Actions */		echo "  <div class='row'>\n";
								echo "    <div class='col-md-10 col-sm-10 col-xs-8'>\n";
								/* Save */		echo "      " . $form->input('submit', 'save_button', null, false, 'Save', 'btn btn-info') . "\n";
								/* Cancel */	echo "      <a href='/admin_content' class='btn btn-link'>Cancel</a>\n";
								echo "      </div>\n";
								/* Delete */	if (@$content[0]['deletable']) echo "    <div class='col-md-2 col-sm-2 col-xs-4 text-right'>" . $form->input('button', 'delete_button', null, false, 'Delete', 'btn btn-danger', '', '', '', '', array('onClick'=>'deleteThisContent(); return false;')) . "</div>\n";
								echo "  </div>\n";

			echo "  </div>\n";
			echo "  <div role='tabpanel' class='tab-pane' id='preview'>\n";
			if ($file_manager->isImage("assets/cms_files/" . date('YmdHis', strtotime($content[0]['saved_on'])) . '/' . $content[0]['slug'])) {
				echo "<center><img src='/assets/cms_files/" . date('YmdHis', strtotime($content[0]['saved_on'])) . '/' . $content[0]['slug'] . "' class='img-responsive' /></center>\n";
			}
			elseif ($file_manager->isPDF("assets/cms_files/" . date('YmdHis', strtotime($content[0]['saved_on'])) . '/' . $content[0]['slug'])) {
				echo "<iframe src='http://docs.google.com/gview?url=" . urlencode($tl->page['protocol'] . $tl->page['root'] . "/assets/cms_files/" . date('YmdHis', strtotime($content[0]['saved_on'])) . "/" . $content[0]['slug']) . "&embedded=true' width='100%' height='500' frameborder='0'>Attempting to display...</iframe>\n";
			}
			elseif (@$metadata['File extension'] == 'docx' || @$metadata['File extension'] == 'doc' || @$metadata['File extension'] == 'xlsx' || @$metadata['File extension'] == 'xls' || @$metadata['File extension'] == 'pptx' || @$metadata['File extension'] == 'ppt') {
				echo "<iframe src='https://view.officeapps.live.com/op/embed.aspx?src=" . urlencode($tl->page['protocol'] . $tl->page['root'] . "/assets/cms_files/" . date('YmdHis', strtotime($content[0]['saved_on'])) . "/" . $content[0]['slug']) . "' width='100%' height='500' frameborder='0'>Attempting to display...</iframe>\n";
			}
			else {
				echo "<br /><br /><br /><br /><br /><center>No preview available</center>\n";
			}
			echo "  </div>\n";
			echo "</div>\n";

		}
		elseif ($filters['type'] == 'r') {
			/* Slug */ 			echo $form->rowStart('slug', 'URL');
								echo "  <div class='input-group'><span class='input-group-addon'>http://" . $tl->page['domain'] . "/</span>\n";
								echo "    " . $form->input('text', 'slug', $operators->firstTrue(@$_POST['slug'], @$content[0]['slug']), true, null, 'form-control', null, 255) . "\n";
								echo "  </div>\n";
								echo $form->rowEnd();
			/* Destination */	echo $form->row('url', 'redirect_to', $operators->firstTrue(@$_POST['redirect_to'], @$content[0]['redirect_to']), true, 'Redirect to', 'form-control', null, 255) . "\n";
			/* HTTP */ 			echo $form->row('select', 'http_status', $operators->firstTrue(@$_POST['http_status'], @$content[0]['http_status']), false, 'Status code', 'form-control', $allStatuses);
			/* Actions */		echo "  <div class='row'>\n";
								echo "    <div class='col-md-10 col-sm-10 col-xs-8'>\n";
								/* Save */		echo "      " . $form->input('submit', 'save_button', null, false, 'Save', 'btn btn-info') . "\n";
								/* Cancel */	echo "      <a href='/admin_content' class='btn btn-link'>Cancel</a>\n";
								echo "      </div>\n";
								/* Delete */	if (@$content[0]['deletable']) echo "    <div class='col-md-2 col-sm-2 col-xs-4 text-right'>" . $form->input('button', 'delete_button', null, false, 'Delete', 'btn btn-danger', '', '', '', '', array('onClick'=>'deleteThisContent(); return false;')) . "</div>\n";
								echo "  </div>\n";
		}
		elseif ($filters['type'] == 'm') {
			/* Type */		echo $form->row('select', 'message_type', $operators->firstTrue(@$_POST['message_type'], @$content[0]['message_type']), true, 'Type', 'form-control', array('s'=>'Success', 'w'=>'Warning', 'e'=>'Error'));
			/* Slug */		echo "  " . $form->row('text', 'slug', $operators->firstTrue(@$_POST['slug'], @$content[0]['slug']), true, 'Slug|Will appear in URL', 'form-control', null, 150) . "\n";
			/* Language */ 	echo $form->row('language', 'language_id', $operators->firstTrue(@$_POST['language_id'], @$content[0]['language_id'], $systemPreferences['Default language']), true, 'Language', 'form-control');
			/* Pseudonym */ 	if (count($allPseudonyms)) echo $form->row('select', 'pseudonym_id', $operators->firstTrue(@$_POST['pseudonym_id'], @$content[0]['pseudonym_id'], @$pseudonym['pseudonym_id']), false, 'Pseudonym', 'form-control', $allPseudonyms);
			/* Content */	echo $form->row('textarea', 'content', $operators->firstTrue(@$_POST['content'], @$content[0]['content']), false, 'Message', 'form-control', null, null, array('rows'=>'10'));
			/* Actions */		echo "  <div class='row'>\n";
								echo "    <div class='col-md-10 col-sm-10 col-xs-8'>\n";
								/* Save */		echo "      " . $form->input('submit', 'save_button', null, false, 'Save', 'btn btn-info') . "\n";
								/* Cancel */	echo "      <a href='/admin_content' class='btn btn-link'>Cancel</a>\n";
								echo "      </div>\n";
								/* Delete */	if (@$content[0]['deletable']) echo "    <div class='col-md-2 col-sm-2 col-xs-4 text-right'>" . $form->input('button', 'delete_button', null, false, 'Delete', 'btn btn-danger', '', '', '', '', array('onClick'=>'deleteThisContent(); return false;')) . "</div>\n";
								echo "  </div>\n";
		}
		elseif ($filters['type'] == 'e') {

			echo "<ul class='nav nav-tabs' role='tablist'>\n";
			echo "  <li role='presentation' class='active'><a href='#details' aria-controls='details' role='tab' data-toggle='tab'>Details</a></li>\n";
			echo "  <li role='presentation'><a href='#preview_html' aria-controls='preview' role='tab' data-toggle='tab'>Preview (HTML)</a></li>\n";
			echo "  <li role='presentation'><a href='#preview_plain' aria-controls='preview' role='tab' data-toggle='tab'>Preview (Non-HTML)</a></li>\n";
			echo "</ul>\n";
			echo "<br />\n";

			echo "<div class='tab-content'>\n";
			echo "  <div role='tabpanel' class='tab-pane active' id='details'>\n";
			
			/* Slug */				echo "  " . $form->row('text', 'slug', $operators->firstTrue(@$_POST['slug'], @$content[0]['slug'], "[" . $systemPreferences['Name of this application'] . "]"), true, 'Subject', 'form-control', null, 255) . "\n";
			/* Language */ 			echo $form->row('language', 'language_id', $operators->firstTrue(@$_POST['language_id'], @$content[0]['language_id'], $systemPreferences['Default language']), true, 'Language', 'form-control');
			/* Pseudonym */ 		if (count($allPseudonyms)) echo $form->row('select', 'pseudonym_id', $operators->firstTrue(@$_POST['pseudonym_id'], @$content[0]['pseudonym_id'], @$pseudonym['pseudonym_id']), false, 'Pseudonym', 'form-control', $allPseudonyms);
			/* Content (HTML) */	echo $form->row('textarea', 'content', $operators->firstTrue(@$_POST['content'], @$content[0]['content']), false, 'Content (HTML)', 'form-control', null, null, array('rows'=>'10'), null, array('onChange'=>'document.getElementById("preview_html").innerHTML=highlightMergeFields(this.value);'));
			/* Content (Plain) */	echo $form->row('textarea', 'content_plain', $operators->firstTrue(@$_POST['content_plain'], @$content[0]['content_plain']), false, 'Content (Non-HTML)', 'form-control', null, null, array('rows'=>'10'), null, ['onChange'=>'document.getElementById("preview_plain").innerHTML=nl2br_TL(highlightMergeFields(this.value));']);
									echo "  " . $form->rowStart('tip');
									echo "  <div class='text-muted'>Use {{ and }} to enclose merge fields. To avoid formatting conflicts, use quotation marks rather than apostrophes within HTML tags.</div>\n";
									echo "  " . $form->rowEnd();
			/* Actions */			echo "  <div class='row'>\n";
									echo "    <div class='col-md-10 col-sm-10 col-xs-8'>\n";
									/* Save */		echo "      " . $form->input('submit', 'save_button', null, false, 'Save', 'btn btn-info') . "\n";
									/* Cancel */	echo "      <a href='/admin_content' class='btn btn-link'>Cancel</a>\n";
									echo "      </div>\n";
									/* Delete */	if (@$content[0]['deletable']) echo "    <div class='col-md-2 col-sm-2 col-xs-4 text-right'>" . $form->input('button', 'delete_button', null, false, 'Delete', 'btn btn-danger', '', '', '', '', array('onClick'=>'deleteThisContent(); return false;')) . "</div>\n";
									echo "  </div>\n";

			echo "  </div>\n";

			$mergeFieldStart = "<span class='label label-default'>";
			$mergeFieldEnd = "</span>";

			echo "  <div role='tabpanel' class='tab-pane' id='preview_html'>\n";
			echo str_replace("{{", $mergeFieldStart, str_replace("}}", $mergeFieldEnd, $operators->firstTrue(@$_POST['content'], @$content[0]['content'])));
			echo "  </div>\n";
			echo "  <div role='tabpanel' class='tab-pane' id='preview_plain'>\n";
			echo nl2br(str_replace("{{", $mergeFieldStart, str_replace("}}", $mergeFieldEnd, $operators->firstTrue(@$_POST['content_plain'], @$content[0]['content_plain']))));
			echo "  </div>\n";
			echo "</div>\n";

			$pageJavaScript .= "function highlightMergeFields(input) {\n";
			$pageJavaScript .= "  output = input.replace(/{{/g, " . '"' . $mergeFieldStart . '"' . ");\n";
			$pageJavaScript .= "  output = output.replace(/}}/g, " . '"' . $mergeFieldEnd . '"' . ");\n";
			$pageJavaScript .= "  return output;\n";
			$pageJavaScript .= "}\n\n";

		}
  			
		echo $form->end();

	}
	else { // index

		echo "<div class='row'>\n";
		echo "  <div class='col-lg-6 col-md-6 col-sm-6 col-xs-12 pageModule'>\n";

		// virtual pages
			echo "    <h2>Pages <a href='/admin_content/" . urlencode("screen=edit|type=p") . "' class='btn btn-default btn-xs'>Add</a></h2>\n";
			if (count($pages) < 1) echo "    <p>None yet...</p>\n";
			else {
				echo "    <ul>\n";
				for ($counter = 0; $counter < count($pages); $counter++) {
					echo "    <li>\n";
					if ($pages[$counter]['language']) echo "      <span class='label label-default'>" . $pages[$counter]['language'] . "</span>\n";
					if ($pages[$counter]['pseudonym_id']) echo "      <span class='label label-default'>" . $pages[$counter]['pseudonym_name'] . "</span>\n";
					echo "      <a href='/admin_content/" . urlencode("screen=edit|type=p|id=" . $pages[$counter]['cms_id']) . "'>" . $pages[$counter]['title'] . "</a>\n";
					echo "    </li>\n";
				}
				echo "    </ul>\n";
			}

		echo "  </div>\n";
		echo "  <div class='col-lg-6 col-md-6 col-sm-6 col-xs-12 pageModule'>\n";
		
		// files
			echo "      <h2>Files <a href='/admin_content/" . urlencode("screen=edit|type=f") . "' class='btn btn-default btn-xs'>Add</a></h2></h2>\n";
			if (count($files) < 1) echo "      <p>None yet...</p>\n";
			else {
				echo "    <ul>\n";
				for ($counter = 0; $counter < count($files); $counter++) {
					echo "    <li>\n";
					echo "      <a href='/admin_content/" . urlencode("screen=edit|type=f|id=" . $files[$counter]['cms_id']) . "'>" . substr($files[$counter]['slug'], strpos($files[$counter]['slug'], '/')) . "</a> &nbsp;\n";
					echo "    </li>\n";
				}
				echo "    </ul>\n";
			}

		echo "  </div>\n";
		echo "</div>\n";

		echo "<div class='row'>\n";
		echo "  <div class='col-lg-6 col-md-6 col-sm-6 col-xs-12 pageModule'>\n";
	
		// content blocks
			echo "    <h2>Content blocks <a href='/admin_content/" . urlencode("screen=edit|type=b") . "' class='btn btn-default btn-xs'>Add</a></h2></h2>\n";
			if (count($blocks) < 1) echo "    <p>None yet...</p>\n";
			else {
				echo "    <ul>\n";
				for ($counter = 0; $counter < count($blocks); $counter++) {
					echo "    <li>\n";
					if ($blocks[$counter]['language']) echo "      <span class='label label-default'>" . $blocks[$counter]['language'] . "</span>\n";
					if ($blocks[$counter]['pseudonym_id']) echo "      <span class='label label-default'>" . $blocks[$counter]['pseudonym_name'] . "</span>\n";
					echo "      <a href='/admin_content/" . urlencode("screen=edit|type=b|id=" . $blocks[$counter]['cms_id']) . "'>" . $blocks[$counter]['slug'] . "</a>\n";
					echo "    </li>\n";
				}
				echo "    </ul>\n";
			}

		echo "  </div>\n";
		echo "  <div class='col-lg-6 col-md-6 col-sm-6 col-xs-12 pageModule'>\n";

		// messages
			echo "    <h2>Messages <a href='/admin_content/" . urlencode("screen=edit|type=m") . "' class='btn btn-default btn-xs'>Add</a></h2></h2>\n";
			if (count($messages) < 1) echo "    <p>None yet...</p>\n";
			else {
				echo "    <ul>\n";
				for ($counter = 0; $counter < count($messages); $counter++) {
					echo "    <li>\n";
					if ($messages[$counter]['language']) echo "      <span class='label label-default'>" . $messages[$counter]['language'] . "</span>\n";
					if ($messages[$counter]['pseudonym_id']) echo "      <span class='label label-default'>" . $messages[$counter]['pseudonym_name'] . "</span>\n";
					echo "      ";
					if ($messages[$counter]['message_type'] == 's') echo "<span class='label label-success'>Success</span>\n";
					elseif ($messages[$counter]['message_type'] == 'w') echo "<span class='label label-warning'>Warning</span>\n";
					elseif ($messages[$counter]['message_type'] == 'e') echo "<span class='label label-danger'>Error</span>\n";
					echo "      <a href='/admin_content/" . urlencode("screen=edit|type=m|id=" . $messages[$counter]['cms_id']) . "'>" . $messages[$counter]['slug'] . "</a>\n";
					echo "    </li>\n";
				}
				echo "    </ul>\n";
			}

		echo "  </div>\n";
		echo "</div>\n";

		echo "<div class='row'>\n";
		echo "  <div class='col-lg-6 col-md-6 col-sm-6 col-xs-12 pageModule'>\n";

		// redirects
			echo "      <h2>Redirects <a href='/admin_content/" . urlencode("screen=edit|type=r") . "' class='btn btn-default btn-xs'>Add</a></h2></h2>\n";
			if (count($redirects) < 1) echo "      <p>None yet...</p>\n";
			else {
				echo "    <ul>\n";
				for ($counter = 0; $counter < count($redirects); $counter++) {
					echo "    <li>\n";
					echo "      " . $redirects[$counter]['http_status'] . ": " . "<a href='/admin_content/" . urlencode("screen=edit|type=r|id=" . $redirects[$counter]['cms_id']) . "'>" . $redirects[$counter]['slug'] . "</a>\n";
					echo "    </li>\n";
				}
				echo "    </ul>\n";
			}

		echo "  </div>\n";
		echo "  <div class='col-lg-6 col-md-6 col-sm-6 col-xs-12 pageModule'>\n";

		// emails
			echo "      <h2>Emails <a href='/admin_content/" . urlencode("screen=edit|type=e") . "' class='btn btn-default btn-xs'>Add</a></h2></h2>\n";
			if (count($emails) < 1) echo "      <p>None yet...</p>\n";
			else {
				echo "    <ul>\n";
				for ($counter = 0; $counter < count($emails); $counter++) {
					echo "    <li>\n";
					if ($emails[$counter]['language']) echo "      <span class='label label-default'>" . $emails[$counter]['language'] . "</span>\n";
					if ($emails[$counter]['pseudonym_id']) echo "      <span class='label label-default'>" . $emails[$counter]['pseudonym_name'] . "</span>\n";
					echo "      <a href='/admin_content/" . urlencode("screen=edit|type=e|id=" . $emails[$counter]['cms_id']) . "'>" . $emails[$counter]['slug'] . "</a>\n";
					echo "    </li>\n";
				}
				echo "    </ul>\n";
			}

		echo "  </div>\n";
		echo "</div>\n";
			
	}
 
?>