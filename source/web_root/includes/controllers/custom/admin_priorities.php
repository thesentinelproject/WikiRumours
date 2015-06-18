<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in['is_administrator'] || !$logged_in['can_edit_settings']) forceLoginThenRedirectHere();
		
	// parse query string
		$pageStatus = $parameter1;
		
	// queries
		$priorities = retrievePriorities(null, null, null, $sortBy = 'severity ASC');
		$numberOfPriorities = floatval(count($priorities));

/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {

		$pageError = null;

		if ($_POST['formName'] == 'editPrioritiesForm' && $_POST['priorityToDelete']) {

			// validate
				$priority = retrievePriorities(array('priority_id'=>$_POST['priorityToDelete']), null, null, null, 1);
				if (!count($priority)) $pageError .= "Unable to find priority to delete. ";
				elseif ($priority[0]['number_of_rumours'] > 0) $pageError .= "Unable to delete because there are " . $priority[0]['number_of_rumours'] . " rumours set to this priority. Please modify those rumours and then try again. ";
				else {

					// delete priority
						deleteFromDb('priorities', array('priority_id'=>$priority[0]['priority_id']), null, null, null, null, 1);

					// update logs
						$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has deleted the priority &quot;" . $priority[0]['priority'] . "&quot; (priority_id " . $priority[0]['priority_id'] . ")";
						$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'priority_id=' . $priority[0]['priority_id']));

					// redirect
						header('Location: /admin_priorities/priority_deleted');
						exit();

				}

		}
		elseif ($_POST['formName'] == 'editPrioritiesForm') {

			// clean input
				$_POST = $parser->trimAll($_POST);
				for ($counter = 0; $counter < count($priorities); $counter++) {
					$_POST['severity_' . $priorities[$counter]['priority_id']] = floatval($_POST['severity_' . $priorities[$counter]['priority_id']]);
					$_POST['action_required_in_' . $priorities[$counter]['priority_id']] = floatval($_POST['action_required_in_' . $priorities[$counter]['priority_id']]);
				}
				$_POST['severity_add'] = floatval($_POST['severity_add']);
				$_POST['action_required_in_add'] = floatval($_POST['action_required_in_add']);
				
			// check for errors
				// check edit
					for ($counter = 0; $counter < count($priorities); $counter++) {
						if (!$_POST['priority_' . $priorities[$counter]['priority_id']]) $pageError .= "Please specify a priority name. ";
					}
				// check add
					if (($_POST['severity_add'] || $_POST['action_required_in_add']) && !$_POST['priority_add']) $pageError .= "Please specify a new priority name. ";

			if (!$pageError) {
				
				// update edit
					for ($counter = 0; $counter < count($priorities); $counter++) {
						updateDb('priorities', array('priority'=>$_POST['priority_' . $priorities[$counter]['priority_id']], 'severity'=>$_POST['severity_' . $priorities[$counter]['priority_id']], 'action_required_in'=>$_POST['action_required_in_' . $priorities[$counter]['priority_id']]), array('priority_id'=>$priorities[$counter]['priority_id']), null, null, null, null, 1);
					}
				// update add
					if ($_POST['priority_add']) {
						$faqID = insertIntoDb('priorities', array('priority'=>$_POST['priority_add'], 'severity'=>$_POST['severity_add'], 'action_required_in'=>$_POST['action_required_in_add']));
					}

				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has updated priorities";
					$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id']));
				
				// redirect
					header('Location: /admin_priorities/priorities_updated');
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