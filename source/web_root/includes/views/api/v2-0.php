<?php

	// initialize
		$numberOfResultsPerPage = 1000;
		$errors = array();
		$warnings = array();

		$localization_manager->populateCountries();

	// retrieve input parameters
		$apiKey = $tl->page['parameter1'];
		$model = $tl->page['parameter2'];
		$output = $tl->page['parameter3'];
		if ($output != 'json' && $output != 'csv') $output = 'xml';

		$parseFilters = explode('|', urldecode($tl->page['parameter4']));

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
			if ($model == 'rumours') $filters[$tablePrefix . 'rumours.country_id'] = $filters['country_id']; // remove ambiguity in join
			elseif ($model == 'sightings') $filters[$tablePrefix . 'rumour_sightings.country_id'] = $filters['country_id']; // remove ambiguity in join
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

		if (@$filters['tag_id'] && $model == 'sightings') {
			$warnings[count($warnings)] = 5; // Can't use tag_id with sightings
			unset($filters['tag_id']);
		}

		if ($keywords) {
			$otherCriteria = "1=2";
			$keywordsExplode = explode(' ', $keywords);
			foreach ($keywordsExplode as $keyword) {
				if (trim($keyword)) $otherCriteria .= " OR LOWER(" . $tablePrefix . "rumours.description) LIKE '%" . addSlashes(trim(strtolower($keyword))) . "%'";
			}
			unset ($filters['keywords']);
		}
		
	// authenticate key
		if (!$apiKey) $errors[count($errors)] = 2; // missing API key
		else {
			$user = retrieveUserKeys(array('user_key'=>'API', 'hash'=>$apiKey), null, null, null, 1);
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
			elseif ($model == 'sightings') {
				$result = retrieveSightings(@$filters, null, @$otherCriteria);
				$numberOfResults = count($result);
				if ($numberOfResults < 1) $warnings[count($warnings)] = 3; // No sightings were retrieved based on your input parameters.

				$numberOfPages = max(1, ceil($numberOfResults / $numberOfResultsPerPage));
				if ($page < 1) $page = 1;
				elseif ($page > $numberOfPages) $page = $numberOfPages;
				
				$data = retrieveSightings(@$filters, null, @$otherCriteria, $sort, floatval(($page * $numberOfResultsPerPage) - $numberOfResultsPerPage) . ',' . $numberOfResultsPerPage);
			}
			else {
				$errors[count($errors)] = 5; // Unable to determine a valid query type.
			}
			
		}
		
	// display data
		if ($output == 'csv') {
			$csvOutput = fopen('php://output', 'w');
			header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename=wikirumours.csv");
			header("Pragma: no-cache");
			header("Expires: 0");
			if ($model == 'rumours') fputcsv($csvOutput, array('RumourID', 'Rumour', 'Country_Abbreviation', 'Country', 'Region', 'Latitude', 'Longitude', 'Occurred_on', 'Status_ID', 'Status', 'Priority_ID', 'Priority', 'Findings', 'Number_of_Sightings'));
			elseif ($model == 'sightings') fputcsv($csvOutput, array('SightingID', 'Heard_on', 'Country_Abbreviation', 'Country', 'Region', 'Latitude', 'Longitude', 'Rumour', 'Occurred_on', 'Findings'));
		}

		$xmlOutput = null;
		$xmlOutput .= "<" . "?" . "xml version='1.0' encoding='ISO-8859-1'" . "?" . ">\n";
		$xmlOutput .= "<wikirumours>\n";
		$xmlOutput .= "  <version>2.1</version>\n";
		$xmlOutput .= "  <status><![CDATA[" . $user[0]['full_name'] . " successfully connected with the WikiRumours API on " . date('F j, Y, \a\t g:i A') . ". Filters: " . $tl->page['parameter4'] . "]]></status>\n";
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
				if ($model == 'rumours') {
					$xmlOutput .= "    <datapoint>\n";
					$xmlOutput .= "      <rumour_id><![CDATA[" . $data[$counter]['public_id'] . "]]></rumour_id>\n";
					$xmlOutput .= "      <rumour><![CDATA[" . $data[$counter]['description'] . "]]></rumour>\n";
					$xmlOutput .= "      <country_abbreviation><![CDATA[" . $data[$counter]['country_id'] . "]]></country_abbreviation>\n";
					$xmlOutput .= "      <country><![CDATA[" . @$localization_manager->countries[$data[$counter]['country_id']] . "]]></country>\n";
					$xmlOutput .= "      <region><![CDATA[" . $data[$counter]['city'] . "]]></region>\n";
					$xmlOutput .= "      <latitude><![CDATA[" . $data[$counter]['latitude'] . "]]></latitude>\n";
					$xmlOutput .= "      <longitude><![CDATA[" . $data[$counter]['longitude'] . "]]></longitude>\n";
					$xmlOutput .= "      <occurred_on><![CDATA[" . $data[$counter]['occurred_on'] . "]]></occurred_on>\n";
					$xmlOutput .= "      <status_id><![CDATA[" . $data[$counter]['status_id'] . "]]></status_id>\n";
					$xmlOutput .= "      <status><![CDATA[" . $data[$counter]['status'] . "]]></status>\n";
					$xmlOutput .= "      <priority_id><![CDATA[" . $data[$counter]['priority_id'] . "]]></priority_id>\n";
					$xmlOutput .= "      <priority><![CDATA[" . $data[$counter]['priority'] . "]]></priority>\n";
					$xmlOutput .= "      <findings><![CDATA[" . $data[$counter]['findings'] . "]]></findings>\n";
					$xmlOutput .= "      <number_of_sightings>" . $data[$counter]['number_of_sightings'] . "</number_of_sightings>\n";
					$xmlOutput .= "    </datapoint>\n";

					if ($output == 'csv') fputcsv($csvOutput, array($data[$counter]['public_id'], $data[$counter]['description'], $data[$counter]['country_id'], @$localization_manager->countries[$data[$counter]['country_id']], $data[$counter]['city'], $data[$counter]['latitude'], $data[$counter]['longitude'], $data[$counter]['occurred_on'], $data[$counter]['status_id'], $data[$counter]['status'], $data[$counter]['priority_id'], $data[$counter]['priority'], $data[$counter]['findings'], $data[$counter]['number_of_sightings']));
				}
				elseif ($model == 'sightings') {
					$xmlOutput .= "    <datapoint>\n";
					$xmlOutput .= "      <sighting_id><![CDATA[" . $data[$counter]['public_id'] . "]]></sighting_id>\n";
					$xmlOutput .= "      <heard_on><![CDATA[" . $data[$counter]['heard_on'] . "]]></heard_on>\n";
					$xmlOutput .= "      <country_abbreviation><![CDATA[" . $data[$counter]['sighting_country_id'] . "]]></country_abbreviation>\n";
					$xmlOutput .= "      <country><![CDATA[" . @$localization_manager->countries[$data[$counter]['sighting_country_id']] . "]]></country>\n";
					$xmlOutput .= "      <region><![CDATA[" . $data[$counter]['sighting_city'] . "]]></region>\n";
					$xmlOutput .= "      <latitude><![CDATA[" . $data[$counter]['sighting_latitude'] . "]]></latitude>\n";
					$xmlOutput .= "      <longitude><![CDATA[" . $data[$counter]['sighting_longitude'] . "]]></longitude>\n";
					$xmlOutput .= "      <rumour><![CDATA[" . $data[$counter]['description'] . "]]></rumour>\n";
					$xmlOutput .= "      <occurred_on><![CDATA[" . $data[$counter]['occurred_on'] . "]]></occurred_on>\n";
					$xmlOutput .= "      <status_id><![CDATA[" . $data[$counter]['status_id'] . "]]></status_id>\n";
					$xmlOutput .= "      <status><![CDATA[" . $data[$counter]['status'] . "]]></status>\n";
					$xmlOutput .= "      <findings><![CDATA[" . $data[$counter]['findings'] . "]]></findings>\n";
					$xmlOutput .= "    </datapoint>\n";

					if ($output == 'csv') fputcsv($csvOutput, array($data[$counter]['public_id'], $data[$counter]['heard_on'], $data[$counter]['country_id'], @$localization_manager->countries[$data[$counter]['country_id']], $data[$counter]['city'], $data[$counter]['latitude'], $data[$counter]['longitude'], $data[$counter]['description'], $data[$counter]['occurred_on'], $data[$counter]['findings']));
				}
			}
			$xmlOutput .= "  </data>\n";
		}
		$xmlOutput .= "</wikirumours>\n";

		if ($output == 'xml') {
			echo $xmlOutput;
		}
		elseif ($output == 'csv') {
			echo $csvOutput;
			fclose($csvOutput);
		}
		elseif ($output == 'json') {
			$simpleXml = simplexml_load_string($xmlOutput, null, LIBXML_NOCDATA);
			$jsonOutput = json_encode($simpleXml);
	
			echo $jsonOutput;
		}

?>
