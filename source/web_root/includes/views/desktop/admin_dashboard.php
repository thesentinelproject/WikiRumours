<?php
	include 'includes/views/desktop/shared/page_top.php';

/*	--------------------------------------
	Admin dashboard
	-------------------------------------- */
	
	if ($pageError) echo "      <div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $pageError . "</div>\n";
	elseif ($pageSuccess == 'alert_resolved') echo "      <div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully marked alert as resolved.</div>\n";
	elseif ($pageSuccess == 'email_sent') echo "      <div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully sent email.</div>\n";
	elseif ($pageSuccess == 'registrant_approved') echo "      <div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully approved registrant.</div>\n";
	elseif ($pageSuccess == 'registrant_deleted') echo "      <div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Successfully deleted registrant.</div>\n";

	$pageJavaScript .= "// tooltips\n";
	$pageJavaScript .= "  $('.tooltips').tooltip({\n";
	$pageJavaScript .= "    placement: 'left',\n\n";
	$pageJavaScript .= "    delay: 250\n\n";
	$pageJavaScript .= "  });\n\n";
	
	if ($report == 'dashboard') {
		
		// alerts
			if (count($alerts) > 0) {
				echo "<div class='pageModule'>\n";
				echo "  <h2>Alerts</h2>\n";
				echo "  " . $form->start('dashboardAlertsForm', null, 'post', null, null, array('onSubmit'=>'return false;')) . "\n";
				echo "  " . $form->input('hidden', 'alertToResolve') . "\n";
				echo "  <table class='table table-condensed table-hover'>\n";
				echo "  <thead>\n";
				echo "  <tr>\n";
				echo "  <th>Date</th>\n";
				echo "  <th>Activity</th>\n";
				echo "  <th>Resolve</th>\n";
				echo "  </tr>\n";
				echo "  </thead>\n";
				echo "  <tbody>\n";
				for ($counter = 0; $counter < min(5, count($alerts)); $counter++) {
					echo "  <tr>\n";
					echo "  <td>" . str_replace(' ', '&nbsp;', date('M j, Y, \a\t g:i:s A', strtotime($alerts[$counter]['connected_on']))) . "</td>\n";
					echo "  <td>";
					echo str_replace(';', '<br />', $alerts[$counter]['activity']);
					if ($alerts[$counter]['error_message']) echo "<br />(" . $alerts[$counter]['error_message'] . ")";
					echo "</td>\n";
					echo "  <td><div class='tableButtonAlignment'>" . $form->input('button', null, null, false, 'Resolved', 'btn btn-default btn-sm', null, null, null, null, array('onClick'=>'resolveAlert("' . $alerts[$counter]['log_id'] . '"); return false;')) . "</div></td>\n";
					echo "  </tr>\n";
				}
				echo "  </tbody>\n";
				echo "  </table>\n";
				if (count($logs) > 0) echo "  <br /><br />" . $form->input('button', null, null, false, 'See all logs', 'btn btn-default', null, null, null, null, array('onClick'=>'document.location.href = "/admin/logs"; return false;')) . "\n";
				echo "  " . $form->end() . "\n";
				echo "</div>\n";
			}
			
		// rumours
			echo "<div class='pageModule'>\n";
			echo "  <h2>Unassigned rumours</h2>\n";
			if (count($rumours) < 1) echo "  <p>None.</p>\n";
			else {
				echo "  <table class='table table-hover table-condensed'>\n";
				echo "  <tr>\n";
				echo "  <th colspan='2'>Rumour</th>\n";
				echo "  <th>Status</th>\n";
				echo "  </tr>\n";
				for ($counter = 0; $counter < min(count($rumours), $numberOfRumoursToDisplay); $counter++) {
					echo "  <tr>\n";
					if ($rumours[$counter]['updated_on'] != '0000-00-00 00:00:00') echo "  <td>" . $parser->bubbleDate($rumours[$counter]['updated_on']) . "</td>\n";
					else echo "  <td>" . $parser->bubbleDate($rumours[$counter]['created_on']) . "</td>\n";
					echo "  <td><a href='/rumour/" . $rumours[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($rumours[$counter]['description']) . "'>" . $parser->truncate($rumours[$counter]['description'], 'c', 60) . "</a></td>\n";
					echo "  <td>" . $rumourStatuses[$rumours[$counter]['status']] . "</td>\n";
					echo "  </tr>\n";
				}
				echo "  </table>\n";
				echo "  " . $form->input('button', null, null, false, 'See all rumours', 'btn btn-default', null, null, null, null, array('onClick'=>'document.location.href = "/admin_dashboard/rumours"; return false;')) . "\n";
			}
			echo "</div>\n";

		// flagged comments
			echo "<div class='pageModule'>\n";
			echo "  <h2>Flagged comments</h2>\n";
			if (count($flaggedComments) < 1) echo "  <p>None.</p>\n";
			else {
				echo "  <table class='table table-hover table-condensed'>\n";
				echo "  <tr>\n";
				echo "  <th colspan='2'>Comment</th>\n";
				echo "  <th>Author</th>\n";
				echo "  <th>Rumour</th>\n";
				echo "  <th>Flags</th>\n";
				echo "  </tr>\n";
				for ($counter = 0; $counter < count($flaggedComments); $counter++) {
					echo "  <tr>\n";
					echo "  <td>" . $parser->bubbleDate(date('d-M-Y', strtotime($flaggedComments[$counter]['comment_created_on']))) . "</td>\n";
					echo "  <td>" . $flaggedComments[$counter]['comment'] . "</td>\n";
					echo "  <td><a href='/profile/" . $flaggedComments[$counter]['comment_created_by'] . "'>" . $flaggedComments[$counter]['comment_created_by_full_name'] . "</a></td>\n";
					echo "  <td><a href='/rumour/" . $flaggedComments[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($flaggedComments[$counter]['description']) . "'>" . $parser->truncate($flaggedComments[$counter]['description'], 'c', 30) . "</a></td>\n";
					echo "  <td>" . floatval($flaggedComments[$counter]['number_of_flags']) . "</td>\n";
					echo "  </tr>\n";
				}
				echo "  </table>\n";
			}
			echo "</div>\n";
			
		// users
			echo "<div class='pageModule'>\n";
			echo "  " . $form->start() . "\n";
			echo "  <h2>Users</h2>\n";
			echo "  <table class='table table-condensed table-hover'>\n";
			echo "  <thead>\n";
			echo "  <tr>\n";
			echo "  <th>User</th>\n";
			echo "  <th>Email</th>\n";
			echo "  <th>Location</th>\n";
			echo "  <th>Registered</th>\n";
			echo "  <th>Last login</th>\n";
			echo "  </tr>\n";
			echo "  </thead>\n";
			echo "  <tbody>\n";
			for ($counter = 0; $counter < min(5, count($users)); $counter++) {
				if (!$users[$counter]['enabled']) echo "  <tr class='error'>\n";
				else echo "  <tr>\n";
				echo "  <td><a href='/profile/" . $users[$counter]['username'] . "' class='tooltips' data-toggle='tooltip' title='" . addSlashes($users[$counter]['full_name']) . "'>" . $users[$counter]['username'] . "</a></td>\n";
				echo "  <td><a href='mailto:" . $users[$counter]['email'] . "'>" . $users[$counter]['email'] . "</a></td>\n";
				$location = trim(@$users[$counter]['region'] . ', ' . trim(@$users[$counter]['province_state'] . ', ' . @$users[$counter]['other_province_state'], ', ') . ', ' . @$countries[@$users[$counter]['country']], ',- ');
				echo "      <td><a href='https://maps.google.com/maps?q=" . urlencode($location) . "' target='_blank'>" . $location . "</a></td>\n";
				echo "  <td class='nowrap'>" . $parser->bubbleDate(date('Y-m-d', strtotime($users[$counter]['registered_on']))) . "</td>\n";
				if ($users[$counter]['last_login'] != '0000-00-00 00:00:00') echo "  <td class='nowrap'>" . $parser->bubbleDate(date('Y-m-d', strtotime($users[$counter]['last_login']))) . "</td>\n";
				else echo "  <td class='text-center'>Never</td>\n";
				echo "  </tr>\n";
			}
			echo "  </tbody>\n";
			echo "  </table>\n";
			if (count($users) > 5) echo "  " . $form->input('button', null, null, false, 'See all users', 'btn btn-default', null, null, null, null, array('onClick'=>'document.location.href = "/admin_dashboard/users"; return false;')) . "\n";
			echo "  " . $form->end() . "\n";
			echo "</div>\n";
			
		// registrations
			if (count($registrants) > 0) {
				echo "<div class='pageModule'>\n";
				echo "  " . $form->start('editRegistrantsForm', '', 'post', 'form-inline', null, array('onSubmit'=>'return false;')) . "\n";
				echo "  " . $form->input('hidden', 'registrantToApprove') . "\n";
				echo "  " . $form->input('hidden', 'registrantToDelete') . "\n";
				echo "  <h2>Pending Registrants</h2>\n";
				echo "  <table class='table table-condensed table-hover'>\n";
				echo "  <thead>\n";
				echo "  <tr>\n";
				echo "  <th>User</th>\n";
				echo "  <th>Email</th>\n";
				echo "  <th>Location</th>\n";
				echo "  <th>Registered</th>\n";
				if ($logged_in['can_edit_users']) echo "  <th></th>\n";
				echo "  </tr>\n";
				echo "  </thead>\n";
				echo "  <tbody>\n";
				for ($counter = 0; $counter < count($registrants); $counter++) {
					echo "  <tr>\n";
					echo "  <td><span class='tooltips' data-toggle='tooltip' title='" . addSlashes($registrants[$counter]['full_name']) . "'>" . $registrants[$counter]['username'] . "</span></td>\n";
					echo "  <td><a href='mailto:" . $registrants[$counter]['email'] . "'>" . $registrants[$counter]['email'] . "</a></td>\n";
					$location = trim($registrants[$counter]['city'] . ', ' . $registrants[$counter]['province_state'] . ', ' . $countries[$registrants[$counter]['country']], ',- ');
					echo "      <td><a href='https://maps.google.com/maps?q=" . urlencode($location) . "' target='_blank'>" . $location . "</a></td>\n";
					echo "  <td class='nowrap'>" . $parser->bubbleDate(date('Y-m-d', strtotime($registrants[$counter]['registered_on']))) . "</td>\n";
					if ($logged_in['can_edit_users']) {
						echo "  <td class='text-right nowrap'>\n";
						echo "    <a href='javascript:void(0)' class='noUnderscore' onClick='approveRegistrant(" . '"' . $registrants[$counter]['registration_id'] . '"' . "); return false;'><span class='label label-default'>APPROVE</span></a>\n";
						echo "    <a href='javascript:void(0)' class='noUnderscore' onClick='deleteRegistrant(" . '"' . $registrants[$counter]['registration_id'] . '"' . "); return false;'><span class='label label-danger'>DELETE</span></a>\n";
						echo "  </td>\n";
					}
					echo "  </tr>\n";
				}
				echo "  </tbody>\n";
				echo "  </table>\n";
				echo "  " . $form->end() . "\n";
				echo "</div>\n";
			}
			
		// send email
			if ($logged_in['can_send_email']) {
				echo "<div class='pageModule'>\n";
				echo "  <h2>Send email</h2>\n";
				echo "  " . $form->start('emailUserForm', null, 'post', null, null, array('onSubmit'=>'validateEmailUserForm(); return false;')) . "\n";
				/* Name */		echo "  " . $form->row('text', 'name', @$_POST['name'], true, 'Name|Recipient name', 'form-control') . "\n";
				/* Email */		echo "  " . $form->row('email', 'email', @$_POST['email'], true, 'Email|Recipient email', 'form-control') . "\n";
				/* Reply to */	echo "  " . $form->row('email', 'reply_to', $operators->firstTrue(@$_POST['reply_to'], $logged_in['email']), false, 'Reply to|Sender email (optional)', 'form-control') . "\n";
				/* Message */	echo "  " . $form->row('textarea', 'message', @$_POST['message'], true, 'Message', 'form-control') . "\n";
				/* Actions */	echo "  " . $form->row('submit', 'Send', null, false, 'Send now', 'btn btn-default btn-info') . "\n";
				echo "  " . $form->end() . "\n";
				echo "</div>\n";
			}
		
	}
	
/*	--------------------------------------
	Rumour report
	-------------------------------------- */
	
	elseif ($report == 'rumours') {
		echo "  " . $form->start() . "\n";
		echo "  <h2>Rumours</h2>\n\n";
		if (count($rumours) < 1) echo "  <p>None.</p>\n";
		else {
			echo "  <table class='table table-hover table-condensed'>\n";
			echo "  <thead>\n";
			echo "  <tr>\n";
			echo "  <th>Rumour</th>\n";
			echo "  <th>Status</th>\n";
			echo "  <th>Assigned to</th>\n";
			echo "  <th><!-- Sightings --><span class='glyphicon glyphicon-eye-open' title='Number of sightings'></span></th>\n";
			echo "  <th><!-- Comments --><span class='glyphicon glyphicon-comment' title='Number of comments'></span></th>\n";
			echo "  <th><!-- Watchlists --><span class='glyphicon glyphicon-align-justify' title='Number of watchlists'></span></th>\n";
			echo "  </tr>\n";
			echo "  </thead>\n";
			echo "  <tbody>\n";
			for ($counter = 0; $counter < count($rumours); $counter++) {
				echo "  <tr>\n";
				echo "  <td><a href='/rumour/" . $rumours[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($rumours[$counter]['description']) . "'>" . $parser->truncate($rumours[$counter]['description'], 'c', 40) . "</a></td>\n";
				echo "  <td>" . $rumourStatuses[$rumours[$counter]['status']] . "</td>\n";
				echo "  <td>" . $rumours[$counter]['assigned_to_full_name'] . "</td>\n";
				echo "  <td class='text-center'>" . floatval($rumours[$counter]['number_of_sightings']) . "</td>\n";
				echo "  <td class='text-center'>" . floatval($rumours[$counter]['number_of_comments']) . "</td>\n";
				echo "  <td class='text-center'>" . floatval($rumours[$counter]['number_of_watchlists']) . "</td>\n";
				echo "  </tr>\n";
			}
			echo "  </tbody>\n";
			echo "  </table>\n";
			
			if ($numberOfPages > 1) {
				echo $form->paginate($filters['page'], $numberOfPages, '/admin_dashboard/rumours/#');
			}
		}
		echo "  " . $form->input('cancel_and_return', null, null, false, 'Return', 'btn btn-default') . "\n";
		echo "  " . $form->end() . "\n";
	}
	
/*	--------------------------------------
	User report
	-------------------------------------- */
	
	elseif ($report == 'users') {
		echo "  " . $form->start() . "\n";
		echo "  <h2>Users</h2>\n\n";
		if (count($users) < 1) echo "  <p>None.</p>\n";
		else {
			echo "  <table class='table table-condensed table-hover'>\n";
			echo "  <thead>\n";
			echo "  <tr>\n";
			echo "  <th>User</th>\n";
			echo "  <th>Email</th>\n";
			echo "  <th>Location</th>\n";
			echo "  <th>Registered</th>\n";
			echo "  <th>Last login</th>\n";
			echo "  </tr>\n";
			echo "  </thead>\n";
			echo "  <tbody>\n";
			for ($counter = 0; $counter < count($users); $counter++) {
				if (!$users[$counter]['enabled']) echo "  <tr class='error'>\n";
				else echo "  <tr>\n";
				echo "  <td><a href='/profile/" . $users[$counter]['username'] . "' class='tooltips' data-toggle='tooltip' title='" . addSlashes($users[$counter]['full_name']) . "'>" . $users[$counter]['username'] . "</a></td>\n";
				echo "  <td><a href='mailto:" . $users[$counter]['email'] . "'>" . $users[$counter]['email'] . "</a></td>\n";
				$location = trim(@$users[$counter]['region'] . ', ' . trim(@$users[$counter]['province_state'] . ', ' . @$users[$counter]['other_province_state'], ', ') . ', ' . @$countries[@$users[$counter]['country']], ',- ');
				echo "  <td><a href='https://maps.google.com/maps?q=" . urlencode($location) . "' target='_blank'>" . $location . "</a></td>\n";
				echo "  <td>" . $parser->bubbleDate(date('Y-m-d', strtotime($users[$counter]['registered_on']))) . "</td>\n";
				if ($users[$counter]['last_login'] == '0000-00-00 00:00:00') echo "  <td>Never</td>\n";
				else echo "  <td>" . $parser->bubbleDate(date('Y-m-d', strtotime($users[$counter]['last_login']))) . "</td>\n";
				echo "  </tr>\n";
			}
			echo "  </tbody>\n";
			echo "  </table>\n\n";
			
			if ($numberOfPages > 1) {
				echo $form->paginate($filters['page'], $numberOfPages, '/admin_dashboard/rumours/#');
			}
		}
		echo "  " . $form->input('cancel_and_return', null, null, false, 'Return', 'btn btn-default') . "\n";
		echo "  " . $form->end() . "\n";
	}
	
	include 'includes/views/desktop/shared/page_bottom.php';
?>