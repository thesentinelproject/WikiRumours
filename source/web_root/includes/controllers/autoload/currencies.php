<?php

	function populateCurrencies() {

		global $currencies_TL;
		global $currencySymbols_TL;

		$currencies_TL = array();
		$currencySymbols_TL = array();
		$result = retrieveFromDb('currencies', null, array('enabled'=>'1'));

		for ($counter = 0; $counter < count($result); $counter++) {
			$currencies_TL[$result[$counter]['currency_id']] = $result[$counter]['currency'];
			if ($result[$counter]['symbol']) $currencySymbols_TL[$result[$counter]['currency_id']] = $result[$counter]['symbol'];
		}

		asort($currencies_TL);
		asort($currencySymbols_TL);

	}

?>