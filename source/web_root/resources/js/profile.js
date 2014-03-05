
	errorMessage = '';

	function friend() {
		document.friendUnfriendForm.friendRequest.value = 'Y';
		document.friendUnfriendForm.submit()
	}
	
	function unfriend() {
		document.friendUnfriendForm.unfriendRequest.value = 'Y';
		document.friendUnfriendForm.submit()
	}
	
	function acceptFriend() {
		document.friendUnfriendForm.acceptRequest.value = 'Y';
		document.friendUnfriendForm.submit()
	}
	
	function rejectFriend() {
		document.friendUnfriendForm.rejectRequest.value = 'Y';
		document.friendUnfriendForm.submit()
	}
