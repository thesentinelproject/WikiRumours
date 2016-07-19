
	errorMessage = '';

	function validateCms() {
		
		errorMessage = '';
		
		// pages
			if (document.editContentForm.contentType.value == 'p') {
				if (!document.editContentForm.slug.value) errorMessage += "Please create a slug to use as a unique descriptor for your content.\n";
				else if (!isStringValid_TL(document.editContentForm.slug.value, 'abcdefghijklmnopqrstuvwxyz0123456789_-', '', true)) errorMessage += "Please ensure your slug contains only alphanumeric characters, dashes or underscores.\n";
				if (!document.editContentForm.title.value) errorMessage += "Please provide a page title.\n";
				if (!document.editContentForm.content.value) errorMessage += "Please provide content to save in the CMS.\n";
			}
		// content blocks
			else if (document.editContentForm.contentType.value == 'b') {
				if (!document.editContentForm.slug.value) errorMessage += "Please create a slug to use as a unique descriptor for your content.\n";
				if (!document.editContentForm.content.value) errorMessage += "Please provide content to save in the CMS.\n";
			}
		// files
			else if (document.editContentForm.contentType.value == 'f' && document.editContentForm.id.value) {
				if (!document.editContentForm.slug.value) errorMessage += "Please specify a filename.\n";
				else if (!isStringValid_TL(document.editContentForm.slug.value, 'abcdefghijklmnopqrstuvwxyz0123456789_-.', '', true)) errorMessage += "Please ensure your filename contains only alphanumeric characters, dashes, underscores or periods.\n";
			}
		// messages
			else if (document.editContentForm.contentType.value == 'm') {
				if (!document.editContentForm.message_type.value) errorMessage += "Please specify a message type.\n";
				if (!document.editContentForm.slug.value) errorMessage += "Please create a slug to use in URLs.\n";
				else if (!isStringValid_TL(document.editContentForm.slug.value, 'abcdefghijklmnopqrstuvwxyz0123456789_-', '', true)) errorMessage += "Please ensure your slug contains only alphanumeric characters, dashes or underscores.\n";
				if (!document.editContentForm.content.value) errorMessage += "Please provide the full message to be displayed.\n";
			}
		// redirects
			else if (document.editContentForm.contentType.value == 'r') {
				if (!document.editContentForm.slug.value) errorMessage += "Please specify a URL to redirect.\n";
				else if (!isStringValid_TL(document.editContentForm.slug.value, 'abcdefghijklmnopqrstuvwxyz0123456789_-', '', true)) errorMessage += "Please ensure your slug contains only alphanumeric characters, dashes or underscores.\n";
				if (!document.editContentForm.redirect_to.value) errorMessage += "Please specify a destination for your redirect.\n";
			}
		
		if (errorMessage) alert(errorMessage);
		else document.editContentForm.submit();
		
	}

	function deleteThisContent() {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.editContentForm.deleteContent.value = 'Y';
			document.editContentForm.submit();
		}
	}
