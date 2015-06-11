<?php

	function retrieveTags($matching = null, $containing = null, $otherCriteria = null, $sortBy = false, $limit = false) {
		
		global $dbConnection;
		global $tablePrefix;
		
		// build query
			if ($matching['rumour_id']) {
				$query = "SELECT " . $tablePrefix . "rumours_x_tags.*,";
				$query .= " " . $tablePrefix . "tags.*";
				$query .= " FROM " . $tablePrefix . "rumours_x_tags";
				$query .= " LEFT JOIN " . $tablePrefix . "tags ON " . $tablePrefix . "rumours_x_tags.tag_id = " . $tablePrefix . "tags.tag_id";
			}
			else {
				$query = "SELECT " . $tablePrefix . "tags.*,";
				$query .= " (SELECT COUNT(rumour_id) FROM " . $tablePrefix . "rumours_x_tags WHERE " . $tablePrefix . "rumours_x_tags.tag_id = " . $tablePrefix . "tags.tag_id) as number_of_rumours";
				$query .= " FROM " . $tablePrefix . "tags";
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
		
?>
