
	errorMessage = '';

	function validateDeleteStatus(id) {
		errorMessage = '';
		if (document.getElementById('delete_prohibited_' + id).value > 0) errorMessage += "Delete prohibited for this status.\n";
		else if (document.getElementById('number_of_rumours_' + id).value > 0) errorMessage += "Unable to delete because there are " + document.getElementById('number_of_rumours_' + id).value + " rumours set to this status. Please modify those rumours and then try again.\n";
		
		if (errorMessage) alert(errorMessage);
		else {
			areYouSure = confirm("Are you sure?");
			if (areYouSure) {
				document.editStatusesForm.statusToDelete.value = id;
				document.editStatusesForm.submit();
			}
		}
	}
	
	function validateUpdateStatuses() {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) document.editStatusesForm.submit();
	}
