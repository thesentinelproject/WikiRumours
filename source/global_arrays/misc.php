<?php

	$connectionTypes = array(
		'R'=>'Robot',
		'U'=>'User'
	);

	$rumourSources = array(
		'w' => 'The Web',
		'e' => 'Email',
		's' => 'SMS',
		'v' => 'Voice / telephone',
		'p' => 'Walk-in / in person'
	);
	
	$externalRumourSources = $rumourSources;
	unset ($externalRumourSources['w']);
	
	$rumourStatuses = array(
		'NU' => 'New / uninvestigated',
		'UI' => 'Under investigation',
		'PT' => 'Probably true',
		'PF' => 'Probably false',
		'CT' => 'Confirmed true',
		'CF' => 'Confirmed false',
		'IV' => 'Impossible to verify',
		'IT' => 'Impossible to verify but probably true',
		'IF' => 'Impossible to verify but probably false'
	);

	$priorityLevels = array(
		1 => 'Low',
		2 => 'Medium',
		3 => 'High'
	);
	
?>
