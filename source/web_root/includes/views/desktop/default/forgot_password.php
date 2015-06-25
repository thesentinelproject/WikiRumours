<?php 

	echo "<h2>Forgot your password?</h2>\n";
		
	echo "<div class='row'>\n";
	echo "  <div class='col-md-6 col-sm-6 col-xs-12'>\n";
	echo "    Enter your email address, and if you're a registered user you'll be emailed a link for resetting your password.</p>\n";
	echo "  </div>\n";
	echo "  <div class='col-md-6 col-sm-6 col-xs-12'>\n";
	
	echo "    " . $form->start('forgotPasswordForm', null, 'post', null, null, array('onSubmit'=>'validateForgotPasswordForm(); return false;')) . "\n";
	/* Email */		echo "<div class='form-group'>\n";
					echo "  " . $form->input('email', 'email', @$_POST['email'], true, '|Email', 'form-control') . "\n";
					echo "</div>\n";
	/* Actions */	echo "<div class='form-group'>\n";
					echo "  " . $form->input('submit', 'send', null, null, 'Send', 'btn btn-info') . "\n";
					echo "  " . $form->input('cancel_and_return', 'cancel', null, null, 'Return to login', 'btn btn-link') . "\n";
					echo "</div>\n";

	echo "    " . $form->end() . "\n";

	echo "  </div>\n";
	echo "</div>\n";
	
?>