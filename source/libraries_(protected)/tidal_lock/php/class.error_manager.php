<?php
	class errorManager_TL {

		public static $errorArray = array();
		public static $errorString = null;

		public function addError($error) {
			if ($error) {
				errorManager_TL::$errorString = trim(errorManager_TL::$errorString . " " . $error);

				$callers = debug_backtrace();
				$counter = count(errorManager_TL::$errorArray);
				errorManager_TL::$errorArray[$counter] = array();
				errorManager_TL::$errorArray[$counter]['error'] = $error;
				errorManager_TL::$errorArray[$counter]['function'] = $callers[1]['function'];
				errorManager_TL::$errorArray[$counter]['class'] = $callers[1]['class'];
			}
			
		}
		
	}
	
/*	
	Error Manager

	::	DESCRIPTION
	
		Centralized internal error handling class

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
