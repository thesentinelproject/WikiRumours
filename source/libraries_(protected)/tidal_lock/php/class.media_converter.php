<?php

	class mediaConverter_TL {
		
		public function convert($incomingFile, $outgoingFilename, $outgoingPath, $desiredWidth, $desiredHeight, $desiredAngle) {

			global $extToMime_TL;
			
			$fileManager = new fileManager_TL();
			
			// get source type and destination type
				$incomingMIME = $fileManager->determineMIME($incomingFile);
				$outgoingExt = pathinfo($outgoingFilename, PATHINFO_EXTENSION);
				$outgoingMIME = $extToMime_TL[$outgoingExt];
			
			// validate incoming file (rudimentary validation only)
				if (!file_exists($incomingFile) && !$fileManager->doesUrlExist($incomingFile)) {
					errorManager_TL::addError("Can't find source image.");
					return false;
				}
				
			// validate outgoing path
				$outgoingPath = trim ($outgoingPath, '/') . '/';
				if (file_exists($outgoingPath)) {
					if (!is_dir($outgoingPath) || is_link($outgoingPath)) {
						errorManager_TL::addError("Destination directory is invalid.");
						return false;
					}
				}
				else {
					mkdir($outgoingPath);
					if (!file_exists($outgoingPath)) {
						errorManager_TL::addError("Unable to locate or create destination directory.");
						return false;
					}
				}
				
			// redirect to appropriate function
				if (substr_count($incomingMIME, 'image')) {
					return $this->convertImage($incomingFile, $outgoingFilename, $outgoingPath, $desiredWidth, $desiredHeight, $desiredAngle);
				}
				elseif (substr_count($incomingMIME, 'video')) {
					if (substr_count($outgoingMIME, 'video')) return $this->convertVideo($incomingFile, $outgoingFilename, $outgoingPath, $desiredWidth, $desiredHeight);
					elseif (substr_count($outgoingMIME, 'image')) return $this->thumbnailVideo($incomingFile, $outgoingFilename, $outgoingPath, $desiredWidth, $desiredHeight);
				}
				else {
					errorManager_TL::addError("Unable to read incoming file and/or incompatible file format.");
					return false;
				}
			
		}
		
		public function convertImage($incomingFile, $outgoingFilename, $outgoingPath, $desiredWidth, $desiredHeight, $desiredAngle) {
	
			global $imageMagickRoot;
			global $extToMime_TL;
			
			$outgoingPath = trim ($outgoingPath, '/') . '/';
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
					errorManager_TL::addError("Unable to retrieve dimensions of currnet image.");
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
					$scale = floor($desiredWidth/$currentWidth);
					$desiredHeight = floor($scale * $currentHeight);
				}
				elseif ($desiredHeight && !$desiredWidth) {
					$scale = floor($desiredHeight/$currentHeight);
					$desiredWidth = floor($scale * $currentWidth);
				}
				else {
					if ($currentWidth > $currentHeight) $currentWidth = $currentHeight;
					elseif ($currentHeight > $currentWidth) $currentHeight = $currentWidth;
				}
				
			// create new image
				if ($imageMagickRoot && file_exists($imageMagickRoot . 'convert')) {
					$convert = $convertWithImageMagick . 'convert';
					$convert .= ' -thumbnail'; // type of conversion
					if ($desiredAngle) $convert .= ' -rotate ' . $desiredAngle;
					$convert .= ' ' . $desiredWidth . 'x' . $desiredHeight; // dimensions of output file
					$convert .= ' ' . escapeshellarg($incomingFile); // input file
					$convert .= ' ' . escapeshellarg($outgoingPath . $outgoingFilename); // output file
					exec ($convert, $output, $return_var);
				}

			// verify complete
				if (!file_exists($outgoingPath . $outgoingFilename)) {
					if ($desiredAngle) {
						errorManager_TL::addError("Unable to convert image using ImageMagick.");
						return false;
					}
					else {
						// calculate transformation
							if ($img = @imagecreatefromjpeg($incomingFile)) $img = imagecreatefromjpeg($incomingFile);
							elseif ($img = @imagecreatefromgif($incomingFile)) $img = imagecreatefromgif($incomingFile);
							elseif ($img = @imagecreatefrompng($incomingFile)) $img = imagecreatefrompng($incomingFile);
							else {
								errorManager_TL::addError("Unable to read image format.");
								return false;
							}
							
						// create a temporary image
							$newImage = imagecreatetruecolor($desiredWidth, $desiredHeight);
							imagealphablending($newImage, false);
							imagesavealpha($newImage, true);  
							
						// copy and resize the old image into the new image
							imagecopyresampled($newImage, $img, 0, 0, 0, 0, $desiredWidth, $desiredHeight, $currentWidth, $currentHeight);
							imagedestroy($img);
							if ($outgoingExt == 'jpg') imagejpeg($newImage, $outgoingPath . $outgoingFilename, 80);
							elseif ($outgoingExt == 'png') imagepng($newImage, $outgoingPath . $outgoingFilename, 0);
							elseif ($outgoingExt == 'gif') imagegif($newImage, $outgoingPath . $outgoingFilename);
							else {
								errorManager_TL::addError("Unable to determine destination file type.");
								return false;
							}
							
						// check for errors again
							if (file_exists($outgoingPath . $outgoingFilename)) return true;
							else {
								errorManager_TL::addError("Unknown problem attempting to create new image.");
								return false;
							}
									
					}
				}
			
		}
		
		public function convertVideo($incomingFile, $outgoingFilename, $outgoingPath, $desiredWidth, $desiredHeight) {
			
			global $imageMagickRoot;
			global $FFmpegRoot;

			// check for errors
				if (!$desiredWidth && !$desiredHeight) {
					errorManager_TL::addError("Missing desired dimensions.");
					return false;
				}
			
			// if one dimension unspecified, choose a 4:3 aspect ratio
				if (!$desiredWidth) $desiredWidth = intval($desiredHeight * 4 / 3);
				if (!$desiredHeight) $desiredHeight = intval($desiredWidth * 3 / 4);
				
			// ensure each dimension is a multiple of two
				$desiredWidth = floor(($desiredWidth) / 2) * 2;
				$desiredHeight = floor(($desiredHeight) / 2) * 2;
				
			// select converter
				if ($imageMagickRoot && file_exists($imageMagickRoot . 'convert')) {
					
					// create new image
						$convert = $imageMagickRoot . 'convert';
						$convert .= ' -resize'; // type of conversion
						$convert .= ' ' . $desiredWidth . 'x' . $desiredHeight; // dimensions of output file
						$convert .= ' ' . escapeshellarg($incomingFile); // input file
						$convert .= ' ' . escapeshellarg($outgoingPath . $outgoingFilename); // output file
						exec ($convert, $output, $return_var);
				
						if (file_exists($outgoingPath . $outgoingFilename)) return true;
						else {
							errorManager_TL::addError("Unknown problem attempting to create new preview video with ImageMagick.");
							return false;
						}
						
				}
				elseif ($FFmpegRoot && file_exists($FFmpegRoot . 'ffmpeg')) {
					
					// file extension must be lowercase
						if (pathinfo($incomingFile, PATHINFO_EXTENSION) != strtolower(pathinfo($incomingFile, PATHINFO_EXTENSION))) {
							errorManager_TL::addError("Please make sure file extension of incoming file is lowercase.");
							return false; 
						}
						
					// create new image
						$convert = $FFmpegRoot . 'ffmpeg';
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
							errorManager_TL::addError("Unknown problem attempting to create new preview video with FFmpeg.");
							return false;
						}
						
				}
				else {
					errorManager_TL::addError("No video converter found.");
					return false;
				}
			
		}
		
		public function thumbnailVideo($incomingFile, $outgoingFilename, $outgoingPath, $desiredWidth, $desiredHeight) {
	
			global $imageMagickRoot;
			
			// check for errors
				if ($imageMagickRoot && !file_exists($imageMagickRoot . 'convert')) {
					errorManager_TL::addError("Can't find ImageMagick on this server.");
					return false;
				}
			
			// check for errors
				if (!$desiredWidth && !$desiredHeight) {
					errorManager_TL::addError("Missing desired dimensions.");
					return false;
				}
			
			// if one dimension unspecified, choose a 4:3 aspect ratio
				if (!$desiredWidth) $desiredWidth = intval($desiredHeight * 4 / 3);
				if (!$desiredHeight) $desiredHeight = intval($desiredWidth * 3 / 4);
								
			// create new image
				$convert = $imageMagickRoot . 'convert';
				$convert .= ' -thumbnail'; // type of conversion
				$convert .= ' ' . $desiredWidth . 'x' . $desiredHeight; // dimensions of output file
				$convert .= ' ' . escapeshellarg($incomingFile) . '[0]'; // input file
				$convert .= ' ' . escapeshellarg($outgoingPath . $outgoingFilename); // output file
				exec ($convert, $output, $return_var);
		
				if (file_exists($outgoingPath . $outgoingFilename)) return true;
				else {
					errorManager_TL::addError("Unknown problem attempting to thumbnail video.");
					return false;
				}
			
		}
		
	}

/*	
	Media Converter

	::	DESCRIPTION
	
		Miscellaneous functions for converting one file or value to another

	::	DEPENDENT ON
	
		metadata_TL
		fileManager_TL
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
