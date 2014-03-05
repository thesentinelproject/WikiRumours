<?php

	class urlShortener_TL {

		public function bitly($longUrl) {
			
			global $bitlyApiKey;
			global $bitlyLogin;
	
			if (!$longUrl) {
				errorManager_TL::addError("No URL specified.");
				return false;
			}
			if (!$bitlyApiKey || !$bitlyLogin) {
				errorManager_TL::addError("No bit.ly credentials specified in the configuration file.");
				return false;
			}
			
			$longUrl = "http://api.bit.ly/v3/shorten?login=" . $bitlyLogin . "&apiKey=" . $bitlyApiKey . "&uri=" . urlencode($longUrl) . "&format=txt";
			
			$fileManager = new fileManager_TL();
			
			if ($fileManager->doesUrlExist($longUrl)) {
				$handle = @fopen($longUrl, 'r');
				$shortUrl = @stream_get_contents($handle);
				@fclose($handle);
				return $shortUrl;
			}
			else {
				errorManager_TL::addError("Unable to retrieve and/or access shortened URL.");
				return false;
			}
						
		}
		
		public function owly($longUrl) {
			
			global $owlyApiKey;
	
			if (!$longUrl) {
				errorManager_TL::addError("No URL specified.");
				return false;
			}
			if (!$owlyApiKey) {
				errorManager_TL::addError("No ow.ly credentials specified in the configuration file.");
				return false;
			}
			
			$longUrl = "http://ow.ly/api/1.1/url/shorten?apiKey=" . $owlyApiKey . "&longUrl=" . urlencode($longUrl);
			
			$fileManager = new fileManager_TL();
			
			if ($fileManager->doesUrlExist($longUrl)) {
				$handle = @fopen($longUrl, 'r');
				$shortUrl = @stream_get_contents($handle);
				@fclose($handle);
				$shortUrl = json_decode($shortUrl, true);
				return $shortUrl['results']['shortUrl'];
			}
			else {
				errorManager_TL::addError("Unable to retrieve and/or access shortened URL.");
				return false;
			}
			
		}
		
		public function customAlphaID($type, $lengthInCharacters, $blacklistArray, $allowMixedCase = true, $excludeAmbiguousCharacters = true) {
			
			/* Creates a unique Bitly-like whitelist identifier */
			
			// check for errors
				if ($type != 'a' && $type != 'n') {
					errorManager_TL::addError("Neither numeric nor alphanumeric specified.");
					return false;
				}
				if (intval($lengthInCharacters) < 1 ) {
					errorManager_TL::addError("String length not specified.");
					return false;
				}
				
			// determine characters to choose from
				if ($type == 'a') {
					if ($allowMixedCase) $source = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
					else $source = 'abcdefghijkmnopqrstuvwxyz23456789';
					
					if (!$excludeAmbiguousCharacters) {
						if ($allowMixedCase) $source .= '01OlI';
						else $source .= '01OI';
					}
				}
				else $source = '0123456789';
	
			// create ID and compare against blacklist
				$id = '';
				
				while (!$id || $matchedBlacklist) {
					
					for ($counter = 0; $counter < $lengthInCharacters; $counter++) {
						$randomCharacter = substr($source, rand(0, strlen($source) - 1), 1);
						$id .= $randomCharacter;
					}
					
					if ($blacklistArray) {
						$matchedBlacklist = false;
						foreach ($blacklistArray as $key => $value) {
							if ($id == $value) $matchedBlacklist = true;
						}
					}
					
				}
			
			// return array
				return $id;
				
		}
		
	}

/*
	URL Shortener

	::	DESCRIPTION
	
		Converts a long URL into a short URL

	::	DEPENDENT ON
	
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
