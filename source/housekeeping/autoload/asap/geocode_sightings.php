<?php

/*
	REMOVING GEOCODING:
	------------------

	if ($currentDatabase != 'production') {
		$output .= "Can geocode only from production due to Google IP restrictions.\n";
	}
	else {

		// initialize
			$limit = 10;

		// retrieve ungeocoded sightings
			$sightings = retrieveFromDb('rumour_sightings', null, array('latitude' => 0, 'longitude' => '0', 'unable_to_geocode' => '0'), null, null, null, null, null, false, $limit);

			if (!count($sightings)){
				$output .= "No outstanding ungeocoded sightings found\n";
			}
			else {
				$output .= "Attempting to geocode " . count($sightings) . " sightings\n";

				for ($counter = 0; $counter < count($sightings); $counter++) {

					$output .= "Checking sighting_id " . $sightings[$counter]['sighting_id'] . "\n";

					$error = null;
					$latitude = null;
					$longitude = null;

					// seek match in database
						$match = retrieveFromDb('rumour_sightings', null, array('LOWER(city)' => strtolower($sightings[$counter]['city']), 'LOWER(country_id)' => strtolower($sightings[$counter]['country_id'])), null, null, null, "latitude <> 0 AND longitude <> 0", null, false, 1);
						if (count($match)) {
							updateDb('rumour_sightings', array('latitude' => $match[0]['latitude'], 'longitude' => $match[0]['longitude']), array('sighting_id'=>$sightings[$counter]['sighting_id']), null, null, null, null, 1);
							$output .= "Successfully matched " . trim($sightings[$counter]['city'] . ',' . $sightings[$counter]['country_id'], ', ') . "\n";
						}
						else {
							$match = retrieveFromDb('rumours', null, array('LOWER(city)' => strtolower($sightings[$counter]['city']), 'LOWER(country_id)' => strtolower($sightings[$counter]['country_id'])), null, null, null, "latitude <> 0 AND longitude <> 0", null, false, 1);
							if (count($match)) {
								updateDb('rumour_sightings', array('latitude' => $match[0]['latitude'], 'longitude' => $match[0]['longitude']), array('sighting_id'=>$rumours[$counter]['sighting_id']), null, null, null, null, 1);
								$output .= "Successfully matched " . trim($rumours[$counter]['city'] . ',' . $rumours[$counter]['country_id'], ', ') . "\n";
							}
							else {
								$output .= "Unable to find a match for " . trim($sightings[$counter]['city'] . ',' . $sightings[$counter]['country_id'], ', ') . "; attempting to query Google Maps API\n";

								// verify cap
									$queriesToday = countInDb('api_calls_external', null, array('destination'=>'gm'), null, null, null, "queried_on > '" . date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') - 1, date('Y'))) . "'");
									if ($queriesToday[0]['count'] >= 2250) {
										$output .= "Too many queries today\n";
									}
									else {
										// parse address
											$location = trim($sightings[$counter]['city'] . ',' . $countries_TL[$sightings[$counter]['country_id']], ', ');

											// retrieve data from Google Maps API
												$googleUrl = 'http://maps.googleapis.com/maps/api/geocode/xml?sensor=false&address=' . urlencode($location);
												if (!$file_manager->doesUrlExist($googleUrl)) {
													$output .= "Unable to access Google Maps API\n";
												}
												else {
													insertIntoDb('api_calls_external', array('destination'=>'gm', 'queried_on'=>date('Y-m-d H:i:s')));
													$result = $parser->parseXML($googleUrl, '');
												
													$latitude = @$result['GeocodeResponse']['result']['geometry']['location']['lat'];
													if (!$latitude) $latitude = @$result['GeocodeResponse']['result'][0]['geometry']['location']['lat'];

													$longitude = @$result['GeocodeResponse']['result']['geometry']['location']['lng'];
													if (!$longitude) $longitude = @$result['GeocodeResponse']['result'][0]['geometry']['location']['lng'];
				
													if (!$latitude || !$longitude) {
														if ($result['GeocodeResponse']['status']) $error = $result['GeocodeResponse']['status'];
														else $error = "Connected to Google Maps API, but was unable to successfully determine latitude and longitude";
														updateDb('rumour_sightings', array('unable_to_geocode'=>'1'), array('sighting_id'=>$sightings[$counter]['sighting_id']), null, null, null, null, 1);
														$output .= $error . "\n";
													}
													else {
														updateDb('rumour_sightings', array('latitude'=>$latitude, 'longitude'=>$longitude), array('sighting_id'=>$sightings[$counter]['sighting_id']), null, null, null, null, 1);
														$output .= "Successfully geocoded " . $location . "\n";
													}
												}

									}


							}
						}
				}

			}

	}

*/

?>