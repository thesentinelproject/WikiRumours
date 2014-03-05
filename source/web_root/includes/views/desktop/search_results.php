<?php
	include 'includes/views/desktop/shared/page_top.php';

	if ($pageError) echo "<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $pageError . "</div>\n";
	
	if ($report == 'recent') echo "<h2>Recent Rumours</h2>\n";
	elseif ($report == 'common') echo "<h2>Most Common Rumours</h2>\n";
	else echo "<h2>Search Results</h2>\n";
			
	if (count($rumours) < 1) {
		echo "<p>No matching rumours found.</p>\n";
	}
	else {
		if ($report == 'common') {
			echo "<table class='table table-hover table-condensed'>\n";
			echo "<tr>\n";
			echo "<th colspan='2'>Rumour</th>\n";
			echo "<th>Sightings</th>\n";
			echo "</tr>\n";
			for ($counter = 0; $counter < count($rumours); $counter++) {
				echo "<tr>\n";
				echo "<td>" . $parser->bubbleDate(date('Y-m-d', strtotime($rumours[$counter]['updated_on']))) . "</td>\n";
				echo "<td><a href='/rumour/" . $rumours[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($rumours[$counter]['description']) . "'>" . $parser->truncate($rumours[$counter]['description'], 'c', 70) . "</a></td>\n";
				echo "<td>" . $rumours[$counter]['number_of_sightings'] . "</td>\n";
				echo "</tr>\n";
			}
			echo "</table>\n";
		}
		else {
			echo "<table class='table table-hover table-condensed'>\n";
			echo "<tr>\n";
			echo "<th colspan='2'>Rumour</th>\n";
			echo "<th>Status</th>\n";
			echo "</tr>\n";
			for ($counter = 0; $counter < count($rumours); $counter++) {
				echo "<tr>\n";
				echo "<td>" . $parser->bubbleDate(date('Y-m-d', strtotime($rumours[$counter]['updated_on']))) . "</td>\n";
				echo "<td><a href='/rumour/" . $rumours[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($rumours[$counter]['description']) . "'>" . $parser->truncate($rumours[$counter]['description'], 'c', 60) . "</a></td>\n";
				echo "<td class='nowrap'>" . $rumourStatuses[$rumours[$counter]['status']] . "</td>\n";
				echo "</tr>\n";
			}
			echo "</table>\n";
		}
		
		if ($numberOfPages > 1) {
			echo $form->paginate($page, $numberOfPages, '/search_results/' . $keyValue->updateKeyValue($keyValue->updateKeyValue($keyValue->updateKeyValue($keyValue->arrayToKeyValue($filters), 'page', '#'), 'report', $report), 'sort', $sort));
		}
		
	}
	
	include 'includes/views/desktop/shared/page_bottom.php';
?>