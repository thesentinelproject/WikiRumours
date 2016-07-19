<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		if ($tl->page['parameter1']) $filters = $keyvalue_array->keyValueToArray(urldecode($tl->page['parameter1']), '|');
		
	// authentication
		if (!$logged_in) $authentication_manager->forceLoginThenRedirectHere();
		
	// queries

		// assigned to me
			if ($logged_in['is_moderator']) $otherCriteria = "(assigned_to = '" . $logged_in['user_id'] . "' OR assigned_to = '0')";
			else $otherCriteria = "assigned_to = '" . $logged_in['user_id'] . "'";

			if (@$pseudonym['pseudonym_id']) $otherCriteria .= " AND " . $tablePrefix . "rumours.pseudonym_id = '" . intval($pseudonym['pseudonym_id']) . "'";
			
			$otherCriteria .= " AND (is_closed = '0' OR is_closed IS NULL)";

			$assignedRumours = retrieveRumours(array($tablePrefix . 'rumours.enabled'=>'1'), null, $otherCriteria, $tablePrefix . 'rumours.updated_on DESC');

		// reported by me	
			$result = countInDb('rumours', 'rumour_id', array('created_by'=>$logged_in['user_id'], $tablePrefix . 'rumours.enabled'=>'1'));
			$numberOfMyRumours = floatval(@$result[0]['count']);
			
			$rowsPerPage = 50;
			$numberOfPages = max(1, ceil($numberOfMyRumours / $rowsPerPage));
			if (@$filters['page'] < 1) $filters['page'] = 1;
			elseif (@$filters['page'] > $numberOfPages) $filters['page'] = $numberOfPages;
			
			$myRumours = retrieveRumours(array('created_by'=>$logged_in['user_id'], $tablePrefix . 'rumours.enabled'=>'1'), null, ($pseudonym['pseudonym_id'] ? $tablePrefix . "rumours.pseudonym_id = '" . intval($pseudonym['pseudonym_id']) . "'" : false), $tablePrefix . 'rumours.updated_on DESC', floatval(($filters['page'] * $rowsPerPage) - $rowsPerPage) . ',' . $rowsPerPage);

/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {
	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>