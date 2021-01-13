setup_project:
	composer install && php artisan key:generate

run: up
	sleep 20 && \
	./vendor/bin/sail artisan migrate && \
	./vendor/bin/sail artisan passport:install --force

up: down
	./vendor/bin/sail up -d &

down:
	./vendor/bin/sail down

tests_local_run:
	./vendor/bin/sail test --testsuite=Feature --stop-on-failure $(ARGS)


