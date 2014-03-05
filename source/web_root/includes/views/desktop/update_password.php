<?php
	include 'includes/views/desktop/shared/page_top.php';

	if ($pageError) echo "<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $pageError . "</div>\n";
	
	echo "    <h2>Update Password</h2>\n";

	echo $form->start('passwordForm', null, 'post', null, null, array('onSubmit'=>'validatePasswordForm(); return false;')) . "\n";
	/* Old password */ 	if (!$logged_in['is_administrator']) echo $form->row('password', 'old_password', '', true, 'Old password', 'input-block-level', '', 72) . "\n";
	/* New password */ 	echo $form->row('password_with_health_meter', 'password', '', true, 'New Password', 'input-block-level', '', 72) . "\n";
	/* Confirm */ 		echo $form->row('password', 'confirm_new_password', '', true, 'Confirm', 'input-block-level', '', 72) . "\n";
	/* Actions */		echo "<!-- Actions -->\n";
						echo "  <div class='formLabel'></div>\n";
						echo "  <div class='formField'>\n";
						echo "    " . $form->input('submit', 'update', 'Update', false, '', 'btn btn-medium btn-info') . "\n";
						echo "    " . $form->input('button', 'cancel', 'Cancel', false, '', 'btn btn-link', '', '', '', '', array('onClick'=>'document.location.href="/profile/' . $username . '"; return false;')) . "\n";
						echo "  </div>\n";
						echo "  <div class='floatClear'></div>\n";

	echo $form->end() . "\n";

	include 'includes/views/desktop/shared/page_bottom.php';
?>