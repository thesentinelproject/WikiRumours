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
		$allowableFilters = array('keywords', 'country', 'status', 'tag_id', 'report', 'page', 'sort');
		foreach ($filters as $key=>$value) {
			if (!in_array($value, $allowableFilters)) unset($filters[$value]);
		}
		
		$page = floatval(@$filters['page']);
		unset($filters['page']);

		$sort = @$filters['sort'];
		unset($filters['sort']);
		
		$report = @$filters['report'];
		unset($filters['report']);
		
		$keywords = @$filters['keywords'];
		unset($filters['keywords']);
		
		if ($report == 'recent') {
			$sort = 'updated_on DESC';
			$page = 1;
			unset($filters);
		}
		
		if ($report == 'common') {
			$sort = 'number_of_sightings DESC';
			$page = 1;
			unset($filters);
		}
		
		if (!$logged_in['is_proxy'] && !$logged_in['is_moderator'] && !$logged_in['is_administrator']) {
			$filters[$tablePrefix . 'rumours.enabled'] = 1;
		}
		
		if (@$countriesShort_TL[@$filters['country']]) {
			$filters[$tablePrefix . 'rumours.country'] = $filters['country']; // remove ambiguity in join
		}
		if (@$filters['country']) unset($filters['country']);
		
		if (@$filters['status'] && !@$rumourStatuses[@$filters['status']]) {
			unset($filters['status']);
		}

		if ($keywords) {
			$otherCriteria = "1=2";
			$keywordsExplode = explode(' ', $keywords);
			foreach ($keywordsExplode as $keyword) {
				if (trim($keyword)) $otherCriteria .= " OR LOWER(description) LIKE '%" . addSlashes(trim(strtolower($keyword))) . "%'";
			}
			unset ($filters['keywords']);
		}

	// queries
		if (@$filters['tag_id']) {
			$result = retrieveRumours(@$filters, null, @$otherCriteria);
			$numberOfRumours = count($result);
		}
		else {
			$result = countInDb('rumours', 'rumour_id', @$filters, null, null, null, @$otherCriteria);
			$numberOfRumours = floatval(@$result[0]['count']);
		}
		
		$numberOfPages = max(1, ceil($numberOfRumours / $maxNumberOfTableRowsPerPage));
		if ($report == 'recent' || $report == 'common') $numberOfPages = 1;
		if ($page < 1) $page = 1;
		elseif ($page > $numberOfPages) $page = $numberOfPages;
		
		$rumours = retrieveRumours(@$filters, null, @$otherCriteria, $sort, floatval(($page * $maxNumberOfTableRowsPerPage) - $maxNumberOfTableRowsPerPage) . ',' . $maxNumberOfTableRowsPerPage);
	
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