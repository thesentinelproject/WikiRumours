<?php 
	$pageTitle = 'Login or Register';
	include 'includes/views/desktop/shared/page_top.php';

	if ($pageError) echo "<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" . $pageError . "</div>\n";
	elseif ($pageSuccess == 'registration_success') echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>Thank you for registering. Please check your email.</div>\n";
	
	echo "  <div id='loginContainer' class='pageModule'>\n";
	echo "    <h2>Log in with your WikiRumours account...</h2>\n";

	echo "    " . $form->start('loginForm', null, 'post', null, null, array('onSubmit'=>'validateLoginForm(); return false;')) . "\n";
	/* Username */		echo $form->row('text', 'loginUsername', $operators->firstTrue(@$_POST['loginUsername'], @$_COOKIE['username']), true, 'Username', 'form-control', '', 30) . "\n";
	/* Password */		echo $form->row('password', 'loginPassword', '', true, 'Password', 'form-control', '', 72) . "\n";
	/* Actions */		echo "<!-- Actions -->\n";
						echo "  <div class='formLabel'></div>\n";
						echo "  <div class='formField'>\n";
						echo "    " . $form->input('submit', 'login', null, false, 'Log In', 'btn btn-info') . "\n";
						echo "    " . $form->input('button', 'reset_password', null, false, 'Reset Password', 'btn btn-link', '', '', '', '', array('onClick'=>'document.location.href="/forgot_password"; return false;')) . "\n";
						echo "  </div>\n";
						echo "  <div class='floatClear'></div>\n";
	
	echo "    " . $form->end() . "\n";
			
	echo "  </div>\n";

	echo "  <div class='registerContainer'>\n";
	echo "    <h2>...or take a moment to register now</h2>\n";

	echo "    " . $form->start('registrationForm', null, 'post', null, null, array('onSubmit'=>'validateRegistrationForm(); return false;')) . "\n";
	/* Username */		echo $form->row('text', 'registerUsername', @$_POST['registerUsername'], true, 'Username', 'form-control', '', 30) . "\n";
	/* Email */			echo $form->row('email', 'email', @$_POST['email'], true, 'Email', 'form-control', '', 100) . "\n";
	/* Name */			echo "<!-- Name -->\n";
						echo "  <div class='formLabel'>Name</div>\n";
						echo "  <div class='formField row'>\n";
						echo "    <div class='col-md-6'>" . $form->input('text', 'first_name', @$_POST['first_name'], false, 'First|First', 'form-control', '', 30) . "</div>\n";
						echo "    <div class='col-md-6'>" . $form->input('text', 'last_name', @$_POST['last_name'], false, 'Last|Last', 'form-control', '', 30) . "</div>\n";
						echo "  </div>\n";
						echo "  <div class='floatClear'></div>\n";
	/* Country */		echo $form->row('country', 'country', @$_POST['country'], false, 'Country', 'form-control') . "\n";
	/* ProvinceState */	echo $form->row('provinceState', 'province_state', @$_POST['province_state'], false, 'Province/State', 'form-control') . "\n";
	/* Other */			echo $form->row('text', 'other_province_state', @$_POST['other_province_state'], false, 'Other province/state', 'form-control', '', 50) . "\n";
	/* CityRegion */	echo $form->row('text', 'region', @$_POST['region'], false, 'City / region', 'form-control', '', 50) . "\n";
	/* Phone */			echo $form->row('tel', 'phone', @$_POST['phone'], false, 'Primary phone', 'form-control', '', 12) . "\n";
	/* Secondary */		echo $form->row('tel', 'secondary_phone', @$_POST['secondary_phone'], false, 'Secondary phone', 'form-control', '', 12) . "\n";
	/* SMS */			echo $form->row('tel', 'sms_notifications', @$_POST['sms_notifications'], false, 'SMS notifications to', 'form-control', '', 12) . "\n";
	/* Password */		echo $form->row('password_with_health_meter', 'password', '', true, 'Password', 'form-control', '', 72) . "\n";
	/* Confirm */		echo $form->row('password', 'confirm', '', true, 'Confirm', 'form-control', '', 72) . "\n";
	/* Actions */		echo $form->row('submit', 'register', null, false, 'Register', 'btn btn-info') . "\n";

	echo $form->end() . "\n";
			
	echo "  </div>\n";

	include 'includes/views/desktop/shared/page_bottom.php';
?>