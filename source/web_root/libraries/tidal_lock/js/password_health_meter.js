
	// jQuery listener (monitors a form field with the id "password")
		$('#password').keydown(function(event) {
			if (event.which != 37 && event.which != 38 && event.which != 39 && event.which != 40) {
				updatePasswordHealthMonitor_TL();
		    }
		});

	// callback which is triggered when password value changes
		function updatePasswordHealthMonitor_TL() {
	
			var password = document.getElementById('password').value;
			var badPasswords = new Array('password', 'pw', 'pass', 'passw0rd', 'test', 'admin', 'user');
			var score = 0;
			var scoreLabels = new Array(			'Awful',	'Very Weak',	'Weak',		'Average',	'Good',		'Strong',	'Very Strong');
			var scoreForegroundColors = new Array(	'ffffff',	'000000',		'000000',	'000000',	'ffffff',	'ffffff',	'ffffff');
			var scoreBackgroundColors = new Array(	'ff0000',	'f0ad4f',		'f2f057',	'e3e039',	'79d156',	'32bf45',	'007a10');
			
			// check for bad passwords
				if (badPasswords.indexOf(password) < 0) score = 1;
			
			// check password length
				if (password.length < 4) score = 0;
				if (score > 0 && password.length > 7) score += 1;
				
			// check for alphanumeric values
				if (score > 0 && /[0-9]/.test(password)) score += 1;
				if (score > 0 && /[A-Z]/.test(password)) score += 1;
				if (score > 0 && /[a-z]/.test(password)) score += 1;
				
			// check for non-alphanumeric values
				if (score > 0 && /[^a-zA-Z0-9]/.test(password)) score += 1;
	
			// update health meter
				document.getElementById('healthMeter').innerHTML = scoreLabels[score];
				document.getElementById('healthMeter').style.color = '#' + scoreForegroundColors[score];
				document.getElementById('healthMeter').style.background = '#' + scoreBackgroundColors[score];
				
			// unhide health meter
				document.getElementById('healthMeterContainer').className = '';

		}

	// CSS for health monitor div
		document.write("<style type='text/css'>\n");
		document.write("  #healthMeter { border: 1px solid #ddd; text-align: center; padding: 5px; margin-top: 5px;  -moz-border-radius: 5px; -webkit-border-radius: 5px; border-radius: 5px; margin-bottom: 5px; }\n\n");
		document.write("</style>\n");

/*
	Password Health Meter

	::	DESCRIPTION
	
		Uses jQuery to format a div depending on the strength of a
		user's password

	::	DEPENDENT ON
	
		jQuery
		
	::	VERSION HISTORY

	::	LICENSE
	
		Copyright (C) 2010-2013
		Timothy Quinn / Tidal Lock / Consolidated Biro
		
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
