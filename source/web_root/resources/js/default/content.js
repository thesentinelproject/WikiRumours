
	errorMessage = '';

	function validateCms() {
		errorMessage = '';
		if (document.addOrEditContent.contentType.value == 'b') {
			if (!document.addOrEditContent.slug.value) errorMessage += "Please create a slug to use as a unique descriptor for your content.\n";
		}
		else if (document.addOrEditContent.contentType.value == 'p') {
			if (!document.addOrEditContent.slug.value) errorMessage += "Please create a slug to use as a unique descriptor for your content.\n";
			else if (!isStringValid_TL(document.addOrEditContent.slug.value, 'abcdefghijklmnopqrstuvwxyz0123456789_-', '', true)) errorMessage += "Please ensure your slug contains only alphanumeric characters, dashes or underscores.\n";
		}
		if (!document.addOrEditContent.content.value) errorMessage += "Please provide content to save in the CMS.\n";
		if (document.addOrEditContent.contentType.value == 'p' && !document.addOrEditContent.title.value) errorMessage += "Please provide a page title.\n";
		if (errorMessage) alert(errorMessage);
		else document.addOrEditContent.submit();
	}

	function deleteThisContent() {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.addOrEditContent.deleteContent.value = 'Y';
			document.addOrEditContent.submit();
		}
	}
	
	function validateUploadCmsFileForm() {
		errorMessage = '';
		if (!document.uploadCmsFileForm.cmsFileUpload.value) errorMessage += "Please specify a file to upload.\n";
		if (errorMessage) alert(errorMessage);
		else document.uploadCmsFileForm.submit();
	}
	
	function deleteFile(filename) {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.uploadCmsFileForm.fileToDelete.value = filename;
			document.uploadCmsFileForm.submit();
		}
	}
	