<?php
	include 'includes/views/desktop/shared/page_top.php';

	echo "<h2>Obtain an API key</h2>\n";
			
	if ($pageError) echo "<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $pageError . "</div>\n";
	elseif ($pageSuccess == 'key_generated') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>API key successfully generated.</div>\n";
	elseif ($pageSuccess == 'query_threshold_updated') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Query threshold successfully updated.</div>\n";
	
	if (count($apiKey) < 1) {
		echo $form->start('apiForm') . "\n";
		echo "<p>You don't currently have an API key. Would you like one? The API is intended for users who wish to import " . $systemPreferences['appName'] . " data into their own custom applications.</p>\n";
		echo "<p>" . $form->input('submit', 'obtain_api', null, false, 'Obtain API key now', 'btn btn-info') . "</p>\n";
		echo "<p>Once you have your key, please consult the <a href='/explore_api'>API guide</a> to set up your queries.</p>\n";
		echo $form->end() . "\n";
	}
	else {
		echo $form->start('apiForm') . "\n";
		echo $form->input('hidden', 'allowUnlimited') . "\n";
		echo $form->input('hidden', 'removeUnlimited') . "\n";
		echo "<table class='table table-hover table-condensed'>\n";
		echo "<tr>\n";
		echo "<th>API key</th>\n";
		echo "<th>Total queries</th>\n";
		echo "<th>Today's queries</th>\n";
		echo "<th>Daily limit</th>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td>" . $apiKey[0]['hash'] . "</td>\n";
		echo "<td>" . floatval($allQueries[0]['count']) . "</td>\n";
		echo "<td>" . floatval($recentQueries[0]['count']) . "</td>\n";
		if ($apiKey[0]['value'] == 'u') echo "<td>Unlimited</td>\n";
		else echo "<td>" . $apiCap . "</td>\n";
		echo "</tr>\n";
		echo "</table>\n";

		echo "<p>This is your private, unique, user-specific access key. Please keep it secure (e.g. do not add it to browser-viewable code).</p>";
		echo "<p>If you feel your current API key may be compromised, you can assign a new key. This will instantly disable your old API key.</p>";
		echo "<p>\n";
		echo "  " . $form->input('submit', 'obtain_api', null, false, 'Assign a new API key', 'btn btn-info') . "\n";
		if ($logged_in['can_edit_users']) {
			if ($apiKey[0]['value'] == 'u') echo "  " . $form->input('button', 'remove_unlimited', null, false, 'Remove unlimited queries', 'btn btn-link', null, null, null, null, array('onClick'=>'validateRemoveUnlimited(); return false;')) . "\n";
			else echo "  " . $form->input('button', 'allow_unlimited', null, false, 'Allow unlimited queries', 'btn btn-link', null, null, null, null, array('onClick'=>'validateAllowUnlimited(); return false;')) . "\n";
		}
		echo "  " . $form->input('button', 'cancel', null, false, 'Cancel', 'btn btn-link', '', '', '', '', array('onClick'=>'document.location.href="/profile/' . $username . '"; return false;')) . "\n";
		echo "</p>\n";
		echo $form->end() . "\n";
	}
		
	include 'includes/views/desktop/shared/page_bottom.php';
?>