<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		if ($tl->page['parameter1']) $filters = $keyvalue_array->keyValueToArray(urldecode($tl->page['parameter1']), '|');
		
	// authenticate user
		if (!$logged_in['is_administrator'] || !$logged_in['can_edit_users']) $authentication_manager->forceLoginThenRedirectHere(true);
		
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
		
	$tl->page['title'] = "All Users";
	$tl->page['section'] = "Administration";
			
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {

		if ($_POST['formName'] == 'adminUsersFilterForm') {
			$tl->page['parameter1'] = $keyvalue_array->updateKeyValue($tl->page['parameter1'], 'keywords', $_POST['keywords'], '|');
			$tl->page['parameter1'] = $keyvalue_array->updateKeyValue($tl->page['parameter1'], 'user_type', $_POST['user_type'], '|');
			$tl->page['parameter1'] = $keyvalue_array->updateKeyValue($tl->page['parameter1'], 'hide_anonymous', $_POST['hide_anonymous'], '|');
			$tl->page['parameter1'] = $keyvalue_array->updateKeyValue($tl->page['parameter1'], 'sort_by', $_POST['sort_by'], '|');
			$tl->page['parameter1'] = $keyvalue_array->updateKeyValue($tl->page['parameter1'], 'sort_by_direction', $_POST['sort_by_direction'], '|');
			$authentication_manager->forceRedirect('/admin_users/' . urlencode($tl->page['parameter1']));
		}

	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>