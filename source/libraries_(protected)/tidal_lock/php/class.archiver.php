<?php

	class compressor_TL {

		public function archive($destinationFile, $destinationPath, $sourcePath, $stripSubdirectories = false, $deleteSourcePath = false) {

			// validate existence of source and destination directories
				if (!file_exists($sourcePath)) {
					errorManager_TL::addError("Unable to locate source directory.");
					return false;
				}
				if (!file_exists($destinationPath)) {
					errorManager_TL::addError("Unable to locate destination directory.");
					return false;
				}
				if (!$this->isZipInstalled()) {
					errorManager_TL::addError("Info-zip does not appear to be installed on this server.");
					return false;
				}
			
			// append slash to destination path, if required
				$destinationPath = rtrim($destinationPath, '/') . '/';
			
			// make sure destination file is a zip and temporarily attach a random number to ensure it doesn't overwrite anything else
				$random = rand(10000,99999);
				$destinationFile = $random . '_' . rtrim($destinationFile, '.zip') . '.zip';
	
			// create the zip
				$execute = "zip -r";
				if ($stripSubdirectories) $execute .= " -j";
				$execute .= " " . $destinationFile;
				$execute .= " " . $sourcePath;
				exec($execute);
				
			// validate the zip
				if (!file_exists($destinationFile)) {
					errorManager_TL::addError("Unknown error attempting to create zip file.");
					return false;
				}
				else {
					// move zip file
						rename($destinationFile, $destinationPath . str_replace($random . '_', '', $destinationFile));
						if (!file_exists($destinationPath . str_replace($random . '_', '', $destinationFile))) {
							unlink ($destinationFile);
							errorManager_TL::addError("Unable to move zip to destination directory.");
							return false;
						}
	
					// delete pre-archived copies
						if ($deleteSourcePath == 'Y') {
							$fileManager = new directoryManager_TL();
							$success = $directoryManager->remove($sourcePath);
							if (!$success) {
								errorManager_TL::addError("Unable to delete source directory.");
								return false;
							}
						}
						
					return true;
						
				}
			
		}
	
		public function unarchive($file, $destinationDirectory, $deleteArchive = false) {
			
			// check for errors
				if (!$this->isZipInstalled()) {
					errorManager_TL::addError("Info-zip does not appear to be installed on this server.");
					return false;
				}
				if (!file_exists($file)) {
					errorManager_TL::addError("There was a problem locating your archive.");
					return false;
				}
				if (!file_exists($destinationDirectory)) {
					$success = mkdir($destinationDirectory);
					if (!$success) {
						errorManager_TL::addError("Unable to locate or create destination directory.");
						return false;
					}
				}
	
			// unarchive
				$execute = '';
				$splitFilename = pathinfo($file);
				$fileExtension = $splitFilename[extension];
				 
				if ($fileExtension == 'gz') $execute = "gunzip -" . $destinationDirectory . " " . $file;
				elseif ($fileExtension == 'zip') $execute = "unzip -u " . $file . " -d " . $destinationDirectory;
				elseif ($fileExtension == 'rar') $execute = "rar e -u " . $file . " -d " . $destinationDirectory;
			
				if ($execute) {
					exec($execute);
					if ($deleteArchive) {
						$success = unlink ($file);
						if ($success) {
							$fileManager = new directoryManager_TL();
							$unarchivedContent = $directoryManager->read($destinationDirectory, true, true, true);
							if (count($unarchivedContent) > 0) return true;
							else {
								errorManager_TL::addError("Unable to retrieve files from archive, which could be because the archive is damaged or because the compression is incompatible with the unarchiving tools on the server.");
								return false;
							}
						}
						else {
							errorManager_TL::addError("Unable to delete the file " . $file . ".");
							return false;
						}
					}
				}
				else {
					rmdir($destinationDirectory);
					errorManager_TL::addError(strtoupper($fileExtension) . " is not a recognized archive file.");
					return false;
				}
				
		}
	
		public function isZipInstalled() {
			$displayLicense = @exec("zip -L");
			if ($displayLicense) return true;
			else return false;
		}
		
	}

/*
	Archive and Unarchive

	::	DESCRIPTION
	
		Functions to archive and unarchive files using InfoZip

	::	DEPENDENT ON

		fileManager_TL

	::	MORE INFORMATION ON INFOZIP
		
		http://www.info-zip.org/mans/zip.html
		http://www.info-zip.org/mans/unzip.html

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
