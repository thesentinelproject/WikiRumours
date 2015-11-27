<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		if ($parameter1) $filters = $keyvalue_array->keyValueToArray(urldecode($parameter1), '|');

	// create export
		if (@$filters['report'] == 'users') {

			// authenticate access
				if (!$logged_in['is_administrator'] || !$logged_in['can_edit_users']) forceLoginThenRedirectHere();

			// retrieve data
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
		elseif (@$filters['report'] == 'rumours') {

			// authenticate user
				if (!$logged_in['is_administrator']) forceLoginThenRedirectHere();

			// retrieve data				
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
		elseif (@$filters['report'] == 'logs') {

			// authenticate user
				if (!$logged_in['is_administrator']) forceLoginThenRedirectHere();
		
			// retrieve data				
				if (@$filters['keywords']) {
					$otherCriteria = '';
					$keywordExplode = explode(' ', $filters['keywords']);
					foreach ($keywordExplode as $keyword) {
						if ($keyword) {
							if ($otherCriteria) $otherCriteria .= " AND";
							$otherCriteria .= " " . $tablePrefix . "logs.activity LIKE '%" . addSlashes($keyword) . "%'";
						}
					}
					$otherCriteria = trim($otherCriteria);
				}

				$result = retrieveFromDb('logs', null, null, null, null, null, @$otherCriteria, null, 'connected_on DESC');

			// sanitize array
				$desiredFields = array('connected_on', 'connection_type', 'activity', 'error_message', 'is_error', 'is_resolved', 'is_released', 'connection_length_in_seconds');
				$logs = array();
				for ($counter = 0; $counter < count($result); $counter++) {
					$logs[$counter] = array();
					foreach ($desiredFields as $field) {
						$logs[$counter][$field] = $result[$counter][$field];
					}
				}

			// create CSV
				header( 'Content-Type: text/csv' );
				header( 'Content-Disposition: attachment;filename=logs.csv');
				$csv = fopen('php://output', 'w');
				fputcsv($csv, $desiredFields);
				foreach ($logs as $log) {
					fputcsv($csv, $log);
				}
				fclose($csv);
				exit();

		}
		else {
			header('Location: /404');
			exit();
		}

/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */

/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
?>