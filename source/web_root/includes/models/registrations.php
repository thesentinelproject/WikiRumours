<?php

	function retrieveRegistrants($matching, $containing, $otherCriteria = null, $sortBy = 'last_name DESC, first_name DESC', $limit = false) {
		
		global $dbConnection;
		global $tablePrefix;
		
		// build query
			$query = "SELECT *,";
			$query .= " TRIM(CONCAT(first_name, ' ', last_name)) as full_name";
			$query .= " FROM " . $tablePrefix . "registrations";
			$query .= " WHERE 1=1";
			if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
			if ($containing) foreach ($containing as $field => $value) $query .= " AND " . $field . " LIKE '%" . $dbConnection->escape_string($value) . "%'";
			if ($otherCriteria) $query .= " AND (" . $otherCriteria . ")";
			if ($sortBy) $query .= " ORDER BY " . $sortBy;
			if ($limit) $query .= " LIMIT " . $dbConnection->escape_string($limit);
			
			$result = $dbConnection->query($query) or die('Unable to execute retrieveRegistrants: ' . $dbConnection->error . '<br /><br />' . $query);

		// create array
			$parser = new parser_TL();
			$items = $parser->mySqliResourceToArray($result);

		// clear memory
			$result->free();

		// return array
			return $items;
		
	}
		
?>
