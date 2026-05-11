#!/bin/sh
set -e
cd /var/www/html

ROLE="${CONTAINER_ROLE:-web}"

mkdir -p storage/framework/sessions \
  storage/framework/views \
  storage/framework/cache/data \
  storage/logs \
  bootstrap/cache

if [ "$ROLE" = "web" ]; then
  php artisan migrate --force
  php artisan db:seed --class=Database\\Seeders\\ContractTemplateSeeder --force 2>/dev/null || true
  php artisan storage:link --force 2>/dev/null || true
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
