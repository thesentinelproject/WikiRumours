<?php

	if ($screen == 'menu') {
		
		// Virtual pages
			echo "  <div class='pageModule'>\n";
			echo "    " . $form->start() . "\n";
			echo "    <h2>Pages</h2>\n";
			if (count($pages) < 1) echo "    <p>None yet...</p>\n";
			else {
				echo "    <ul>\n";
				for ($counter = 0; $counter < count($pages); $counter++) {
					echo "    <li>\n";
					if ($pages[$counter]['language']) echo "      <span class='label label-default'>" . $pages[$counter]['language'] . "</span>\n";
					if ($pages[$counter]['pseudonym_id']) echo "      <span class='label label-default'>" . $pages[$counter]['pseudonym_name'] . "</span>\n";
					echo "      <a href='/admin_content/" . $pages[$counter]['cms_id'] . "'>" . $pages[$counter]['title'] . "</a>\n";
					echo "    </li>\n";
				}
				echo "    </ul>\n";
			}
			echo "    <div>" . $form->input('button', 'add_a_page', null, false, 'Create page', 'btn btn-info', '', '', '', '', array('onClick'=>'document.location.href="/admin_content/add_page"; return false;')) . "</div>\n";
			echo "    " . $form->end() . "\n";
			echo "  </div>\n";
		
		// content blocks
			echo "  <div class='pageModule'>\n";
			echo "    " . $form->start() . "\n";
			echo "    <h2>Content blocks</h2>\n";
			if (count($blocks) < 1) echo "    <p>None yet...</p>\n";
			else {
				echo "    <ul>\n";
				for ($counter = 0; $counter < count($blocks); $counter++) {
					echo "    <li>\n";
					if ($blocks[$counter]['language']) echo "      <span class='label label-default'>" . $blocks[$counter]['language'] . "</span>\n";
					if ($blocks[$counter]['pseudonym_id']) echo "      <span class='label label-default'>" . $blocks[$counter]['pseudonym_name'] . "</span>\n";
					echo "      <a href='/admin_content/" . $blocks[$counter]['cms_id'] . "'>" . $blocks[$counter]['slug'] . "</a>\n";
					echo "    </li>\n";
				}
				echo "    </ul>\n";
			}
			echo "    <div>" . $form->input('button', 'add_a_block', null, false, 'Create content block', 'btn btn-info', '', '', '', '', array('onClick'=>'document.location.href="/admin_content/add_block"; return false;')) . "</div>\n";
			echo "    " . $form->end() . "\n";
			echo "  </div>\n";
		
		// files
			echo "  <div class='pageModule'>\n";
			echo "    " . $form->start() . "\n";
			echo "      <h2>Files</h2>\n";
			if (count($files) < 1) echo "      <p>None yet...</p>\n";
			else {
				echo "    <ul>\n";
				for ($counter = 0; $counter < count($files); $counter++) {
					echo "    <li>\n";
					echo "      <a href='/admin_content/" . $files[$counter]['cms_id'] . "'>" . substr($files[$counter]['slug'], strpos($files[$counter]['slug'], '/')) . "</a> &nbsp;\n";
					echo "    </li>\n";
				}
				echo "    </ul>\n";
			}
			echo "    <div>" . $form->input('button', 'add_a_file', null, false, 'Add file', 'btn btn-info', '', '', '', '', array('onClick'=>'document.location.href="/admin_content/add_file"; return false;')) . "</div>\n";
			echo "    " . $form->end() . "\n";
			echo "  </div>\n";
			
	}
	else {
		
		if ($screen == 'add_page') echo "<h2>Add a virtual page</h2>\n";
		elseif ($screen == 'edit_page') echo "<h2>Editing the page &quot;" . $cms[0]['title'] . "&quot;</h2>\n";
		elseif ($screen == 'add_block') echo "<h2>Add a content block</h2>\n";
		elseif ($screen == 'edit_block') echo "<h2>Editing the content block &quot;" . $cms[0]['slug'] . "&quot;</h2>\n";
		elseif ($screen == 'add_file') echo "<h2>Add a file</h2>\n";
		elseif ($screen == 'edit_file') echo "<h2>Editing the file &quot;" . $cms[0]['slug'] . "&quot;</h2>\n";
		
		echo $form->start('addOrEditContent', null, 'post', null, array('enctype'=>'multipart/form-data'), array('onSubmit'=>'validateCms(); return false;')) . "\n";
		echo $form->input('hidden', 'deleteContent') . "\n";
		echo $form->input('hidden', 'currentScreen', $screen) . "\n";
		if ($screen == 'add_page' || $screen == 'edit_page') echo "  " . $form->input('hidden', 'contentType', 'p') . "\n";
		elseif ($screen == 'add_block' || $screen == 'edit_block') echo "  " . $form->input('hidden', 'contentType', 'b') . "\n";
		elseif ($screen == 'add_file' || $screen == 'edit_file') echo "  " . $form->input('hidden', 'contentType', 'f') . "\n";
		
		if ($screen == 'add_page' || $screen == 'edit_page') {
		
			/* Title */ 	echo $form->row('text', 'title', $operators->firstTrue(@$_POST['title'], @$cms[0]['title']), false, 'Title', 'form-control', '', 150);
			/* Slug */ 		echo $form->rowStart('slug', 'URL');
							if ($screen == 'edit_page') {
								echo "  <div class='row'>\n";
								echo "    <div class='col-md-9 col-sm-8 col-xs-8'>\n";
								echo "      <div class='input-group'><span class='input-group-addon'>http://" . $environmentals['domain'] . "/</span>\n";
								echo "        " . $form->input('text', 'slug', $operators->firstTrue(@$_POST['slug'], @$cms[0]['slug']), true, '|Short unique descriptor (no spaces)', 'form-control', null, 255) . "\n";
								echo "      </div>\n";
								echo "    </div>\n";
								echo "    <div id='cmsPreviewLink' class='col-md-3 col-sm-4 col-xs-4 text-right'>\n";
								echo "      <a href='/" . $cms[0]['slug'] . "' target='_blank'>View current</a>\n";
								echo "    </div>\n";
								echo "  </div>\n";
							}
							else {
								echo "  <div class='input-group'><span class='input-group-addon'>http://" . $environmentals['domain'] . "/</span>\n";
								echo "    " . $form->input('text', 'slug', $operators->firstTrue(@$_POST['slug'], @$cms[0]['slug']), true, '|Short unique descriptor (no spaces)', 'form-control', null, 255) . "\n";
								echo "  </div>\n";
							}
							echo $form->rowEnd();
			/* Language */ 	echo $form->row('language', 'language_id', $operators->firstTrue(@$_POST['language_id'], @$cms[0]['language_id'], $systemPreferences['Default language']), true, 'Language', 'form-control');
			/* Pseudonym */ 	if (count($allPseudonyms)) echo $form->row('select', 'pseudonym_id', $operators->firstTrue(@$_POST['pseudonym_id'], @$cms[0]['pseudonym_id'], @$pseudonym['pseudonym_id']), false, 'Pseudonym', 'form-control', $allPseudonyms);
			/* Content */	echo $form->row('textarea', 'content', $operators->firstTrue(@$_POST['content'], @$cms[0]['content']), false, 'Content|Plain text or HTML', 'form-control', null, null, array('rows'=>'10'));
							
			echo "  <div id='cms_more_fields'";
			if (!@$_POST['login_required'] && !@$cms[0]['login_required'] && !@$_POST['content_js'] && !@$cms[0]['content_js'] && !@$_POST['content_css'] && !@$cms[0]['content_css']) echo " style='display: none;'";
			echo ">\n";
			
			/* Login */			echo $form->row('yesno_bootstrap_switch', 'login_required', $operators->firstTrue(@$_POST['login_required'], @$cms[0]['login_required']), false, 'Login required');
			/* JavaScript */	echo $form->row('textarea', 'content_js', $operators->firstTrue(@$_POST['content_js'], @$cms[0]['content_js']), false, 'JavaScript|Runs at pageload', 'form-control', null, null, array('rows'=>'7'));
			/* CSS */			echo $form->row('textarea', 'content_css', $operators->firstTrue(@$_POST['content_css'], @$cms[0]['content_css']), false, 'CSS', 'form-control', null, null, array('rows'=>'7'));
			
			echo "  </div>\n";
		}
		elseif ($screen == 'add_block' || $screen == 'edit_block') {
			/* Slug */		echo "  " . $form->row('text', 'slug', $operators->firstTrue(@$_POST['slug'], @$cms[0]['slug']), true, 'Slug|Short unique descriptor', 'form-control', null, 150) . "\n";
							echo "  " . $form->rowStart('tip');
							echo "  <div class='text-muted'>To embed this content block into the site, add <strong>echo retrieveContentBlock('');</strong> with the unique slug between the quotation marks</div>\n";
							echo "  " . $form->rowEnd();
			/* Language */ 	echo $form->row('language', 'language_id', $operators->firstTrue(@$_POST['language_id'], @$cms[0]['language_id'], $systemPreferences['Default language']), true, 'Language', 'form-control');
			/* Pseudonym */ 	if (count($allPseudonyms)) echo $form->row('select', 'pseudonym_id', $operators->firstTrue(@$_POST['pseudonym_id'], @$cms[0]['pseudonym_id'], @$pseudonym['pseudonym_id']), false, 'Pseudonym', 'form-control', $allPseudonyms);
			/* Content */	echo $form->row('textarea', 'content', $operators->firstTrue(@$_POST['content'], @$cms[0]['content']), false, 'Content|Plain text or HTML', 'form-control', null, null, array('rows'=>'10'));
		}
		elseif ($screen == 'add_file') {
			/* Upload */	echo "  " . $form->row('file', 'cmsFileUpload', null, true, 'Specify a file to upload');
		}
		elseif ($screen == 'edit_file') {
			/* Slug */		echo $form->rowStart('slug', 'Filename');
							echo "  <div class='row'>\n";
							echo "    <div class='col-md-9 col-sm-8 col-xs-8'>\n";
							echo "      " . $form->input('text', 'slug', $operators->firstTrue(@$_POST['slug'], $cms[0]['slug']), true, '|Filename', 'form-control', null, 255) . "\n";
							echo "    </div>\n";
							echo "    <div id='cmsPreviewLink' class='col-md-3 col-sm-4 col-xs-4 text-right'>\n";
							echo "      <a href='/" . $filePath . '/' . $cms[0]['slug'] . "' target='_blank'>View current</a>\n";
							echo "    </div>\n";
							echo "  </div>\n";
							echo $form->rowEnd();
			/* Metadata */	if (@$metadata) {
								foreach ($metadata as $key=>$value) {
									if ($value) echo "  " . $form->row('uneditable', $key, $value, false, $key, 'text-bold') . "\n";
								}
							}
		}
		
		/* Actions */		echo "  <div class='row'>\n";
							echo "    <div class='col-md-10 col-sm-10 col-xs-8'>\n";
							// save
								echo "      " . $form->input('submit', 'save_button', null, false, 'Save', 'btn btn-info') . "\n";
							// more
								if ($screen == 'add_page' || $screen == 'edit_page') {
									echo "      " . $form->input('button', 'more', null, false, 'More', 'btn btn-link', '', '', '', '', '') . "\n";
									$pageJavaScript .= "$('#more').click(function () { $('#cms_more_fields').slideToggle('slow'); });";
								}
							// return
								echo "        " . $form->input('cancel_and_return', 'cancel_button', null, false, 'Cancel', 'btn btn-link') . "\n";
							echo "      </div>\n";
							
							// delete
								if ($screen == 'edit_page' || $screen == 'edit_block' || $screen == 'edit_file') {
									echo "    <div class='col-md-2 col-sm-2 col-xs-4 text-right'>\n";
									echo "      " . $form->input('button', 'delete_button', null, false, 'Delete', 'btn btn-danger', '', '', '', '', array('onClick'=>'deleteThisContent(); return false;')) . "\n";
									echo "    </div>\n";
								}
							echo "  </div>\n";
  			
		echo $form->end();
		
	}
 
?>