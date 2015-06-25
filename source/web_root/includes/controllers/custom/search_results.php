<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		if ($parameter1) $filters = $keyvalue_array->keyValueToArray(urldecode($parameter1), '|');

	// clean input
		$allowableFilters = array('keywords', 'country_id', 'priority_id', 'status_id', 'tag_id', 'report', 'page', 'sort', 'view');
		if (@$filters) {
			foreach ($filters as $key=>$value) {
				if (!in_array($value, $allowableFilters)) unset($filters[$value]);
			}
		}
		
	// build query
		$page = floatval(@$filters['page']);

		if (@$filters['view'] != 'map') $filters['view'] = 'table';

		$report = @$filters['report'];

		$sort = @$filters['sort'];
		if ($report == 'common') $sort = 'number_of_sightings DESC';
		elseif ($sort == 'priority_high') $sort = 'severity DESC';
		elseif ($sort == 'priority_low') $sort = 'severity ASC';
		elseif ($sort == 'status_up') $sort = $tablePrefix . 'rumours.status_id ASC';
		elseif ($sort == 'status_down') $sort = $tablePrefix . 'rumours.status_id DESC';
		elseif ($sort == 'date_low') $sort = $tablePrefix . 'rumours.updated_on ASC';
		else {
			$sort = $tablePrefix . 'rumours.updated_on DESC';
			$filters['sort'] = 'date_high';
		}
		
		$keywords = @$filters['keywords'];
		
		$otherCriteria = $tablePrefix . "rumours.enabled = '1'";
		if (@$pseudonym['pseudonym_id']) $otherCriteria = $tablePrefix . "rumours.pseudonym_id = '" . $pseudonym['pseudonym_id'] . "'";
		if (@$filters['country_id']) $otherCriteria .= " AND (" . $tablePrefix . "rumours.country_id = '" . $filters['country_id'] . "')";
		if (@$filters['status_id']) $otherCriteria .= " AND (" . $tablePrefix . "rumours.status_id = '" . $filters['status_id'] . "')";
		if (@$filters['priority_id']) $otherCriteria .= " AND (" . $tablePrefix . "rumours.priority_id = '" . $filters['priority_id'] . "')";
		if ($keywords) {
			$keywordsExplode = explode(' ', $keywords);
			$otherCriteria .= " AND (1=2";
			foreach ($keywordsExplode as $keyword) {
				if (trim($keyword)) $otherCriteria .= " OR LOWER(description) LIKE '%" . addSlashes(trim(strtolower($keyword))) . "%'";
			}
			$otherCriteria .= ")";
		}

		$rowsPerPage = 50;
		if ($report == 'common' || $report == 'recent') $limit = 150;
		else $limit = 500; // max. search results
		
	// queries
		if (@$filters['tag_id']) {
			$result = retrieveRumours(array('tag_id'=>$filters['tag_id']), null, @$otherCriteria, null, @$limit);
			$numberOfRumours = count($result);

			$numberOfPages = max(1, ceil($numberOfRumours / $rowsPerPage));
			if ($report == 'recent' || $report == 'common') $numberOfPages = 1;
			if ($page < 1) $page = 1;
			elseif ($page > $numberOfPages) $page = $numberOfPages;
			
			$rumours = retrieveRumours(array('tag_id'=>$filters['tag_id']), null, @$otherCriteria, $sort, floatval(($page * $rowsPerPage) - $rowsPerPage) . ',' . $rowsPerPage);

			$map = retrieveRumours(array('tag_id'=>$filters['tag_id']), null, @$otherCriteria . " AND " . $tablePrefix . "rumours.latitude <> 0 AND " . $tablePrefix . "rumours.longitude <> 0", $sort, @$limit);
		}
		else {
			$result = countInDb('rumours', 'rumour_id', null, null, null, null, @$otherCriteria);
			$numberOfRumours = min(floatval(@$result[0]['count']), @$limit);
		
			$numberOfPages = max(1, ceil($numberOfRumours / $rowsPerPage));
			if ($report == 'recent' || $report == 'common') $numberOfPages = 1;
			if ($page < 1) $page = 1;
			elseif ($page > $numberOfPages) $page = $numberOfPages;
			
			$rumours = retrieveRumours(null, null, @$otherCriteria, $sort, floatval(($page * $rowsPerPage) - $rowsPerPage) . ',' . $rowsPerPage);

			$map = retrieveRumours(null, null, @$otherCriteria . " AND " . $tablePrefix . "rumours.latitude <> 0 AND " . $tablePrefix . "rumours.longitude <> 0", $sort, @$limit);
		}

	if (@$filters['view'] == 'map') $pageLoadEvents = "populateMap();";
	if ($report == 'recent') $pageTitle = "Recent Rumours";
	elseif ($report == 'common') $pageTitle = "Most Common Rumours";
	else $pageTitle = "Search Results";
	
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