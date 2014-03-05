<?php

	class fileManager_TL {

		public function readTextFile($file) {

			if (!$file) {
				errorManager_TL::addError("No file specified.");
				return false;
			}
			
			$contents = @file_get_contents($file);
			if ($contents) return $contents;
			else {
			
				if ($this->doesUrlExist(substr($file, 0, strpos($file, '?')))) {
					$handle = @fopen($file, 'r');
					$contents = @stream_get_contents($handle);
					if (!$contents) $contents = @fread($handle, @filesize($file));
					@fclose($handle);
					return $contents;
				}
				else return false;
	
			}
			
		}
		
		public function writeTextFile($file, $contents) {
			
			if (!$file) {
				errorManager_TL::addError("No file specified.");
				return false;
			}
			
			$handle = fopen($file, 'w');
			$contents = fwrite($handle, $contents);
			fclose($handle);
	
			if (file_exists($file)) return $contents;
			else {
				errorManager_TL::addError("Unable to create file.");
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
		
		public function isImage($file) {
			
			if (!$file) {
				errorManager_TL::addError("No file specified.");
				return false;
			}
			
			if (intval(sprintf("%u", @filesize($file))) < 2000000) { // memory-intensive test for images < 2MB
	
				if ($temp = @imagecreatefromjpeg($file)) return 'JPG';
				if ($temp = @imagecreatefromgif($file)) return 'GIF';
				if ($temp = @imagecreatefrompng($file)) return 'PNG';
				if ($temp = @imagecreatefromwbmp($file)) return 'BMP';
				
				return false;
				
			}
			else { // for larger files, mime type will have to suffice
				
				$mimeType = $this->determineMIME($file);
				if ($mimeType == 'image/jpg' || $mimeType == 'image/jpeg') return 'JPG';
				if ($mimeType == 'image/png') return 'PNG';
				if ($mimeType == 'image/gif') return 'GIF';
				if ($mimeType == 'image/bmp') return 'BMP';
				
				return false;
				
			}
		
		}
		
		public function isPDF($file, $separatelySavedFilename = false, $acceptWeakerValidation = false) {

			if (!$file) {
				errorManager_TL::addError("No file specified.");
				return false;
			}

			if (!file_exists($file)) {
				errorManager_TL::addError("Unable to locate file.");
				return false;
			}
			
			$fp = @fopen($tmpFile, 'r');
			if (fgets($fp, 4) == '%PDF') return true;
			
			$mime = $this->determineMIME($file);
			if (substr_count($mime, 'pdf') > 0) return true;
			
			if ($acceptWeakerValidation) {
				if (substr($file, -4) == '.pdf' || substr($separatelySavedFilename, -4) == '.pdf') return true;
			}
	
			return false;
	
		}
		
		public function determineMIME($file) {

			if (!$file) {
				errorManager_TL::addError("No file specified.");
				return false;
			}

			if (!file_exists($file)) {
				errorManager_TL::addError("Unable to locate file.");
				return false;
			}
			
			global $extToMime_TL;
			
			ob_start();
			system("file -i -b {$file}");
			$mime = trim(ob_get_clean());
			if ($mime) return $mime;
			else {
				// if unsuccessful with OB, try reading EXIF
					if (function_exists('exif_read_data')) $exifData = exif_read_data($file);
					$mime = @$exifData['MimeType'];
					if (@$mime) return $mime;
					else {
						// if unsuccessful with EXIF, try the file extension
							$mime = $extToMime_TL[pathinfo($file, PATHINFO_EXTENSION)];
							if ($mime) return $mime;
							else return false;
					}
			}
			
		}
		
		public function extractFileMetadata($file) {

			if (!$file) {
				errorManager_TL::addError("No file specified.");
				return false;
			}
			
			if (!file_exists($file)) {
				errorManager_TL::addError("Unable to find file.");
				return false;
			}
			
			$metadata = array();
			$metadata['MIME'] = $this->determineMIME($file);
			$metadata['FileExtension'] = pathinfo($file, PATHINFO_EXTENSION);
			$parser = new parser_TL();
			$metadata['FileSizeInBytes'] = filesize($file);
			$metadata['FileSize'] = $parser->addFileSizeSuffix($metadata['FileSizeInBytes']);
			
			if (substr_count($metadata['MIME'], 'image')) $metadata += $this->extractImageMetadata($file);
			elseif (substr_count($metadata['MIME'], 'video')) $metadata += $this->extractVideoMetadata($file);
			elseif (substr_count($metadata['MIME'], 'audio')) $metadata += $this->extractAudioMetadata($file);
			
			return $metadata;
			
		}
		
		private function extractImageMetadata($file) {
	
			$metadata = array();
			
			$fileExt = pathinfo($file, PATHINFO_EXTENSION);
						
			if ($fileExt == 'jpg' || $fileExt == 'tif') {

				// EXIF data
					if (function_exists('exif_read_data')) $exifData = exif_read_data($file);
					$metadata = @$exifData;
					
					// width & height
						$metadata['Width'] = @$exifData['COMPUTED']['Width'];
						$metadata['Height'] = @$exifData['COMPUTED']['Height'];
						
						if (!$metadata['Width']) $metadata['Width'] =  @$exifData['ExifImageWidth'];
						if (!$metadata['Height']) $metadata['Height'] =  @$exifData['ExifImageHeight'];
						
					// color
						if (@$exifData['COMPUTED']['IsColor'] == 1) $metadata['Color'] = 'Y';
	
					// device
						if (@$exifData['Make'] || @$exifData['Model']) {
							$metadata['Make'] = trim(@$exifData['Make']);
							$metadata['Model'] = trim(@$exifData['Model']);
	
							if (substr_count($metadata['Model'], $metadata['Make']) > 0) $metadata['Device'] = $metadata['Model'];
							else $metadata['Device'] = $metadata['Make'] . ' / ' . $metadata['Model'];
						}
	
					// shutter
						if (@$exifData['ExposureTime']) {
							$shutterArray = explode('/', @$exifData['ExposureTime']);
							$shutterArray[1] = intval($shutterArray[1] / $shutterArray[0]);
							$shutterArray[0] = 1;
							$metadata['Shutter'] = implode('/', $shutterArray);
							if ($metadata['Shutter'] == 0) $metadata['Shutter'] = '';
						}
	
					// aperture
						if (@$exifData['COMPUTED']['ApertureFNumber']) $metadata['Aperture'] = @$exifData['COMPUTED']['ApertureFNumber'];
						if (!@$metadata['Aperture']) $metadata['Aperture'] = '';
						
					// date/time taken
						if (@$exifData['DateTimeOriginal']) $taken = strtotime(@$exifData['DateTimeOriginal']);
						elseif (@$exifData['FileDateTime']) $taken = @$exifData['FileDateTime'];
	
						if (@$taken) $metadata['DateTimeTaken'] = date('Y-m-d H:i:s', @$taken);
						
					// geolocation
						if (@$exifData['GPSLatitude'][0] && @$exifData['GPSLongitude'][0]) {
							$metadata['Latitude'] = convertDmsToDecimalDegrees_TL(substr(@$exifData['GPSLatitude'][0],0,strrpos($exifData['GPSLatitude'][0],'/')), substr($exifData['GPSLatitude'][1],0,strrpos($exifData['GPSLatitude'][1],'/')), substr($exifData['GPSLatitude'][2],0,strrpos($exifData['GPSLatitude'][2],'/')), $exifData['GPSLatitudeRef']);
							$metadata['Longitude'] = convertDmsToDecimalDegrees_TL(substr(@$exifData['GPSLongitude'][0],0,strrpos($exifData['GPSLongitude'][0],'/')), substr($exifData['GPSLongitude'][1],0,strrpos($exifData['GPSLongitude'][1],'/')), substr($exifData['GPSLongitude'][2],0,strrpos($exifData['GPSLongitude'][2],'/')), $exifData['GPSLongitudeRef']);
						}
						
			}

			// dimensions
				if (!@$metadata['Width'] || !@$metadata['Height']) {
					$dimensions = getimagesize($file);
					if (!@$metadata['Width']) $metadata['Width'] =  $dimensions[0];
					if (!@$metadata['Height']) $metadata['Height'] =  $dimensions[1];
				}
						
			return $metadata;
			
		}
		
		private function extractAudioMetadata($file) {
	
			$fileExt = pathinfo($file, PATHINFO_EXTENSION);
				
			$getID3 = @new getID3;
			$id3metadata = @$getID3->analyze($file);
	
			// extract title
				if ($id3metadata['tags_html']['id3v2']['title'][0] > $id3metadata['tags_html']['id3v1']['title'][0]) $metadata['Title'] = $id3metadata['tags_html']['id3v2']['title'][0];
				else $metadata['Title'] = $id3metadata['tags_html']['id3v1']['title'][0];
				
			// extract playtime
				$hours = 0;
				$minutes = 0;
				$seconds = 0;
				
				$playtime = @$id3metadata['playtime_string'];
				$playtimeArray = explode(':', $playtime);
				$minutes = str_pad($playtimeArray[0], 2, '0', STR_PAD_LEFT);
				$seconds = str_pad($playtimeArray[1], 2, '0', STR_PAD_LEFT);
				if ($minutes > 59) {
					$hours = str_pad(floor($minutes / 60), 2, '0', STR_PAD_LEFT);
					$minutes = str_pad($minutes - ($hours * 60), 2, '0', STR_PAD_LEFT);
				}
				$metadata['Playtime'] = $hours . ':' . $minutes . ':' . $seconds;
				$metadata['PlaytimeHour'] = $hours;
				$metadata['PlaytimeMinute'] = $minutes;
				$metadata['PlaytimeSecond'] = $seconds;
				
			// extract year
				$metadata['Year'] = $id3metadata['tags_html']['id3v1']['year'][0];
				
			// extract bitrate
				$metadata['Bitrate'] = @intval($id3metadata['video']['bitrate'] / 1000);
				if ($metadata['Bitrate'] == 0) {
					$metadata['Bitrate'] = @intval($id3metadata['audio']['bitrate'] / 1000);
					if ($metadata['Bitrate'] == 0) {
						$metadata['Bitrate'] = '';
					}
				}
				
			// extract encoder
				$metadata['Encoder'] = @intval($id3metadata['video']['encoder']);
				if ($metadata['Encoder'] == 0) $metadata['Encoder'] = '';
				
			return $metadata;
			
		}
		
		private function extractVideoMetadata($file) {
	
			global $imageMagickRoot;
			global $FFmpegRoot;
							
			$metadata = array();
			
			$fileExt = pathinfo($file, PATHINFO_EXTENSION);
				
			if ($imageMagickRoot && file_exists($imageMagickRoot . 'identify')) {
				$identify = $imageMagickRoot . 'identify';
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
				if (!@$metadata['Width']) $metadata['Width'] = @intval($id3metadata['video']['resolution_x']);
				if (!@$metadata['Height']) $metadata['Height'] = @intval($id3metadata['video']['resolution_y']);
				
				if (!@$metadata['Width'] || !@$metadata['Height']) {
					$dimensions = getimagesize($file);
					$metadata['Width'] = $dimensions[0];
					$metadata['Height'] = $dimensions[1];
				}
				
				if ((!$metadata['Width'] || !$metadata['Height']) && $FFmpegRoot && file_exists($FFmpegRoot . 'ffmpeg')) {
					ob_start();
					passthru($FFmpegRoot . "ffmpeg -i \"{$file}\" 2>&1");
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
				
				if (!@$metadata['Width']) $metadata['Width'] = '';
				if (!@$metadata['Height']) $metadata['Height'] = '';
				
			// extract playtime
				$hours = 0;
				$minutes = 0;
				$seconds = 0;
				
				$playtime = @$id3metadata['playtime_string'];
				if ($playtime) {
					$playtimeArray = explode(':', $playtime);
					$minutes = str_pad($playtimeArray[0], 2, '0', STR_PAD_LEFT);
					$seconds = str_pad($playtimeArray[1], 2, '0', STR_PAD_LEFT);
					if ($minutes > 59) {
						$hours = str_pad(floor($minutes / 60), 2, '0', STR_PAD_LEFT);
						$minutes = str_pad($minutes - ($hours * 60), 2, '0', STR_PAD_LEFT);
					}
					$metadata['Playtime'] = $hours . ':' . $minutes . ':' . $seconds;
					$metadata['PlaytimeHour'] = $hours;
					$metadata['PlaytimeMinute'] = $minutes;
					$metadata['PlaytimeSecond'] = $seconds;
				}
				
			// extract bitrate
				$metadata['Bitrate'] = @intval($id3metadata['video']['bitrate'] / 1000);
				if (!@$metadata['Bitrate']) $metadata['Bitrate'] = @intval($id3metadata['bitrate'] / 1000);
				if (!@$metadata['Bitrate']) $metadata['Bitrate'] = '';
				
			// extract framerate
				$metadata['Framerate'] = @intval($id3metadata['video']['frame_rate']);
				
			// extract encoder
				$metadata['Encoder'] = @intval($id3metadata['video']['encoder']);
				
			return $metadata;
			
		}
	
	}
	
/*	
	File Manager

	::	DESCRIPTION
	
		Functions to manipulate files and folders

	::	DEPENDENT ON
	
		parser_TL
		$imageMagickRoot
		$FFmpegRoot
		getID3
	
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
