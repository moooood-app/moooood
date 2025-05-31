.PHONY: help install

DOCKER_EXEC_API = docker compose exec -it -e "TERM=xterm-256color" api

tls: ## installs the local TLS certificate in the system keychain
	docker cp $(docker compose ps -q api):/data/caddy/pki/authorities/local/root.crt /tmp/root.crt && sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain /tmp/root.crt

help: ## automatically generates a documentation of the available Makefile targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

install: ## installs the dependencies and sets up the environment
	$(DOCKER_EXEC_API) composer install
	cp .env.sso.dist .env.sso
	$(DOCKER_EXEC_API) bin/console lexik:jwt:generate-keypair --skip-if-exists
	$(DOCKER_EXEC_API) bin/console doctrine:migrations:migrate --no-interaction

lint: lint-cs lint-sf lint-phpstan ## runs all linters

lint-cs: ## runs the PHP CS Fixer
	$(DOCKER_EXEC_API) composer run php-cs-fixer

lint-sf: ## runs the Symfony linters
	$(DOCKER_EXEC_API) bin/console lint:container
	$(DOCKER_EXEC_API) bin/console lint:yaml config
	$(DOCKER_EXEC_API) bin/console lint:twig

lint-phpstan: ## runs PHPStan
	$(DOCKER_EXEC_API) composer run phpstan

test: test-reset-database ## runs all tests
	$(DOCKER_EXEC_API) php -d memory_limit=1G vendor/bin/phpunit

test-integration:
	$(DOCKER_EXEC_API) php -d memory_limit=1G vendor/bin/phpunit --testsuite=integration

test-unit: ## runs the unit tests
	$(DOCKER_EXEC_API) php -d memory_limit=1G vendor/bin/phpunit --testsuite=unit

test-reset-database: ## resets the test database and loads fixtures
	$(DOCKER_EXEC_API) bin/console --env=test doctrine:database:create  --if-not-exists
	$(DOCKER_EXEC_API) bin/console --env=test doctrine:schema:drop --full-database --force --quiet
	$(DOCKER_EXEC_API) bin/console --env=test doctrine:schema:create --quiet
	$(DOCKER_EXEC_API) php -d memory_limit=1G bin/console --env=test doctrine:fixtures:load --no-interaction --quiet

migrate: ## runs the database migrations
	$(DOCKER_EXEC_API) bin/console doctrine:migrations:migrate --no-interaction

up: down ## starts the Docker containers
	docker compose up -d --wait

down: ## stops the Docker containers
	docker compose down --remove-orphans

build: ## builds the Docker images
	COMPOSE_BAKE=true docker compose build

build-bento: ## builds a Bento and containerizes it
	docker compose run --rm builder sh -c "bentoml build && bentoml containerize ${BENTO_NAME} -t ${BENTO_NAME}:${VERSION}"

app: ## starts the Expo app
	npx expo run:ios