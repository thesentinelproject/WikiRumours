
	errorMessage = '';

	function validateSearchFaqForm() {
		errorMessage = '';
		if (!isStringValid_TL(document.searchFaqForm.keywords.value, "abcdefghijklmnopqrstuvwxyz0123456789-' ", '', '')) errorMessage += "Please specify only alphanumeric characters.\n";
		if (errorMessage) alert(errorMessage);
		else document.searchFaqForm.submit();
	}
