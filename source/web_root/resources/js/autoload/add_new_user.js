
	function addNewUser() {

		errorMessage = '';

		if (!document.getElementById('newuser_username').value) errorMessage += "Please choose a username for the new user.\n";
		if (document.getElementById('newuser_email').value && !validateEmail_TL(document.getElementById('newuser_email').value)) errorMessage += "Please specify a valid email address for the new user.\n";
		if (!document.getElementById('newuser_country').value) errorMessage += "Please specify the new user's country.\n";

		return errorMessage;

	}
