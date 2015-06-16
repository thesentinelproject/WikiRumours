
	errorMessage = '';

	function validateDeleteFaq(id) {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.editFaqForm.faqToDelete.value = id;
			document.editFaqForm.submit();
		}
	}
	
	function validateUpdateFaqs() {
		errorMessage = '';
		if (document.getElementById('question_add').value && !document.getElementById('answer_add').value) errorMessage += "Please specify an answer.\n";
		if (document.getElementById('answer_add').value && !document.getElementById('question_add').value) errorMessage += "Please specify a question.\n";
		if (errorMessage) alert(errorMessage);
		else document.editFaqForm.submit();
	}
	
	function validateDeleteFaqSection(id) {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.editFaqSectionForm.faqSectionToDelete.value = id;
			document.editFaqSectionForm.submit();
		}
	}
