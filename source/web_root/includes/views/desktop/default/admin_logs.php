<?php
	include 'includes/views/desktop/shared/page_top.php';

	echo "  <h2>" . (@$numberOfLogs ? "<span class='label label-default'>" . number_format($numberOfLogs) . "</span> " : false) . "Logs</h2>\n\n";

	echo $form->start('adminLogsForm', null, 'post', null, null, array('onSubmit'=>'validateAdminLogsForm(); return false;')) . "\n";
	echo $form->input('hidden', 'exportData') . "\n";

	// display users
		if (count($logs) < 1) echo "  <p>None.</p>\n";
		else {
			echo "  <table class='table table-condensed table-hover'>\n";
			echo "  <thead>\n";
			echo "  <tr>\n";
			echo "  <th width='25%'>Date</th>\n";
			echo "  <th width='10%'>Initiated by</th>\n";
			echo "  <th width='10%'>Length</th>\n";
			echo "  <th width='60%'>Activity</th>\n";
			echo "  <th width='5%'></th>\n";
			echo "  </tr>\n";
			echo "  </thead>\n";
			echo "  <tbody>\n";
			for ($counter = 0; $counter < count($logs); $counter++) {
				echo "  <tr>\n";
				// Date
					echo "  <td>\n";
					echo "    " . str_replace(' ', '&nbsp;', date('g:i:s A', strtotime($logs[$counter]['connected_on']))) . "\n";
					echo "    <small><span class='text-muted'>" . date('j-M-Y', strtotime($logs[$counter]['connected_on'])) . "</span></small>\n";
					echo "  </td>\n";
				// Initiated by
					echo "  <td>" . @$connectionTypes[$logs[$counter]['connection_type']] . "</td>\n";
				// Length
					echo "  <td>\n";
					if (!@$logs[$counter]['connection_released']) echo "    Timed out\n";
					else echo floatval(@$logs[$counter]['connection_length_in_seconds']) . "&nbsp;s";
					echo "  </td>\n";
				// Activity
					$activity = nl2br($logs[$counter]['activity']);
					if ($logs[$counter]['error_message']) $activity .= $logs[$counter]['error_message'];
					$lineBreak = strpos($activity, '<br />');
					if (!$lineBreak) $lineBreak = strlen($activity);
					$preview = substr($activity, 0, $lineBreak);
					$preview = $parser->truncate($preview, 'c', 45);

					echo "  <td>";
					echo "    <div id='preview_" . $counter . "' class='collapse in'>" . $preview . "</div>\n";
					echo "    <div id='activity_" . $counter . "' class='collapse'>" . $activity . "</div>\n";
					echo "  </td>\n";
					echo "  <td>";
					if (strlen($preview) < strlen($activity)) {
						echo "    <div id='moreSelector_" . $counter . "' class='collapse in'><small><a href='' onClick='moreOrLessSelector(" . $counter . "); revealOrHideLog(" . $counter . "); return false;'>More</a></small></div>\n";
						echo "    <div id='lessSelector_" . $counter . "' class='collapse'><small><a href='' onClick='moreOrLessSelector(" . $counter . "); revealOrHideLog(" . $counter . "); return false;'>Less</a></small></div>\n";
					}
					echo "  </td>\n";
					echo "  </tr>\n";
			}
			echo "  </tbody>\n";
			echo "  </table>\n\n";

		}
		
	// filters
		echo "  <div class='row'>\n";
		// keywords
			echo "    <div class='col-lg-8 col-md-8 col-sm-10 col-xs-8'>\n";
			echo "      " . $form->input('search', 'keywords', @$filters['keywords'], false, null, 'form-control') . "\n";
			echo "    </div>\n";
		// filter button
			echo "    <div class='col-lg-2 col-md-2 col-sm-2 col-xs-4'>\n";
			echo "      " . $form->input('submit', 'filter_button', null, false, 'Filter', 'btn btn-info btn-block') . "\n";
			echo "    </div>\n";
		// export button
			echo "    <div class='col-lg-2 col-md-2 hidden-sm hidden-xs text-right'>\n";
			echo "      " . $form->input('button', 'export_button', null, false, 'Export', 'btn btn-link', null, null, null, null, array('onClick'=>'exportUsers(); return false;')) . "\n";
			echo "    </div>\n";
		echo "  </div>\n";
		echo $form->end() . "\n";

	// pagination
		if ($numberOfPages > 1) {
			echo "  <br />\n";
			echo "  <div class='row'>\n";
			echo "    <div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>\n";
			echo $form->paginate($filters['page'], $numberOfPages, '/admin_logs/' . $keyvalue_array->updateKeyValue($parameter1, 'page', '#', '|'));
			echo "    </div>\n";
			echo "  </div>\n";
		}

	include 'includes/views/desktop/shared/page_bottom.php';
?>