#!/bin/sh
set -e

echo "==> Clearing old caches..."
doppler run -- php artisan optimize:clear || true

echo "==> Running Laravel optimizations and migrations via Doppler..."
doppler run -- php artisan config:cache
doppler run -- php artisan route:cache
doppler run -- php artisan migrate --force

echo "==> Starting web server..."
exec /entrypoint supervisord
