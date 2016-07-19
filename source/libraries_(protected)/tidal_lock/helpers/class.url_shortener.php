<?php

	class url_shortener_TL {

		public function bitly($longUrl) {
			
			global $bitlyApiKey;
			global $bitlyLogin;
			global $tl;
	
			if (!$longUrl) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No URL specified.\n";
				return false;
			}
			if (!$bitlyApiKey || !$bitlyLogin) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No bit.ly credentials specified in the configuration file.\n";
				return false;
			}
			
			$longUrl = "http://api.bit.ly/v3/shorten?login=" . $bitlyLogin . "&apiKey=" . $bitlyApiKey . "&uri=" . urlencode($longUrl) . "&format=txt";
			
			$fileManager = new file_manager_TL();
			
			if ($fileManager->doesUrlExist($longUrl)) {
				$handle = @fopen($longUrl, 'r');
				$shortUrl = @stream_get_contents($handle);
				@fclose($handle);
				return $shortUrl;
			}
			else {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to retrieve and/or access shortened URL.\n";
				return false;
			}
						
		}
		
		public function owly($longUrl) {
			
			global $owlyApiKey;
			global $tl;
	
			if (!$longUrl) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No URL specified.\n";
				return false;
			}
			if (!$owlyApiKey) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No ow.ly credentials specified in the configuration file.\n";
				return false;
			}
			
			$longUrl = "http://ow.ly/api/1.1/url/shorten?apiKey=" . $owlyApiKey . "&longUrl=" . urlencode($longUrl);
			
			$fileManager = new file_manager_TL();
			
			if ($fileManager->doesUrlExist($longUrl)) {
				$handle = @fopen($longUrl, 'r');
				$shortUrl = @stream_get_contents($handle);
				@fclose($handle);
				$shortUrl = json_decode($shortUrl, true);
				return $shortUrl['results']['shortUrl'];
			}
			else {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to retrieve and/or access shortened URL.\n";
				return false;
			}
			
		}
		
		public function customAlphaID($type, $lengthInCharacters, $blacklistArray, $allowMixedCase = true, $excludeAmbiguousCharacters = true) {

			/* Creates a unique Bitly-like whitelist identifier */

			global $tl;
			
			// check for errors
				if ($type != 'a' && $type != 'n') {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Neither numeric nor alphanumeric specified.\n";
					return false;
				}
				if (intval($lengthInCharacters) < 1 ) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": String length not specified.\n";
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
				
				while (!$id || @$matchedBlacklist) {
					
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
