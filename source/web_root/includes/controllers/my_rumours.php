<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$page = $parameter1;
		
	// authentication
		if (!$logged_in) forceLoginThenRedirectHere();
		
	// queries
		if ($logged_in['is_moderator']) $assignedRumours = retrieveRumours(null, null, "(assigned_to = 0 OR assigned_to = " . $logged_in['user_id'] . ") AND (status = 'NU' OR status = 'UI')", $tablePrefix . 'rumours.updated_on DESC');
		else $assignedRumours = retrieveRumours(null, null, "(assigned_to = " . $logged_in['user_id'] . ") AND (status = 'NU' OR status = 'UI')", $tablePrefix . 'rumours.updated_on DESC');
		
		$result = countInDb('rumours', 'rumour_id', array('created_by'=>$logged_in['user_id'], $tablePrefix . 'rumours.enabled'=>'1'));
		$numberOfMyRumours = floatval(@$result[0]['count']);
		
		$numberOfPages = max(1, ceil($numberOfMyRumours / $maxNumberOfTableRowsPerPage));
		if ($page < 1) $page = 1;
		elseif ($page > $numberOfPages) $page = $numberOfPages;
		
		$myRumours = retrieveRumours(array('created_by'=>$logged_in['user_id'], $tablePrefix . 'rumours.enabled'=>'1'), null ,null, $tablePrefix . 'rumours.updated_on DESC', floatval(($page * $maxNumberOfTableRowsPerPage) - $maxNumberOfTableRowsPerPage) . ',' . $maxNumberOfTableRowsPerPage);

	// instantiate required class(es)
		$parser = new parser_TL();
		$operators = new operators_TL();
		
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