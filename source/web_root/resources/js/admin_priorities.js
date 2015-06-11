
	errorMessage = '';

	function validateDeletePriority(id) {
		errorMessage = '';
		if (document.getElementById('number_of_rumours_' + id).value > 0) errorMessage += "Unable to delete because there are " + document.getElementById('number_of_rumours_' + id).value + " rumours set to this priority. Please modify those rumours and then try again.\n";
		if (errorMessage) alert(errorMessage);
		else {
			areYouSure = confirm("Are you sure?");
			if (areYouSure) {
				document.editPrioritiesForm.priorityToDelete.value = id;
				document.editPrioritiesForm.submit();
			}
		}
	}
	
	function validateUpdatePriorities() {
		errorMessage = '';
		if ((document.editPrioritiesForm.severity_add.value || document.editPrioritiesForm.action_required_in_add.value) && !document.editPrioritiesForm.priority_add.value) errorMessage += "Please specify a new priority name.\n";
		if (errorMessage) alert(errorMessage);
		else {
			areYouSure = confirm("Are you sure?");
			if (areYouSure) document.editPrioritiesForm.submit();
		}
	}
