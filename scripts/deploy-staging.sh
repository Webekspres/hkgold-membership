#!/usr/bin/env bash

# Script Deployment Staging VPS HK GOLD VIP
# Dipanggil di VPS target

set -e

echo "========================================================="
echo "  HK GOLD VIP — Staging VPS Deployment Script            "
echo "========================================================="

# 1. Periksa token Doppler (API dan/atau backoffice)
if [ -f .env.staging ]; then
    echo "==> Loading environment from .env.staging..."
    set -a
    # shellcheck disable=SC1091
    source .env.staging
    set +a
fi

if [ -z "${DOPPLER_TOKEN_API:-${DOPPLER_TOKEN:-}}" ] || [ -z "${DOPPLER_TOKEN_BACKOFFICE:-${DOPPLER_TOKEN:-}}" ]; then
    echo "ERROR: DOPPLER_TOKEN_API / DOPPLER_TOKEN_BACKOFFICE (atau DOPPLER_TOKEN) tidak ditemukan!"
    echo "Pastikan token ada di environment VPS atau di file .env.staging"
    exit 1
fi

# 2. Pull perubahan terbaru dari git
echo "==> Pulling latest code..."
git pull origin main || git pull origin staging || true

# 3. Build & Spin up container
# Jika host nginx sudah pegang 80/443, pakai override host-nginx (expose :3000/:8000)
COMPOSE_FILES=(-f docker-compose.staging.yml)
if [ -f docker-compose.staging.host-nginx.yml ] && ss -tln | grep -qE ':80|:443'; then
    echo "==> Host nginx terdeteksi — pakai docker-compose.staging.host-nginx.yml"
    COMPOSE_FILES+=(-f docker-compose.staging.host-nginx.yml)
fi

echo "==> Building & starting Docker containers..."
docker compose "${COMPOSE_FILES[@]}" --env-file .env.staging up -d --build --remove-orphans

# 4. Prisma sync (opsional — schema utama sudah dari Laravel migrate di entrypoint)
echo "==> Sync Prisma schema (best-effort)..."
if ! docker compose "${COMPOSE_FILES[@]}" --env-file .env.staging exec -T api-elysia \
  doppler run -- bunx prisma db push; then
  echo "WARN: prisma db push gagal/butuh --accept-data-loss."
  echo "      Schema biasanya sudah ada dari php artisan migrate — lanjut."
fi

# 5. Cleanup unused images
echo "==> Cleaning up old unused Docker images..."
docker image prune -f

echo "========================================================="
echo "  Deploy Staging Selesai!                                "
echo "  Backoffice: https://staging.hkgoldvip.com              "
echo "  API:        https://api.staging.hkgoldvip.com          "
echo "========================================================="
