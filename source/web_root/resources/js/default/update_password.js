
	errorMessage = '';

	function validatePasswordForm() {
		errorMessage = '';
		if (typeof(document.getElementById('old_password')) != 'undefined' && document.getElementById('old_password') != null) {
			if (!document.passwordForm.old_password.value) errorMessage += "Please provide your old password.\n";
		}
		if (!document.passwordForm.password.value) errorMessage += "Please provide your new password.\n";
		if (document.passwordForm.password.value && document.passwordForm.password.value != document.passwordForm.confirm_new_password.value) errorMessage += "Your password doesn't match the confirmation password.\n";
		if (errorMessage) alert(errorMessage);
		else document.passwordForm.submit();
	}
