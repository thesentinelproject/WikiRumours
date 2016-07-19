<?php

	// page content
		$otherLanguages = retrieveContent(array('slug'=>$cms[0]['slug'], $tablePrefix . 'cms.pseudonym_id'=>$cms[0]['pseudonym_id']), null, "cms_id != '" . $cms[0]['cms_id'] . "'");

		echo "<div class='tab-content'>\n";
		echo "  <div class='tab-pane active' id='" . $cms[0]['language_id'] . "'>\n";
		echo "    " . $cms[0]['content'] . "\n";
	  	echo "  </div>\n";
		for ($counter = 0; $counter < count($otherLanguages); $counter++) {
			echo "  <div class='tab-pane' id='" . $otherLanguages[$counter]['language_id'] . "'>\n";
			echo "    " . $otherLanguages[$counter]['content'] . "\n";
		  	echo "  </div>\n";
		}
	  	echo "</div>\n";

		if (count($otherLanguages) || $logged_in['can_edit_content']) {
			echo "<div id='cmsPageBottomNav' class='row'>\n";
			echo "  <div class='col-lg-11 col-md-11 col-sm-10 col-xs-9'>\n";
			if (count($otherLanguages)) {
				echo "    <ul class='nav nav-pills mutedPills'>\n";
				echo "      <li class='active'><a href='#" . $cms[0]['language_id'] . "' data-toggle='tab'>" . $cms[0]['language'] . "</a></li>\n";
				for ($counter = 0; $counter < count($otherLanguages); $counter++) {
					echo "      <li><a href='#" . $otherLanguages[$counter]['language_id'] . "' data-toggle='tab'>" . $otherLanguages[$counter]['native'] . "</a></li>\n";
				}
				echo "    </ul>\n";
			}
			echo "  </div>\n";
			echo "  <div class='col-lg-1 col-md-1 col-sm-2 col-xs-3 text-right'>\n";
			if ($logged_in['can_edit_content']) echo "    <a href='/admin_content/" . urlencode("screen=edit|id=" . $cms[0]['cms_id']) . "' class='btn btn-link'>Edit</a>\n";
			echo "  </div>\n";
			echo "</div>\n";
		}

?>
