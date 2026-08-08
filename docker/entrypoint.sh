#!/usr/bin/env sh
set -eu

cd /var/www/html

# Ensure writable dirs exist (named volumes can wipe permissions)
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true

if [ -z "${APP_KEY:-}" ]; then
  echo "ERROR: APP_KEY is empty. Generate one with: php artisan key:generate --show"
  exit 1
fi

# Wait for MySQL
if [ -n "${DB_HOST:-}" ]; then
  echo "Waiting for database at ${DB_HOST}:${DB_PORT:-3306}..."
  i=0
  until php -r "new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" 2>/dev/null; do
    i=$((i + 1))
    if [ "$i" -ge 60 ]; then
      echo "Database not ready after 60s"
      exit 1
    fi
    sleep 1
  done
  echo "Database is ready."
fi

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  php artisan migrate --force --no-interaction
  php artisan storage:link 2>/dev/null || true
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan event:cache 2>/dev/null || true
fi

exec "$@"
