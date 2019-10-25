<?php 

	// Login

		echo "  <div id='loginContainer' class='pageModule'>\n";
		echo "    <h2>Log in with your " . $tl->settings['Name of this application'] . " account...</h2>\n";

		echo "    " . $form->start('loginForm', null, 'post', null, null, array('onSubmit'=>'validateLoginForm(); return false;')) . "\n";
		/* Username */		echo $form->row('text', 'loginUsername', $operators->firstTrue(@$_POST['loginUsername'], @$_COOKIE['username']), true, 'Username', 'form-control', '', 30) . "\n";
		/* Honeypot */		echo "<div class='hidden'>\n";
							echo $form->row('text', 'loginTitle', @$_POST['loginTitle'], false, 'Title') . "\n";
							echo "</div>\n";
		/* Password */		echo $form->row('password', 'loginPassword', '', true, 'Password', 'form-control', '', 72) . "\n";
		/* Actions */		echo $form->rowStart('actions');
							echo "  " . $form->input('submit', 'login', null, false, 'Log In', 'btn btn-info') . "\n";
							echo "  " . $form->input('button', 'reset_password', null, false, 'Reset Password', 'btn btn-link', '', '', '', '', array('onClick'=>'document.location.href="/forgot_password"; return false;')) . "\n";
							echo $form->rowEnd();
		
		echo "    " . $form->end() . "\n";
				
		echo "  </div>\n";

	// Register

		echo "  <div class='registerContainer'>\n";
		echo "    <h2>...or take a moment to register now</h2>\n";

		echo "    " . $form->start('registrationForm', null, 'post', null, null, array('onSubmit'=>'validateRegistrationForm(); return false;')) . "\n";
		/* Username */		echo $form->row('text', 'registerUsername', @$_POST['registerUsername'], true, 'Username', 'form-control', '', 30) . "\n";
		/* Email */			echo $form->row('email', 'email', @$_POST['email'], true, 'Email', 'form-control', '', 100) . "\n";
		/* Name */			echo $form->rowStart('name', 'Name');
							echo "  <div class='row'>\n";
							echo "    <div class='col-md-6'>" . $form->input('text', 'first_name', @$_POST['first_name'], false, 'First|First', 'form-control', '', 30) . "</div>\n";
							echo "    <div class='col-md-6'>" . $form->input('text', 'last_name', @$_POST['last_name'], false, 'Last|Last', 'form-control', '', 30) . "</div>\n";
							echo "  </div>\n";
							echo $form->rowEnd();
		/* Honeypot */		echo "<div class='hidden'>\n";
							echo $form->row('text', 'title', @$_POST['title'], false, 'Title') . "\n";
							echo "</div>\n";
		/* Country */		echo $form->row('country', 'country_id', $operators->firstTrue(@$_POST['country_id'], @$tl->page['domain_alias']['country_id']), false, "Country", 'form-control') . "\n";
		/* Region */		echo $form->row('region', 'region', ['country_id'=>$operators->firstTrue(@$_POST['country_id'], @$tl->page['domain_alias']['country_id']), 'region_id'=>@$_POST['region_id'], 'region_other'=>@$_POST['region_other']], false, "Region", 'form-control', null, null, ['link-to'=>'country_id']) . "\n";
		/* Community */		echo $form->row('text', 'city', @$_POST['city'], false, 'Community', 'form-control', '', 50) . "\n";
		/* Phone */			echo $form->rowStart('primary_phone', 'Primary Phone');
							echo "  <div class='row'>\n";
							echo "    <div class='col-lg-6 col-md-4 col-sm-4 col-xs-4'>" . $form->input('tel', 'primary_phone', @$_POST['primary_phone'], false, null, 'form-control', '', 12) . "</div>\n";
							echo "    <div class='col-lg-3 col-md-4 col-sm-4 col-xs-4 text-right'><label class='control-label'>SMS?</label></div>\n";
							echo "    <div class='col-lg-3 col-md-4 col-sm-4 col-xs-4 text-right'>" . $form->input('yesno_bootstrap_switch', 'primary_phone_sms', @$_POST['primary_phone_sms'], false, null, null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default')) . "</div>\n";
							echo "  </div>\n";
							echo $form->rowEnd();
		/* 2nd Phone */		echo $form->rowStart('secondary_phone', 'Secondary Phone');
							echo "  <div class='row'>\n";
							echo "    <div class='col-lg-6 col-md-4 col-sm-4 col-xs-4'>" . $form->input('tel', 'secondary_phone', @$_POST['secondary_phone'], false, null, 'form-control', '', 12) . "</div>\n";
							echo "    <div class='col-lg-3 col-md-4 col-sm-4 col-xs-4 text-right'><label class='control-label'>SMS?</label></div>\n";
							echo "    <div class='col-lg-3 col-md-4 col-sm-4 col-xs-4 text-right'>" . $form->input('yesno_bootstrap_switch', 'secondary_phone_sms', @$_POST['secondary_phone_sms'], false, null, null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default')) . "</div>\n";
							echo "  </div>\n";
							echo $form->rowEnd();
		/* Password */		echo $form->row('password_with_health_meter', 'password', '', true, 'Password', 'form-control', '', 72) . "\n";
		/* Confirm */		echo $form->row('password', 'confirm', '', true, 'Confirm', 'form-control', '', 72) . "\n";
		/* Actions */		echo $form->row('submit', 'register', null, false, 'Register', 'btn btn-info') . "\n";

		echo $form->end() . "\n";
				
		echo "  </div>\n";

?>