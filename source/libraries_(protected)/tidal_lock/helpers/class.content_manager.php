<?php

	class content_manager_TL {

		public function retrieveContentBlock($matching) {

			global $tl;

			if (!$matching || !is_array($matching)) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No criteria provided for matching content block.\n";
				return false;
			}

			$block = retrieveFromDb('cms', null, array_merge($matching, array('content_type'=>'b')));

			if (count($block) <> 1) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to retrieve desired content block.\n";
				return false;
			}
			else {
				return @$block[0]['content'];
			}

		}

	}

/*
		Content Manager

	::	DESCRIPTION
	
		Handles issues relating to the CMS

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
