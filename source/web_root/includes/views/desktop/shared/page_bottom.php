
	<!-- PAGE CONTENT ENDS -->

<?php

	if (!$hideSiteChrome) {
		
		echo "        </div>\n";
		echo "        <div id='siteNav' class='col-xs-12 col-sm-3 col-sm-pull-9 col-md-3 col-md-pull-9'>\n";
		echo "          <div class='visible-xs'><hr></div>\n";
		echo "          <div id='siteNavLiner'>\n";
		
		if ($logged_in) {
			echo "            <div id='salutation'>\n";
			echo "              Welcome, <strong><a href='/profile/" . $logged_in['username'] . "'>" . $logged_in['full_name'] . "</a></strong>\n";
			echo "            </div><!-- salutation -->\n";
			if ($logged_in['rumours_assigned'] > 0) {
				echo "            <div id='rumourAlert'>\n";
				echo "              <span class='label label-danger pull-right'><a href='/my_rumours' class='inverse'>" . floatval($logged_in['rumours_assigned']) . "</a></span>\n";
				echo "              <a href='/my_rumours'>Rumours requiring your attention</a>\n";
				echo "            </div><!-- rumourAlert -->\n";
				
			}
		}
		echo "            <div class='siteNavItem'><a href='/search_results/report%3Drecent'>Recent rumours</a></div>\n";
		echo "            <div class='siteNavItem'><a href='/search_results/report%3Dcommon'>Most common rumours</a></div>\n";
		echo "            <div class='siteNavItem'>" . $form->input('button', 'add_rumour', null, false, 'Report a Rumour', 'btn btn-info btn-block', null, null, null, null, array('onClick'=>'document.location.href="/rumour_add"; return false;')) . "</div>\n";
		// Search
			echo "            <div id='siteNavSearch'>\n";
			echo "              " . $form->start('searchForm', '', 'post') . "\n";
			echo "                <div class='form-group'>" . $form->input('search', 'search_keywords', @$keywords, false, null, 'form-control') . "</div>\n";
			echo "                <div class='siteNavItemSearch'><a href='javascript:void(0)' onClick='return false' id='advancedSearchButton' data-toggle='collapse' data-target='#siteNavSearchAdvancedToggle'>Advanced Search</a></div>\n";
		// Advanced search
				echo "                <div id='siteNavSearchAdvancedToggle' class='collapse";
				if (@$filters[$tablePrefix . 'rumours.country'] || @$filters['priority'] || @$filters['status'] || @$filters['tag_id']) echo " in";
				echo "'>\n";
				echo "                  <div id='siteNavSearchAdvanced'>\n";
				/* Country */	echo "                    <div class='form-group'>" . $form->input('country', 'search_country', @$filters[$tablePrefix . 'rumours.country'], false, 'All countries', 'form-control') . "</div>\n";
				/* Priority */	echo "                    <div class='form-group'>" . $form->input('select', 'search_priority', @$filters['priority'], false, 'All priorities', 'form-control', $priorityLevels) . "</div>\n";
				/* Status */	echo "                    <div class='form-group'>" . $form->input('select', 'search_status', @$filters['status'], false, 'All statuses', 'form-control', $rumourStatuses) . "</div>\n";
				/* Tag */		$allTags = array();
								$result = retrieveFromDb('tags', null, null, null, null, null, 'tag ASC');
								for ($counter = 0; $counter < count($result); $counter++) {
									$allTags[$result[$counter]['tag_id']] = $result[$counter]['tag'];
								}
								echo "                    <div class='form-group'>" . $form->input('select', 'search_tag', @$filters['tag_id'], false, 'All tags', 'form-control', $allTags) . "</div>\n";
				/* Submit */	echo "                    <div class='form-group'>" . $form->input('submit', 'search', 'Search', null, null, 'btn btn-default btn-block') . "</div>\n";
				echo "                  </div><!-- siteNavSearchAdvanced -->\n";
				echo "                </div><!-- siteNavSearchAdvancedToggle -->\n";
			echo "              " . $form->end() . "<!-- searchForm -->\n";
			echo "            </div><!-- siteNavSearch -->\n";
		// API
			echo "            <div>\n";
			echo "              <span class='pull-left transluscent'><span class='glyphicon glyphicon-new-window'></span>&nbsp;&nbsp;</span>\n";
			echo "              <div class='siteNavItemLabel'><a href='/explore_api'>Explore the API</a></div>\n";
			echo "            </div>\n";
		// About
			echo "            <div>\n";
			echo "              <span class='pull-left transluscent'><span class='glyphicon glyphicon-exclamation-sign'></span>&nbsp;&nbsp;</span>\n";
			echo "              <div class='siteNavItemLabel'><a href='/about'>About WikiRumours</a></div>\n";
			echo "            </div>\n";
		// Help
			echo "            <div>\n";
			echo "              <span class='pull-left transluscent'><span class='glyphicon glyphicon-question-sign'></span>&nbsp;&nbsp;</span>\n";
			echo "              <div class='siteNavItemLabel'><a href='/help'>Help</a></div>\n";
			echo "            </div>\n";
		// Contact
			echo "            <div>\n";
			echo "              <span class='pull-left transluscent'><span class='glyphicon glyphicon-envelope'></span>&nbsp;&nbsp;</span>\n";
			echo "              <div class='siteNavItemLabel'><a href='/contact'>Contact us</a></div>\n";
			echo "            </div>\n";
			
		echo "          </div><!-- siteNavLiner -->\n";
		
		echo "        </div><!-- siteNav -->\n";
		echo "      </div><!-- pageContent-->\n\n";
		echo "    </div>\n";
		
		// footer
			echo "    <div id='footer'>\n";
			echo "      <div class='container footerContainer'>\n";
			echo "        <div id='footerNav'>\n";
			echo "          <div class='row'>\n";
			echo "            <div class='col-xs-12 col-md-offset-3 col-md-9'>\n";
			echo "              <ul class='list-inline fixInlineList'>\n";
			echo "                <li><a href='/'>" . $systemPreferences['appName'] . "</a></li>\n";
			if ($redirectForMobile) echo "                <li><a href='http://m." . trim($environmentals['domain'], '/') . "'>Mobile Site</a></li>\n";
			echo retrieveContentBlock('footer nav');
			echo "              </ul>\n";
			echo "            </div>\n";
			echo "          </div>\n";
			echo "        </div><!-- footerNav -->\n";
			echo "      </div>\n\n";
			echo "    </div><!-- footer -->\n";
			
			
		// end page container
			echo "  </div>\n\n";
			
	}

	// load jQuery
		if ($devMode) echo "  <!-- jQuery (fallback) --><script src='/libraries/jquery_(fallback)/jquery_v1-9.js'></script>\n";
		else echo "  <!-- jQuery --><script src='http://code.jquery.com/jquery-latest.js'></script>\n";
				
	// load Bootstrap JS
		echo "  <!-- Bootstrap --><script src='/libraries/bootstrap/3-0-0/js/bootstrap.min.js'></script>\n";

	// load Bootstrap Switch JS
		echo "  <!-- Bootstrap Switch --><script src='/libraries/bootstrap-switch-master/static/js/bootstrap-switch.min.js'></script>\n";
		
	// load Select2 JS
		echo "  <!-- Select2 --><script src='/libraries/select2/select2-release-3.2/select2.js'></script>\n";
		
	// load Google Maps
		echo "  <!-- Google Maps API --><script type='text/javascript' src='http://maps.google.com/maps/api/js?sensor=false'></script>\n";
		
	// load Google AJAX API and Google charts package
		echo "  <!-- Google Charts --><script type='text/javascript' src='https://www.google.com/jsapi'></script>\n";
		echo "    <script type='text/javascript'>\n";
		echo "      // Load the Visualization API and the piechart package.\n";
		echo "        google.load('visualization', '1.0', {'packages':['corechart']});\n\n";
		echo "    </script>\n";
				
	// load Tidal Lock JS
		if ($handle = opendir('libraries/tidal_lock/js/.')) {
			while (false !== ($file = readdir($handle))) {
				if (substr_count($file, ".js") > 0) echo "  <!-- Tidal Lock --><script src='/libraries/tidal_lock/js/" . $file . "'></script>\n";
			}
			closedir($handle);
		}

	// load other JS
		if (file_exists('resources/js/' . $templateName . '.js')) echo "  <script src='/resources/js/" . $templateName . ".js'></script>\n";
		
		if ($handle = opendir('resources/js/shared/.')) {
			while (false !== ($file = readdir($handle))) {
				if (substr_count($file, ".js") > 0) echo "  <script src='/resources/js/shared/" . $file . "'></script>\n";
			}
			closedir($handle);
		}
		
	// load page-specific JS
		if ($pageJavaScript) {
			echo "  <!-- Page-specific JS --><script type='text/javascript'>\n";
			echo "    //<![CDATA[\n\n";
			echo $pageJavaScript . "\n";
			echo "    //]]>\n";
			echo "  </script>\n";
		}
		
	// load AJAX
		if (file_exists('includes/controllers/ajax/' . $templateName . '.php') && file_exists('resources/js/ajax_callbacks/' . $templateName . '.js')) {
			echo "  <!-- AJAX --><script src='/resources/js/ajax_callbacks/" . $templateName . ".js'></script>\n";
			echo "  <!-- AJAX --><script type='text/javascript'>\n";
			echo "    //<![CDATA[\n\n";
			echo "      function callAjax(dataToSend) {\n";
			echo "        $.ajax({\n";
			echo "          type: 'POST',\n";
			echo "          url: 'includes/controllers/ajax/" . $templateName . ".php',\n";
			echo "          data: dataToSend,\n";
			echo "          success: function(data) {\n";
			echo "            ajaxCallback(data, '');\n";
			echo "          },\n";
			echo "          error: function(errorData) {\n";
			echo "            ajaxCallback('', errorData);\n";
			echo "          }\n";
			echo "	      });\n";
			echo "      }\n";	
			echo "    //]]>\n";
			echo "  </script>\n";
		}
	
	echo "</body>\n";
	echo "</html>\n";

	$dbConnection->close();
?>
