<?php
	$pageTitle = 'Content';
	include 'includes/views/desktop/shared/page_top.php';

	if ($pageError) echo "<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $pageError . "</div>\n";
	elseif ($pageSuccess == 'page_added') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Page successfully added.</div>\n";
	elseif ($pageSuccess == 'page_updated') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Page successfully updated.</div>\n";
	elseif ($pageSuccess == 'page_deleted') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Page successfully deleted.</div>\n";
	elseif ($pageSuccess == 'block_added') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Content block successfully added.</div>\n";
	elseif ($pageSuccess == 'block_updated') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Content block successfully updated.</div>\n";
	elseif ($pageSuccess == 'block_deleted') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Content block successfully deleted.</div>\n";
	elseif ($pageSuccess == 'file_added') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>File successfully added.</div>\n";
	elseif ($pageSuccess == 'file_updated') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>File successfully updated.</div>\n";
	elseif ($pageSuccess == 'file_deleted') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>File successfully deleted.</div>\n";
	
	if ($screen == 'menu') {
		
		// Virtual pages
			echo "  <div class='pageModule'><!-- Pages -->\n";
			echo "    " . $form->start() . "\n";
			echo "    <h2>Pages</h2>\n";
			if (count($pages) < 1) echo "    <p>None yet...</p>\n";
			else {
				echo "    <ul>\n";
				for ($counter = 0; $counter < count($pages); $counter++) {
					echo "    <li><a href='/content/" . $pages[$counter]['cms_id'] . "'>" . $pages[$counter]['title'] . "</a> &nbsp; <small><a href='/" . $pages[$counter]['slug'] . "' class='subtle'>PREVIEW</a></small></li>\n";
				}
				echo "    </ul>\n";
			}
			echo "    <div>" . $form->input('button', 'add_a_page', null, false, 'Add a page', 'btn btn-info', '', '', '', '', array('onClick'=>'document.location.href="/content/add_page"; return false;')) . "</div>\n";
			echo "    " . $form->end() . "\n";
			echo "  </div>\n";
		
		// content blocks
			echo "  <div class='pageModule'><!-- Content blocks -->\n";
			echo "    " . $form->start() . "\n";
			echo "    <h2>Content blocks</h2>\n";
			if (count($blocks) < 1) echo "    <p>None yet...</p>\n";
			else {
				echo "    <div class='muted'>To embed a content block in this site, simply type <b>echo retrieveContentBlock('');</b> with the unique slug between the single quotes</div>\n";
				echo "    <br />\n";
				echo "    <ul>\n";
				for ($counter = 0; $counter < count($blocks); $counter++) {
					echo "    <li><a href='/content/" . $blocks[$counter]['cms_id'] . "'>" . $blocks[$counter]['slug'] . "</a></li>\n";
				}
				echo "    </ul>\n";
			}
			echo "    <div>" . $form->input('button', 'add_a_block', null, false, 'Add a content block', 'btn btn-info', '', '', '', '', array('onClick'=>'document.location.href="/content/add_block"; return false;')) . "</div>\n";
			echo "    " . $form->end() . "\n";
			echo "  </div>\n";
		
		// files
			echo "  <div class='pageModule'><!-- Files -->\n";
			echo "    " . $form->start('uploadCmsFileForm', null, 'post', null, array('enctype'=>'multipart/form-data'), array('onSubmit'=>'validateUploadCmsFileForm(); return false;')) . "\n";
			echo "    " . $form->input('hidden', 'fileToDelete') . "\n";
			echo "      <h2>Files</h2>\n";
			if (count($files) < 1) echo "      <p>None yet...</p>\n";
			else {
				echo "    <ul>\n";
				for ($counter = 0; $counter < count($fileArray); $counter++) {
					echo "    <li>\n";
					echo "      <a href='/" . $fileArray[$counter]['full_path'] . "'>" . $fileArray[$counter]['filename_wrapped'] . "</a> &nbsp;\n";
					if ($fileArray[$counter]['metadata']) {
						echo "      <small><a href='javascript:void(0)' onClick='return false' class='subtle' id='popover_" . $counter . "' data-toggle='popover' title=" . '"' . $fileArray[$counter]['filename'] . '"' . " data-content=" . '"' . $fileArray[$counter]['metadata'] . '"' . ">METADATA</a></small> &nbsp;\n";
						$pageJavaScript .= "$('#popover_" . $counter . "').popover( {trigger: 'hover', placement: 'top', animation: true });\n";
					}
					echo "      <small><a href='javascript:void(0)' onClick='deleteFile(" . '"' . urlencode($files[$counter]) . '"' . "); return false;' class='subtle'>DELETE</a></small>\n";
					echo "    </li>\n";
				}
				echo "    </ul>\n";
			}
			echo "    <div>\n";
			echo "      " . $form->input('file', 'cmsFileUpload', null, true) . "<br />\n";
			echo "      " . $form->input('submit', 'add', null, false, 'Add a file', 'btn btn-info') . "\n";
			echo "    </div>\n";
			echo "    " . $form->end() . "\n";
			echo "  </div>\n";
			
	}
	else {
		
		if ($screen == 'add_page') echo "<h2>Add a virtual page</h2>\n";
		elseif ($screen == 'add_block') echo "<h2>Add a content block</h2>\n";
		elseif ($cms[0]['page_or_block'] == 'p') echo "<h2>Editing the page &quot;" . $cms[0]['title'] . "&quot;</h2>\n";
		elseif ($cms[0]['page_or_block'] == 'b') echo "<h2>Editing the content block &quot;" . $cms[0]['slug'] . "&quot;</h2>\n";
		
		echo $form->start('addOrEditContent', null, 'post', null, null, array('onSubmit'=>'validateCms(); return false;')) . "\n";
		echo $form->input('hidden', 'deleteContent') . "\n";
		if (@$cms[0]['page_or_block'] == 'b' || @$screen == 'add_block') echo "  " . $form->input('hidden', 'contentType', 'b') . "\n";
		else echo "  " . $form->input('hidden', 'contentType', 'p') . "\n";
		
		if (@$cms[0]['page_or_block'] == 'p' || @$screen == 'add_page') {
		
			/* Title */ 	echo $form->row('text', 'title', $operators->firstTrue(@$_POST['title'], @$cms[0]['title']), false, 'Title', 'form-control', '', 150);
			/* Slug */ 		echo "<!-- Slug -->\n";
							echo "  <div class='formLabel'>URL</div>\n";
							echo "  <div class='formField'>\n";
							echo "    <div class='input-group'><span class='input-group-addon'>http://" . $environmentals['domain'] . "/</span>\n";
							echo "      " . $form->input('text', 'slug', $operators->firstTrue(@$_POST['slug'], @$cms[0]['slug']), true, '|Short unique descriptor (no spaces)', 'form-control', null, 150) . "\n";
							echo "    </div>\n";
							echo "  </div>\n";
							echo "  <div class='floatClear'></div>\n";
		}
		else {			
			/* Slug */		echo "<!-- Slug -->\n";
							echo "  <div id='formLabel_slug' class='formLabel'>Slug</div>\n";
							echo "  <div id='formField_slug' class='formField'>\n";
							echo "      " . $form->input('text', 'slug', $operators->firstTrue(@$_POST['slug'], @$cms[0]['slug']), true, '|Short unique descriptor', 'form-control', null, 150) . "\n";
							echo "  </div>\n";
							echo "  <div class='floatClear'></div>\n";
		}
		
		/* Content */		echo $form->row('textarea', 'content', $operators->firstTrue(@$_POST['content'], @$cms[0]['content']), false, 'Content|Plain text or HTML', 'form-control', null, null, array('rows'=>'10'));
		
		if (@$cms[0]['page_or_block'] == 'p' || @$screen == 'add_page') {
			echo "  <div id='cms_more_fields'";
			if (!@$_POST['content_js'] && !@$cms[0]['content_js'] && !@$_POST['content_css'] && !@$cms[0]['content_css']) echo " style='display: none;'";
			echo ">\n";
			
			/* JavaScript */	echo $form->row('textarea', 'content_js', $operators->firstTrue(@$_POST['content_js'], @$cms[0]['content_js']), false, 'JavaScript|Runs at pageload', 'form-control', null, null, array('rows'=>'7'));
			/* CSS */			echo $form->row('textarea', 'content_css', $operators->firstTrue(@$_POST['content_css'], @$cms[0]['content_css']), false, 'CSS', 'form-control', null, null, array('rows'=>'7'));
			
			echo "  </div>\n";
		}
		
		/* Actions */		echo "  <div class='formLabel'></div>\n";
							echo "  <div class='formField'>\n";
							echo "        " . $form->input('submit', 'save', null, false, 'Save', 'btn btn-info') . "\n";
							if (@$cms[0]['page_or_block'] == 'p' || @$screen == 'add_page') {
								echo "        " . $form->input('button', 'more', null, false, 'More', 'btn btn-link', '', '', '', '', '') . "\n";
								$pageJavaScript .= "$('#more').click(function () { $('#cms_more_fields').slideToggle('slow'); });";
							}
							if (@$screen != 'add_page' && @$screen != 'add_block') echo "        " . $form->input('button', 'delete', null, false, 'Delete', 'btn btn-link', '', '', '', '', array('onClick'=>'deleteThisContent(); return false;')) . "\n";
							echo "        " . $form->input('cancel_and_return', null, null, false, null, 'btn btn-link') . "\n";
							echo "  </div>\n";
							echo "  <div class='floatClear'></div>\n";
  			
		echo $form->end();
		
	}
 
	include 'includes/views/desktop/shared/page_bottom.php';
?>