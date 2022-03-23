INSERT INTO `wr_users` (`username`, `password_hash`, `password_score`, `first_name`, `last_name`, `email`, `primary_phone`, `primary_phone_sms`, `secondary_phone`, `secondary_phone_sms`, `country_id`, `region_id`, `other_region`, `city`, `is_proxy`, `is_tester`, `is_moderator`, `is_community_liaison`, `is_administrator`, `registered_on`, `registered_by`, `referred_by`, `last_login`, `ok_to_contact`, `anonymous`, `enabled`)
VALUES
	('dev', '$2a$08$rnQ4ryO7wD3HFbBvWnMTjOVyE8QGd3NzI7uxydVnvnNw0LG4AL/lC', 83, 'SOC', 'SOC', '_dev@dev.ngo', '', 0, '', 0, 'AF', 0, '', '', 0, 0, 0, 0, 1, '2022-02-24 04:59:02', 1, 0, '2022-02-24 05:22:30', 1, 0, 1);


INSERT INTO `wr_user_permissions`
	(
		`user_id`, `name`, `value`, `can_edit_content`, `can_update_settings`, `can_edit_settings`,
		`can_edit_users`, `can_send_email`, `can_run_housekeeping`
	)
VALUES
	((select user_id from `wr_users` where username='dev'), 'dev', 0, 1, 1, 1, 1, 1, 0);

