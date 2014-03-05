
	errorMessage = '';

	function validateProfileForm() {
		errorMessage = '';
		if (!document.profileForm.username.value) errorMessage += "Please provide your username.\n";
		if (!isStringValid_TL(document.profileForm.username.value, '0123456789abcdefghijklmnopqrstuvwxyz-_', '', true)) errorMessage += "Your username can only contain alphanumeric characters.\n";
		if (document.profileForm.email.value && !validateEmail_TL(document.profileForm.email.value)) errorMessage += "There appears to be a problem with your email address.\n";
		if (!document.profileForm.first_name.value) errorMessage += "Please provide your first name.\n";
		if (!document.profileForm.last_name.value) errorMessage += "Please provide your last name.\n";
		if (errorMessage) alert(errorMessage);
		else document.profileForm.submit();
	}
	
	function deleteProfileImage() {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.profileForm.deleteCurrentProfileImage.value = 'Y';
			document.profileForm.submit()
		}
	}
	
	function revealOrHidePermissions() {
		if (document.profileForm.is_administrator.checked) $('#admin_permissions').collapse('show');
		else $('#admin_permissions').collapse('hide');
	}

	function revealOrHideTerminationReason() {
		if (document.profileForm.enabled.checked) $('#termination_notes').collapse('hide');
		else $('#termination_notes').collapse('show');
	}
