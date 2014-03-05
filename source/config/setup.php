<?php

	// about this app
		$tablePrefix = "wr_";
		
	// maintain state
		$numberOfDaysToPreserveLogin = 365;
		$numberOfDaysToPreservePasswordKey = 3;
		$numberOfDaysToPreserveEmailKey = 7;
		$numberOfDaysToPreserveDbBackups = 30;
		$numberOfDaysToPreserveLogs = 90;
		$maximumNumberOfInvitationReminders = 3;

	// timezone
		$timezone = "US/Eastern";
		
	// analytics
		$googleAnalyticsID = "";
		$googleAnalyticsDomain = "";
		$googleAnalyticsAccommodateMultipleTopLevelDomains = false;
		
		$mixPanelToken = "";
		
	// connections
		$cronConnectionIntervalInMinutes = 0; // set to 0 if no cron connection
		
	// mobile
		$redirectForMobile = false;
		$redirectMobileToSpecificUrl = '/';
		
	// images
		$profileImageSizes_TL = array(
			'large' => 150,
			'small' => 75,
			'verysmall' => 50
		); // do not change this if there are existing images

	// helper apps on the server
		$imageMagickRoot = '/usr/bin/';
		$FFmpegRoot = '/usr/local/dh/bin/';
		
	// CMS
		$maxFilesizeForCmsUploads = 2000; // in kilobytes
		
	// Internal API
		$apiCap = 100;
		
	// External APIs
		$payPalApiUsername = "";
		$payPalApiPassword = "";
		$payPalApiSignature = "";
		
		$bitlyApiKey = null;
		$bitlyLogin = null;
		$owlyApiKey = null;

	// Google fonts
		$fonts_TL = array(
			'Lato'
		);
		
	// Pagination
		$maxNumberOfTableRowsPerPage = 50;
		
?>