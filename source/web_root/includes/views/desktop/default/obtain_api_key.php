<?php

	echo "<h2>Obtain " . $user[0]['username'] . "'s API key</h2>\n";
			
	if (count($apiKey) < 1) {
		echo $form->start('apiForm') . "\n";
		echo "<p>You don't currently have an API key. Would you like one? The API is intended for users who wish to import structured data into their own custom applications.</p>\n";
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
		else echo "<td>" . @$systemPreferences['Maximum API calls'] . "</td>\n";
		echo "</tr>\n";
		echo "</table>\n";

		echo "<p>This is your private, unique, user-specific access key. Please keep it secure (e.g. do not add it to browser-viewable code). If you feel your current API key may be compromised, you can assign a new key. This will instantly disable your old API key.</p>";

		echo "  " . $form->input('button', 'obtain_api', null, false, 'Assign a new API key', 'btn btn-default', null, null, null, null, array('onClick'=>'validateRecycleKey(); return false;')) . "\n";
		if ($logged_in['can_edit_users']) {
			if ($apiKey[0]['value'] == 'u') echo "  " . $form->input('button', 'remove_unlimited', null, false, 'Remove unlimited queries', 'btn btn-default', null, null, null, null, array('onClick'=>'validateRemoveUnlimited(); return false;')) . "\n";
			else echo "  " . $form->input('button', 'allow_unlimited', null, false, 'Allow unlimited queries', 'btn btn-default', null, null, null, null, array('onClick'=>'validateAllowUnlimited(); return false;')) . "\n";
		}
		
		echo $form->end() . "\n";
	}
		
?>