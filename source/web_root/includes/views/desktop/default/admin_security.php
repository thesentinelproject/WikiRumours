<?php

	echo "<h2>" . $tl->page['title'] . "</h2>\n";

	if ($screen == 'all') {

		if (!count($banned)) echo "<p>None yet.</p>\n";
		else {

			echo "<table class='table table-hover table-condensed'>\n";
			echo "<thead>\n";
			echo "<tr>\n";
			echo "<th>IP</th>\n";
			echo "<th>Location</th>\n";
			echo "<th>Attempts</th>\n";
			echo "<th>Date Banned</th>\n";
			echo "<th></th>\n";
			echo "</tr>\n";
			echo "</thead>\n";
			echo "<tbody>\n";

			for ($counter = 0; $counter < count($banned); $counter++) {

				echo "<tr>\n";
				// ip
					echo "<td><span class='bootstrap_tooltip' data-toggle='tooltip' data-placement='left' title=" . '"' . addSlashes($banned[$counter]['notes']) .'"' . ">" . $banned[$counter]['ip'] . "</span></td>\n";
				// location
					$city = $banned[$counter]['city'];
					if (@$localization_manager->countries[$banned[$counter]['country_id']]) $country = $localization_manager->countries[$banned[$counter]['country_id']];
					else $country = "Unknown";
					echo "<td>" . trim($city . ", " . $country, ", ") . "</td>\n";
				// attempts
					echo "<td>" . floatval($banned[$counter]['attempts']) . "</td>\n";
				// date
					echo "<td>" . date('F j, Y', strtotime($banned[$counter]['banned_on'])) . "</td>\n";
				// actions
					echo "<td class='right'>\n";
					echo "  " . $form->input('button', null, null, false, 'Edit', 'btn btn-default btn-sm', null, null, null, null, array('onClick'=>'document.location.href="/admin_security/screen=edit_banned_ip|id=' . $banned[$counter]['banned_id'] . '"; return false;')) . "\n";
					echo "  " . $form->input('button', null, null, false, 'Whois', 'btn btn-link btn-sm', null, null, null, null, array('onClick'=>'document.location.href="http://whatismyipaddress.com/ip/' . $banned[$counter]['ip'] . '"; return false;')) . "\n";
					echo "</td>\n";
				echo "</tr>\n";

			}

			echo "</tbody>\n";
			echo "</table>\n";

		}

		echo $form->input('button', 'add_button', null, false, 'Add', 'btn btn-info', '', '', '', '', array('onClick'=>'document.location.href="/admin_security/screen=add_banned_ip"; return false;')) . "\n";

	}
	elseif ($screen == 'edit_banned_ip' || $screen == 'add_banned_ip') {

		echo $form->start('updateBannedIpForm', '', 'post', null, null, ['onSubmit'=>'validateBannedIpForm(); return false;']) . "\n";
		echo $form->input('hidden', 'screen', $screen) . "\n";
		echo $form->input('hidden', 'unblockRequested') . "\n";

		// IP
			if ($screen == 'add_banned_ip') echo $form->row('text', 'ip', $operators->firstTrue(@$_POST['ip'], $banned[0]['ip']), true, "IP", 'form-control', null, 50);
			else echo $form->row('uneditable_static', 'ip', $banned[0]['ip'], false, "IP");
		// Notes
			echo $form->row('text', 'notes', $operators->firstTrue(@$_POST['notes'], $banned[0]['notes']), false, "Notes", 'form-control', null, 255);
		// Actions
			echo $form->rowStart('actions');
			echo "  <div class='row'>\n";
			echo "    <div class='col-lg-9 col-md-9 col-sm-9 col-xs-9'>\n";
			echo "      " . $form->input('submit', 'submit_button', null, true, ($screen == 'add_banned_ip' ? "Start blocking" : "Save"), 'btn btn-info') . "\n";
			echo "      " . $form->input('cancel_and_return', 'cancel_button', null, true, "Cancel", 'btn btn-link') . "\n";
			echo "    </div>\n";
			echo "    <div class='col-lg-3 col-md-3 col-sm-3 col-xs-3 text-right'>\n";
			if ($screen == 'edit_banned_ip') echo "      " . $form->input('button', 'unblock_button', null, true, "Unblock", 'btn btn-danger', null, null, null, null, ['onClick'=>'unblock(); return false;']) . "\n";
			echo "    </div>\n";
			echo "  </div>\n";
			echo $form->rowEnd();

		echo $form->end() . "\n";

	}

?>