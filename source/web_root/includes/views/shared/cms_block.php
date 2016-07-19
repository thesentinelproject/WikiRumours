<?php

			$cms = retrieveContent(array('slug'=>$slug, 'content_type'=>'b', $tablePrefix . 'cms.language_id'=>$operators->firstTrue(@$pseudonym['language_id'], @$systemPreferences['Default language']), $tablePrefix . 'cms.pseudonym_id'=>@$pseudonym['pseudonym_id']));
			if (!count($cms)) $cms = retrieveContent(array('slug'=>$slug, 'content_type'=>'b', $tablePrefix . 'cms.language_id'=>$operators->firstTrue(@$pseudonym['language_id'], @$systemPreferences['Default language']), $tablePrefix . 'cms.pseudonym_id'=>'0'));
			if (count($cms)) {

				$otherLanguages = retrieveContent(array('slug'=>$cms[0]['slug'], $tablePrefix . 'cms.pseudonym_id'=>$cms[0]['pseudonym_id']), null, "cms_id != '" . $cms[0]['cms_id'] . "'");

				if (count($otherLanguages)) {

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

					echo "<div id='cmsPageBottomNav'>\n";
					if (count($otherLanguages)) {
						echo "  <ul class='nav nav-pills mutedPills'>\n";
						echo "    <li class='active'><a href='#" . $cms[0]['language_id'] . "' data-toggle='tab'>" . $cms[0]['language'] . "</a></li>\n";
						for ($counter = 0; $counter < count($otherLanguages); $counter++) {
							echo "    <li><a href='#" . $otherLanguages[$counter]['language_id'] . "' data-toggle='tab'>" . $otherLanguages[$counter]['native'] . "</a></li>\n";
						}
						echo "  </ul>\n";
					}
					echo "</div>\n";

				}
				else echo $cms[0]['content'];

			}

?>