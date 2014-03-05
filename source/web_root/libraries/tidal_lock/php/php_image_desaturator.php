<?php

	if (isset($_REQUEST["src"])) {

	// include(s)
		include 'is_image_v2-1.php';
	
	// get image type
		$fileManager = new fileManager_TL();
		$imageType = $fileManager->isImage($_REQUEST["src"]);
	
	// create new image
		if ($imageType == 'JPG') $img = imagecreatefromjpeg($_REQUEST["src"]);
		elseif ($imageType == 'GIF') {
			$img = imagecreatefromgif($_REQUEST["src"]);
			imagealphablending($img, true);
			imagesavealpha($img, true);
		}
		elseif ($imageType == 'PNG') {
			$img = imagecreatefrompng($_REQUEST["src"]);
			imagealphablending($img, true);
			imagesavealpha($img, true);
		}

	// apply grayscale filter
		imagefilter($img, IMG_FILTER_GRAYSCALE);

	// set content type and deliver image
		if ($imageType == 'JPG') {
			header("Content-type: image/jpeg");
			imagejpeg($img, '', 50);
		}
		elseif ($imageType == 'GIF') {
			header("Content-type: image/gif");
			imagegif($img, '');
		}
		elseif ($imageType == 'PNG') {
			header("Content-type: image/png");
			imagepng($img, '', 5);
		}
		
}

/*
	PHP Image Desaturator

	::	DESCRIPTION
	
		Creates a desaturated image using basic PHP methods, unlike alternate
		JS/CSS approaches which tend to leave a bigger footprint in the
		markup. Not ideal for large images or memory-intensive operations.

		Use the path of this script in an image src URL, e.g.
		<img src="{path to script}/php_image_desaturator.php?src={relative path to image being desaturated}" />

	::	DEPENDENT ON
	
		fileManager_TL

	::	RETURNS
	
		Raw stream of desaturated image
		
	::	VERSION HISTORY

	::	LICENSE
	
		Copyright (C) 2010-2013
		Timothy Quinn / Tidal Lock / Consolidated Biro
		
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
