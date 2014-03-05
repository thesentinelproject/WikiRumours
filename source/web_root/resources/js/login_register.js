
	errorMessage = '';

	function validateLoginForm() {
		errorMessage = '';
		if (!document.loginForm.loginUsername.value) errorMessage += "Please provide your username.\n";
		if (!document.loginForm.loginPassword.value) errorMessage += "Please provide your password.\n";
		if (errorMessage) alert(errorMessage);
		else document.loginForm.submit();
	}

	function validateRegistrationForm() {
		errorMessage = '';
		if (!document.registrationForm.registerUsername.value) errorMessage += "Please provide a username.\n";
		if (!isStringValid_TL(document.registrationForm.username.value, '0123456789abcdefghijklmnopqrstuvwxyz-_', '', '')) errorMessage += "Your username can only contain alphanumeric characters.\n";
		if (!validateEmail_TL(document.registrationForm.email.value)) errorMessage += "There appears to be a problem with your email address.\n";
		if (!document.registrationForm.first_name.value) errorMessage += "Please provide your first name.\n";
		if (!document.registrationForm.last_name.value) errorMessage += "Please provide your last name.\n";
		if (!document.registrationForm.password.value) errorMessage += "Please provide your password.\n";
		else if (document.registrationForm.password.value != document.registrationForm.confirm.value) errorMessage += "Your password doesn't match the confirmation password.\n";
		if (errorMessage) alert(errorMessage);
		else document.registrationForm.submit();
	}
