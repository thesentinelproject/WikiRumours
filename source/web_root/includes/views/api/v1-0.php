<?php

	// retrieve input parameters
		$output = $parameter3;
		if ($output != 'json') $output = 'xml';

	// display data
		$xmlOutput = '';
		
		$xmlOutput .= "<" . "?" . "xml version='1.0' encoding='ISO-8859-1'" . "?" . ">\n";
		$xmlOutput .= "<wikirumours>\n";
		$xmlOutput .= "  <version>1.0</version>\n";
		$xmlOutput .= "  <status><![CDATA[API deprecated]]></status>\n";
		$xmlOutput .= "  <page>1</page>\n";
		$xmlOutput .= "  <number_of_results>0</number_of_results>\n";
		$xmlOutput .= "  <number_of_results_on_this_page>0</number_of_results_on_this_page>\n";
		$xmlOutput .= "  <warnings>\n";
		$xmlOutput .= "  </warnings>\n";
		$xmlOutput .= "  <errors>\n";
		$xmlOutput .= "    <error_code>6</error_code>\n";
		$xmlOutput .= "    <human_readable_error>" . $apiErrorCodes[6] . "</human_readable_error>\n";
		$xmlOutput .= "  </errors>\n";
		$xmlOutput .= "</wikirumours>\n";
		
		if ($output == 'xml') {
			echo $xmlOutput;
		}
		elseif ($output == 'json') {
			$simpleXml = simplexml_load_string($xmlOutput, null, LIBXML_NOCDATA);
			$jsonOutput = json_encode($simpleXml);
	
			echo $jsonOutput;
		}

	// close DB connection
		$dbConnection->close();
		
?>
