
	function unfollow(rumourID) {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.watchlistForm.rumourToUnfollow.value = rumourID;
			document.watchlistForm.submit();
		}
	}

	function notify(rumourID) {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.watchlistForm.rumourToNotify.value = rumourID;
			document.watchlistForm.submit();
		}
	}

	function doNotNotify(rumourID) {
		areYouSure = confirm("Are you sure?");
		if (areYouSure) {
			document.watchlistForm.rumourToUnnotify.value = rumourID;
			document.watchlistForm.submit();
		}
	}
