<?php

	class downloads_widget_TL {

		public $numberOfDownloads = null;
		public $downloads = null;
		public $html = null;

		public function initialize() {

			global $systemPreferences;

			$directory_manager = new directory_manager_TL();

			$downloadPath = __DIR__ . '/../../../web_root/downloads';

			// check for errors
				if (!file_exists($downloadPath)) {
					$console .= __CLASS__ . "->" . __FUNCTION__ . ": Unable to locate downloads directory.\n";
					return false;
				}

			// retrieve directory contents
				$this->downloads = $directory_manager->read($downloadPath, $recursive = true, false, true);
				$this->numberOfDownloads = count($this->downloads);
				for ($counter = 0; $counter < $this->numberOfDownloads; $counter++) {
					$this->downloads[$counter] = str_replace($downloadPath, '', $this->downloads[$counter]);
				}

			// create view
				$this->html = "<h2>" . ($this->numberOfDownloads ? "<span class='label label-default'>" . number_format(floatval($this->numberOfDownloads)) . "</span> " : false) . "Downloads</h2>";

				if (!$this->numberOfDownloads) {
					$this->html = "<p class='text-muted'>No results</p>\n";
				}
				else {

					$this->html .= "<table class='table table-condensed'>\n";
					$this->html .= "<thead>\n";
					$this->html .= "<tr>\n";
					$this->html .= "<th>Filename</th>\n";
					$this->html .= "<th>Created on</th>\n";
					$this->html .= "<th>Expires on</th>\n";
					$this->html .= "</tr>\n";
					$this->html .= "</thead>\n";
					$this->html .= "<tbody>\n";
					for ($counter = 0; $counter < $this->numberOfDownloads; $counter++) {
						$path = '/downloads' . $this->downloads[$counter];
						$filename = substr($this->downloads[$counter], 21);
						$timeCreated = strtotime(substr($this->downloads[$counter], 1, 10) . ' ' . substr($this->downloads[$counter], 12, 2) . ':' . substr($this->downloads[$counter], 15, 2) . ':' . substr($this->downloads[$counter], 18, 2));
						$createdOn = date('j-M-Y @ g:i A', $timeCreated);
						if (!@$systemPreferences['Delete downloadables after']) $expiresOn = '-';
						else $expiresOn = date('j-M-Y @ g:i A', mktime(date('H', $timeCreated), date('i', $timeCreated), date('s', $timeCreated), date('m', $timeCreated), date('d', $timeCreated) + floatval($systemPreferences['Delete downloadables after']), date('Y', $timeCreated)));


						$this->html .= "<tr>\n";
						$this->html .= "<td><a href='" . $path . "' target='_blank'>" . $filename . "</a></td>\n";
						$this->html .= "<td>" . $createdOn . "</td>\n";
						$this->html .= "<td>" . $expiresOn . "</td>\n";
						$this->html .= "</tr>\n";
					}
					$this->html .= "</tbody>\n";
					$this->html .= "</table>\n\n";

				}

		}

	}

?>
