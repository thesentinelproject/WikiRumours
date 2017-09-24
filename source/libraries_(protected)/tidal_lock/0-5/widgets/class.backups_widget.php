<?php

	class backups_widget_TL {

		public $backups;
		private $locations = [
			'db' =>			"Database Backups",
			'vault' =>		"Vault Backups",
			'uploads' =>	"Upload Backups"
		];
		private $downloadPath = null;
		private $minutesToPreserveBackupCopy = 5;

		public function __construct() {

			$directory_manager = new directory_manager_TL();

			foreach ($this->locations as $location => $label) {

				$this->backups[$location] = [];

	    		$result = $directory_manager->read('../backups/' . $location, true, false, true);

	    		for ($counter = 0; $counter < count($result); $counter++) {
	    			$date = substr($result[$counter], strpos($result[$counter], 'backups/' . $location . '/') + strlen('backups/' . $location . '/'), 10);
	    			if (substr($result[$counter], -3) == '.gz' || substr($result[$counter], -4) == '.zip') $this->backups[$location][$date] = $result[$counter];
	    		}

	    		asort($this->backups[$location]);

			}

			if ($_POST['backupToDownload']) $this->moveBackup();

		}

		private function moveBackup() {

			if (!$_POST['backupToDownload']) return false;

			$expiry = date('Y-m-d_H-i-s', mktime(date('H'), date('i') + $this->minutesToPreserveBackupCopy, date('s'), date('m'), date('d'), date('Y')));

			mkdir('trash/' . $expiry);

			copy($_POST['backupToDownload'], 'trash/' . $expiry . '/' . basename($_POST['backupToDownload']));

			$this->downloadPath = 'trash/' . $expiry . '/' . basename($_POST['backupToDownload']);

		}

		public function displayBackups() {

			if ($_POST['backupToDownload']) {

				if (!file_exists($this->downloadPath)) {
					echo "<h2>Sorry, there was a problem moving this backup into a downloadable folder.<h2>\n";
				}
				else {
					$parser = new parser_TL();

					echo "<h2>Ready for download</h2>\n";
					echo "<p><a href='" . $this->downloadPath . "'>Download now</a> (" . $parser->addFileSizeSuffix(@filesize($this->downloadPath)) . ")</p>\n";
					echo "<p>This copy will be deleted at " . date('g:i A', mktime(date('H'), date('i') + $this->minutesToPreserveBackupCopy, date('s'), date('m'), date('d'), date('Y'))) . " (in " . floatval($this->minutesToPreserveBackupCopy) . " minutes).</p>\n";
					echo "<p><a href='" . $_SERVER['REQUEST_URI'] . "'>Return</a></p>\n";
				}

			}
			else {

				$form = new form_TL();

				echo $form->start('backupsForm');
				echo $form->input('hidden', 'backupToDownload') . "\n";

				foreach ($this->locations as $location => $label) {

					echo "<div id='" . $location . "_container' class='pageModule'>\n";

					echo "  <h2>" . $label . "</h2>\n";

					echo "  <div>\n";

					if (!count($this->backups[$location])) echo "<p>No backups found</p>\n";
					else {
						foreach ($this->backups[$location] as $date => $file) {
							echo "    <a href='javascript:void(0);' onClick='document.backupsForm.backupToDownload.value=" . '"' . $file .'"' . "; document.backupsForm.submit();'><span class='badge'>" . $date . "</span></a>\n";
						}
					}

					echo "  </div>\n";

					echo "</div>\n";

				}

				echo $form->end();

			}

		}

	}

?>