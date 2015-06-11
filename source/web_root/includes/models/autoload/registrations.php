<?php

	function retrieveRegistrants($matching, $containing, $otherCriteria = null, $sortBy = 'registered_on DESC', $limit = false) {
		
		global $dbConnection;
		global $tablePrefix;
		
		// build query
			$query = "SELECT " . $tablePrefix . "registrations.*,";
			$query .= " TRIM(CONCAT(" . $tablePrefix . "registrations.first_name, ' ', " . $tablePrefix . "registrations.last_name)) as full_name,";
			$query .= " " . $tablePrefix . "countries.country as country,";
			$query .= " " . $tablePrefix . "regions.region as region";
			$query .= " FROM " . $tablePrefix . "registrations";
			$query .= " LEFT JOIN " . $tablePrefix . "countries ON " . $tablePrefix . "countries.country_id = " . $tablePrefix . "registrations.country_id";
			$query .= " LEFT JOIN " . $tablePrefix . "regions ON " . $tablePrefix . "regions.region_id = " . $tablePrefix . "registrations.region_id";
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
