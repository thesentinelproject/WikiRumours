<?php

	function retrieveTables_TL() {

		global $dbConnection;
		
		// build query
			$result = $dbConnection->query("SHOW FULL TABLES") or die($dbConnection->error . '<br /><br />' . $query);

		// create array
			$parser = new parser_TL();
			$items = $parser->mySqliResourceToArray($result);

		// return array
			return $items;
		
	}
	
?>
