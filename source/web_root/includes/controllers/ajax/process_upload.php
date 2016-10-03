<?php

	include __DIR__ . '/../../../initialize.php';

	// parse query string
		if ($_SERVER['QUERY_STRING']) {
			$query = explode('=', $_SERVER['QUERY_STRING']);
			$destinationPath = urldecode($query[1]);
		}

	// check for errors
		if (!@$destinationPath) {
			header('HTTP/1.1 404 Not Found');
			header('Content-type: text/plain');
			exit('No destination path provided.');
		}

		if (!file_exists(__DIR__ . '/../../../../' . $destinationPath)) {
			mkdir(__DIR__ . '/../../../../' . $destinationPath);
			if (!file_exists(__DIR__ . '/../../../../' . $destinationPath)) {
				header('HTTP/1.1 404 Not Found');
				header('Content-type: text/plain');
				exit('Unable to locate destination path (' . $destinationPath . ').');
			}
		}

		if (empty($_FILES)) {
			header('HTTP/1.1 404 Not Found');
			header('Content-type: text/plain');
			exit('Unable to find uploaded file.');		
		}

		if ($tl->settings['Maximum filesize for uploads'] && filesize($_FILES['file']['tmp_name']) > (floatval($tl->settings['Maximum filesize for uploads']) * 1024 * 1024)) {
			header('HTTP/1.1 413 Request Entity Too Large');
			header('Content-type: text/plain');
			exit('File size too large.');		
		}

		if (!$logged_in) {
			header('HTTP/1.1 401 Unauthorized');
			header('Content-type: text/plain');
			exit('Unable to authenticate user.');
		}

	// process upload
		$dropzone = new drag_and_drop_widget_TL();
		$dropzone->processUpload(['destination_path'=>$destinationPath]);

		exit();

?>
