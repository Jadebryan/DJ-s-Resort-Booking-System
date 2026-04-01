#!/bin/bash
set -euo pipefail

echo "Starting deployment..."

php artisan down || true

git pull origin main

composer install --no-dev --optimize-autoloader

npm install
npm run build

php artisan config:cache
php artisan route:cache

php artisan migrate --force

php artisan tenants:migrate:safe --force

php artisan up

echo "Deployment complete!"
