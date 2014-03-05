
	errorMessage = '';

	function validateAddRumourForm() {
		errorMessage = '';
		
		if (document.addRumourForm.step.value == 1) {
			if (!document.addRumourForm.description.value) errorMessage += "Please enter a rumour.\n";
			if (!document.addRumourForm.country_occurred.value) errorMessage += "Please specify the country where occurred.\n";
		}
		else if (document.addRumourForm.step.value == 3) {
			if (!document.addRumourForm.description.value) errorMessage += "Please enter a rumour.\n";
			if (!document.addRumourForm.country_occurred.value) errorMessage += "Please specify the country where occurred.\n";
			if (!document.addRumourForm.country_heard.value) errorMessage += "Please specify the country where heard.\n";
			if (!document.addRumourForm.heard_on.value) errorMessage += "Please specify the date heard.\n";
			if (document.contains(addRumourForm.email) && document.contains(addRumourForm.newuser_country)) {
				if (document.addRumourForm.email.value && !validateEmail_TL(document.addRumourForm.email.value)) errorMessage += "Please specify a valid email address.\n";
				if (!document.addRumourForm.created_by.value && !document.addRumourForm.newuser_country.value) errorMessage += "Please specify the new user's country.\n";
			}
		}

		if (errorMessage) alert(errorMessage);
		else document.addRumourForm.submit();
	}
	
	function matchRumour(publicID) {
		document.addRumourForm.rumour_id.value = publicID;
		document.addRumourForm.submit();
	}
