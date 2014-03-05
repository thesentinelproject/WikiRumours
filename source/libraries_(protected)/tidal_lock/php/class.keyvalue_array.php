<?php

	class keyvalueArray_TL {

		public function keyValueToArray($keyValueString, $delimiter = '&') {
			
			$outputArray = array();
			
			$pairs = explode($delimiter, $keyValueString);
			foreach ($pairs as $counter => $pair) {
				$keyValue = explode('=', $pair);
				if ($keyValue[0] && $keyValue[1]) $outputArray[$keyValue[0]] = $keyValue[1];
			}
			
			return $outputArray;
			
		}
		
		public function arrayToKeyValue($array, $delimiter = '&') {
			
			if (!is_array($array)) {
				errorManager_TL::addError("No array specified.");
				return false;
			}
			
			$outputString = '';
			
			foreach ($array as $key => $value) {
				$outputString .= $key . '=' . $value . $delimiter;
			}
			
			$outputString = trim($outputString, $delimiter);
			
			return $outputString;
			
		}
		
		public function updateKeyValue($keyValueString, $key, $value, $delimiter = '&') {
			
			$array = $this->keyValueToArray($keyValueString, $delimiter);
	
			if (!is_null($value)) $array[$key] = $value;
			else unset ($array[$key]);
			
			asort($array);
			$outputString = $this->arrayToKeyValue($array, $delimiter);
			
			return $outputString;
			
		}
		
	}

/*
	Key-Value/Array Functions

	::	DESCRIPTION
	
		Functions to convert arrays to key value pairs and vice versa, as
		well as to add/remove values from a key value pair (as when
		updating a query string without wanting to complete rebuild it)

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
