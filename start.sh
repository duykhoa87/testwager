#!/bin/sh
set -e

php artisan migrate --force
php artisan db:seed
php artisan optimize
php artisan route:optimize
php artisan cache:clear