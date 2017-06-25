<?php

	class advertiser_TL {

		public function insertTracking($uacctID) {

			global $tl;
	
			if (!$uacctID) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No tracking ID specified.\n";
				return false;
			}
			
			return 	"<script type='text/javascript'>\n" .
					"  window.google_analytics_uacct = '" . $uacctID . "';\n" .
					"</script>\n";
			
		}
		
		public function insertAd($name, $adClientID, $adSlot, $width, $height) {
			
			global $tl;

			if (!$adClientID || !$adSlot || !$width || !$height) {
				$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": Insufficient parameters specified.\n";
				return false;
			}
			
			return	"<script type='text/javascript'><!--\n" .
					"  google_ad_client = '" . $adClientID . "';\n" .
					"  /* " . $name . " */\n" .
					"  google_ad_slot = '" . $adSlot . "';\n" .
					"  google_ad_width = " . $width . ";\n" .
					"  google_ad_height = " . $height . ";\n" .
					"  //-->\n" .
					"  </script>\n" .
					"<script type='text/javascript' src='http://pagead2.googlesyndication.com/pagead/show_ads.js'>\n" .
					"</script>\n";
			
		}
		
	}

/*
		Insert Google AdSenese

	::	DESCRIPTION
	
		Inserts basic Google AdSense code

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
