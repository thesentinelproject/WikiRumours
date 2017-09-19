<?php

	/*
			Attributable PHP SDK v1.0
			--
			For complete documentation, please visit attributables.com/sdks

	*/

	class attributable {

		public $key = 								null;
		public $apiVersion = 						"1-0";

		public $settings = [
			'default_to_current_datetime' =>		true,
			'default_to_current_ip' =>				true,
			'default_to_current_user_agent' =>		true,
			'timeout' =>							60
		];

		public $success =							[];
		public $errors =							[];
		public $warnings =							[];

		public function capture($event, $occurred_on = null, $author = null, $tags = null, $is_alert = null, $is_resolved = null, $execution_time_in_seconds = null, $comments = null) {

			// check for errors
				if (!$this->key) {
					$this->errors[] = "Missing API key";
					return false;
				}

				if (!$this->apiVersion) {
					$this->errors[] = "API version not specified";
					return false;
				}

				if (!$event) {
					$this->errors[] = "Event not specified";
					return false;
				}

				if ($occurred_on && !strtotime($occurred_on)) {
					$this->errors[] = "Badly formatted datetime";
					return false;
				}

				if ($author && !is_array($author)) {
					$this->errors[] = "Author should be an array";
					return false;
				}

				if ($tags && !is_array($tags)) {
					$this->errors[] = "Tags should be an array";
					return false;
				}

			// santitize data
				if (!@$occurred_on && $this->settings['default_to_current_datetime']) {
					$occurred_on = date('Y-m-d H:i:s');
				}

				if (@$author['phone']) {
					$author['phone'] = preg_replace('/[^\d-]+/', '', $author['phone']);
				}

				if (!@$author['ip'] && $this->settings['default_to_current_ip']) {
					$author['ip'] = (@$_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : @$_SERVER['REMOTE_HOST']);
				}

				if (!@$author['user_agent'] && $this->settings['default_to_current_user_agent']) {
					$author['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
				}

				if ($author['latitude']) $author['latitude'] = floatval($author['latitude']);

				if ($author['longitude']) $author['longitude'] = floatval($author['longitude']);

				$is_alert = floatval($is_alert);

				$is_resolved = floatval($is_resolved);

				$execution_time_in_seconds = floatval($execution_time_in_seconds);

			// create data payload
				$payload = [];
				$payload['event'] =																									addSlashes($event);
				$payload['occurred_on'] =																							date('Y-m-d H:i:s', strtotime($occurred_on));
				if ($author) {
					$payload['author'] = [];
					if (@$author['user_id']) $payload['author']['user_id'] =														$author['user_id'];
					if (@$author['first_name']) $payload['author']['first_name'] =													$author['first_name'];
					if (@$author['last_name']) $payload['author']['last_name'] =													$author['last_name'];
					if (@$author['ip']) $payload['author']['ip'] =																	$author['ip'];
					if (@$author['latitude']) $payload['author']['latitude'] =														$author['latitude'];
					if (@$author['longitude']) $payload['author']['longitude'] =													$author['longitude'];
					if (@$author['user_agent']) $payload['author']['user_agent'] =													$author['user_agent'];
					if (@$author['email']) $payload['author']['email'] =															$author['email'];
					if (@$author['phone']) $payload['author']['phone'] =															$author['phone'];
					if (array_key_exists('is_blacklisted', @$author)) $payload['author']['is_blacklisted'] =						$author['is_blacklisted'];
					if (array_key_exists('is_greylisted', @$author)) $payload['author']['is_greylisted'] =							$author['is_greylisted'];
					if (array_key_exists('is_whitelisted', @$author)) $payload['author']['is_whitelisted'] =						$author['is_whitelisted'];
				}
				if ($tags) {
					$payload['tags'] = [];
					foreach ($tags as $key => $value) {
						$payload['tags'][$key] =																					addSlashes($value);
					}
				}
				if ($is_alert !== null) $payload['is_alert'] =																		$is_alert;
				if ($is_resolved !== null) $payload['is_resolved'] =																$is_resolved;
				if ($execution_time_in_seconds !== null) $payload['execution_time_in_seconds'] =									$execution_time_in_seconds;
				if ($comments) $payload['comments'] =																				addSlashes($comments);

				$payload = json_encode($payload);

			// connect with Attributable
				$curl = curl_init();

				curl_setopt_array($curl, [
					CURLOPT_URL => "https://api.attributables.com/" . $this->apiVersion . "/" . $this->key . "/capture",
					CURLOPT_HEADER => true,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => "application/json",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => $this->settings['timeout'],
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "POST",
					CURLOPT_POSTFIELDS => $payload,
					CURLOPT_HTTPHEADER => [
						"Content-Type:application/json",
						"cache-control: no-cache"
					],
					CURLINFO_HEADER_OUT => true,
				]);

				$response = curl_exec($curl);
				$err = curl_error($curl);
				$info = curl_getinfo($curl);

				curl_close($curl);

			// parse & return
				if ($err) {
					$this->errors[] = $err;
					return false;
				}
				else {

					// separate headers from content
						list($headers, $content) = explode("\r\n\r\n", $response, 2);
					// convert content from JSON to array if required
						$array = json_decode($content, true);
					// return
						return ['headers'=>$headers, 'content'=>(is_array($array)? $array : $content)];

				}

		}

		public function measure($metric, $value, $occurred_on = null) {

			// check for errors
				if (!$this->key) {
					$this->errors[] = "Missing API key";
					return false;
				}

				if (!$this->apiVersion) {
					$this->errors[] = "API version not specified";
					return false;
				}

				if (!$metric) {
					$this->errors[] = "Metric not specified";
					return false;
				}

				if (!$value) {
					$this->errors[] = "Value not specified";
					return false;
				}

			// create data payload
				$payload = [
					'metric' =>			addSlashes($metric),
					'value' =>			addSlashes($value),
					'occurred_on' =>	(!$occurred_on || !strtotime($occurred_on) ? date('Y-m-d H:i:s') : $occurred_on)
				];

				$payload = json_encode($payload);

			// connect with Attributable
				$curl = curl_init();

				curl_setopt_array($curl, [
					CURLOPT_URL => "https://api.attributables.com/" . $this->apiVersion . "/" . $this->key . "/measure",
					CURLOPT_HEADER => true,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => "application/json",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => $this->settings['timeout'],
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "POST",
					CURLOPT_POSTFIELDS => $payload,
					CURLOPT_HTTPHEADER => [
						"Content-Type:application/json",
						"cache-control: no-cache"
					],
					CURLINFO_HEADER_OUT => true,
				]);

				$response = curl_exec($curl);
				$err = curl_error($curl);
				$info = curl_getinfo($curl);

				curl_close($curl);

			// parse & return
				if ($err) {
					$this->errors[] = $err;
					return false;
				}
				else {

					// separate headers from content
						list($headers, $content) = explode("\r\n\r\n", $response, 2);
					// convert content from JSON to array if required
						$array = json_decode($content, true);
					// return
						return ['headers'=>$headers, 'content'=>(is_array($array)? $array : $content)];

				}

		}

		public function events($eventID = null, $author = null, $startDate = null, $endDate = null, $tags = null, $is_alert = null, $is_resolved = null, $page = null) {

			// check for errors
				if (!$this->key) {
					$this->errors[] = "Missing API key";
					return false;
				}

				if (!$this->apiVersion) {
					$this->errors[] = "API version not specified";
					return false;
				}

				if ($author && !is_array($author)) {
					$this->errors[] = "Author should be an array";
					return false;
				}

				if ($tags && !is_array($tags)) {
					$this->errors[] = "Tags should be an array";
					return false;
				}

			// create data payload
				$payload = [];
				if ($eventID) $payload['event_id'] =																				addSlashes($eventID);
				if ($author) {
					$payload['author'] = [];
					if (@$author['author_id']) $payload['author']['author_id'] =													$author['author_id'];
					if (@$author['user_id']) $payload['author']['user_id'] =														$author['user_id'];
					if (@$author['ip']) $payload['author']['ip'] =																	$author['ip'];
					if (@$author['email']) $payload['author']['email'] =															$author['email'];
					if (@$author['phone']) $payload['author']['phone'] =															$author['phone'];
					if (array_key_exists('is_blacklisted', @$author)) $payload['author']['is_blacklisted'] =						$author['is_blacklisted'];
					if (array_key_exists('is_greylisted', @$author)) $payload['author']['is_greylisted'] =							$author['is_greylisted'];
					if (array_key_exists('is_whitelisted', @$author)) $payload['author']['is_whitelisted'] =						$author['is_whitelisted'];
				}
				if ($startDate) $payload['start_date'] =																			$startDate;
				if ($endDate) $payload['end_date'] =																				$endDate;
				if ($tags) {
					$payload['tags'] = [];
					foreach ($tags as $key => $value) {
						$payload['tags'][$key] =																					addSlashes($value);
					}
				}
				if ($is_alert === 1 || $is_alert === 0) $payload['is_alert'] =														$is_alert;
				if ($is_resolved === 1 || $is_resolved === 0) $payload['is_resolved'] =												$is_resolved;
				if ($page) $payload['page'] =																						$page;

				$payload = json_encode($payload);

			// connect with Attributable
				$curl = curl_init();

				curl_setopt_array($curl, [
					CURLOPT_URL => "https://api.attributables.com/" . $this->apiVersion . "/" . $this->key . "/events",
					CURLOPT_HEADER => true,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => "application/json",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => $this->settings['timeout'],
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "POST",
					CURLOPT_POSTFIELDS => $payload,
					CURLOPT_HTTPHEADER => [
						"Content-Type:application/json",
						"cache-control: no-cache"
					],
					CURLINFO_HEADER_OUT => true,
				]);

				$response = curl_exec($curl);
				$err = curl_error($curl);
				$info = curl_getinfo($curl);

				curl_close($curl);

			// parse & return
				if ($err) {
					$this->errors[] = $err;
					return false;
				}
				else {

					// separate headers from content
						list($headers, $content) = explode("\r\n\r\n", $response, 2);
					// convert content from JSON to array if required
						$array = json_decode($content, true);
					// return
						return ['headers'=>$headers, 'content'=>(is_array($array)? $array : $content)];

				}

		}

		public function user($author, $page = null) {

			// check for errors
				if (!$this->key) {
					$this->errors[] = "Missing API key";
					return false;
				}

				if (!$this->apiVersion) {
					$this->errors[] = "API version not specified";
					return false;
				}

				if (!$author) {
					$this->errors[] = "Author not specified";
					return false;
				}
				elseif (!is_array($author)) {
					$this->errors[] = "Author should be an array";
					return false;
				}

			// create data payload
				$payload = [];
				if (@$author['author_id']) $payload['author_id'] =																$author['author_id'];
				if (@$author['user_id']) $payload['user_id'] =																	$author['user_id'];
				if (@$author['ip']) $payload['ip'] =																			$author['ip'];
				if (@$author['email']) $payload['email'] =																		$author['email'];
				if (@$author['phone']) $payload['phone'] =																		$author['phone'];
				if (array_key_exists('is_blacklisted', @$author)) $payload['is_blacklisted'] =									$author['is_blacklisted'];
				if (array_key_exists('is_greylisted', @$author)) $payload['is_greylisted'] =									$author['is_greylisted'];
				if (array_key_exists('is_whitelisted', @$author)) $payload['is_whitelisted'] =									$author['is_whitelisted'];
				if ($page) $payload['page'] =																					$page;

				$payload = json_encode($payload);

			// connect with Attributable
				$curl = curl_init();

				curl_setopt_array($curl, [
					CURLOPT_URL => "https://api.attributables.com/" . $this->apiVersion . "/" . $this->key . "/user",
					CURLOPT_HEADER => true,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => "application/json",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => $this->settings['timeout'],
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "POST",
					CURLOPT_POSTFIELDS => $payload,
					CURLOPT_HTTPHEADER => [
						"Content-Type:application/json",
						"cache-control: no-cache"
					],
					CURLINFO_HEADER_OUT => true,
				]);

				$response = curl_exec($curl);
				$err = curl_error($curl);
				$info = curl_getinfo($curl);

				curl_close($curl);

			// parse & return
				if ($err) {
					$this->errors[] = $err;
					return false;
				}
				else {

					// separate headers from content
						list($headers, $content) = explode("\r\n\r\n", $response, 2);
					// convert content from JSON to array if required
						$array = json_decode($content, true);
					// return
						return ['headers'=>$headers, 'content'=>(is_array($array)? $array : $content)];

				}

		}

	}

?>
