<?php 
		
	if ($pageStatus == 'reset_successful') {
		echo "<h2>Email Address Updated</h2>\n";
		echo "Your email address has been confirmed.";
	}
	else {
		echo "<h2>Error</h2>\n";
		if (!$pageError) echo "An unspecified error has occurred.\n";
	}
	
?>