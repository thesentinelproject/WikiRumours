<?php

	class directory_manager_TL {

		public function read($directory, $recursive = true, $returnDirectories = false, $returnFiles = true, $excludeRegex = '') {
	
			global $console;

	    	$directory = rtrim($directory, '/');
	    	if (!$directory) {
				$console .= __FUNCTION__ . ": No directory specified.\n";
	    		return false;
	    	}
	    	
	    	$result = array();
			$skipByExclude = false;
			$handle = opendir($directory);
	
			if ($handle) {
				while (false !== ($file = readdir($handle))) {
					preg_match("/(^(([\.]){1,2})$|(\.(svn|git|md))|(Thumbs\.db|\.DS_STORE))$/iu", $file, $skip);
					if ($excludeRegex){
						preg_match($excludeRegex, $file, $skipByExclude);
					}
					if (!$skip && !$skipByExclude) {
						if (is_dir($directory . DIRECTORY_SEPARATOR . $file)) {
							if($recursive) {
								$result = array_merge($result, $this->read($directory . DIRECTORY_SEPARATOR . $file, $recursive, $returnDirectories, $returnFiles, $excludeRegex));
							}
							if($returnDirectories){
								$file = $directory . DIRECTORY_SEPARATOR . $file;
								$result[] = $file;
							}
						}
						else {
							if($returnFiles){
								$file = $directory . DIRECTORY_SEPARATOR . $file;
								$result[] = $file;
							}
						}
					}
				}
				
				closedir($handle);
			}
			
			return $result;
			
		}	
		
		public function copy($sourcePath, $destinationPath) {
	
			global $console;

			// check if paths exist
				if (!$sourcePath) {
					$console .= __FUNCTION__ . ": No source path specified.\n";
					return false;
				}
				if (!file_exists($sourcePath)) {
					$console .= __FUNCTION__ . ": Unable to locate the source path " . $sourcePath . ".\n";
					return false;
				}
				if (!$destinationPath) {
					$console .= __FUNCTION__ . ": No destination path specified.\n";
					return false;
				}
				if (!file_exists($destinationPath)) {
					mkdir ($destinationPath);
					if (!file_exists($destinationPath)) {
						$console .= __FUNCTION__ . ": Unable to locate or create destination path.\n";
						return false;
					}
				}
	
			// open source directory
				$dir = opendir($sourcePath);
	
			// copy contents
				while (false !== ($file = readdir($dir))) {
			        if (($file != '.') && ($file != '..')) {
			            if (is_dir($sourcePath . '/' . $file)) $this->copy($sourcePath . '/' . $file, $destinationPath . '/' . $file);
			            else copy($sourcePath . '/' . $file, $destinationPath . '/' . $file);
			        }
			    }
	
		    // close directory
			    closedir($dir);
			    
		    return true;
		    
		}

		public function remove($dir) {
			
			global $console;

			if (!$dir) {
				$console .= __FUNCTION__ . ": No directory specified.\n";
				return false;
			}
			if (!file_exists($dir)) {
				$console .= __FUNCTION__ . ": Unable to locate the directory " . $dir . ".\n";
				return false;
			}
			if (!is_dir($dir) || is_link($dir)) {
				return unlink($dir);
			}
		
			foreach (scandir($dir) as $item) {
				if ($item == '.' || $item == '..') continue;
				if (!$this->remove($dir . "/" . $item)) {
					chmod($dir . "/" . $item, 0777);
					if (!$this->remove($dir . "/" . $item)) {
						$console .= __FUNCTION__ . ": Unable to delete the directory " . $dir . "/" . $item . ".\n";
						return false;
					}
				};
			}
			
			return rmdir($dir);
			
		}

	}
	
/*	
	Directory Manager

	::	DESCRIPTION
	
		Functions to manipulate folders

	::	DEPENDENT ON
	
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
