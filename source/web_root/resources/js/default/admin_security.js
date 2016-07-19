
	$('.bootstrap_tooltip').tooltip();

	var errorMessage = '';

	function validateBannedIpForm() {

		errorMessage = '';

		if (document.updateBannedIpForm.screen.value == 'add_banned_ip' && !document.updateBannedIpForm.ip.value) errorMessage += "Please specify an IP number. ";

		if (errorMessage) alert(errorMessage);
		else document.updateBannedIpForm.submit();
	}

	function unblock() {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.updateBannedIpForm.unblockRequested.value = 'Y';
			document.updateBannedIpForm.submit();
		}
	}
