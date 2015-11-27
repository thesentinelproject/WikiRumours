<?php

	function retrieveLogs($matching = null, $containing = null, $otherCriteria = null, $sortBy = false, $limit = false) {
		
		global $dbConnection;
		global $tablePrefix;
		
		// build query
			if (@$matching['relationship_name'] || @$matching['relationship_value']) {
				$query = "SELECT " . $tablePrefix . "log_relationships.*,";
				$query .= " " . $tablePrefix . "logs.*";
				$query .= " FROM " . $tablePrefix . "log_relationships";
				$query .= " LEFT JOIN " . $tablePrefix . "logs ON " . $tablePrefix . "log_relationships.log_id = " . $tablePrefix . "logs.log_id";
			}
			else {
				$query = "SELECT " . $tablePrefix . "logs.*";
				$query .= " FROM " . $tablePrefix . "logs";
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
