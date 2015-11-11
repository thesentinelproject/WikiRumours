<?php

	echo "  <h2>" . (@$numberOfRumours ? "<span class='label label-default'>" . @$numberOfRumours . "</span> " : false) . "Rumours</h2>\n\n";

	echo "  " . $form->start('adminRumoursForm', '', 'post') . "\n";
	echo $form->input('hidden', 'sort_by', @$filters['sort_by']) . "\n";
	echo $form->input('hidden', 'sort_by_direction', @$filters['sort_by_direction']) . "\n";
	echo $form->input('hidden', 'exportData') . "\n";
	if (count($rumours) < 1) echo "  <p>None.</p>\n";
	else {
		echo "  <table class='table table-hover table-condensed'>\n";
		echo "  <thead>\n";
		echo "  <tr>\n";
		echo "  <th><a href='' onClick='changeSort(" . '"rumour"' . "); return false;'>Rumour</a></th>\n";
		echo "  <th colspan='2' class='hidden-xs'><a href='' onClick='changeSort(" . '"date"' . "); return false;'>Updated</a></th>\n";
		echo "  <th><a href='' onClick='changeSort(" . '"location"' . "); return false;'>Location</a></th>\n";
		echo "  <th><a href='' onClick='changeSort(" . '"status"' . "); return false;'>Status</a></th>\n";
		echo "  <th class='visible-lg'><a href='' onClick='changeSort(" . '"assigned_to"' . "); return false;'>Assigned to</a></th>\n";
		echo "  <th class='hidden-xs'><span class='tooltips' data-toggle='tooltip' title='Number of sightings'><span class='glyphicon glyphicon-eye-open'></span></span></th>\n";
		echo "  <th class='hidden-xs'><span class='tooltips' data-toggle='tooltip' title='Number of comments'><span class='glyphicon glyphicon-comment'></span></span></th>\n";
		echo "  <th class='hidden-xs'><span class='tooltips' data-toggle='tooltip' title='Number of watchlists'><span class='glyphicon glyphicon-align-justify'></span></span></th>\n";
		echo "  </tr>\n";
		echo "  </thead>\n";
		echo "  <tbody>\n";
		for ($counter = 0; $counter < count($rumours); $counter++) {
			echo "  <tr>\n";
			// rumour
				echo "  <td><a href='/rumour/" . $rumours[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($rumours[$counter]['description']) . "' class='popovers' data-placement='top' data-toggle='popover' data-content=" . '"' . htmlspecialchars($rumours[$counter]['description'], ENT_QUOTES) . '"' . ">" . $parser->truncate($rumours[$counter]['description'], 'w', 3) . "</a></td>\n";
			// date
				echo "  <td class='nowrap hidden-xs'>" . date('j-M-Y', strtotime($rumours[$counter]['updated_on'])) . "</td>\n";
				if ($rumours[$counter]['update_by'] && $rumours[$counter]['update_by'] < date('Y-m-d H:i:s')) echo "      <td class='nowrap hidden-xs'><span class='glyphicon glyphicon-time transluscent' title='This rumour is overdue an update!'></span></td>\n";
				else echo "  <td class='nowrap hidden-xs'></td>";
			// location
				$locationMap = trim(@$rumours[$counter]['city'] . ', ' . @$rumours[$counter]['country'], ', ');
				$locationLabel = @$rumours[$counter]['country'];
				if (@$rumours[$counter]['city']) $locationLabel .= " (" . @$rumours[$counter]['city'] . ")";
				echo "  <td><a href='https://maps.google.com/maps?q=" . urlencode($locationMap) . "' target='_blank'>" . $locationLabel . "</a></td>\n";
			// status
				echo "  <td>" . $rumours[$counter]['status'] . "</td>\n";
			// assigned to
				echo "  <td class='nowrap visible-lg'><a href='/profile/" . $rumours[$counter]['assigned_to_username'] . "'>" . $rumours[$counter]['assigned_to_full_name'] . "</a></td>\n";
			// other
				echo "  <td class='text-center hidden-xs'>" . floatval($rumours[$counter]['number_of_sightings']) . "</td>\n";
				echo "  <td class='text-center hidden-xs'>" . floatval($rumours[$counter]['number_of_comments']) . "</td>\n";
				echo "  <td class='text-center hidden-xs'>" . floatval($rumours[$counter]['number_of_watchlists']) . "</td>\n";
			echo "  </tr>\n";
		}
		echo "  </tbody>\n";
		echo "  </table>\n";
	}

	// filters
		echo "  <div class='row'>\n";
		// keywords
			echo "    <div class='col-lg-2 col-md-2 col-sm-4 col-xs-4'>\n";
			echo "      " . $form->input('search', 'keywords', @$filters['keywords'], false, null, 'form-control') . "\n";
			echo "    </div>\n";
		// status
			echo "    <div class='col-lg-3 col-md-3 col-sm-3 col-xs-2'>\n";
			echo "      " . $form->input('select', 'rumour_status', @$filters['rumour_status'], false, '|All rumours', 'form-control', $rumourStatuses, null, null, null, array('onChange'=>'document.adminRumoursForm.submit();')) . "\n";
			echo "    </div>\n";
		// country
			echo "    <div class='col-lg-3 col-md-3 col-sm-3 col-xs-2'>\n";
			echo "      " . $form->input('country', 'rumour_country', @$filters['rumour_country'], false, 'All countries', 'form-control', null, null, null, null, array('onChange'=>'document.adminRumoursForm.submit();')) . "\n";
			echo "    </div>\n";
		// filter button
			echo "    <div class='col-lg-2 col-md-2 col-sm-2 col-xs-4'>\n";
			echo "      " . $form->input('submit', 'filter_button', null, false, 'Filter', 'btn btn-info btn-block') . "\n";
			echo "    </div>\n";
		// export button
			echo "    <div class='col-lg-2 col-md-2 hidden-sm hidden-xs btn btn-link text-right'>\n";
			echo "      <a href='/export/" . urlencode($keyvalue_array->updateKeyValue($parameter1, 'report', 'rumours', '|')) . "' target='_blank'>Export</a>\n";
			echo "    </div>\n";
		echo "  </div>\n";
		echo $form->end() . "\n";

	// pagination
		if ($numberOfPages > 1) {
			echo "  <br />\n";
			echo "  <div class='row'>\n";
			echo "    <div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>\n";
			echo $form->paginate($filters['page'], $numberOfPages, '/admin_rumours/' . $keyvalue_array->updateKeyValue($parameter1, 'page', '#', '|'));
			echo "    </div>\n";
			echo "  </div>\n";
		}
	
?>