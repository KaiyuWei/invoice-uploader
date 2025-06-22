# Makefile

PHP_CS_FIXER = vendor/bin/php-cs-fixer

up:
	docker compose up -d

build-up:
	docker compose up -d --build

down:
	docker compose down

build:
	docker compose build

logs:
	docker compose logs -f

ps:
	docker compose ps

gen-swagger:
	./vendor/bin/openapi app/ -o ./docs/openapi.yaml

fix:
	$(PHP_CS_FIXER) fix $(file)