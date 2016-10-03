<?php

	class file_manager_TL {

		public function readTextFile($file) {

			global $tl;

			if (!$file) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No file specified.\n";
				return false;
			}
			
			$contents = @file_get_contents($file);
			if ($contents) return $contents;
			else {

				if (file_exists($file) || $this->doesUrlExist(substr($file, 0, strpos($file, '?')))) {
					$handle = @fopen($file, 'r');
					$contents = @stream_get_contents($handle);
					if (!$contents) $contents = @fread($handle, @filesize($file));
					@fclose($handle);
					if ($contents) return $contents;
					else {
						$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": The file " . $file . " appears to be empty.\n";
						return false;
					}
				}
				else {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Can't locate the file " . $file . ".\n";
					return false;
				}
	
			}
			
		}
		
		public function writeTextFile($file, $contents) {

			global $tl;
			
			if (!$file) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No file specified.\n";
				return false;
			}
			
			$handle = fopen($file, 'w');
			$contents = fwrite($handle, $contents);
			fclose($handle);
	
			if (file_exists($file)) return $contents;
			else {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to create file.\n";
				return false;
			}
			
		}
			
		public function isTrackingPixelOrIcon($imageUrl, $alsoFlagIcons = false) {
			
			$dimensions = @getimagesize($imageUrl);
			if ($dimensions[0] == 1 || $dimensions[1] == 1) return true;
			elseif ($alsoFlagIcons && ($dimensions[0] < 100 || $dimensions[1] < 100)) return true;
			else return false;
		
		}
		
		public function doesUrlExist($url) {
			if (@fclose(@fopen($url, "r"))) return true;
			else return false;
		}
		
		public function isHeaderValid($url) {
			$remoteHeader = get_headers($url);
			$response = substr($remoteHeader[0], 9, 3);
			if ($response == '200') return true;
			else return false;
		}

		public function getFilesize($file) {
			$filesize = @filesize($file);
			if (!$filesize) {
				$head = @array_change_key_case(@get_headers($file, TRUE));
				$filesize = @$head['content-length'];
				if (!$filesize) {
					$ch = curl_init($file);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
					curl_setopt($ch, CURLOPT_HEADER, TRUE);
					curl_setopt($ch, CURLOPT_NOBODY, TRUE);
					$data = curl_exec($ch);
					$filesize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
					curl_close($ch);
				}
			}
			return $filesize;
		}
		
		public function isImage($file) {

			global $tl;

			if (!$file) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No file specified.\n";
				return false;
			}

			if (!file_exists($file)) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to locate the file " . $file . ".\n";
				return false;
			}
			
			if (intval(sprintf("%u", @filesize($file))) < 2000000) { // memory-intensive test for images < 2MB
	
				if ($temp = @imagecreatefromjpeg($file)) return 'JPG';
				if ($temp = @imagecreatefromgif($file)) return 'GIF';
				if ($temp = @imagecreatefrompng($file)) return 'PNG';
				if ($temp = @imagecreatefromwbmp($file)) return 'BMP';
			}

			$mimeType = $this->determineMIME($file);
			if (strpos($mimeType, 'image/jpg') !== false || strpos($mimeType, 'image/jpeg') !== false) return 'JPG';
			if (strpos($mimeType, 'image/png') !== false) return 'PNG';
			if (strpos($mimeType, 'image/gif') !== false) return 'GIF';
			if (strpos($mimeType, 'image/bmp') !== false) return 'BMP';
			if (strpos($mimeType, 'image/tif') !== false) return 'TIF';
		
			return false;
		
		}
		
		public function isPDF($file, $separatelySavedFilename = false, $acceptWeakerValidation = false) {

			global $tl;

			if (!$file) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No file specified.\n";
				return false;
			}

			if (!file_exists($file)) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to locate the file " . $file . ".\n";
				return false;
			}
			
			$fp = @fopen($tmpFile, 'r');
			if (@fgets($fp, 4) == '%PDF') return true;
			
			$mime = $this->determineMIME($file);
			if (substr_count($mime, 'pdf') > 0) return true;
			
			if ($acceptWeakerValidation) {
				if (substr($file, -4) == '.pdf' || substr($separatelySavedFilename, -4) == '.pdf') return true;
			}
	
			return false;
	
		}
		
		public function determineMIME($file) {

			global $tl;

			if (!$file) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No file specified.\n";
				return false;
			}

			if (!file_exists($file)) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to locate the file " . $file . ".\n";
				return false;
			}
			
			ob_start();
			system("file -i -b " . escapeshellarg($file));
			$mime = trim(ob_get_clean());
			if ($mime) return $mime;
			else {
				// if unsuccessful with OB, try reading EXIF
					if (function_exists('exif_read_data')) $exifData = @exif_read_data($file);
					$mime = @$exifData['MimeType'];
					if (@$mime) return $mime;
					else return false;
			}
			
		}
		
		public function extractFileMetadata($file) {

			global $tl;

			if (!$file) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No file specified.\n";
				return false;
			}
			
			if (!file_exists($file)) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to find the file " . $file . ".\n";
				return false;
			}
			
			$metadata = array();
			$metadata['MIME type'] = $this->determineMIME($file);
			$metadata['File extension'] = pathinfo($file, PATHINFO_EXTENSION);
			$parser = new parser_TL();
			$metadata['File size'] = filesize($file);
			
			if (substr_count($metadata['MIME type'], 'image')) $metadata += $this->extractImageMetadata($file);
			elseif (substr_count($metadata['MIME type'], 'video') || $metadata['File extension'] == 'mp4' || $metadata['File extension'] == 'mov' || $metadata['File extension'] == 'avi') $metadata += $this->extractVideoMetadata($file);
			elseif (substr_count($metadata['MIME type'], 'audio') || $metadata['File extension'] == 'mp3') $metadata += $this->extractAudioMetadata($file);
			elseif (substr_count($metadata['MIME type'], 'pdf') || $metadata['File extension'] == 'pdf') $metadata += $this->extractPdfMetadata($file);
			
			return $metadata;
			
		}
		
		private function extractImageMetadata($file) {
	
			global $operators;
			global $localization_manager;

			$metadata = array();
			
			$fileExt = pathinfo($file, PATHINFO_EXTENSION);
						
			if ($fileExt == 'jpg' || $fileExt == 'tif') {

				// EXIF data
					if (function_exists('exif_read_data')) $exifData = @exif_read_data($file);
					
					// color
						if (@$exifData['COMPUTED']['IsColor'] == 1) $metadata['Color'] = 'Y';
	
					// device
						if (@$exifData['Make']) $metadata['Make'] = trim($exifData['Make']);
						if (@$exifData['Model']) $metadata['Model'] = trim($exifData['Model']);
						
					// shutter
						if (@$exifData['ExposureTime']) {
							$shutterArray = explode('/', @$exifData['ExposureTime']);
							if ($shutterArray[0] && $shutterArray[1]) {
								$shutterArray[1] = intval($shutterArray[1] / $shutterArray[0]);
								$shutterArray[0] = 1;
								$metadata['Shutter'] = implode('/', $shutterArray);
								if (!$metadata['Shutter']) unset ($metadata['Shutter']);
							}
						}
	
					// aperture
						if (@$exifData['COMPUTED']['ApertureFNumber']) $metadata['Aperture'] = @$exifData['COMPUTED']['ApertureFNumber'];

					// ISO
						if (@$exifData['ISOSpeedRatings'][0]) $metadata['ISO'] = @$exifData['ISOSpeedRatings'][0];
						elseif (floatval(@$exifData['ISOSpeedRatings'][0])) $metadata['ISO'] = @$exifData['ISOSpeedRatings'][0];
						
					// datetime taken
						$captured = $operators->firstTrue(
							@$exifData['DateTimeOriginal'],
							@$exifData['FileDateTime']
						);
						
						if ($captured && date('Y', strtotime($captured)) > 1969) $metadata['Captured on'] = date('Y-m-d H:i:s', strtotime($captured));
						
					// geolocation
						if (@$exifData['GPSLatitude'][0]) $metadata['Latitude'] = $localization_manager->dmsToDecimalDegrees(substr(@$exifData['GPSLatitude'][0],0,strrpos($exifData['GPSLatitude'][0],'/')), substr($exifData['GPSLatitude'][1],0,strrpos($exifData['GPSLatitude'][1],'/')), substr($exifData['GPSLatitude'][2],0,strrpos($exifData['GPSLatitude'][2],'/')), $exifData['GPSLatitudeRef']);
						if (@$exifData['GPSLongitude'][0]) $metadata['Longitude'] = $localization_manager->dmsToDecimalDegrees(substr(@$exifData['GPSLongitude'][0],0,strrpos($exifData['GPSLongitude'][0],'/')), substr($exifData['GPSLongitude'][1],0,strrpos($exifData['GPSLongitude'][1],'/')), substr($exifData['GPSLongitude'][2],0,strrpos($exifData['GPSLongitude'][2],'/')), $exifData['GPSLongitudeRef']);
						
					// raw EXIF data
						$metadata['RawEXIF'] = print_r(@$exifData, true);
			}

			// dimensions
				$dimensions = getimagesize($file);
				$metadata['Width'] = $operators->firstTrue(
					@$dimensions[0],
					@$exifData['COMPUTED']['Width'],
					@$exifData['ExifImageWidth']
				);

				$metadata['Height'] = $operators->firstTrue(
					@$dimensions[1],
					@$exifData['COMPUTED']['Height'],
					@$exifData['ExifImageHeight']
				);

				if ($metadata['Width'] > $metadata['Height']) $metadata['Orientation'] = 'Landscape';
				if ($metadata['Height'] > $metadata['Width']) $metadata['Orientation'] = 'Portrait';
						
			return $metadata;
			
		}
		
		private function extractAudioMetadata($file) {

			$fileExt = pathinfo($file, PATHINFO_EXTENSION);
				
			$getID3 = @new getID3;
			$id3metadata = @$getID3->analyze($file);

			// title
				$metadata['Title'] = operators_TL::firstTrue(
					@$id3metadata['tags_html']['id3v2']['title'][0],
					@$id3metadata['tags']['id3v2']['title'][0],
					@$id3metadata['tags_html']['id3v1']['title'][0],
					@$id3metadata['tags']['id3v1']['title'][0]
				);

			// artist
				$metadata['Artist'] = operators_TL::firstTrue(
					@$id3metadata['comments_html']['artist'][0],
					@$id3metadata['tags_html']['id3v2']['artist'][0],
					@$id3metadata['tags']['id3v2']['artist'][0],
					@$id3metadata['tags_html']['id3v1']['artist'][0],
					@$id3metadata['tags']['id3v1']['artist'][0]
				);
				
			// album
				$metadata['Album'] = operators_TL::firstTrue(
					@$id3metadata['tags_html']['id3v2']['album'][0],
					@$id3metadata['tags']['id3v2']['album'][0],
					@$id3metadata['tags_html']['id3v1']['album'][0],
					@$id3metadata['tags']['id3v1']['album'][0]
				);

			// track
				$metadata['Track'] = operators_TL::firstTrue(
					@$id3metadata['tags_html']['id3v2']['track_number'][0],
					@$id3metadata['tags']['id3v2']['track_number'][0],
					@$id3metadata['tags_html']['id3v1']['track_number'][0],
					@$id3metadata['tags']['id3v1']['track_number'][0]
				);

			// genre
				$metadata['Genre'] = operators_TL::firstTrue(
					@$id3metadata['tags_html']['id3v2']['genre'][0],
					@$id3metadata['tags']['id3v2']['genre'][0],
					@$id3metadata['tags_html']['id3v1']['genre'][0],
					@$id3metadata['tags']['id3v1']['genre'][0]
				);

			//  year
				$metadata['Year'] = operators_TL::firstTrue(
					@$id3metadata['tags_html']['id3v2']['year'][0],
					@$id3metadata['tags']['id3v2']['year'][0],
					@$id3metadata['tags_html']['id3v1']['year'][0],
					@$id3metadata['tags']['id3v1']['year'][0]
				);
				
			//  playtime
				$hours = 0;
				$minutes = 0;
				$seconds = 0;
				
				if (@$id3metadata['playtime_string']) {
					$playtime = $id3metadata['playtime_string'];
					$playtimeArray = explode(':', $playtime);
					$minutes = str_pad($playtimeArray[0], 2, '0', STR_PAD_LEFT);
					$seconds = str_pad($playtimeArray[1], 2, '0', STR_PAD_LEFT);
					if ($minutes > 59) {
						$hours = str_pad(floor($minutes / 60), 2, '0', STR_PAD_LEFT);
						$minutes = str_pad($minutes - ($hours * 60), 2, '0', STR_PAD_LEFT);
					}
					$metadata['Playtime'] = $hours . ':' . $minutes . ':' . $seconds;
				}
				
				if (@$id3metadata['playtime_seconds']) $metadata['Playtime in seconds'] = round($id3metadata['playtime_seconds'], 4);
				
			//  bitrate
				if (@$id3metadata['video']['bitrate']) $metadata['Bitrate'] = intval($id3metadata['video']['bitrate'] / 1000);
				elseif (@$id3metadata['audio']['bitrate']) $metadata['Bitrate'] = intval($id3metadata['audio']['bitrate'] / 1000);
				elseif (@$id3metadata['mpeg']['audio']['bitrate']) $metadata['Bitrate'] = intval($id3metadata['mpeg']['audio']['bitrate'] / 1000);
				
			// extract encoder
				if (@$id3metadata['video']['encoder']) $metadata['Encoder'] = $id3metadata['video']['encoder'];
				elseif (@$id3metadata['audio']['encoder']) $metadata['Encoder'] = $id3metadata['audio']['encoder'];
				
			// lossless
				if (@$id3metadata['audio']['lossless'] == 1) $metadata['Lossless'] = 'Y';
				
			return $metadata;
			
		}
		
		private function extractVideoMetadata($file) {

			global $pathToFFmpeg;
			global $pathToImageMagick;
							
			$metadata = array();
			
			$fileExt = pathinfo($file, PATHINFO_EXTENSION);
				
			if ($pathToImageMagick && file_exists(rtrim($pathToImageMagick, '/') . '/identify')) {
				$identify = rtrim($pathToImageMagick, '/') . '/identify';
				$identify .= ' ' . escapeshellarg($file); // input file
				exec ($identify, $output, $return_var);
	
				// dimensions
					$splitFirstValue = explode(' ', $output[0]);
					$dimensions = $splitFirstValue[2];
					$splitDimensions = explode('x', $dimensions);
					$metadata['Width'] = $splitDimensions[0];
					$metadata['Height'] = $splitDimensions[1];
			}
				
			// look for ID3 tags within the file itself
				$getID3 = @new getID3;
				$id3metadata = @$getID3->analyze($file);
	
			// extract dimensions (if not already obtained)
				if (!@$metadata['Width'] || !@$metadata['Height']) $dimensions = getimagesize($file);

				if (!@$metadata['Width']) {
					$metadata['Width'] = operators_TL::firstTrue(
						@$id3metadata['video']['resolution_x'],
						@$id3metadata['quicktime']['video']['resolution_x'],
						$dimensions[0]
					);
				}
				
				if (!@$metadata['Height']) {
					$metadata['Height'] = operators_TL::firstTrue(
						@$id3metadata['video']['resolution_y'],
						@$id3metadata['quicktime']['video']['resolution_y'],
						$dimensions[1]
					);
				}
				
				if ((!$metadata['Width'] || !$metadata['Height']) && $pathToFFmpeg && file_exists(rtrim($pathToFFmpeg, '/') . '/ffmpeg')) {
					ob_start();
					passthru(rtrim($pathToFFmpeg, '/') . "/ffmpeg -i \"{$file}\" 2>&1");
					$resolution = ob_get_contents();
					ob_end_clean();
					$search = '/Video: (.*?),(.*?),(.*?),/';
					$resolution = preg_match($search, $resolution, $durmatches, PREG_OFFSET_CAPTURE, 3);
					$resolution = $durmatches[3][0];
					$resolutionsplit = array();
					$resolutionsplit = explode("x", $resolution);
					$metadata['Width'] = trim($resolutionsplit[0]);
					$metadata['Height'] = trim($resolutionsplit[1]);
				}
				
			// extract playtime
				$hours = 0;
				$minutes = 0;
				$seconds = 0;
				
				if (@$id3metadata['playtime_string']) {
					$playtime = $id3metadata['playtime_string'];
					if ($playtime) {
						$playtimeArray = explode(':', $playtime);
						$minutes = str_pad($playtimeArray[0], 2, '0', STR_PAD_LEFT);
						$seconds = str_pad($playtimeArray[1], 2, '0', STR_PAD_LEFT);
						if ($minutes > 59) {
							$hours = str_pad(floor($minutes / 60), 2, '0', STR_PAD_LEFT);
							$minutes = str_pad($minutes - ($hours * 60), 2, '0', STR_PAD_LEFT);
						}
						$metadata['Playtime'] = $hours . ':' . $minutes . ':' . $seconds;
					}
				}
				
			// extract bitrate
				$metadata['Bitrate'] = operators_TL::firstTrue(
					intval(@$id3metadata['video']['bitrate'] / 1000),
					intval(@$id3metadata['bitrate'] / 1000)
				);
				
			// extract framerate
				$metadata['Framerate'] = operators_TL::firstTrue(
					@$id3metadata['video']['frame_rate'],
					@$id3metadata['quicktime']['video']['frame_rate']
				);
				
			// extract encoder
				if (@$id3metadata['video']['encoder']) $metadata['Encoder'] = intval($id3metadata['video']['encoder']);
				
			return $metadata;
			
		}

		private function extractPdfMetadata($file) {
	
			$metadata = array();
			
			$fileExt = pathinfo($file, PATHINFO_EXTENSION);
						
			if ($fileExt == 'pdf') {
				$pdf = new PDFInfo;
				$pdf->load($file);
				$metadata['Title'] = $pdf->title;
				$metadata['Author'] = $pdf->author;
				$metadata['Number of pages'] = $pdf->pages;
			}
						
			return $metadata;
			
		}
		
	}
	
/*	
	File Manager

	::	DESCRIPTION
	
		Functions to manipulate files and folders

	::	DEPENDENT ON
	
		parser_TL
		operators_TL
		getID3 library
		PDFInfo library
	
	::	VERSION HISTORY

	::	LICENSE
	
		Copyright (C) Timothy Quinn / Tidal Lock / Consolidated Biro
		
		Permission is hereby granted, free of charge, to any person
		obtaining a copy of this software and associated documentation
		files (the "Software"), to deal in the Software without
		restriction, including without limitation the rights to use,
		copy, modify, merge, publish, distribute, sublicense, and/or
		sell copies of the Software, and to permit persons to whom the
		Software is furnished to do so, subject to the following
		conditions:
		
		The above copyright notice and this permission notice shall be
		included in all copies or substantial portions of the Software.
		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
		EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
		OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
		NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
		HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
		WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
		FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
		OTHER DEALINGS IN THE SOFTWARE.
*/
	
?>
