<?php
	include 'includes/views/desktop/shared/page_top.php';

	if ($step == 1) {
		
			echo "<h2>Report a rumour</h2>\n";

			echo $form->start('addRumourForm', null, 'post', null, null, array('onSubmit'=>'validateAddRumourForm(); return false;')) . "\n";
			echo $form->input('hidden', 'step', @$step) . "\n";
	
			echo "<div class='row form-group'>\n";
			/* Description */	echo "  <div class='col-md-12'>" . $form->input('textarea', 'description', @$_POST['description'], true, 'Rumour|Please be as concise as possible', 'form-control', null, null, array('rows'=>'10')) . "</div>\n";
			echo "</div>\n";
			echo "<div class='row form-group'>\n";
			/* Country */		echo "  <div class='col-md-6 col-xs-9'>" . $form->input('country', 'country', $operators->firstTrue(@$_POST['country'], @$pseudonym['country_id']), false, 'In which country did this occur?', 'form-control') . "</div>\n";
			/* Actions */		echo "  <div class='col-md-3 col-md-offset-3 col-xs-3 text-right'>" . $form->input('submit', 'add_rumour', null, false, 'Continue', 'btn btn-info btn-block') . "</div>\n";
			echo "</div>\n";

			echo $form->end();
			
	}
	elseif ($step == 2) {
		
			echo "<h2>Do you see your rumour here?</h2>\n";

			echo $form->start('matchRumourForm', null, 'post') . "\n";
			echo $form->input('hidden', 'rumour_id', $matchingRumour) . "\n";
			echo $form->input('hidden', 'step', @$step) . "\n";
			echo $form->input('hidden', 'description', htmlspecialchars($_POST['description'], ENT_QUOTES)) . "\n";
			echo $form->input('hidden', 'country', $_POST['country']) . "\n";

			for ($counter = 0; $counter < count($matches); $counter++) {
				echo "<div class='matchingRumourList'>\n";
				echo "<p>" . $matches[$counter]['description'] . "</p>\n";
				echo "<p><button class='btn btn-default' onClick='matchRumour(" . '"' . $matches[$counter]['public_id'] . '"' . "); return false;'><span class='glyphicon glyphicon-ok'></span> This is the same rumour</button></p>\n";
				echo "</div>\n";
			}
			echo $form->input('submit', 'new_rumour', null, false, "My rumour isn't listed here", 'btn btn-info') . "\n";
			echo $form->input('button', 'search_again', null, false, 'Start over', 'btn btn-link', null, null, null, null, array('onClick'=>'document.location.href = "/rumour_add"; return false;')) . "</p>\n";

			echo $form->end();
			
	}
	elseif ($step == 3) {
		
			echo "<h2>Almost done reporting this rumour...</h2>\n";
			include 'includes/views/desktop/shared/add_or_edit_rumour.php';		
	}
	
	include 'includes/views/desktop/shared/page_bottom.php';
?>