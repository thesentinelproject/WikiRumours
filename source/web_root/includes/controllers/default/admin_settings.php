<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$screen = @$tl->page['parameter1'];
		if (!$screen) $screen = 'all';

		if ($screen == 'update' || $screen == 'edit') $id = floatval(@$tl->page['parameter2']);

	// authenticate user
		if (!$logged_in['is_administrator']) $authentication_manager->forceLoginThenRedirectHere();
		
		if (!$logged_in['can_update_settings']) $authentication_manager->forceLoginThenRedirectHere();
		elseif (($screen == 'edit' || $screen == 'add') && !$logged_in['can_edit_settings']) $authentication_manager->forceRedirect('/admin_settings/all');

	// queries
		if ($screen == 'all') {
			$settings = retrieveFromDb('settings', null, null, null, null, null, null, null, 'setting ASC');
			$tl->page['title'] = "System settings";
		}
		elseif (($screen == 'update' || $screen == 'edit') && $id) {
			$setting = retrieveSingleFromDb('settings', null, array('setting_id'=>$id));
			if (!count($setting)) $authentication_manager->forceRedirect('/admin_settings/all');
			else $tl->page['title'] = ucwords($screen) . " &quot;" . $setting[0]['setting'] . "&quot;";
			if ($screen == 'edit') $tl->page['warning'] = "Changing setting attributes <strong>will not update</strong> these attributes in the source code, so please exercise caution.";
		}
		elseif ($screen == 'add') {
			$tl->page['title'] = "Add setting";
		}
		else $authentication_manager->forceRedirect('/admin_settings/all');

		$localization_manager->populateCountries();
		$localization_manager->populateLanguages();

	$tl->page['section'] = "Administration";
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {
		
		$tl->page['error'] = '';

		if ($_POST['formName'] == 'updateSettingForm') {

			// retrieve record
				$setting = retrieveSingleFromDb('settings', null, array('setting_id'=>$id));
				if (!count($setting)) $tl->page['error'] .= "Unable to retrieve setting for some reason. ";
				else {

					// clean input
						if ($setting[0]['input_type'] == 'yesno_bootstrap_switch' || $setting[0]['input_type'] == 'checkbox' || $setting[0]['input_type'] == 'checkbox_stacked_bootstrap') {
							if (isset($_POST['setting_value'])) $_POST['setting_value'] = 1;
							else $_POST['setting_value'] = 0;
						}

					// update DB
						updateDbSingle('settings', array('value'=>$_POST['setting_value'], 'updated_by'=>$logged_in['user_id'], 'updated_on'=>date('Y-m-d H:i:s')), array('setting_id'=>$setting[0]['setting_id']));

					// update log
						$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated the system setting &quot;" . $setting[0]['setting'] . "&quot; (setting_id " . $setting[0]['setting_id'] . ")";
						$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'setting_id=' . $setting[0]['setting_id']));
					
					// redirect
						$authentication_manager->forceRedirect('/admin_settings/all/success=setting_updated');

				}

		}

		elseif ($_POST['formName'] == 'editSettingForm' && $_POST['deleteThisSetting'] == 'Y' && $logged_in['can_edit_settings']) {

			// retrieve record
				$setting = retrieveSingleFromDb('settings', null, array('setting_id'=>$id));
				if (!count($setting)) $tl->page['error'] .= "Unable to retrieve setting for some reason. ";
				else {

					// update DB
						deleteFromDbSingle('settings',  array('setting_id'=>$setting[0]['setting_id']));

					// update log
						$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has deleted the system setting &quot;" . $setting[0]['setting'] . "&quot; (setting_id " . $setting[0]['setting_id'] . ")";
						$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'setting_id=' . $setting[0]['setting_id']));
					
					// redirect
						$authentication_manager->forceRedirect('/admin_settings/all/success=setting_deleted');

				}

		}

		elseif ($_POST['formName'] == 'editSettingForm' && $screen == 'edit' && $logged_in['can_edit_settings']) {

			// retrieve record
				$setting = retrieveSingleFromDb('settings', null, array('setting_id'=>$id));
				if (!count($setting)) $tl->page['error'] .= "Unable to retrieve setting for some reason. ";
				else {

					// clean input
						$_POST = $parser->trimAll($_POST);
						if (isset($_POST['is_mandatory'])) $_POST['is_mandatory'] = 1;
						else $_POST['is_mandatory'] = 0;

					// update DB
						updateDbSingle('settings', array('setting'=>$_POST['setting'], 'prepend'=>$_POST['prepend'], 'append'=>$_POST['append'], 'input_type'=>$_POST['input_type'], 'options'=>$_POST['options'], 'is_mandatory'=>$_POST['is_mandatory'], 'tooltip'=>$_POST['tooltip'], 'updated_by'=>$logged_in['user_id'], 'updated_on'=>date('Y-m-d H:i:s')), array('setting_id'=>$setting[0]['setting_id']));

					// update log
						$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has edited the system setting &quot;" . $setting[0]['setting'] . "&quot; (setting_id " . $setting[0]['setting_id'] . ")";
						$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'setting_id=' . $setting[0]['setting_id']));
					
					// redirect
						$authentication_manager->forceRedirect('/admin_settings/all/success=setting_edited');

				}

		}

		elseif ($_POST['formName'] == 'editSettingForm' && $screen == 'add' && $logged_in['can_edit_settings']) {

			// clean input
				$_POST = $parser->trimAll($_POST);
				if (isset($_POST['is_mandatory'])) $_POST['is_mandatory'] = 1;
				else $_POST['is_mandatory'] = 0;

			// update DB
				$settingID = insertIntoDb('settings', array('setting'=>$_POST['setting'], 'prepend'=>$_POST['prepend'], 'value'=>$_POST['setting_value'], 'append'=>$_POST['append'], 'input_type'=>$_POST['input_type'], 'options'=>$_POST['options'], 'is_mandatory'=>$_POST['is_mandatory'], 'tooltip'=>$_POST['tooltip'], 'updated_by'=>$logged_in['user_id'], 'updated_on'=>date('Y-m-d H:i:s')));

			// update log
				$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has created the system setting &quot;" . $_POST['setting'] . "&quot; (setting_id " . $settingID . ")";
				$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'setting_id=' . $settingID));
			
			// redirect
				$authentication_manager->forceRedirect('/admin_settings/all/success=setting_added');

		}

	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>