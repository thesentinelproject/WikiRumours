<?php

	class drag_and_drop_widget_TL {

		public $html = null;
		public $js = null;
		public $file = null;

		public function initialize($params) {

			/*
				ALLOWABLE PARAMETERS
				--
				id: varchar
				post_path: URI of script which AJAX is to call
				destination_path: folder where uploads are to be placed
				max_files: int
				thumbnail_width: int
				thumbnail_height: int
				upload_multiple: true/false
				parallel_uploads: true/false
				acceptable_file_types: comma-delimited mime types
				events: Dropzone-specific events, with ['parameters'] and ['actions']
			*/

			global $systemPreferences;
			global $tl;

			$form = new form_TL();

			// check for missing parameters
				if (!$params['id']) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Missing ID for upload element.\n";
					return false;
				}

				if (!$params['post_path']) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Missing path for posting file upload.\n";
					return false;
				}
				else $params['post_path'] = rtrim($params['post_path'], '/');

				if (!@$params['destination_path']) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Missing destination path.\n";
					return false;
				}
				else $params['destination_path'] = trim($params['destination_path'], '/');

			// create view
				$this->html .= "<div id='" . $params['id'] . "' class='dropzone'>\n";
				$this->html .= "  <div class='dz-message' data-dz-message><span class='text-muted'>" . $params['message'] . "</span></div>\n";
				$this->html .= "  <div class='fallback'>" . $form->input('file', 'file', null, false, null, null, null, null, array('multiple'=>'')) . "</div>\n";
				$this->html .= "</div>\n";
				$this->html .= "<div id='filenames_" . $params['id'] . "' class='hidden'></div>\n";

				$this->js .= "// initialize dropzone\n";
				$this->js .= "  var myDropzone_" . $params['id'] . " = new Dropzone('div#" . $params['id'] . "', {\n";
				$this->js .= "    url: '" . $params['post_path'] . "?destination_path=" . urlencode($params['destination_path']) . "',\n";
				$this->js .= "    paramName: 'file',\n";
				$this->js .= "    addRemoveLinks: false,\n";
				$this->js .= "    filesizeBase: 1024,\n";
				$this->js .= "    maxFilesize: " . $systemPreferences['Maximum filesize for uploads'] . ", // MB\n";
				$this->js .= "    maxFiles: " . (@$params['max_files'] ? $params['max_files'] : 'null') . ",\n";
				if (@$params['thumbnail_width']) $this->js .= "    thumbnailWidth: " .  $params['thumbnail_width'] . ",\n";
				if (@$params['thumbnail_height']) $this->js .= "    thumbnailHeight: " .  $params['thumbnail_height'] . ",\n";
				$this->js .= "    uploadMultiple: " . (@$params['upload_multiple'] ? $params['upload_multiple'] : 'false') . ",\n";
				$this->js .= "    parallelUploads: " . (@$params['parallel_upload_s'] ? $params['parallel_uploads'] : 'true') . ",\n";
				if ($params['acceptable_mime_types']) $this->js .= "    acceptedFiles: '" . $params['acceptable_mime_types'] . "',\n";
				$this->js .= "    init: function() {\n";
				$this->js .= "      this.on('success', function(file, message) {\n";
				$this->js .= "        document.getElementById('filenames_" . $params['id'] . "').innerHTML += " . '"' . "<input type='hidden' name='file_" . $params['id'] . "[]' value = " . '" + ' . "'" . '"' . $params['destination_path'] . "/" . "' + file.name + '" . '" />' . "'" . ";\n";
				if (@$params['events']['success']) {
					$this->js .= "        " . $params['events']['success'] . "\n";
					unset($params['events']['success']);
				}
				$this->js .= "      });\n";
				$this->js .= "      this.on('error', function(file, message) {\n";
				$this->js .= "        alert(message);\n";
				if (@$params['events']['error']) {
					$this->js .= "        " . $params['events']['error'] . "\n";
					unset($params['events']['error']);
				}
				$this->js .= "      });\n";
				if (@$params['events']) {
					foreach ($params['events'] as $event => $consequences) {
						$this->js .= "      this.on('" . $event . "', function(" . $consequences['parameters'] . ") {\n";
						$this->js .= "        " . $consequences['actions'] . "\n";
						$this->js .= "      });\n";
					}
				}
				$this->js .= "    }\n";
				$this->js .= "  });\n";

		}

		public function processUpload($params) {

			global $systemPreferences;
			global $_FILES;

			// check for errors
				if (!$params['destination_path']) {
					header('HTTP/1.1 404 Not Found');
					header('Content-type: text/plain');
					exit('Unable to locate destination path.');
					return false;
				}
				else $params['destination_path'] = trim($params['destination_path'], '/');

				if (!file_exists(__DIR__ . '/../../../' . $params['destination_path'])) {
					header('HTTP/1.1 404 Not Found');
					header('Content-type: text/plain');
					exit('Unable to locate destination path (' . $params['destination_path'] . ').');
				}

				if (empty($_FILES)) {
					header('HTTP/1.1 404 Not Found');
					header('Content-type: text/plain');
					exit('Unable to find uploaded file.');		
					return false;
				}

				if ($systemPreferences['Maximum filesize for uploads'] && filesize($_FILES['file']['tmp_name']) > (floatval($systemPreferences['Maximum filesize for uploads']) * 1024 * 1024)) {
					header('HTTP/1.1 413 Request Entity Too Large');
					header('Content-type: text/plain');
					exit('File size too large.');		
					return false;
				}

			// move file
				$success = move_uploaded_file($_FILES['file']['tmp_name'], __DIR__ . '/../../../' . $params['destination_path'] . '/' . $_FILES['file']['name']);
				if (!$success || !file_exists(__DIR__ . '/../../../' . $params['destination_path'] . '/' . $_FILES['file']['name'])) {
					header('HTTP/1.1 500 Internal Server Error');
					header('Content-type: text/plain');
					exit('Unable to save uploaded file to the server.');		
					return false;
				}

			exit();

		}

	}

?>
