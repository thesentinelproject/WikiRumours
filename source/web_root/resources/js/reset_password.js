
	errorMessage = '';

	function validateResetPassword() {
		errorMessage = '';
		if (!document.resetPasswordForm.oldPassword.value) errorMessage += "Please provide an old password.\n";
		if (!document.resetPasswordForm.password.value) errorMessage += "Please provide a new password.\n";
		if (document.resetPasswordForm.password.value && document.resetPasswordForm.password.value != document.resetPasswordForm.confirmPassword.value) errorMessage += "Your new password doesn't match the confirmation password.\n";
		if (errorMessage) alert(errorMessage);
		else document.resetPasswordForm.submit();
	}
