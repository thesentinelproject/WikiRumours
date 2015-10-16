
	function retrieveUploadAttributes(element) {

		var _URL = window.URL || window.webkitURL;
	    var file, img;
		var attributes = new Array();

		attributes['filesize'] = document.getElementById(element).files[0].size;
		attributes['mime'] = document.getElementById(element).files[0].type;
		attributes['filename'] = document.getElementById(element).files[0].name;

		if ((file = document.getElementById(element).files[0])) {
			img = new Image();
			img.onload = function() {
				if (document.getElementById(element + '_width')) document.getElementById(element + '_width').value = img.width;
				if (document.getElementById(element + '_height')) document.getElementById(element + '_height').value = img.height;
			};
		
			img.src = _URL.createObjectURL(file);
		}

		return attributes;

	}
