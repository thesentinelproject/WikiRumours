<?php

	echo "    <h2>Update " . $user[0]['username'] . "'s Password</h2>\n";

	echo $form->start('passwordForm', null, 'post', null, null, array('onSubmit'=>'validatePasswordForm(); return false;')) . "\n";
	/* Old password */ 	if (!$logged_in['is_administrator']) echo $form->row('password', 'old_password', '', true, 'Old password', 'form-control', '', 72) . "\n";
	/* New password */ 	echo $form->row('password_with_health_meter', 'password', '', true, 'New Password', 'form-control', '', 72) . "\n";
	/* Confirm */ 		echo $form->row('password', 'confirm_new_password', '', true, 'Confirm', 'form-control', '', 72) . "\n";
	/* Actions */		echo $form->row('submit', 'update', null, false, 'Update', 'btn btn-info') . "\n";

	echo $form->end() . "\n";

?>