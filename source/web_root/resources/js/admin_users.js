
	// tooltips
		$('.tooltips').tooltip({
			placement: 'bottom',
			delay: 250
		});

	// resort
		function changeSort(field) {
			if (document.getElementById('sort_by').value == field) {
				if (document.getElementById('sort_by_direction').value == 'ASC') document.getElementById('sort_by_direction').value = 'DESC';
				else document.getElementById('sort_by_direction').value = 'ASC';
			}
			else {
				document.getElementById('sort_by').value = field;
				document.getElementById('sort_by_direction').value = 'ASC';
			}
			document.adminUsersFilterForm.submit()
		}

	// change user type
		function changeUserType(value) {
			document.adminUsersFilterForm.user_type.value = value;
			document.adminUsersFilterForm.submit()
		}

	// export
		function exportUsers() {
			document.adminUsersFilterForm.exportData.value = 'Y';
			document.adminUsersFilterForm.submit()
		}
