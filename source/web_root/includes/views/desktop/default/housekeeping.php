<?php
	include 'includes/views/desktop/shared/page_top.php';

	echo "<h2>Housekeeping</h2>\n";
	
	if (!$pageError && $action == 'run') {
		$output = retrieveSingleFromDb('logs', null, array('log_id'=>$logID));
		if ($output[0]['activity']) echo nl2br($output[0]['activity']);
		else echo "<p>There was an unexpected error attempting to retrieve this log.</p>\n";
		echo "<p><br />\n";
		echo "  " . $form->input('button', 'logs', null, null, 'Do it again', 'btn btn-info', null, null, null, null, array('onClick'=>'document.location.href = "/housekeeping/run";')) . "\n";
		echo "  " . $form->input('button', 'logs', null, null, 'See all logs', 'btn btn-link', null, null, null, null, array('onClick'=>'document.location.href = "/logs";')) . "\n";
		echo "</p>\n";
	}
	elseif ($action == 'source_code' && !$pageError) {
		echo "<div class='container'>\n";
		highlight_file($url);
		echo "</div>\n";
		echo "<br /><div>" . $form->input('cancel_and_return', 'cancel_button', null, null, 'Return', 'btn btn-info') . "</div>\n";
	}
	elseif (!$pageError) {

		echo "<p>Although housekeeping is intended to be run via cron job, you can run the housekeeping bot manually (which will update the logs as if an actual cron job had been run).</p>";

		echo $form->start('housekeepingForm');

		/* Status */			echo $form->row('uneditable_static', 'bot_status', $botStatus, false, "Robot is");
		/* Previous Cron */		echo $form->row('uneditable_static', 'previous_cron', $previousCron, false, "Previous cron connection");
		/* Previous Manual */	if ($previousManual != 'Never') echo $form->row('uneditable_static', 'previous_manual', $previousManual, false, "Previous manual connection");

		$frequencies = $directory_manager->read(__DIR__ . '/../../../../../housekeeping/autoload', false, true, false);
		foreach ($frequencies as $frequency) {
			$frequency = substr($frequency, strrpos($frequency, '/'));
			$frequency = substr($frequency, strrpos($frequency, '\\') + 1);

			$tasks = $directory_manager->read(__DIR__ . '/../../../../../housekeeping/autoload/' . $frequency);
			if (count($tasks)) {
				echo $form->rowStart($frequency . '_tasks', ucwords($frequency) . " tasks");
				echo "<ul class='housekeepingTasks'>\n";
				for ($counter = 0; $counter < count($tasks); $counter++) {
					$task = str_replace('.php', '', substr($tasks[$counter], strrpos($tasks[$counter], '/')));
					$task = substr($task, strrpos($task, '\\') + 1);
					/* Task */			echo "<li><a href='/housekeeping/source_code/folder=" . $frequency . "|script=" . $task . "'>" . ucWords(str_replace('_', ' ', $task)) . "</a></li>\n";
				}
				echo "</ul>\n";
				echo $form->rowEnd();
			}
		}

		echo $form->end();

		echo $form->input('button', 'run_button', null, null, 'Run now', 'btn btn-info', null, null, null, null, array('onClick'=>'document.location.href = "/housekeeping/run";')) . "\n";

	}
	else {
		echo "<h2>Error</h2>\n\n";
		echo $pageError;
	}
	
	include 'includes/views/desktop/shared/page_bottom.php';
?>
