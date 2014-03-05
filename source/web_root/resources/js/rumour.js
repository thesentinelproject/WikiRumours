
	errorMessage = '';

	function removeSighting(sightingID) {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.attributionForm.sightingToRemove.value = sightingID;
			document.attributionForm.submit();
		}
	}

	function removeTag(tagID) {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.editTagsForm.tagToRemove.value = tagID;
			document.editTagsForm.submit();
		}
	}

	function validateeditTagsForm() {
		errorMessage = '';
		if (!document.editTagsForm.new_tag.value) errorMessage += "Please specify one or more tags.\n";
		if (errorMessage) alert(errorMessage);
		else document.editTagsForm.submit();
	}
	
	function validateAddSightingForm() {
		errorMessage = '';
		if (!document.rumourActionsForm.country.value) errorMessage += "Please specify a country.\n";
		if (!document.rumourActionsForm.heard_on.value) errorMessage += "Please specify a date.\n";
		if (errorMessage) alert(errorMessage);
		else {
			document.rumourActionsForm.addThisSighting.value = "Y";
			document.rumourActionsForm.submit();
		}
	}
	
	function validateAddToWatchlist() {
		document.rumourActionsForm.addToWatchlist.value = "Y";
		document.rumourActionsForm.submit();
	}
	
	function validateRemoveFromWatchlist() {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.rumourActionsForm.removeFromWatchlist.value = "Y";
			document.rumourActionsForm.submit();
		}
	}

	function validateAddCommentForm() {
		errorMessage = '';
		if (!document.rumourActionsForm.new_comment.value) errorMessage += "Please provide a comment.\n";
		if (errorMessage) alert(errorMessage);
		else document.rumourActionsForm.submit();
	}
	
	function validateModerateCommentsForm(action, commentID) {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			if (action == 'flag') document.moderateCommentsForm.commentToFlag.value = commentID;
			else if (action == 'disable') document.moderateCommentsForm.commentToDisable.value = commentID;
			else if (action == 'enable') document.moderateCommentsForm.commentToEnable.value = commentID;
			document.moderateCommentsForm.submit();
		}
	}