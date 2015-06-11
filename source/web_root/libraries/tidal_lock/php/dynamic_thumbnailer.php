<?php

	if (!@$_REQUEST["source"]) die("No source image specified.");
	else {

		// get parameters
			$source = $_REQUEST["source"];
			$desiredWidth = floatval(@$_REQUEST["desired_width"]);
			$desiredHeight = floatval(@$_REQUEST["desired_height"]);
			
		// validate image
			if (!file_exists($source)) die("Can't find source image.");
			$currentDimensions = @getimagesize($source);
			$currentWidth = @$currentDimensions[0];
			$currentHeight = @$currentDimensions[1];
			if (!$currentWidth || !$currentHeight) {
				$currentWidth = @imagesx($source);
				$currentHeight = @imagesy($source);
			}

			if (!$currentWidth || !$currentHeight) die("Unable to retrieve dimensions of current image.");
			else {
				
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
					if ($img = @imagecreatefromjpeg($source)) $img = imagecreatefromjpeg($source);
					elseif ($img = @imagecreatefrompng($source)) $img = imagecreatefrompng($source);
					elseif ($img = @imagecreatefromgif($source)) $img = imagecreatefromgif($source);
					else die("Unable to read image format.");
		
				// create a temporary image
					$newImage = imagecreatetruecolor($desiredWidth, $desiredHeight);
					imagealphablending($newImage, false);
					imagesavealpha($newImage, true);
				
				// copy and resize the old image into the new image
					imagecopyresampled($newImage, $img, 0, 0, intval(@$xOffset), intval(@$yOffset), $desiredWidth, $desiredHeight, $currentWidth, $currentHeight);
					imagedestroy($img);
					if ($img = @imagecreatefromjpeg($source)) {
						header("Content-type: image/jpeg");
						imagejpeg($newImage, null, 80);
					}
					elseif ($img = @imagecreatefrompng($source)) {
						header("Content-type: image/png");
						imagepng($newImage, null, 0);
					}
					elseif ($img = @imagecreatefromgif($source)) {
						header("Content-type: image/gif");
						imagegif($newImage, null);
					}
					
			}
				
	}

/*
	Dynamic Thumbnailer

	::	DESCRIPTION
	
		Creates a thumbnail on the fly from JPGs, PNGs or GIFs. Not ideal for 
		large images or memory-intensive operations.

		Use the path of this script in an image src URL, e.g.
		<img src="{path to script}/dynamic_thumbnailer.php?source={relative path to image being desaturated}&desired_width={width}&desired_height={height}" />

	::	DEPENDENT ON
	
	::	RETURNS
	
		Raw stream of image
		
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
