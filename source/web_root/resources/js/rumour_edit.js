
	errorMessage = '';

	function validateEditRumourForm() {
		errorMessage = '';
		
		if (!document.editRumourForm.description.value) errorMessage += "Please enter a rumour.\n";
		if (!document.editRumourForm.status.value) errorMessage += "Please specify a status.\n";
		if ((document.editRumourForm.status.value != 'NU' && document.editRumourForm.status.value != 'UI') && !document.editRumourForm.findings.value) errorMessage += "Please specify findings before finalizing status on this rumour.\n";
		if (!document.editRumourForm.country.value) errorMessage += "Please specify a country.\n";
		if (document.contains(editRumourForm.email) && document.contains(editRumourForm.newuser_country)) {
			if (document.editRumourForm.email.value && !validateEmail_TL(document.editRumourForm.email.value)) errorMessage += "Please specify a valid email address.\n";
			if (!document.editRumourForm.created_by.value && !document.editRumourForm.newuser_country.value) errorMessage += "Please specify the new user's country.\n";
		}

		if (errorMessage) alert(errorMessage);
		else document.editRumourForm.submit();
	}
	
	function deleteRumour() {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.editRumourForm.deleteThisRumour.value = 'Y';
			document.editRumourForm.submit();
		}
	}
