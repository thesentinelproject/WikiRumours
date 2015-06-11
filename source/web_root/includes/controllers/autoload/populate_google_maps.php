<?php

	function createJavaScriptToPopulateGoogleMaps_TL($locationArray, $canvasID, $mapCenter = '55,-95', $mapZoom = 3, $mapStyle = 'TERRAIN', $crosshairIcon = false, $latitudeElement = false, $longitudeElement = false) {

		// locationArray should be structured as follows:
		//
		//   $locationArray[0]['Caption']
		//   $locationArray[0]['Link']
		//   $locationArray[0]['Icon']
		//   $locationArray[0]['HideIcon']
		//   $locationArray[0]['Latitude']
		//   $locationArray[0]['Longitude']
		
		if (!$canvasID) return false;
		$random = rand(0,9999999);
		$output = '';

		$output .= "      var myLatlng = new google.maps.LatLng(" . $mapCenter . ");\n";
		$output .= "      var myOptions = {\n";
		$output .= "        zoom: " . $mapZoom . ",\n";
		$output .= "        center: myLatlng,\n";
		$output .= "        mapTypeId: google.maps.MapTypeId." . $mapStyle . "\n";
		$output .= "      };\n";
		$output .= "      var map_" . $random . " = new google.maps.Map(document.getElementById('" . $canvasID . "'), myOptions);\n\n";

		for ($counter = 0; $counter < count($locationArray); $counter++) {
			if ($locationArray[$counter]['Latitude'] <> 0 && $locationArray[$counter]['Longitude'] <> 0 && !$locationArray[$counter]['HideIcon']) {
				$output .= "      var myLatlng = new google.maps.LatLng(" . $locationArray[$counter]['Latitude'] . "," . $locationArray[$counter]['Longitude'] . ");\n";
				$output .= "      var marker_" . $counter . " = new google.maps.Marker({\n";
				$output .= "        position: myLatlng, \n";
				$output .= "        map: map_" . $random . ", \n";
				if ($locationArray[$counter]['Icon']) $output .= "        icon: " . '"' . $locationArray[$counter]['Icon'] . '"' . ", \n";
				$output .= "        title:" . '"' . htmlspecialchars($locationArray[$counter]['Caption'], ENT_QUOTES) . '"' . "\n";
				$output .= "      });\n";
				if ($locationArray[$counter]['Link']) {
					$output .= "      google.maps.event.addListener(marker_" . $counter . ", 'click', function() {\n";
					$output .= "        document.location.href = '" . $locationArray[$counter]['Link'] . "';\n";
					$output .= "      });\n";
				}
			}
				
			if ($locationArray[$counter]['Latitude'] <> 0 && $locationArray[$counter]['Longitude'] <> 0) {
				if ($crosshairIcon) {
					$output .= "    var marker_" . $counter . "_crosshair = new google.maps.Marker({\n";
					$output .= "      map: map_" . $random . ",\n";
					$output .= "      icon: '" . $crosshairIcon . "',\n";
					$output .= "      title: 'Position crosshair and then click for geocoordinates...'\n";
					$output .= "    });\n";
					$output .= "    marker_" . $counter . "_crosshair.bindTo('position', map_" . $random . ", 'center');\n\n";				

					$output .= "    google.maps.event.addListener(marker_" . $counter . "_crosshair, 'click', function() {\n";
					$output .= "      var crosshairCoords = map_" . $random . ".getCenter();\n";
					$output .= "      var latToCopy = crosshairCoords.lat();\n";
					$output .= "      var longToCopy = crosshairCoords.lng();\n";
					if ($latitudeElement && $longitudeElement) {
						$output .= "      document.getElementById('" . $latitudeElement ."').value = latToCopy;\n";
						$output .= "      document.getElementById('" . $longitudeElement ."').value = longToCopy;\n";
					}
					else $output .= "      alert(latToCopy + ', ' + longToCopy);\n";
					$output .= "    });\n\n";
				}
			}
		}
			
		return $output;

	}
	
/*
	Populate Google Maps

	::	DESCRIPTION
	
		Plots locations on a Google Map

	::	DEPENDENT ON

	::	RETURNS
	
		JavaScript if successful, false if not
		
	::	VERSION HISTORY
	
		27-Feb-2013:	Updated function name and changed from automatically
						outputting JavaScript to returning a variable
						containing JavaScript

	::	LICENSE
	
		Copyright (C) 2010-2013
		Timothy Quinn / Tidal Lock / Consolidated Biro
		
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