<?php
	include 'includes/views/desktop/shared/page_top.php';

	echo "<h2>Housekeeping</h2>\n";
	
	if (!$pageError && $areYouSure == 'run') {
		include 'housekeeping.php';
		$output = retrieveFromDb('logs', array('log_id'=>$logID), null, null, null, null, null, 1);
		if ($output[0]['activity']) echo str_replace('|', '<br />', $output[0]['activity']);
		else echo "<p>There was an unexpected error attempting to retrieve this log.</p>\n";
		echo "<p><br />\n";
		echo "  " . $form->input('button', 'logs', null, null, 'Do it again', 'btn btn-info', null, null, null, null, array('onClick'=>'document.location.href = "/housekeeping/run";')) . "\n";
		echo "  " . $form->input('button', 'logs', null, null, 'See all logs', 'btn btn-link', null, null, null, null, array('onClick'=>'document.location.href = "/logs";')) . "\n";
		echo "  " . $form->input('cancel_and_return', 'cancel', null, null, 'Return', 'btn btn-link') . "\n";
		echo "</p>\n";
	}
	elseif (!$pageError) {
		echo "<div class='row'>\n";
		echo "  <div class='col-md-3'>Bot status</div>\n";
		echo "  <div class='col-md-3'>&nbsp;" . $botStatus . "</div>\n";
		echo "  <div class='col-md-3'>Last connection</div>\n";
		echo "  <div class='col-md-3'>";
		if (!is_null(@$numberOfMinutesSincePreviousCronConnection)) echo floatval($numberOfMinutesSincePreviousCronConnection) . " minute(s) ago at<br />" . date('Y-m-d H:i:s', strtotime($previousCronConnection[0]['connected_on']));
		else echo "N / A";
		echo "  </div>\n";
		echo "</div>\n";
		
		echo "<p><br />Although housekeeping is intended to be run via cron job, you can run the housekeeping bot manually (which will update the logs as if an actual cron job had been run).</p>";
		echo "<p>\n";
		echo "  " . $form->input('button', 'logs', null, null, 'Run it now', 'btn btn-info', null, null, null, null, array('onClick'=>'document.location.href = "/housekeeping/run";')) . "\n";
		echo "  " . $form->input('button', 'logs', null, null, 'See all logs', 'btn btn-link', null, null, null, null, array('onClick'=>'document.location.href = "/logs";')) . "\n";
		echo "  " . $form->input('cancel_and_return', 'cancel', null, null, 'Return', 'btn btn-link') . "\n";
		echo "</p>\n";
	}
	else {
		echo "<h2>Error</h2>\n\n";
		echo $pageError;
	}
	
	include 'includes/views/desktop/shared/page_bottom.php';
?>
