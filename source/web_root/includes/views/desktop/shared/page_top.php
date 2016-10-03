<?php

	// specify XHTML compliance and begin document header
		echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>\n";
		echo "<html lang='en' xmlns='http://www.w3.org/1999/xhtml'>\n";

		echo "<head>\n";

	// refresh
		if (@$refreshInSeconds) echo "<meta http-equiv='refresh' content='" . $refreshInSeconds . "' />\n";
	
	// discourage caching
		echo "  <meta http-equiv='Pragma' content='no-cache'>\n";
		echo "  <meta http-equiv='Expires' content='-1'>\n";
		echo "  <meta http-equiv='CACHE-CONTROL' content='NO-CACHE'>\n";

	// define character set
		echo "  <meta charset='UTF-8'>\n";
		echo "  <meta http-equiv='content-type' content='text/html; charset=UTF-8' />\n";
		
	// set IE compatibility
		echo "  <meta http-equiv='X-UA-Compatible' content='IE=9; IE=8;' />\n";

	// define viewport
		echo "  <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
		
	// define description for search engine indexing
		echo "  <meta name='description' content=" . '"' . $tl->settings['Describe this application'] . '"' . ">\n";

	// Facebook sharing tags
		if (@$tl->page['title']) echo "  <meta property='og:title' content=" . '"' . $tl->page['title'] . '"' . " />\n";
		if (@$tl->page['description']) echo "  <meta property='og:description' content=" . '"' . $tl->page['description'] . '"' . " />\n";
		if (@$pageImage) echo "  <meta property='og:image' content='" . $pageImage . "' />\n";

	// Twitter sharing tags
		if (@$tl->page['title'] || @$tl->page['description'] || @$pageImage) {
			echo "  <meta name='twitter:card' content='summary'>\n";
			if (@$tl->page['title']) echo "  <meta property='twitter:title' content=" . '"' . $tl->page['title'] . '"' . " />\n";
			if (@$tl->page['description']) echo "  <meta property='twitter:description' content=" . '"' . $tl->page['description'] . '"' . " />\n";
			if (@$pageImage) echo "  <meta property='twitter:image' content='" . $pageImage . "' />\n";
		}
		
	// load Bootstrap stylesheet
		echo "  <!-- Bootstrap --><link href='/libraries/bootstrap/bootstrap-3.3.4-dist/css/bootstrap.min.css' rel='stylesheet' media='screen' type='text/css' />\n";

	// load Bootstrap Switch stylesheet
		echo "  <!-- Bootstrap Switch --><link href='/libraries/bootstrap-switch/bootstrap_switch_3-0/dist/css/bootstrap3/bootstrap-switch.min.css' rel='stylesheet' />\n";
		
	// load Bootstrap datetimepicker stylesheet
		echo "  <!-- Bootstrap Datepicker --><link href='/libraries/bootstrap-datetimepicker-master/css/bootstrap-datetimepicker.min.css' rel='stylesheet' media='screen' />\n";

	// load Dropzone
		echo "  <!-- Dropzone --><link href='/libraries/dropzone/dropzone_3-8-4/downloads/css/dropzone.css?rand=" . rand(10000, 99999) . "' rel='stylesheet' />\n";

	// load Font Awesome stylesheet
		echo "  <!-- Font Awesome --><link href='/libraries/font_awesome/font-awesome_4-3-0/font-awesome.min.css' rel='stylesheet' media='screen' type='text/css' />\n";

	// load Select2 stylesheet
		echo "  <!-- Select2 --><link href='/libraries/select2/4-0-2-rc-1/dist/css/select2.min.css' rel='stylesheet' />\n";
//		echo "  <!-- Select2 --><link href='/libraries/select2/select2_4-0/dist/css/select2.min' rel='stylesheet' />\n";

	// load TidalLock stylesheets
		if ($handle = opendir('libraries/tidal_lock/css/.')) {
			while (false !== ($file = readdir($handle))) {
				if (substr_count($file, ".css") > 0) echo "  <!-- Tidal Lock --><link href='/libraries/tidal_lock/css/" . $file . "' rel='stylesheet' media='screen' type='text/css' />\n";
			}
			closedir($handle);
		}

	// load Google Material Design icons
		$url = "https://fonts.googleapis.com/icon?family=Material+Icons";
		if ($file_manager->doesUrlExist($url)) echo "  <!-- Google Material Design Icons --><link rel='stylesheet' type='text/css' href='" . $url . "'>\n";
		else {
			echo "  <!-- Google Material Design Icons --><link rel='stylesheet' type='text/css' href='/libraries/material_design_icons/material_design_icons.css'>\n";
			$loadMaterialDesignLocally = true;
		}

	// load base stylesheets
		if ($handle = opendir('resources/css/desktop/autoload/.')) {
			while (false !== ($file = readdir($handle))) {
				if (substr_count($file, ".css") > 0) echo "  <link href='/resources/css/desktop/autoload/" . $file . "' rel='stylesheet' media='screen' type='text/css' />\n";
			}
			closedir($handle);
		}
		if (file_exists("resources/css/desktop/" . $tl->page['template'] . ".css") > 0) echo "  <link href='/resources/css/desktop/" . $tl->page['template'] . ".css' rel='stylesheet' media='screen' type='text/css' />\n";
		
	// load page-specific CSS
		if ($pageCss) {
			echo "  <style type='text/css'>\n";
			echo $pageCss . "\n";
			echo "  </style>\n";
		}

	// specify favicon
		if (file_exists('resources/img/icons/favicon.ico')) echo "  <link href='/resources/img/icons/favicon.ico' rel='SHORTCUT ICON' />\n";
		
	// specify canonical, if provided
		if ($tl->page['canonical_url']) echo "  <link rel='canonical' href='" . $tl->page['canonical_url'] . "' />\n\n";

	// load Google Analytics
		if ($currentDatabase == 'production') {
			if (@$tl->page['domain_alias']['google_analytics_id']) echo $analytics->insertGoogleAnalytics($tl->page['domain_alias']['google_analytics_id']); 
			elseif (@$tl->settings['Google Analytics ID']) echo $analytics->insertGoogleAnalytics($tl->settings['Google Analytics ID']); 
		}

	// specify page title
		echo "  <title>";
		if ($tl->page['title']) echo $tl->page['title'] . " - ";
		if ($tl->page['section']) echo $tl->page['section'] . " - ";
		echo htmlspecialchars($tl->settings['Name of this application'], ENT_QUOTES);
		echo "</title>\n";

		echo "</head>\n\n";

	// start body
		echo "<body onLoad='";
		if ($pageLoadEvents) echo $pageLoadEvents;
		echo "'>\n\n";

	if (!$tl->page['hide_page_chrome']) {
		
		// start header content
			echo "  <div id='pageContainer'>\n\n";
				
		// environment
			if (@$tl->settings['Display environment warning'] && (@$currentDatabase == 'dev' || @$currentDatabase == 'staging')) {
				echo "  <div id='environmentWarning' class='collapse in'><center>" . strtoupper($currentDatabase) . "</center></div>\n";
			}

		// maintenance mode
			if (@$tl->settings['Maintenance Mode'] == 'On' && @$logged_in['is_administrator']) {
				echo "  <div id='maintenanceWarning'><center>The website is currently in maintenance mode and is disabled for all users except administrators.</center></div>\n";
			}

		// console
			if (@$logged_in['is_tester'] && @$tl->settings['Enable console for testers']) {
				echo "  <div id='console' class='collapse'>\n";
				echo "  </div><!-- console -->\n";
			}

		echo "    <div class='container'>\n";
		echo "      <div id='header' class='row'>\n";

		// logo
			echo "        <div id='logo' class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
			echo "          <h1 class='hidden'>" . htmlspecialchars($tl->settings['Name of this application'], ENT_QUOTES) . "</h1>\n";
			echo "          <a href='/'><img src='" . (@$tl->page['domain_alias']['destination_url'] && file_exists($tl->page['domain_alias']['destination_url']) ? '/' . $tl->page['domain_alias']['destination_url'] : "/resources/img/logo.png") . "' border='0' class='img-responsive' alt='" . htmlspecialchars($tl->settings['Name of this application'], ENT_QUOTES) . "' /></a>\n";
			echo "        </div><!-- logo -->\n\n";
	
		// header
			echo "        <div id='userNav' class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n";
			
			// non-mobile experience
				echo "          <ul id='userNavNonMobile' class='nav nav-pills pull-right hidden-xs'>\n";
				include __DIR__ . "/user_nav.php";
				echo "          </ul>\n";
			// mobile experience
				echo "          <ul id='userNavMobile' class='visible-xs hideBullets'>\n";
				include __DIR__ . "/user_nav.php";
				echo "            <hr />\n";
				echo "          </ul>\n";
					
			echo "        </div><!-- userNav -->\n";
			echo "      </div><!-- header -->\n";
			echo "    </div>\n";
			
			echo "    <div class='container'>\n";
			
			// begin page content
				echo "      <div id='pageContent' class='row'>\n";
				echo "        <div class='col-xs-12 col-sm-9 col-sm-push-3 col-md-9 col-md-push-3'>\n";
				
		// success, warning or error message
			if (@$tl->page['error']) echo "        <div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $tl->page['error'] . "</div>\n";
			elseif (@$tl->page['warning']) echo "        <div class='alert alert-warning alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $tl->page['warning'] . "</div>\n";
			elseif (@$tl->page['success']) echo "        <div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $tl->page['success'] . "</div>\n";
				
			echo "        <!-- PAGE CONTENT BEGINS -->\n\n";

	}

?>
