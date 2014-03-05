/*
	* Copyright (C) 2010-2013 Timothy Quinn / Tidal Lock / Consolidated Biro
	* 
	* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
	* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
	* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
	* 
*/

	function geolocate_TL(uriToSendData, additionalKeyValuePairs) {

		if (navigator.geolocation) {

			navigator.geolocation.getCurrentPosition(

				// valid geocoordinates
					function (geoObject) {
						geolocateCallback_TL(geoObject.coords.latitude, geoObject.coords.longitude, '', uriToSendData, additionalKeyValuePairs);
					},
				
				// error handling
					function retrievePositionError_TL(error) {
						switch(error.code) {
							case error.PERMISSION_DENIED: geolocateCallback_TL('', '', "User denied the request for Geolocation.", uriToSendData, additionalKeyValuePairs); break;
							case error.POSITION_UNAVAILABLE: geolocateCallback_TL('', '', "Location information is unavailable.", uriToSendData, additionalKeyValuePairs); break;
							case error.TIMEOUT: geolocateCallback_TL('', '', "The request to get user location timed out.", uriToSendData, additionalKeyValuePairs); break;
							case error.UNKNOWN_ERROR: geolocateCallback_TL('', '', "An unknown error occurred.", uriToSendData, additionalKeyValuePairs); break;
						}
					},
					
				// timeout
					{timeout:10000});
			
		}
		else geolocateCallback_TL('', '', "Geolocation is not supported by this browser.", uriToSendData, additionalKeyValuePairs);
		
	}
	
	function geolocateCallback_TL(latitude, longitude, error, uriToSendData, additionalKeyValuePairs) {

		dataToSend = "task=updateGeocoordinates&latitude=" + latitude + "&longitude=" + longitude + "&error=" + escape(error);
		if (additionalKeyValuePairs) dataToSend += "&" + additionalKeyValuePairs;

		$.ajax({
			type: "POST",
			url: uriToSendData,
			data: dataToSend,
			success: function(msg) { },
			error: function(msg) { }
		});
		
	}	
	
	/*
		Geolocate v1.0
	
		-- Description:
		Uses HTML5's built-in geolocation
		
		-- Returns:
		Calls the function geolocateCallback_TL() with three
		parameters: latitude, longitude and error
		
	*/
