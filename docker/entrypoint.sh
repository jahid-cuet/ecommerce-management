#!/bin/sh
# Prepares the app on container start, then runs the given command (the web server by default).
set -e

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

# Copy the settings from compose.yaml into .env. `php artisan serve` does not pass container
# environment variables on to the PHP server process, so the app must read them from .env.
set_env() {
    if grep -q "^$1=" .env; then
        sed -i "s|^$1=.*|$1=$2|" .env
    else
        echo "$1=$2" >> .env
    fi
}

for key in APP_URL DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD; do
    value=$(printenv "$key" || true)
    if [ -n "$value" ]; then
        set_env "$key" "$value"
    fi
done

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force --no-interaction
fi

echo "Waiting for the database..."
until mysqladmin ping -h"$DB_HOST" -P"${DB_PORT:-3306}" -u"$DB_USERNAME" -p"$DB_PASSWORD" --silent 2>/dev/null; do
    sleep 2
done

php artisan migrate --force --no-interaction

# Seed demo data on the first start only, so later restarts keep changes made in the app.
if [ "$(php artisan tinker --execute='echo App\Models\User::count();' 2>/dev/null | tail -n 1)" = "0" ]; then
    echo "Seeding demo data..."
    php artisan db:seed --force --no-interaction
fi

if [ ! -L public/storage ]; then
    php artisan storage:link --no-interaction
fi

echo "Ready on ${APP_URL}"

exec "$@"
