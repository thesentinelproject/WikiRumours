drop-db:
	mysqladmin -f -u root -p drop wikirumours 2>/dev/null || true

create-db:
	mysqladmin -u root -p create wikirumours

add-local-dev-user:
	mysql -u root wikirumours < db_setup/dev-setup.sql

reset: drop-db create-db # add-local-dev-user
	mysql -u root wikirumours < db_setup/wikirumours_msf.sql
	mysql -u root wikirumours < db_setup/add-dev-user.sql

blank-reset: drop-db create-db # add-local-dev-user
	mysql -u root wikirumours < db_setup/import_me.sql
	mysql -u root wikirumours < db_setup/add-dev-user.sql

docker-reset:
	docker-compose exec -T mysql bash -c 'mysqladmin -f -u root drop wikirumours >/dev/null || true'
	docker-compose exec -T mysql mysqladmin -u root create wikirumours
	docker-compose exec -T mysql mysql wikirumours < db_setup/wikirumours_msf.sql
	docker-compose exec -T mysql mysql wikirumours < db_setup/add-dev-user.sql

docker-blank-reset:
	docker-compose exec -T mysql bash -c 'mysqladmin -f -u root drop wikirumours >/dev/null || true'
	docker-compose exec -T mysql mysqladmin -u root create wikirumours
	docker-compose exec -T mysql mysql wikirumours < db_setup/import_me.sql
	docker-compose exec -T mysql mysql wikirumours < db_setup/add-dev-user.sql
