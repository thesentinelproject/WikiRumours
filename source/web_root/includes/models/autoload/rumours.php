<?php

	function retrieveRumours($matching = null, $containing = null, $otherCriteria = null, $sortBy = 'updated_on DESC', $limit = false) {
		
		global $dbConnection;
		global $tablePrefix;
		
		// build query
			if (@$matching['tag_id']) {
				$query = "SELECT " . $tablePrefix . "rumours_x_tags.*,";
				$query .= " ". $tablePrefix . "rumours.*,";
				$query .= " ". $tablePrefix . "rumours.occurred_on,";
				$query .= " (SELECT IF (is_closed = 0, DATE_ADD(updated_on, INTERVAL action_required_in DAY), '')) as update_by, ";
				$query .= " " . $tablePrefix . "countries.country,";
				$query .= " " . $tablePrefix . "statuses.status,";
				$query .= " " . $tablePrefix . "statuses.icon as status_icon,";
				$query .= " " . $tablePrefix . "statuses.is_closed,";
				$query .= " " . $tablePrefix . "priorities.priority,";
				$query .= " " . $tablePrefix . "priorities.severity,";
				$query .= " " . $tablePrefix . "priorities.icon as priority_icon,";
				$query .= " " . $tablePrefix . "rumours.enabled AS rumour_enabled,";
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
				$query .= " LEFT JOIN " . $tablePrefix . "countries ON " . $tablePrefix . "rumours.country_id = " . $tablePrefix . "countries.country_id";
				$query .= " LEFT JOIN " . $tablePrefix . "statuses ON " . $tablePrefix . "rumours.status_id = " . $tablePrefix . "statuses.status_id";
				$query .= " LEFT JOIN " . $tablePrefix . "priorities ON " . $tablePrefix . "rumours.priority_id = " . $tablePrefix . "priorities.priority_id";
				$query .= " LEFT JOIN " . $tablePrefix . "users AS " . $tablePrefix . "creators ON " . $tablePrefix . "rumours.created_by = " . $tablePrefix . "creators.user_id";
				$query .= " LEFT JOIN " . $tablePrefix . "users AS " . $tablePrefix . "enterers ON " . $tablePrefix . "rumours.entered_by = " . $tablePrefix . "enterers.user_id";
				$query .= " LEFT JOIN " . $tablePrefix . "users AS " . $tablePrefix . "updaters ON " . $tablePrefix . "rumours.updated_by = " . $tablePrefix . "updaters.user_id";
				$query .= " LEFT JOIN " . $tablePrefix . "users AS " . $tablePrefix . "assigned ON " . $tablePrefix . "rumours.assigned_to = " . $tablePrefix . "assigned.user_id";
			}
			else {
				$query = "SELECT " . $tablePrefix . "rumours.*,";
				$query .= " ". $tablePrefix . "rumours.occurred_on,";
				$query .= " (SELECT IF (is_closed = 0, DATE_ADD(updated_on, INTERVAL action_required_in DAY), '')) as update_by, ";
				$query .= " " . $tablePrefix . "countries.country,";
				$query .= " " . $tablePrefix . "statuses.status,";
				$query .= " " . $tablePrefix . "statuses.icon as status_icon,";
				$query .= " " . $tablePrefix . "statuses.is_closed,";
				$query .= " " . $tablePrefix . "priorities.priority,";
				$query .= " " . $tablePrefix . "priorities.severity,";
				$query .= " " . $tablePrefix . "priorities.icon as priority_icon,";
				$query .= " " . $tablePrefix . "rumours.enabled AS rumour_enabled,";
				$query .= " " . $tablePrefix . "rumours.updated_on AS rumour_updated_on,";
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
				$query .= " LEFT JOIN " . $tablePrefix . "countries ON " . $tablePrefix . "rumours.country_id = " . $tablePrefix . "countries.country_id";
				$query .= " LEFT JOIN " . $tablePrefix . "statuses ON " . $tablePrefix . "rumours.status_id = " . $tablePrefix . "statuses.status_id";
				$query .= " LEFT JOIN " . $tablePrefix . "priorities ON " . $tablePrefix . "rumours.priority_id = " . $tablePrefix . "priorities.priority_id";
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

			$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . ': ' . $dbConnection->error . '<br /><br />' . $query);

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
			$query .= " " . $tablePrefix . "rumour_sightings.country_id as sighting_country_id,";
			$query .= " " . $tablePrefix . "sighting_countries.country as sighting_country,";
			$query .= " " . $tablePrefix . "rumour_sightings.city as sighting_city,";
			$query .= " " . $tablePrefix . "rumour_sightings.latitude as sighting_latitude,";
			$query .= " " . $tablePrefix . "rumour_sightings.longitude as sighting_longitude,";
			$query .= " " . $tablePrefix . "rumour_sightings.created_by as heard_by,";
			$query .= " " . $tablePrefix . "users.*,";
			$query .= " " . $tablePrefix . "rumours.*,";
			$query .= " " . $tablePrefix . "rumours.country_id as country_id_occurred,";
			$query .= " " . $tablePrefix . "rumour_countries.country as country_occurred,";
			$query .= " " . $tablePrefix . "rumours.city as city_occurred,";
			$query .= " " . $tablePrefix . "rumours.latitude as latitude_occurred,";
			$query .= " " . $tablePrefix . "rumours.longitude as longitude_occurred,";
			$query .= " " . $tablePrefix . "rumours.public_id as rumour_public_id,";
			$query .= " " . $tablePrefix . "statuses.status,";
			$query .= " " . $tablePrefix . "priorities.priority,";
			$query .= " " . $tablePrefix . "priorities.severity,";
			$query .= " " . $tablePrefix . "sources.source as source,";
			$query .= " " . $tablePrefix . "rumour_sightings.public_id as public_id";
			$query .= " FROM " . $tablePrefix . "rumour_sightings";
			$query .= " LEFT JOIN " . $tablePrefix . "users ON " . $tablePrefix . "rumour_sightings.created_by = " . $tablePrefix . "users.user_id";
			$query .= " LEFT JOIN " . $tablePrefix . "rumours ON " . $tablePrefix . "rumour_sightings.rumour_id = " . $tablePrefix . "rumours.rumour_id";
			$query .= " LEFT JOIN " . $tablePrefix . "countries AS " . $tablePrefix . "rumour_countries ON " . $tablePrefix . "rumours.country_id = " . $tablePrefix . "rumour_countries.country_id";
			$query .= " LEFT JOIN " . $tablePrefix . "countries AS " . $tablePrefix . "sighting_countries ON " . $tablePrefix . "rumour_sightings.country_id = " . $tablePrefix . "sighting_countries.country_id";
			$query .= " LEFT JOIN " . $tablePrefix . "statuses ON " . $tablePrefix . "rumours.status_id = " . $tablePrefix . "statuses.status_id";
			$query .= " LEFT JOIN " . $tablePrefix . "priorities ON " . $tablePrefix . "rumours.priority_id = " . $tablePrefix . "priorities.priority_id";
			$query .= " LEFT JOIN " . $tablePrefix . "sources ON " . $tablePrefix . "rumour_sightings.source_id = " . $tablePrefix . "sources.source_id";
			$query .= " WHERE 1=1";
			if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
			if ($containing) foreach ($containing as $field => $value) $query .= " AND " . $field . " LIKE '%" . $dbConnection->escape_string($value) . "%'";
			if ($otherCriteria) $query .= " AND (" . $otherCriteria . ")";
			if ($sortBy) $query .= " ORDER BY " . $sortBy;
			if ($limit) $query .= " LIMIT " . $dbConnection->escape_string($limit);
			
			$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . ': ' . $dbConnection->error . '<br /><br />' . $query);

		// create array
			$parser = new parser_TL();
			$items = $parser->mySqliResourceToArray($result);

		// clear memory
			$result->free();

		// return array
			return $items;
		
	}
	
?>
