<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		$parseFilters = explode('|', urldecode($parameter1));

		$filters = array();
		foreach ($parseFilters as $value) {
			$splitFilters = explode('=', $value);
			if ($splitFilters[1]) $filters[$splitFilters[0]] = $splitFilters[1];
		}
		
	// clean input
		$allowableFilters = array('keywords', 'country', 'priority', 'status', 'tag_id', 'report', 'page', 'sort');
		foreach ($filters as $key=>$value) {
			if (!in_array($value, $allowableFilters)) unset($filters[$value]);
		}
		
		$otherCriteria = null;

		$page = floatval(@$filters['page']);

		$sort = @$filters['sort'];
		if ($report == 'common') $sort = 'number_of_sightings DESC';
		elseif ($sort == 'priority_high') $sort = 'priority DESC';
		elseif ($sort == 'priority_low') $sort = 'priority ASC';
		elseif ($sort == 'status_up') $sort = 'status ASC';
		elseif ($sort == 'status_down') $sort = 'status DESC';
		elseif ($sort == 'date_low') $sort = $tablePrefix . 'rumours.updated_on ASC';
		else {
			$sort = $tablePrefix . 'rumours.updated_on DESC';
			$filters['sort'] = 'date_high';
		}
		
		$report = @$filters['report'];

		$keywords = @$filters['keywords'];
		
		if (!$logged_in['is_proxy'] && !$logged_in['is_moderator'] && !$logged_in['is_administrator']) {
			$otherCriteria .= " AND (" . $tablePrefix . "rumours.enabled = '1')"; // clarifies join
		}
		
		if (@$filters['country']) {
			$otherCriteria .= " AND (" . $tablePrefix . "rumours.country = '" . $filters['country'] . "')"; // clarifies join
		}

		if (@$filters['status']) {
			$otherCriteria .= " AND (status = '" . $filters['status'] . "')";
		}

		if (@$filters['priority']) {
			$otherCriteria .= " AND (priority = '" . $filters['priority'] . "')";
		}
		
		if ($keywords) {
			$keywordsExplode = explode(' ', $keywords);
			$otherCriteria .= " AND (1=2";
			foreach ($keywordsExplode as $keyword) {
				if (trim($keyword)) $otherCriteria .= " OR LOWER(description) LIKE '%" . addSlashes(trim(strtolower($keyword))) . "%'";
			}
			$otherCriteria .= ")";
		}

		if ($otherCriteria) $otherCriteria = "1=1" . $otherCriteria;


	// queries
		if (@$filters['tag_id']) {
			$result = retrieveRumours(null, null, @$otherCriteria);
			$numberOfRumours = count($result);
		}
		else {
			$result = countInDb('rumours', 'rumour_id', null, null, null, null, @$otherCriteria);
			$numberOfRumours = floatval(@$result[0]['count']);
		}
		
		$numberOfPages = max(1, ceil($numberOfRumours / $maxNumberOfTableRowsPerPage));
		if ($report == 'recent' || $report == 'common') $numberOfPages = 1;
		if ($page < 1) $page = 1;
		elseif ($page > $numberOfPages) $page = $numberOfPages;
		
		$rumours = retrieveRumours(null, null, @$otherCriteria, $sort, floatval(($page * $maxNumberOfTableRowsPerPage) - $maxNumberOfTableRowsPerPage) . ',' . $maxNumberOfTableRowsPerPage);
	
	// instantiate required class(es)
		$parser = new parser_TL();
		$keyValue = new keyvalueArray_TL();
		
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