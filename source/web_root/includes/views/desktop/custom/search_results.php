<?php

	echo "<h2>" . $tl->page['title'] . "</h2>\n";
			
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
				echo "<th>Occurred</th>\n";
				echo "<th>Rumour</th>\n";
				echo "<th>Status</th>\n";
				echo "<th>Sightings</th>\n";
				echo "</tr>\n";
				for ($counter = 0; $counter < count($rumours); $counter++) {
					echo "<tr>\n";
					echo "<td class='nowrap'>" . date('j-M-Y', strtotime($rumours[$counter]['updated_on'])) . "</td>\n";
					echo "<td class='nowrap'>" . date('j-M-Y', strtotime($rumours[$counter]['occurred_on'])) . "</td>\n";
					echo "<td><a href='/rumour/" . $rumours[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($rumours[$counter]['description']) . "'>" . $parser->truncate($rumours[$counter]['description'], 'c', 30) . "</a></td>\n";
					echo "<td>" . $operators->firstTrue(@$rumours[$counter]['status'], '-') . "</td>\n";
					echo "<td>" . floatval($rumours[$counter]['number_of_sightings']) . "</td>\n";
					echo "</tr>\n";
				}
				echo "</table>\n";
			}
			else {
				echo "<table class='table table-hover table-condensed'>\n";
				echo "<tr>\n";
				// Updated
					if ($filters['sort'] == 'date_high') echo "<th><a href='/search_results/" . $keyvalue_array->updateKeyValue($keyvalue_array->arrayToKeyValue($filters, '|'), 'sort', 'date_low', '|') . "'>Updated</a></th>\n";
					else echo "<th><a href='/search_results/" . $keyvalue_array->updateKeyValue($keyvalue_array->arrayToKeyValue($filters, '|'), 'sort', 'date_high', '|') . "'>Updated</a></th>\n";
				// Occurred
					if ($filters['sort'] == 'occurred_date_high') echo "<th><a href='/search_results/" . $keyvalue_array->updateKeyValue($keyvalue_array->arrayToKeyValue($filters, '|'), 'sort', 'occurred_date_low', '|') . "'>Occurred</a></th>\n";
					else echo "<th><a href='/search_results/" . $keyvalue_array->updateKeyValue($keyvalue_array->arrayToKeyValue($filters, '|'), 'sort', 'occurred_date_high', '|') . "'>Occurred</a></th>\n";
				// Priority
//					echo "<th>\n";
//					if ($filters['sort'] == 'priority_high') echo "<a href='/search_results/" . $keyvalue_array->updateKeyValue($keyvalue_array->arrayToKeyValue($filters, '|'), 'sort', 'priority_low', '|') . "'>Priority</a>\n";
//					else echo "<a href='/search_results/" . $keyvalue_array->updateKeyValue($keyvalue_array->arrayToKeyValue($filters, '|'), 'sort', 'priority_high', '|') . "'>Priority</a>\n";
//					echo "</th>\n";
				// Rumour
					echo "<th colspan='2'>Rumour</th>\n";
				// Status
					if ($filters['sort'] == 'status_down') echo "<th><a href='/search_results/" . $keyvalue_array->updateKeyValue($keyvalue_array->arrayToKeyValue($filters, '|'), 'sort', 'status_up', '|') . "'>Status</a></th>\n";
					else echo "<th><a href='/search_results/" . $keyvalue_array->updateKeyValue($keyvalue_array->arrayToKeyValue($filters, '|'), 'sort', 'status_down', '|') . "'>Status</a></th>\n";
				echo "</tr>\n";
				for ($counter = 0; $counter < count($rumours); $counter++) {
					echo "<tr>\n";
					echo "<td class='nowrap'>" . date('j-M-Y', strtotime($rumours[$counter]['updated_on'])) . "</td>\n";
					echo "<td class='nowrap'>" . ($rumours[$counter]['occurred_on'] != '0000-00-00 00:00:00' ? date('j-M-Y', strtotime($rumours[$counter]['occurred_on'])) : false) . "</td>\n";
					echo "<td class='nowrap'>\n";
					if (@$rumours[$counter]['priority_icon']) echo "  <span class='tooltips' data-toggle='tooltip' title='" . @$rumours[$counter]['priority'] . "'>" . $rumours[$counter]['priority_icon'] . "</span>\n";
					elseif (@$rumours[$counter]['priority']) echo "  <span class='tooltips' data-toggle='tooltip' title='" . @$rumours[$counter]['priority'] . "'><span class='badge'>" . substr($rumours[$counter]['priority'], 0, 1) . "</span></span>\n";
					else echo "  -\n";
					echo "</td>\n";
					echo "<td><span class='tooltips' data-toggle='tooltip' title='" . @$rumours[$counter]['description'] . "'><a href='/rumour/" . $rumours[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($rumours[$counter]['description']) . "'>" . $parser->truncate($rumours[$counter]['description'], 'c', 50) . "</a></span></td>\n";
					echo "<td class='nowrap'>\n";
					if (@$rumours[$counter]['status_icon']) echo "  <span class='tooltips' data-toggle='tooltip' title='" . @$rumours[$counter]['status'] . "'>" . $rumours[$counter]['status_icon'] . "</span>\n";
					elseif (@$rumours[$counter]['status']) echo "  <span class='tooltips' data-toggle='tooltip' title='" . @$rumours[$counter]['status'] . "'><span class='badge'>" . substr($rumours[$counter]['status'], 0, 1) . "</span></span>\n";
					else echo "  -\n";
					echo "</td>\n";
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

			$tl->page['javascript'] .= "// Populate map\n";
			$tl->page['javascript'] .= "  var mapLoaded = false;\n\n";
			$tl->page['javascript'] .= "  function populateMap() {\n";
			$tl->page['javascript'] .= "    var myLatlng = new google.maps.LatLng(" . $map[count($map) - 1]['latitude'] . "," . $map[count($map) - 1]['longitude'] . ");\n";
			$tl->page['javascript'] .= "    var myOptions = {\n";
			$tl->page['javascript'] .= "      zoom: 6,\n";
			$tl->page['javascript'] .= "      center: myLatlng,\n";
			$tl->page['javascript'] .= "      mapTypeId: google.maps.MapTypeId.TERRAIN\n";
			$tl->page['javascript'] .= "    };\n";
			$tl->page['javascript'] .= "    var thisMap = new google.maps.Map(document.getElementById('searchResultsMapCanvas'), myOptions);\n";
			$tl->page['javascript'] .= "    mapLoaded = true;\n\n";

			for ($counter = 0; $counter < count($map); $counter++) {
				$tl->page['javascript'] .= "    var myLatlng = new google.maps.LatLng(" . $map[$counter]['latitude'] . "," . $map[$counter]['longitude'] . ");\n";
				$tl->page['javascript'] .= "    var rumour_marker_" . $counter . " = new google.maps.Marker({\n";
				$tl->page['javascript'] .= "      position: myLatlng, \n";
				$tl->page['javascript'] .= "      map: thisMap, \n";
				if (substr_count($map[$counter]['status'], 'true')) $tl->page['javascript'] .= "      icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png', \n";
				elseif (substr_count($map[$counter]['status'], 'false')) $tl->page['javascript'] .= "      icon: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png', \n";
				else $tl->page['javascript'] .= "      icon: 'http://maps.google.com/mapfiles/ms/icons/yellow-dot.png', \n";
				$tl->page['javascript'] .= "      title:" . '"' . htmlspecialchars(preg_replace( "/\r|\n/", "", $map[$counter]['status'] . " : " . $map[$counter]['description']), ENT_QUOTES) . '"' . "\n";
				$tl->page['javascript'] .= "    });\n";
				$tl->page['javascript'] .= "    google.maps.event.addListener(rumour_marker_" . $counter . ", 'click', function() {\n";
				$tl->page['javascript'] .= "      document.location.href = '/rumour/" . $map[$counter]['public_id'] . "';\n";
				$tl->page['javascript'] .= "    });\n";
			}
			
			$tl->page['javascript'] .= "  }\n";

		echo "</div>\n";
		
	}
	
?>