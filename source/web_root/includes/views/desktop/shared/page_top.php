<?php

	// specify XHTML compliance and begin document header
		echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>\n";
		echo "<html lang='en' xmlns='http://www.w3.org/1999/xhtml'>\n";

		echo "<head>\n";

	// refresh
		if (@$refreshInSeconds) echo "<meta http-equiv='refresh' content='" . $refreshInSeconds . "' />\n";
	
	// define character set
		echo "  <meta charset='UTF-8'>\n";
		echo "  <meta http-equiv='content-type' content='text/html; charset=UTF-8' />\n";
		
	// set IE compatibility
		echo "  <meta http-equiv='X-UA-Compatible' content='IE=9; IE=8;' />\n";

	// define viewport
		echo "  <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
		
	// define description for search engine indexing
		echo "  <meta name='description' content=" . '"' . $operators->firstTrue(@$pseudonym['description'], "Conflict mitigration through information") . '"' . ">\n";

	// Facebook sharing tags
		if (@$pageTitle) echo "  <meta property='og:title' content=" . '"' . $pageTitle . '"' . " />\n";
		if (@$pageDescription) echo "  <meta property='og:description' content=" . '"' . $pageDescription . '"' . " />\n";
		if (@$pageImage) echo "  <meta property='og:image' content='" . $pageImage . "' />\n";

	// Twitter sharing tags
		if (@$pageTitle || @$pageDescription || @$pageImage) {
			echo "  <meta name='twitter:card' content='summary'>\n";
			if (@$pageTitle) echo "  <meta property='twitter:title' content=" . '"' . $pageTitle . '"' . " />\n";
			if (@$pageDescription) echo "  <meta property='twitter:description' content=" . '"' . $pageDescription . '"' . " />\n";
			if (@$pageImage) echo "  <meta property='twitter:image' content='" . $pageImage . "' />\n";
		}
		
	// load Bootstrap stylesheet
		if ($file_manager->doesUrlExist('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css')) echo "  <!-- Bootstrap --><link href='//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css' rel='stylesheet' media='screen' type='text/css' />\n";
		else echo "  <!-- Bootstrap (fallback) --><link href='/libraries/bootstrap/bootstrap-3.3.4-dist/css/bootstrap.min.css' rel='stylesheet' media='screen' type='text/css' />\n";

	// load Bootstrap Switch stylesheet
		echo "  <!-- Bootstrap Switch --><link href='/libraries/bootstrap-switch/bootstrap_switch_3-0/dist/css/bootstrap3/bootstrap-switch.min.css' rel='stylesheet' />\n";
		
	// load Bootstrap datetimepicker stylesheet
		echo "  <!-- Bootstrap Datepicker --><link href='/libraries/bootstrap-datetimepicker-master/css/bootstrap-datetimepicker.min.css' rel='stylesheet' media='screen' />\n";

	// load Font Awesome stylesheet
		if ($file_manager->doesUrlExist('https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css')) echo "  <!-- Font Awesome --><link href='//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' rel='stylesheet' media='screen' type='text/css' />\n";
		else echo "  <!-- Font Awesome (fallback) --><link href='/libraries/font_awesome/font-awesome_4-3-0/font-awesome.min.css' rel='stylesheet' media='screen' type='text/css' />\n";

	// load Select2 stylesheet
		if ($file_manager->doesUrlExist('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css')) echo "  <!-- Select2 --><link href='//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css' rel='stylesheet' />\n";
		else echo "  <!-- Select2 --><link href='/libraries/select2/select2_4-0/dist/css/select2.min' rel='stylesheet' />\n";

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
		if (file_exists("resources/css/desktop/" . $templateName . ".css") > 0) echo "  <link href='/resources/css/desktop/" . $templateName . ".css' rel='stylesheet' media='screen' type='text/css' />\n";
		
	// load page-specific CSS
		if ($pageCss) {
			echo "  <style type='text/css'>\n";
			echo $pageCss . "\n";
			echo "  </style>\n";
		}

	// specify favicon
		if (file_exists('resources/img/icons/favicon.ico')) echo "  <link href='/resources/img/icons/favicon.ico' rel='SHORTCUT ICON' />\n";
		
	// specify canonical, if provided
		if ($canonicalUrl) echo "  <link rel='canonical' href='" . $canonicalUrl . "' />\n\n";

	// load Google Analytics
		if ($currentDatabase == 'production') {
			if (@$pseudonym['google_analytics_id']) echo $analytics->insertGoogleAnalytics($pseudonym['google_analytics_id']); 
			elseif (@$systemPreferences['Google Analytics ID']) echo $analytics->insertGoogleAnalytics($systemPreferences['Google Analytics ID']); 
		}

	// specify page title
		echo "  <title>";
		if (!$pageTitle && !$noPageTitle) $pageTitle = ucwords(str_replace('_', ' ', $templateName));
		if ($pageTitle) echo $pageTitle . " - ";
		if ($sectionTitle) echo $sectionTitle . " - ";
		echo htmlspecialchars($operators->firstTrue(@$pseudonym['name'], $systemPreferences['Name of this application']), ENT_QUOTES);
		echo "</title>\n";

		echo "</head>\n\n";

	// start body
		echo "<body onLoad='";
		if ($pageLoadEvents) echo $pageLoadEvents;
		echo "'>\n\n";

	if (!$hideSiteChrome) {
		
		// start header content
			echo "  <div id='pageContainer'>\n\n";
				
		// console
			if (@$logged_in['is_tester'] && @$systemPreferences['Enable console for testers']) {
				echo "  <div id='console' class='collapse'>\n";
				echo "  </div><!-- console -->\n";
			}

		echo "    <div class='container'>\n";
		echo "      <div id='header' class='row'>\n";
			
		// logo
			echo "        <div id='logo' class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
			echo "          <h1 class='hidden'>" . htmlspecialchars($operators->firstTrue(@$pseudonym['name'], $systemPreferences['Name of this application']), ENT_QUOTES) . "</h1>\n";
			echo "          <a href='/'><img src='" . (@$pseudonym['pseudonym_id'] && file_exists('assets/pseudonym_logos/' . $pseudonym['pseudonym_id'] . '.' . $pseudonym['logo_ext']) ? '/assets/pseudonym_logos/' . $pseudonym['pseudonym_id'] . '.' . $pseudonym['logo_ext'] : "/resources/img/logo.png") . "' border='0' class='img-responsive' alt='" . htmlspecialchars($operators->firstTrue(@$pseudonym['name'], $systemPreferences['Name of this application']), ENT_QUOTES) . "' /></a>\n";
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
				
			// success or error message
				if (@$pageError) echo "          <div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $pageError . "</div>\n";
				elseif (@$pageWarning) echo "          <div class='alert alert-warning alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $pageWarning . "</div>\n";
				elseif (@$errorMessages[$pageStatus]) echo "          <div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $errorMessages[$pageStatus] . "</div>\n";
				elseif (@$successMessages[$pageStatus]) echo "          <div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $successMessages[$pageStatus] . "</div>\n";
				
			echo "        <!-- PAGE CONTENT BEGINS -->\n\n";

	}

?>
