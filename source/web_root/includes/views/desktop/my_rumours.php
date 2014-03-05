<?php
	include 'includes/views/desktop/shared/page_top.php';

	// rumours assigned to me
		if (count($assignedRumours) > 0) {
			echo "<div class='pageModule'>\n";
			echo "  <h2>Rumours assigned to me</h2>\n";
			echo "  <table class='table table-hover table-condensed'>\n";
			echo "  <thead>\n";
			echo "  <tr>\n";
			echo "  <th>Rumour</th>\n";
			echo "  <th>Status</th>\n";
			echo "  <th>Assigned to</th>\n";
			echo "  </tr>\n";
			echo "  </thead>\n";
			echo "  <tbody>\n";
			for ($counter = 0; $counter < count($assignedRumours); $counter++) {
				echo "  <tr>\n";
				echo "  <td><a href='/rumour/" . $assignedRumours[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($assignedRumours[$counter]['description']) . "'>" . $parser->truncate($assignedRumours[$counter]['description'], 'c', 60) . "</a></td>\n";
				echo "  <td>" . $rumourStatuses[$assignedRumours[$counter]['status']] . "</td>\n";
				if ($assignedRumours[$counter]['assigned_to_username']) $username = "<a href='/profile/" . $assignedRumours[$counter]['assigned_to_username'] . "'>" . $assignedRumours[$counter]['assigned_to_username'] . "</a>";
				else $username = '';
				echo "  <td>" . $username . "</td>\n";
				echo "  </tr>\n";
			}
			echo "  </tbody>\n";
			echo "  </table>\n";
			echo "</div>\n";
		}

	// rumours I've created
		echo "<h2>Rumours I've reported</h2>\n";
		echo "<div class='pageModule'>\n";
		
		if (count($myRumours) < 1) {
			echo "  <p>You haven't reported any rumours. Want to <a href='/rumour_add'>add one now</a>?</p>\n";
		}
		else {
			echo "  <table class='table table-hover table-condensed'>\n";
			echo "  <thead>\n";
			echo "  <tr>\n";
			echo "  <th colspan='2'>Rumour</th>\n";
			echo "  <th>Status</th>\n";
			echo "  </tr>\n";
			echo "  </thead>\n";
			echo "  <tbody>\n";
			for ($counter = 0; $counter < count($myRumours); $counter++) {
				echo "  <tr>\n";
				echo "  <td>" . $parser->bubbleDate(date('d-M-Y', strtotime($myRumours[$counter]['updated_on']))) . "</td>\n";
				echo "  <td><a href='/rumour/" . $myRumours[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($myRumours[$counter]['description']) . "'>" . $parser->truncate($myRumours[$counter]['description'], 'c', 60) . "</a></td>\n";
				echo "  <td>" . $rumourStatuses[$myRumours[$counter]['status']] . "</td>\n";
				echo "  </tr>\n";
			}
			echo "  </tbody>\n";
			echo "  </table>\n";
			
			if ($numberOfPages > 1) {
				echo $form->paginate($page, $numberOfPages, '/my_rumours/#');
			}
		}
		
		echo "</div>\n";
		
	include 'includes/views/desktop/shared/page_bottom.php';
?>