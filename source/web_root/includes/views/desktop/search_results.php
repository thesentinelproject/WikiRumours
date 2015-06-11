<?php
	if (@$filters['view'] == 'map') $pageLoadEvents = "populateMap();";
	include 'includes/views/desktop/shared/page_top.php';

	if ($report == 'recent') echo "<h2>Recent Rumours</h2>\n";
	elseif ($report == 'common') echo "<h2>Most Common Rumours</h2>\n";
	else echo "<h2>Search Results</h2>\n";
			
	if (count($rumours) < 1) {
		echo "<p>No matching rumours found.</p>\n";
	}
	else {

		// tabs
			echo "<ul id='searchResultsTabs' class='nav nav-pills mutedPills'>\n";
			echo "  <li class='" . ($filters['view'] == 'table' ? "active" : false) . "'><a href='#table' data-toggle='tab'>View as table</a></li>\n";
			echo "  <li class='" . ($filters['view'] == 'map' ? "active" : false) . "'><a href='#map' data-toggle='tab' onClick='if (!mapLoaded) populateMap();'>View as map</a></li>\n";
			echo "</ul>\n\n";

		echo "<div class='tab-content'>\n";
		echo "  <div class='tab-pane" . ($filters['view'] == 'table' ? " active" : false) . "' id='table'>\n";

		// table
			if ($report == 'common') {
				echo "<table class='table table-hover table-condensed'>\n";
				echo "<tr>\n";
				echo "<th>Updated</th>\n";
				echo "<th>Rumour</th>\n";
				echo "<th>Status</th>\n";
				echo "<th>Sightings</th>\n";
				echo "</tr>\n";
				for ($counter = 0; $counter < count($rumours); $counter++) {
					echo "<tr>\n";
					echo "<td class='nowrap'>" . date('j-M-Y', strtotime($rumours[$counter]['updated_on'])) . "</td>\n";
					echo "<td><a href='/rumour/" . $rumours[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($rumours[$counter]['description']) . "'>" . $parser->truncate($rumours[$counter]['description'], 'c', 40) . "</a></td>\n";
					echo "<td>" . $operators->firstTrue(@$rumours[$counter]['status'], '-') . "</td>\n";
					echo "<td>" . floatval($rumours[$counter]['number_of_sightings']) . "</td>\n";
					echo "</tr>\n";
				}
				echo "</table>\n";
			}
			else {
				echo "<table class='table table-hover table-condensed'>\n";
				echo "<tr>\n";
				if ($filters['sort'] == 'date_high') echo "<th><a href='/search_results/" . $keyvalue_array->updateKeyValue($keyvalue_array->arrayToKeyValue($filters, '|'), 'sort', 'date_low', '|') . "'>Updated</a></th>\n";
				else echo "<th><a href='/search_results/" . $keyvalue_array->updateKeyValue($keyvalue_array->arrayToKeyValue($filters, '|'), 'sort', 'date_high', '|') . "'>Updated</a></th>\n";
				echo "<th>Rumour</th>\n";
				if ($filters['sort'] == 'priority_high') echo "<th><a href='/search_results/" . $keyvalue_array->updateKeyValue($keyvalue_array->arrayToKeyValue($filters, '|'), 'sort', 'priority_low', '|') . "'>Priority</a></th>\n";
				else echo "<th><a href='/search_results/" . $keyvalue_array->updateKeyValue($keyvalue_array->arrayToKeyValue($filters, '|'), 'sort', 'priority_high', '|') . "'>Priority</a></th>\n";
				if ($filters['sort'] == 'status_down') echo "<th><a href='/search_results/" . $keyvalue_array->updateKeyValue($keyvalue_array->arrayToKeyValue($filters, '|'), 'sort', 'status_up', '|') . "'>Status</a></th>\n";
				else echo "<th><a href='/search_results/" . $keyvalue_array->updateKeyValue($keyvalue_array->arrayToKeyValue($filters, '|'), 'sort', 'status_down', '|') . "'>Status</a></th>\n";
				echo "</tr>\n";
				for ($counter = 0; $counter < count($rumours); $counter++) {
					echo "<tr>\n";
					echo "<td class='nowrap'>" . date('j-M-Y', strtotime($rumours[$counter]['updated_on'])) . "</td>\n";
					echo "<td><a href='/rumour/" . $rumours[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($rumours[$counter]['description']) . "'>" . $parser->truncate($rumours[$counter]['description'], 'c', 30) . "</a></td>\n";
					echo "<td class='nowrap'>" . $operators->firstTrue(@$rumours[$counter]['priority'], '-') . "</td>\n";
					echo "<td class='nowrap'>" . $operators->firstTrue(@$rumours[$counter]['status'], '-') . "</td>\n";
					echo "</tr>\n";
				}
				echo "</table>\n";
			}
			
			if ($numberOfPages > 1) {
				echo $form->paginate($page, $numberOfPages, '/search_results/' . $keyvalue_array->updateKeyValue($keyvalue_array->updateKeyValue($keyvalue_array->updateKeyValue($keyvalue_array->arrayToKeyValue($filters), 'page', '#'), 'report', $report), 'sort', $filters['sort']));
			}

		echo "  </div>\n";

		// map
			echo "  <div class='tab-pane" . ($filters['view'] == 'map' ? " active" : false) . "' id='map'>\n";
			echo "    <div id='searchResultsMapCanvas' class='img-rounded img-thumbnail'>Loading...</div>\n";
			echo "  </div><!-- #map -->\n";

			$pageJavaScript .= "// Populate map\n";
			$pageJavaScript .= "  var mapLoaded = false;\n\n";
			$pageJavaScript .= "  function populateMap() {\n";
			$pageJavaScript .= "    var myLatlng = new google.maps.LatLng(" . $map[count($map) - 1]['latitude'] . "," . $map[count($map) - 1]['longitude'] . ");\n";
			$pageJavaScript .= "    var myOptions = {\n";
			$pageJavaScript .= "      zoom: 6,\n";
			$pageJavaScript .= "      center: myLatlng,\n";
			$pageJavaScript .= "      mapTypeId: google.maps.MapTypeId.TERRAIN\n";
			$pageJavaScript .= "    };\n";
			$pageJavaScript .= "    var thisMap = new google.maps.Map(document.getElementById('searchResultsMapCanvas'), myOptions);\n";
			$pageJavaScript .= "    mapLoaded = true;\n\n";

			for ($counter = 0; $counter < count($map); $counter++) {
				$pageJavaScript .= "    var myLatlng = new google.maps.LatLng(" . $map[$counter]['latitude'] . "," . $map[$counter]['longitude'] . ");\n";
				$pageJavaScript .= "    var rumour_marker_" . $counter . " = new google.maps.Marker({\n";
				$pageJavaScript .= "      position: myLatlng, \n";
				$pageJavaScript .= "      map: thisMap, \n";
				if (substr_count($map[$counter]['status'], 'true')) $pageJavaScript .= "      icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png', \n";
				elseif (substr_count($map[$counter]['status'], 'false')) $pageJavaScript .= "      icon: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png', \n";
				else $pageJavaScript .= "      icon: 'http://maps.google.com/mapfiles/ms/icons/yellow-dot.png', \n";
				$pageJavaScript .= "      title:" . '"' . htmlspecialchars(preg_replace( "/\r|\n/", "", $map[$counter]['status'] . " : " . $map[$counter]['description']), ENT_QUOTES) . '"' . "\n";
				$pageJavaScript .= "    });\n";
				$pageJavaScript .= "    google.maps.event.addListener(rumour_marker_" . $counter . ", 'click', function() {\n";
				$pageJavaScript .= "      document.location.href = '/rumour/" . $map[$counter]['public_id'] . "';\n";
				$pageJavaScript .= "    });\n";
			}
			
			$pageJavaScript .= "  }\n";

		echo "</div>\n";
		
	}
	
	include 'includes/views/desktop/shared/page_bottom.php';
?>