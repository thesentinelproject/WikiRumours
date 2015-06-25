<?php

	echo "<h2>My Watchlist</h2>\n";
			
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
			echo "<td>" . $operators->firstTrue(@$watchlist[$counter]['status'], '-') . "</td>\n";
			echo "<td>" . date('j-M-Y', strtotime($watchlist[$counter]['created_on'])) . "</td>\n";
			echo "<td class='nowrap'>\n";
			if ($watchlist[$counter]['notify_of_updates']) echo "    <a href='javascript:void(0)' onClick='doNotNotify(" . '"' . $watchlist[$counter]['rumour_id'] . '"' . "); return false;'><span class='tooltips' data-toggle='tooltip' title='Turn off notification'><span class='glyphicon glyphicon-envelope transluscent'></span></span></a>\n";
			else echo "    <a href='javascript:void(0)' onClick='notify(" . '"' . $watchlist[$counter]['rumour_id'] . '"' . "); return false;'><span class='tooltips' data-toggle='tooltip' title='Turn on notification'><span class='glyphicon glyphicon-ban-circle transluscent'></span></span></a>\n";
			echo "    <a href='javascript:void(0)' onClick='unfollow(" . '"' . $watchlist[$counter]['rumour_id'] . '"' . "); return false;'><span class='tooltips' data-toggle='tooltip' title='Stop watching'><span class='glyphicon glyphicon-trash transluscent'></span></span></a>\n";
			echo "</td>\n";
			echo "</tr>\n";
		}
		echo "</table>\n";
		
		echo $form->end() . "\n";
	}
	
?>