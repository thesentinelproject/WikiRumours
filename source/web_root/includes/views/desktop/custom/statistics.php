<?php

	// load Google Charts packages
		$pageJavaScript .= "  google.load('visualization', '1.1', {packages:['bar', 'corechart']});\n";

	echo "<h2>Statistics" . (@$pseudonym['name'] ? " for " . $pseudonym['name'] : false) . "</h2>\n";

	echo "<div class='pageModule row container-fluid'>\n";

	echo "  <div class='row'>\n";
	echo "    <div class='col-lg-6 col-md-6 col-sm-6 col-xs-12'>\n";
	// rumours
		echo "      <div class='panel panel-default'>\n";
	 	echo "        <div class='panel-heading'>\n";
	 	echo "          <h3 class='panel-title'>Rumours</h3>\n";
	 	echo "        </div>\n";
	  	echo "        <div class='panel-body'>" . $numberOfRumours . "</div>\n";
		echo "      </div>\n";
	echo "    </div>\n";
	echo "    <div class='col-lg-6 col-md-6 col-sm-6 col-xs-12'>\n";
	// sightings			
		echo "      <div class='panel panel-default'>\n";
	 	echo "        <div class='panel-heading'>\n";
	 	echo "          <h3 class='panel-title'>Sightings</h3>\n";
	 	echo "        </div>\n";
	  	echo "        <div class='panel-body'>" . $numberOfSightings . "</div>\n";
		echo "      </div>\n";
	echo "    </div>\n";
	echo "  </div>\n";

	// statuses
		echo "  <div class='row'>\n";
		echo "    <div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>\n";
		echo "      <div class='panel panel-default'>\n";
	 	echo "        <div class='panel-heading'>\n";
	 	echo "          <h3 class='panel-title'>Statuses</h3>\n";
	 	echo "        </div>\n";
	  	echo "        <div class='panel-body'>\n";
		echo "          <ul class='nav nav-pills mutedPills'>\n";
		echo "            <li class='active'><a href='#statusChart' data-toggle='tab'>View as chart</a></li>\n";
		echo "            <li><a href='#statusTable' data-toggle='tab'>View as table</a></li>\n";
		echo "          </ul>\n";
		echo "          <div class='tab-content'>\n";
		echo "            <div class='tab-pane active' id='statusChart'>\n";
	  	echo "              <div id='statusPie' style='width: 100%; height: auto;'></div>\n";
	  	echo "            </div>\n";
		echo "            <div class='tab-pane' id='statusTable'>\n";
		echo "              <table class='table table-condensed'>\n";
		echo "              <thead>\n";
		echo "              <tr>\n";
		echo "              <th>Status</th>\n";
		echo "              <th>Rumours</th>\n";
		echo "              <th>Percentage</th>\n";
		echo "              </tr>\n";
		echo "              </thead>\n";
		echo "              <tbody>\n";
		for ($counter = 0; $counter < count($statuses); $counter++) {
			echo "              <tr>\n";
			echo "              <td>" . htmlspecialchars($statuses[$counter]['status'], ENT_QUOTES). "</td>\n";
			echo "              <td>" . $statuses[$counter]['count'] . "</td>\n";
			echo "              <td>" . number_format($statuses[$counter]['count'] / $numberOfRumours * 100, 1) . "%</td>\n";
			echo "              </tr>\n";
		}
		echo "              </tbody>\n";
		echo "              </table>\n";
	  	echo "            </div>\n";
	  	echo "          </div>\n";
	  	echo "        </div>\n";
		echo "      </div>\n";
		echo "    </div>\n";
		echo "  </div>\n";

		$pageJavaScript .= "// status chart\n";
		$pageJavaScript .= "  google.setOnLoadCallback(drawStatusPie);\n\n";
		$pageJavaScript .= "  function drawStatusPie() {\n";
		$pageJavaScript .= "    var data = new google.visualization.arrayToDataTable([\n";
		$pageJavaScript .= "      ['Status', 'Rumours'],\n";
		for ($counter = 0; $counter < count($statuses); $counter++) {
			$pageJavaScript .= "      ['" . htmlspecialchars($statuses[$counter]['status'], ENT_QUOTES). "', " . $statuses[$counter]['count'] . "]";
			if ($counter < count($statuses) - 1) $pageJavaScript .= ",";
			$pageJavaScript .= "\n";
		}
		$pageJavaScript .= "    ]);\n\n";
		$pageJavaScript .= "    var options = {\n";
		$pageJavaScript .= "      is3D: true,\n";
		$pageJavaScript .= "      chartArea: { left: 0, top: 0, width: '100%', height: '100%' },\n";
		$pageJavaScript .= "      pieSliceText: 'value'\n";
		$pageJavaScript .= "    };\n\n";
		$pageJavaScript .= "    var chart = new google.visualization.PieChart(document.getElementById('statusPie'));\n";
		$pageJavaScript .= "    chart.draw(data, options);\n";
		$pageJavaScript .= "  };\n\n";

	// tags
		echo "  <div class='row'>\n";
		echo "    <div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>\n";
		echo "      <div class='panel panel-default'>\n";
	 	echo "        <div class='panel-heading'>\n";
	 	echo "          <h3 class='panel-title'>Tags</h3>\n";
	 	echo "        </div>\n";
	  	echo "        <div class='panel-body'>\n";
		echo "          <ul class='nav nav-pills mutedPills'>\n";
		echo "            <li class='active'><a href='#tagChart' data-toggle='tab'>View as chart</a></li>\n";
		echo "            <li><a href='#tagTable' data-toggle='tab'>View as table</a></li>\n";
		echo "          </ul>\n";
		echo "          <div class='tab-content'>\n";
		echo "            <div class='tab-pane active' id='tagChart'>\n";
	  	echo "              <div id='tagPie' style='width: 100%;'></div>\n";
	  	echo "            </div>\n";
		echo "            <div class='tab-pane' id='tagTable'>\n";
		echo "              <table class='table table-condensed'>\n";
		echo "              <thead>\n";
		echo "              <tr>\n";
		echo "              <th>Tag</th>\n";
		echo "              <th>Rumours</th>\n";
		echo "              <th>Percentage</th>\n";
		echo "              </tr>\n";
		echo "              </thead>\n";
		echo "              <tbody>\n";
		for ($counter = 0; $counter < count($tags); $counter++) {
			echo "              <tr>\n";
			echo "              <td>" . htmlspecialchars($tags[$counter]['tag'], ENT_QUOTES). "</td>\n";
			echo "              <td>" . $tags[$counter]['count'] . "</td>\n";
			echo "              <td>" . number_format($tags[$counter]['count'] / $numberOfRumours * 100, 1) . "%</td>\n";
			echo "              </tr>\n";
		}
		echo "              </tbody>\n";
		echo "              </table>\n";
	  	echo "            </div>\n";
	  	echo "          </div>\n";
	  	echo "        </div>\n";
		echo "      </div>\n";
		echo "    </div>\n";
		echo "  </div>\n";

		$pageJavaScript .= "// tag chart\n";
		$pageJavaScript .= "  google.setOnLoadCallback(drawTagPie);\n\n";
		$pageJavaScript .= "  function drawTagPie() {\n";
		$pageJavaScript .= "    var data = new google.visualization.arrayToDataTable([\n";
		$pageJavaScript .= "      ['Tags', 'Rumours'],\n";
		for ($counter = 0; $counter < count($tags); $counter++) {
			$pageJavaScript .= "      ['" . htmlspecialchars($tags[$counter]['tag'], ENT_QUOTES). "', " . $tags[$counter]['count'] . "]";
			if ($counter < count($tags) - 1) $pageJavaScript .= ",";
			$pageJavaScript .= "\n";
		}
		$pageJavaScript .= "    ]);\n\n";
		$pageJavaScript .= "    var options = {\n";
		$pageJavaScript .= "      is3D: true,\n";
		$pageJavaScript .= "      chartArea: { left: 0, top: 0, width: '100%', height: '100%' },\n";
		$pageJavaScript .= "      pieSliceText: 'value'\n";
		$pageJavaScript .= "    };\n\n";
		$pageJavaScript .= "    var chart = new google.visualization.PieChart(document.getElementById('tagPie'));\n";
		$pageJavaScript .= "    chart.draw(data, options);\n";
		$pageJavaScript .= "  };\n\n";

	echo "</div>\n";

	// rumours and sightings over time
		echo "<div class='pageModule row'>\n";
		echo "  <div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>\n";
		echo "    <h3>Rumours and sightings by date</h3>\n";
		echo "    <ul class='nav nav-pills mutedPills'>\n";
		echo "      <li class='active'><a href='#rumoursAndSightingsByDateChartTab' data-toggle='tab'>View as chart</a></li>\n";
		echo "      <li><a href='#rumoursAndSightingsByDateTableTab' data-toggle='tab'>View as table</a></li>\n";
		echo "    </ul>\n";
		echo "    <div class='tab-content'>\n";
		echo "      <div class='tab-pane active' id='rumoursAndSightingsByDateChartTab'>\n";
		echo "        <div id='rumoursAndSightingsByDateChart' style='width: 100%; height: auto;''></div>\n";
		echo "      </div>\n";
		echo "      <div class='tab-pane' id='rumoursAndSightingsByDateTableTab'>\n";
		echo "        <table class='table table-condensed'>\n";
		echo "        <thead>\n";
		echo "        <tr>\n";
		echo "        <th>Month</th>\n";
		echo "        <th>Rumours</th>\n";
		echo "        <th>Sightings</th>\n";
		echo "        </tr>\n";
		echo "        </thead>\n";
		echo "        <tbody>\n";
		foreach ($rumoursAndSightingsByDateTable as $month=>$counts) {
			echo "        <tr>\n";
			echo "        <td>" . htmlspecialchars($month, ENT_QUOTES). "</td>\n";
			echo "        <td>" . floatval(@$counts['rumours']) . "</td>\n";
			echo "        <td>" . floatval(@$counts['sightings']) . "</td>\n";
			echo "        </tr>\n";
		}
		echo "        </tbody>\n";
		echo "        </table>\n";
		echo "      </div>\n";
		echo "    </div>\n";
		echo "  </div>\n";
		echo "</div>\n";

		$pageJavaScript .= "// rumours by instance chart\n";
		$pageJavaScript .= "  google.setOnLoadCallback(drawRumoursAndSightingsByDateChart);\n\n";
		$pageJavaScript .= "  function drawRumoursAndSightingsByDateChart() {\n";
		$pageJavaScript .= "    var data = new google.visualization.arrayToDataTable([\n";
		$pageJavaScript .= "      ['Month', 'Rumours', 'Sightings'],\n";
		$counter = 0;
		foreach ($rumoursAndSightingsByDateChart as $month=>$counts) {
			$pageJavaScript .= "      ['" . htmlspecialchars($month, ENT_QUOTES). "', " . floatval(@$counts['rumours']) . ", " . floatval(@$counts['sightings']) . "]";
			if ($counter < count($rumoursAndSightingsByDateChart) - 1) $pageJavaScript .= ",";
			$pageJavaScript .= "\n";
			$counter++;
		}
		$pageJavaScript .= "    ]);\n\n";
		$pageJavaScript .= "    var options = {\n";
		$pageJavaScript .= "      curveType: 'function',\n";
		$pageJavaScript .= "      width: '100%',\n";
		$pageJavaScript .= "      vAxis: { viewWindow: { min: 0 }},\n";
		$pageJavaScript .= "      hAxis: { slantedText: true },\n";
		$pageJavaScript .= "      legend: { position: 'bottom' }\n";
		$pageJavaScript .= "    };\n\n";
		$pageJavaScript .= "    var chart = new google.visualization.LineChart(document.getElementById('rumoursAndSightingsByDateChart'));\n";
		$pageJavaScript .= "    chart.draw(data, options);\n";
		$pageJavaScript .= "  };\n\n";

	if (!@$pseudonym['pseudonym_id']) {
		// rumours and sightings per instance
			echo "<div class='pageModule row'>\n";
			echo "  <div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>\n";
			echo "    <h3>Deployments of " . htmlspecialchars($systemPreferences['Name of this application'], ENT_QUOTES) . "</h3>\n";
			echo "    <div id='rumoursAndSightingsByPseudonymChart' style='width: 100%; height: auto;''></div>\n";
			echo "  </div>\n";
			echo "</div>\n";

			$pageJavaScript .= "// rumours by instance chart\n";
			$pageJavaScript .= "  google.setOnLoadCallback(drawRumoursAndSightingsByPseudonymChart);\n\n";
			$pageJavaScript .= "  function drawRumoursAndSightingsByPseudonymChart() {\n";
			$pageJavaScript .= "    var data = new google.visualization.arrayToDataTable([\n";
			$pageJavaScript .= "      ['Deployment', 'Rumours', 'Sightings'],\n";
			for ($counter = 0; $counter < count($rumoursAndSightingsByPseudonym); $counter++) {
				$pageJavaScript .= "      ['" . htmlspecialchars($rumoursAndSightingsByPseudonym[$counter]['name'], ENT_QUOTES). "', " . $rumoursAndSightingsByPseudonym[$counter]['number_of_rumours'] . ", " . $rumoursAndSightingsByPseudonym[$counter]['number_of_sightings'] . "]";
				if ($counter < count($rumoursAndSightingsByPseudonym) - 1) $pageJavaScript .= ",";
				$pageJavaScript .= "\n";
			}
			$pageJavaScript .= "    ]);\n\n";
			$pageJavaScript .= "    var options = {\n";
			$pageJavaScript .= "      colors:['#5bc0de','#addcea'],\n";
			$pageJavaScript .= "      bars: 'horizontal' // Required for Material Bar Charts.\n";
			$pageJavaScript .= "    };\n\n";
			$pageJavaScript .= "    var chart = new google.charts.Bar(document.getElementById('rumoursAndSightingsByPseudonymChart'));\n";
			$pageJavaScript .= "    chart.draw(data, options);\n";
			$pageJavaScript .= "  };\n";
	}

?>