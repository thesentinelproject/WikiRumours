<?php
	$sectionTitle = "Profile";
	$pageTitle = "My Password";
	include 'includes/views/desktop/shared/page_top.php';

	echo "    <h2>Update Password</h2>\n";

	echo $form->start('passwordForm', null, 'post', null, null, array('onSubmit'=>'validatePasswordForm(); return false;'));
	/* Old password */ 	if (!$logged_in['is_administrator']) echo $form->row('password', 'old_password', '', true, 'Old password', 'form-control', '', 72);
	/* New password */ 	echo $form->row('password_with_health_meter', 'password', '', true, 'New Password', 'form-control', '', 72);
	/* Confirm */ 		echo $form->row('password', 'confirm_new_password', '', true, 'Confirm', 'form-control', '', 72);
	/* Actions */		echo $form->row('submit', 'update', null, false, 'Update', 'btn btn-medium btn-info');

	echo $form->end() . "\n";

	include 'includes/views/desktop/shared/page_bottom.php';
?>