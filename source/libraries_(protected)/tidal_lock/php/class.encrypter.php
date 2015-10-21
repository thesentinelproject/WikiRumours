<?php

	class encrypter_TL {

		public function quickEncrypt($plaintext, $salt = false) {
			
			/* 		Uses MD5 for rapid low-criticality one-way encryption. For higher-grade one-way encryption,
					try a Bcrypt implementation such as PH Pass. */
			
			// add salt
				$plaintext .= $salt;
				
			// encrypt with MD5
				$encryptedText = md5($plaintext);
				
			// transpose last two characters for added security
				$encryptedText = substr($encryptedText, 0, -2) . substr($encryptedText, -1, 1) . substr($encryptedText, -2, 1);
			
			return $encryptedText;
		}

		public function robustEncrypt($plaintext, $salt, $key) {
			return base64_encode(@mcrypt_encrypt(MCRYPT_BLOWFISH, md5($key), $plaintext, MCRYPT_MODE_CBC, md5($salt)));
		}

		public function robustDecrypt($cyphertext, $salt, $key) {
			return @mcrypt_decrypt(MCRYPT_BLOWFISH, md5($key), base64_decode($cyphertext), MCRYPT_MODE_CBC, md5($salt));
		}
		
		public function obfuscateID($plainText, $numericSalt) {
			
			/* Performs rudimentary two-way encryption to obfuscate a value requiring only minimal security (e.g. a database ID or a URL that you want to transmit by GET) */
			
			global $console;

			// ensure salt is numeric
				$numericSalt = intval($numericSalt);
	
			// convert all characters to numeric ASCII equivalents
				for ($counter = 0; $counter < strlen($plainText); $counter++) {
					$asciiCode = str_pad(ord(substr($plainText, $counter, 1)), 3, '0', STR_PAD_LEFT);
					if ($asciiCode < 1 || $asciiCode > 255) {
						$console .= __CLASS__ . "->" . __FUNCTION__ . ": Input string contains characters that aren't part of the standard or extended ASCII character sets.\n";
						return false;
					}
					else $convertedText .= $asciiCode;
				}
				
			// apply salt
				for ($counter = 0; $counter < strlen($convertedText); $counter++) {
					$transposedCharacter = intval(substr($convertedText, $counter, 1)) + intval(substr($numericSalt, $saltCounter, 1));
					if ($transposedCharacter >= 10) $transposedCharacter = $transposedCharacter - 10;
					$saltedText .= $transposedCharacter;
					$saltCounter++;
					if ($saltCounter == strlen($numericSalt)) $saltCounter = 0;
				}
				
			// look for patterns to reverse convert and thereby shorten the overall string
				for ($counter = 0; $counter < strlen($saltedText) - 3; $counter = $counter + 3) {
					if ((substr($saltedText, $counter, 3) >= 65 && substr($saltedText, $counter, 3) <= 90) || (substr($saltedText, $counter, 3) >= 97 && substr($saltedText, $counter, 3) <= 122)) {
						$shortenedText .= chr(substr($saltedText, $counter, 3));
					}
					else {
						$shortenedText .= substr($saltedText, $counter, 3);
					}
				}
				$shortenedText .= substr($saltedText, $counter);
				
			// apply simple checksum
				for ($counter = 0; $counter < strlen($shortenedText); $counter++) {
					$checksum += intval(substr($shortenedText, $counter, 1));
				}
				
				$checksum = substr($checksum, -1);
	
			// attach checksum and return obfuscated value
				$obfuscatedText = $shortenedText . $checksum;
				return $obfuscatedText;
				
		}
		
		public function deobfuscateID($obfuscatedText, $numericSalt) {

			global $console;
			
			// ensure salt is numeric
			// (note that same salt must be used as when text was obfuscated)
				$numericSalt = intval($numericSalt);
				
			// strip checksum
				$strippedText = substr($obfuscatedText, 0, strlen($obfuscatedText) - 1);
				
			// convert embedded letters back to numeric values
				for ($counter = 0; $counter < strlen($strippedText); $counter++) {
					if ((ord(substr($strippedText, $counter, 1)) >= 65 && ord(substr($strippedText, $counter, 1)) <= 90) || (ord(substr($strippedText, $counter, 1)) >= 97 && ord(substr($strippedText, $counter, 1)) <= 122)) {
						$convertedText .= str_pad(ord(substr($strippedText, $counter, 1)), 3, '0', STR_PAD_LEFT);
					}
					else {
						$convertedText .= substr($strippedText, $counter, 1);
					}
				}
			// reverse salt
				for ($counter = 0; $counter < strlen($convertedText); $counter++) {
					$transposedCharacter = intval(substr($convertedText, $counter, 1)) - intval(substr($numericSalt, $saltCounter, 1));
					if ($transposedCharacter < 0) $transposedCharacter = $transposedCharacter + 10;
					$unsaltedText .= $transposedCharacter;
					$saltCounter++;
					if ($saltCounter == strlen($numericSalt)) $saltCounter = 0;
				}
				
			// convert all remaining ASCII codes back into HTML characters
				for ($counter = 0; $counter < strlen($unsaltedText); $counter = $counter + 3) {
					$htmlCharacter = substr($unsaltedText, $counter, 3);
					if ($htmlCharacter >= 0 && $htmlCharacter <= 255) {
						$plainText .= chr(substr($unsaltedText, $counter, 3));
					}
					else {
						$console .= __CLASS__ . "->" . __FUNCTION__ . ": Input string appears to contain characters that aren't part of the standard or extended ASCII character sets; please check that the identical salt was used for obfuscation and de-obfuscation.\n";
						return false;
					}
				}
	
			// return original text
				return $plainText;
				
		}
		
	}
 
/*
	Encrypter

	::	DESCRIPTION
	
		Functions for creating unique identifiers and encryption keys

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
