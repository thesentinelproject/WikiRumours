<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in['is_administrator'] || !$logged_in['can_edit_settings']) forceLoginThenRedirectHere();
		
	// parse query string
		$subView = $operators->firstTrue(@$parameter1, 'index');
		if ($subView == 'edit') $id = @$parameter2;
		else $pageStatus = @$parameter2;
		
	// queries
		if ($subView == 'edit' && $id) {
			$result = retrievePseudonyms(array('pseudonym_id'=>$id), null, null, null, 1);
			if (!count($result)) {
				header('Location: /admin_pseudonyms/add');
				exit();
			}
			else {
				// split URL into subdomain and domain
					if (substr_count($result[0]['url'], '.') > 1) {
						$result[0]['subdomain'] = substr($result[0]['url'], 0, strpos($result[0]['url'], '.'));
						$result[0]['domain'] = substr($result[0]['url'], strlen($result[0]['subdomain'] . '.'));
					}
					else $result[0]['domain'] = $result[0]['url'];
				// check logo
					if (@$result[0]['pseudonym_id'] && @$result[0]['logo_ext']) {
						$logo = 'assets/pseudonym_logos/' . $result[0]['pseudonym_id'] . '.' . $result[0]['logo_ext'];
						if (!file_exists($logo)) $logo = null;
					}

			}
		}
		elseif ($subView == 'add') {
		}
		else {
			$pseudonyms = retrievePseudonyms(null, null, null, $tablePrefix . 'pseudonyms.name ASC');
		}

/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {

		$pageError = null;

		if ($_POST['formName'] == 'editPseudonymForm' && $_POST['deleteThisPseudonym'] == 'Y') {

			// delete logo
				if (@$logo) {
					$success = unlink ($logo);
					if (!$success || file_exists($logo)) $pageError .= "Unable to delete logo for some reason. ";
				}

			if (!$pageError) {

				// delete pseudonym
					deleteFromDb('pseudonyms', array('pseudonym_id'=>$pseudonym[0]['pseudonym_id']), null, null, null, null, 1);

				// update logs
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has deleted the pseudonym &quot;" . $pseudonym[0]['name'] . "&quot; (pseudonym_id " . $pseudonym[0]['pseudonym_id'] . ")";
					$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'pseudonym_id=' . $pseudonym[0]['pseudonym_id']));

				// redirect
					header('Location: /admin_pseudonyms/index/pseudonym_deleted');
					exit();

			}


		}
		elseif ($_POST['formName'] == 'editPseudonymForm' && $_POST['deleteThisLogo'] == 'Y') {

			// check for errors
				if (!@$logo) $pageError = "Unable to locate logo to delete. ";
				else {

					// remove from server
						if (@$logo) $success = unlink ($logo);
						if (!$success || file_exists($logo)) $pageError .= "Unable to delete logo for some reason. ";
						else {

							// update logs
								$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has removed the logo from the pseudonym &quot;" . $pseudonym[0]['name'] . "&quot; (pseudonym_id " . $pseudonym[0]['pseudonym_id'] . ")";
								$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'pseudonym_id=' . $pseudonym[0]['pseudonym_id']));

							// redirect
								header('Location: /admin_pseudonyms/index/pseudonym_logo_deleted');
								exit();

						}

				}
		
		}
		elseif ($_POST['formName'] == 'editPseudonymForm') {

			// clean input
				$_POST = $parser->trimAll($_POST);

			// check for errors
				if (!$_POST['domain'] || substr_count($_POST['domain'], '.') < 1) $pageError .= "Please specify a valid domain. ";
				if (!$_POST['name']) $pageError .= "Please specify an pseudonym name. ";
				if ($_FILES['new_logo']['tmp_name']) {
					$fileExtension = strtolower($file_manager->isImage($_FILES['new_logo']['tmp_name']));
					if (!$fileExtension) $pageError .= "An invalid image was uploaded; please upload a JPG, PNG or GIF. ";
					else {
						$dimensions = getimagesize($_FILES['new_logo']['tmp_name']);
						if ($dimensions[0] > 2000 || $dimensions[1] > 1000) $pageError .= "Your uploaded logo is way too big. Please make sure that the width or height is no more than 2000 pixels. ";
						else {
							$filesize = $file_manager->getFilesize($_FILES['new_logo']['tmp_name']);
							if ($filesize > 1048576) $pageError .= "Your uploaded logo is way too big. Please make sure that the file is no larger than 1 MB. ";
						}
					}
				}

			// update DB
				if (!$pageError) {

					if ($subView == 'edit') updateDb('pseudonyms', array('url'=>trim($_POST['subdomain'] . '.' . $_POST['domain'], '. '), 'name'=>$_POST['name'], 'description'=>$_POST['description'], 'country_id'=>$_POST['country_id'], 'language_id'=>$_POST['language_id'], 'outgoing_email'=>$_POST['outgoing_email'], 'google_analytics_id'=>$_POST['google_analytics_id'], 'logo_ext'=>$fileExtension), array('pseudonym_id'=>$pseudonym[0]['pseudonym_id']), null, null, null, null, 1);
					else $pseudonymID = insertIntoDb('pseudonyms', array('url'=>trim($_POST['subdomain'] . '.' . $_POST['domain'], '. '), 'name'=>$_POST['name'], 'description'=>$_POST['description'], 'country_id'=>$_POST['country_id'], 'language_id'=>$_POST['language_id'], 'outgoing_email'=>$_POST['outgoing_email'], 'google_analytics_id'=>$_POST['google_analytics_id'], 'logo_ext'=>$fileExtension));

					// update logo
						if ($_FILES['new_logo']['tmp_name']) {
							$destination = 'assets/pseudonym_logos/' . $operators->firstTrue(@$pseudonymID, $pseudonym[0]['pseudonym_id']) . '.' . $fileExtension;
							// delete old
								@unlink ($logo);
								if (file_exists($logo)) $pageError .= "Unable to delete previous logo for some reason. ";
								else {
									// save new
										$success = move_uploaded_file($_FILES['new_logo']['tmp_name'], $destination);
										if (!$success || !file_exists($destination)) $pageError .= "Unable to save logo for some reason. ";
								}
						}

					// update logs
						if ($subView == 'edit') {
							$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated the pseudonym &quot;" . $pseudonym[0]['name'] . "&quot; (pseudonym_id " . $pseudonym[0]['pseudonym_id'] . ")";
							$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'pseudonym_id=' . $pseudonym[0]['pseudonym_id']));
						}
						else {
							$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has added the pseudonym &quot;" . $_POST['name'] . "&quot; (pseudonym_id " . $pseudonymID . ")";
							$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'pseudonym_id=' . $pseudonymID));
						}

					// redirect
						if (!$pageError) {
							header('Location: /admin_pseudonyms/index/pseudonym_updated');
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