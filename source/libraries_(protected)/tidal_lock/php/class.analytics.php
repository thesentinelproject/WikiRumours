<?php

	class analytics_TL {

		public function insertGoogleAnalytics($webPropertyID, $domain = '', $accommodateMultipleTopLevelDomains = false) {
	
			// check input
				if (!$webPropertyID) {
					errorManager_TL::addError("No ID specified.");
					return false;
				}
	
			// create HTML
				$code = "<!-- Google Analytics -->\n";
				$code .= "  <script type='text/javascript'>\n";
				$code .= "    var _gaq = _gaq || [];\n";
				$code .= "     _gaq.push(['_setAccount', '" . $webPropertyID . "']);\n";
	
			// accommodate subdomains and additional top-level domains
				if ($domain || $accommodateMultipleTopLevelDomains) $code .= "      _gaq.push(['_setDomainName', '" . $domain . "']);\n";
				if ($accommodateMultipleTopLevelDomains) $code .= "      _gaq.push(['_setAllowLinker', true]);\n";
	
			// continue creating HTML
				$code .= "    _gaq.push(['_trackPageview']);\n\n";
				$code .= "    (function() {\n";
				$code .= "      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;\n";
				$code .= "      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';\n";
				$code .= "      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);\n";
				$code .= "    })();\n";
				$code .= "  </script>\n";
	
			// return HTML
				return $code;
			
		}
		
		public function insertMixPanel($token) {
	
			// check input
				if (!$token) {
					errorManager_TL::addError("No token specified.");
					return false;
				}
				
			// create HTML
				return	"<!-- Mixpanel -->" .
						"<script type='text/javascript'>" .
						'(function(c,a){window.mixpanel=a;var b,d,h,e;b=c.createElement("script");b.type="text/javascript";b.async=!0;b.src=("https:"===c.location.protocol?"https:":"http:")+' .
						"'//cdn.mxpnl.com/libs/mixpanel-2.1.min.js'" . 
						';d=c.getElementsByTagName("script")[0];d.parentNode.insertBefore(b,d);a._i=[];a.init=function(b,c,f){function d(a,b){var c=b.split(".");2==c.length&&(a=a[c[0]],b=c[1]);a[b]=function(){a.push([b].concat(Array.prototype.slice.call(arguments,0)))}}var g=a;"undefined"!==typeof f?' .
						'g=a[f]=[]:f="mixpanel";g.people=g.people||[];h="disable track track_pageview track_links track_forms register register_once unregister identify name_tag set_config people.identify people.set people.increment".split(" ");for(e=0;e<h.length;e++)d(g,h[e]);a._i.push([b,c,f])};a.__SV=1.1})(document,window.mixpanel||[]);' .
						'mixpanel.init("' . $token . '");' .
						"</script>\n";
		}
		
		
	}
	
/*	
	Analytics

	::	DESCRIPTION
	
		Functions to implement Google Analytics and Mixpanel

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
