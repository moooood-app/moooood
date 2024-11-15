.PHONY: help install

DOCKER_EXEC_PHP = docker compose exec -it -e "TERM=xterm-256color" php

tls:
	docker cp $(docker compose ps -q api):/data/caddy/pki/authorities/local/root.crt /tmp/root.crt && sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain /tmp/root.crt

help: ## automatically generates a documentation of the available Makefile targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

install:
	$(DOCKER_EXEC_PHP) composer install

migrate:
	$(DOCKER_EXEC_PHP) bin/console doctrine:migrations:migrate

run:
	docker compose up -d --wait
