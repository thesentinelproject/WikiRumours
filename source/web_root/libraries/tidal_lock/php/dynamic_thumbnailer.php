<?php

	if (isset($_REQUEST["source"])) {

	// get parameters
		$source = $_REQUEST["source"];
		$desiredWidth = floatval(@$_REQUEST["desired_width"]);
		$desiredHeight = floatval(@$_REQUEST["desired_height"]);
		
	// validate image
		if (!file_exists($source)) die("Can't find source image.");
		$currentDimensions = @getimagesize($source);
		$currentWidth = @$currentDimensions[0];
		$currentHeight = @$currentDimensions[1];
		
		if ($currentWidth && $currentHeight) {
			
			// supply missing dimensions
				if (!$desiredWidth && !$desiredHeight) {
					$desiredWidth = $currentWidth;
					$desiredHeight = $currentHeight;
				}
				elseif ($desiredWidth && !$desiredHeight) $desiredHeight = 999999999;
				elseif ($desiredHeight && !$desiredWidth) $desiredWidth = 999999999;
				
			// prevent upscaling
				if ($currentDimensions[0] < $desiredWidth) $desiredWidth = $currentDimensions[0];
				if ($currentDimensions[1] < $desiredHeight) $desiredHeight = $currentDimensions[1];

			// create destination dimensions
				$scale = min($desiredWidth/$currentWidth, $desiredHeight/$currentHeight);
				$newWidth = floor($scale * $currentWidth);
				$newHeight = floor($scale * $currentHeight);
				
			// create new image
				if ($img = @imagecreatefromjpeg($source)) $img = imagecreatefromjpeg($source);
				elseif ($img = @imagecreatefromgif($source)) $img = imagecreatefromgif($source);
				elseif ($img = @imagecreatefrompng($source)) $img = imagecreatefrompng($source);
	
			// create a temporary image
				$newImage = imagecreatetruecolor($newWidth, $newHeight);
				
			// copy and resize the old image into the new image
				imagecopyresampled($newImage, $img, 0,0,0,0, $newWidth, $newHeight, $currentWidth, $currentHeight);
				imagedestroy($img);
				if ($img = @imagecreatefromjpeg($source)) {
					header("Content-type: image/jpeg");
					imagejpeg($newImage, '', 80);
				}
				elseif ($img = @imagecreatefromgif($source)) {
					header("Content-type: image/png");
					imagepng($newImage, '', 80);
				}
				elseif ($img = @imagecreatefrompng($source)) {
					header("Content-type: image/gif");
					imagegif($newImage, '');
				}
				
		}
		else die("Unable to parse source image.");
				
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
