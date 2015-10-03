<?php 

	if ($logged_in) {
		echo "<h2>Unable to Reset Your Password</h2>\n";
		echo "<p>You need to be logged out to reset your password via email.</p>\n";
	}
	else {

		echo "<h2>Reset Your Password</h2>\n";
		echo "<p>Once you've assigned a new password, you'll be asked to log in immediately.</p>\n";
		
		echo $form->start('resetPasswordForm', null, 'post', null, null, array('onSubmit'=>'validateResetPassword(); return false;'));
		/* Password */		echo $form->row('password_with_health_meter', 'password', '', true, 'Password', 'form-control', '', 72) . "\n";
		/* Confirm */		echo $form->row('password', 'confirm', '', true, 'Confirm', 'form-control', '', 72) . "\n";
		/* Actions */		echo $form->row('submit', 'reset_now', null, false, 'Reset now', 'btn btn-primary') . "\n";
		
		echo $form->end();
	
	}
	
?>