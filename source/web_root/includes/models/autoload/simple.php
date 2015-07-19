<?php

	function retrieveFromDb($table, $columns = null, $matching = null, $containing = null, $beginsWith = null, $endsWith = null, $otherCriteria = null, $groupBy = null, $sortBy = false, $limit = false) {
		
		global $dbConnection;
		global $tablePrefix;
		
		// validate input
			if (!$table) return false; // no table specified
			if ($columns && !is_array($columns)) $columns = array($columns=>$columns);
			
		// build query
			$query = "SELECT";
			if ($columns) {
				foreach ($columns as $column => $label) $query .= " " . $column . " AS " . $label . ",";
				$query = trim($query, ',');
			}
			else $query .= " *";
			$query .= " FROM " . $tablePrefix . $table;
			$query .= " WHERE 1=1";
			if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
			if ($containing) foreach ($containing as $field => $value) $query .= " AND " . $field . " LIKE '%" . $dbConnection->escape_string($value) . "%'";
			if ($beginsWith) foreach ($beginsWith as $field => $value) $query .= " AND " . $field . " LIKE '" . $dbConnection->escape_string($value) . "%'";
			if ($endsWith) foreach ($endsWith as $field => $value) $query .= " AND " . $field . " LIKE '%" . $dbConnection->escape_string($value) . "'";
			if ($otherCriteria) $query .= " AND (" . $otherCriteria . ")";
			if ($groupBy) $query .= " GROUP BY " . $groupBy;
			if ($sortBy) $query .= " ORDER BY " . $sortBy;
			if ($limit) $query .= " LIMIT " . $dbConnection->escape_string($limit);

			$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . '(' . addSlashes($table) . '): ' . $dbConnection->error . '<br /><br />' . $query);

		// create array
			$parser = new parser_TL();
			$items = $parser->mySqliResourceToArray($result);

		// clear memory
			$result->free();

		// return array
			return $items;
		
	}

	function retrieveSingleFromDb($table, $columns = null, $matching = null, $containing = null, $beginsWith = null, $endsWith = null, $otherCriteria = null, $groupBy = null, $sortBy = false) {
		return retrieveFromDb($table, $columns, $matching, $containing, $beginsWith, $endsWith, $otherCriteria, $groupBy, $sortBy, 1);
	}

	function countInDb($table, $column = null, $matching = null, $containing = null, $beginsWith = null, $endsWith = null, $otherCriteria = null) {
		
		global $dbConnection;
		global $tablePrefix;
		
		// validate input
			if (!$table) return false; // no table specified
		
		// build query
			$query = "SELECT";
			if ($column) $query .= " COUNT(" . $column . ")";
			else $query .= " COUNT(*)";
			$query .= " AS count FROM " . $tablePrefix . $table;
			$query .= " WHERE 1=1";
			if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
			if ($containing) foreach ($containing as $field => $value) $query .= " AND " . $field . " LIKE '%" . $dbConnection->escape_string($value) . "%'";
			if ($beginsWith) foreach ($beginsWith as $field => $value) $query .= " AND " . $field . " LIKE '" . $dbConnection->escape_string($value) . "%'";
			if ($endsWith) foreach ($endsWith as $field => $value) $query .= " AND " . $field . " LIKE '%" . $dbConnection->escape_string($value) . "'";
			if ($otherCriteria) $query .= " AND (" . $otherCriteria . ")";
									
			$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . '(' . addSlashes($table) . '): ' . $dbConnection->error . '<br /><br />' . $query);

		// create array
			$parser = new parser_TL();
			$items = $parser->mySqliResourceToArray($result);

		// clear memory
			$result->free();

		// return array
			return $items;
		
	}
	
	function updateDb($table, $values, $matching = null, $containing = null, $beginsWith = null, $endsWith = null, $otherCriteria = null, $limit = false) {

		global $dbConnection;
		global $tablePrefix;
		
		// validate input
			if (!$table) return false; // no table specified
			if (!$values) return false; // no values to update
		
		// build query
			$query = "UPDATE " . $tablePrefix . $table;
			$query .= " SET";
			foreach ($values as $field => $value) $query .= " " . $field. " = '" . $dbConnection->escape_string($value) . "',";
			$query = trim($query, ',');
			$query .= " WHERE 1=1";
			if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
			if ($containing) foreach ($containing as $field => $value) $query .= " AND " . $field . " LIKE '%" . $dbConnection->escape_string($value) . "%'";
			if ($beginsWith) foreach ($beginsWith as $field => $value) $query .= " AND " . $field . " LIKE '" . $dbConnection->escape_string($value) . "%'";
			if ($endsWith) foreach ($endsWith as $field => $value) $query .= " AND " . $field . " LIKE '%" . $dbConnection->escape_string($value) . "'";
			if (!is_null($otherCriteria)) $query .= " AND (" . $otherCriteria . ")";
			if ($limit) $query .= " LIMIT " . $dbConnection->escape_string($limit);

			$dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . '(' . addSlashes($table) . '): ' . $dbConnection->error . '<br /><br />' . $query);
			
			return $dbConnection->affected_rows;
		
	}

	function updateDbSingle($table, $values, $matching = null, $containing = null, $beginsWith = null, $endsWith = null, $otherCriteria = null) {
		return updateDb($table, $values, $matching, $containing, $beginsWith, $endsWith, $otherCriteria, 1);
	}

	function insertIntoDb($table, $values) {
		
		global $dbConnection;
		global $tablePrefix;
		
		// validate input
			if (!$table) return false; // no table specified
			if (!$values) return false; // no values to update
		
		// build query
			$query = "INSERT INTO " . $tablePrefix . $table . " (";
			foreach ($values as $field => $value) $query .= $field . ",";
			$query = trim($query, ',');
			$query .= ") VALUES (";
			foreach ($values as $field => $value) $query .= "'" . $dbConnection->escape_string($value) . "',";
			$query = trim($query, ',');
			$query .= ")";
			
			$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . '(' . addSlashes($table) . '): ' . $dbConnection->error . '<br /><br />' . $query);
			
			return $dbConnection->insert_id;
		
	}
		
	function updateOrInsertIntoDb($table, $values, $matching = null, $containing = null, $beginsWith = null, $endsWith = null, $otherCriteria = null, $limit = false) {

		global $dbConnection;
		global $tablePrefix;

		$exists = retrieveSingleFromDb($table, null, $matching, $containing, $beginsWith, $endsWith, $otherCriteria);

		if (count($exists)) updateDb($table, $values, $matching, $containing, $beginsWith, $endsWith, $otherCriteria, $limit);
		else insertIntoDb($table, $values);

	}
		
	function deleteFromDb($table, $matching = null, $containing = null, $beginsWith = null, $endsWith = null, $otherCriteria = null, $limit = false) {
		
		global $dbConnection;
		global $tablePrefix;
		
		// validate input
			if (!$table) return false; // no table specified
			if (!$matching && !$containing && !$beginsWith && !$endsWith && !$otherCriteria) return false; // no criteria
			
		// build query
			$query = "DELETE FROM " . $tablePrefix . $table;
			$query .= " WHERE 1=1";
			if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
			if ($containing) foreach ($containing as $field => $value) $query .= " AND " . $field . " LIKE '%" . $dbConnection->escape_string($value) . "%'";
			if ($beginsWith) foreach ($beginsWith as $field => $value) $query .= " AND " . $field . " LIKE '" . $dbConnection->escape_string($value) . "%'";
			if ($endsWith) foreach ($endsWith as $field => $value) $query .= " AND " . $field . " LIKE '%" . $dbConnection->escape_string($value) . "'";
			if (!is_null($otherCriteria)) $query .= " AND (" . $otherCriteria . ")";
			if ($limit) $query .= " LIMIT " . $dbConnection->escape_string($limit);
			
			$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . '(' . addSlashes($table) . '): ' . $dbConnection->error . '<br /><br />' . $query);

		// return status
			return $dbConnection->affected_rows;
			
	}

	function deleteFromDbSingle($table, $matching = null, $containing = null, $beginsWith = null, $endsWith = null, $otherCriteria = null, $limit = false) {
		return deleteFromDb($table, $matching, $containing, $beginsWith, $endsWith, $otherCriteria, 1);
	}
	
	function cloneInDb($sourceTable, $destinationTable, $columnsToClone, $matching) {
		
		global $dbConnection;
		global $tablePrefix;
		
		// validate input
			if (!$sourceTable) return false; // no source table specified
			if (!$destinationTable) return false; // no destination table specified
			if (!$columnsToClone) return false; // no columns specified
			if (!$matching) return false; // no criteria specified
			
		// retrieve source data
			$query = "SELECT * FROM " . $tablePrefix . $sourceTable;
			$query .= " WHERE 1=1";
			foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
			
		// create array
			$parser = new parser_TL();
			$items = $parser->mySqliResourceToArray($result);

		// clear memory
			$result->free();
			
		// save destination data
			for ($itemCounter = 0; $itemCounter < count($items); $itemCounter++) {
				$query = "INSERT INTO " . $tablePrefix . $destinationTable . " (";
				$query .= "datetime, ";
				foreach ($columnsToClone as $counter => $column) $query .= $column . ",";
				$query = trim($query, ',');
				$query .= ") VALUES (";
				$query .= "'" . date('Y-m-d H:i:s') . "', ";
				foreach ($columnsToClone as $counter => $column) $query .= "'" . $dbConnection->escape_string($items[$itemCounter][$column]) . "',";
				$query = trim($query, ',');
				$query .= ")";
				
				$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . '(' . addSlashes($table) . '): ' . $dbConnection->error . '<br /><br />' . $query);
			}
			
		return true;
			
	}

	function directlyQueryDb($query) {
		
		/*
			Use with EXTREME caution, since any queries made through this interface will NOT be automatically
			protected from SQL injection. Escape all your values before calling this function.
		*/

		global $dbConnection;
		
		// validate input
			if (!$query) return false; // no query specified
			
		// build query
			$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . ': ' . $dbConnection->error . '<br /><br />' . $query);

			if (!is_object($result)) return $result;
			else {

				// create array
					$parser = new parser_TL();
					$items = $parser->mySqliResourceToArray($result);

				// clear memory
					$result->free();

				// return array
					return $items;
					
			}
		
	}
	
?>
