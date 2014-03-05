<?php 
	include 'includes/views/desktop/shared/page_top.php';
		
	if ($pageSuccess == 'success') {
		echo "<h2>Email Address Updated</h2>\n";
		echo "Your email address has been confirmed.";
	}
	else {
		echo "<h2>Error</h2>\n";
		if ($pageError) echo $pageError;
		else echo "An unspecified error has occurred.\n";
	}
	
	include 'includes/views/desktop/shared/page_bottom.php';
?>