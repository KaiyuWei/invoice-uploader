# Makefile

PHP_CS_FIXER = vendor/bin/php-cs-fixer

up:
	docker compose up -d

build-up:
	docker compose up -d --build

down:
	docker compose down

gen-swagger:
	./vendor/bin/openapi app/ -o ./docs/openapi.yaml

fix:
	$(PHP_CS_FIXER) fix $(file)

test:
	docker-compose exec app sh -c "cd /var/www/html && php artisan test"