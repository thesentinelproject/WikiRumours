
	errorMessage = '';

	function validateAddSightingForm() {
		errorMessage = '';
		if (!document.addSightingForm.country.value) errorMessage += "Please specify a country.\n";
		if (!document.addSightingForm.heard_on.value) errorMessage += "Please specify a date.\n";
		if (!document.addSightingForm.source_id.value) errorMessage += "Please specify a source.\n";
		if (!document.addSightingForm.created_by.value) errorMessage += "Please specify who heard the rumour.\n";
		if (document.addSightingForm.created_by.value == 'add') errorMessage += addNewUser();
		if (errorMessage) alert(errorMessage);
		else document.addSightingForm.submit();
	}
	
	function removeSighting() {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.addSightingForm.deleteThisSighting.value = 'Y';
			document.addSightingForm.submit();
		}
	}
