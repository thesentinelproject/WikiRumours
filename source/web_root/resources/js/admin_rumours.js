
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
			document.adminRumoursForm.submit()
		}

	// export
		function exportUsers() {
			document.adminRumoursForm.exportData.value = 'Y';
			document.adminRumoursForm.submit()
		}
