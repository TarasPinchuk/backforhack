#!/usr/bin/env sh
set -e

cd /var/www/html

if [ ! -d vendor ]; then
  composer install --no-interaction --prefer-dist
fi

if [ ! -f .env ]; then
  if [ -f .env.example ]; then
    cp .env.example .env
  fi
fi

if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
  php artisan key:generate --force || true
fi

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache

php artisan config:clear || true

php artisan migrate --force || true

if [ "${L5_SWAGGER_GENERATE_ALWAYS}" = "true" ]; then
  php artisan l5-swagger:generate || true
fi

php artisan serve --host=0.0.0.0 --port=8000