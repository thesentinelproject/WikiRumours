
	var errorMessage = '';

	function validateAddEditRumourForm() {

		errorMessage = '';
		
		if (document.addEditRumourForm.templateName.value == 'rumour_add') {
			if (!document.addEditRumourForm.description.value) errorMessage += "Please enter a rumour.\n";
			if (!document.addEditRumourForm.country.value) errorMessage += "Please specify the country where occurred.\n";
			if (!document.addEditRumourForm.status.value) errorMessage += "Please specify a status.\n";
			if (!document.addEditRumourForm.country_heard.value) errorMessage += "Please specify the country where heard.\n";
			if (!document.addEditRumourForm.heard_on.value) errorMessage += "Please specify the date heard.\n";
			if (!document.addSightingForm.heard_by.value) errorMessage += "Please specify who heard the rumour.\n";
			if (document.addEditRumourForm.heard_by.value == 'add') errorMessage += addNewUser();
		}
		else if (document.addEditRumourForm.templateName.value == 'rumour_edit') {
			if (!document.addEditRumourForm.description.value) errorMessage += "Please enter a rumour.\n";
			if (!document.addEditRumourForm.country.value) errorMessage += "Please specify a country.\n";
			if (!document.addEditRumourForm.status.value) errorMessage += "Please specify a status.\n";
		}

		if (errorMessage) alert(errorMessage);
		else document.addEditRumourForm.submit();
	}
	

