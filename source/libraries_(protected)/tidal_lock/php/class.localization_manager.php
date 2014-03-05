<?php

	class localization_TL {

		public function geocodeUsingGoogle($location) {
			
			if (!$location) {
				errorManager_TL::addError("No location specified.");
				return false;
			}
			
			$geolocation = array();
			$fileManager = new fileManager_TL();
			$parser = new parser_TL();
			$googleUrl = 'http://maps.googleapis.com/maps/api/geocode/xml?sensor=false&address=' . urlencode($location);
			
			if (!$fileManager->doesUrlExist($googleUrl)) {
				errorManager_TL::addError("Unable to access Google Maps API.");
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
						errorManager_TL::addError("Connected to Google Maps API, but was unable to successfully determine latitude and longitude.");
						return false;
					}
				}
			}
			
			return $geolocation;
			
		}
		
		public function reverseGeocodeUsingGoogle($coords) {
			
			$location = array();
			$fileManager = new fileManager_TL();
			$parser = new parser_TL();
			$googleUrl = 'http://maps.googleapis.com/maps/api/geocode/xml?sensor=false&latlng=' . str_replace(' ', '', $coords);
			
			if (!$coords) {
				errorManager_TL::addError("No geocoordinates specified.");
				return false;
			}
			
			if (!$fileManager->doesUrlExist($googleUrl)) {
				errorManager_TL::addError("Unable to access Google Maps API.");
				return false;
			}
			else {
				$result = $parser->parseXML($googleUrl, '');
				if (!$result) {
					errorManager_TL::addError("Unable to parse results from Google.");
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
	
		inputValidator_TL();
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
