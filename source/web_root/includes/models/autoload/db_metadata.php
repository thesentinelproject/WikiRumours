<?php

	function retrieveTables_TL($host = null, $dbName = null, $user = null, $password = null) {

		global $dbConnection;
		global $db_TL;

		if (!$host || !$dbName || !$user || !$password) {
			$connection = $dbConnection;
			$dbName = $db_TL['Name'];
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
	
	function retrieveDbSize_TL($host = null, $dbName = null, $user = null, $password = null) {

		global $dbConnection;
		global $db_TL;
		
		if (!$host || !$dbName || !$user || !$password) {
			$connection = $dbConnection;
			$dbName = $db_TL['Name'];
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
	
?>
