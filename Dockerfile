# Runs the app with PHP 8.4. Node is only used while building the frontend.

FROM php:8.4-cli-bookworm AS base

# PHP extensions the app needs (gd is used by the tests to create fake images).
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql bcmath gd intl zip opcache pcntl \
    && apt-get update \
    && apt-get install -y --no-install-recommends unzip default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html


FROM base AS build

# Node for building the React frontend.
COPY --from=node:24-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:24-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

# Install dependencies first so Docker can cache them between builds.
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --no-autoloader --prefer-dist

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY . .

# The Vite build runs `php artisan wayfinder:generate`, so PHP dependencies must be ready first.
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi \
    && npm run build \
    && rm -rf node_modules


FROM base AS app

COPY --from=build /var/www/html /var/www/html
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

RUN chmod +x /usr/local/bin/entrypoint \
    && mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

ENV PHP_CLI_SERVER_WORKERS=4

EXPOSE 8000

ENTRYPOINT ["entrypoint"]
# --no-reload lets the server use PHP_CLI_SERVER_WORKERS to handle several requests at once.
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000", "--no-reload"]
