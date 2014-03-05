/*
	* Copyright (C) 2010-2013 Timothy Quinn / Tidal Lock / Consolidated Biro
	* 
	* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
	* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
	* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
	* 
*/

	function truncateText (inputText, truncateHow, desiredLength) {

		var outputText = '';
		
		if (truncateHow == 'character') {
			if (inputText.length > desiredLength) outputText = inputText.substring(0, desiredLength - 3) + "... ";
			else outputText = inputText;
		}
		
		if (truncateHow == 'word') {
			var words = inputText.split(' ', desiredLength);
			
			for (counter = 0; counter < words.length; counter++) {
				outputText += words[counter];
			}
			
			outputText += '...';
		}

		return outputText;

	}

	/*
		Truncate Text v1.0
	
		-- Description:
		Returns a portion of the input text followed by
		an ellipsis.
		
		-- Returns:
		Truncated input
	    
	*/
