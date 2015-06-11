
	errorMessage = '';

	// tooltips
		$('.tooltips').tooltip({
			placement: 'bottom',
			delay: 250
		});

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
