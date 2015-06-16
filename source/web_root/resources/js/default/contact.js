
	errorMessage = '';

	function validateContactForm() {
		errorMessage = '';
		if (!document.contactForm.name.value) errorMessage += "Please provide your name.\n";
		if (!validateEmail_TL(document.contactForm.email.value)) errorMessage += "There appears to be a problem with your email address.\n";
		if (!document.contactForm.message.value) errorMessage += "Please write a brief message.\n";
		if (errorMessage) alert(errorMessage);
		else document.contactForm.submit();
	}
