<?php

	// FAQs

		function retrieveFaqs($matching, $containing, $otherCriteria = false, $sortBy = false, $limit = false) {

			global $dbConnection;
			global $tablePrefix;
			
			if ($matching || $containing || $otherCriteria || $sortBy || $limit) {

				// build query
					$query = "SELECT " . $tablePrefix . "faqs.*,";
					$query .= " " . $tablePrefix . "faqs.position AS faq_position,";
					$query .= " " . $tablePrefix . "faq_sections.position AS section_position";
					$query .= " FROM " . $tablePrefix . "faqs";
					$query .= " LEFT JOIN " . $tablePrefix . "faq_sections ON " . $tablePrefix . "faqs.section_id = " . $tablePrefix . "faq_sections.section_id";
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
			else {

				// retrieve chapters
				
					// build query
						$query = "SELECT " . $tablePrefix . "faqs.*,";
						$query .= " " . $tablePrefix . "faq_sections.*,";
						$query .= " " . $tablePrefix . "faqs.position AS faq_position,";
						$query .= " " . $tablePrefix . "faq_sections.position AS section_position";
						$query .= " FROM " . $tablePrefix . "faqs";
						$query .= " LEFT JOIN " . $tablePrefix . "faq_sections ON " . $tablePrefix . "faqs.section_id = " . $tablePrefix . "faq_sections.section_id";
						$query .= " GROUP BY " . $tablePrefix . "faqs.section_id";
						$query .= " ORDER BY " . $tablePrefix . "faq_sections.position ASC";
						
						$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . ': ' . $dbConnection->error . '<br /><br />' . $query);
			
					// create array
						$parser = new parser_TL();
						$sections = $parser->mySqliResourceToArray($result);

					// clear memory
						$result->free();
						
				// populate sections
						$items = array();
						for ($counter = 0; $counter < count($sections); $counter++) {
							// copy info on sections
								$items[$counter]['section_id'] = $sections[$counter]['section_id'];
								$items[$counter]['section_name'] = $sections[$counter]['name'];
								$items[$counter]['section_position'] = $sections[$counter]['section_position'];
							
							// build FAQ query
								$query = "SELECT *";
								$query .= " FROM " . $tablePrefix . "faqs";
								$query .= " WHERE section_id = '" . $sections[$counter]['section_id'] . "'";
								$query .= " ORDER BY position ASC";
								
								$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . ': ' . $dbConnection->error . '<br /><br />' . $query);
					
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
		
	// FAQ sections
	
		function retrieveFaqsections($matching = null, $containing = null, $otherCriteria = null, $sortBy = false, $limit = false) {
			
			global $dbConnection;
			global $tablePrefix;
			
			// build query
				$query = "SELECT *,";
				$query .= " (SELECT COUNT(" . $tablePrefix . "faqs.faq_id) FROM " . $tablePrefix . "faqs WHERE " . $tablePrefix . "faqs.section_id = " . $tablePrefix . "faq_sections.section_id) AS number_of_faqs";
				$query .= " FROM " . $tablePrefix . "faq_sections";
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
