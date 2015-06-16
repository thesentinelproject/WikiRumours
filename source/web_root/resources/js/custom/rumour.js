
	errorMessage = '';

	// jump to sightings tab
		$('#jumpToSightingsTab').click(function () {
			$('#rumourTabs a[href="#sightings"]').tab('show');
		});

/*	--------------------------------------
	Rumour
	-------------------------------------- */

	function removeTag(tagID) {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.editTagsForm.tagToRemove.value = tagID;
			document.editTagsForm.submit();
		}
	}

	function validateAddTags() {
		errorMessage = '';
		if (!document.editTagsForm.new_tags.value) errorMessage += "Please specify one or more tags.\n";
		if (errorMessage) alert(errorMessage);
		else document.editTagsForm.submit();
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

/*	--------------------------------------
	Sightings
	-------------------------------------- */

	function validateAddSightingForm() {
		errorMessage = '';
		if (!document.addSightingForm.country.value) errorMessage += "Please specify a country.\n";
		if (!document.addSightingForm.heard_on.value) errorMessage += "Please specify a date.\n";
		if (!document.addSightingForm.created_by.value) errorMessage += "Please specify who heard the rumour.\n";
		if (document.addSightingForm.created_by.value == 'add') errorMessage += addNewUser();
		if (errorMessage) alert(errorMessage);
		else document.addSightingForm.submit();
	}
	
	function removeSighting(sightingID) {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.moderateSightingsForm.sightingToRemove.value = sightingID;
			document.moderateSightingsForm.submit();
		}
	}

/*	--------------------------------------
	Comments
	-------------------------------------- */

	function validateAddCommentForm() {
		errorMessage = '';
		if (!document.addCommentForm.new_comment.value) errorMessage += "Please provide a comment.\n";
		if (errorMessage) alert(errorMessage);
		else document.addCommentForm.submit();
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