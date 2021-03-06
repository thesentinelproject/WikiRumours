<?php

	class localization_manager_TL {

		public $countries = array();
		public $regions = array();
		public $languages = array();
		public $languages_native = array();
		public $currencies = array();
		public $currency_symbols = array();

		public function populateCountries($country = null) {

			if ($country) $matching = ['country_id'=>$country];

			$result = retrieveCountries(@$matching);

			for ($counter = 0; $counter < count($result); $counter++) {
				$this->countries[$result[$counter]['country_id']] = $result[$counter]['country'];
			}

		}

		public function populateRegions($country = null) {

			global $tablePrefix;

			if ($country) $matching = [$tablePrefix . 'regions.country_id'=>$country];

			$result = retrieveRegions(@$matching);

			for ($counter = 0; $counter < count($result); $counter++) {
				if (!@$this->regions[$result[$counter]['country_id']]) $this->regions[$result[$counter]['country_id']] = array();
				$this->regions[$result[$counter]['country_id']]['regions'][] = $result[$counter];
				$this->regions[$result[$counter]['country_id']]['region_type'] = $result[$counter]['subdivision'];
				$this->regions[$result[$counter]['country_id']]['country'] = $result[$counter]['country'];
			}

		}

		public function populateLanguages($commonOnly = false) {
			
			$result = retrieveLanguages(($commonOnly ? ['common'=>'1'] : false));

			for ($counter = 0; $counter < count($result); $counter++) {
				$this->languages[$result[$counter]['language_id']] = $result[$counter]['language'];
				$this->languages_native[$result[$counter]['language_id']] = $result[$counter]['native'];
			}

		}

		public function populateCurrencies() {

			$result = retrieveCurrencies();

			for ($counter = 0; $counter < count($result); $counter++) {
				$this->currencies[$result[$counter]['currency_id']] = $result[$counter]['currency'];
				$this->currency_symbols[$result[$counter]['currency_id']] = $result[$counter]['symbol'];
				$this->currency_countries[$result[$counter]['currency_id']] = $result[$counter]['country'];
			}

		}

		public function geocodeUsingGoogle($location) {

			global $tl;
			
			if (!$location) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No location specified.\n";
				return false;
			}
			
			$geolocation = array();
			$fileManager = new file_manager_TL();
			$parser = new parser_TL();
			$googleUrl = 'http://maps.googleapis.com/maps/api/geocode/xml?sensor=false&address=' . urlencode($location);
			
			if (!$fileManager->doesUrlExist($googleUrl)) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to access Google Maps API.\n";
				return false;
			}
			else {
				$result = $parser->parseXML($googleUrl, '');
				
				$geolocation['Latitude'] = $result['GeocodeResponse']['result']['geometry']['location']['lat'];
				if (!$geolocation['Latitude']) $geolocation['Latitude'] = $result['GeocodeResponse']['result'][0]['geometry']['location']['lat'];
				$geolocation['Longitude'] = $result['GeocodeResponse']['result']['geometry']['location']['lng'];
				if (!$geolocation['Longitude']) $geolocation['Longitude'] = $result['GeocodeResponse']['result'][0]['geometry']['location']['lng'];
	
				if (!$geolocation['Latitude'] || !$geolocation['Longitude']) {
					if ($result['GeocodeResponse']['status']) $geolocation['Error'] = $result['GeocodeResponse']['status'];
					else {
						$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Connected to Google Maps API, but was unable to successfully determine latitude and longitude.\n";
						return false;
					}
				}
			}
			
			return $geolocation;
			
		}
		
		public function reverseGeocodeUsingGoogle($coords) {
			
			$location = array();
			$fileManager = new file_manager_TL();
			$parser = new parser_TL();
			$googleUrl = 'http://maps.googleapis.com/maps/api/geocode/xml?sensor=false&latlng=' . str_replace(' ', '', $coords);
			global $tl;
			
			if (!$coords) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No geocoordinates specified.\n";
				return false;
			}
			
			if (!$fileManager->doesUrlExist($googleUrl)) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to access Google Maps API.\n";
				return false;
			}
			else {
				$result = $parser->parseXML($googleUrl, '');
				if (!$result) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to parse results from Google.\n";
					return false;
				}
				else $location = $result;
			}
			
			return $location;
			
		}
		
		public function dmsToDecimalDegrees($degrees, $minutes, $seconds, $direction) {
			
			$totalSeconds = (floatval($minutes) * 60) + floatval($seconds);
			$fraction = $totalSeconds / 3600;
			$decimalDegrees = floatval($degrees) + $fraction;
			if ($direction == 'S' || $direction == 'W') $decimalDegrees = 0 - $decimalDegrees;
			
			return $decimalDegrees;
			
		}
		
	}
	
/*	
	Localization

	::	DESCRIPTION
	
		Functions and properties to localize content

	::	DEPENDENT ON
	
		input_validator_TL();
		parser_TL();
	
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
