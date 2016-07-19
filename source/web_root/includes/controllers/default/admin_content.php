<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in['is_administrator'] || !$logged_in['can_edit_content']) $authentication_manager->forceLoginThenRedirectHere(true);

	// parse query string
		if ($tl->page['parameter1']) $filters = $keyvalue_array->keyValueToArray(urldecode($tl->page['parameter1']), '|');
		if (!@$filters['screen']) $filters['screen'] = 'index';
		
	// queries
		$types = array(
			'p' => 'page',
			'b' => 'content block',
			'f' => 'file',
			'r' => 'redirect',
			'm' => 'message',
			'e' => 'email'
		);

		if (@$filters['screen'] == 'edit') {
			if (@$filters['id']) {
				$content = retrieveFromDb('cms', null, array('cms_id'=>@$filters['id']));
				if (!count($content))$authentication_manager->forceRedirect('/404');
				else {
	
					$filters['type'] == $content[0]['content_type'];

					if (@$filters['type'] == 'f') {
						$filePath = __DIR__ . '/../../../assets/cms_files/' . date('YmdHis', strtotime($content[0]['saved_on']));
						if (!file_exists($filePath . '/' . $content[0]['slug'])) $tl->page['error'] .= "Unable to locate the file " . date('YmdHis', strtotime($content[0]['saved_on'])) . '/' . $content[0]['slug'] . ". ";
						else $metadata = $file_manager->extractFileMetadata($filePath . '/' . $content[0]['slug']);
					}
				}
			}
			elseif (!@$types[@$filters['type']]) $authentication_manager->forceRedirect('/404');
		}
		else { // index
			$pages = retrieveContent(array('content_type'=>'p'), null, null, 'slug ASC, ' . $tablePrefix . 'cms.pseudonym_id ASC, ' . $tablePrefix . 'cms.language_id ASC');
			$blocks = retrieveContent(array('content_type'=>'b'), null, null, 'slug ASC, ' . $tablePrefix . 'cms.pseudonym_id ASC, ' . $tablePrefix . 'cms.language_id ASC');
			$files = retrieveContent(array('content_type'=>'f'), null, null, 'slug ASC, ' . $tablePrefix . 'cms.pseudonym_id ASC, ' . $tablePrefix . 'cms.language_id ASC');
			$redirects = retrieveContent(array('content_type'=>'r'), null, null, 'slug ASC, ' . $tablePrefix . 'cms.pseudonym_id ASC, ' . $tablePrefix . 'cms.language_id ASC');
			$messages = retrieveContent(array('content_type'=>'m'), null, null, 'slug ASC, ' . $tablePrefix . 'cms.pseudonym_id ASC, ' . $tablePrefix . 'cms.language_id ASC');
			$emails = retrieveContent(array('content_type'=>'e'), null, null, 'slug ASC, ' . $tablePrefix . 'cms.pseudonym_id ASC, ' . $tablePrefix . 'cms.language_id ASC');
		}

		$allPseudonyms = array();
		$result = retrievePseudonyms();
		for ($counter = 0; $counter < count($result); $counter++) {
			$allPseudonyms[$result[$counter]['pseudonym_id']] = $result[$counter]['name'];
		}

		$allStatuses = array();
		$result = retrieveFromDb('http_statuses', null, null, null, null, null, "code_id >= 300 AND code_id < 400");
		for ($counter = 0; $counter < count($result); $counter++) {
			$allStatuses[$result[$counter]['code_id']] = $result[$counter]['code_id'] . ": " . $result[$counter]['status'];
		}

	$tl->page['title'] = 'Content';
	$tl->page['section'] = "Administration";
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {
		
		if ($_POST['formName'] == 'editContentForm' && $_POST['deleteContent'] == 'Y' && @$content[0]['deletable'] && !@$tl->page['error']) {

			if ($content[0]['content_type'] == 'f') {
				// delete file, if applicable
					$success = $directory_manager->remove($filePath);
					if (!$success || file_exists($filePath . '/' . $content[0]['slug'])) $tl->page['error'] .= "Unable to delete file. ";
			}

			if (!$tl->page['error']) {
				// delete in DB
					deleteFromDbSingle('cms', array('cms_id'=>$content[0]['cms_id']));
				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") deleted the " . $types[$content[0]['content_type']] . " &quot;" . $_POST['slug'] . "&quot;";
					$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'cms_id=' . $content[0]['cms_id']));
				// redirect
					$authentication_manager->forceRedirect('/admin_content/success=deletion_successful');
			}

		}
		
		elseif ($_POST['formName'] == 'editContentForm') {

			// clean input
				$_POST = $parser->trimAll($_POST);
				if (isset($_POST['login_required'])) $_POST['login_required'] = 1;
				else $_POST['login_required'] = 0;
				if (@end(@$_POST['file_cmsFileUpload'])) $_POST['slug'] = substr(end($_POST['file_cmsFileUpload']), strrpos(end($_POST['file_cmsFileUpload']), '/') + 1);

			// validate input
				if ($filters['type'] == 'p') {
					if (!$_POST['slug']) $tl->page['error'] .= "Please create a slug to use as a unique descriptor for your content.\n";
					elseif (!$input_validator->isStringValid($_POST['slug'], 'abcdefghijklmnopqrstuvwxyz0123456789_-', '')) $tl->page['error'] .= "Please ensure your slug contains only alphanumeric characters, dashes or underscores. ";
					if (!$_POST['title']) $tl->page['error'] .= "Please provide a page title.\n";
					if (!$_POST['content']) $tl->page['error'] .= "Please provide content to save in the CMS.\n";
				}
				elseif ($filters['type'] == 'b') {
					if (!$_POST['slug']) $tl->page['error'] .= "Please create a slug to use as a unique descriptor for your content.\n";
					if (!$_POST['content']) $tl->page['error'] .= "Please provide content to save in the CMS.\n";
				}
				elseif ($filters['type'] == 'f' && !@$content) {
					if (!@end(@$_POST['file_cmsFileUpload'])) $tl->page['error'] .= "Please specify a file to upload. ";
					elseif (!$_POST['slug']) $tl->page['error'] .= "Unable to determine filename of upload.\n";
					elseif (@filesize(end($_POST['file_cmsFileUpload'])) > ($systemPreferences['Maximum filesize for uploads'] * 1024 * 1024)) $tl->page['error'] .= "The uploaded file is too large. Please limit files to " . $parser->addFileSizeSuffix($systemPreferences['Maximum filesize for uploads']) . "MB each. ";
				}
				elseif ($filters['type'] == 'f' && @$content) {
					if (!$_POST['slug']) $tl->page['error'] .= "Please specify a filename.\n";
					elseif (!$input_validator->isStringValid($_POST['slug'], 'abcdefghijklmnopqrstuvwxyz0123456789_-.', '')) $tl->page['error'] .= "Please ensure your filename contains only alphanumeric characters, dashes, underscores or periods. ";
				}
				elseif ($filters['type'] == 'm') {
					if (!$_POST['message_type']) $tl->page['error'] .= "Please specify a message type.\n";
					if (!$_POST['slug']) $tl->page['error'] .= "Please create a slug to use in URLs.\n";
					elseif (!$input_validator->isStringValid($_POST['slug'], 'abcdefghijklmnopqrstuvwxyz0123456789_-', '')) $tl->page['error'] .= "Please ensure your slug contains only alphanumeric characters, dashes or underscores. ";
					if (!$_POST['content']) $tl->page['error'] .= "Please provide the full message to be displayed.\n";
				}
				elseif ($filters['type'] == 'r') {
					if (!$_POST['slug']) $tl->page['error'] .= "Please create a slug to use as a unique descriptor for your content.\n";
					elseif (!$input_validator->isStringValid($_POST['slug'], 'abcdefghijklmnopqrstuvwxyz0123456789_-', '')) $tl->page['error'] .= "Please ensure your slug contains only alphanumeric characters, dashes or underscores. ";
					if (!$_POST['redirect_to']) $tl->page['error'] .= "Please specify a destination for your redirect.\n";
				}
				elseif ($filters['type'] == 'e') {
					if (!$_POST['slug']) $tl->page['error'] .= "Please specify a subject line.\n";
					if (!$_POST['content']) $tl->page['error'] .= "Please provide an HTML version of your email.\n";
					if (!$_POST['content_plain']) $tl->page['error'] .= "Please provide a non-HTML version of your email.\n";
				}
				
				if ($filters['type'] != 'e') {
					if (!@$content || strtolower($_POST['slug']) != strtolower(@$content[0]['slug'])) {
						$slugExists = retrieveSingleFromDb('cms', 'cms_id', array('slug'=>$_POST['slug'], 'content_type'=>$filters['type'], 'language_id'=>@$_POST['language_id'], 'pseudonym_id'=>@$_POST['pseudonym_id']));
						if (count($slugExists)) $tl->page['error'] .= "The slug you've specified already exists for this content type in the database. ";
					}
				}
				
			// upload file, if required
				if (!$tl->page['error']) {
					if ($filters['type'] == 'f' && @end(@$_POST['file_cmsFileUpload'])) {
						$fileDate = date('Y-m-d H:i:s');
						$filePath = __DIR__ . '/../../../assets/cms_files/' . date('YmdHis', strtotime($fileDate));
						$successfullyCreatedDirectory = @mkdir($filePath);
						if (!$successfullyCreatedDirectory || !file_exists($filePath)) $tl->page['error'] .= "Unable to create subdirectory for this upload. ";
						else {
							$uploadSuccessful = rename(__DIR__ . '/../../../../' . end($_POST['file_cmsFileUpload']), $filePath . '/' . $_POST['slug']);
							if (!$uploadSuccessful || !file_exists($filePath . '/' . $_POST['slug'])) $tl->page['error'] .= "Unable to save uploaded file for some reason. ";
						}
					}
				}

			// rename file, if required
				if (!$tl->page['error']) {
					if ($filters['type'] == 'f' && @$content && strtolower($_POST['slug']) != strtolower(@$content[0]['slug'])) {
						$filePath = 'assets/cms_files/' . date('YmdHis', strtotime($content[0]['saved_on'])) . '/';
						$successfullyRenamed = rename($filePath . $content[0]['slug'], $filePath . $_POST['slug']);
					}
				}

			// update database
				if (!$tl->page['error']) {

					if (@$content) updateDbSingle('cms', array('slug'=>@$_POST['slug'], 'title'=>@$_POST['title'], 'language_id'=>@$_POST['language_id'], 'pseudonym_id'=>@$_POST['pseudonym_id'], 'content'=>@$_POST['content'], 'content_plain'=>@$_POST['content_plain'], 'content_js'=>@$_POST['content_js'], 'content_css'=>@$_POST['content_css'], 'redirect_to'=>@$_POST['redirect_to'], 'http_status'=>@$_POST['http_status'], 'message_type'=>@$_POST['message_type'], 'content_type'=>$filters['type'], 'login_required'=>@$_POST['login_required']), array('cms_id'=>$filters['id']));
					else $contentID = insertIntoDb('cms', array('slug'=>@$_POST['slug'], 'title'=>@$_POST['title'], 'language_id'=>@$_POST['language_id'], 'pseudonym_id'=>@$_POST['pseudonym_id'], 'content'=>@$_POST['content'], 'content_plain'=>@$_POST['content_plain'], 'content_js'=>@$_POST['content_js'], 'content_css'=>@$_POST['content_css'], 'redirect_to'=>@$_POST['redirect_to'], 'http_status'=>@$_POST['http_status'], 'message_type'=>@$_POST['message_type'], 'content_type'=>$filters['type'], 'login_required'=>@$_POST['login_required'], 'saved_on'=>($fileDate ? date('Y-m-d H:i:s', strtotime($fileDate)) : date('Y-m-d H:i:s')), 'saved_by'=>$logged_in['user_id']));

					// update log
						$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") " . (@$content? "edited" : "created") . " the " . $types[$filters['type']] . " &quot;" . $_POST['slug'] . "&quot;";
						$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'cms_id=' . (@$contentID ? $contentID : @$content[0]['cms_id'])));
						
					// redirect
						$authentication_manager->forceRedirect('/admin_content/' . urlencode("success=content_" . (@$content ? "updated" : "added")));
						
				}
				
		}
		
	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>