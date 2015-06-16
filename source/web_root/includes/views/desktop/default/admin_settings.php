<?php
	$sectionTitle = "Administration";
	include 'includes/views/desktop/shared/page_top.php';

	echo "<h2>" . $pageTitle . "</h2>\n";

	if ($screen == 'all') {

		if (!count($settings)) echo "<p>None yet.</p>\n";
		else {

			echo "<table class='table table-hover table-condensed'>\n";
			echo "<thead>\n";
			echo "<tr>\n";
			echo "<th>Setting</th>\n";
			echo "<th>Value</th>\n";
			echo "<th></th>\n";
			echo "</tr>\n";
			echo "</thead>\n";
			echo "<tbody>\n";

			for ($counter = 0; $counter < count($settings); $counter++) {

				echo "<tr>\n";
				// setting
					echo "<td><a href='/admin_settings/update/" . $settings[$counter]['preference_id'] . "'" . (@$settings[$counter]['tooltip'] ? " class='tooltips' onClick='return false' data-toggle='tooltip' title='" . htmlspecialchars($settings[$counter]['tooltip'], ENT_QUOTES) . "'" : false) . ">" . $settings[$counter]['preference'] . "</a></td>\n";
				// value
					if ($settings[$counter]['input_type'] == 'yesno_bootstrap_switch') echo "<td>" . ($settings[$counter]['value'] == 1 ? 'Yes' : 'No') . "</td>\n";
					elseif ($settings[$counter]['input_type'] == 'country') echo "<td>" . $countries_TL[$settings[$counter]['value']] . "</td>\n";
					elseif ($settings[$counter]['input_type'] == 'language') echo "<td>" . $languages_TL[$settings[$counter]['value']] . "</td>\n";
					else echo "<td>" . trim($settings[$counter]['prepend'] . " " . $settings[$counter]['value'] . " " . $settings[$counter]['append']) . "</td>\n";
				// actions
					echo "<td class='text-right'><a href='/admin_settings/update/" . $settings[$counter]['preference_id'] . "'>Update</a></td>\n";
				echo "</tr>\n";

			}

			echo "</tbody>\n";
			echo "</table>\n";

			if ($logged_in['can_edit_settings']) echo "    " . $form->input('button', 'add_button', null, false, 'Add setting', 'btn btn-info', '', '', '', '', array('onClick'=>'document.location.href="/admin_settings/add"; return false;')) . "\n";

		}

	}
	elseif ($screen == 'update') {

		echo $form->start('updateSettingForm', '', 'post') . "\n";

		// tooltip
			if ($setting[0]['tooltip']) echo "<div class='container-fluid form-group text-muted'>" . $setting[0]['tooltip'] . "</div>\n";

		// value
			if (@$setting[0]['options']) {
				$options = explode('|', $setting[0]['options']);
				$options = array_combine($options, $options);
			}

			echo "<div class='container-fluid form-group'>\n";
			if (!@$setting[0]['prepend'] && !@$setting[0]['append']) echo $form->input($operators->firstTrue($setting[0]['input_type'], 'select'), 'setting_value', $operators->firstTrue(@$_POST['setting_value'], @$setting[0]['value']), @$setting[0]['is_mandatory'], null, 'form-control', @$options, 255, array('data-on-color'=>'default', 'data-off-color'=>'default'));
			else {
				echo "<div class='input-group'>";
				if (@$setting[0]['prepend']) echo "<span class='input-group-addon'>" . $setting[0]['prepend'] . "</span>";
				echo $form->input($operators->firstTrue(@$setting[0]['input_type'], 'select'), 'setting_value', $operators->firstTrue(@$_POST['setting_value'], @$setting[0]['value']), $setting[0]['is_mandatory'], null, 'form-control', @$options);
				if (@$setting[0]['append']) echo "<span class='input-group-addon'>" . $setting[0]['append'] . "</span>";
				echo "</div>";
			}
			echo "</div>\n";

		// actions
			echo "<div class='row'>\n";
			echo "  <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6'>\n";
			echo "    " . $form->input('submit', 'update_button', null, false, 'Update', 'btn btn-info') . "\n";
			echo "    " . $form->input('cancel_and_return', 'cancel_button', null, false, 'Cancel', 'btn btn-link') . "\n";
			echo "  </div>\n";
			echo "  <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6 text-right'>\n";
			if ($logged_in['can_edit_settings']) echo "    " . $form->input('button', 'edit_button', null, false, 'Edit or delete', 'btn btn-link', '', '', '', '', array('onClick'=>'document.location.href="/admin_settings/edit/' . $setting[0]['preference_id'] . '"; return false;')) . "\n";
			echo "  </div>\n";
			echo "</div>\n";

		echo $form->end() . "\n";

	}
	elseif ($screen == 'edit' || $screen == 'add') {

		echo $form->start('editSettingForm', '', 'post') . "\n";
		echo $form->input('hidden', 'deleteThisSetting') . "\n";

		/* Setting */		echo $form->row('text', 'preference', $operators->firstTrue(@$_POST['preference'], @$setting[0]['preference']), true, "<a href='javascript:void(0)' class='tooltips' onClick='return false' data-toggle='tooltip' title='Use a pipe to specify placeholder text, e.g. {label} &#124; {placeholder}'>Setting</a>", 'form-control', '', 255);
		/* Prepend */		echo $form->row('text', 'prepend', $operators->firstTrue(@$_POST['prepend'], @$setting[0]['prepend']), false, 'Prepend', 'form-control', '', 30);
		/* Value */			if ($screen == 'add') echo $form->row('text', 'setting_value', @$_POST['setting_value'], true, 'Value', 'form-control', '', 255);
		/* Append */		echo $form->row('text', 'append', $operators->firstTrue(@$_POST['append'], @$setting[0]['append']), false, 'Append', 'form-control', '', 30);
		/* Input type */	echo $form->row('text', 'input_type', $operators->firstTrue(@$_POST['input_type'], @$setting[0]['input_type'], 'text'), true, 'Input type', 'form-control', '', 30);
		/* Options */		echo $form->row('text', 'options', $operators->firstTrue(@$_POST['options'], @$setting[0]['options']), false, "<a href='javascript:void(0)' class='tooltips' onClick='return false' data-toggle='tooltip' title='Use pipes as delimiters, e.g.  {option_1} &#124; {option_2}'>Input options</a>", 'form-control', '', 255);
		/* Mandatory */		echo $form->row('yesno_bootstrap_switch', 'is_mandatory', $operators->firstTrue(@$_POST['is_mandatory'], @$setting[0]['is_mandatory']), false, 'Mandatory?', null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default'));
		/* Tooltip */		echo $form->row('text', 'tooltip', $operators->firstTrue(@$_POST['tooltip'], @$setting[0]['tooltip']), true, 'Tooltip', 'form-control', '', 255);

		/* Actions */		echo $form->rowStart('actions');
							echo "<div class='row'>\n";
							echo "  <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6'>\n";
							echo "    " . $form->input('submit', 'save_button', null, false, 'Save', 'btn btn-info') . "\n";
							echo "    " . $form->input('cancel_and_return', 'cancel_button', null, false, 'Cancel', 'btn btn-link') . "\n";
							echo "  </div>\n";
							echo "  <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6 text-right'>\n";
							if (@$setting[0]['preference_id']) echo "    " . $form->input('button', 'delete_button', null, false, 'Delete', 'btn btn-danger', '', '', '', '', array('onClick'=>'deleteSetting(); return false;')) . "\n";
							echo "  </div>\n";
							echo "</div>\n";
							echo $form->rowEnd();

		echo $form->end() . "\n";

	}

	include 'includes/views/desktop/shared/page_bottom.php';
?>