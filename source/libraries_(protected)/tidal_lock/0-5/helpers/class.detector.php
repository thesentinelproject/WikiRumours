<?php

	class detector_TL {

		public $browser = array();
		public $system = array();
		public $page = array();
		public $connection = array();

		public function detectAll() {
			$this->browser();
			$this->system();
			$this->page();
			$this->connection();
		}

		public function browser() {

			// browser and OS versions
				$this->browser['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
				$result = @get_browser($this->browser['user_agent'], true);
				if ($result) {
					$this->browser['os'] = @$result['platform'];
					$this->browser['browser'] = @$result['browser'];
					$this->browser['browser_version'] = @$result['version'];
					$this->browser['js_enabled'] = @$result['javascript'];
					$this->browser['cookies_enabled'] = @$result['cookies'];
				}
				else {
					$result = $this->parseUserAgent($_SERVER['HTTP_USER_AGENT']);
					$this->browser['os'] = @$result['os'];
					$this->browser['os_version'] = @$result['os_version'];
					$this->browser['browser'] = @$result['browser'];
					$this->browser['browser_version'] = @$result['browser_version'];
				}

			// localization and language
				$this->browser['localization'] = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5);
				if ($this->browser['localization']) {
					$result = retrieveSingleFromDb('languages', 'language', array('short_id'=>substr($this->browser['localization'], 0, 2)));
					if (count($result)) $this->browser['language'] = $result[0]['language'];
				}

		}

		public function system() {

			global $dbConnection;

			$this->system['server'] = str_replace('.', '.<wbr>', gethostbyaddr($_SERVER['SERVER_ADDR']));
			$this->system['php_version'] = phpversion();
			$this->system['mysql_version'] = $dbConnection->server_info;

		}

		public function page() {

			$this->page['root'] = trim($_SERVER['SERVER_NAME'], '/');
	
			if (substr_count(trim($_SERVER['SERVER_NAME'], '/'), '.') < 2) { // e.g. mydomain.com
				$this->page['domain'] = trim($_SERVER['SERVER_NAME'], '/');
			}
			else {
				$this->page['subdomain'] = substr(trim($_SERVER['SERVER_NAME'], '/'), 0, strpos(trim($_SERVER['SERVER_NAME'], '/'), '.')); // e.g. www
				$this->page['domain'] = substr(trim($_SERVER['SERVER_NAME'], '/'), strpos(trim($_SERVER['SERVER_NAME'], '/'), '.') + 1); // e.g. mydomain.com
			}
			
			if (@$_SERVER['HTTPS']) $this->page['protocol'] = 'https://';
			else $this->page['protocol'] = 'http://';
				/*
				 	This isn't a definitive test, and is dependent on proper server configuration. If it's failing, you should
				 	override this setting manually after this function has executed.
				 */
			
			$this->page['folder'] = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/")) . "/"; // e.g. /public/application/
			
			$this->page['page'] = substr($_SERVER['REQUEST_URI'], strrpos($_SERVER['REQUEST_URI'], "/") + 1); // e.g. index.php
			
			$this->page['query_string'] = $_SERVER['QUERY_STRING'];

		}

		public function connection() {

			if (!@$this->connection['ip']) {
				$this->connection['ip'] = @$_SERVER['REMOTE_ADDR'];
				if (!$this->connection['ip']) $this->connection['ip'] = @$_SERVER['REMOTE_HOST'];
			}

			if (strlen(@$this->connection['ip']) > 3) {
				$result = json_decode(file_get_contents("http://ipinfo.io/" . $this->connection['ip'] . "/json"));
				$this->connection['city'] = $result->city;
				$this->connection['country'] = $result->country;
				$this->connection['geocoordinates'] = $result->loc;
				if (substr_count($this->connection['geocoordinates'], ',') > 0) {
					$this->connection['latitude'] = substr($this->connection['geocoordinates'], 0, strpos($this->connection['geocoordinates'], ','));
					$this->connection['longitude'] = substr($this->connection['geocoordinates'], strpos($this->connection['geocoordinates'], ',') + 1);
					unset($this->connection['geocoordinates']);
				}
				$this->connection['isp'] = $result->org;
				if (!$this->connection['isp']) $this->connection['isp'] = $result->hostname;
			}

			$this->connection['outgoing_ip'] = @file_get_contents("http://ipecho.net/plain");
			if (!$this->connection['outgoing_ip']) {
				preg_match('/Current IP Address: \[?([:.0-9a-fA-F]+)\]?/', @file_get_contents('http://checkip.dyndns.com/'), $m);
				$this->connection['outgoing_ip'] = @$m[1];
			}

		}

		public function parseUserAgent($agent) { 

			$result = array();

		    // OS
				if (preg_match('/android/i', $agent))					$result['os'] = 'Android';
				elseif (preg_match('/iphone/i', $agent))				$result['os'] = 'iPhone';
				elseif (preg_match('/ipad/i', $agent))					$result['os'] = 'iPad';
				elseif (preg_match('/blackberry/i', $agent))			$result['os'] = 'Blackberry';
				elseif (preg_match('/linux/i', $agent))					$result['os'] = 'Linux';
				elseif (preg_match('/macintosh|mac os x/i', $agent))	$result['os'] = 'Macintosh';
				elseif (preg_match('/windows|win32/i', $agent))			$result['os'] = 'Windows';

			// OS version
				if (preg_match('/Windows NT 5.1/i', $agent))			$result['os_version'] = 'XP';
				elseif (preg_match('/Windows NT 6.0/i', $agent))		$result['os_version'] = 'Vista';
				elseif (preg_match('/Windows NT 6.1/i', $agent))		$result['os_version'] = '7';
				elseif (preg_match('/Windows NT 6.2/i', $agent))		$result['os_version'] = '8';
				elseif (preg_match('/Windows NT 6.3/i', $agent))		$result['os_version'] = '8.1';
				elseif (preg_match('/Windows NT 10.0/i', $agent))		$result['os_version'] = '10';
				else {
				    $known = array('Version', $result['os'], 'other');
				    $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
				    if (!preg_match_all($pattern, $agent, $matches)) {
				        // we have no matching number just continue
				    }
					// count results
						$i = count(@$matches['os']);
						if ($i != 1) { // We will have two since we are not using 'other' argument yet
							// see if version is before or after the name */
								if (strripos($agent,"Version") < strripos($agent, @$result['os'])) $result['os_version'] = @$matches['version'][0];
								else $result['os_version'] = @$matches['version'][1];
						}
						else @$result['os_version'] = @$matches['version'][0];

				}
		    
		    // Browser
			    if (preg_match('/MSIE/i', $agent) && !preg_match('/Opera/i',$agent))	$result['browser'] = "MSIE";
			    elseif (preg_match('/Firefox/i', $agent))								$result['browser'] = 'Firefox'; 
			    elseif (preg_match('/Chrome/i', $agent))								$result['browser'] = 'Chrome'; 
			    elseif (preg_match('/Safari/i', $agent))								$result['browser'] = 'Safari'; 
			    elseif (preg_match('/Opera/i', $agent))									$result['browser'] = 'Opera'; 
			    elseif (preg_match('/Netscape/i', $agent))								$result['browser'] = 'Netscape'; 
		    
		    // Browser version
			    $known = array('Version', $result['browser'], 'other');
			    $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
			    if (!preg_match_all($pattern, $agent, $matches)) {
			        // we have no matching number just continue
			    }
				// count results
					$i = count($matches['browser']);
					if ($i != 1) { // We will have two since we are not using 'other' argument yet
						// see if version is before or after the name */
							if (strripos($agent,"Version") < strripos($agent, @$this->browser['browser'])) $result['browser_version'] = @$matches['version'][0];
							else $result['browser_version'] = $matches['version'][1];
					}
					else $result['browser_version'] = $matches['version'][0];

			return $result;
		    
		} 

	}

/*
		Detector

	::	DESCRIPTION
	
		Detects various attributes and environmentals

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
