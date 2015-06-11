<?php 
	include 'includes/views/desktop/shared/page_top.php';

	echo "<h2>Auto-migration</h2>\n";

	if ($go != "go") {
		echo "<p><a href='/migrate/go'>Go</a></p>\n";
	}
	elseif ($go == "go") {

		// unneeded tables
			directlyQueryDb("DROP TABLE wr_provinces_in_canada, wr_states_in_usa");
			echo "<p>Removed the tables states_in_usa and provinces_in_canada.</p>\n";

		// migrate instances into rumours
			directlyQueryDb("UPDATE wr_rumours SET pseudonym_id = '1'");
			echo "<p>Rumours now populated with pseudonym_id.</p>\n";

		// migrate FAQs
			directlyQueryDb("ALTER TABLE wr_faqs CHANGE chapter_id section_id INT(3)");
			directlyQueryDb("ALTER TABLE wr_faqs CHANGE faq_position position INT(3)");
			echo "<p>FAQs migrated.</p>\n";

		// migrate FAQ sections
			directlyQueryDb("ALTER TABLE wr_faq_chapters CHANGE chapter_id section_id INT(3)");
			directlyQueryDb("ALTER TABLE wr_faq_chapters CHANGE chapter_position position INT(3)");
			directlyQueryDb("RENAME TABLE wr_faq_chapters TO wr_faq_sections");
			echo "<p>FAQ sections migrated.</p>\n";

		// migrate notifications
			directlyQueryDb("UPDATE wr_notifications SET recipient_email = email");
			directlyQueryDb("ALTER TABLE wr_notifications DROP email");
			echo "<p>Notifications migrated.</p>\n";

		// migrate sightings
			directlyQueryDb("ALTER TABLE wr_rumour_sightings CHANGE country country_id VARCHAR(2)");
			directlyQueryDb("ALTER TABLE wr_rumour_sightings CHANGE region city VARCHAR(50)");
			directlyQueryDb("UPDATE wr_rumour_sightings SET source = 1 WHERE source = 'w'"); // website
			directlyQueryDb("UPDATE wr_rumour_sightings SET source = 2 WHERE source = 'e'"); // email
			directlyQueryDb("UPDATE wr_rumour_sightings SET source = 3 WHERE source = 's'"); // sms
			directlyQueryDb("UPDATE wr_rumour_sightings SET source = 4 WHERE source = 'v'"); // voice
			directlyQueryDb("UPDATE wr_rumour_sightings SET source = 5 WHERE source = 'p'"); // walk-in
			directlyQueryDb("ALTER TABLE wr_rumour_sightings CHANGE source source_id TINYINT(2)");
			echo "<p>Sightings migrated.</p>\n";
	
		// migrate rumours
			$time = time();
			directlyQueryDb("ALTER TABLE wr_rumours CHANGE country country_id VARCHAR(2)");
			directlyQueryDb("ALTER TABLE wr_rumours CHANGE region city VARCHAR(50)");
			updateDB('rumours', array('status_id'=>'0', 'pseudonym_id'=>'1'));

			$rumours = retrieveFromDb('rumours');
			for ($counter = 0; $counter < count($rumours); $counter++) {
				$status = retrieveFromDB('statuses', null, array('abbreviation'=>$rumours[$counter]['status']));
				updateDB('rumours', array('status_id'=>@$status[0]['status_id']), array('rumour_id'=>$rumours[$counter]['rumour_id']));
			}
			directlyQueryDb("ALTER TABLE wr_rumours DROP status");
			echo "<p>Rumours migrated in " . (time() - $time) . " sec.</p>\n";

		// migrate users
			$time = time();
			directlyQueryDb("UPDATE wr_users SET country_id = country");
			directlyQueryDb("ALTER TABLE wr_users DROP country");
			directlyQueryDb("UPDATE wr_users SET other_region = other_province_state");
			directlyQueryDb("ALTER TABLE wr_users DROP other_province_state");
			directlyQueryDb("ALTER TABLE wr_users CHANGE phone primary_phone VARCHAR(12)");
//			updateDB('users', array('region_id'=>'0'));
			directlyQueryDb("UPDATE wr_users SET primary_phone_sms = 1 WHERE sms_notifications = primary_phone");
			directlyQueryDb("UPDATE wr_users SET secondary_phone_sms = 1 WHERE sms_notifications = secondary_phone");
			directlyQueryDb("ALTER TABLE wr_users DROP sms_notifications");

			$users = retrieveFromDb('users');
			for ($counter = 0; $counter < count($users); $counter++) {
				$regions = retrieveFromDB('regions', null, array('abbreviation'=>$users[$counter]['province_state'], 'country_id'=>$users[$counter]['country_id']));
				updateDB('users', array('region_id'=>@$regions[0]['region_id']), array('user_id'=>$users[$counter]['user_id']));
			}
			directlyQueryDb("ALTER TABLE wr_users DROP province_state");
			directlyQueryDb("UPDATE wr_users SET is_moderator = 1 WHERE username = 'timothyquinn'");
			echo "<p>Users migrated in " . (time() - $time) . " sec.</p>\n";

		// migrate registrants
			$time = time();
			directlyQueryDb("UPDATE wr_registrations SET country_id = country");
			directlyQueryDb("ALTER TABLE wr_registrations DROP country");
			directlyQueryDb("UPDATE wr_registrations SET other_region = other_province_state");
			directlyQueryDb("ALTER TABLE wr_registrations DROP other_province_state");
			directlyQueryDb("ALTER TABLE wr_registrations CHANGE phone primary_phone VARCHAR(12)");
//			updateDB('users', array('region_id'=>'0'));
			directlyQueryDb("UPDATE wr_registrations SET primary_phone_sms = 1 WHERE sms_notifications = primary_phone");
			directlyQueryDb("UPDATE wr_registrations SET secondary_phone_sms = 1 WHERE sms_notifications = secondary_phone");
			directlyQueryDb("ALTER TABLE wr_registrations DROP sms_notifications");

			$registrants = retrieveFromDb('registrations');
			for ($counter = 0; $counter < count($registrants); $counter++) {
				$regions = retrieveFromDB('regions', null, array('abbreviation'=>$registrants[$counter]['province_state'], 'country_id'=>$registrants[$counter]['country_id']));
				updateDB('registrations', array('region_id'=>@$regions[0]['region_id']), array('registration_id'=>$registrants[$counter]['registration_id']));
			}
			directlyQueryDb("ALTER TABLE wr_registrations DROP province_state");
			echo "<p>Registrations migrated in " . (time() - $time) . " sec.</p>\n";

		// fix CMS
			directlyQueryDb("UPDATE wr_cms SET content = REPLACE(content, 'workflow_large.jpg', '20150515161359/workflow.jpg')");
			directlyQueryDb("UPDATE wr_cms SET content = REPLACE(content, 'workflow_small.jpg', '20150515161359/workflow.jpg')");
			directlyQueryDb("UPDATE wr_cms SET content = REPLACE(content, 'una_hakika_caller.jpg', '20150527140323/una_hakika_caller.jpg')");
			directlyQueryDb("UPDATE wr_cms SET content = REPLACE(content, 'community_ambassadors.jpg', '20150527140339/community_ambassadors.jpg')");

	}

	include 'includes/views/desktop/shared/page_bottom.php';
?>