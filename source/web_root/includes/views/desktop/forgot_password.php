<?php 
	include 'includes/views/desktop/shared/page_top.php';

	echo "<h2>Forgot your password?</h2>\n";
		
	echo "<p>Enter your email address, and if you're a registered user you'll be emailed a link for resetting your password.</p>\n";

	echo $form->start('forgotPasswordForm', null, 'post', null, null, array('onSubmit'=>'validateForgotPasswordForm(); return false;')) . "\n";

	/* Email */		echo $form->row('email', 'email', @$_POST['email'], true, 'Email', 'form-control') . "\n";
	/* Actions */	echo $form->rowStart('actions');
					echo "  " . $form->input('submit', 'send', null, false, 'Send', 'btn btn-info') . "\n";
					echo "  " . $form->input('cancel_and_return', 'cancel', null, null, null, 'btn btn-link') . "\n";
					echo $form->rowEnd();

	echo $form->end() . "\n";
					
	include 'includes/views/desktop/shared/page_bottom.php';
?>