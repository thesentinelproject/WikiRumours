<?php

	// initialize
		$numberOfResultsPerPage = 1000;
		$errors = array();
		$warnings = array();

	// retrieve input parameters
		$apiKey = $parameter1;
		$model = $parameter2;
		$output = $parameter3;
		if ($output != 'json') $output = 'xml';

		$parseFilters = explode('|', urldecode($parameter4));

		$filters = array();
		foreach ($parseFilters as $value) {
			$splitFilters = explode('=', $value);
			if ($splitFilters[1]) $filters[$splitFilters[0]] = $splitFilters[1];
		}
		
		$allowableFilters = array('public_id', 'keywords', 'country_id', 'status_id', 'priority_id', 'tag_id', 'page'); // re-add status
		foreach ($filters as $key=>$value) {
			if (!in_array($key, $allowableFilters)) unset($filters[$key]);
		}

		$page = floatval(@$filters['page']);
		unset($filters['page']);
		
		$keywords = @$filters['keywords'];
		unset($filters['keywords']);
		
		if (@$filters['country_id']) {
			$filters[$tablePrefix . 'rumours.country_id'] = $filters['country_id']; // remove ambiguity in join
			unset($filters['country_id']);
		}

		if (@$filters['status_id']) {
			$filters[$tablePrefix . 'rumours.status_id'] = $filters['status_id'];
			unset($filters['status_id']);
		}

		if (@$filters['priority_id']) {
			$filters[$tablePrefix . 'rumours.priority_id'] = $filters['priority_id'];
			unset($filters['priority_id']);
		}

		if ($keywords) {
			$otherCriteria = "1=2";
			$keywordsExplode = explode(' ', $keywords);
			foreach ($keywordsExplode as $keyword) {
				if (trim($keyword)) $otherCriteria .= " OR LOWER(description) LIKE '%" . addSlashes(trim(strtolower($keyword))) . "%'";
			}
			unset ($filters['keywords']);
		}
		
	// authenticate key
		if (!$apiKey) $errors[count($errors)] = 2; // missing API key
		else {
			$user = retrieveUserKeys(array('name'=>'API', 'hash'=>$apiKey), null, null, null, 1);
			if (count($user) < 1) $errors[count($errors)] = 3; // invalid API key
		}

	// any valid filters? mandatory for non-administrators
		if (!$user[0]['is_administrator']) {
			if (count($filters) < 1) $errors[count($errors)] = 1; // no valid filters found
		}
		
	// check usage cap
		if (!count($errors) && !$user[0]['unlimited_api_queries']) {
			if ($user[0]['internal_api_calls_today'] >= $systemPreferences['Maximum API calls']) $errors[count($errors)] = 4; // maximum query limit achieved
			elseif ($user[0]['internal_api_calls_today'] >= ($systemPreferences['Maximum API calls'] - intval($systemPreferences['Maximum API calls'] / 20))) $warnings[count($warnings)] = 1; // approaching maximum queries
		}
		
	// increment query count
		if (!count($errors)) {
			$user[0]['internal_api_calls_today']++;
			insertIntoDb('api_calls_internal', array('api_key'=>$apiKey, 'queried_on'=>date('Y-m-d H:i:s')));
		}
		
	// retrieve data
		if (!count($errors)) {

			if ($model == 'rumours') {
				if (@$filters['tag_id']) {
					$result = retrieveRumours(@$filters, null, @$otherCriteria);
					$numberOfResults = count($result);
				}
				else {
					$result = countInDb('rumours', 'rumour_id', @$filters, null, null, null, @$otherCriteria);
					$numberOfResults = floatval(@$result[0]['count']);
				}
				if ($numberOfResults < 1) $warnings[count($warnings)] = 2; // No vocabulary was retrieved based on your input parameters.

				$numberOfPages = max(1, ceil($numberOfResults / $numberOfResultsPerPage));
				if ($page < 1) $page = 1;
				elseif ($page > $numberOfPages) $page = $numberOfPages;
				
				$data = retrieveRumours(@$filters, null, @$otherCriteria, $sort, floatval(($page * $numberOfResultsPerPage) - $numberOfResultsPerPage) . ',' . $numberOfResultsPerPage);
			}
			else {
				$errors[count($errors)] = 5; // Unable to determine a valid query type.
			}
			
		}
		
	// display data
		$xmlOutput = '';
		
		$xmlOutput .= "<" . "?" . "xml version='1.0' encoding='ISO-8859-1'" . "?" . ">\n";
		$xmlOutput .= "<wikirumours>\n";
		$xmlOutput .= "  <version>1.0</version>\n";
		$xmlOutput .= "  <status><![CDATA[" . $user[0]['full_name'] . " successfully connected with the WikiRumours API on " . date('F j, Y, \a\t g:i A') . ". Filters: " . $parameter4 . "]]></status>\n";
		$xmlOutput .= "  <page>" . $page . "</page>\n";
		$xmlOutput .= "  <number_of_results>" . $numberOfResults . "</number_of_results>\n";
		$xmlOutput .= "  <number_of_results_on_this_page>" . count($data) . "</number_of_results_on_this_page>\n";
		$xmlOutput .= "  <warnings>\n";
		for ($counter = 0; $counter < count($warnings); $counter++) {
			$xmlOutput .= "    <warning_code>" . $warnings[$counter] . "</warning_code>\n";
			$xmlOutput .= "    <human_readable_warning>" . $apiWarningCodes[$warnings[$counter]] . "</human_readable_warning>\n";
		}
		$xmlOutput .= "  </warnings>\n";
		$xmlOutput .= "  <errors>\n";
		for ($counter = 0; $counter < count($errors); $counter++) {
			$xmlOutput .= "    <error_code>" . $errors[$counter] . "</error_code>\n";
			$xmlOutput .= "    <human_readable_error>" . $apiErrorCodes[$errors[$counter]] . "</human_readable_error>\n";
		}
		$xmlOutput .= "  </errors>\n";
		if (!$error) {
			$xmlOutput .= "  <number_of_queries_today>" . $user[0]['internal_api_calls_today'] . "</number_of_queries_today>\n";
			$xmlOutput .= "  <data>\n";
			for ($counter = 0; $counter < count($data); $counter++) {
				$xmlOutput .= "    <datapoint>\n";
				$xmlOutput .= "      <rumour_id><![CDATA[" . $data[$counter]['public_id'] . "]]></rumour_id>\n";
				$xmlOutput .= "      <rumour><![CDATA[" . $data[$counter]['description'] . "]]></rumour>\n";
				$xmlOutput .= "      <country_abbreviation><![CDATA[" . $data[$counter]['country'] . "]]></country_abbreviation>\n";
				$xmlOutput .= "      <country><![CDATA[" . @$countries[$data[$counter]['country']] . "]]></country>\n";
				$xmlOutput .= "      <region><![CDATA[" . $data[$counter]['region'] . "]]></region>\n";
				$xmlOutput .= "      <occurred_on><![CDATA[" . $data[$counter]['occurred_on'] . "]]></occurred_on>\n";
				$xmlOutput .= "      <status_id><![CDATA[" . $data[$counter]['status_id'] . "]]></status_id>\n";
				$xmlOutput .= "      <status><![CDATA[" . $data[$counter]['status'] . "]]></status>\n";
				$xmlOutput .= "      <priority_id><![CDATA[" . $data[$counter]['priority_id'] . "]]></priority_id>\n";
				$xmlOutput .= "      <priority><![CDATA[" . $data[$counter]['priority'] . "]]></priority>\n";
				$xmlOutput .= "      <findings><![CDATA[" . $data[$counter]['findings'] . "]]></findings>\n";
				$xmlOutput .= "      <number_of_sightings>" . $data[$counter]['number_of_sightings'] . "</number_of_sightings>\n";
				$xmlOutput .= "    </datapoint>\n";
			}
			$xmlOutput .= "  </data>\n";
		}
		$xmlOutput .= "</wikirumours>\n";
		
		if ($output == 'xml') {
			echo $xmlOutput;
		}
		elseif ($output == 'json') {
			$simpleXml = simplexml_load_string($xmlOutput, null, LIBXML_NOCDATA);
			$jsonOutput = json_encode($simpleXml);
	
			echo $jsonOutput;
		}

	// close DB connection
		$dbConnection->close();
		
?>
