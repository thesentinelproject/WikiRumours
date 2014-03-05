<?php
	include 'includes/views/desktop/shared/page_top.php';

	echo "<h2>My Watchlist</h2>\n";
			
	if ($pageError) echo "<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $pageError . "</div>\n";
	elseif ($pageSuccess == 'rumour_removed') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully removed rumour.</div>\n";
	elseif ($pageSuccess == 'notification_added') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully added notification.</div>\n";
	elseif ($pageSuccess == 'notification_removed') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully removed notification.</div>\n";
	
	if (count($watchlist) < 1) {
		echo "<p>You aren't currently watching any rumours. To begin monitoring and receive notification of updates, locate a rumour and click the &quot;add to watchlist&quot; button.</p>\n";
	}
	else {
		echo $form->start('watchlistForm', '', 'post', null, null, array('onSubmit'=>'return false;')) . "\n";
		echo $form->input('hidden', 'rumourToUnfollow') . "\n";
		echo $form->input('hidden', 'rumourToNotify') . "\n";
		echo $form->input('hidden', 'rumourToUnnotify') . "\n";
		echo "<table class='table table-hover table-condensed'>\n";
		echo "<tr>\n";
		echo "<th>Rumour</th>\n";
		echo "<th>Status</th>\n";
		echo "<th>Created on</th>\n";
		echo "<th></th>\n";
		echo "</tr>\n";
		for ($counter = 0; $counter < count($watchlist); $counter++) {
			echo "<tr>\n";
			echo "<td><a href='/rumour/" . $watchlist[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($watchlist[$counter]['description']) . "'>" . $parser->truncate($watchlist[$counter]['description'], 'c', 50) . "</a></td>\n";
			echo "<td>" . $rumourStatuses[$watchlist[$counter]['status']] . "</td>\n";
			echo "<td>" . date('d-M-Y', strtotime($watchlist[$counter]['created_on'])) . "</td>\n";
			echo "<td>\n";
			for ($outputCounter = 0; $outputCounter < 2; $outputCounter++) {
				if ($outputCounter == 0) {
					echo "  <div class='hidden-xs'>\n"; // non-mobile experience
					if ($watchlist[$counter]['notify_of_updates']) echo "    <a href='javascript:void(0)' onClick='doNotNotify(" . '"' . $watchlist[$counter]['rumour_id'] . '"' . "); return false;'><span class='glyphicon glyphicon-envelope transluscent' title='Turn off notification'></span></a>\n";
					else echo "    <a href='javascript:void(0)' onClick='notify(" . '"' . $watchlist[$counter]['rumour_id'] . '"' . "); return false;'><span class='glyphicon glyphicon-ban-circle transluscent' title='Turn on notification'></span></a>\n";
					echo "    <a href='javascript:void(0)' onClick='unfollow(" . '"' . $watchlist[$counter]['rumour_id'] . '"' . "); return false;'><span class='glyphicon glyphicon-trash transluscent' title='Stop watching'></span></a>\n";
					echo "  </div>\n";
				}
				else {
					echo "  <div class='visible-xs'>\n"; // mobile experience
					if ($watchlist[$counter]['notify_of_updates']) echo "    <button class='btn btn-default' onClick='doNotNotify(" . '"' . $watchlist[$counter]['rumour_id'] . '"' . "); return false;'><span class='glyphicon glyphicon-envelope transluscent' title='Turn off notification'></span></button>\n";
					else echo "    <button class='btn btn-default' onClick='notify(" . '"' . $watchlist[$counter]['rumour_id'] . '"' . "); return false;'><span class='glyphicon glyphicon-ban-circle transluscent' title='Turn on notification'></span></button>\n";
					echo "    <button class='btn btn-default' onClick='unfollow(" . '"' . $watchlist[$counter]['rumour_id'] . '"' . "); return false;'><span class='glyphicon glyphicon-trash transluscent' title='Stop watching'></span></button>\n";
					echo "  </div>\n";
				}
			}
			echo "</td>\n";
			echo "</tr>\n";
		}
		echo "</table>\n";
		echo $form->end() . "\n";
	}
	
	include 'includes/views/desktop/shared/page_bottom.php';
?>