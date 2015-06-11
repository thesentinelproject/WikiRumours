<?php

	function populateStatusesTagsAndSources() {

		global $oldRumourStatuses; // to be removed once API v1 is officially deprecated
		global $rumourStatuses;
		global $rumourTags;
		global $rumourSources;
		global $rumourPriorities;
		global $locationTypes;

		// status
			$oldRumourStatuses = array();
			$rumourStatuses = array();

			$result = retrieveFromDb('statuses');
			for ($counter = 0; $counter < count($result); $counter++) {
				$rumourStatuses[$result[$counter]['status_id']] = $result[$counter]['status'];
				$oldRumourStatuses[$result[$counter]['abbreviation']] = $result[$counter]['status'];
			}

		// tags
			$rumourTags = array();
			$result = retrieveFromDb('tags');
			for ($counter = 0; $counter < count($result); $counter++) {
				$rumourTags[$result[$counter]['tag_id']] = $result[$counter]['tag'];
			}
			natcasesort($rumourTags);

		// sources
			$rumourSources = array();
			$result = retrieveFromDb('sources');
			for ($counter = 0; $counter < count($result); $counter++) {
				$rumourSources[$result[$counter]['source_id']] = ucwords(strtolower($result[$counter]['source']));
			}

		// priorities
			$rumourPriorities = array();
			$result = retrieveFromDb('priorities');
			for ($counter = 0; $counter < count($result); $counter++) {
				$rumourPriorities[$result[$counter]['priority_id']] = ucwords(strtolower($result[$counter]['priority']));
			}

		//
			$locationTypes = array();
			$result = retrieveFromDb('location_types');
			for ($counter = 0; $counter < count($result); $counter++) {
				$locationTypes[$result[$counter]['name']] = $result[$counter]['name'];
			}

	}

?>