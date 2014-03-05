<?php

	function retrieveWatchlist($matching, $containing = null, $otherCriteria = null, $sortBy = false, $limit = false) {
		
		global $dbConnection;
		global $tablePrefix;
		
		// build query
			$query = "SELECT " . $tablePrefix . "watchlist.*,";
			$query .= " " . $tablePrefix . "rumours.*,";
			$query .= " " . $tablePrefix . "users.*,";
			$query .= " TRIM(CONCAT(" . $tablePrefix . "users.first_name, ' ', " . $tablePrefix . "users.last_name)) as full_name";
			$query .= " FROM " . $tablePrefix . "watchlist";
			$query .= " LEFT JOIN " . $tablePrefix . "rumours ON " . $tablePrefix . "watchlist.rumour_id = " . $tablePrefix . "rumours.rumour_id";
			$query .= " LEFT JOIN " . $tablePrefix . "users ON " . $tablePrefix . "watchlist.created_by = " . $tablePrefix . "users.user_id";
			$query .= " WHERE 1=1";
			if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
			if ($containing) foreach ($containing as $field => $value) $query .= " AND " . $field . " LIKE '%" . $dbConnection->escape_string($value) . "%'";
			if ($otherCriteria) $query .= " AND (" . $otherCriteria . ")";
			if ($sortBy) $query .= " ORDER BY " . $sortBy;
			if ($limit) $query .= " LIMIT " . $dbConnection->escape_string($limit);
			
			$result = $dbConnection->query($query) or die('Unable to execute retrieveWatchlist: ' . $dbConnection->error . '<br /><br />' . $query);

		// create array
			$parser = new parser_TL();
			$items = $parser->mySqliResourceToArray($result);

		// clear memory
			$result->free();

		// return array
			return $items;
		
	}
		
?>
