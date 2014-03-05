
	errorMessage = '';

	function resolveAlert(alertID) {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.dashboardAlertsForm.alertToResolve.value = alertID;
			document.dashboardAlertsForm.submit();
		}
	}
	
	function approveRegistrant(id) {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.editRegistrantsForm.registrantToApprove.value = id;
			document.editRegistrantsForm.submit();
		}
	}
	
	function deleteRegistrant(id) {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.editRegistrantsForm.registrantToDelete.value = id;
			document.editRegistrantsForm.submit();
		}
	}
	
	function validateEmailUserForm() {
		errorMessage = '';
		if (!document.emailUserForm.name.value) errorMessage += "Please provide a name for the recipient of your email.\n";
		if (!validateEmail_TL(document.emailUserForm.email.value)) errorMessage += "Please provide a valid email address for the recipient of your email.\n";
		if (document.emailUserForm.reply_to.value && !validateEmail_TL(document.emailUserForm.reply_to.value)) errorMessage += "Please provide a valid email address for the sender of your email.\n";
		if (!document.emailUserForm.message.value) errorMessage += "Please provide a message for the recipient of your email.\n";
		if (errorMessage) alert(errorMessage);
		else document.emailUserForm.submit();
	}
