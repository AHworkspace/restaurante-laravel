#!/usr/bin/env bash
set -e

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan storage:link || true
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

apache2-foreground
