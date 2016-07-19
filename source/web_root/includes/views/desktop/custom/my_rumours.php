<?php

	// rumours assigned to me
		if (count($assignedRumours) > 0) {
			echo "<div class='pageModule'>\n";
			echo "  <h2>" . ($logged_in['is_moderator'] ? "Unassigned rumours and rumours assigned to me" : "Rumours assigned to me") . "</h2>\n";
			echo "  <table class='table table-hover table-condensed'>\n";
			echo "  <thead>\n";
			echo "  <tr>\n";
			echo "  <th colspan='2'>Updated</th>\n";
			echo "  <th>Rumour</th>\n";
			echo "  <th>Status</th>\n";
			echo "  <th>Priority</th>\n";
			echo "  <th>Assigned&nbsp;to</th>\n";
			echo "  </tr>\n";
			echo "  </thead>\n";
			echo "  <tbody>\n";
			for ($counter = 0; $counter < count($assignedRumours); $counter++) {
				echo "  <tr>\n";
				echo "  <td class='nowrap'>" . date('j-M-Y', strtotime($assignedRumours[$counter]['updated_on'])) . "</td>\n";
				if ($assignedRumours[$counter]['update_by'] && $assignedRumours[$counter]['update_by'] < date('Y-m-d H:i:s')) echo "    <td><span class='glyphicon glyphicon-time transluscent' title='This rumour is overdue an update!'></span></td>\n";
				else echo "  <td></td>\n";
				echo "  <td><a href='/rumour/" . $assignedRumours[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($assignedRumours[$counter]['description']) . "'>" . $parser->truncate($assignedRumours[$counter]['description'], 'c', 50) . "</a></td>\n";
				echo "  <td>" . $operators->firstTrue(@$assignedRumours[$counter]['status'], '-') . "</td>\n";
				echo "  <td>" . $operators->firstTrue(@$assignedRumours[$counter]['priority'], '-') . "</td>\n";
				if ($assignedRumours[$counter]['assigned_to_username']) $username = "<a href='/profile/" . $assignedRumours[$counter]['assigned_to_username'] . "'>" . $assignedRumours[$counter]['assigned_to_username'] . "</a>";
				else $username = '-';
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
			echo "  <th>Updated</th>\n";
			echo "  <th>Rumour</th>\n";
			echo "  <th>Status</th>\n";
			echo "  </tr>\n";
			echo "  </thead>\n";
			echo "  <tbody>\n";
			for ($counter = 0; $counter < count($myRumours); $counter++) {
				echo "  <tr>\n";
				echo "  <td class='nowrap'>" . date('j-M-Y', strtotime($myRumours[$counter]['updated_on'])) . "</td>\n";
				echo "  <td><a href='/rumour/" . $myRumours[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($myRumours[$counter]['description']) . "'>" . $parser->truncate($myRumours[$counter]['description'], 'c', 60) . "</a></td>\n";
				echo "  <td>" . $operators->firstTrue(@$myRumours[$counter]['status'], '-') . "</td>\n";
				echo "  </tr>\n";
			}
			echo "  </tbody>\n";
			echo "  </table>\n";
			
			if ($numberOfPages > 1) {
				echo $form->paginate($filters['page'], $numberOfPages, '/my_rumours/#');
			}
		}
		
		echo "</div>\n";
		
?>