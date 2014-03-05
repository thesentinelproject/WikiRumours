<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$screen = $parameter1;
		if (!$screen) $screen = 'menu';
		
		$pageSuccess = $parameter2;

	// authenticate user
		if (!$logged_in['is_administrator'] || !$logged_in['can_edit_content']) forceLoginThenRedirectHere();

	// instantiate required class(es)
		$validator = new inputValidator_TL();
		$operators = new operators_TL();
		$parser = new parser_TL();
		$fileManager = new fileManager_TL();
		
	// queries
		if ($screen == 'menu') {
			$pages = retrieveFromDb('cms', array('page_or_block'=>'p'), null, null, null);
			
			$blocks = retrieveFromDb('cms', array('page_or_block'=>'b'), null, null, null);
			$directoryManager = new directoryManager_TL();
			$files = $directoryManager->read('assets/cms_files');
			
			$fileArray = array();
			for ($counter = 0; $counter < count($files); $counter++) {
				$fileArray[$counter] = array();
				$fileArray[$counter]['full_path'] = $files[$counter];
				$fileArray[$counter]['filename'] = str_replace("assets/cms_files/", '', str_replace("assets/cms_files\\", '', $files[$counter]));
				$fileArray[$counter]['filename_wrapped'] = $parser->forceWrap($fileArray[$counter]['filename'], 40);
				$metadata = $fileManager->extractFileMetadata($files[$counter]);
				$metadata = print_r($metadata, true);
				$fileArray[$counter]['metadata'] = htmlspecialchars(print_r($metadata, true), ENT_QUOTES);
			}			
		}
		elseif ($screen == 'add_page' || $screen == 'add_block') {
			$cms = array();
			$cms[0] = array();
			if (@$_POST['slug_page']) $cms[0]['slug'] = $_POST['slug_page'];
			if (@$_POST['slug_block']) $cms[0]['slug'] = $_POST['slug_block'];
			$cms[0]['title'] = @$_POST['title'];
			$cms[0]['content'] = @$_POST['content'];
			$cms[0]['content_js'] = @$_POST['content_js'];
			$cms[0]['content_css'] = @$_POST['content_css'];
		}
		else {
			$cms = retrieveFromDb('cms', array('cms_id'=>$screen), null, null, null);
			if (count($cms) <> 1) {
				header('Location: /content');
				exit();
			}
		}
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {
		
		if ($_POST['formName'] == 'addOrEditContent' && $_POST['deleteContent'] == 'Y') {
			deleteFromDb('cms', array('cms_id'=>floatval($screen)), null, null, null, null, 1);
			if ($_POST['contentType'] == 'p') header('Location: /content/menu/page_deleted');
			elseif ($_POST['contentType'] == 'b') header('Location: /content/menu/block_deleted');
			exit();
		}
		
		elseif ($_POST['formName'] == 'addOrEditContent') {
			
			// clean input
				$_POST = $parser->trimAll($_POST);
							
			// validate input
				$pageError = '';
				if ($_POST['contentType'] == 'b') {
					if (!$_POST['slug']) $pageError .= "Please create a slug to use as a unique descriptor for your content.\n";
				}
				elseif ($_POST['contentType'] == 'p') {
					if (!$_POST['slug']) $pageError .= "Please create a slug to use as a unique descriptor for your content.\n";
					elseif (!$validator->isStringValid($_POST['slug'], 'abcdefghijklmnopqrstuvwxyz0123456789_-', '')) $pageError .= "Please ensure your slug contains only alphanumeric characters, dashes or underscores. ";
				}
				if ($_POST['contentType'] == 'p' && !$_POST['title']) $pageError .= "Please provide a page title.\n";
				if ($screen == 'add_page' || $screen == 'add_block' || $_POST['slug'] != $cms[0]['slug']) {
					$doesThisSlugAlreadyExist = retrieveFromDb('cms', array('slug'=>$_POST['slug']), null, null, null);
					if (count($doesThisSlugAlreadyExist) > 0) $pageError .= "The slug you've specified already exists in the database. ";
				}

			// update database
				if (!$pageError) {
					if ($screen == 'add_page') $cmsID = insertIntoDb('cms', array('slug'=>$_POST['slug'], 'title'=>$_POST['title'], 'content'=>$_POST['content'], 'content_js'=>$_POST['content_js'], 'content_css'=>$_POST['content_css'], 'page_or_block'=>'p', 'saved_on'=>date('Y-m-d H:i:s'), 'saved_by'=>$logged_in['user_id']));
					elseif ($screen == 'add_block') $cmsID = insertIntoDb('cms', array('slug'=>$_POST['slug'], 'content'=>$_POST['content'], 'content_js'=>$_POST['content_js'], 'content_css'=>$_POST['content_css'], 'page_or_block'=>'b', 'saved_on'=>date('Y-m-d H:i:s'), 'saved_by'=>$logged_in['user_id']));
					elseif ($_POST['contentType'] == 'p') updateDb('cms', array('slug'=>$_POST['slug'], 'title'=>$_POST['title'], 'content'=>$_POST['content'], 'content_js'=>$_POST['content_js'], 'content_css'=>$_POST['content_css'], 'page_or_block'=>'p', 'saved_on'=>date('Y-m-d H:i:s'), 'saved_by'=>$logged_in['user_id']), array('cms_id'=>$cms[0]['cms_id']), null, null, null, null, 1);
					elseif ($_POST['contentType'] == 'b') updateDb('cms', array('slug'=>$_POST['slug'], 'content'=>$_POST['content'], 'content_js'=>$_POST['content_js'], 'content_css'=>$_POST['content_css'], 'page_or_block'=>'b', 'saved_on'=>date('Y-m-d H:i:s'), 'saved_by'=>$logged_in['user_id']), array('cms_id'=>$cms[0]['cms_id']), null, null, null, null, 1);
				}
				
			// update log
				if (!$pageError) {
					if ($screen == 'add_page') $activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") added the virtual page " . $_POST['title'] . " (cms_id " . $cmsID . ")";
					elseif ($screen == 'add_block') $activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") added the content block " . $_POST['slug'] . " (cms_id " . $cmsID . ")";
					elseif ($_POST['contentType'] == 'p') $activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") updated the virtual page " . $_POST['title'] . " (cms_id " . $cms[0]['cms_id'] . ")";
					elseif ($_POST['contentType'] == 'b') $activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") updated the content block " . $_POST['slug'] . " (cms_id " . $cms[0]['cms_id'] . ")";
					$logger->logItInDb($activity);
				}
				
			// redirect
				if (!$pageError) {
					if ($screen == 'add_page') header('Location: /content/menu/page_added');
					elseif ($screen == 'add_block') header('Location: /content/menu/block_added');
					elseif ($_POST['contentType'] == 'p') header('Location: /content/menu/page_updated');
					elseif ($_POST['contentType'] == 'b') header('Location: /content/menu/block_updated');
					exit();
				}
				
		}
		
		elseif ($_POST['formName'] == 'uploadCmsFileForm' && $_POST['fileToDelete']) {
			
			$success = unlink('assets/cms_files/' . urldecode($_POST['fileToDelete']));
			if (!$success) $pageError .= "Unable to delete the file &quot;" . urldecode($_POST['fileToDelete']) . "&quot; ";
			else {
				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") deleted the CMS file " . urldecode($_POST['fileToDelete']);
					$logger->logItInDb($activity);
				// redirect
					header('Location: /content/menu/file_deleted');
					exit();
			}
			
		}

		elseif ($_POST['formName'] == 'uploadCmsFileForm') {
			
			// validate input
				$pageError = '';
				if (!$_FILES['cmsFileUpload']['name']) $pageError .= "Please specify a file to upload. ";
				elseif ($_FILES['cmsFileUpload']['size'] > ($maxFilesizeForCmsUploads * 1024) || @filesize($_FILES['cmsFileUpload']['tmp_name']) > ($maxFilesizeForCmsUploads * 1024)) $pageError .= "The uploaded file is too large. Please limit files to " . $parser->addFileSizeSuffix($maxFilesizeForCmsUploads * 1024) . " each. ";
				
			// move file
				if (!$pageError) {
					$newFilename = str_replace(' ', '_', $_FILES['cmsFileUpload']['name']);
					$success = move_uploaded_file($_FILES['cmsFileUpload']['tmp_name'], 'assets/cms_files/' . $newFilename);
					if (!$success) $pageError .= "Unable to save uploaded file for some reason. ";
					else {
						// update log
							$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") uploaded the CMS file " . $filename;
							$logger->logItInDb($activity);
						// redirect
							header('Location: /content/menu/file_added');
							exit();
					}
				}
			
		}
		
	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>