<?php 

	include 'includes/views/desktop/shared/page_top.php';
			
	echo "<div class='full'>\n";
	
	if ($confirmed) {
		echo "<h2>Registration Complete</h2>\n";
		echo "Your email address has been confirmed. To ensure security, please <a href='/login_register'>log in</a>.<br /><br /><br />&nbsp;\n\n";
	}
	else {
		echo "<h2>Unable to Confirm Registration</h2>\n";
		if ($pageError) echo $pageError;
		else echo "Please <a href='/contact'>let us know</a> so that we can help resolve this problem.";
	}
	
	echo "</div>\n";
	
	include 'includes/views/desktop/shared/page_bottom.php';
?>
