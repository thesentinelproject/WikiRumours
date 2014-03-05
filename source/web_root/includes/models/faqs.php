<?php

	// FAQs

		function retrieveFaqs($matching, $containing, $otherCriteria = false, $sortBy = false, $limit = false) {

			global $dbConnection;
			global $tablePrefix;
			
			if ($matching || $containing || $otherCriteria || $sortBy || $limit) {

				// build query
					$query = "SELECT " . $tablePrefix . "faqs.*";
					$query .= " FROM " . $tablePrefix . "faqs";
					$query .= " LEFT JOIN " . $tablePrefix . "faq_chapters ON " . $tablePrefix . "faqs.chapter_id = " . $tablePrefix . "faq_chapters.chapter_id";
					$query .= " WHERE 1=1";
					if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
					if ($containing) foreach ($containing as $field => $value) $query .= " AND " . $field . " LIKE '%" . $dbConnection->escape_string($value) . "%'";
					if ($otherCriteria) $query .= " AND (" . $otherCriteria . ")";
					if ($sortBy) $query .= " ORDER BY " . $sortBy;
					if ($limit) $query .= " LIMIT " . $dbConnection->escape_string($limit);
					
					$result = $dbConnection->query($query) or die('Unable to execute retrieveUnchapteredFaqs: ' . $dbConnection->error . '<br /><br />' . $query);
		
				// create array
					$parser = new parser_TL();
					$items = $parser->mySqliResourceToArray($result);
		
				// clear memory
					$result->free();
					
				// return array
					return $items;
						
			}
			else {

				// retrieve chapters
				
					// build query
						$query = "SELECT " . $tablePrefix . "faqs.*,";
						$query .= " " . $tablePrefix . "faq_chapters.*";
						$query .= " FROM " . $tablePrefix . "faqs";
						$query .= " LEFT JOIN " . $tablePrefix . "faq_chapters ON " . $tablePrefix . "faqs.chapter_id = " . $tablePrefix . "faq_chapters.chapter_id";
						$query .= " GROUP BY " . $tablePrefix . "faqs.chapter_id";
						$query .= " ORDER BY " . $tablePrefix . "faq_chapters.chapter_position ASC";
						
						$result = $dbConnection->query($query) or die('Unable to execute retrieveChapteredFaqs: ' . $dbConnection->error . '<br /><br />' . $query);
			
					// create array
						$parser = new parser_TL();
						$chapters = $parser->mySqliResourceToArray($result);

					// clear memory
						$result->free();
						
				// populate chapters
						$items = array();
						for ($counter = 0; $counter < count($chapters); $counter++) {
							// copy info on chapters
								$items[$counter]['chapter_id'] = $chapters[$counter]['chapter_id'];
								$items[$counter]['chapter_name'] = $chapters[$counter]['name'];
								$items[$counter]['chapter_position'] = $chapters[$counter]['chapter_position'];
							
							// build FAQ query
								$query = "SELECT *";
								$query .= " FROM " . $tablePrefix . "faqs";
								$query .= " WHERE chapter_id = '" . $chapters[$counter]['chapter_id'] . "'";
								$query .= " ORDER BY faq_position ASC";
								
								$result = $dbConnection->query($query) or die('Unable to execute retrieveChapteredFaqs: ' . $dbConnection->error . '<br /><br />' . $query);
					
							// create array
								$parser = new parser_TL();
								$items[$counter]['FAQs'] = $parser->mySqliResourceToArray($result);
					
							// clear memory
								$result->free();
										
						}

				// return array
					return $items;
				
			}
			
		}
		
	// FAQ chapters
	
		function retrieveFaqChapters($matching = null, $containing = null, $otherCriteria = null, $sortBy = false, $limit = false) {
			
			global $dbConnection;
			global $tablePrefix;
			
			// build query
				$query = "SELECT *,";
				$query .= " (SELECT COUNT(" . $tablePrefix . "faqs.faq_id) FROM " . $tablePrefix . "faqs WHERE " . $tablePrefix . "faqs.chapter_id = " . $tablePrefix . "faq_chapters.chapter_id) AS number_of_faqs";
				$query .= " FROM " . $tablePrefix . "faq_chapters";
				$query .= " WHERE 1=1";
				if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
				if ($containing) foreach ($containing as $field => $value) $query .= " AND " . $field . " LIKE '%" . $dbConnection->escape_string($value) . "%'";
				if ($otherCriteria) $query .= " AND (" . $otherCriteria . ")";
				if ($sortBy) $query .= " ORDER BY " . $sortBy;
				if ($limit) $query .= " LIMIT " . $dbConnection->escape_string($limit);
	
				$result = $dbConnection->query($query) or die('Unable to execute retrieveFaqChapters: ' . $dbConnection->error . '<br /><br />' . $query);
	
			// create array
				$parser = new parser_TL();
				$items = $parser->mySqliResourceToArray($result);
	
			// clear memory
				$result->free();
				
			// return array
				return $items;
			
		}
			
?>
