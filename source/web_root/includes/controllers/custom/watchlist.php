<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// authenticate user
		if (!$logged_in) $authentication_manager->forceLoginThenRedirectHere();
		
	// queries
		$watchlist = retrieveWatchlist(array('wr_watchlist.created_by'=>$logged_in['user_id']), null, null, $tablePrefix . 'watchlist.created_on DESC');
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {
		
		if ($_POST['formName'] == 'watchlistForm' && $_POST['rumourToUnfollow'] && $logged_in) {
			
			// delete
				deleteFromDb('watchlist', array('rumour_id'=>$_POST['rumourToUnfollow'], 'created_by'=>$logged_in['user_id']), null, null, null, null, 1);
			
			// redirect
				$authentication_manager->forceRedirect('/watchlist/success=rumour_removed');
			
		}
		elseif ($_POST['formName'] == 'watchlistForm' && $_POST['rumourToNotify'] && $logged_in) {

			// delete
				updateDb('watchlist', array('notify_of_updates'=>'1'), array('rumour_id'=>$_POST['rumourToNotify'], 'created_by'=>$logged_in['user_id']), null, null, null, null, 1);
			
			// redirect
				$authentication_manager->forceRedirect('/watchlist/success=notification_added');
			
		}
		elseif ($_POST['formName'] == 'watchlistForm' && $_POST['rumourToUnnotify'] && $logged_in) {

			// delete
				updateDb('watchlist', array('notify_of_updates'=>'0'), array('rumour_id'=>$_POST['rumourToUnnotify'], 'created_by'=>$logged_in['user_id']), null, null, null, null, 1);
			
			// redirect
				$authentication_manager->forceRedirect('/watchlist/success=notification_removed');
			
		}
		
	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>