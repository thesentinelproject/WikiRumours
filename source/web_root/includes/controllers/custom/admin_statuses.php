<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in['is_administrator'] || !$logged_in['can_edit_settings']) forceLoginThenRedirectHere();
		
	// parse query string
		$pageStatus = $parameter1;
		
	// queries
		$statuses = retrieveStatuses();
		$numberOfStatuses = floatval(count($statuses));
		
	$pageTitle = "Statuses";
	$sectionTitle = "Administration";

/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {

		$pageError = null;

		if ($_POST['formName'] == 'editStatusesForm' && $_POST['statusToDelete']) {

			// validate
				$status = retrieveStatuses(array('status_id'=>$_POST['statusToDelete']), null, null, null, 1);
				if (!count($status)) $pageError .= "Unable to find status to delete. ";
				elseif ($status[0]['delete_prohibited']) $pageError .= "Delete prohibited for this status. ";
				elseif ($status[0]['number_of_rumours'] > 0) $pageError .= "Unable to delete because there are " . $priority[0]['number_of_rumours'] . " statuses set to this priority. Please modify those rumours and then try again. ";
				else {

					// delete priority
						deleteFromDb('statuses', array('status_id'=>$status[0]['status_id']), null, null, null, null, 1);

					// update logs
						$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has deleted the status &quot;" . $status[0]['status'] . "&quot; (status_id " . $status[0]['status_id'] . ")";
						$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'status_id=' . $status[0]['status_id']));

					// redirect
						header('Location: /admin_statuses/status_deleted');
						exit();

				}

		}
		elseif ($_POST['formName'] == 'editStatusesForm') {

			// clean input
				$_POST = $parser->trimAll($_POST);
				for ($counter = 0; $counter < count($statuses); $counter++) {
					$_POST['position_' . $statuses[$counter]['status_id']] = floatval($_POST['position_' . $statuses[$counter]['status_id']]);
					if (isset($_POST['is_closed_' . $statuses[$counter]['status_id']])) $_POST['is_closed_' . $statuses[$counter]['status_id']] = 1;
					else $_POST['is_closed_' . $statuses[$counter]['status_id']] = 0;
				}
				$_POST['position_add'] = floatval($_POST['position_add']);
				if (isset($_POST['is_closed_add'])) $_POST['is_closed_add'] = 1;
				else $_POST['is_closed_add'] = 0;
				
			// check for errors
				// check edit
					for ($counter = 0; $counter < count($statuses); $counter++) {
						if (!$_POST['status_' . $statuses[$counter]['status_id']]) $pageError .= "Please specify a status name. ";
					}

			if (!$pageError) {
				
				// update edit
					for ($counter = 0; $counter < count($statuses); $counter++) {
						updateDb('statuses', array('status'=>$_POST['status_' . $statuses[$counter]['status_id']], 'position'=>$_POST['position_' . $statuses[$counter]['status_id']], 'is_closed'=>$_POST['is_closed_' . $statuses[$counter]['status_id']]), array('status_id'=>$statuses[$counter]['status_id']), null, null, null, null, 1);
					}
				// update add
					if ($_POST['status_add']) insertIntoDb('statuses', array('status'=>$_POST['status_add'], 'position'=>$_POST['position_add'], 'is_closed'=>$_POST['is_closed_add']));

				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated statuses";
					$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id']));
				
				// redirect
					header('Location: /admin_statuses/statuses_updated');
					exit();

			}

		}

	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
		if (!$pageStatus) $pageWarning = "Please be cautious. Any changes made here will impact rumours which are already saved in the system.";
	}
		
?>