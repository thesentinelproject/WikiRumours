<?php
	$pageTitle = $cms[0]['title'];
	$pageCss = $cms[0]['content_css'];
	include 'includes/views/desktop/shared/page_top.php';
	echo $cms[0]['content'];
	if ($logged_in['can_edit_content']) echo "<div class='text-right'><a href='/content/" . $cms[0]['cms_id'] . "'>Edit</a></div>\n";
	$pageJavaScript = $cms[0]['content_js'];
	include 'includes/views/desktop/shared/page_bottom.php';
?>