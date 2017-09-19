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

	// load third-party JS
		if (count(@$tl->frontEndLibraries)) {
			foreach ($tl->frontEndLibraries as $key =>$value) {
				if (@$value['local_js_path'] || @$value['remote_js_path']) {
					echo "  <!-- " . $key . (@$value['version'] ? " v." . $value['version'] : false) . " -->\n";
					if (@$value['remote_js_path']) echo "    <script type='text/javascript' src='" . $value['remote_js_path'] . "'></script>\n";
					elseif (@$value['local_js_path']) echo "    <script type='text/javascript' src='/libraries/" . $value['local_js_path'] . "'></script>\n";
					echo "\n";
				}
			}
		}
				
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
		if ($tl->page['javascript']) {
			echo "  <!-- Page-specific JS --><script type='text/javascript'>\n";
			echo "    //<![CDATA[\n\n";
			echo $tl->page['javascript'] . "\n";
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
			echo "  <!-- AJAX --><script src='/resources/js/ajax_callbacks/" . $tl->page['template'] . ".js'></script>\n";
			echo "  <!-- AJAX --><script type='text/javascript'>\n";
			echo "    //<![CDATA[\n\n";
			echo "      function callAjax(dataToSend) {\n";
			echo "        $.ajax({\n";
			echo "          type: 'POST',\n";
			echo "          url: 'includes/controllers/ajax/" . $tl->page['template'] . ".php',\n";
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
