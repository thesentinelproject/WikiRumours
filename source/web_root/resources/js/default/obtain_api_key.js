
	errorMessage = '';

	function validateAllowUnlimited() {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.apiForm.allowUnlimited.value = 'Y';
			document.apiForm.submit()
		}
	}

	function validateRemoveUnlimited() {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.apiForm.removeUnlimited.value = 'Y';
			document.apiForm.submit()
		}
	}

	function validateRecycleKey() {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) document.apiForm.submit()
	}
