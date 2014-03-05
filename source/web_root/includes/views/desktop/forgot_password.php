<?php 
	include 'includes/views/desktop/shared/page_top.php';

	echo "<h2>Forgot your password?</h2>\n";
		
	echo "<p>Enter your email address, and if you're a registered user you'll be emailed a link for resetting your password.</p>\n";

	if ($pageError) echo "<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $pageError . "</div>\n";
	elseif ($pageSuccess == 'success') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>If your email address is in our system, you'll shortly receive instructions on how to reset your password.</div>\n";
	
	echo $form->start('forgotPasswordForm', null, 'post', 'form-inline', null, array('onSubmit'=>'validateForgotPasswordForm(); return false;')) . "\n";
	/* Email */		echo "  <div class='form-group'>" . $form->input('email', 'email', @$_POST['email'], true, 'Email|Email', 'form-control') . "</div>\n";
	/* Actions */	echo "  <div class='form-group'>\n";
					echo "    " . $form->input('submit', 'send', null, false, 'Send', 'btn btn-info') . "\n";
					echo "    " . $form->input('cancel_and_return', 'cancel', null, null, null, 'btn btn-link') . "\n";
					echo "  </div>\n";
	echo $form->end() . "\n";
					
	include 'includes/views/desktop/shared/page_bottom.php';
?>