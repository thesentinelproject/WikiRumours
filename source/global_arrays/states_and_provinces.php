<?php

	// USA
		$usaStates_TL = array("AK" => "Alaska", "AL" => "Alabama", "AR" => "Arkansas", "AS" => "American Samoa", "AZ" => "Arizona", "CA" => "California", "CO" => "Colorado", "CT" => "Connecticut", "DC" => "District of Columbia", "DE" => "Delaware", "FL" => "Florida", "FM" => "Micronesia", "GA" => "Georgia", "GU" => "Hguam", "HI" => "Hawaii", "IA" => "Iowa", "ID" => "Idaho", "IL" => "Illinois", "IN" => "Indiana", "KS" => "Kansas", "KY" => "Kentucky", "LA" => "Louisiana", "MA" => "Massachusetts", "MD" => "Maryland", "ME" => "Maine", "MH" => "Marshall Islands", "MI" => "Michigan", "MN" => "Minnesota", "MS" => "Mississippi", "MO" => "Missouri", "MT" => "Montana", "NE" => "Nebraska", "NV" => "Nevada", "NH" => "New Hampshire", "NJ" => "New Jersey", "NM" => "New Mexico", "NY" => "New York", "NC" => "North Carolina", "ND" => "North Dakota", "MP" => "Northern Mariana Islands", "OH" => "Ohio", "OK" => "Oklahoma", "OR" => "Oregon", "PW" => "Palau", "PA" => "Pennsylvania", "PR" => "Puerto Rico", "RI" => "Rhode Island", "SC" => "South Carolina", "SD" => "South Dakota", "TN" => "Tennessee", "TX" => "Texas", "UM" => "U.S. Minor Outlying Islands", "UT" => "Utah", "VT" => "Vermont", "VI" => "Virgin Islands of the U.S.", "VA" => "Virginia", "WA" => "Washington", "WV" => "West Virginia", "WI" => "Wisconsin", "WY" => "Wyoming");
		$usaStateAbbreviations_TL = array_flip($usaStates_TL);

	// Canada
		$cdnProvinces_TL = array("AB" => "Alberta", "BC" => "British Columbia", "MB" => "Manitoba", "NB" => "New Brunswick", "NL" => "Newfoundland / Labrador", "NS" => "Nova Scotia", "NT" => "Northwest Territories", "NU" => "Nunavut", "ON" => "Ontario", "PE" => "Prince Edward Island", "QC" => "Quebec", "SK" => "Saskatchewan", "YT" => "Yukon Territory" );
		$cdnProvinceAbbreviations_TL = array_flip($cdnProvinces_TL);

	// North America
		$northAmerStatesAndProvinces_TL = array_merge($usaStates_TL, array('' => '---'), $cdnProvinces_TL);
		$northAmerProvincesAndStates_TL = array_merge($cdnProvinces_TL, array('' => '---'), $usaStates_TL);

	// Australia
		$ausStates_TL = array("AU-NSW" => "New South Wales", "AU-QLD" => "Queensland", "AU-SA" => "South Australia", "AU-TAS" => "Tasmania", "AU-VIC" => "Victoria", "AU-WA" => "Western Australia", "AU-ACT" => "Australian Capital Territory", "AU-NT" => "Northern Territory");
		$ausStateAbbreviations_TL = array_flip($ausStates_TL);

?>