FROM php:8.4-cli-alpine AS base

WORKDIR /app

VOLUME /app/var/

RUN apk update && apk upgrade

RUN apk add build-base
RUN apk add autoconf
RUN apk add --update linux-headers

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apk add --no-cache libzip-dev \
	zip \
	postgresql-dev \
	libxslt-dev

RUN set -eux; \
	docker-php-ext-install \
		intl \
		opcache \
		zip \
		pdo_pgsql \
		xsl \
	;

RUN pecl install redis && \
	docker-php-ext-enable redis

RUN pecl install apcu && \
		docker-php-ext-enable apcu

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

COPY --link docker/messenger/conf.d/10-app.ini $PHP_INI_DIR/app.conf.d/

RUN apk add --no-cache supervisor
COPY --link docker/messenger/supervisor.conf /etc/supervisord.conf

# Dev image
FROM base AS dev

ENV APP_ENV=dev XDEBUG_MODE=off

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Install Xdebug - not yet available on PHP 8.4
# RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY --link docker/messenger/conf.d/20-app.dev.ini $PHP_INI_DIR/app.conf.d/

CMD ["supervisord", "-c", "/etc/supervisord.conf"]

# Production image
FROM base AS prod

ENV APP_ENV=prod

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --link docker/messenger/conf.d/20-app.prod.ini $PHP_INI_DIR/app.conf.d/

# prevent the reinstallation of vendors at every changes in the source code
COPY --link api/composer.* api/symfony.* ./
RUN set -eux; \
	composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

COPY --link api ./

RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	composer dump-env prod; \
	composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync;

CMD ["supervisord", "-c", "/etc/supervisord.conf"]
