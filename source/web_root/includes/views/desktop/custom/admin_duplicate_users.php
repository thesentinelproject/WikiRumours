<?php

	echo "<h2>" . $tl->page['title'] . "</h2>\n";

	echo $duplicates->html;
	$pageJavaScript .= $duplicates->js;

?>