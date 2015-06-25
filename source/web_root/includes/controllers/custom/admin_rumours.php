<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		if ($parameter1) $filters = $keyvalue_array->keyValueToArray(urldecode($parameter1), '|');
		
	// authenticate user
		if (!$logged_in['is_administrator']) forceLoginThenRedirectHere();
		
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
			if (@$filters['rumour_country'] && @$countries_TL[$filters['rumour_country']]) $matching += array($tablePrefix . 'rumours.country_id'=>$filters['rumour_country']);
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

	$pageTitle = 'Rumours';
	$sectionTitle = 'Administration';
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {
		
		if ($_POST['formName'] == 'adminRumoursForm' && $_POST['exportData'] == 'Y') {

			// retrieve data
				$result = retrieveRumours($matching, null, @$otherCriteria, $sortBy);

			// sanitize array
				$desiredFields = array('description', 'updated_on', 'country', 'city', 'status', 'assigned_to_username', 'number_of_sightings', 'number_of_comments', 'number_of_watchlists');
				$rumours = array();
				for ($counter = 0; $counter < count($result); $counter++) {
					$rumours[$counter] = array();
					foreach ($desiredFields as $field) {
						$rumours[$counter][$field] = $result[$counter][$field];
					}
				}

			// create CSV
				header( 'Content-Type: text/csv' );
				header( 'Content-Disposition: attachment;filename=rumours.csv');
				$csv = fopen('php://output', 'w');
				fputcsv($csv, $desiredFields);
				foreach ($rumours as $rumour) {
					fputcsv($csv, $rumour);
				}
				fclose($csv);
				exit();

		}

		elseif ($_POST['formName'] == 'adminRumoursForm') {
			$parameter1 = $keyvalue_array->updateKeyValue($parameter1, 'keywords', $_POST['keywords'], '|');
			$parameter1 = $keyvalue_array->updateKeyValue($parameter1, 'rumour_status', $_POST['rumour_status'], '|');
			$parameter1 = $keyvalue_array->updateKeyValue($parameter1, 'rumour_country', $_POST['rumour_country'], '|');
			$parameter1 = $keyvalue_array->updateKeyValue($parameter1, 'sort_by', $_POST['sort_by'], '|');
			$parameter1 = $keyvalue_array->updateKeyValue($parameter1, 'sort_by_direction', $_POST['sort_by_direction'], '|');
			header('Location: /admin_rumours/' . urlencode($parameter1));
			exit();
		}
		
	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>