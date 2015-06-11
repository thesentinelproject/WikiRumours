
	errorMessage = '';

	function validateDeleteNotification(id) {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.editNotificationsForm.notificationEmailToDelete.value = id;
			document.editNotificationsForm.submit();
		}
	}
	
	function validateEmailUserForm() {
		errorMessage = '';
		if (!document.emailUserForm.name.value) errorMessage += "Please provide a name for the recipient of your email.\n";
		if (!validateEmail_TL(document.emailUserForm.email.value)) errorMessage += "Please provide a valid email address for the recipient of your email.\n";
		if (document.emailUserForm.reply_to_email.value && !validateEmail_TL(document.emailUserForm.reply_to_email.value)) errorMessage += "Please provide a valid email address for the sender of your email.\n";
		if (!document.emailUserForm.subject.value) errorMessage += "Please provide a subject for the email.\n";
		if (!document.emailUserForm.message.value) errorMessage += "Please provide a message for the email.\n";
		if (errorMessage) alert(errorMessage);
		else document.emailUserForm.submit();
	}
