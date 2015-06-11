<?php

	function populateLanguages() {

		global $languages_TL;
		global $languagesNative_TL;

		$languages_TL = array();
		$languagesNative_TL = array();
		$result = retrieveFromDb('languages', null, array('common'=>'1'));

		for ($counter = 0; $counter < count($result); $counter++) {
			$languages_TL[$result[$counter]['language_id']] = $result[$counter]['language'];
			if ($result[$counter]['native']) $languagesNative_TL[$result[$counter]['language_id']] = $result[$counter]['native'];
		}

		asort($languages_TL);
		asort($languagesNative_TL);

	}

?>