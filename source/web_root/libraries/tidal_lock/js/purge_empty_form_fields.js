
	function removeEmptyFormFields(form) {
		for (counter = 0; counter < document.getElementById(form).elements.length; counter++) {
			if (!document.getElementById(form).elements[counter].value) document.getElementById(form).elements[counter].removeAttribute('name');
		}
	}
