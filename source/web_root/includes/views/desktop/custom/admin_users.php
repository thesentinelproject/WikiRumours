<?php

	echo "  <h2>" . (@$numberOfUsers ? "<span class='label label-default'>" . @$numberOfUsers . "</span> " : false) . "Users</h2>\n\n";

	echo $form->start('adminUsersFilterForm', null, 'post') . "\n";
	echo $form->input('hidden', 'sort_by', @$filters['sort_by']) . "\n";
	echo $form->input('hidden', 'sort_by_direction', @$filters['sort_by_direction']) . "\n";
	echo $form->input('hidden', 'exportData') . "\n";

	// display users
		echo "  <table class='table table-condensed'>\n";
		echo "  <thead>\n";
		echo "  <tr>\n";
		echo "  <th><a href='' onClick='changeSort(" . '"user"' . "); return false;'>User</a></th>\n";
		echo "  <th><a href='' onClick='changeSort(" . '"location"' . "); return false;'>Location</a></th>\n";
		echo "  <th><a href='' onClick='changeSort(" . '"registered"' . "); return false;'>Registered</a></th>\n";
		echo "  <th class='nowrap'><a href='' onClick='changeSort(" . '"last_login"' . "); return false;'>Last login</a></th>\n";
		echo "  <th></th>\n";
		echo "  </tr>\n";
		echo "  </thead>\n";
		echo "  <tbody>\n";
		for ($counter = 0; $counter < count($users); $counter++) {
			if (!$users[$counter]['enabled']) echo "  <tr class='error'>\n";
			else echo "  <tr>\n";
			// name
				echo "  <td><a href='/profile/" . $users[$counter]['username'] . "' class='tooltips' data-toggle='tooltip' title='" . addSlashes($users[$counter]['full_name']) . "'>" . $users[$counter]['username'] . "</a></td>\n";
			// location
				$locationMap = trim(@$users[$counter]['city'] . ', ' . trim(@$users[$counter]['region'] . ', ' . @$users[$counter]['other_region'], ', ') . ', ' . @$users[$counter]['country'], ',- ');
				$locationLabel = @$users[$counter]['country'];
				if (@$users[$counter]['city']) $locationLabel .= " (" . @$users[$counter]['city'] . ")";
				echo "  <td><a href='https://maps.google.com/maps?q=" . urlencode($locationMap) . "' target='_blank'>" . $locationLabel . "</a></td>\n";
			// registered
				echo "  <td class='nowrap'>" . date('j-M-Y', strtotime($users[$counter]['registered_on'])) . "</td>\n";
			// last login
				if ($users[$counter]['last_login'] && $users[$counter]['last_login'] != '0000-00-00 00:00:00') echo "  <td class='nowrap'>" . date('j-M-Y', strtotime($users[$counter]['last_login'])) . "</td>\n";
				else echo "  <td>Never</td>\n";
			// email
				echo "  <td>\n";
				if ($users[$counter]['email']) echo "    <a href='mailto:" . $users[$counter]['email'] . "' class='tooltips' data-toggle='tooltip' title='" . $users[$counter]['email'] . "'><span class='glyphicon glyphicon-envelope' aria-hidden='true'></span></a>\n";
				if ($users[$counter]['is_administrator']) echo "  <span class='tooltips' data-toggle='tooltip' title='Administrator'><a href='' onClick='changeUserType(" . '"is_administrator"' . "); return false;'><span class='glyphicon glyphicon-certificate' aria-hidden='true'></span></a></span>\n";
				if ($users[$counter]['is_moderator']) echo "  <span class='tooltips' data-toggle='tooltip' title='Moderator'><a href='' onClick='changeUserType(" . '"is_moderator"' . "); return false;'><span class='glyphicon glyphicon-phone' aria-hidden='true'></span></a></span>\n";
				if ($users[$counter]['is_community_liaison']) echo "  <span class='tooltips' data-toggle='tooltip' title='Community Liaison'><a href='' onClick='changeUserType(" . '"is_community_liaison"' . "); return false;'><span class='glyphicon glyphicon-bullhorn' aria-hidden='true'></span></a></span>\n";
				if ($users[$counter]['is_proxy']) echo "  <span class='tooltips' data-toggle='tooltip' title='Proxy'><a href='' onClick='changeUserType(" . '"is_proxy"' . "); return false;'><span class='glyphicon glyphicon-adjust' aria-hidden='true'></span></a></span>\n";
				if ($users[$counter]['is_tester']) echo "  <span class='tooltips' data-toggle='tooltip' title='Tester'><a href='' onClick='changeUserType(" . '"is_tester"' . "); return false;'><span class='glyphicon glyphicon-scale' aria-hidden='true'></span></a></span>\n";
				echo "  </td>\n";
			echo "  </tr>\n";
		}
		echo "  </tbody>\n";
		echo "  </table>\n\n";
	
	// filters
		echo "  <div class='row'>\n";
		// keywords
			echo "    <div class='col-lg-2 col-md-2 col-sm-4 col-xs-4'>\n";
			echo "      " . $form->input('search', 'keywords', @$filters['keywords'], false, null, 'form-control') . "\n";
			echo "    </div>\n";
		// user types
			echo "    <div class='col-lg-3 col-md-3 col-sm-3 col-xs-2'>\n";
			echo "      " . $form->input('select', 'user_type', @$filters['user_type'], false, '|All users', 'form-control', array('is_administrator'=>'Administrators', 'is_moderator'=>'Moderators', 'is_proxy'=>'Proxy', 'is_community_liaison'=>'Community Liaison', 'is_tester'=>'Testers'), null, null, null, array('onChange'=>'document.adminUsersFilterForm.submit();')) . "\n";
			echo "    </div>\n";
		// anonymous
			echo "    <div class='col-lg-3 col-md-3 col-sm-3 col-xs-2'>\n";
			echo "      " . $form->input('select', 'hide_anonymous', @$filters['hide_anonymous'], true, null, 'form-control', array('N'=>'Show anonymous', 'Y'=>'Hide anonymous'), null, null, null, array('onChange'=>'document.adminUsersFilterForm.submit();')) . "\n";
			echo "    </div>\n";
		// filter button
			echo "    <div class='col-lg-2 col-md-2 col-sm-2 col-xs-4'>\n";
			echo "      " . $form->input('submit', 'filter_button', null, false, 'Filter', 'btn btn-info btn-block') . "\n";
			echo "    </div>\n";
		// export button
			echo "    <div class='col-lg-2 col-md-2 hidden-sm hidden-xs text-right'>\n";
			echo "      " . $form->input('button', 'export_button', null, false, 'Export', 'btn btn-link', null, null, null, null, array('onClick'=>'exportUsers(); return false;')) . "\n";
			echo "    </div>\n";
		echo "  </div>\n";
		echo $form->end() . "\n";

	// pagination
		if ($numberOfPages > 1) {
			echo "  <br />\n";
			echo "  <div class='row'>\n";
			echo "    <div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>\n";
			echo $form->paginate($filters['page'], $numberOfPages, '/admin_users/' . $keyvalue_array->updateKeyValue($parameter1, 'page', '#', '|'));
			echo "    </div>\n";
			echo "  </div>\n";
		}
	
?>