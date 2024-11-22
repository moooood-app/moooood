.PHONY: help install

DOCKER_EXEC_API = docker compose exec -it -e "TERM=xterm-256color" php

tls:
	docker cp $(docker compose ps -q api):/data/caddy/pki/authorities/local/root.crt /tmp/root.crt && sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain /tmp/root.crt

help: ## automatically generates a documentation of the available Makefile targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

install:
	$(DOCKER_EXEC_API) composer install
	cp api/.env.sso.dist api/.env.sso
	$(DOCKER_EXEC_API) bin/console lexik:jwt:generate-keypair --skip-if-exists
	$(DOCKER_EXEC_API) bin/console doctrine:migrations:migrate --no-interaction


lint:
	$(DOCKER_EXEC_API) vendor/bin/php-cs-fixer fix
	$(DOCKER_EXEC_API) bin/console lint:container
	$(DOCKER_EXEC_API) bin/console lint:yaml config
	$(DOCKER_EXEC_API) bin/console lint:twig
	$(DOCKER_EXEC_API) vendor/bin/phpstan --memory-limit=1G analyse

test: test-reset-database
	$(DOCKER_EXEC_API) vendor/bin/phpunit

test-unit:
	$(DOCKER_EXEC_API) vendor/bin/phpunit --testsuite=unit

test-reset-database:
	$(DOCKER_EXEC_API) bin/console --env=test doctrine:database:create  --if-not-exists
	$(DOCKER_EXEC_API) bin/console --env=test doctrine:schema:drop --full-database --force --quiet
	$(DOCKER_EXEC_API) bin/console --env=test doctrine:schema:create --quiet
	$(DOCKER_EXEC_API) bin/console --env=test doctrine:fixtures:load --no-interaction --quiet

test-integration: test-reset-database
	$(DOCKER_EXEC_API) vendor/bin/phpunit --testsuite=integration

migrate:
	$(DOCKER_EXEC_API) bin/console doctrine:migrations:migrate --no-interaction

run: stop
	docker compose up -d --wait

stop:
	docker compose down --remove-orphans
