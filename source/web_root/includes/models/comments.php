<?php

	function retrieveComments($matching = null, $containing = null, $otherCriteria = null, $sortBy = false, $limit = false) {
		
		global $dbConnection;
		global $tablePrefix;
		
		// build query
			$query = "SELECT " . $tablePrefix . "comments.*,";
			$query .= " " . $tablePrefix . "comments.created_on AS comment_created_on,";
			$query .= " " . $tablePrefix . "comments.enabled AS comment_enabled,";
			$query .= " " . $tablePrefix . "users.*,";
			$query .= " " . $tablePrefix . "rumours.*,";
			$query .= " (SELECT COUNT(flagged_by) FROM " . $tablePrefix . "comment_flags WHERE " . $tablePrefix . "comment_flags.comment_id = " . $tablePrefix . "comments.comment_id) as number_of_flags";
			$query .= " FROM " . $tablePrefix . "comments";
			$query .= " LEFT JOIN " . $tablePrefix . "users ON " . $tablePrefix . "comments.created_by = " . $tablePrefix . "users.user_id";
			$query .= " LEFT JOIN " . $tablePrefix . "rumours ON " . $tablePrefix . "comments.rumour_id = " . $tablePrefix . "rumours.rumour_id";
			$query .= " WHERE 1=1";
			if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
			if ($containing) foreach ($containing as $field => $value) $query .= " AND " . $field . " LIKE '%" . $dbConnection->escape_string($value) . "%'";
			if ($otherCriteria) $query .= " AND (" . $otherCriteria . ")";
			if ($sortBy) $query .= " ORDER BY " . $sortBy;
			if ($limit) $query .= " LIMIT " . $dbConnection->escape_string($limit);
			
			$result = $dbConnection->query($query) or die('Unable to execute retrieveComments: ' . $dbConnection->error . '<br /><br />' . $query);

		// create array
			$parser = new parser_TL();
			$items = $parser->mySqliResourceToArray($result);

		// clear memory
			$result->free();

		// return array
			return $items;
		
	}

	function retrieveFlaggedComments($matching = null, $containing = null, $otherCriteria = null, $sortBy = false, $limit = false) {
		
		global $dbConnection;
		global $tablePrefix;
		
		// build query
			$query = "SELECT " . $tablePrefix . "comment_flags.*,";
			$query .= " " . $tablePrefix . "comments.*,";
			$query .= " " . $tablePrefix . "comments.created_on AS comment_created_on,";
			$query .= " " . $tablePrefix . "comments.created_by AS comment_created_by,";
			$query .= " TRIM(CONCAT(" . $tablePrefix . "users.first_name, ' ', " . $tablePrefix . "users.last_name)) as comment_created_by_full_name,";
			$query .= " " . $tablePrefix . "users.*,";
			$query .= " " . $tablePrefix . "rumours.*,";
			$query .= " COUNT(" . $tablePrefix . "comment_flags.flagged_by) as number_of_flags";
			$query .= " FROM " . $tablePrefix . "comment_flags";
			$query .= " LEFT JOIN " . $tablePrefix . "comments ON " . $tablePrefix . "comment_flags.comment_id = " . $tablePrefix . "comments.comment_id";
			$query .= " LEFT JOIN " . $tablePrefix . "users ON " . $tablePrefix . "comments.created_by = " . $tablePrefix . "users.user_id";
			$query .= " LEFT JOIN " . $tablePrefix . "rumours ON " . $tablePrefix . "comments.rumour_id = " . $tablePrefix . "rumours.rumour_id";
			$query .= " WHERE 1=1";
			$query .= " AND " . $tablePrefix . "comments.enabled = 1";
			if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
			if ($containing) foreach ($containing as $field => $value) $query .= " AND " . $field . " LIKE '%" . $dbConnection->escape_string($value) . "%'";
			if ($otherCriteria) $query .= " AND (" . $otherCriteria . ")";
			$query .= " GROUP BY " . $tablePrefix . "comment_flags.comment_id";
			if ($sortBy) $query .= " ORDER BY " . $sortBy;
			if ($limit) $query .= " LIMIT " . $dbConnection->escape_string($limit);
			
			$result = $dbConnection->query($query) or die('Unable to execute retrieveFlaggedComments: ' . $dbConnection->error . '<br /><br />' . $query);

		// create array
			$parser = new parser_TL();
			$items = $parser->mySqliResourceToArray($result);

		// clear memory
			$result->free();

		// return array
			return $items;
		
	}
	
?>
