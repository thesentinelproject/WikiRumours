<?php

	function displayCmsBlock($matching) {

		global $tablePrefix;
		global $tl;

		$cms = new cms_widget_TL();

		// make sure all matching keys are table-specific (and assume, if not, that the desired table is "cms")
			foreach ($matching as $key=>$value) {
				if (!substr_count($key, '.')) {
					unset($matching[$key]);
					$matching[$tablePrefix . 'cms.' . $key] = $value;
				}
			}

		// look for matching domain alias and language
			$result = $cms->retrieveContent(array_merge($matching, [$tablePrefix . 'cms.content_type'=>'b', $tablePrefix . 'cms.language_id'=>(@$tl->page['domain_alias']['language_id'] ? $tl->page['domain_alias']['language_id'] : @$tl->settings['Default language']), $tablePrefix . 'cms.domain_alias_id'=>@$tl->page['domain_alias']['cms_id']]));

			if (!count($result)) {
				// look for matching block, ideally with matching language, but no domain alias
					$result = $cms->retrieveContent(array_merge($matching, [$tablePrefix . 'cms.content_type'=>'b', $tablePrefix . 'cms.language_id'=>(@$tl->page['domain_alias']['language_id'] ? $tl->page['domain_alias']['language_id'] : @$tl->settings['Default language']), $tablePrefix . 'cms.domain_alias_id'=>'0']));
			}

			if (!count($result)) {
				// look for matching block with default site language, but no domain alias
					$result = $cms->retrieveContent(array_merge($matching, [$tablePrefix . 'cms.content_type'=>'b', $tablePrefix . 'cms.language_id'=>@$tl->settings['Default language'], $tablePrefix . 'cms.domain_alias_id'=>'0']));
			}

			if (!count($result)) {
				// look for matching block, with no language and no domain alias
					$result = $cms->retrieveContent(array_merge($matching, [$tablePrefix . 'cms.content_type'=>'b', $tablePrefix . 'cms.language_id'=>'', $tablePrefix . 'cms.domain_alias_id'=>'0']));
			}

			if (count($result)) {

				$otherLanguages = $cms->retrieveContent(array_merge($matching, [$tablePrefix . 'cms.public_id'=>$result[0]['public_id'], $tablePrefix . 'cms.domain_alias_id'=>$result[0]['domain_alias_id']]), $tablePrefix . "cms.cms_id != '" . $result[0]['cms_id'] . "'");

				if (!count($otherLanguages)) {
					$tl->page['css'] .= $result[0]['content_css'];
					echo $result[0]['content'];
					$tl->page['javascript'] .= $result[0]['content_js'];
				}
				else {

					echo "<div class='tab-content'>\n";
					echo "  <div class='tab-pane active' id='" . $result[0]['language_id'] . "'>\n";
					echo "    " . $result[0]['content'] . "\n";
				  	echo "  </div>\n";

					$tl->page['css'] .= $result[0]['content_css'];
					$tl->page['javascript'] .= $result[0]['content_js'];

					for ($counter = 0; $counter < count($otherLanguages); $counter++) {
						echo "  <div class='tab-pane' id='" . $otherLanguages[$counter]['language_id'] . "'>\n";
						echo "    " . $otherLanguages[$counter]['content'] . "\n";
					  	echo "  </div>\n";

						$tl->page['css'] .= $result[$counter]['content_css'];
						$tl->page['javascript'] .= $result[$counter]['content_js'];
					}
				  	echo "</div>\n";

					echo "<div id='cmsPageBottomNav'>\n";
					if (count($otherLanguages)) {
						echo "  <ul class='nav nav-pills mutedPills'>\n";
						echo "    <li class='active'><a href='#" . $result[0]['language_id'] . "' data-toggle='tab'>" . (@$result[0]['native_language'] ? $result[0]['native_language'] : $result[0]['language']) . "</a></li>\n";
						for ($counter = 0; $counter < count($otherLanguages); $counter++) {
							echo "    <li><a href='#" . $otherLanguages[$counter]['language_id'] . "' data-toggle='tab'>" . (@$otherLanguages[$counter]['native_language'] ? $otherLanguages[$counter]['native_language'] : $otherLanguages[$counter]['language']) . "</a></li>\n";
						}
						echo "  </ul>\n";
					}
					echo "</div>\n";

				}

			}

	}

?>
