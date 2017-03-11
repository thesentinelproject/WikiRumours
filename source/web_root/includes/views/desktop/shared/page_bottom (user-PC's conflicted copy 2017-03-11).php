<?php

	if (!@$tl->page['hide_page_chrome']) {
		
		echo "        <!-- PAGE CONTENT ENDS -->\n\n";
		
		echo "        </div>\n";

		include 'side_nav.php';

		echo "      </div><!-- pageContent-->\n\n";
		echo "    </div>\n";
		
		// footer
			echo "    <div id='footer'>\n";
			echo "      <div class='container footerContainer'>\n";
			echo "        <div id='footerNav'>\n";
			echo "          <div class='row'>\n";
			echo "            <div class='col-xs-12 col-md-offset-3 col-md-9'>\n";
			echo "              <ul class='list-inline fixInlineList'>\n";
			echo "                <li><a href='/'>" . $tl->settings['Name of this application'] . "</a></li>\n";
			if ($tl->settings['Redirect for mobile']) echo "              <li><a href='http://m." . trim($tl->page['domain'], '/') . "'>Mobile Site</a></li>\n";
			if ($tl->settings['Redirect for tablet']) echo "              <li><a href='http://t." . trim($tl->page['domain'], '/') . "'>Tablet Site</a></li>\n";
			displayCmsBlock(['public_id'=>"footer nav"]);
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
		echo "  <!-- jQuery --><script src='/libraries/jquery/jquery_v1-11-1.js'></script>\n";
				
	// load Bootstrap JS
		echo "  <!-- Bootstrap --><script src='/libraries/bootstrap/bootstrap-3.3.4-dist/js/bootstrap.min.js'></script>\n";

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
		echo "  <!-- Select2 --><script src='/libraries/select2/4-0-2-rc-1/dist/js/select2.min.js'></script>\n";
//		echo "  <!-- Select2 --><script src='/libraries/select2/select2_4-0/dist/js/select2.full.min'></script>\n";
		
	// load Dropzone
		echo "  <!-- Dropzone --><script src='/libraries/dropzone/dropzone_3-8-4/downloads/dropzone.js'></script>\n";

	// load Moment.js
		echo "  <!-- Moment.js -->\n";
		echo "    <script src='/libraries/moment-js/moment.min.js'></script>\n";
		echo "    <script src='/libraries/moment-js/moment-timezone-with-data.min.js'></script>\n";

	// load Google Maps Visualization library
//		if ($file_manager->doesUrlExist('http://maps.google.com/maps/api/js?sensor=false')) echo "  <!-- Google Maps API --><script type='text/javascript' src='http://maps.google.com/maps/api/js?sensor=false'></script>\n";
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
		if (file_exists('resources/js/custom/' . $tl->page['template'] . '.js')) echo "  <script src='/resources/js/custom/" . $tl->page['template'] . ".js'></script>\n";
		elseif (file_exists('resources/js/default/' . $tl->page['template'] . '.js')) echo "  <script src='/resources/js/default/" . $tl->page['template'] . ".js'></script>\n";
		
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
		if ($tl->page['console'] && $logged_in['is_tester'] && $tl->settings['Enable console for testers']) {
			echo "  <!-- Activate console --><script type='text/javascript'>\n";
			echo "    //<![CDATA[\n";
			echo "      var console = " . '"' . "<h4><span class='label label-default'>CONSOLE</span></h4>" . '"' . ";\n";
			echo "      console += " . '"' . addSlashes(str_replace('<br /><br />', '<br />', preg_replace('/(\r\n|\n|\r)/','<br />', nl2br($tl->page['console'])))) . '"' . ";\n";
			echo "      document.getElementById('console').innerHTML = console;\n";
			echo "    //]]>\n";
			echo "    $('#console').collapse('show');\n";
			echo "  </script>\n";
		}

	// Hide environment
		if (@$tl->settings['Display environment warning'] && (@$currentDatabase == 'dev' || @$currentDatabase == 'staging')) {
			echo "  <!-- Hide environment --><script type='text/javascript'>\n";
			echo "    //<![CDATA[\n";
			echo "    $('#environmentWarning').collapse('hide');\n";
			echo "    //]]>\n";
			echo "  </script>\n";
		}

	// load AJAX
		if (file_exists('includes/controllers/ajax/' . $tl->page['template'] . '.php') && file_exists('resources/js/ajax_callbacks/' . $tl->page['template'] . '.js')) {
			echo "  <!-- AJAX --><script src='/resources/js/ajax_callbacks/" . $tl->page['template'] . ".js?rand=" . rand(10000, 99999) . "'></script>\n";
			echo "  <!-- AJAX --><script type='text/javascript'>\n";
			echo "    //<![CDATA[\n\n";
			echo "      function callAjax(dataToSend) {\n";
			echo "        $.ajax({\n";
			echo "          type: 'POST',\n";
			echo "          url: '/includes/controllers/ajax/" . $tl->page['template'] . ".php',\n";
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

?>
