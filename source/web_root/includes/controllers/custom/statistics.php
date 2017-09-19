<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// rumours
		if (@$tl->page['domain_alias']['cms_id']) $result = countInDb('rumours', 'rumour_id', array('enabled'=>'1', 'domain_alias_id'=>$tl->page['domain_alias']['cms_id']));
		else $result = countInDb('rumours', 'rumour_id', array('enabled'=>'1'));
		$numberOfRumours = floatval(@$result[0]['count']);

	// sightings
		if (@$tl->page['domain_alias']['cms_id']) {
			$result = retrieveSightings(array('domain_alias_id'=>$tl->page['domain_alias']['cms_id']));
			$numberOfSightings = count($result);
		}
		else {
			$result = countInDb('rumour_sightings', 'sighting_id');
			$numberOfSightings = floatval(@$result[0]['count']);
		}

	// rumours and sightings over time
		$rumoursAndSightingsByDateChart = array();
		// sightings
			$result = directlyQueryDb("SELECT DATE_FORMAT(heard_on, '%M %Y') AS month, COUNT(sighting_id) AS count FROM " . $tablePrefix . "rumour_sightings LEFT JOIN " . $tablePrefix . "rumours ON " . $tablePrefix . "rumour_sightings.rumour_id = " . $tablePrefix . "rumours.rumour_id WHERE heard_on > '0000-00-00 00:00:00' " . (@$tl->page['domain_alias']['cms_id'] ? " AND " . $tablePrefix . "rumours.domain_alias_id = '" . $tl->page['domain_alias']['cms_id'] . "'" : false) . "GROUP BY month ORDER BY heard_on ASC");
			for ($counter = 0; $counter < count($result); $counter++) {
				$month = $result[$counter]['month'];
				if (!count(@$rumoursAndSightingsByDateChart[$month])) $rumoursAndSightingsByDateChart[$month] = array();
				$rumoursAndSightingsByDateChart[$month]['sightings'] = $result[$counter]['count'];
			}
		// rumours
			$result = retrieveFromDb('rumours', array("DATE_FORMAT(occurred_on, '%M %Y')"=>'month', "COUNT(rumour_id)"=>'count'), null, null, null, null, "occurred_on > '0000-00-00 00:00:00'" . (@$tl->page['domain_alias']['cms_id'] ? " AND " . $tablePrefix . "rumours.domain_alias_id = '" . $tl->page['domain_alias']['cms_id'] . "'" : false), 'month', 'occurred_on ASC');
			for ($counter = 0; $counter < count($result); $counter++) {
				$month = $result[$counter]['month'];
				if (!count(@$rumoursAndSightingsByDateChart[$month])) $rumoursAndSightingsByDateChart[$month] = array();
				$rumoursAndSightingsByDateChart[$month]['rumours'] = $result[$counter]['count'];
			}
		$rumoursAndSightingsByDateTable = array_reverse($rumoursAndSightingsByDateChart);

	// statuses
		$statuses = directlyQueryDb("SELECT " . $tablePrefix . "statuses.status, " . $tablePrefix . "statuses.hex_color, COUNT(" . $tablePrefix . "statuses.status) AS count FROM " . $tablePrefix . "rumours LEFT JOIN " . $tablePrefix . "statuses ON " . $tablePrefix . "rumours.status_id = " . $tablePrefix . "statuses.status_id" . (@$tl->page['domain_alias']['cms_id'] ? " WHERE domain_alias_id = '" . $tl->page['domain_alias']['cms_id'] . "'" : false ) . " GROUP BY " . $tablePrefix . "statuses.status ORDER BY position ASC");

	// tags
		$numberOfTagsToDisplay = 20;
		$tags = directlyQueryDb("SELECT " . $tablePrefix . "tags.tag, COUNT(" . $tablePrefix . "tags.tag) AS count FROM " . $tablePrefix . "rumours_x_tags LEFT JOIN " . $tablePrefix . "tags ON " . $tablePrefix . "rumours_x_tags.tag_id = " . $tablePrefix . "tags.tag_id LEFT JOIN " . $tablePrefix . "rumours ON " . $tablePrefix . "rumours_x_tags.rumour_id = " . $tablePrefix . "rumours.rumour_id" . (@$tl->page['domain_alias']['cms_id'] ? " WHERE " . $tablePrefix . "rumours.domain_alias_id = '" . $tl->page['domain_alias']['cms_id'] . "'" : false ) . " GROUP BY " . $tablePrefix . "tags.tag ORDER BY count DESC" . ($numberOfTagsToDisplay ? " LIMIT " . $numberOfTagsToDisplay : false));

	// rumours and sightings by domain alias
		if (!@$tl->page['domain_alias']['cms_id']) {
			$rumoursAndSightingsByDomain = directlyQueryDb("SELECT " . $tablePrefix . "cms.title, (SELECT COUNT(*) FROM " . $tablePrefix . "rumours WHERE " . $tablePrefix . "rumours.domain_alias_id = " . $tablePrefix . "cms.cms_id) AS number_of_rumours, (SELECT COUNT(*) FROM " . $tablePrefix . "rumour_sightings LEFT JOIN " . $tablePrefix . "rumours ON " . $tablePrefix . "rumours.rumour_id = " . $tablePrefix . "rumour_sightings.rumour_id WHERE " . $tablePrefix . "rumours.domain_alias_id = " . $tablePrefix . "cms.cms_id) AS number_of_sightings FROM " . $tablePrefix . "cms WHERE " . $tablePrefix . "cms.content_type = 'd' ORDER BY " . $tablePrefix . "cms.title ASC");
		}
	
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
		
	if (count($_POST) > 0) {
	}
	
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>