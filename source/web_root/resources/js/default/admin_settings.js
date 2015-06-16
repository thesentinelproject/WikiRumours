
	function deleteSetting() {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.editSettingForm.deleteThisSetting.value = 'Y';
			document.editSettingForm.submit();
		}
	}
