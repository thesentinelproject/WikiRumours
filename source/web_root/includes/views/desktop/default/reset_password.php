<?php 

	if ($pageStatus == 'success') {
		echo "<h2>Congratulations</h2>\n";
		echo "<p>Your password has been reset. Please <a href='/login_register'>log in</a>.</p>\n";
	}
	elseif (count($doesKeyExist) < 1) {
		echo "<h2>Unable to Reset Your Password</h2>\n";
		echo "<p>There's something wrong with the link that brought you here. Please check that the link is complete or rekey it by hand; sometimes mail readers cut a link in two by inserting an inopportune line break.</p>\n";
	}
	elseif ($logged_in) {
		echo "<h2>Unable to Reset Your Password</h2>\n";
		echo "<p>You need to be logged out to reset your password via email.</p>\n";
	}
	else {

		echo "<h2>Reset Your Password</h2>\n";
		echo "<p>Congratulations, you're now ready to reset your password! Once you've assigned a new password, you'll be asked to log in immediately.</p>\n";
		
		echo $form->start('resetPasswordForm', null, 'post', null, null, array('onSubmit'=>'validateResetPassword(); return false;'));
		/* Password */		echo $form->row('password_with_health_meter', 'password', '', true, 'Password', 'form-control', '', 72) . "\n";
		/* Confirm */		echo $form->row('password', 'confirm', '', true, 'Confirm', 'form-control', '', 72) . "\n";
		/* Actions */		echo $form->row('submit', 'reset_now', null, false, 'Reset now', 'btn btn-info') . "\n";
		
		echo $form->end();
	
	}
	
?>