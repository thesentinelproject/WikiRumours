<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		if ($parameter1) $filters = $keyvalue_array->keyValueToArray(urldecode($parameter1), '|');
		
	// authenticate user
		if (!$logged_in['is_administrator'] || !$logged_in['can_edit_users']) forceLoginThenRedirectHere();
		
	// queries
		$rowsPerPage = 50;
		
		if (@$filters['keywords']) {
			$otherCriteria = '';
			$keywordExplode = explode(' ', $filters['keywords']);
			foreach ($keywordExplode as $keyword) {
				if ($keyword) {
					if ($otherCriteria) $otherCriteria .= " AND";
					$otherCriteria .= " (" . $tablePrefix . "users.username LIKE '%" . addSlashes($keyword) . "%' OR " . $tablePrefix . "users.first_name LIKE '%" . addSlashes($keyword) . "%' OR " . $tablePrefix . "users.last_name LIKE '%" . addSlashes($keyword) . "%' OR " . $tablePrefix . "users.email LIKE '%" . addSlashes($keyword) . "%')";
				}
			}
			$otherCriteria = trim($otherCriteria);
		}

		$matching = array();
		// anonymous
			if (!@$filters['hide_anonymous']) $filters['hide_anonymous'] = 'Y';
			if (@$filters['hide_anonymous'] == 'Y') $matching += array('anonymous'=>'0');
		// user type
			if (@$filters['user_type'] && ($filters['user_type'] == 'is_administrator' || $filters['user_type'] == 'is_moderator' || $filters['user_type'] == 'is_proxy' || $filters['user_type'] == 'is_community_liaison' || $filters['user_type'] == 'is_tester')) $matching += array($filters['user_type']=>'1');
		// sort by
			if (@$filters['sort_by'] == 'user') $sortBy = 'username ' . $operators->firstTrue(@$filters['sort_by_direction'], 'ASC');
			elseif (@$filters['sort_by'] == 'location') $sortBy = 'country ' . $operators->firstTrue(@$filters['sort_by_direction'], 'ASC') . ', city ' . $operators->firstTrue(@$filters['sort_by_direction'], 'ASC');
			elseif (@$filters['sort_by'] == 'registered') $sortBy = 'registered_on ' . $operators->firstTrue(@$filters['sort_by_direction'], 'ASC');
			elseif (@$filters['sort_by'] == 'last_login') $sortBy = 'last_login ' . $operators->firstTrue(@$filters['sort_by_direction'], 'ASC');
			else $sortBy = 'username ASC';
		
		$result = countInDb('users', 'user_id', $matching, null, null, null, @$otherCriteria);
		$numberOfUsers = floatval(@$result[0]['count']);
		
		$numberOfPages = max(1, ceil($numberOfUsers / $rowsPerPage));
		$filters['page'] = floatval(@$filters['page']);
		if ($filters['page'] < 1) $filters['page'] = 1;
		elseif ($filters['page'] > $numberOfPages) $filters['page'] = $numberOfPages;
		
		$users = retrieveUsers($matching, null, @$otherCriteria, $sortBy, floatval(($filters['page'] * $rowsPerPage) - $rowsPerPage) . ',' . $rowsPerPage);
			
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {

		if ($_POST['formName'] == 'adminUsersFilterForm' && $_POST['exportData'] == 'Y') {

			// retrieve data
				$result = retrieveUsers($matching, null, @$otherCriteria, $sortBy);

			// sanitize array
				$desiredFields = array('username', 'first_name', 'last_name', 'country', 'region', 'other_region', 'city', 'email', 'phone', 'secondary_phone', 'sms_notifications');
				$users = array();
				for ($counter = 0; $counter < count($result); $counter++) {
					$users[$counter] = array();
					foreach ($desiredFields as $field) {
						$users[$counter][$field] = $result[$counter][$field];
					}
				}

			// create CSV
				header( 'Content-Type: text/csv' );
				header( 'Content-Disposition: attachment;filename=users.csv');
				$csv = fopen('php://output', 'w');
				fputcsv($csv, $desiredFields);
				foreach ($users as $user) {
					fputcsv($csv, $user);
				}
				fclose($csv);
				exit();

		}

		elseif ($_POST['formName'] == 'adminUsersFilterForm') {
			$parameter1 = $keyvalue_array->updateKeyValue($parameter1, 'keywords', $_POST['keywords'], '|');
			$parameter1 = $keyvalue_array->updateKeyValue($parameter1, 'user_type', $_POST['user_type'], '|');
			$parameter1 = $keyvalue_array->updateKeyValue($parameter1, 'hide_anonymous', $_POST['hide_anonymous'], '|');
			$parameter1 = $keyvalue_array->updateKeyValue($parameter1, 'sort_by', $_POST['sort_by'], '|');
			$parameter1 = $keyvalue_array->updateKeyValue($parameter1, 'sort_by_direction', $_POST['sort_by_direction'], '|');
			header('Location: /admin_users/' . urlencode($parameter1));
			exit();
		}

	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>