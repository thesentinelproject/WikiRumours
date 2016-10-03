<?php

	include __DIR__ . '/../../../initialize.php';

	// initialize
		if (!@$_POST['name']) $_POST['name'] = 'region';
		$value = ['region_id'=>@$_POST['region_id'], 'other_region'=>@$_POST['other_region'], 'country_id'=>@$_POST['country_id']];

	// if country provided, retrieve regions for that country
		if (@$_POST['country_id']) {

			$localization_manager = new localization_manager_TL();
			$localization_manager->populateCountries($_POST['country_id']);
			$localization_manager->populateRegions($_POST['country_id']);

			if (count(@$localization_manager->regions[$_POST['country_id']]['regions'])) {
				$options = array();
				foreach ($localization_manager->regions[$_POST['country_id']]['regions'] as $region) {
					$options[$region['region_id']] = $region['region'];
				}
			}

			$subdivision = ucwords(@$localization_manager->regions[$_POST['country_id']]['region_type']);

		}
		
		if (!@$subdivision) $subdivision = "Region";

	// create input
		$form = new form_TL();
		echo $form->input('region', $_POST['name'], $value, (@$_POST['mandatory'] ? true : false), urldecode(@$_POST['labelPlaceholder']), urldecode(@$_POST['class']), @$options, (@$_POST['maxlength'] ? floatval($_POST['maxlength']) : null), null, (@$_POST['truncateLabel'] ? $_POST['truncateLabel'] : null), null);
		echo $form->input('hidden', 'regionType', $subdivision);

?>
