<?php

	class database_manager_TL {

		public function retrieve($table, $columns = null, $matching = null, $containing = null, $beginsWith = null, $endsWith = null, $otherCriteria = null, $groupBy = null, $sortBy = false, $limit = false) {
			
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

		public function retrieveSingle($table, $columns = null, $matching = null, $containing = null, $beginsWith = null, $endsWith = null, $otherCriteria = null, $groupBy = null, $sortBy = false) {
			return $this->retrieve($table, $columns, $matching, $containing, $beginsWith, $endsWith, $otherCriteria, $groupBy, $sortBy, 1);
		}

		public function howMany($table, $column = null, $matching = null, $containing = null, $beginsWith = null, $endsWith = null, $otherCriteria = null) {
			
			global $dbConnection;
			global $tablePrefix;
			
			// validate input
				if (!$table) return false; // no table specified
			
			// build query
				$query = "SELECT";
				$query .= " COUNT(" . ($column ? $column : "*") . ") AS count";
				$query .= " FROM " . $tablePrefix . $table;
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
				return $items[0]['count'];
			
		}
		
		public function update($table, $values, $matching = null, $containing = null, $beginsWith = null, $endsWith = null, $otherCriteria = null, $limit = false) {

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

		public function updateSingle($table, $values, $matching = null, $containing = null, $beginsWith = null, $endsWith = null, $otherCriteria = null) {
			return $this->update($table, $values, $matching, $containing, $beginsWith, $endsWith, $otherCriteria, 1);
		}

		public function insert($table, $values) {
			
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
			
		public function updateOrInsert($table, $values, $matching = null, $containing = null, $beginsWith = null, $endsWith = null, $otherCriteria = null, $limit = false) {

			global $dbConnection;
			global $tablePrefix;

			$exists = retrieveSingleFromDb($table, null, $matching, $containing, $beginsWith, $endsWith, $otherCriteria);

			if (count($exists)) return $this->update($table, $values, $matching, $containing, $beginsWith, $endsWith, $otherCriteria, $limit);
			else return $this->insert($table, $values);

		}
			
		public function delete($table, $matching = null, $containing = null, $beginsWith = null, $endsWith = null, $otherCriteria = null, $limit = false) {
			
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

		public function deleteSingle($table, $matching = null, $containing = null, $beginsWith = null, $endsWith = null, $otherCriteria = null, $limit = false) {
			return $this->delete($table, $matching, $containing, $beginsWith, $endsWith, $otherCriteria, 1);
		}
		
		public function cloneData($sourceTable, $destinationTable, $columnsToClone, $matching) {
			
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

		public function query($query) {
			
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

		public function keys($table, $matching = null) {

			global $dbConnection;
			global $tablePrefix;

			// validate input
				if (!$table) return false; // no table specified
				if (!$matching && !$containing && !$beginsWith && !$endsWith && !$otherCriteria) return false; // no criteria
				
			// build query
				$query = "SHOW KEYS FROM " . $tablePrefix . $table;
				$query .= " WHERE 1=1";
				if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";

				$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . '(' . addSlashes($table) . '): ' . $dbConnection->error . '<br /><br />' . $query);

			// create array
				$parser = new parser_TL();
				$items = $parser->mySqliResourceToArray($result);

			// clear memory
				$result->free();

			// return array
				return $items;

		}

		public function tables($host = null, $dbName = null, $user = null, $password = null) {

			global $dbConnection;
			global $tl;

			if (!$host || !$dbName || !$user || !$password) {
				$connection = $dbConnection;
				$dbName = $tl->db['Name'];
			}
			else {
				$connection = @new mysqli($host, $user, $password, $dbName);
				if ($connection->connect_errno > 0) return false;
			}
			
			// build query
				$query = "SELECT table_name,";
				$query .= " ROUND(((data_length + index_length) / 1024)) AS size_kb";
				$query .= " FROM information_schema.TABLES";
				$query .= " WHERE table_schema = '" . $dbName . "'";
				$result = $connection->query($query) or die('Unable to execute ' . __FUNCTION__ . ': ' . $connection->error . '<br /><br />' . $query);
				if ($connection != $dbConnection) $connection->close();

			// create array
				$parser = new parser_TL();
				$items = $parser->mySqliResourceToArray($result);

			// return array
				return $items;

		}
		
		public function size($host = null, $dbName = null, $user = null, $password = null) {

			global $dbConnection;
			global $tl;
			
			if (!$host || !$dbName || !$user || !$password) {
				$connection = $dbConnection;
				$dbName = $tl->db['Name'];
			}
			else {
				$connection = @new mysqli(trim($host), trim($user), trim($password), trim($dbName));
				if ($connection->connect_errno > 0) return false;
			}

			// build query
				$query = "SELECT table_schema AS name,";
				$query .= " ROUND(SUM(DATA_LENGTH + INDEX_LENGTH) / 1024) as size_kb";
				$query .= " FROM information_schema.tables";
				$query .= " WHERE table_schema = '" . $dbName . "'";
				$query .= " GROUP BY table_schema";
				$result = $connection->query($query) or die('Unable to execute ' . __FUNCTION__ . ': ' . $connection->error . '<br /><br />' . $query);
				if ($connection != $dbConnection) $connection->close();

			// create array
				$parser = new parser_TL();
				$items = $parser->mySqliResourceToArray($result);

			// return array
				return $items;
			
		}

		public function emptyData($table) {

			global $dbConnection;
			global $tablePrefix;
			
			// validate input
				if (!$table) return false;
				
			// build query
				$query = "TRUNCATE " . $tablePrefix . $table;
				
				$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . '(' . addSlashes($table) . '): ' . $dbConnection->error . '<br /><br />' . $query);

			// test
				if ($this->howMany($table)) return false;
				else return true;
				
		}
	
	}

/*
	Database

	::	DESCRIPTION
	
		Basic sanitized database queries

	::	DEPENDENT ON

	::	VERSION HISTORY

	::	LICENSE
	
		Copyright (C) Timothy Quinn / Tidal Lock / Consolidated Biro
		
		Permission is hereby granted, free of charge, to any person
		obtaining a copy of this software and associated documentation
		files (the "Software"), to deal in the Software without
		restriction, including without limitation the rights to use,
		copy, modify, merge, publish, distribute, sublicense, and/or
		sell copies of the Software, and to permit persons to whom the
		Software is furnished to do so, subject to the following
		conditions:
		
		The above copyright notice and this permission notice shall be
		included in all copies or substantial portions of the Software.
		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
		EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
		OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
		NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
		HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
		WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
		FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
		OTHER DEALINGS IN THE SOFTWARE.
*/
	

?>
