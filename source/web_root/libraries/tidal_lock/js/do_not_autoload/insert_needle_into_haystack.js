/*
	* Copyright (C) 2010-2013 Timothy Quinn / Tidal Lock / Consolidated Biro
	* 
	* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
	* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
	* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
	* 
*/

	function insertNeedleIntoHaystack_TL(needle, haystack, separator) {
		
		// define values
			if (!separator) separator = ',';
			if (haystack) {
				var haystackArray = haystack.split(separator);
			}
			else {
				var haystackArray = new Array();
			}
			var needleExistsInHaystack = 'N';

		// check if needle already exists in haystack
			for (counter = 0; counter < haystackArray.length; counter++) {
				if (trim(haystackArray[counter]).toLowerCase() == trim(needle).toLowerCase()) needleExistsInHaystack = 'Y';
			}

		// if not, add it
			if (needleExistsInHaystack == 'N') haystackArray[haystackArray.length] = needle;
		
		// sanitize values if containing separator
			for (counter = 0; counter < haystackArray.length; counter++) {
				haystackArray[counter] = haystackArray[counter].replace(/,/g, "&#44;");
			}
			
		// turn array back into string
			var newHaystack = haystackArray.toString();
			
		// replace default separator (comma) with chosen separator
			newHaystack = newHaystack.replace(/,/g, separator);
		
		return newHaystack;
		
	}
	
/*
	Insert Needle Into Haystack v1.6

	-- Description:
	Checks whether a needle exists in a given haystack and,
	if not, inserts it at the end.
	
	-- Returns:
	String consisting of new haystack
    
	-- Dependent on:
	Trim: http://codelibrary.tidallock.com/?p=78
    
*/
