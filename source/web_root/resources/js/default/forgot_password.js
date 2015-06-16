
	errorMessage = '';

	function validateForgotPasswordForm() {
		errorMessage = '';
		if (validateEmail_TL(document.forgotPasswordForm.email.value) == false) errorMessage += "There appears to be a problem with your email address.\n";
		if (errorMessage) alert(errorMessage);
		else document.forgotPasswordForm.submit();
	}
