<?php

	class avatar_manager_TL {

		public function retrieveProfileImage($username, $addRandomToClearCache = true) {
			
			global $profileImageSizes;
			global $tl;
			
			if (!$username) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No username specified.\n";
				return false;
			}
			
			$image = array();
			$encryption = new encrypter_TL();
			$key = $encryption->quickEncrypt($username, $tl->salts['public_keys']);
	
			$image['key'] = $key;
			$image['sizes'] = array();
			
			if ($profileImageSizes) {
				foreach ($profileImageSizes as $type=>$width) {
					$url = 'assets/profile_images/' . $key . '_' . $type . '.jpg';
					if (file_exists($url)) {
						$image['sizes'][$type] = $url;
						if ($addRandomToClearCache) $image['sizes'][$type] .= '?random=' . rand(1, 99999);
					}
				}
			}
			
			return $image;
					
		}
		
		public function createProfileImage($username, $sourceImage) {
			
			global $profileImageSizes;
			global $tl;
			
			if (!$username) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No username specified.\n";
				return false;
			}
			if (!$sourceImage) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No source image specified.\n";
				return false;
			}
			
			$error = '';
			$encryption = new encrypter_TL();
			$key = $encryption->quickEncrypt($username, $tl->salts['public_keys']);

			$converter = new media_converter_TL();
			
			foreach ($profileImageSizes as $type=>$width) {
				$destinationFile = $key . '_' . $type . '.jpg';
				$destinationPath = 'assets/profile_images/';
				$success = $converter->convertImage($sourceImage, $destinationFile, $destinationPath, $width, $width, null);
				if (!file_exists($destinationPath . $destinationFile)) $error .= "Unable to create " . $type . " profile image .";
			}
			
			if ($error) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": " . $error . ".\n";
				return false;
			}
			else return true;
							
		}
		
		public function deleteProfileImage($username) {
			
			global $profileImageSizes;
			global $tl;
	
			if (!$username) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No username specified.\n";
				return false;
			}
			
			$error = '';
			$encryption = new encrypter_TL();
			$key = $encryption->quickEncrypt($username, $tl->salts['public_keys']);
	
			foreach ($profileImageSizes as $type=>$width) {
				$filePath = 'assets/profile_images/' . $key . '_' . $type . '.jpg';
				if (file_exists($filePath)) {
					$success = unlink($filePath);
					if (!$success || file_exists($filePath)) $error .= "Unable to delete " . $type . " profile image. ";
				}
			}
			
			if ($error) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": " . $error . ".\n";
				return false;
			}
			else return true;
								
		}
		
		public function retrieveGravatar($email, $width, $destination = false) {
			
			global $tl;

			if (!$email) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No email specified.\n";
				return false;
			}
			
			if (!floatval($width)) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No desired width specified.\n";
				return false;
			}
			

			$url = 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=404';
			if (floatval($width)) $url .= '&s=' . floatval($width);
			
			$fileManager = new file_manager_TL();
			
			if (!$fileManager->doesUrlExist($url)) return false;
			else {
				if ($destination) copy($url, $destination);
				return $url;
			}
			
		}
		
	}
	
/*
	Avatar Manager

	::	DESCRIPTION
	
		Functions for creating, displaying and deleting profile images
		in various sizes

	::	DEPENDENT ON
	
		encrypter_TL
		media_converter_TL
		file_manager_TL
	
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