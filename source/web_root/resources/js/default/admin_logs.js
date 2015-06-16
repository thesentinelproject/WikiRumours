
	errorMessage = '';

	// toggles
		function revealOrHideLog(logID) {
			$('#preview_' + logID).collapse('toggle');
			$('#activity_' + logID).collapse('toggle');
		}

		function moreOrLessSelector(logID) {
			$('#moreSelector_' + logID).collapse('toggle');
			$('#lessSelector_' + logID).collapse('toggle');
		}

	// export
		function exportUsers() {
			document.adminLogsForm.exportData.value = 'Y';
			document.adminLogsForm.submit()
		}

	// keyword filter
		function validateAdminLogsForm() {
			errorMessage = '';
			if (!isStringValid_TL(document.adminLogsForm.keywords.value, "abcdefghijklmnopqrstuvwxyz0123456789-' ", '', '')) errorMessage += "Please specify only alphanumeric characters.\n";
			if (errorMessage) alert(errorMessage);
			else document.adminLogsForm.submit();
		}
