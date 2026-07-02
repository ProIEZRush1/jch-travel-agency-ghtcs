# Client site — Laravel + Vue (Inertia) + embedded Baileys WhatsApp gateway. Laravel assets
# are pre-built and committed, so the image build is just composer (fast) plus `npm ci` for the
# Node gateway in gateway/; no npm build of the Laravel app at image time. Laravel is served by
# `php artisan serve` under an auto-respawn loop (see docker/start.sh) so a single request error
# can never leave the app permanently down. The gateway runs as a detached child on 127.0.0.1:3001.
# TLS is terminated by the upstream proxy; Laravel listens on :8080.
#
# The root npm install (playwright, as a runtime "dependencies" entry — see package.json) plus
# `playwright install --with-deps chromium` power the LIVE Expedia TAAP hotel scraper
# (scripts/expedia-taap-search.mjs, driven by App\Services\ExpediaTaapClient). This adds real
# build time/image size, but it's what lets /hoteles show the agent's actual TAAP rates instead
# of any mock/hardcoded data.
FROM php:8.4-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev libsqlite3-dev libpq-dev unzip git curl ca-certificates \
    && docker-php-ext-install zip bcmath pdo pdo_sqlite pdo_pgsql pgsql \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# php.ini: keep $_ENV populated from the environment (variables_order with E) and NEVER print PHP
# notices into the HTTP response (a `php artisan serve` broken-pipe notice would corrupt the SPA HTML).
RUN printf 'display_errors=Off\nlog_errors=On\nvariables_order=EGPCS\n' > /usr/local/etc/php/conf.d/zzz-overcloud.ini

WORKDIR /app
COPY . /app

RUN cp .env.production .env \
    && composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts \
    && php artisan package:discover --ansi \
    && cd gateway && npm ci --omit=dev && cd /app \
    && npm ci --omit=dev \
    && npx playwright install --with-deps chromium \
    && mkdir -p database storage/framework/cache storage/framework/sessions storage/framework/views storage/logs storage/wa storage/app/private bootstrap/cache \
    && touch database/database.sqlite \
    && chmod -R ug+rwX storage bootstrap/cache database \
    && chmod +x docker/start.sh

EXPOSE 8080

CMD ["sh", "docker/start.sh"]
