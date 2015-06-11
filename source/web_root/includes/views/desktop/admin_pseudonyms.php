<?php
	if (@$pseudonym[0]['pseudonym_id']) $pageTitle = "Edit the pseudonym &quot;" . @$pseudonym[0]['name'] . "&quot;";
	elseif ($subView == 'add') $pageTitle = "Add pseudonym";
	else $pageTitle = "Pseudonyms";
	$sectionTitle = "Administration";
	include 'includes/views/desktop/shared/page_top.php';

	echo "  <h2>" . (count(@$pseudonyms) ? "<span class='label label-default'>" . count(@$pseudonyms) . "</span> " : false) . $pageTitle . "</h2>\n\n";

	// add/edit

		if ($subView == 'edit' || $subView == 'add') {
			echo $form->start('editPseudonymForm', '', 'post', null, array('enctype'=>'multipart/form-data'), array('onSubmit'=>'validateEditPseudonymForm(); return false;')) . "\n";
			echo $form->input('hidden', 'deleteThisLogo') . "\n";
			echo $form->input('hidden', 'deleteThisPseudonym') . "\n";

			// url
				echo $form->rowStart('url', 'URL');
				echo "  <div class='row'>\n";
				echo "    <div class='col-md-3'>" . $form->input('text', 'subdomain', $operators->firstTrue(@$_POST['subdomain'], @$result[0]['subdomain'], @$environmentals['subdomain']), false, '|subdomain', 'form-control', '', 50) . "</div>\n";
				echo "    <div class='col-md-1 text-center'>.</div>\n";
				echo "    <div class='col-md-8'>" . $form->input('text', 'domain', $operators->firstTrue(@$_POST['domain'], @$result[0]['domain'], @$environmentals['domain']), true, '|domain.com', 'form-control', '', 205) . "</div>\n";
				echo "  </div>\n";
				echo $form->rowEnd();
			// name
				echo $form->row('text', 'name', $operators->firstTrue(@$_POST['name'], @$result[0]['name']), true, "<a href='javascript:void(0)' class='tooltips' onClick='return false' data-toggle='tooltip' title='This name will be used throughout the application, including in outgoing email notifications.'>Name</a>", 'form-control', '', 255);
			// description
				echo $form->row('textarea', 'description', $operators->firstTrue(@$_POST['description'], @$result[0]['description']), false, "<a href='javascript:void(0)' class='tooltips' onClick='return false' data-toggle='tooltip' title='This description will appear as the meta description for this website in Google.'>Description</a>", 'form-control', '', 255);
			// country
				echo $form->row('country', 'country_id', $operators->firstTrue(@$_POST['country_id'], @$result[0]['country_id']), false, 'Default country', 'form-control');
			// language
				echo $form->row('language', 'language_id', $operators->firstTrue(@$_POST['language_id'], @$result[0]['language_id']), false, 'Default language', 'form-control');
			// outgoing email
				echo $form->row('email', 'outgoing_email', $operators->firstTrue(@$_POST['outgoing_email'], @$result[0]['outgoing_email']), false, 'Outgoing email address', 'form-control', '', 100);
			// google analytics
				echo $form->row('text', 'google_analytics_id', $operators->firstTrue(@$_POST['google_analytics_id'], @$result[0]['google_analytics_id']), false, 'Google Analytics ID', 'form-control', '', 255);
			// logo
				echo $form->rowStart('logo', "Logo");
				if (@$logo) echo "<div><img src='/" . $logo . "' border='0' class='img-responsive' alt='Current logo' /></div><br />\n";
				echo "  <div>" . $form->input('file', 'new_logo') . "</div>\n";
				echo $form->rowEnd();
			// actions
				echo $form->rowStart('actions');
				echo "  " . $form->input('submit', 'update_button', null, false, 'Save', 'btn btn-info') . "\n";
				if (@$logo) echo "  " . $form->input('button', 'delete_logo_button', null, false, 'Delete current logo', 'btn btn-link', '', '', '', '', array('onClick'=>'deleteLogo(); return false;')) . "\n";
				if ($subView == 'edit') echo "  " . $form->input('button', 'delete_pseudonym_button', null, false, 'Delete pseudonym', 'btn btn-link', '', '', '', '', array('onClick'=>'deletePseudonym(); return false;')) . "\n";
				echo "  " . $form->input('cancel_and_return', 'cancel_button', null, false, 'Cancel', 'btn btn-link') . "\n";
				echo $form->rowEnd();

			echo $form->end() . "\n";
		}

	// index

		else {

			if (count(@$pseudonyms) < 1) echo "<p>" . $systemPreferences['Name of this application'] . " can be customized for specific uses or regions (i.e. pseudonyms) by attaching default settings to individual URLs.</p>\n";
			else {

				echo "<table class='table table-hover table-condensed'>\n";
				echo "<thead>\n";
				echo "<tr>\n";
				echo "<th>Name</th>\n";
				echo "<th>URL</th>\n";
				echo "<th>Default Country</th>\n";
				echo "<th>Default Language</th>\n";
				echo "</tr>\n";
				echo "</thead>\n";
				echo "<tbody>\n";
				for ($counter = 0; $counter < count($pseudonyms); $counter++) {
					echo "<tr>\n";
					// name
						echo "<td><a href='/admin_pseudonyms/edit/" . $pseudonyms[$counter]['pseudonym_id'] . "' class='popovers' data-placement='top' data-toggle='popover' data-content=" . '"' . htmlspecialchars($pseudonyms[$counter]['description'], ENT_QUOTES) . '"' . ">" . $pseudonyms[$counter]['name'] . "</a></td>\n";
					// url
						echo "<td>" . $pseudonyms[$counter]['url'] . "</td>\n";
					// country
						echo "<td>" . $pseudonyms[$counter]['default_country'] . "</td>\n";
					// language
						echo "<td>" . $pseudonyms[$counter]['default_language'] . "</td>\n";
					echo "</tr>\n";
				}
				echo "</tbody>\n";
				echo "</table>\n";

			}

			echo $form->input('button', 'add_button', null, false, 'Add an pseudonym', 'btn btn-info', null, null, null, null, array('onClick'=>'document.location.href="/admin_pseudonyms/add"; return false;')) . "\n";

		}
	
	include 'includes/views/desktop/shared/page_bottom.php';
?>