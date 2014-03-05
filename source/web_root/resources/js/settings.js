
	errorMessage = '';

	function validateDeleteLogo() {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.editPreferencesForm.logoToDelete.value = 'Y';
			document.editPreferencesForm.submit();
		}
	}
	
	function validateUpdateNotification(id) {
		errorMessage = '';
		if (!validateEmail_TL(document.getElementById('notification_email_' + id).value)) errorMessage += "Please specify a valid email address.\n";
		if (errorMessage) alert(errorMessage);
		else {
			document.editNotificationsForm.notificationEmailToUpdate.value = id;
			document.editNotificationsForm.submit();
		}
	}
	
	function validateDeleteNotification(id) {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.editNotificationsForm.notificationEmailToDelete.value = id;
			document.editNotificationsForm.submit();
		}
	}
	
	function validateAddNotification() {
		errorMessage = '';
		if (!validateEmail_TL(document.getElementById('notification_email_add').value)) errorMessage += "Please specify a valid email address.\n";
		if (errorMessage) alert(errorMessage);
		else {
			document.editNotificationsForm.addNotificationEmail.value = 'Y';
			document.editNotificationsForm.submit();
		}
	}

	function validateUpdateFaq(id) {
		errorMessage = '';
		if (!document.getElementById('question_' + id).value) errorMessage += "Please specify a question.\n";
		if (!document.getElementById('answer_' + id).value) errorMessage += "Please specify an answer.\n";
		if (errorMessage) alert(errorMessage);
		else {
			document.editFaqForm.faqToUpdate.value = id;
			document.editFaqForm.submit();
		}
	}
	
	function validateDeleteFaq(id) {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.editFaqForm.faqToDelete.value = id;
			document.editFaqForm.submit();
		}
	}
	
	function validateAddFaq() {
		errorMessage = '';
		if (!document.getElementById('question_add').value) errorMessage += "Please specify a question.\n";
		if (!document.getElementById('answer_add').value) errorMessage += "Please specify an answer.\n";
		if (errorMessage) alert(errorMessage);
		else {
			document.editFaqForm.addFaq.value = 'Y';
			document.editFaqForm.submit();
		}
	}

	function validateUpdateFaqChapter(id) {
		errorMessage = '';
		if (!document.getElementById('name_' + id).value) errorMessage += "Please specify a chapter name.\n";
		if (errorMessage) alert(errorMessage);
		else {
			document.editFaqChapterForm.faqChapterToUpdate.value = id;
			document.editFaqChapterForm.submit();
		}
	}
	
	function validateDeleteFaqChapter(id) {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.editFaqChapterForm.faqChapterToDelete.value = id;
			document.editFaqChapterForm.submit();
		}
	}
	
	function validateAddFaqChapter() {
		errorMessage = '';
		if (!document.getElementById('name_add').value) errorMessage += "Please specify a chapter name.\n";
		if (errorMessage) alert(errorMessage);
		else {
			document.editFaqChapterForm.addFaqChapter.value = 'Y';
			document.editFaqChapterForm.submit();
		}
	}
