<?php

	if (@$attributableConfig[$currentAttributableCredentials]['API']) {

		// retrieve from Attributable
			@$attributableOutput = $attributable->user(['is_blacklisted'=>'1']);
			if (!@$attributableOutput['content']['success']) emailSystemNotification(__FILE__ . ": " . (is_array($attributableOutput) ? print_r($attributableOutput, true) : $attributableOutput), 'Attributable failure');
			elseif (@$attributableOutput['content']['number_of_results'] > @$attributableOutput['content']['number_of_results_on_this_page']) emailSystemNotification(__FILE__ . ": " . (is_array($attributableOutput) ? print_r($attributableOutput, true) : $attributableOutput), 'Attributable warning');

		// update DB
			if (count(@$attributableOutput['content']['authors'])) {

				$database_manager->emptyData('blacklisted_ips');

				foreach ($attributableOutput['content']['authors'] as $id => $author) {

					if (count(@$author['ips'])) {

						foreach ($author['ips'] as $ip) {

							$encodedIpv4 = null;
							$encodedIpv6 = null;

							if (strlen($ip['ip']) < 16) $encodedIpv4 = $parser->encodeIP($ip['ip']);
							else $encodedIpv6 = $parser->encodeIP($ip['ip']);

							$database_manager->insert('blacklisted_ips', ['ipv4'=>@$encodedIpv4, 'ipv6'=>@$encodedIpv6, 'created_on'=>date('Y-m-d H:i:s')]);
							@$ipsAdded++;

						}

					}

				}

			}

		// update log
			$output .= floatval(@$ipsAdded) . " IP(s) processed\n";
			
	}

?>
