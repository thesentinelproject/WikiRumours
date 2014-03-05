<?php

	class avatarManager_TL {

		public function retrieveProfileImage($username, $addRandomToClearCache = true) {
			
			global $salts_TL;
			global $profileImageSizes_TL;
			
			if (!$username) {
				errorManager_TL::addError("No username specified.");
				return false;
			}
			
			$image = array();
			$encryption = new encrypter_TL();
			$key = $encryption->quickEncrypt($username, $salts_TL['public_keys']);
	
			$image['key'] = $key;
			$image['sizes'] = array();
			
			if ($profileImageSizes_TL) {
				foreach ($profileImageSizes_TL as $type=>$width) {
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
			
			global $salts_TL;
			global $profileImageSizes_TL;
			
			if (!$username) {
				errorManager_TL::addError("No username specified.");
				return false;
			}
			if (!$sourceImage) {
				errorManager_TL::addError("No source image specified.");
				return false;
			}
			
			$error = '';
			$encryption = new encrypter_TL();
			$key = $encryption->quickEncrypt($username, $salts_TL['public_keys']);

			$converter = new mediaConverter_TL();
			
			foreach ($profileImageSizes_TL as $type=>$width) {
				$destinationFile = $key . '_' . $type . '.jpg';
				$destinationPath = 'assets/profile_images/';
				$success = $converter->convertImage($sourceImage, $destinationFile, $destinationPath, $width, $width, null);
				if (!file_exists($destinationPath . $destinationFile)) $error .= "Unable to create " . $type . " profile image .";
			}
			
			if ($error) {
				errorManager_TL::addError($error);
				return false;
			}
			else return true;
							
		}
		
		public function deleteProfileImage($username) {
			
			global $salts_TL;
			global $profileImageSizes_TL;
	
			if (!$username) {
				errorManager_TL::addError("No username specified.");
				return false;
			}
			
			$error = '';
			$encryption = new encrypter_TL();
			$key = $encryption->quickEncrypt($username, $salts_TL['public_keys']);
	
			foreach ($profileImageSizes_TL as $type=>$width) {
				$filePath = 'assets/profile_images/' . $key . '_' . $type . '.jpg';
				if (file_exists($filePath)) {
					$success = unlink($filePath);
					if (!$success || file_exists($filePath)) $error .= "Unable to delete " . $type . " profile image. ";
				}
			}
			
			if ($error) {
				errorManager_TL::addError($error);
				return false;
			}
			else return true;
								
		}
		
		public function retrieveGravatar($email, $width, $destination = false) {
			
			if (!$email) {
				errorManager_TL::addError("No email specified.");
				return false;
			}
			
			if (!floatval($width)) {
				errorManager_TL::addError("No desired width specified.");
				return false;
			}
			

			$url = 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=404';
			if (floatval($width)) $url .= '&s=' . floatval($width);
			
			$fileManager = new fileManager_TL();
			
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
		mediaConverter_TL
		fileManager_TL
	
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