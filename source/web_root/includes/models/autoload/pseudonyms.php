<?php

	function retrievePseudonyms($matching = null, $containing = null, $otherCriteria = null, $sortBy = false, $limit = false) {
		
		global $dbConnection;
		global $tablePrefix;
		
		// build query
			$query = "SELECT " . $tablePrefix . "pseudonyms.*,";
			$query .= " " . $tablePrefix . "countries.country AS default_country,";
			$query .= " " . $tablePrefix . "languages.language AS default_language";
			$query .= " FROM " . $tablePrefix . "pseudonyms";
			$query .= " LEFT JOIN " . $tablePrefix . "countries ON " . $tablePrefix . "countries.country_id = " . $tablePrefix . "pseudonyms.country_id";
			$query .= " LEFT JOIN " . $tablePrefix . "languages ON " . $tablePrefix . "languages.language_id = " . $tablePrefix . "pseudonyms.language_id";
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
