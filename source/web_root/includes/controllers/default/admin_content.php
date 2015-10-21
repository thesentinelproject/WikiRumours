<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$screen = $parameter1;
		if (!$screen) $screen = 'menu';
		
		$pageStatus = $parameter2;

	// authenticate user
		if (!$logged_in['is_administrator'] || !$logged_in['can_edit_content']) forceLoginThenRedirectHere();

	// queries
		if ($screen == 'menu') {
			$pages = retrieveContent(array('cms_type'=>'p'), null, null, 'slug ASC, ' . $tablePrefix . 'cms.pseudonym_id ASC, ' . $tablePrefix . 'cms.language_id ASC');
			$blocks = retrieveContent(array('cms_type'=>'b'), null, null, 'slug ASC, ' . $tablePrefix . 'cms.pseudonym_id ASC, ' . $tablePrefix . 'cms.language_id ASC');
			$files = retrieveContent(array('cms_type'=>'f'));
		}
		elseif ($screen != 'add_page' && $screen != 'add_block' && $screen != 'add_file') {
			$cms = retrieveFromDb('cms', null, array('cms_id'=>$screen));
			if (count($cms) == 1) {
				if ($cms[0]['cms_type'] == 'p') $screen = 'edit_page';
				elseif ($cms[0]['cms_type'] == 'b') $screen = 'edit_block';
				if ($cms[0]['cms_type'] == 'f') {
					$screen = 'edit_file';
					$filePath = 'assets/cms_files/' . date('YmdHis', strtotime($cms[0]['saved_on']));
					if (!file_exists($filePath . '/' . $cms[0]['slug'])) $pageError .= "Unable to locate the file. ";
					else $metadata = $file_manager->extractFileMetadata($filePath . '/' . $cms[0]['slug']);
				}
			}
			else {
				header('Location: /admin_content');
				exit();
			}
		}

		$allPseudonyms = array();
		$result = retrievePseudonyms();
		for ($counter = 0; $counter < count($result); $counter++) {
			$allPseudonyms[$result[$counter]['pseudonym_id']] = $result[$counter]['name'];
		}
		
	$pageTitle = 'Content';
	$sectionTitle = "Administration";
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {
		
		if ($_POST['formName'] == 'addOrEditContent' && $_POST['deleteContent'] == 'Y') {
			if ($screen == 'edit_page') {
				// delete
					deleteFromDb('cms', array('cms_id'=>$cms[0]['cms_id']), null, null, null, null, 1);
				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") deleted the page &quot;" . addSlashes($cms[0]['slug']) . "&quot;";
					$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'cms_id=' . $cms[0]['cms_id']));
				// redirect
					header('Location: /admin_content/menu/page_deleted');
					exit();
			}
			elseif ($screen == 'edit_block') {
				// delete
					deleteFromDb('cms', array('cms_id'=>$cms[0]['cms_id']), null, null, null, null, 1);
				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") deleted the content block &quot;" . addSlashes($cms[0]['slug']) . "&quot;";
					$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'cms_id=' . $cms[0]['cms_id']));
				// redirect
					header('Location: /admin_content/menu/block_deleted');
					exit();
			}
			elseif ($screen == 'edit_file') {
				// delete file
					$success = $directory_manager->remove($filePath);
					if (!$success || file_exists($filePath . '/' . $cms[0]['slug'])) $pageError .= "Unable to delete file. ";
					else {
						// delete from DB
							deleteFromDb('cms', array('cms_id'=>$cms[0]['cms_id']), null, null, null, null, 1);
						// update log
							$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") deleted the file &quot;" . addSlashes($cms[0]['slug']) . "&quot;";
							$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'cms_id=' . $cms[0]['cms_id']));
						//redirect
							header('Location: /admin_content/menu/file_deleted');
							exit();
					}
			}
		}
		
		elseif ($_POST['formName'] == 'addOrEditContent') {
			
			// clean input
				$_POST = $parser->trimAll($_POST);
				if (isset($_POST['login_required'])) $_POST['login_required'] = 1;
				else $_POST['login_required'] = 0;
				$_FILES['cmsFileUpload']['name'] = str_replace(' ', '_', @$_FILES['cmsFileUpload']['name']);
				
			// validate input
				$pageError = '';
				
				if ($screen == 'add_block' || $screen == 'edit_block') {
					if (!$_POST['slug']) $pageError .= "Please create a slug to use as a unique descriptor for your content.\n";
					if (!$_POST['content']) $pageError .= "Please provide content to save in the CMS.\n";
				}
				elseif ($screen == 'add_page' || $screen == 'edit_page') {
					if (!$_POST['slug']) $pageError .= "Please create a slug to use as a unique descriptor for your content.\n";
					elseif (!$input_validator->isStringValid($_POST['slug'], 'abcdefghijklmnopqrstuvwxyz0123456789_-', '')) $pageError .= "Please ensure your slug contains only alphanumeric characters, dashes or underscores. ";
					if (!$_POST['title']) $pageError .= "Please provide a page title.\n";
					if (!$_POST['content']) $pageError .= "Please provide content to save in the CMS.\n";
				}
				elseif ($screen == 'add_file') {
					if (!$_FILES['cmsFileUpload']['name']) $pageError .= "Please specify a file to upload. ";
					elseif ($_FILES['cmsFileUpload']['size'] > ($systemPreferences['Maximum filesize for CMS uploads'] * 1024) || @filesize($_FILES['cmsFileUpload']['tmp_name']) > ($systemPreferences['Maximum filesize for CMS uploads'] * 1024)) $pageError .= "The uploaded file is too large. Please limit files to " . $parser->addFileSizeSuffix($systemPreferences['Maximum filesize for CMS uploads'] * 1024) . " each. ";
					elseif (!$input_validator->isStringValid($_FILES['cmsFileUpload']['name'], 'abcdefghijklmnopqrstuvwxyz0123456789_-.', '')) $pageError .= "Please ensure your filename contains only alphanumeric characters, dashes, underscores or periods. ";
				}
				elseif ($screen == 'edit_file') {
					if (!$_POST['slug']) $pageError .= "Please specify a filename.\n";
					elseif (!$input_validator->isStringValid($_POST['slug'], 'abcdefghijklmnopqrstuvwxyz0123456789_-.', '')) $pageError .= "Please ensure your filename contains only alphanumeric characters, dashes, underscores or periods. ";
				}
				
				if ($screen == 'add_page' || $screen == 'add_block' || (($screen == 'edit_page' || $screen == 'edit_block') && strtolower($_POST['slug']) != strtolower(@$cms[0]['slug']))) {
					$doesThisSlugAlreadyExist = retrieveFromDb('cms', 'cms_id', array('slug'=>$_POST['slug'], 'cms_type'=>(substr_count($screen, 'block') > 0 ? 'b' : 'p'), 'language_id'=>@$_POST['language_id'], 'pseudonym_id'=>@$_POST['pseudonym_id']));
					if (count($doesThisSlugAlreadyExist) > 0) $pageError .= "The slug you've specified already exists in the database. ";
				}
				
			// upload file, if required
				if ($screen == 'add_file' && !$pageError) {
					$fileDate = date('Y-m-d H:i:s');
					$filePath = 'assets/cms_files/' . date('YmdHis', strtotime($fileDate));
					$successfullyCreatedDirectory = @mkdir($filePath);
					if (!$successfullyCreatedDirectory || !file_exists($filePath)) $pageError .= "Unable to create subdirectory for this upload. ";
					else {
						$uploadSuccessful = @move_uploaded_file($_FILES['cmsFileUpload']['tmp_name'], $filePath . '/' . $_FILES['cmsFileUpload']['name']);
						if (!$uploadSuccessful || !file_exists($filePath . '/' . $_FILES['cmsFileUpload']['name'])) $pageError .= "Unable to save uploaded file for some reason. ";
					}
				}

			// update database
				if (!$pageError) {
					if ($screen == 'add_page') $cmsID = insertIntoDb('cms', array('slug'=>$_POST['slug'], 'title'=>$_POST['title'], 'language_id'=>$_POST['language_id'], 'pseudonym_id'=>@$_POST['pseudonym_id'], 'content'=>$_POST['content'], 'content_js'=>$_POST['content_js'], 'content_css'=>$_POST['content_css'], 'cms_type'=>'p', 'login_required'=>$_POST['login_required'], 'saved_on'=>date('Y-m-d H:i:s'), 'saved_by'=>$logged_in['user_id']));
					elseif ($screen == 'edit_page') updateDb('cms', array('slug'=>$_POST['slug'], 'title'=>$_POST['title'], 'language_id'=>$_POST['language_id'], 'pseudonym_id'=>@$_POST['pseudonym_id'], 'content'=>$_POST['content'], 'content_js'=>$_POST['content_js'], 'content_css'=>$_POST['content_css'], 'cms_type'=>'p', 'login_required'=>$_POST['login_required'], 'saved_on'=>date('Y-m-d H:i:s'), 'saved_by'=>$logged_in['user_id']), array('cms_id'=>$cms[0]['cms_id']), null, null, null, null, 1);
					elseif ($screen == 'add_block') $cmsID = insertIntoDb('cms', array('slug'=>$_POST['slug'], 'language_id'=>$_POST['language_id'], 'pseudonym_id'=>@$_POST['pseudonym_id'], 'content'=>$_POST['content'], 'content_js'=>$_POST['content_js'], 'content_css'=>$_POST['content_css'], 'cms_type'=>'b', 'saved_on'=>date('Y-m-d H:i:s'), 'saved_by'=>$logged_in['user_id']));
					elseif ($screen == 'edit_block') updateDb('cms', array('slug'=>$_POST['slug'], 'language_id'=>$_POST['language_id'], 'pseudonym_id'=>@$_POST['pseudonym_id'], 'content'=>$_POST['content'], 'content_js'=>$_POST['content_js'], 'content_css'=>$_POST['content_css'], 'cms_type'=>'b', 'saved_on'=>date('Y-m-d H:i:s'), 'saved_by'=>$logged_in['user_id']), array('cms_id'=>$cms[0]['cms_id']), null, null, null, null, 1);
					elseif ($screen == 'add_file') $cmsID = insertIntoDb('cms', array('slug'=>$_FILES['cmsFileUpload']['name'], 'cms_type'=>'f', 'saved_on'=>date('Y-m-d H:i:s', strtotime($fileDate)), 'saved_by'=>$logged_in['user_id']));
					elseif ($screen == 'edit_file') {
						$filePath = 'assets/cms_files/' . date('YmdHis', strtotime($cms[0]['saved_on'])) . '/';
						$successfullyRenamed = rename($filePath . $cms[0]['slug'], $filePath . $_POST['slug']);
						if (!$successfullyRenamed || !file_exists($filePath . $_POST['slug'])) $pageError .= "Unable to rename file for some reason. ";
						else {
							updateDb('cms', array('slug'=>$_POST['slug']), array('cms_id'=>$cms[0]['cms_id']), null, null, null, null, 1);
						}
					}
				}
				
			// update log
				if (!$pageError) {
					if ($screen == 'add_page') $activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") added the virtual page &quot;" . $_POST['title'] . "&quot; (cms_id " . $cmsID . ")";
					elseif ($screen == 'edit_page') $activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") updated the virtual page &quot;" . $_POST['title'] . "&quot; (cms_id " . $cms[0]['cms_id'] . ")";
					elseif ($screen == 'add_block') $activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") added the content block &quot;" . $_POST['slug'] . "&quot; (cms_id " . $cmsID . ")";
					elseif ($screen == 'edit_block') $activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") updated the content block &quot;" . $_POST['slug'] . "&quot; (cms_id " . $cms[0]['cms_id'] . ")";
					elseif ($screen == 'add_file') $activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") added the file &quot;" . $_FILES['cmsFileUpload']['name'] . "&quot; (cms_id " . $cmsID . ")";
					elseif ($screen == 'edit_file') $activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") updated the file &quot;" . $_POST['slug'] . "&quot; (cms_id " . $cms[0]['cms_id'] . ")";
					$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'cms_id=' . (@$cmsID ? $cmsID : @$cms[0]['cms_id'])));
				}
				
			// redirect
				if (!$pageError) {
					if ($screen == 'add_page') header('Location: /admin_content/menu/page_added');
					elseif ($screen == 'edit_page') header('Location: /admin_content/menu/page_updated');
					elseif ($screen == 'add_block') header('Location: /admin_content/menu/block_added');
					elseif ($screen == 'edit_block') header('Location: /admin_content/menu/block_updated');
					elseif ($screen == 'add_file') header('Location: /admin_content/menu/file_added');
					elseif ($screen == 'edit_file') header('Location: /admin_content/menu/file_updated');
					exit();
				}
				
		}
		
	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>