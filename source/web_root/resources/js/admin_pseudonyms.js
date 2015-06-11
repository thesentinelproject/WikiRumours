
	var errorMessage = '';

	// tooltips
		$('.tooltips').tooltip({
			placement: 'top',
			delay: 250
		});

	// popovers
		$('.popovers').popover({
			trigger: 'hover'
		});

	// validate form
		function validateEditPseudonymForm() {
			errorMessage = '';
			if (!document.editPseudonymForm.domain.value || (document.editPseudonymForm.domain.value.match(/./g) || []).length < 1) errorMessage += "Please specify a valid domain.\n";
			if (!document.editPseudonymForm.name.value) errorMessage += "Please specify an instance name.\n";
			if (errorMessage) alert(errorMessage);
			else document.editPseudonymForm.submit();
		}

	// delete instance
		function deletePseudonym() {
			areYouSure = confirm("Are you sure?");
			if (areYouSure) {
				document.editPseudonymForm.deleteThisPseudonym.value = 'Y';
				document.editPseudonymForm.submit()
			}
		}

	// delete logo	
		function deleteLogo() {
			areYouSure = confirm("Are you sure?");
			if (areYouSure) {
				document.editPseudonymForm.deleteThisLogo.value = 'Y';
				document.editPseudonymForm.submit()
			}
		}
