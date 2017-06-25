<?php

	// alerts
		if ($logs->numberOfLogs) {
			echo "<div class='pageModule'>\n";
			echo $logs->html;
			$tl->page['javascript'] .= $logs->js;
			echo "</div>\n";
		}

	// unsent mail
		if (count($unsentMail)) {
			echo "<div class='pageModule'>\n";
			echo "  <h2>" . (count($unsentMail) ? "<span class='label label-default'>" . count($unsentMail) . "</span> " : false) . "Unsent mail</h2>\n";
			echo "        <table class='table table-condensed'>\n";
			echo "        <thead>\n";
			echo "        <tr>\n";
			echo "        <th>Date</th>\n";
			echo "        <th>From</th>\n";
			echo "        <th>To</th>\n";
			echo "        <th>Subject</th>\n";
			echo "        <th>Attempt(s)</th>\n";
			echo "        </tr>\n";
			echo "        </thead>\n";
			echo "        <tbody>\n";
			for ($counter = 0; $counter < count($unsentMail); $counter++) {
				if ($unsentMail[$counter]['failed_attempts'] >= $tl->settings['Maximum allowable failures per email address']) echo "        <tr class='danger'>\n";
				else echo "        <tr>\n";
				echo "        <td class='nowrap'>\n";
				echo "          " . date('j-M-Y', strtotime($unsentMail[$counter]['queued_on'])) . "<br />\n";
				echo "          <div class='text-muted'><small>" . date('g:i:s A', strtotime($unsentMail[$counter]['queued_on'])) . "</small></div>\n";
				echo "        </td>\n";
				echo "        <td>" . $unsentMail[$counter]['from_name'] . " <" . $unsentMail[$counter]['from_email'] . "></td>\n";
				echo "        <td>" . $unsentMail[$counter]['to_name'] . " <" . $unsentMail[$counter]['to_email'] . "></td>\n";
				echo "        <td><span class='popovers' data-placement='top' data-toggle='popover' title=" . '"' . htmlspecialchars($unsentMail[$counter]['subject'], ENT_QUOTES) . '"' . " data-html='true' data-content=" . '"' . nl2br(htmlspecialchars($unsentMail[$counter]['message_text'], ENT_QUOTES)) . '"' . ">" . $unsentMail[$counter]['subject'] . "</span></td>\n";
				echo "        <td>" . intval($unsentMail[$counter]['failed_attempts']) . "</td>\n";
				echo "        </tr>\n";
			}
			echo "        </tbody>\n";
			echo "        </table>\n";
			echo "</div>\n";
		}

	// flagged comments
		if (count($flaggedComments)) {
			echo "<div class='pageModule'>\n";
			echo "  <h2>Flagged comments</h2>\n";
			echo "  <table class='table table-hover table-condensed'>\n";
			echo "  <tr>\n";
			echo "  <th colspan='2'>Comment</th>\n";
			echo "  <th>Author</th>\n";
			echo "  <th>Rumour</th>\n";
			echo "  <th>Flags</th>\n";
			echo "  </tr>\n";
			for ($counter = 0; $counter < count($flaggedComments); $counter++) {
				echo "  <tr>\n";
				echo "  <td>" . date('j-M-Y', strtotime($flaggedComments[$counter]['comment_created_on'])) . "</td>\n";
				echo "  <td>" . $flaggedComments[$counter]['comment'] . "</td>\n";
				echo "  <td><a href='/profile/" . $flaggedComments[$counter]['comment_created_by'] . "'>" . $flaggedComments[$counter]['comment_created_by_full_name'] . "</a></td>\n";
				echo "  <td><a href='/rumour/" . $flaggedComments[$counter]['public_id'] . "/" . $parser->seoFriendlySuffix($flaggedComments[$counter]['description']) . "'>" . $parser->truncate($flaggedComments[$counter]['description'], 'c', 30) . "</a></td>\n";
				echo "  <td>" . floatval($flaggedComments[$counter]['number_of_flags']) . "</td>\n";
				echo "  </tr>\n";
			}
			echo "  </table>\n";
			echo "</div>\n";
		}
		
	// registrations
		if (count($registrants) > 0) {
			echo "<div class='pageModule'>\n";
			echo "  " . $form->start('editRegistrantsForm', '', 'post', 'form-inline', null, array('onSubmit'=>'return false;')) . "\n";
			echo "  " . $form->input('hidden', 'registrantToApprove') . "\n";
			echo "  " . $form->input('hidden', 'registrantToDelete') . "\n";
			echo "  <h2>Pending Registrants</h2>\n";
			echo "  <table class='table table-condensed table-hover'>\n";
			echo "  <thead>\n";
			echo "  <tr>\n";
			echo "  <th>Registered</th>\n";
			echo "  <th>User</th>\n";
			echo "  <th>Email</th>\n";
			echo "  <th>Location</th>\n";
			if ($logged_in['can_edit_users']) echo "  <th></th>\n";
			echo "  </tr>\n";
			echo "  </thead>\n";
			echo "  <tbody>\n";
			for ($counter = 0; $counter < count($registrants); $counter++) {
				echo "  <tr>\n";
				echo "  <td class='nowrap'>" . date('j-M-Y', strtotime($registrants[$counter]['registered_on'])) . "</td>\n";
				echo "  <td><span class='tooltips' data-toggle='tooltip' title='" . addSlashes($registrants[$counter]['full_name']) . "'>" . $registrants[$counter]['username'] . "</span></td>\n";
				echo "  <td><a href='mailto:" . $registrants[$counter]['email'] . "'>" . $registrants[$counter]['email'] . "</a></td>\n";
				$locationMap = trim(@$registrants[$counter]['city'] . ', ' . trim(@$registrants[$counter]['region'] . ', ' . @$registrants[$counter]['other_region'], ', ') . ', ' . @$registrants[$counter]['country'], ',- ');
				$locationLabel = @$registrants[$counter]['country'];
				if (@$registrants[$counter]['city']) $locationLabel .= " (" . @$registrants[$counter]['city'] . ")";
				echo "      <td><a href='https://maps.google.com/maps?q=" . urlencode($locationMap) . "' target='_blank'>" . $locationLabel . "</a></td>\n";
				if ($logged_in['can_edit_users']) {
					echo "  <td class='text-right nowrap'>\n";
					echo "    " . $form->input('button', null, null, false, 'Approve', 'btn btn-default btn-sm', null, null, null, null, array('onClick'=>'approveRegistrant("' . $registrants[$counter]['registration_id'] . '"); return false;')) . "\n";
					echo "    " . $form->input('button', null, null, false, 'Delete', 'btn btn-link btn-sm', null, null, null, null, array('onClick'=>'deleteRegistrant("' . $registrants[$counter]['registration_id'] . '"); return false;')) . "\n";
					echo "  </td>\n";
				}
				echo "  </tr>\n";
			}
			echo "  </tbody>\n";
			echo "  </table>\n";
			echo "  " . $form->end() . "\n";
			echo "</div>\n";
		}

	// system stats
		echo "<div class='pageModule'>\n";
		echo "  <h2>System stats</h2>\n";
		echo "  <div class='row'>\n";

		// system
			echo "    <div class='col-lg-10 col-md-9 col-sm-6 col-xs-6'>\n";
			echo "      <div class='row'>\n";
			echo "        <div class='col-md-6'><strong>Server</strong></div>\n";
			echo "        <div class='col-md-6'>" . gethostbyaddr($_SERVER['SERVER_ADDR']) . "</div>\n";
			echo "      </div>\n";
			echo "      <div class='row'>\n";
			echo "        <div class='col-md-6'><strong><a href='' onClick='return false;' data-toggle='modal' data-target='#phpinfoModal'>PHP</a> version</strong></div>\n";
			echo "        <div class='col-md-6'>" . phpversion() . "</div>\n";
			echo "      </div>\n";
			echo "      <div class='row'>\n";
			echo "        <div class='col-md-6'><strong>MySQL version</strong></div>\n";
			echo "        <div class='col-md-6'>" . $dbConnection->server_info . "</div>\n";
			echo "      </div>\n";
			echo "      <div class='row'>\n";
			echo "        <div class='col-md-6'><strong>Current DB</strong></div>\n";
			echo "        <div class='col-md-6'>" . $currentDatabase . "</div>\n";
			echo "      </div>\n";
			echo "      <div class='row'>\n";
			echo "        <div class='col-md-6'><strong>Size of DB</strong></div>\n";
			echo "        <div class='col-md-6'>" . $parser->addFileSizeSuffix($dbSize[0]['size_kb'] * 1024) . "</div>\n";
			echo "      </div>\n";
			echo "      <div class='row'>\n";
			echo "        <div class='col-md-6'><strong>SMTP status</strong></div>\n";
			echo "        <div class='col-md-6'>" . $mailServerStatus . "</div>\n";
			echo "      </div>\n";
			echo "    </div>\n";

		// local system time
			echo "    <div class='col-lg-2 col-md-3 col-sm-6 col-xs-6'>\n";
			echo "      <div id='timer' class='text-center'>" . date('g:i A') . "</div>\n";
			echo "      <div id='timerTimezone' class='text-muted text-center'>APP TIMEZONE: " . date('e') . "</div>\n";
			echo "    </div>\n";
			
		echo "  </div>\n";
		echo "</div>\n";

		// update clock
			$tl->page['javascript'] .= "// Dashboard clock (showing server time rather than browser time)\n";
			$tl->page['javascript'] .= "  var serverTime = moment('" . date('Y') . ", " . date('m') . ", " . date('d') . ", " . date('H') . ", " . date('i') . ", " . date('s') . "');\n";
			$tl->page['javascript'] .= "  var browserTime = moment();\n";
			$tl->page['javascript'] .= "  var offsetInMinutes = Math.round((browserTime - serverTime) / 60);\n";
			$tl->page['javascript'] .= "  setInterval(function(){\n";
			$tl->page['javascript'] .= "    var currentTime = moment().add(" . intval(@$offsetInMinutes) . ", 'Minutes');\n";
			$tl->page['javascript'] .= "    document.getElementById('timer').innerHTML = currentTime.format('h:mm A');;\n";
			$tl->page['javascript'] .= "  },500);\n";

		// phpinfo modal
			echo "<div class='modal fade' id='phpinfoModal' tabindex='-1' role='dialog' aria-labelledby='phpinfoModalLabel' aria-hidden='true'>\n";
			echo "  <div class='modal-dialog'>\n";
			echo "    <div class='modal-content'>\n";
			echo "      <div class='modal-header'>\n";
			echo "        <button type='button' class='close' data-dismiss='modal'><span aria-hidden='true'>&times;</span><span class='sr-only'>Close</span></button>\n";
			echo "        <h4 class='modal-title' id='phpinfoModalLabel'>PHP Configuration</h4>\n";
			echo "      </div>\n";
			echo "      <div class='modal-body'>\n";
			echo "        <pre>" . print_r(phpinfo_array(1), true) . "</pre>\n";
			echo "      </div>\n";
			echo "    </div>\n";
			echo "  </div>\n";
			echo "</div>\n\n";
		
?>