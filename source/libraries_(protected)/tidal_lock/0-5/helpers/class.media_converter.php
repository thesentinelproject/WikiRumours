<?php

	class media_converter_TL {
		
		public function convert($incomingFile, $outgoingFilename, $outgoingPath, $desiredWidth, $desiredHeight = null, $desiredAngle = null) {

			global $extToMime_TL;
			global $tl;
			
			$fileManager = new file_manager_TL();
			
			// get source type and destination type
				$incomingMIME = $fileManager->determineMIME($incomingFile);
				$outgoingExt = pathinfo($outgoingFilename, PATHINFO_EXTENSION);
				$outgoingMIME = $extToMime_TL[$outgoingExt];
			
			// validate incoming file (rudimentary validation only)
				if (!file_exists($incomingFile) && !$fileManager->doesUrlExist($incomingFile)) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Can't find source image.\n";
					return false;
				}
				
			// validate outgoing path
				$outgoingPath = trim ($outgoingPath, '/') . '/';
				if (file_exists($outgoingPath)) {
					if (!is_dir($outgoingPath) || is_link($outgoingPath)) {
						$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Destination directory is invalid.\n";
						return false;
					}
				}
				else {
					mkdir($outgoingPath);
					if (!file_exists($outgoingPath)) {
						$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to locate or create destination directory.\n";
						return false;
					}
				}
				
			// redirect to appropriate function
				if (substr_count($incomingMIME, 'image')) {
					return $this->convertImage($incomingFile, $outgoingFilename, $outgoingPath, $desiredWidth, $desiredHeight = null, $desiredAngle = null);
				}
				elseif (substr_count($incomingMIME, 'video')) {
					if (substr_count($outgoingMIME, 'video')) return $this->convertVideo($incomingFile, $outgoingFilename, $outgoingPath, $desiredWidth, $desiredHeight);
					elseif (substr_count($outgoingMIME, 'image')) return $this->thumbnailVideo($incomingFile, $outgoingFilename, $outgoingPath, $desiredWidth, $desiredHeight);
				}
				else {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to read incoming file and/or incompatible file format.\n";
					return false;
				}
			
		}
		
		public function convertImage($incomingFile, $outgoingFilename, $outgoingPath, $desiredWidth, $desiredHeight = null, $desiredAngle = null) {

			global $pathToImageMagick;
			global $extToMime_TL;
			global $tl;
			
			$outgoingPath = rtrim ($outgoingPath, '/') . '/';
			$outgoingExt = pathinfo($outgoingFilename, PATHINFO_EXTENSION);
			$outgoingMIME = $extToMime_TL[$outgoingExt];

			$currentDimensions = @getimagesize($incomingFile);
			$currentWidth = @$currentDimensions[0];
			$currentHeight = @$currentDimensions[1];
			if (!$currentWidth || !$currentHeight) {
				$currentWidth = @imagesx($incomingFile);
				$currentHeight = @imagesy($incomingFile);
			}
			
			// check for errors
				if (!$currentWidth || !$currentHeight) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to retrieve dimensions of current image.\n";
					return false;
				}
				
			// prevent upscaling
				if ($currentWidth < $desiredWidth) $desiredWidth = $currentWidth;
				if ($currentHeight < $desiredHeight) $desiredHeight = $currentHeight;
			
			// supply missing dimensions
				if (!$desiredWidth && !$desiredHeight) {
					$desiredWidth = $currentWidth;
					$desiredHeight = $currentHeight;
				}
				elseif ($desiredWidth && !$desiredHeight) {
					$scale = $desiredWidth / $currentWidth;
					$desiredHeight = floor($scale * $currentHeight);
				}
				elseif ($desiredHeight && !$desiredWidth) {
					$scale = $desiredHeight / $currentHeight;
					$desiredWidth = floor($scale * $currentWidth);
				}
				else {
					if ($currentWidth > $currentHeight) {
						$xOffset = floor(($currentWidth - $currentHeight) / 2);
						$currentWidth = $currentHeight;
					}
					elseif ($currentHeight > $currentWidth) {
						$yOffset = floor(($currentHeight - $currentWidth) / 2);
						$currentHeight = $currentWidth;
					}
				}
				
			// create new image
				if ($pathToImageMagick && file_exists(rtrim($pathToImageMagick, '/') . '/convert')) {
					if ((@$xOffset || @$yOffset) && !$desiredAngle) {
						$convert = rtrim($pathToImageMagick, '/') . '/convert';
						$convert .= ' -define jpeg:size=' . min($currentWidth, $currentHeight) . 'x' . min($currentWidth, $currentHeight);
						$convert .= ' ' . escapeshellarg($incomingFile); // input file
						$convert .= ' -thumbnail'; // type of conversion
						$convert .= ' ' . $desiredWidth . 'x' . $desiredHeight . '^ -gravity center -extent ' .  $desiredWidth . 'x' . $desiredHeight; // dimensions of output file
						$convert .= ' ' . escapeshellarg($outgoingPath . $outgoingFilename); // output file
					}
					elseif ($desiredAngle) {
						$convert = rtrim($pathToImageMagick, '/') . '/convert';
						$convert .= ' -rotate ' . $desiredAngle;
						$convert .= ' ' . escapeshellarg($incomingFile); // input file
						$convert .= ' ' . escapeshellarg($outgoingPath . $outgoingFilename); // output file
					}
					else {
						$convert = rtrim($pathToImageMagick, '/') . '/convert';
						$convert .= ' -thumbnail'; // type of conversion
						$convert .= ' ' . $desiredWidth . 'x' . $desiredHeight; // dimensions of output file
						$convert .= ' ' . escapeshellarg($incomingFile); // input file
						$convert .= ' ' . escapeshellarg($outgoingPath . $outgoingFilename); // output file
					}
					exec ($convert, $output, $return_var);
				}

			// verify complete
				if (file_exists($outgoingPath . $outgoingFilename)) return true;
				else {
					if ($desiredAngle) {
						$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to convert image using ImageMagick\n";
						return false;
					}
					else {
						// create destination image object
							if ($img = @imagecreatefromjpeg($incomingFile)) $img = imagecreatefromjpeg($incomingFile);
							elseif ($img = @imagecreatefromgif($incomingFile)) $img = imagecreatefromgif($incomingFile);
							elseif ($img = @imagecreatefrompng($incomingFile)) $img = imagecreatefrompng($incomingFile);
							else {
								$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to read image format.\n";
								return false;
							}
							
						// create a temporary image
							$newImage = imagecreatetruecolor($desiredWidth, $desiredHeight);
							imagealphablending($newImage, false);
							imagesavealpha($newImage, true);  
							
						// copy and resize the old image into the new image
							imagecopyresampled($newImage, $img, 0, 0, intval(@$xOffset), intval(@$yOffset), $desiredWidth, $desiredHeight, $currentWidth, $currentHeight);
							imagedestroy($img);
							if ($outgoingExt == 'jpg') imagejpeg($newImage, $outgoingPath . $outgoingFilename, 80);
							elseif ($outgoingExt == 'png') imagepng($newImage, $outgoingPath . $outgoingFilename, 8);
							elseif ($outgoingExt == 'gif') imagegif($newImage, $outgoingPath . $outgoingFilename);
							else {
								$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to determine destination file type.\n";
								return false;
							}
							
						// check for errors again
							if (file_exists($outgoingPath . $outgoingFilename)) return true;
							else {
								$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unknown problem attempting to create new image.\n";
								return false;
							}
									
					}
				}
			
		}
		
		public function convertVideo($incomingFile, $outgoingFilename, $outgoingPath, $desiredWidth, $desiredHeight = null) {
			
			global $pathToImageMagick;
			global $pathToFFmpeg;
			global $tl;

			// check for errors
				if (!$desiredWidth && !$desiredHeight) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Missing desired dimensions.\n";
					return false;
				}
			
			// if one dimension unspecified, choose a 4:3 aspect ratio
				if (!$desiredWidth) $desiredWidth = intval($desiredHeight * 4 / 3);
				if (!$desiredHeight) $desiredHeight = intval($desiredWidth * 3 / 4);
				
			// ensure each dimension is a multiple of two
				$desiredWidth = floor(($desiredWidth) / 2) * 2;
				$desiredHeight = floor(($desiredHeight) / 2) * 2;
				
			// select converter
				if ($pathToImageMagick && file_exists(rtrim($pathToImageMagick, '/') . '/convert')) {
					
					// create new image
						$convert = rtrim($pathToImageMagick, '/') . '/convert';
						$convert .= ' -resize'; // type of conversion
						$convert .= ' ' . $desiredWidth . 'x' . $desiredHeight; // dimensions of output file
						$convert .= ' ' . escapeshellarg($incomingFile); // input file
						$convert .= ' ' . escapeshellarg($outgoingPath . $outgoingFilename); // output file
						exec ($convert, $output, $return_var);
				
						if (file_exists($outgoingPath . $outgoingFilename)) return true;
						else {
							$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unknown problem attempting to create new preview video with ImageMagick.\n";
							return false;
						}
						
				}
				elseif ($pathToFFmpeg && file_exists(rtrim($pathToFFmpeg, '/') . '/ffmpeg')) {
					
					// file extension must be lowercase
						if (pathinfo($incomingFile, PATHINFO_EXTENSION) != strtolower(pathinfo($incomingFile, PATHINFO_EXTENSION))) {
							$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Please make sure file extension of incoming file is lowercase.\n";
							return false; 
						}
						
					// create new image
						$convert = rtrim($pathToFFmpeg, '/') . '/ffmpeg';
						$convert .= ' -itsoffset -1'; // capture frame after first second
						$convert .= ' -i ' . escapeshellarg($incomingFile); // input path
						$convert .= ' -vcodec mjpeg'; // incoming codec
						$convert .= ' -qscale 70'; // quality of JPG
						$convert .= ' -vframes 1'; // limit to a single frame
						$convert .= ' -an'; // disable audio recording
						$convert .= ' -f rawvideo'; // format
						$convert .= ' -s ' . $desiredWidth . 'x' . $desiredHeight; // size of output image
						$convert .= ' -y'; // overwrite output file, if exists
						$convert .= ' ' . escapeshellarg($outgoingPath . $outgoingFilename); // output path
						// $convert .= ' 2>&1'; // error handling for debug purposes only
						exec ($convert, $output, $return_var);
				
						if (file_exists($outgoingPath . $outgoingFilename)) return true;
						else {
							$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unknown problem attempting to create new preview video with FFmpeg.\n";
							return false;
						}
						
				}
				else {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No video converter found.\n";
					return false;
				}
			
		}
		
		public function thumbnailVideo($incomingFile, $outgoingFilename, $outgoingPath, $desiredWidth, $desiredHeight) {
	
			global $pathToImageMagick;
			global $tl;
			
			// check for errors
				if ($pathToImageMagick && !file_exists(rtrim($pathToImageMagick, '/') . '/convert')) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Can't find ImageMagick on this server.\n";
					return false;
				}
			
			// check for errors
				if (!$desiredWidth && !$desiredHeight) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Missing desired dimensions.\n";
					return false;
				}
			
			// if one dimension unspecified, choose a 4:3 aspect ratio
				if (!$desiredWidth) $desiredWidth = intval($desiredHeight * 4 / 3);
				if (!$desiredHeight) $desiredHeight = intval($desiredWidth * 3 / 4);
								
			// create new image
				$convert = rtrim($pathToImageMagick, '/') . '/convert';
				$convert .= ' -thumbnail'; // type of conversion
				$convert .= ' ' . $desiredWidth . 'x' . $desiredHeight; // dimensions of output file
				$convert .= ' ' . escapeshellarg($incomingFile) . '[0]'; // input file
				$convert .= ' ' . escapeshellarg($outgoingPath . $outgoingFilename); // output file
				exec ($convert, $output, $return_var);
		
				if (file_exists($outgoingPath . $outgoingFilename)) return true;
				else {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unknown problem attempting to thumbnail video.\n";
					return false;
				}
			
		}
		
		public function thumbnailPDF($incomingFile, $outgoingFilename, $outgoingPath, $desiredWidth = null, $desiredHeight = null) {

			global $pathToImageMagick;
			global $pathToGhostScript;
			global $tl;

			$outgoingPath = rtrim ($outgoingPath, '/') . '/';
			$outgoingExt = pathinfo($outgoingFilename, PATHINFO_EXTENSION);

			if (!file_exists($incomingFile)) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to locate PDF.\n";
				return false;
			}

			$file_manager = new file_manager_TL();
			if (!$file_manager->isPDF($incomingFile)) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Does not appear to be a valid PDF.\n";
				return false;
			}
			
			// convert first page of PDF to JPG (try using GhostScript first, then try ImageMagick)
					if ($pathToGhostScript && file_exists($pathToGhostScript)) {
						$ghostScriptDeviceEngines = array('jpg' => 'jpeg', 'jpeg' => 'jpeg', 'tif' => 'tiff24nc', 'bmp' => 'bmpsep8', 'png' => 'png16m');
						if (!$outgoingExt || !@$ghostScriptDeviceEngines[$outgoingExt]) {
							$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to convert to desired file extension.\n";
							return false;
						}

						$convert = rtrim($pathToGhostScript, '/') . "/gs";
						$convert .= " -dBATCH -dNOPAUSE -dFirstPage=1 -dLastPage=1";
						$convert .= " -sDEVICE=" . $ghostScriptDeviceEngines[$outgoingExt];
						$convert .= " -sOutputFile=" . escapeshellarg($outgoingPath . 'temp_' . $outgoingFilename); // output file
						$convert .= " " . escapeshellarg($incomingFile); // input file
						exec ($convert, $output, $return_var);
					}

					if (!file_exists($outgoingPath . 'temp_' . $outgoingFilename)) {

						if ($pathToImageMagick && file_exists(rtrim($pathToImageMagick, '/') . '/convert')) {
							$convert = rtrim($pathToImageMagick, '/') . '/convert';
							$convert .= ' ' . escapeshellarg($incomingFile) . '[0]'; // input file
							$convert .= ' ' . escapeshellarg($outgoingPath . 'temp_' . $outgoingFilename); // output file
							exec ($convert, $output, $return_var);
						}

					}

					if (!file_exists($outgoingPath . 'temp_' . $outgoingFilename)) {
						$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to convert PDF.\n";
						return false;
					}

			// resize if necessary
				if ($desiredWidth || $desiredHeight) {
					$success = $this->convertImage($outgoingPath . 'temp_' . $outgoingFilename, $outgoingFilename, $outgoingPath, $desiredWidth, $desiredHeight);
					@unlink($outgoingPath . 'temp_' . $outgoingFilename);
					return $success;
				}
				else {
					rename($outgoingPath . 'temp_' . $outgoingFilename, $outgoingPath . $outgoingFilename);
					if (file_exists($outgoingPath . $outgoingFilename)) return true;
					else {
						@unlink($outgoingPath . 'temp_' . $outgoingFilename);
						$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to rename converted file.\n";
						return false;
					}
				}

		}

		public function base64ToJpg($base64_string, $file) {
			
			$ifp = fopen($file, "wb");

			$data = explode(',', $base64_string);

			fwrite($ifp, base64_decode($data[1]));

			fclose($ifp);

			return $file;
		
		}
		
	}

/*	
	Media Converter

	::	DESCRIPTION
	
		Miscellaneous functions for converting one file or value to another

	::	DEPENDENT ON
	
		metadata_TL
		file_manager_TL
		ImageMagick
		FFmpeg
	
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
