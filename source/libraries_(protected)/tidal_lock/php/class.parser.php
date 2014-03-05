<?php

	class parser_TL {

/*		--------------------------------------
		Parsing URL
		-------------------------------------- */
		
			public function parseURL($url, $retrieveHtml = false, $sanitizeHtml = true) {
				
				$metadata = array();
				$metadata['error'] = '';
				$fileManager = new fileManager_TL();
				
				// check for errors
					if (!$url) {
						errorManager_TL::addError("No URL specified.");
						return false;
					}
									
				// parse URL structure
					$parseUrl = parse_url($url);
					$metadata['protocol'] = $parseUrl['scheme'];
					if ($metadata['protocol'] == 'https' || $metadata['protocol'] == 'ftps') $metadata['ssl'] = true;
					else $metadata['ssl'] = false;
					if (substr_count($parseUrl['host'], '.') == 1) $metadata['domain'] = $parseUrl['host'];
					elseif (substr_count($parseUrl['host'], '.') == 2) {
						$metadata['subdomain'] = substr($parseUrl['host'], 0, strpos($parseUrl['host'], '.'));
						$metadata['domain'] = substr($parseUrl['host'], strpos($parseUrl['host'], '.') + 1);
					}
					$metadata['path'] = trim($parseUrl['path'], '/');
					$metadata['query_string'] = $parseUrl['query'];
					
				// check if URL exists
					if ($fileManager->doesUrlExist($url)) $metadata['is_real'] = true;
					else $metadata['error'] .= "Unable to retrieve webpage; URL may be invalid, or website might be down. ";
		
				// check URL headers
					if ($fileManager->isHeaderValid($url)) $metadata['valid_headers'] = true;
					else $metadata['error'] .= "URL headers are invalid; the webpage may be down or redirecting. ";
		
				// retrieve HTML
					if ($retrieveHtml) {
						$html = @file_get_contents($url);
						if (!$html) {
							$removeSSL = str_replace('https', 'http', $url);
							if (!$html) {
								$handle = @fopen($removeSSL, 'r');
								$html = @stream_get_contents($handle);
								if (!$html) $html = @fread($handle, @filesize($removeSSL));
								@fclose($handle);
							}
						}
						if ($sanitizeHtml) $html = htmlspecialchars($html, ENT_QUOTES);
						if (!$html) $metadata['error'] .= "No content found on this webpage. ";
						
						// retrieve page title
						    $matches = array();
						    if (preg_match('/&lt;title&gt;(.*?)&lt;\/title&gt;/', $html, $matches)) $metadata['title'] = trim(preg_replace("/[\t\s]+/", " ", $matches[1]));
						    if (!$metadata['title']) $metadata['title'] = trim(preg_replace("/[\t\s]+/", " ", $this->extractFromHTML($html, '<title>', '</title>')));
						    if (!$metadata['title']) $metadata['title'] = trim(preg_replace("/[\t\s]+/", " ", $this->extractFromHTML($html, '&lt;title&gt;', '&lt;/title&gt;')));
						    
						// check if RSS
							if (substr_count($html, '<rss') > 0 || substr_count($html, '<feed') > 0) $metadata['is_RSS'] = true;
							
						// add HTML to array
							$metadata['HTML'] = $html;
							
					}
					
				// parse social media IDs
					if ($metadata['domain'] == 'facebook.com' || $metadata['domain'] == 'fb.me') {
						// facebook
							$metadata['is_facebook'] = true;
							if (substr($metadata['path'], 0, 6) == 'pages/') {
								$metadata['is_facebook_page'] = true;
								$suffix = substr($metadata['path'], 6);
								$end = strpos($suffix, '/');
								if (!$end) $end = strlen($suffix);
								$metadata['facebook_page'] = substr($suffix, 0, $end);
							}
							else {
								$metadata['is_facebook_account'] = true;
								$end = strpos($metadata['path'], '/');
								if (!$end) $end = strlen($metadata['path']);
								$metadata['facebook_newsfeed'] = substr($metadata['path'], 0, $end);
							}
					}
					elseif ($metadata['domain'] == 'linkedin.com' || $metadata['domain'] == 'lknd.in') {
						// linkedin
							$metadata['is_linkedin'] = true;
							if (substr($metadata['path'], 0, 8) == 'company/') {
								$metadata['is_linkedin_company_page'] = true;
								$suffix = substr($metadata['path'], 8);
								$end = strpos($suffix, '/');
								if (!$end) $end = strlen($suffix);
								$metadata['linkedin_company_page'] = substr($suffix, 0, $end);
							}
							elseif (substr($metadata['path'], 0, 3) == 'in/') {
								$metadata['is_linkedin_employee_profile'] = true;
								$suffix = substr($metadata['path'], 3);
								$end = strpos($suffix, '/');
								if (!$end) $end = strlen($suffix);
								$metadata['linkedin_profile_page'] = substr($suffix, 0, $end);
							}
							elseif (substr($metadata['path'], 0, 4) == 'pub/') {
								$metadata['is_linkedin_employee_profile'] = true;
								$suffix = substr($metadata['path'], 4);
								$end = strpos($suffix, '/');
								if (!$end) $end = strlen(suffix);
								$metadata['linkedin_profile_page'] = substr($suffix, 0, $end);
							}
							elseif (substr($metadata['path'], 0, 8) == 'profile/') {
								$metadata['error'] = "Improper LinkedIn URL.";
							}
					}
					elseif ($metadata['domain'] == 'twitter.com' || $metadata['domain'] == 't.co') {
						// twitter
							$metadata['is_twitter'] = true;
							if (substr($metadata['path'], 0, 3) == '#!/') {
								$suffix = substr($metadata['path'], 3);
								$end = strpos($suffix, '/');
								if (!$end) $end = strlen($suffix);
								$metadata['twitter_username'] = substr($suffix, 0, $end);
							}
							else{
								$end = strpos($metadata['path'], '/');
								if (!$end) $end = strlen($metadata['path']);
								$metadata['twitter_username'] = substr($metadata['path'], 0, $end);
							}
					}
					elseif (($metadata['domain'] == 'google.com' && $metadata['subdomain'] == 'plus') || $metadata['domain'] == 'goo.gl') {
						// google plus
							$metadata['is_googleplus'] = true;
							$end = strpos($metadata['path'], '/');
							if (!$end) $end = strlen($metadata['path']);
							$metadata['google_id'] = substr($metadata['path'], 0, $end);
					}
					
				// parse video IDs
					if ($metadata['domain'] == 'youtube.com' || $metadata['domain'] == 'youtu.be') {
						// youtube
							$metadata['is_video'] = true;
							parse_str($metadata['query_string'], $query);
							$metadata['youtube_id'] = $query['v'];
					}
					elseif ($metadata['domain'] == 'vimeo.com') {
						// vimeo
							$metadata['is_video'] = true;
							$metadata['vimeo_id'] = substr(str_replace('http://vimeo.com/', '', $url), 0, 8);
					}
					elseif ($metadata['domain'] == 'dailymotion.com') {
						// dailymotion
							$metadata['is_video'] = true;
							$metadata['daily_motion_id'] = substr(str_replace('http://www.dailymotion.com/video/', '', $url), 0, 6);
					}
					
				// return metadata
					return $metadata;
				
			}
			
		public function activateURLs($inputString, $newBrowser = false, $class = false) {

			if (!$inputString) {
				errorManager_TL::addError("No input specified.");
				return false;
			}
			
			if ($newBrowser) $target = " target='_blank'";
			if ($class) $class = " class='" . $class . "'";
				
			// activate HTTPS, HTTP, FTPS and FTP

				$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
				$urls = array();
				$urlsToReplace = array();
				if(preg_match_all($reg_exUrl, $inputString, $urls)) {
					$numOfMatches = count($urls[0]);
					$numOfUrlsToReplace = 0;
					for($matchCounter = 0; $matchCounter < $numOfMatches; $matchCounter++) {
						$alreadyAdded = false;
						$numOfUrlsToReplace = count($urlsToReplace);
						for($urlCounter = 0; $urlCounter < $numOfUrlsToReplace; $urlCounter++) {
							if($urlsToReplace[$urlCounter] == $urls[0][$matchCounter]) $alreadyAdded = true;
						}
						if(!$alreadyAdded) array_push($urlsToReplace, $urls[0][$matchCounter]);
					}
					$numOfUrlsToReplace = count($urlsToReplace);
					for($urlCounter = 0; $urlCounter < $numOfUrlsToReplace; $urlCounter++) {
						$inputString = str_replace($urlsToReplace[$urlCounter], "<a href='" . $urlsToReplace[$urlCounter] . "'" . $target . $class . ">" . $urlsToReplace[$urlCounter] . "</a> ", $inputString);
					}
				}
				
			// activate email addresses
				$emailAddresses = array();
				$words = explode(' ', $inputString);
				
				foreach ($words as $word) {
					$word = trim($word, '.!?;:/"()[]-=|' . "'");
					$validator = new inputValidator_TL();
					if ($validator->validateEmailBasic($word)) {
						$emailAddresses[$word] = $word;
					}
				}
				
				foreach ($emailAddresses as $email) {
					$inputString = str_replace($email, "<a href='mailto:" . $email . "'" . $class . ">" . $email . "</a>", $inputString);
				}

			// return modified string
				return $inputString;
			
		}
		
		public function activateTwitterHashesAndIDs($inputString, $newBrowser = false, $class = false) {
			
			if (!$inputString) {
				errorManager_TL::addError("No input specified.");
				return false;
			}
			
			if ($newBrowser) $target = " target='_blank'";
			if ($class) $class = " class='" . $class . "'";
			
			$inputString = preg_replace('/\#(\w+)/', "#<a href='http://search.twitter.com/search?q=$1'" . $target . $class . ">$1</a>", $inputString);
			$inputString = preg_replace('/\@(\w+)/', "@<a href='http://twitter.com/$1'" . $target . $class . ">$1</a>", $inputString);
	
			return $inputString;
			
		}
		
		public function extractImageURLs($html, $ignoreTrackingPixels = true) {
			
			$uniqueImageURLs = array();
			$imageURLs = array();
			$fileManager = new fileManager_TL();
			
			$dom = new domDocument;
			$dom->loadHTML($string);
			$dom->preserveWhiteSpace = false;
			$images = $dom->getElementsByTagName('img');

			foreach($images as $img) {
				if (!$ignoreTrackingPixels || ($ignoreTrackingPixels && !$fileManager->isTrackingPixelOrIcon($img->getAttribute('src')))) {
					$uniqueImageURLs[$img->getAttribute('src')] = $img->getAttribute('src');
				}
			}
			
			foreach ($uniqueImageURLs as $image) {
				$imageURLs[count($imageURLs)] = $image;
			}
			
			return $imageURLs;
    			
		}
		
		public function seoFriendlySuffix($input, $length = 10) {
			
			if (!$input) return false;
			else {
				$suffix = urldecode($input);
				$suffix = $this->strtolatin($suffix); // convert accented characters
				$suffix = strtolower($suffix); // convert to lowercase
				$suffix = $this->truncate($suffix, 'w', floatval($length)); // truncate to reasonable size
				$suffix = str_replace(' ', '-', $suffix); // remove spaces
				$suffix = $this->includeOrExcludeCharacters($suffix, 'abcdefghijklmnopqrstuvwxyz0123456789-'); // remove any remaining problematic characters
				
				return $suffix;
			}
			
		}
		
/*		--------------------------------------
		Parsing IP
		-------------------------------------- */
		
		function encodeIP($ip, $type = 'ipv4') {
			
			// check for errors
				if (!$ip || ($type != 'ipv4' && $type != 'ipv6')) return false;
				
			// encode for MySQL
				if ($type == 'ipv4') return ip2long($ip);
				elseif ($type == 'ipv6') return mysql_real_escape_string(inet_pton($ip));
			
		}
		
		function decodeIP($ip, $type = 'ipv4') {
			
			// check for errors
				if (!$ip || ($type != 'ipv4' && $type != 'ipv6')) return false;
				
			// encode for MySQL
				if ($type == 'ipv4') return long2ip($ip);
				elseif ($type == 'ipv6') return inet_ntop($ip);
			
		}
		
/*		--------------------------------------
		Parsing HTML
		-------------------------------------- */
		
		public function extractFromHTML($html, $openingDelimiter, $closingDelimiter) {
	
			// check for errors
				if (!$html) {
					errorManager_TL::addError("No HTML specified.");
					return false;
				}
			
			// locate start of string
				if ($openingDelimiter) {
					$startPoint = strpos($html, $openingDelimiter);
					if ($startPoint === false) {
						errorManager_TL::addError("Unable to find opening delimiter.");
						return false;
					}
					else $startPoint += strlen($openingDelimiter);
				}
				else $startPoint = 0;
	
			// locate end of string
				if ($closingDelimiter) {
					$endPoint = strpos($html, $closingDelimiter, $startPoint);
					if ($endPoint === false) {
						errorManager_TL::addError("Unable to find closing delimiter.");
						return false;
					}
				}
				else $endPoint = strlen($html);
	
			// return substring
				return substr($html, $startPoint, $endPoint - $startPoint);
			
		}
		
		public function removeHTML($inputString, $tagsToRemove = '') {
			
			/*	Removes specific HTML tags from a string. If no tags are specified, attempts to remove all tags
				Any tags specified for $tagsToRemove should be in the following format: 'br,img,h1'
				To remove all tags, pass an empty value into $tagsToRemove */
	
			if (!$inputString) {
				errorManager_TL::addError("No input specified.");
				return false;
			}
			
			if ($tagsToRemove) {
				$tags = explode(',', $tagsToRemove);
				foreach ($tags as $key => $value) {
					if (trim($value)) {
						$outputString = '';
						$copyToOutput = 'Y';
						// check for opening tag
							for ($counter = 0; $counter < strlen($inputString); $counter++) {
								$tagPrefix = '<' . $value;
								if (substr($inputString, $counter - 1, 1) == '>') $copyToOutput = 'Y';
								if (substr($inputString, $counter, strlen($tagPrefix)) == $tagPrefix) $copyToOutput = 'N';
								if ($copyToOutput == 'Y') $outputString .= substr($inputString, $counter, 1);
							}
							$inputString = $outputString;
							$outputString = '';
						// check for closing tag
							for ($counter = 0; $counter < strlen($inputString); $counter++) {
								$tagPrefix = '</' . $value;
								if (substr($inputString, $counter - 1, 1) == '>') $copyToOutput = 'Y';
								if (substr($inputString, $counter, strlen($tagPrefix)) == $tagPrefix) $copyToOutput = 'N';
								if ($copyToOutput == 'Y') $outputString .= substr($inputString, $counter, 1);
							}
							$inputString = $outputString;
					}
				}
				$ouputString = $inputString;
			}
			else {
				$outputString = '';
				$copyToOutput = 'Y';
				for ($counter = 0; $counter < strlen($inputString); $counter++) {
					if (substr($inputString, $counter - 1, 1) == '>') $copyToOutput = 'Y';
					if (substr($inputString, $counter, 1) == '<') $copyToOutput = 'N';
					if ($copyToOutput == 'Y') $outputString .= substr($inputString, $counter, 1);
				}
				
			}
			
			return $outputString;
			
		}
		
/*		--------------------------------------
		Parsing XML / RSS
		-------------------------------------- */
		
		public function parseRSS($url, $xmlStream, $get_attributes = 1, $priority = 'tag') {
		
			/*	this is simply a wrapper for parseXML() which
				correctly interprets a single-item RSS feed */
			
			if (!$url && !$xmlStream) {
				errorManager_TL::addError("No input specified.");
				return false;
			}
			
			$xmlArray = $this->parseXML($url, $xmlStream, $get_attributes = 1, $priority = 'tag');
				
			if ($xmlArray['rss']['channel']['item']['title']) {
				$xmlArray['rss']['channel']['item'][0] = array();
				foreach ($xmlArray['rss']['channel']['item'] AS $label => $value) {
					$xmlArray['rss']['channel']['item'][0][$label] = $value;
					if ($label) unset ($xmlArray['rss']['channel']['item'][$label]);
				}
			}
			
			return $xmlArray;
			
		}
	
		public function parseXML($url, $xmlStream, $get_attributes = 1, $priority = 'tag') {

			if (!$url && !$xmlStream) {
				errorManager_TL::addError("No input specified.");
				return false;
			}
			
			$characterEncoding = 'UTF-8';
			
			$contents = "";
		    if (!function_exists('xml_parser_create'))
		    {
			   return array ();
		    }
		    $parser = xml_parser_create('');
			    
			if ($url) {
			    if (!($fp = @ fopen($url, 'rb')))
			    {
				   return array ();
			    }
			    while (!feof($fp))
			    {
				   $contents .= fread($fp, 8192);
			    }
			    fclose($fp);
			}
			else {
			    $contents = $xmlStream;
			}
			xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, $characterEncoding);
		    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		    xml_parse_into_struct($parser, trim($contents), $xml_values);
		    xml_parser_free($parser);
		    if (!$xml_values)
			   return; //Hmm...
		    $xml_array = array ();
		    $parents = array ();
		    $opened_tags = array ();
		    $arr = array ();
		    $current = & $xml_array;
		    $repeated_tag_index = array ();
		    foreach ($xml_values as $data)
		    {
			   unset ($attributes, $value);
			   extract($data);
			   $result = array ();
			   $attributes_data = array ();
			   if (isset ($value))
			   {
				  if ($priority == 'tag')
					 $result = $value;
				  else
					 $result['value'] = $value;
			   }
			   if (isset ($attributes) and $get_attributes)
			   {
				  foreach ($attributes as $attr => $val)
				  {
					 if ($priority == 'tag')
						$attributes_data[$attr] = $val;
					 else
						$result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
				  }
			   }
			   if ($type == "open")
			   {
				  $parent[$level -1] = & $current;
				  if (!is_array($current) or (!in_array($tag, array_keys($current))))
				  {
					 $current[$tag] = $result;
					 if ($attributes_data)
						$current[$tag . '_attr'] = $attributes_data;
					 $repeated_tag_index[$tag . '_' . $level] = 1;
					 $current = & $current[$tag];
				  }
				  else
				  {
					 if (isset ($current[$tag][0]))
					 {
						$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
						$repeated_tag_index[$tag . '_' . $level]++;
					 }
					 else
					 {
						$current[$tag] = array (
						    $current[$tag],
						    $result
						);
						$repeated_tag_index[$tag . '_' . $level] = 2;
						if (isset ($current[$tag . '_attr']))
						{
						    $current[$tag]['0_attr'] = $current[$tag . '_attr'];
						    unset ($current[$tag . '_attr']);
						}
					 }
					 $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
					 $current = & $current[$tag][$last_item_index];
				  }
			   }
			   elseif ($type == "complete")
			   {
				  if (!isset ($current[$tag]))
				  {
					 $current[$tag] = $result;
					 $repeated_tag_index[$tag . '_' . $level] = 1;
					 if ($priority == 'tag' and $attributes_data)
						$current[$tag . '_attr'] = $attributes_data;
				  }
				  else
				  {
					 if (isset ($current[$tag][0]) and is_array($current[$tag]))
					 {
						$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
						if ($priority == 'tag' and $get_attributes and $attributes_data)
						{
						    $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
						}
						$repeated_tag_index[$tag . '_' . $level]++;
					 }
					 else
					 {
						$current[$tag] = array (
						    $current[$tag],
						    $result
						);
						$repeated_tag_index[$tag . '_' . $level] = 1;
						if ($priority == 'tag' and $get_attributes)
						{
						    if (isset ($current[$tag . '_attr']))
						    {
							   $current[$tag]['0_attr'] = $current[$tag . '_attr'];
							   unset ($current[$tag . '_attr']);
						    }
						    if ($attributes_data)
						    {
							   $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
						    }
						}
						$repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
					 }
				  }
			   }
			   elseif ($type == 'close')
			   {
				  $current = & $parent[$level -1];
			   }
		    }
		    return ($xml_array);
		}
		
/*		--------------------------------------
		Parsing DB resource
		-------------------------------------- */
		
		public function mySqliResourceToArray($resource, $htmlentities = false) {
	
			if (!$resource) {
				errorManager_TL::addError("No resource specified.");
				return false;
			}
			
			$items = array();
			
			for ($counter = 0; $counter < $resource->num_rows; $counter++) {
				$resourceDetails = $resource->fetch_assoc();
				$items[$counter] = array();
				foreach($resourceDetails AS $key => $value){
					if ($htmlentities) $items[$counter][$key] = htmlentities($value);
					else $items[$counter][$key] = $value;
				}
			}		
	
			return $items;
			
		}
		
/*		--------------------------------------
		Parsing date
		-------------------------------------- */
		
		public function calendarPageDate($datetime) {

			if (!$datetime) {
				errorManager_TL::addError("No date specified.");
				return false;
			}
			
			$output = "<div class='calendarPage'>";
			$output .= "<div class='calendarPageTop'>";
			$output .= "<span class='calendarPageMonth'>" . strtoupper(date('M', strtotime($datetime))) . "</span>";
			$output .= "<span class='calendarPageYear'>" . strtoupper(date('Y', strtotime($datetime))) . "</span>";
			$output .= "</div>";
			$output .= strtoupper(date('j', strtotime($datetime)));
			
			if (date('H', strtotime($datetime)) > 0) {
				$output .= "<div class='calendarPageBottom'>";
				$output .= strtoupper(date('g:i A', strtotime($datetime)));
				$output .= "</div>";
			}
			
			$output .= "</div>";
			
			return $output;
			
		}
		
		public function bubbleDate($date) {
			
			if (!$date) {
				errorManager_TL::addError("No date specified.");
				return false;
			}
			
			$output = "<div class='bubbleDate'>";
			$output .= "<div class='bubbleDay'>" . strtoupper(date('j', strtotime($date))) . "</div>";
			$output .= "<div class='bubbleMonthYear'>";
			$output .= "<span class='bubbleMonth'>" . strtoupper(date('M', strtotime($date))) . "</span>";
			$output .= "&nbsp;";
			$output .= "<span class='bubbleYear'>" . strtoupper(date('Y', strtotime($date))) . "</span>";
			$output .= "</div>";
			$output .= "<div class='floatClear'></div>";
			$output .= "</div>";
			
			return $output;
			
		}	
		
/*		--------------------------------------
		Parsing plaintext
		-------------------------------------- */
		
		public function addOrdinalSuffix($number) {

			if (!floatval($number)) {
				errorManager_TL::addError("No number specified.");
				return false;
			}
			
			if (substr($number, -1) == '1') return $number . "st";
			elseif (substr($number, -1) == '2') return $number . "nd";
			elseif (substr($number, -1) == '3') return $number . "rd";
			else return $number . "th";
				
		}
	    
		public function addFileSizeSuffix($filesizeInBytes) {

			if (!floatval($filesizeInBytes)) {
				errorManager_TL::addError("No filesize specified.");
				return false;
			}
			
			$b = floatval($filesizeInBytes);
			$kb = round($b / 1024, 1);
			$mb = round($kb / 1024, 1);
			$gb = round($mb / 1024, 1);
			$tb = round($gb / 1024, 1);
			$pb = round($tb / 1024, 1);
			$eb = round($pb / 1024, 1);
			$zb = round($eb / 1024, 1);
			$yb = round($zb / 1024, 1);

			if ($yb > 1) return $yb . " YB";
			if ($zb > 1) return $zb . " ZB";
			if ($eb > 1) return $eb . " EB";
			if ($pb > 1) return $pb . " PB";
			if ($tb > 1) return $tb . " TB";
			if ($gb > 1) return $gb . " GB";
			if ($mb > 1) return $mb . " MB";
			if ($kb > 1) return $kb . " KB";
			if ($b > 1) return $b . " B";
			
		}
		
		public function strtolatin($input) {
			/* Replaces accented characters with plaintext equivalents */
			return preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml|caron);~i', '$1', htmlentities($input, ENT_QUOTES, 'UTF-8'));
		}
		
		public function retrieveMeaningfulKeywords($input, $numberOfKeywords = null) {

			$result = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', $input, -1, PREG_SPLIT_NO_EMPTY);
			$result = array_unique($result);
			usort($result, array('parser_TL','sortValuesLargeToSmall'));
			
			return array_slice($result, 0, $numberOfKeywords);
		}
		
		private function sortValuesLargeToSmall($a,$b){
			return strlen($b) - strlen($a);
		}
		
		public function forceWrap($input, $length) {
			
			/* Inserts line breaks in a lengthy string where spaces aren't available to auto-wrap */
			
			$output = '';
			
			for ($counter = 0; $counter < strlen($input); $counter = $counter + $length) {
				$output .= substr($input, $counter, $length) . '<br />';
			}
			$output .= substr($input, $counter);
			
			if (substr($output, -6) == '<br />') $output = substr($output, 0, -6);
			
			return $output;
			
		}
		
		public function trimAll($array, $charlist = ' ') {
			
			/* Trims whitespace or other characters from all values in an array. Helpful for sanitizing all $_POST values on a page. */
			
			foreach($array as $key => $value) {
				if (is_array($value)) $array[$key] = $this->trimAll($array[$key], $charlist);
				else $array[$key] = trim($value, $charlist);
			}
			
			return $array;
			
		}

		public function truncate($inputText, $charactersOrWords = 'c', $desiredLength = 50, $moreLink = false, $moreClass = false, $moreLabel = false) {

			if (!$inputText) {
				errorManager_TL::addError("No text specified.");
				return false;
			}
			
			if ($inputText !== html_entity_decode($inputText)) $decodeRequired = true;
			else $decodeRequired = false;
			
			if ($charactersOrWords == 'c') {
				if ($decodeRequired) $inputText = html_entity_decode($inputText);
				if (strlen($inputText) > $desiredLength) {
					$outputText = substr($inputText, 0, $desiredLength) . "... ";
					if ($moreLink) $outputText .= "<a href='" . $moreLink . "' class='" . $moreClass . "'>Read more &raquo;</a>";
				}
				else $outputText = $inputText;
				if ($decodeRequired) $outputText = htmlentities($outputText);
			}
			
			elseif ($charactersOrWords == 'w') {
				if (substr_count($inputText, ' ') > $desiredLength) {
					$wordCounter = 0;
					$outputText = '';
					while ($wordCounter < $desiredLength) {
						$indexOfNextWord = stripos($inputText, ' ');
						$outputText .= substr($inputText, 0, $indexOfNextWord) . ' ';
						$inputText = substr($inputText, $indexOfNextWord + 1);
						$wordCounter++;
					}
					$outputText = trim($outputText) . "... ";
					if (!$moreLabel) $moreLabel = "&raquo;";
					if ($moreLink) $outputText .= "<a href='" . $moreLink . "' class='" . $moreClass . "'>" . $moreLabel . "</a>";
				}
				else $outputText = $inputText;
			}
	
			return trim($outputText);
	
		}
		
		public function sanitizeFromMicrosoftWord($input) {
			
			/* Addresses spotty conversion with htmlspecialchars_decode() and html_entity_decode() */

			$convert = array(
				chr(145)=>"'", // opening smart single quote
				chr(146)=>"'", // closing smart single quote
				chr(226) . chr(128) . chr(153)=>"'", // smart apostrophe
				chr(147)=>'"', // opening smart double quote
				chr(148)=>'"', // closing smart double quote
				chr(149)=>'*', // bullet
				chr(150)=>'-', // en dash
				chr(151)=>'--', // em dash
				chr(226) . chr(128) . chr(34)=>'--', // em dash
				chr(133)=>'...' // ellipsis
			);
			
			foreach ($convert as $old=>$new) {
				$input = str_replace($old, $new, $input);
			}
			
			return $input;
			
		}
		
		public function includeOrExcludeCharacters($input, $allowedCharacters = false, $disallowedCharacters = false) {
			
			/*	Sanitizes a string by returning only allowed
				characters and/or stripping out disallowed characters */
			
			$input = urldecode(htmlspecialchars_decode($input, ENT_QUOTES));
			$output = '';
			
			if ($allowedCharacters) {
				
				for ($includeCounter = 0; $includeCounter < strlen($input); $includeCounter++) {
					$foundIt = false;
					for($counter = 0; $counter < strlen($allowedCharacters); $counter++) {
						if (substr($input, $includeCounter, 1) == substr($allowedCharacters, $counter, 1) && !$foundIt) {
							$output .= substr($input, $includeCounter, 1);
							$foundIt = true;
						}
					}
				}
				
			}
			
			if ($disallowedCharacters) {
			
				if ($output) {
					$input = $output;
					$output = '';
				}
						
				for ($excludeCounter = 0; $excludeCounter < strlen($input); $excludeCounter++) {
					$addIt = true;
					for($counter = 0; $counter < strlen($disallowedCharacters); $counter++) {
						if (substr($input, $excludeCounter, 1) == substr($allowedCharacters, $counter, 1) && $addIt) {
							$foundIt = false;
						}
					}
					if ($addIt) $output .= substr($input, $excludeCounter, 1);
				}
				
			}
			
			return $output;
			
		}
		
	}

/*
	Parser

	::	DESCRIPTION
	
		Functions for parsing XML, HTML, URLs and plaintext

	::	DEPENDENT ON
	
		inputValidator_TL
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
