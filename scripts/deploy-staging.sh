#!/usr/bin/env bash

# Script Deployment Staging VPS HK GOLD VIP
# Dipanggil di VPS target

set -e

echo "========================================================="
echo "  HK GOLD VIP — Staging VPS Deployment Script            "
echo "========================================================="

# 1. Periksa variabel DOPPLER_TOKEN
if [ -z "$DOPPLER_TOKEN" ] && [ -f .env.staging ]; then
    echo "==> Loading environment from .env.staging..."
    export $(grep -v '^#' .env.staging | xargs)
fi

if [ -z "$DOPPLER_TOKEN" ]; then
    echo "ERROR: DOPPLER_TOKEN tidak ditemukan!"
    echo "Pastikan DOPPLER_TOKEN ada di environment VPS atau di file .env.staging"
    exit 1
fi

# 2. Pull perubahan terbaru dari git
echo "==> Pulling latest code..."
git pull origin main || git pull origin staging || true

# 3. Build & Spin up container
echo "==> Building & starting Docker containers..."
docker compose -f docker-compose.staging.yml up -d --build --remove-orphans

# 4. Run Prisma DB Push / Migrations
echo "==> Running Prisma DB migrations..."
docker compose -f docker-compose.staging.yml exec -T api-elysia doppler run -- bunx prisma db push

# 5. Cleanup unused images
echo "==> Cleaning up old unused Docker images..."
docker image prune -f

echo "========================================================="
echo "  Deploy Staging Selesai!                                "
echo "  Backoffice: https://hkgoldvip.com                      "
echo "  API:        https://api.hkgoldvip.com                  "
echo "========================================================="
