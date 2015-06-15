<?php
	$pageTitle = "Settings";
	$sectionTitle = "Administration";
	include 'includes/views/desktop/shared/page_top.php';

	echo "<h2>System preferences</h2>\n";

	echo $form->start('editPreferencesForm', '', 'post', null, null, array('onSubmit'=>'validateSystemPreferences(); return false;')) . "\n";

	for ($counter = 0; $counter < count($preferences); $counter++) {
		if (@$preferences[$counter]['options']) {
			$options = explode('|', $preferences[$counter]['options']);
			$options = array_combine($options, $options);
		}

		if ($preferences[$counter]['tooltip']) $preferences[$counter]['preference'] = "<a href='javascript:void(0)' class='tooltips' onClick='return false' data-toggle='tooltip' title='" . htmlspecialchars($preferences[$counter]['tooltip'], ENT_QUOTES) . "'>" . $preferences[$counter]['preference'] . "</a>";

		if (!@$preferences[$counter]['prepend'] && !@$preferences[$counter]['append']) echo $form->row($operators->firstTrue($preferences[$counter]['input_type'], 'select'), 'preference_' . $preferences[$counter]['preference_id'], $operators->firstTrue(@$_POST['preference_' . $preferences[$counter]['preference_id']], @$preferences[$counter]['value']), @$preferences[$counter]['is_mandatory'], $preferences[$counter]['preference'], 'form-control', @$options, 255);
		else {
			echo $form->rowStart('preference_' . $preferences[$counter]['preference_id'], $preferences[$counter]['preference']);
			echo "<div class='input-group'>";
			if (@$preferences[$counter]['prepend']) echo "<span class='input-group-addon'>" . $preferences[$counter]['prepend'] . "</span>";
			echo $form->input($operators->firstTrue(@$preferences[$counter]['input_type'], 'select'), 'preference_' . $preferences[$counter]['preference_id'], $operators->firstTrue(@$_POST['preference_' . $preferences[$counter]['preference_id']], @$preferences[$counter]['value']), $preferences[$counter]['is_mandatory'], $preferences[$counter]['preference'], 'form-control', @$options);
			if (@$preferences[$counter]['append']) echo "<span class='input-group-addon'>" . $preferences[$counter]['append'] . "</span>";
			echo "</div>";
			echo $form->rowEnd();
		}

		if (substr_count($preferences[$counter]['preference'], 'Server path to ImageMagick') > 0 && $preferences[$counter]['value']) {
			if (!file_exists(rtrim($preferences[$counter]['value'], '/') . '/convert')) {
				echo $form->rowStart('warning');
				echo "  <div class='alert alert-danger'>No sign of ImageMagick in " . rtrim($preferences[$counter]['value'], '/') . "</div>\n";
				echo $form->rowEnd();
			}
		}

		if (substr_count($preferences[$counter]['preference'], 'Server path to FFmpeg') > 0 && $preferences[$counter]['value']) {
			if (!file_exists(rtrim($preferences[$counter]['value'], '/') . '/ffmpeg')) {
				echo $form->rowStart('warning');
				echo "  <div class='alert alert-danger'>No sign of FFmpeg in " . rtrim($preferences[$counter]['value'], '/') . "</div>\n";
				echo $form->rowEnd();
			}
		}
	}

	echo $form->row('submit', 'submitPreferencesButton', null, false, 'Save', 'btn btn-default');
	echo $form->end() . "\n";
	
	include 'includes/views/desktop/shared/page_bottom.php';
?>