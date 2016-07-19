<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in) $authentication_manager->forceLoginThenRedirectHere(true);
		elseif (!$logged_in['is_administrator'] || !$logged_in['can_edit_users']) $authentication_manager->forceRedirect('/404');
		
	// parse query string
		if ($tl->page['parameter1']) $filters = $keyvalue_array->keyValueToArray(urldecode($tl->page['parameter1']), '|');

	// query
		$duplicates = new merge_duplicates_widget_TL();
		$duplicates->initialize([
			'this_template'=>$tl->page['template'],
			'view'=>@$filters['view'],
			'table'=>'users',
			'matching'=>null,
			'compare'=>['email', 'primary_phone', 'secondary_phone'],
			'also_display'=>['username', 'first_name', 'last_name', 'country_id', 'city', 'ok_to_contact', 'anonymous'],
			'ids_to_merge'=>@$filters['ids_to_merge'],
			'select_to_merge'=>@$filters['select_to_merge'],
			'foreign_keys'=>['rumours'=>['created_by', 'entered_by', 'updated_by'], 'rumour_sightings'=>['created_by', 'entered_by']]
		]);

	$tl->page['title'] = 'Duplicate Users';
	$tl->page['section'] = "Administration";
		
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