<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$pageStatus = $parameter1;

	// authenticate user
		if (!$logged_in['is_administrator'] || !$logged_in['can_edit_settings']) forceLoginThenRedirectHere();
		
	// queries
		$preferences = retrieveFromDb('preferences', null, array('user_id'=>'0'), null, null, null, null, null, 'position ASC');
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {
		
		$pageError = '';

		if ($_POST['formName'] == 'editPreferencesForm') {

			// update DB
				for ($counter = 0; $counter < count($preferences); $counter++) {
					if ($preferences[$counter]['input_type'] == 'yesno_bootstrap_switch') {
						if (isset($_POST['preference_' . $preferences[$counter]['preference_id']])) $_POST['preference_' . $preferences[$counter]['preference_id']] = 1;
						else $_POST['preference_' . $preferences[$counter]['preference_id']] = 0;
					}

					updateDb('preferences', array('value'=>$_POST['preference_' . $preferences[$counter]['preference_id']], 'updated_by'=>$logged_in['user_id'], 'updated_on'=>date('Y-m-d H:i:s')), array('preference_id'=>$preferences[$counter]['preference_id']), null, null, null, null, 1);
				}

			// update log
				$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated system preferences";
				$logger->logItInDb($activity);
			
			// redirect
				header('Location: /admin_settings/main/default/preferences_updated');
				exit();

		}

	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>