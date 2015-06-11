
	errorMessage = '';

	function validateAddRumourForm() {
		errorMessage = '';
		
		if (!document.addRumourForm.description.value) errorMessage += "Please enter a rumour.\n";
		if (!document.addRumourForm.country.value) errorMessage += "Please specify the country where occurred.\n";
		if (errorMessage) alert(errorMessage);
		else document.addRumourForm.submit();
	}

	function matchRumour(publicID) {
		document.matchRumourForm.rumour_id.value = publicID;
		document.matchRumourForm.submit();
	}
