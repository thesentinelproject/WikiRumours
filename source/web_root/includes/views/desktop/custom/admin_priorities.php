<?php
	$pageTitle = "Priorities";
	$sectionTitle = "Administration";
	include 'includes/views/desktop/shared/page_top.php';

	echo "<h2>" . (@$numberOfPriorities ? "<span class='label label-default'>" . @$numberOfPriorities . "</span> " : false) . "Priorities</h2>\n\n";

	echo $form->start('editPrioritiesForm', '', 'post', null, null, array('onSubmit'=>'validateUpdatePriorities(); return false;')) . "\n";
	echo $form->input('hidden', 'priorityToDelete') . "\n";

	for ($counter = 0; $counter < count($priorities); $counter++) {
		/* Priority */			echo $form->rowStart('priority_' . $priorities[$counter]['priority_id'], 'Priority');
								echo "  <div class='row'>\n";
								echo "    <div class='col-lg-10 col-md-10 col-sm-9 col-xs-8'>" .  $form->input('text', 'priority_' . $priorities[$counter]['priority_id'], $operators->firstTrue(@$_POST['priority_' . $priorities[$counter]['priority_id']], $priorities[$counter]['priority']), true, null, 'form-control', null, 50) . "</div>\n";
								echo "    <div class='col-lg-2 col-md-2 col-sm-3 col-xs-4 text-right'>" .  $form->input('button', 'delete_priority_button_' . $priorities[$counter]['priority_id'], null, false, 'Delete?', 'btn btn-link', null, null, null, null, array('onClick'=>'validateDeletePriority("' . $priorities[$counter]['priority_id'] . '"); return false;')) . "</div>\n";
								echo "  </div>\n"; 
								echo $form->rowEnd();
		/* Severity */			echo $form->row('number', 'severity_' . $priorities[$counter]['priority_id'], $operators->firstTrue(@$_POST['severity_' . $priorities[$counter]['priority_id']], @$priorities[$counter]['severity']), false, 'Severity', 'form-control', null, null, array('min'=>0, 'max'=>99));
		/* Action required */	echo $form->rowStart('action_required_in_' . $priorities[$counter]['priority_id'], 'Action required in');
								echo "  <div class='input-group'>\n";
								echo "    " .  $form->input('number', 'action_required_in_' . $priorities[$counter]['priority_id'], $operators->firstTrue(@$_POST['action_required_in_' . $priorities[$counter]['priority_id']], @$priorities[$counter]['action_required_in']), false, null, 'form-control', null, null, array('min'=>0, 'max'=>365)) . "\n";
								echo "    <span class='input-group-addon'>days</span>\n";
								echo "  </div>\n"; 
								echo $form->rowEnd();
		/* # Rumours */			echo $form->row('hidden', 'number_of_rumours_' . $priorities[$counter]['priority_id'], @$priorities[$counter]['number_of_rumours']);
		echo "<hr />";
	}

	/* Priority */			echo $form->row('text', 'priority_add', @$_POST['priority_add'], false, 'Priority', 'form-control', null, 50);
	/* Severity */			echo $form->row('number', 'severity_add', @$_POST['severity_add'], false, 'Severity', 'form-control', null, null, array('min'=>0, 'max'=>99));
	/* Action required */	echo $form->rowStart('action_required_in_add', 'Action required in');
							echo "  <div class='input-group'>\n";
							echo "    " .  $form->input('number', 'action_required_in_add', @$_POST['action_required_in_add'], false, null, 'form-control', null, null, array('min'=>0, 'max'=>365)) . "\n";
							echo "    <span class='input-group-addon'>days</span>\n"; 
							echo "  </div>\n"; 
							echo $form->rowEnd();
	/* Actions */			echo $form->row('submit', 'submit_button', null, false, 'Save', 'btn btn-info');

	echo "  " . $form->end() . "\n";
	
	include 'includes/views/desktop/shared/page_bottom.php';
?>