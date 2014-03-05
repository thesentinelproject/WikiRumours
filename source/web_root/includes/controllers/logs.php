<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in['is_administrator']) forceLoginThenRedirectHere();
		
	// parse query string
		$parseFilters = explode('|', urldecode($parameter1));

		$filters = array();
		foreach ($parseFilters as $value) {
			$splitFilters = explode('=', $value);
			if ($splitFilters[1]) $filters[$splitFilters[0]] = $splitFilters[1];
		}

	// queries
		if (!@$filters['sortBy']) $filters['sortBy'] = 'connected_on DESC';

		$result = countInDb('logs', 'log_id');
		$numberOfLogs = floatval(@$result[0]['count']);
		
		$numberOfPages = max(1, ceil($numberOfLogs / $maxNumberOfTableRowsPerPage));
		$filters['page'] = floatval(@$filters['page']);
		if ($filters['page'] < 1) $filters['page'] = 1;
		elseif ($filters['page'] > $numberOfPages) $filters['page'] = $numberOfPages;
		
		$logs = retrieveFromDb('logs', null, null, null, null, null, $filters['sortBy'], floatval(($filters['page'] * $maxNumberOfTableRowsPerPage) - $maxNumberOfTableRowsPerPage) . ',' . $maxNumberOfTableRowsPerPage);
		
	// instantiate required class(es)
		$validator = new inputValidator_TL();
		$operators = new operators_TL();
		$parser = new parser_TL();
		
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