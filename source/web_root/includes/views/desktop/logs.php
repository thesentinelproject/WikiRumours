<?php
	include 'includes/views/desktop/shared/page_top.php';

	echo "  <h2>Logs</h2>\n\n";
	if (count($logs) < 1) echo "  <p>None.</p>\n";
	else {
		echo "  <table class='table table-condensed table-hover'>\n";
		echo "  <thead>\n";
		echo "  <tr>\n";
		echo "  <th>Date</th>\n";
		echo "  <th>Activity</th>\n";
		echo "  </tr>\n";
		echo "  </thead>\n";
		echo "  <tbody>\n";
		for ($counter = 0; $counter < count($logs); $counter++) {
			echo "  <tr>\n";
			echo "  <td>" . str_replace(' ', '&nbsp;', date('M j, Y, \a\t g:i:s A', strtotime($logs[$counter]['connected_on']))) . "</td>\n";
			echo "  <td>" . str_replace('|', '<br />', $logs[$counter]['activity']);
			if ($logs[$counter]['error_message']) echo "<br />(" . $logs[$counter]['error_message'] . ")";
			echo "</td>\n";
			echo "  </tr>\n";
		}
		echo "  </tbody>\n";
		echo "  </table>\n\n";
		
		if ($numberOfPages > 1) {
			echo $form->paginate($filters['page'], $numberOfPages, '/logs/page=#');
		}
	}
	
	include 'includes/views/desktop/shared/page_bottom.php';
?>