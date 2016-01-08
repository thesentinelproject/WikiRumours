<?php

	class input_validator_TL {

		public function validateEmailBasic($email) {
			if (substr_count($email, '@') < 1) return false;
			elseif (substr_count($email, '.') < 1) return false;
			else return true;
		}

		public function validateEmailRobust($email) {
		   
			if (!$this->validateEmailBasic($email)) return false;
			else {

				$isValid = true;
				$atIndex = strrpos($email, "@");
			   
				if (is_bool($atIndex) && !$atIndex) $isValid = false;
				else {
					$domain = substr($email, $atIndex+1);
					$local = substr($email, 0, $atIndex);
					$localLen = strlen($local);
					$domainLen = strlen($domain);
		
					if ($localLen < 1 || $localLen > 64) $isValid = false; // local part length exceeded
					else if ($domainLen < 1 || $domainLen > 255) $isValid = false; // // domain part length exceeded
					else if ($local[0] == '.' || $local[$localLen-1] == '.') $isValid = false; // // local part starts or ends with '.'
					else if (preg_match('/\\.\\./', $local)) $isValid = false; // local part has two consecutive dots
					else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) $isValid = false; // character not valid in domain part
					else if (preg_match('/\\.\\./', $domain)) $isValid = false; // domain part has two consecutive dots
					else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
						// character not valid in local part unless local part is quoted
							if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) $isValid = false;
							if ($isValid && !(checkdnsrr($domain,"MX") ||  checkdnsrr($domain,"A"))) $isValid = false; // domain not found in DNS
					}
				}
			   
				return $isValid;

				// Courtesy Douglas Lovell
				// http://www.linuxjournal.com/article/9585

			}
			
		}

		public function isStringValid($input, $canOnlyContain, $cannotContain, $caseInsensitive = true) {
			
			$foundInvalid = false;
			
			if ($caseInsensitive) {
				$input = strtolower($input);
				$canOnlyContain = strtolower($canOnlyContain);
				$cannotContain = strtolower($cannotContain);
			}
			
			if ($canOnlyContain) {
				for ($counter = 0; $counter < strlen($input); $counter++) {
					if (substr_count($canOnlyContain, substr($input, $counter, 1)) < 1) $foundInvalid = true;
				}
			}
			
			if ($cannotContain) {
				for ($counter = 0; $counter < strlen($cannotContain); $counter++) {
					if (substr_count($input, substr($cannotContain, $counter, 1)) > 0) $foundInvalid = true;
				}
			}
			
			if (!$foundInvalid) return true;
			else return false;
			
		}
		
	}

/*
	Validators

	::	DESCRIPTION
	
		Functions to validate various file and data types

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
