<?php

	function populateCountriesAndRegions() {

		global $countries_TL;
		global $regions_TL;

		$countries_TL = array();
		$regions_TL = array();

		$result = retrieveCountries();

		for ($counter = 0; $counter < count($result); $counter++) {
			$countries_TL[$result[$counter]['country_id']] = $result[$counter]['country'];
			if ($result[$counter]['number_of_regions']) {
				$regions_TL[$result[$counter]['country_id']] = array();
				$regions_TL[$result[$counter]['country_id']]['subdivision'] = $result[$counter]['subdivision'];
			}
		}

		$result = retrieveFromDb('regions');
		for ($counter = 0; $counter < count($result); $counter++) {
			$regions_TL[$result[$counter]['country_id']]['regions'][count(@$regions_TL[$result[$counter]['country_id']]['regions'])] = $result[$counter];
		}

	}

?>