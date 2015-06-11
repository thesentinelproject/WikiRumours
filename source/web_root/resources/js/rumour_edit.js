
	function deletePhoto() {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.addEditRumourForm.deleteThisPhoto.value = 'Y';
			document.addEditRumourForm.submit();
		}
	}

	function deleteRumour() {
		areYouSure = confirm("Are you sure you want to delete, rather than disable, this rumour?");
		if (areYouSure) {
			document.addEditRumourForm.deleteThisRumour.value = 'Y';
			document.addEditRumourForm.submit();
		}
	}
