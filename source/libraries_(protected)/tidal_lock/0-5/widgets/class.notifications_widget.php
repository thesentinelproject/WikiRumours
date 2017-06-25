<?php

	class notifications_widget_TL {

		public $html = null;
		public $js = null;

		private $screen = null;
		private $tab = null;
		private $recipient_id = null;
		private $recipient_type = null;
		private $notification_id = null;

		public $recipients = null;
		public $users = null;
		private $adminNotifications = null;
		private $userNotifications = null;

		private $recipientTypes = ['a'=>"Admin", 'u'=>"User"];

		public function initialize() {

			global $tl;
			global $tablePrefix;

			// parse query string
				$this->recipient_id = floatval(@$tl->page['filters']['recipient_id']);
				$this->notification_id = floatval(@$tl->page['filters']['notification_id']);
				$this->recipient_type = @$tl->page['filters']['recipient_type'];
				$this->tab = @$tl->page['filters']['tab'];

				if (@$tl->page['filters']['screen'] == 'edit_notification' && $this->notification_id) $this->screen = 'edit_notification'; 
				elseif (@$tl->page['filters']['screen'] == 'add_notification') $this->screen = 'add_notification'; 
				elseif (@$tl->page['filters']['screen'] == 'edit_recipient' && $this->recipient_id) $this->screen = 'edit_recipient'; 
				elseif (@$tl->page['filters']['screen'] == 'add_recipient') $this->screen = 'add_recipient'; 
				else $this->screen = 'index';

			// query database
				if ($this->screen == 'index') {
					$this->retrieveAdminRecipients(null, true);
					$this->retrieveNotifications(['recipient_type'=>'a'], true);
					$this->retrieveNotifications(['recipient_type'=>'u']);
				}
				elseif ($this->screen == 'edit_recipient') {
					$this->retrieveAdminRecipients([$tablePrefix . 'notification_recipients.recipient_id'=>$this->recipient_id], true);
					$this->retrieveNotifications(['recipient_type'=>'a']);
				}
				elseif ($this->screen == 'add_recipient') {
					$this->retrieveNotifications(['recipient_type'=>'a']);
				}
				elseif ($this->screen == 'edit_notification') {
					$this->retrieveNotifications(['type_id'=>$this->notification_id, 'recipient_type'=>$this->recipient_type]);
				}

			// call controllers
				if (count($_POST)) $this->controllers();

			// call views
				$this->createView();

		}

		public function retrieveAdminRecipients($matching = null, $includeNotifications = false) {

			global $dbConnection;
			global $tablePrefix;

			$parser = new parser_TL();
			
			$query = "SELECT " . $tablePrefix . "notification_recipients.recipient_id,";
			$query .= " " . $tablePrefix . "notification_recipients.name,";
			$query .= " " . $tablePrefix . "notification_recipients.email";
			$query .= " FROM " . $tablePrefix . "notification_recipients";
			$query .= " LEFT JOIN " . $tablePrefix . "notification_recipients_x_types ON " . $tablePrefix . "notification_recipients.recipient_id = " . $tablePrefix . "notification_recipients_x_types.recipient_id";
			$query .= " LEFT JOIN " . $tablePrefix . "notification_types ON " . $tablePrefix . "notification_recipients_x_types.type_id = " . $tablePrefix . "notification_types.type_id";
			$query .= " WHERE 1=1";
			if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
			$query .= " GROUP BY " . $tablePrefix . "notification_recipients.recipient_id";

			$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . ': ' . $dbConnection->error . '<br /><br />' . $query);

			$this->recipients = $parser->mySqliResourceToArray($result);
			
			$result->free();

			if ($includeNotifications) {

				for ($recipientCounter = 0; $recipientCounter < count($this->recipients); $recipientCounter++) {
						$query = "SELECT " . $tablePrefix . "notification_types.type_id,";
						$query .= " " . $tablePrefix . "notification_types.type,";
						$query .= " " . $tablePrefix . "notification_types.recipient_type";
						$query .= " FROM " . $tablePrefix . "notification_recipients_x_types";
						$query .= " LEFT JOIN " . $tablePrefix . "notification_types ON " . $tablePrefix . "notification_recipients_x_types.type_id = " . $tablePrefix . "notification_types.type_id";
						$query .= " WHERE " . $tablePrefix . "notification_recipients_x_types.recipient_id = '" . $this->recipients[$recipientCounter]['recipient_id'] . "'";
						$query .= " AND " . $tablePrefix . "notification_types.recipient_type = 'a'";

						$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . ': ' . $dbConnection->error . '<br /><br />' . $query);

						$this->recipients[$recipientCounter]['types'] = $parser->mySqliResourceToArray($result);
						
						$result->free();

				}

			}

			return $this->recipients;

		}

		private function retrieveUserRecipients($matching = null) {

			global $dbConnection;
			global $tablePrefix;

			$parser = new parser_TL();
			
			$query = "SELECT " . $tablePrefix . "users.user_id,";
			$query .= " " . $tablePrefix . "users.first_name,";
			$query .= " " . $tablePrefix . "users.last_name,";
			$query .= " CONCAT(" . $tablePrefix . "users.first_name, ' ', " . $tablePrefix . "users.last_name) AS full_name,";
			$query .= " " . $tablePrefix . "users.email";
			$query .= " FROM " . $tablePrefix . "users";
			$query .= " LEFT JOIN " . $tablePrefix . "notification_recipients_x_types ON " . $tablePrefix . "users.user_id = " . $tablePrefix . "notification_recipients_x_types.user_id";
			$query .= " LEFT JOIN " . $tablePrefix . "notification_types ON " . $tablePrefix . "notification_recipients_x_types.type_id = " . $tablePrefix . "notification_types.type_id";
			$query .= " WHERE 1=1";
			if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
			$query .= " GROUP BY " . $tablePrefix . "users.user_id";

			$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . ': ' . $dbConnection->error . '<br /><br />' . $query);

			$this->users = $parser->mySqliResourceToArray($result);
			
			$result->free();

			return $this->users;
			
		}

		private function retrieveNotifications($matching = null, $includeRecipients = false) {

			global $dbConnection;
			global $tablePrefix;

			$parser = new parser_TL();
			
			// retrieve notification(s)
				$query = "SELECT " . $tablePrefix . "notification_types.type_id,";
				$query .= " " . $tablePrefix . "notification_types.type,";
				$query .= " " . $tablePrefix . "notification_types.recipient_type,";
				$query .= " (SELECT COUNT(*) FROM " . $tablePrefix . "notification_recipients LEFT JOIN " . $tablePrefix . "notification_recipients_x_types ON " . $tablePrefix . "notification_recipients.recipient_id = " . $tablePrefix . "notification_recipients_x_types.recipient_id WHERE " . $tablePrefix . "notification_recipients_x_types.type_id = " . $tablePrefix . "notification_types.type_id) AS number_of_subscribers";
				$query .= " FROM " . $tablePrefix . "notification_types";
				$query .= " WHERE 1=1";
				if ($matching) foreach ($matching as $field => $value) $query .= " AND " . $field . " = '" . $dbConnection->escape_string($value) . "'";
				$query .= " GROUP BY " . $tablePrefix . "notification_types.type_id";

				$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . ': ' . $dbConnection->error . '<br /><br />' . $query);

				if (@$matching['recipient_type'] == 'a') $this->adminNotifications = $parser->mySqliResourceToArray($result);
				elseif (@$matching['recipient_type'] == 'u') $this->userNotifications = $parser->mySqliResourceToArray($result);
				
				$result->free();

			// retrieve associated recipients
				if ($includeRecipients) {
					for ($counter = 0; $counter < count($this->adminNotifications); $counter++) {
						$query = "SELECT " . $tablePrefix . "notification_recipients.recipient_id,";
						$query .= " " . $tablePrefix . "notification_recipients.name";
						$query .= " FROM " . $tablePrefix . "notification_recipients_x_types";
						$query .= " LEFT JOIN " . $tablePrefix . "notification_recipients ON " . $tablePrefix . "notification_recipients_x_types.recipient_id = " . $tablePrefix . "notification_recipients.recipient_id";
						$query .= " WHERE " . $tablePrefix . "notification_recipients_x_types.type_id = '" . $this->adminNotifications[$counter]['type_id'] . "'";

						$result = $dbConnection->query($query) or die('Unable to execute ' . __FUNCTION__ . ': ' . $dbConnection->error . '<br /><br />' . $query);

						if (@$matching['recipient_type'] == 'a') $this->adminNotifications[$counter]['recipients'] = $parser->mySqliResourceToArray($result);
						elseif (@$matching['recipient_type'] == 'u') $this->userNotifications[$counter]['recipients'] = $parser->mySqliResourceToArray($result);
						
						$result->free();
					}
				}

			if (@$matching['recipient_type'] == 'a') return $this->adminNotifications;
			elseif (@$matching['recipient_type'] == 'u') return $this->userNotifications;

		}

		private function controllers() {

			global $tl;
			global $logged_in;

			$authentication_manager = new authentication_manager_TL();
			$parser = new parser_TL();
			$input_validator = new input_validator_TL();
			$logger = new logger_TL();

			if ($_POST['formName'] == 'addEditRecipientForm' && @$this->recipient_id && $_POST['deleteRecipient'] == 'Y') {

				// delete notification
					deleteFromDbSingle('notification_recipients', ['recipient_id'=>$this->recipient_id]);
					deleteFromDb('notification_recipients_x_types', ['recipient_id'=>$this->recipient_id]);
					
				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has deleted the notification recipient &quot;" . $this->adminRecipients[0]['name'] . "&quot;";
					$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'recipient_id=' . $this->recipient_id));
					
				// redirect
					$authentication_manager->forceRedirect('/' . $tl->page['template'] . '/success=recipient_deleted');

			}
			elseif ($_POST['formName'] == 'addEditRecipientForm') {

				// clean input
					$_POST = $parser->trimAll($_POST);
					
				// check for errors
					if (!$_POST['name']) $tl->page['error'] .= "Please specify a recipient name. ";
					if (!$_POST['email'] || !$input_validator->validateEmailBasic($_POST['email'])) $tl->page['error'] .= "Please provide a valid recipient email. ";
					$exists = retrieveSingleFromDb('notification_recipients', null, ['email'=>$_POST['email']], null, null, null, "recipient_id != '" . @$this->recipient_id . "'");
					if (count($exists)) $tl->page['error'] .= "This email address belongs to an existing recipient. ";

					if (!$tl->page['error']) {

						// update DB
							if ($this->screen == 'add_recipient') $this->recipient_id = insertIntoDb('notification_recipients', ['name'=>$_POST['name'], 'email'=>$_POST['email']]);
							else updateDbSingle('notification_recipients', ['name'=>$_POST['name'], 'email'=>$_POST['email']], ['recipient_id'=>$this->recipient_id]);

							deleteFromDb('notification_recipients_x_types', ['recipient_id'=>$this->recipient_id]);
							for ($counter = 0; $counter < count($this->adminNotifications); $counter++) {
								if (isset($_POST['notification_' . $this->adminNotifications[$counter]['type_id']])) insertIntoDb('notification_recipients_x_types', ['recipient_id'=>$this->recipient_id, 'type_id'=>$this->adminNotifications[$counter]['type_id']]);
							}

						// update log
							$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has " . ($this->screen == 'add_recipient' ? "added" : "updated") . " the notification recipient &quot;" . $_POST['name'] . "&quot;";
							$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'recipient_id=' . $this->recipient_id));

						// redirect
							$authentication_manager->forceRedirect('/' . $tl->page['template'] . '/success=recipient_' . ($this->screen == 'add_recipient' ? "added" : "updated"));

					}

			}
			elseif ($_POST['formName'] == 'addEditNotificationForm' && @$this->notification_id && $_POST['deleteNotification'] == 'Y') {

				$notification = ($this->recipient_type == 'a' ? $this->adminNotifications : $this->userNotifications);

				// delete notification
					deleteFromDbSingle('notification_types', ['type_id'=>$this->notification_id]);
					deleteFromDb('notification_recipients_x_types', ['type_id'=>$this->notification_id]);
					
				// update log
					$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has deleted the " . @$this->recipientTypes[$this->recipient_type] . " notification &quot;" . $notification[0]['name'] . "&quot;";
					$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'notification_id=' . $this->notification_id));
					
				// redirect
					$authentication_manager->forceRedirect('/' . $tl->page['template'] . "/" . urlencode("tab=" . (@$this->recipient_type == 'a' ? "admin_notifications" : "user_notifications") . "|success=notification_deleted"));

			}
			elseif ($_POST['formName'] == 'addEditNotificationForm') {

				// clean input
					$_POST = $parser->trimAll($_POST);
					
				// check for errors
					if (!$_POST['type']) $tl->page['error'] .= "Please specify a name for your notification. ";
					$exists = retrieveSingleFromDb('notification_types', null, ['type'=>$_POST['type']], null, null, null, "type_id != '" . @$this->notification_id . "'");
					if (count($exists)) $tl->page['error'] .= "This notification name already exists. ";

					if (!$tl->page['error']) {

						// update DB
							if ($this->screen == 'add_notification') $this->notification_id = insertIntoDb('notification_types', ['type'=>$_POST['type'], 'recipient_type'=>$this->recipient_type]);
							else updateDbSingle('notification_types', ['type'=>$_POST['type']], ['type_id'=>$this->notification_id]);

						// update log
							$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") has " . ($this->screen == 'add_notification' ? "added" : "updated") . " the notification &quot;" . $_POST['type'] . "&quot;";
							$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id'], 'type_id=' . $this->notification_id));

						// redirect
							$authentication_manager->forceRedirect('/' . $tl->page['template'] . "/" . urlencode("tab=" . (@$this->recipient_type == 'a' ? "admin_notifications" : "user_notifications") . "|success=notification_" . ($this->screen == 'add_notification' ? "added" : "updated")));

					}

			}
			elseif ($_POST['formName'] == 'sendEmailForm') {

				// clean input
					$_POST = $parser->trimAll($_POST);
					if ($_POST['reply_to_email'] && !$_POST['reply_to_name']) $_POST['reply_to_name'] = $_POST['from_name'];
					
				// check for errors
					if (!$_POST['from_name']) $tl->page['error'] .= "Please provide a name for the sender of your email. ";
					if (!$_POST['to_name']) $tl->page['error'] .= "Please provide a name for the receiver of your email. ";
					if (!$input_validator->validateEmailRobust($_POST['to_email'])) $tl->page['error'] .= "Please provide a valid email address for the receiver of your email. ";
					if ($_POST['reply_to_email'] && !$input_validator->validateEmailRobust($_POST['reply_to_email'])) $tl->page['error'] .= "Please provide a valid email address for the reply-to. ";
					if (!$_POST['subject']) $tl->page['error'] .= "Please provide a subject for the email. ";
					if (!$_POST['message']) $tl->page['error'] .= "Please provide a message for the email. ";
					
					if (!$tl->page['error']) {
						// send email
							$messagePlain = $_POST['message'];
							if (function_exists(createHtmlEmail)) $messageHtml = createHtmlEmail(str_replace("\n", "<br />", $messagePlain));
							else $messageHtml = $messagePlain;
							insertIntoDb('mail_queue', array('to_name'=>$_POST['to_name'], 'to_email'=>$_POST['to_email'], 'from_name'=>(@$_POST['from_name'] ? $_POST['from_name'] : $tl->settings['Name of this application']), 'from_email'=>$tl->mail['OutgoingAddress'], 'subject'=>$_POST['subject'], 'message_html'=>$messageHtml, 'message_text'=>$messagePlain, 'reply_name'=>$_POST['reply_to_name'], 'reply_email'=>$_POST['reply_to_email'], 'queued_on'=>date('Y-m-d H:i:s')));

						// update log
							$activity = $logged_in['full_name'] . " (user_id " . $logged_in['user_id'] . ") sent an email to " . $_POST['name'] . " (" . $_POST['email'] . ") with the subject &quot;" . $_POST['subject'] . "&quot;";
							$logger->logItInDb($activity, null, array('user_id=' . $logged_in['user_id']));

						// redirect
							$authentication_manager->forceRedirect('/' . $tl->page['template'] . '/' . urlencode("tab=send|success=email_queued"));
					}

			}

		}

		private function createView() {

			global $tl;
			global $logged_in;

			$form = new form_TL();

			if ($this->screen == 'index') {

				// title
					$this->html .= "<h2>Notifications</h2>\n";

				// nav
					$this->html .= "<ul class='nav nav-tabs' role='tablist'>\n";
					$this->html .= "  <li role='presentation'" . (!$this->tab ? " class='active'" : false) . "><a href='#index' aria-controls='index' role='tab' data-toggle='tab'>Recipients" . (count($this->recipients) ? " (" . count($this->recipients) . ")" : false) . "</a></li>\n";
					$this->html .= "  <li role='presentation'" . ($this->tab == 'admin_notifications' ? " class='active'" : false) . "><a href='#admin_notifications' aria-controls='admin_notifications' role='tab' data-toggle='tab'>Admin Notifications" . (count($this->adminNotifications) ? " (" . count($this->adminNotifications) . ")" : false) . "</a></li>\n";
					$this->html .= "  <li role='presentation'" . ($this->tab == 'user_notifications' ? " class='active'" : false) . "><a href='#user_notifications' aria-controls='user_notifications' role='tab' data-toggle='tab'>User Notifications" . (count($this->userNotifications) ? " (" . count($this->userNotifications) . ")" : false) . "</a></li>\n";
					$this->html .= "  <li role='presentation'" . ($this->tab == 'send' ? " class='active'" : false) . "><a href='#send' aria-controls='send' role='tab' data-toggle='tab'>Send Email</a></li>\n";
					$this->html .= "</ul>\n";

				// content
					$this->html .= "<br />\n";
					$this->html .= "<div class='tab-content'>\n";

					// recipients
						$this->html .= "  <div role='tabpanel' class='tab-pane" . (!$this->tab ? " active" : false) . "' id='index'>\n";

						if (!count($this->recipients)) $this->html .= "<p>None yet</p>\n";
						else {

							$this->html .= "    <table class='table table-condensed'>\n";
							$this->html .= "    <thead>\n";
							$this->html .= "    <tr>\n";
							$this->html .= "    <th>Name</th>\n";
							$this->html .= "    <th>Email</th>\n";
							$this->html .= "    <th>Notifications</th>\n";
							$this->html .= "    <th></th>\n";
							$this->html .= "    </tr>\n";
							$this->html .= "    </thead>\n";
							$this->html .= "    <tbody>\n";

							for ($recipientCounter = 0; $recipientCounter < count($this->recipients); $recipientCounter++) {
								$this->html .= "    <tr>\n";
								$this->html .= "    <td>" . $this->recipients[$recipientCounter]['name'] . "</td>\n";
								$this->html .= "    <td>" . $this->recipients[$recipientCounter]['email'] . "</td>\n";
								$this->html .= "    <td>\n";
								for ($notificationCounter = 0; $notificationCounter < count($this->recipients[$recipientCounter]['types']); $notificationCounter++) {
									$this->html .= "      <a href='/" . $tl->page['template'] . "/" . urlencode("screen=edit_notification|recipient_type=" . $this->recipients[$recipientCounter]['types'][$notificationCounter]['recipient_type'] . "|notification_id=" . $this->recipients[$recipientCounter]['types'][$notificationCounter]['type_id']) . "'><span class='badge'><span class='glyphicon glyphicon-envelope'></span> &nbsp; " . strtoupper($this->recipients[$recipientCounter]['types'][$notificationCounter]['type']) . "</span></a>\n";
								}
								$this->html .= "    </td>\n";
								$this->html .= "    <td><a href='/" . $tl->page['template'] . "/" . urlencode("screen=edit_recipient|recipient_id=" .  $this->recipients[$recipientCounter]['recipient_id']) . "' class='btn btn-default btn-sm'>Edit</a></td>\n";
								$this->html .= "    </tr>\n";
							}

							$this->html .= "    </tbody>\n";
							$this->html .= "    </table>\n";

						}

						$this->html .= "    <p><a href='/" . $tl->page['template'] . "/" . urlencode("screen=add_recipient") . "' class='btn btn-primary'>Add Recipient</a></p>\n";

						$this->html .= "  </div>\n";

					// admin notifications
						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab == 'admin_notifications' ? " active" : false) . "' id='admin_notifications'>\n";

						if (!count($this->adminNotifications)) $this->html .= "<p>None yet</p>\n";
						else {

							$this->html .= "    <table class='table table-condensed'>\n";
							$this->html .= "    <thead>\n";
							$this->html .= "    <tr>\n";
							$this->html .= "    <th>Notification</th>\n";
							$this->html .= "    <th>Recipients</th>\n";
							$this->html .= "    <th></th>\n";
							$this->html .= "    </tr>\n";
							$this->html .= "    </thead>\n";
							$this->html .= "    <tbody>\n";

							for ($notificationCounter = 0; $notificationCounter < count($this->adminNotifications); $notificationCounter++) {
								$this->html .= "    <tr>\n";
								$this->html .= "    <td>" . $this->adminNotifications[$notificationCounter]['type'] . "</td>\n";
								$this->html .= "    <td>\n";
								for ($recipientCounter = 0; $recipientCounter < count($this->adminNotifications[$notificationCounter]['recipients']); $recipientCounter++) {
									$this->html .= "      <a href='" . $tl->page['template'] . "/" . urlencode("screen=edit_recipient|recipient_id=" . $this->adminNotifications[$notificationCounter]['recipients'][$recipientCounter]['recipient_id']) . "'><span class='badge'><span class='glyphicon glyphicon-user'></span> &nbsp; " . strtoupper($this->adminNotifications[$notificationCounter]['recipients'][$recipientCounter]['name']) . "</span></a>\n";
								}
								$this->html .= "    </td>\n";
								$this->html .= "    <td><a href='/" . $tl->page['template'] . "/" . urlencode("screen=edit_notification|recipient_type=a|notification_id=" .  $this->adminNotifications[$notificationCounter]['type_id']) . "' class='btn btn-default btn-sm'>Edit</a></td>\n";
								$this->html .= "    </tr>\n";
							}

							$this->html .= "    </tbody>\n";
							$this->html .= "    </table>\n";

						}

						$this->html .= "    <p><a href='/" . $tl->page['template'] . "/" . urlencode("screen=add_notification|recipient_type=a") . "' class='btn btn-primary'>Add Notification</a></p>\n";

						$this->html .= "  </div>\n";

					// user notifications
						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab == 'user_notifications' ? " active" : false) . "' id='user_notifications'>\n";

						if (!count($this->userNotifications)) $this->html .= "<p>None yet</p>\n";
						else {

							$this->html .= "    <table class='table table-condensed'>\n";
							$this->html .= "    <thead>\n";
							$this->html .= "    <tr>\n";
							$this->html .= "    <th>Notification</th>\n";
							$this->html .= "    <th>Recipients</th>\n";
							$this->html .= "    <th></th>\n";
							$this->html .= "    </tr>\n";
							$this->html .= "    </thead>\n";
							$this->html .= "    <tbody>\n";

							for ($notificationCounter = 0; $notificationCounter < count($this->userNotifications); $notificationCounter++) {
								$this->html .= "    <tr>\n";
								$this->html .= "    <td>" . $this->userNotifications[$notificationCounter]['type'] . "</td>\n";
								$this->html .= "    <td>" . $this->userNotifications[$notificationCounter]['number_of_subscribers'] . "</td>\n";
								$this->html .= "    <td><a href='/" . $tl->page['template'] . "/" . urlencode("screen=edit_notification|recipient_type=u|notification_id=" .  $this->userNotifications[$notificationCounter]['type_id']) . "' class='btn btn-default btn-sm'>Edit</a></td>\n";
								$this->html .= "    </tr>\n";
							}

							$this->html .= "    </tbody>\n";
							$this->html .= "    </table>\n";

						}

						$this->html .= "    <p><a href='/" . $tl->page['template'] . "/" . urlencode("screen=add_notification|recipient_type=u") . "' class='btn btn-primary'>Add Notification</a></p>\n";

						$this->html .= "  </div>\n";

					// send email
						$this->html .= "  <div role='tabpanel' class='tab-pane" . ($this->tab == 'send' ? " active" : false) . "' id='send'>\n";

						$this->html .= "  " . $form->start('sendEmailForm') . "\n";
						
						// from
							$this->html .= $form->rowStart('from', 'From');
							$this->html .= "  <div class='row'>\n";
							$this->html .= "    <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6'>\n";
							$this->html .= "      " . $form->input('text', 'from_name', (@$_POST['from_name'] ? $_POST['from_name'] : $logged_in['full_name']), true, '|Name', 'form-control') . "\n";
							$this->html .= "    </div>\n";
							$this->html .= "    <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6 text-muted'>\n";
							$this->html .= "      " . $tl->mail['OutgoingAddress'] . "\n";
							$this->html .= "    </div>\n";
							$this->html .= "  </div>\n";
							$this->html .= $form->rowEnd();
						// to
							$this->html .= $form->rowStart('recipient', 'To');
							$this->html .= "  <div class='row'>\n";
							$this->html .= "    <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6'>\n";
							$this->html .= "      " . $form->input('text', 'to_name', @$_POST['to_name'], true, '|Name', 'form-control') . "\n";
							$this->html .= "    </div>\n";
							$this->html .= "    <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6'>\n";
							$this->html .= "      " . $form->input('email', 'to_email', @$_POST['to_email'], true, '|Email', 'form-control') . "\n";
							$this->html .= "    </div>\n";
							$this->html .= "  </div>\n";
							$this->html .= $form->rowEnd();
						// reply
							$this->html .= $form->rowStart('replyTo', 'Reply to');
							$this->html .= "  <div class='row'>\n";
							$this->html .= "    <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6'>\n";
							$this->html .= "      " . $form->input('text', 'reply_to_name', (@$_POST['reply_to_name'] ? $_POST['reply_to_name'] : $logged_in['full_name']), false, '|Name', 'form-control') . "\n";
							$this->html .= "    </div>\n";
							$this->html .= "    <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6'>\n";
							$this->html .= "      " . $form->input('email', 'reply_to_email', (@$_POST['reply_to_email'] ? $_POST['reply_to_email'] : $logged_in['email']), false, '|Email', 'form-control') . "\n";
							$this->html .= "    </div>\n";
							$this->html .= "  </div>\n";
							$this->html .= $form->rowEnd();
						// subject
							$this->html .= $form->row('text', 'subject', @$_POST['subject'], true, 'Subject', 'form-control') . "\n";
						// message
							$this->html .= $form->row('textarea', 'message', @$_POST['message'], true, 'Message', 'form-control', null, null, null, null, array('rows'=>'5')) . "\n";
						// actions
							$this->html .= $form->row('submit', 'Send', null, false, 'Send', 'btn btn-primary') . "\n";
						
						$this->html .= "  " . $form->end() . "\n";

						$this->html .= "  </div>\n";

					$this->html .= "</div>\n";

			}
			elseif ($this->screen == 'edit_recipient' || $this->screen == 'add_recipient') {

					$this->html .= "<h2>" . ucwords(str_replace('_', ' ', $this->screen)) . "</h2>\n";

					$this->html .= $form->start('addEditRecipientForm') . "\n";
					$this->html .= $form->input('hidden', 'deleteRecipient') . "\n";

					// name
						$this->html .= $form->row('text', 'name', (@$_POST['name'] ? $_POST['name'] : @$this->recipients[0]['name']), true, "Name", 'form-control');
					// email
						$this->html .= $form->row('email', 'email', (@$_POST['email'] ? $_POST['email'] : @$this->recipients[0]['email']), true, "Email", 'form-control');
					// notifications
						$this->html .= $form->rowStart('notifications', "Notifications");
						for ($counter = 0; $counter < count($this->adminNotifications); $counter++) {
							$isset = 0;
							if (@$this->recipients[0]['types']) {
								foreach ($this->recipients[0]['types'] as $key=>$value) {
									if ($value['type_id'] == $this->adminNotifications[$counter]['type_id']) $isset = 1;
								}
							}
							$this->html .= "  <div>" . $form->input('checkbox_stacked_bootstrap', 'notification_' . $this->adminNotifications[$counter]['type_id'], (@$_POST['notification_' . $this->adminNotifications[$counter]['type_id']] ? $_POST['notification_' . $this->adminNotifications[$counter]['type_id']] : $isset), false, $this->adminNotifications[$counter]['type']) . "</div>\n";
						}
						$this->html .= $form->rowEnd();
					// actions
						$this->html .= $form->rowStart('actions');
						$this->html .= "  <div class='row'>\n";
						$this->html .= "    <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6'>\n";
						$this->html .= "      " . $form->input('submit', 'submit_button', null, false, "Save", 'btn btn-primary') . "\n";
						$this->html .= "      " . $form->input('cancel_and_return', 'cancel_button', null, false, "Cancel", 'btn btn-link') . "\n";
						$this->html .= "    </div>\n";
						$this->html .= "    <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6 text-right'>\n";
						if ($this->screen == 'edit_recipient') $this->html .= "      " . $form->input('button', 'delete_button', null, false, "Delete", 'btn btn-danger', null, null, null, null, ['onClick'=>'if (confirm("Are you sure?")) { document.addEditRecipientForm.deleteRecipient.value = "Y"; document.addEditRecipientForm.submit(); }']) . "\n";
						$this->html .= "    </div>\n";
						$this->html .= "  </div>\n";
						$this->html .= $form->rowEnd();

					$this->html .= $form->end();
				
			}
			elseif ($this->screen == 'edit_notification' || $this->screen == 'add_notification') {

				$notification = ($this->recipient_type == 'a' ? $this->adminNotifications : $this->userNotifications);
				
				$this->html .= "<h2>" . ucwords(str_replace('_', ' ' . @$this->recipientTypes[$this->recipient_type] . ' ', $this->screen)) . "</h2>\n";

				$this->html .= $form->start('addEditNotificationForm') . "\n";
				$this->html .= $form->input('hidden', 'deleteNotification') . "\n";

				// type
					$this->html .= $form->row('text', 'type', (@$_POST['type'] ? $_POST['type'] : @$notification[0]['type']), true, "Name", 'form-control');
				// actions
					$this->html .= $form->rowStart('actions');
					$this->html .= "  <div class='row'>\n";
					$this->html .= "    <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6'>\n";
					$this->html .= "      " . $form->input('submit', 'submit_button', null, false, "Save", 'btn btn-primary') . "\n";
					$this->html .= "      " . $form->input('cancel_and_return', 'cancel_button', null, false, "Cancel", 'btn btn-link') . "\n";
					$this->html .= "    </div>\n";
					$this->html .= "    <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6 text-right'>\n";
					if ($this->screen == 'edit_notification') $this->html .= "      " . $form->input('button', 'delete_button', null, false, "Delete", 'btn btn-danger', null, null, null, null, ['onClick'=>'if (confirm("Are you sure?")) { document.addEditNotificationForm.deleteNotification.value = "Y"; document.addEditNotificationForm.submit(); }']) . "\n";
					$this->html .= "    </div>\n";
					$this->html .= "  </div>\n";
					$this->html .= $form->rowEnd();

				$this->html .= $form->end();

			}

		}

	}

?>
