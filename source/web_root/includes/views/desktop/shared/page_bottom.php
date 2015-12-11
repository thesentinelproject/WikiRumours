<?php

	if (!@$hideSiteChrome) {
		
		echo "        <!-- PAGE CONTENT ENDS -->\n\n";
		
		echo "        </div>\n";
		echo "        <div id='siteNav' class='col-xs-12 col-sm-3 col-sm-pull-9 col-md-3 col-md-pull-9'>\n";
		echo "          <div class='visible-xs'><hr></div>\n";
		echo "          <div id='siteNavLiner'>\n";

		// side navigation
			if (@$logged_in) {
				echo "            <div id='salutation'>\n";
				echo "              Welcome, <strong><a href='/profile/" . $logged_in['username'] . "'>" . $logged_in['full_name'] . "</a></strong>\n";
				echo "            </div><!-- salutation -->\n";
				if (@$logged_in['rumours_assigned'] > 0) {
					echo "            <div id='rumourAlert'>\n";
					echo "              <span class='label label-danger pull-right'><a href='/my_rumours' class='inverse'>" . floatval($logged_in['rumours_assigned']) . "</a></span>\n";
					echo "              <a href='/my_rumours'>Rumours requiring your attention</a>\n";
					echo "            </div><!-- rumourAlert -->\n";
					
				}
			}
			echo "            <div class='siteNavItem'><a href='/search_results/report%3Drecent'>Recent rumours</a></div>\n";
			echo "            <div class='siteNavItem'><a href='/search_results/report%3Dcommon'>Most common rumours</a></div>\n";
			echo "            <div class='siteNavItem'><a href='/statistics'>Statistics</a></div>\n";
			echo "            <div class='siteNavItem'>" . $form->input('button', 'add_rumour', null, false, 'Report a Rumour', 'btn btn-info btn-block', null, null, null, null, array('onClick'=>'document.location.href="/rumour_add"; return false;')) . "</div>\n";
			echo "            " . $form->start('searchForm', '', 'post', 'form-horizontal') . "\n";
			// Search
				echo "              <div id='siteNavSearch' class='container-fluid'>\n";
				echo "                <div class='form-group'>" . $form->input('search', 'search_keywords', @$keywords, false, null, 'form-control') . "</div>\n";
				echo "                <div class='siteNavItemSearch form-group'><a href='javascript:void(0)' onClick='return false' id='advancedSearchButton' data-toggle='collapse' data-target='#siteNavSearchAdvancedToggle'>Advanced Search</a></div>\n";
			// Advanced search
				echo "                <div id='siteNavSearchAdvancedToggle' class='collapse";
				if (@$filters['country_id'] || @$filters['priority_id'] || @$filters['status_id'] || @$filters['tag_id']) echo " in";
				echo "'>\n";
				echo "                  <div id='siteNavSearchAdvanced'>\n";
				/* Country */	echo "                    <div class='form-group'>" . $form->input('country', 'search_country', @$filters['country_id'], false, 'All countries', 'form-control') . "</div>\n";
				/* Priority */	echo "                    <div class='form-group'>" . $form->input('select', 'search_priority', @$filters['priority_id'], false, 'All priorities', 'form-control', $rumourPriorities) . "</div>\n";
				/* Status */	echo "                    <div class='form-group'>" . $form->input('select', 'search_status', @$filters['status_id'], false, 'All statuses', 'form-control', $rumourStatuses) . "</div>\n";
				/* Tag */		echo "                    <div class='form-group'>" . $form->input('select', 'search_tag', @$filters['tag_id'], false, 'All tags', 'form-control', $rumourTags) . "</div>\n";
				/* Submit */	echo "                    <div class='form-group'>" . $form->input('submit', 'search', 'Search', null, null, 'btn btn-info btn-block') . "</div>\n";
				echo "                  </div><!-- siteNavSearchAdvanced -->\n";
				echo "                </div><!-- siteNavSearchAdvancedToggle -->\n";
				echo "              </div><!-- siteNavSearch -->\n";
			echo "            " . $form->end() . "<!-- searchForm -->\n";
			// API
				echo "            <div>\n";
				echo "              <span class='pull-left transluscent'><span class='glyphicon glyphicon-new-window'></span>&nbsp;&nbsp;</span>\n";
				echo "              <div class='siteNavItemLabel'><a href='/explore_api'>Explore the API</a></div>\n";
				echo "            </div>\n";
			// About
				echo "            <div>\n";
				echo "              <span class='pull-left transluscent'><span class='glyphicon glyphicon-exclamation-sign'></span>&nbsp;&nbsp;</span>\n";
				echo "              <div class='siteNavItemLabel'><a href='/about'>About " . htmlspecialchars($operators->firstTrue(@$pseudonym['name'], $systemPreferences['Name of this application']), ENT_QUOTES) . "</a></div>\n";
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
			echo "                <li><a href='/'>" . $operators->firstTrue(@$pseudonym['name'], $systemPreferences['Name of this application']) . "</a></li>\n";
			if ($systemPreferences['Redirect for mobile']) echo "              <li><a href='http://m." . trim($environmentals['domain'], '/') . "'>Mobile Site</a></li>\n";
			if ($systemPreferences['Redirect for tablet']) echo "              <li><a href='http://t." . trim($environmentals['domain'], '/') . "'>Tablet Site</a></li>\n";
			$slug = 'footer nav';
			include 'includes/views/shared/cms_block.php';
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
		if ($file_manager->doesUrlExist('http://code.jquery.com/jquery-latest.js')) echo "  <!-- jQuery --><script src='http://code.jquery.com/jquery-latest.js'></script>\n";
		else echo "  <!-- jQuery (fallback) --><script src='/libraries/jquery/jquery_v1-11-1.js'></script>\n";
				
	// load Bootstrap JS
		if ($file_manager->doesUrlExist('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js')) echo "  <!-- Bootstrap --><script src='//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js'></script>\n";
		else echo "  <!-- Bootstrap (fallback) --><script src='/libraries/bootstrap/bootstrap-3.3.4-dist/js/bootstrap.min.js'></script>\n";

	// load Bootstrap Switch JS
		echo "  <!-- Bootstrap Switch --><script src='/libraries/bootstrap-switch/bootstrap_switch_3-0/dist/js/bootstrap-switch.min.js'></script>\n";
		
	// load Bootstrap Datetimepicker JS
		echo "  <!-- Bootstrap Datetimepicker -->\n";
		echo "    <script type='text/javascript' src='/libraries/bootstrap-datetimepicker-master/js/bootstrap-datetimepicker.js' charset='UTF-8'></script>\n";
		echo "    <script type='text/javascript' src='/libraries/bootstrap-datetimepicker-master/js/locales/bootstrap-datetimepicker.fr.js' charset='UTF-8' /></script>\n";
		echo "    <script type='text/javascript'>\n";
		echo "      $('.form_datetime').datetimepicker({\n";
        echo "        weekStart: 1,\n";
        echo "        todayBtn:  1,\n";
		echo "        autoclose: 1,\n";
		echo "        todayHighlight: 1,\n";
		echo "        startView: 2,\n";
		echo "        forceParse: 0,\n";
        echo "        showMeridian: 1\n";
    	echo "      });\n";
		echo "    </script>\n";

	// load Select2 JS
		if ($file_manager->doesUrlExist('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js')) echo "  <!-- Select2 --><script src='//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js'></script>\n";
		else echo "  <!-- Select2 --><script src='/libraries/select2/select2_4-0/dist/js/select2.full.min'></script>\n";
		
	// load Moment.js
		echo "  <!-- Moment.js -->\n";
		echo "    <script src='/libraries/moment-js/moment.min.js'></script>\n";
		echo "    <script src='/libraries/moment-js/moment-timezone-with-data.min.js'></script>\n";

	// load Google Maps Visualization library
		if ($file_manager->doesUrlExist('https://maps.googleapis.com/maps/api/js?libraries=visualization&sensor=true_or_false')) echo "  <!-- Google Maps Visualization Library --><script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?libraries=visualization&sensor=true_or_false'></script>\n";

	// load Google AJAX API and Google charts package
		if ($file_manager->doesUrlExist('https://www.google.com/jsapi')) echo "  <!-- Google Charts --><script type='text/javascript' src='https://www.google.com/jsapi'></script>\n";

	// load Google Material Design icons
		if (@$loadMaterialDesignLocally) echo "  <!-- Google Material Design Icons --><script type='text/javascript' src='/libraries/material_design_icons/rendering.js'></script>\n";
				
	// load Tidal Lock JS
		if ($handle = opendir('libraries/tidal_lock/js/.')) {
			while (false !== ($file = readdir($handle))) {
				if (substr_count($file, ".js") > 0) echo "  <!-- Tidal Lock --><script src='/libraries/tidal_lock/js/" . $file . "'></script>\n";
			}
			closedir($handle);
		}

	// load other JS
		if (file_exists('resources/js/custom/' . $templateName . '.js')) echo "  <script src='/resources/js/custom/" . $templateName . ".js'></script>\n";
		elseif (file_exists('resources/js/default/' . $templateName . '.js')) echo "  <script src='/resources/js/default/" . $templateName . ".js'></script>\n";
		
		if ($handle = opendir('resources/js/autoload/.')) {
			while (false !== ($file = readdir($handle))) {
				if (substr_count($file, ".js") > 0) echo "  <script src='/resources/js/autoload/" . $file . "'></script>\n";
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
		
	// load console
		if ($console && $logged_in['is_tester'] && $systemPreferences['Enable console for testers']) {
			echo "  <!-- Activate console --><script type='text/javascript'>\n";
			echo "    //<![CDATA[\n";
			echo "      var console = " . '"' . "<h4><span class='label label-default'>CONSOLE</span></h4>" . '"' . ";\n";
			echo "      console += " . '"' . addSlashes(str_replace('<br /><br />', '<br />', preg_replace('/(\r\n|\n|\r)/','<br />', nl2br($console)))) . '"' . ";\n";
			echo "      document.getElementById('console').innerHTML = console;\n";
			echo "    //]]>\n";
			echo "    $('#console').collapse('show');\n";
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
