
	errorMessage = '';

	function validateDeleteLogo() {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.editPreferencesForm.logoToDelete.value = 'Y';
			document.editPreferencesForm.submit();
		}
	}
