<?php

	function retrieveRumours($matching = null, $containing = null, $otherCriteria = null, $sortBy = 'updated_on DESC', $limit = false) {
		
		global $dbConnection;
		global $tablePrefix;
		
		// build query
			if (@$matching['tag_id']) {
				$query = "SELECT " . $tablePrefix . "rumours_x_tags.*,";
				$query .= " ". $tablePrefix . "rumours.*,";
				$query .= " ". $tablePrefix . "rumours.enabled AS rumour_enabled,";
				$query .= " TRIM(CONCAT(" . $tablePrefix . "creators.first_name, ' ', " . $tablePrefix . "creators.last_name)) as created_by_full_name,";
				$query .= " " . $tablePrefix . "creators.username as created_by_username,";
				$query .= " " . $tablePrefix . "creators.user_id as created_by_user_id,";
				$query .= " TRIM(CONCAT(" . $tablePrefix . "enterers.first_name, ' ', " . $tablePrefix . "enterers.last_name)) as entered_by_full_name,";
				$query .= " " . $tablePrefix . "enterers.username as entered_by_username,";
				$query .= " " . $tablePrefix . "enterers.user_id as entered_by_user_id,";
				$query .= " TRIM(CONCAT(" . $tablePrefix . "updaters.first_name, ' ', " . $tablePrefix . "updaters.last_name)) as updated_by_full_name,";
				$query .= " " . $tablePrefix . "updaters.username as updated_by_username,";
				$query .= " " . $tablePrefix . "updaters.user_id as updated_by_user_id,";
				$query .= " TRIM(CONCAT(" . $tablePrefix . "assigned.first_name, ' ', " . $tablePrefix . "assigned.last_name)) as assigned_to_full_name,";
				$query .= " " . $tablePrefix . "assigned.username as assigned_to_username,";
				$query .= " " . $tablePrefix . "assigned.user_id as assigned_to_user_id,";
				$query .= " (SELECT COUNT(created_by) FROM " . $tablePrefix . "rumour_sightings WHERE " . $tablePrefix . "rumour_sightings.rumour_id = " . $tablePrefix . "rumours.rumour_id) as number_of_sightings,";
				$query .= " (SELECT COUNT(created_by) FROM " . $tablePrefix . "comments WHERE " . $tablePrefix . "comments.rumour_id = " . $tablePrefix . "rumours.rumour_id) as number_of_comments,";
				$query .= " (SELECT COUNT(flagged_by) FROM " . $tablePrefix . "comment_flags LEFT JOIN " . $tablePrefix . "comments ON " . $tablePrefix . "comment_flags.comment_id = " . $tablePrefix . "comments.comment_id WHERE " . $tablePrefix . "comments.rumour_id = " . $tablePrefix . "rumours.rumour_id) as number_of_flagged_comments,";
				$query .= " (SELECT COUNT(created_by) FROM " . $tablePrefix . "watchlist WHERE " . $tablePrefix . "watchlist.rumour_id = " . $tablePrefix . "rumours.rumour_id) as number_of_watchlists";
				$query .= " FROM " . $tablePrefix . "rumours_x_tags";
				$query .= " LEFT JOIN " . $tablePrefix . "rumours ON " . $tablePrefix . "rumours_x_tags.rumour_id = " . $tablePrefix . "rumours.rumour_id";
				$query .= " LEFT JOIN " . $tablePrefix . "users AS " . $tablePrefix . "creators ON " . $tablePrefix . "rumours.created_by = " . $tablePrefix . "creators.user_id";
				$query .= " LEFT JOIN " . $tablePrefix . "users AS " . $tablePrefix . "enterers ON " . $tablePrefix . "rumours.entered_by = " . $tablePrefix . "enterers.user_id";
				$query .= " LEFT JOIN " . $tablePrefix . "users AS " . $tablePrefix . "updaters ON " . $tablePrefix . "rumours.updated_by = " . $tablePrefix . "updaters.user_id";
				$query .= " LEFT JOIN " . $tablePrefix . "users AS " . $tablePrefix . "assigned ON " . $tablePrefix . "rumours.assigned_to = " . $tablePrefix . "assigned.user_id";
			}
			else {
				$query = "SELECT " . $tablePrefix . "rumours.*,";
				$query .= " ". $tablePrefix . "rumours.enabled AS rumour_enabled,";
				$query .= " TRIM(CONCAT(" . $tablePrefix . "creators.first_name, ' ', " . $tablePrefix . "creators.last_name)) as created_by_full_name,";
				$query .= " " . $tablePrefix . "creators.username as created_by_username,";
				$query .= " " . $tablePrefix . "creators.user_id as created_by_user_id,";
				$query .= " TRIM(CONCAT(" . $tablePrefix . "enterers.first_name, ' ', " . $tablePrefix . "enterers.last_name)) as entered_by_full_name,";
				$query .= " " . $tablePrefix . "enterers.username as entered_by_username,";
				$query .= " " . $tablePrefix . "enterers.user_id as entered_by_user_id,";
				$query .= " TRIM(CONCAT(" . $tablePrefix . "updaters.first_name, ' ', " . $tablePrefix . "updaters.last_name)) as updated_by_full_name,";
				$query .= " " . $tablePrefix . "updaters.username as updated_by_username,";
				$query .= " " . $tablePrefix . "updaters.user_id as updated_by_user_id,";
				$query .= " TRIM(CONCAT(" . $tablePrefix . "assigned.first_name, ' ', " . $tablePrefix . "assigned.last_name)) as assigned_to_full_name,";
				$query .= " " . $tablePrefix . "assigned.username as assigned_to_username,";
				$query .= " " . $tablePrefix . "assigned.user_id as assigned_to_user_id,";
				$query .= " (SELECT COUNT(created_by) FROM " . $tablePrefix . "rumour_sightings WHERE " . $tablePrefix . "rumour_sightings.rumour_id = " . $tablePrefix . "rumours.rumour_id) as number_of_sightings,";
				$query .= " (SELECT COUNT(created_by) FROM " . $tablePrefix . "comments WHERE " . $tablePrefix . "comments.rumour_id = " . $tablePrefix . "rumours.rumour_id) as number_of_comments,";
				$query .= " (SELECT COUNT(flagged_by) FROM " . $tablePrefix . "comment_flags LEFT JOIN " . $tablePrefix . "comments ON " . $tablePrefix . "comment_flags.comment_id = " . $tablePrefix . "comments.comment_id WHERE " . $tablePrefix . "comments.rumour_id = " . $tablePrefix . "rumours.rumour_id) as number_of_flagged_comments,";
				$query .= " (SELECT COUNT(created_by) FROM " . $tablePrefix . "watchlist WHERE " . $tablePrefix . "watchlist.rumour_id = " . $tablePrefix . "rumours.rumour_id) as number_of_watchlists";
				$query .= " FROM " . $tablePrefix . "rumours";
				$query .= " LEFT JOIN " . $tablePrefix . "users AS " . $tablePrefix . "creators ON " . $tablePrefix . "rumours.created_by = " . $tablePrefix . "creators.user_id";
				$query .= " LEFT JOIN " . $tablePrefix . "users AS " . $tablePrefix . "enterers ON " . $tablePrefix . "rumours.entered_by = " . $tablePrefix . "enterers.user_id";
				$query .= " LEFT JOIN " . $tablePrefix . "users AS " . $tablePrefix . "updaters ON " . $tablePrefix . "rumours.updated_by = " . $tablePrefix . "updaters.user_id";
				$query .= " LEFT JOIN " . $tablePrefix . "users AS " . $tablePrefix . "assigned ON " . $tablePrefix . "rumours.assigned_to = " . $tablePrefix . "assigned.user_id";
			}
			$query .= " WHERE 1=1";
			if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
			if ($containing) foreach ($containing as $field => $value) $query .= " AND " . $field . " LIKE '%" . $dbConnection->escape_string($value) . "%'";
			if ($otherCriteria) $query .= " AND (" . $otherCriteria . ")";
			if ($sortBy) $query .= " ORDER BY " . $sortBy;
			if ($limit) $query .= " LIMIT " . $dbConnection->escape_string($limit);
			
			$result = $dbConnection->query($query) or die('Unable to execute retrieveRumours: ' . $dbConnection->error . '<br /><br />' . $query);

		// create array
			$parser = new parser_TL();
			$items = $parser->mySqliResourceToArray($result);

		// clear memory
			$result->free();

		// return array
			return $items;
		
	}

	function retrieveSightings($matching = null, $containing = null, $otherCriteria = null, $sortBy = false, $limit = false) {
		
		global $dbConnection;
		global $tablePrefix;
		
		// build query
			$query = "SELECT " . $tablePrefix . "rumour_sightings.*,";
			$query .= " " . $tablePrefix . "rumour_sightings.country as sighting_country,";
			$query .= " " . $tablePrefix . "rumour_sightings.region as sighting_region,";
			$query .= " " . $tablePrefix . "users.*,";
			$query .= " " . $tablePrefix . "rumours.*,";
			$query .= " " . $tablePrefix . "rumours.country as country_occurred,";
			$query .= " " . $tablePrefix . "rumours.region as region_occurred";
			$query .= " FROM " . $tablePrefix . "rumour_sightings";
			$query .= " LEFT JOIN " . $tablePrefix . "users ON " . $tablePrefix . "rumour_sightings.created_by = " . $tablePrefix . "users.user_id";
			$query .= " LEFT JOIN " . $tablePrefix . "rumours ON " . $tablePrefix . "rumour_sightings.rumour_id = " . $tablePrefix . "rumours.rumour_id";
			$query .= " WHERE 1=1";
			if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
			if ($containing) foreach ($containing as $field => $value) $query .= " AND " . $field . " LIKE '%" . $dbConnection->escape_string($value) . "%'";
			if ($otherCriteria) $query .= " AND (" . $otherCriteria . ")";
			if ($sortBy) $query .= " ORDER BY " . $sortBy;
			if ($limit) $query .= " LIMIT " . $dbConnection->escape_string($limit);
			
			$result = $dbConnection->query($query) or die('Unable to execute retrieveSightings: ' . $dbConnection->error . '<br /><br />' . $query);

		// create array
			$parser = new parser_TL();
			$items = $parser->mySqliResourceToArray($result);

		// clear memory
			$result->free();

		// return array
			return $items;
		
	}
	
?>
