<?php

	echo "<div id='newuser_container' class='collapse";
	if (@$_POST['newuser_first_name'] || @$_POST['newuser_last_name'] || @$_POST['newuser_username'] || @$_POST['newuser_email'] || @$_POST['newuser_primary_phone'] || @$_POST['newuser_primary_phone_sms'] || @$_POST['newuser_secondary_phone'] || @$_POST['newuser_secondary_phone_sms'] || @$_POST['newuser_country'] || @$_POST['newuser_ok_to_contact'] || @$_POST['newuser_anonymous']) echo " in";
	echo "'>\n";
	/* Name */			echo "  <div class='row form-group'>\n";
						echo "    <div class='col-md-6'>" . $form->input('text', 'newuser_first_name', @$_POST['newuser_first_name'], false, '|First name', 'form-control', '', 30) . "</div>\n";
						echo "    <div class='col-md-6'>" . $form->input('text', 'newuser_last_name', @$_POST['newuser_last_name'], false, '|Last name', 'form-control', '', 30) . "</div>\n";
						echo "  </div>\n";
	/* Username */		while (!@$random) {
							$random = rand(1000000,9999999);
							$exists = retrieveUsers(array('username'=>$random), null, null, null, 1);
							if (count($exists)) $random = null;
						}
						echo "  <div class='row form-group'>\n";
						echo "    <div class='col-md-9'>" . $form->input('text', 'newuser_username', @$_POST['newuser_username'], false, '|Username', 'form-control', '', 30) . "</div>\n";
						echo "    <div class='col-md-3'>" . $form->input('button', 'newuser_username_anonymizer', null, true, 'Anonymize', 'btn btn-link', null, null, null, null, array('onClick'=>'document.getElementById("newuser_username").value="' . $random . '"; return false;')) . "</div>\n";
						echo "  </div>\n";
	/* Email */			echo "  <div class='row form-group'>\n";
						echo "    <div class='col-md-12'>" . $form->input('email', 'newuser_email', @$_POST['newuser_email'], false, '|Email', 'form-control', '', 100) . "</div>\n";
						echo "  </div>\n";
	/* Phone */			echo "  <div class='row form-group'>\n";
						echo "    <div class='col-lg-6 col-md-4 col-sm-4 col-xs-4'>" . $form->input('tel', 'newuser_primary_phone', @$_POST['newuser_primary_phone'], false, '|Primary phone', 'form-control', '', 12) . "</div>\n";
						echo "    <div class='col-lg-3 col-md-4 col-sm-4 col-xs-4'><label class='control-label'>SMS?</label></div>\n";
						echo "    <div class='col-lg-3 col-md-4 col-sm-4 col-xs-4 text-right'>" . $form->input('yesno_bootstrap_switch', 'newuser_primary_phone_sms', @$_POST['newuser_primary_phone_sms'], false, null, null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default')) . "</div>\n";
						echo "  </div>\n";
	/* 2nd Phone */		echo "  <div class='row form-group'>\n";
						echo "    <div class='col-lg-6 col-md-4 col-sm-4 col-xs-4'>" . $form->input('tel', 'newuser_secondary_phone', @$_POST['newuser_secondary_phone'], false, '|Secondary phone', 'form-control', '', 12) . "</div>\n";
						echo "    <div class='col-lg-3 col-md-4 col-sm-4 col-xs-4'><label class='control-label'>SMS?</label></div>\n";
						echo "    <div class='col-lg-3 col-md-4 col-sm-4 col-xs-4 text-right'>" . $form->input('yesno_bootstrap_switch', 'newuser_secondary_phone_sms', @$_POST['newuser_secondary_phone_sms'], false, null, null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default')) . "</div>\n";
						echo "  </div>\n";
	/* Country */		echo "  <div class='row form-group'>\n";
						echo "    <div class='col-md-12'>" . $form->input('country', 'newuser_country', $operators->firstTrue(@$_POST['newuser_country'], @$tl->page['domain_alias']['country_id']), false, "Country", 'form-control') . "</div>\n";
						echo "  </div>\n";
	/* Anonymized */	echo "  <div class='row form-group'>\n";
						echo "    <div class='col-md-3'><strong>OK to <a href='javascript:void(0)' class='tooltips' onClick='return false' data-toggle='tooltip' title='Choosing NO will prevent " . htmlspecialchars($tl->settings['Name of this application'], ENT_QUOTES) . " from contacting you, regardless of other profile settings. Use with caution.'>contact</a>?</strong></div>\n";
						echo "    <div class='col-md-3 text-right'>" . $form->input('yesno_bootstrap_switch', 'newuser_ok_to_contact', @$_POST['newuser_ok_to_contact'], false, null, null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default')) . "</div>\n";
						echo "    <div class='col-md-3'><strong><a href='javascript:void(0)' class='tooltips' onClick='return false' data-toggle='tooltip' title='" . htmlspecialchars($tl->settings['Name of this application'], ENT_QUOTES) . " is a community-based platform. Please avoid hiding your profile unless you have significant security or privacy concerns.'>Anonymous</a>?</strong></div>\n";
						echo "    <div class='col-md-3 text-right'>" . $form->input('yesno_bootstrap_switch', 'newuser_anonymous', @$_POST['newuser_anonymous'], false, null, null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default')) . "</div>\n";
						echo "  </div>\n";
	echo "</div>\n";

?>