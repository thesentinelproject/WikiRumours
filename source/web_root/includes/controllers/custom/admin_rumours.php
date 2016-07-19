<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		if ($tl->page['parameter1']) $filters = $keyvalue_array->keyValueToArray(urldecode($tl->page['parameter1']), '|');
		
	// authenticate user
		if (!$logged_in['is_administrator']) $authentication_manager->forceLoginThenRedirectHere(true);
		
	// queries
		$rowsPerPage = 50;

		if (@$filters['keywords']) {
			$otherCriteria = '';
			$keywordExplode = explode(' ', $filters['keywords']);
			foreach ($keywordExplode as $keyword) {
				if ($keyword) {
					if ($otherCriteria) $otherCriteria .= " AND";
					$otherCriteria .= " " . $tablePrefix . "rumours.description LIKE '%" . addSlashes($keyword) . "%'";
				}
			}
			$otherCriteria = trim($otherCriteria);
		}

		$matching = array();
		// status
			if (@$filters['rumour_status']) $matching += array($tablePrefix . 'rumours.status_id'=>$filters['rumour_status']);
		// country
			if (@$filters['rumour_country']) $matching += array($tablePrefix . 'rumours.country_id'=>$filters['rumour_country']);
		// sort by
			if (@$filters['sort_by'] == 'rumour') $sortBy = 'description ' . $operators->firstTrue(@$filters['sort_by_direction'], 'ASC');
			elseif (@$filters['sort_by'] == 'date') $sortBy = 'updated_on ' . $operators->firstTrue(@$filters['sort_by_direction'], 'ASC') . ', city ' . $operators->firstTrue(@$filters['sort_by_direction'], 'ASC');
			elseif (@$filters['sort_by'] == 'location') $sortBy = $tablePrefix . 'countries.country ' . $operators->firstTrue(@$filters['sort_by_direction'], 'ASC') . ', city ' . $operators->firstTrue(@$filters['sort_by_direction'], 'ASC');
			elseif (@$filters['sort_by'] == 'status') $sortBy = $tablePrefix . 'statuses.status ' . $operators->firstTrue(@$filters['sort_by_direction'], 'ASC');
			elseif (@$filters['sort_by'] == 'assigned_to') $sortBy = 'assigned_to ' . $operators->firstTrue(@$filters['sort_by_direction'], 'ASC');
			else $sortBy = 'description ASC';

		$result = countInDb('rumours', 'rumour_id', $matching, null, null, null, @$otherCriteria);
		$numberOfRumours = floatval(@$result[0]['count']);
		
		$numberOfPages = max(1, ceil($numberOfRumours / $rowsPerPage));
		$filters['page'] = floatval(@$filters['page']);
		if ($filters['page'] < 1) $filters['page'] = 1;
		elseif ($filters['page'] > $numberOfPages) $filters['page'] = $numberOfPages;
		
		$rumours = retrieveRumours($matching, null, @$otherCriteria, $sortBy, floatval(($filters['page'] * $rowsPerPage) - $rowsPerPage) . ',' . $rowsPerPage);

	$tl->page['title'] = 'Rumours';
	$tl->page['section'] = 'Administration';
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {
		
		if ($_POST['formName'] == 'adminRumoursForm') {
			$tl->page['parameter1'] = $keyvalue_array->updateKeyValue($tl->page['parameter1'], 'keywords', $_POST['keywords'], '|');
			$tl->page['parameter1'] = $keyvalue_array->updateKeyValue($tl->page['parameter1'], 'rumour_status', $_POST['rumour_status'], '|');
			$tl->page['parameter1'] = $keyvalue_array->updateKeyValue($tl->page['parameter1'], 'rumour_country', $_POST['rumour_country'], '|');
			$tl->page['parameter1'] = $keyvalue_array->updateKeyValue($tl->page['parameter1'], 'sort_by', $_POST['sort_by'], '|');
			$tl->page['parameter1'] = $keyvalue_array->updateKeyValue($tl->page['parameter1'], 'sort_by_direction', $_POST['sort_by_direction'], '|');
			$authentication_manager->forceRedirect('/admin_rumours/' . urlencode($tl->page['parameter1']));
		}
		
	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>