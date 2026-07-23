#!/usr/bin/env bash
# Setup phpMyAdmin di VPS (host nginx + TLS + basic auth + container).
# Jalankan dari root repo di VPS: bash scripts/setup-phpmyadmin-vps.sh
#
# Env opsional:
#   PMA_BASIC_USER  (default: padli-webekspres)
#   PMA_BASIC_PASS  (wajib diisi jika belum ada htpasswd)

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

PMA_BASIC_USER="${PMA_BASIC_USER:-padli-webekspres}"
CONF_SRC="$ROOT/docker/nginx/phpmyadmin.hkgoldvip.com.conf"
CONF_DST="/etc/nginx/sites-available/phpmyadmin.hkgoldvip.com.conf"
HTPASSWD="/etc/nginx/.htpasswd-phpmyadmin"
WEBROOT="/var/www/certbot"

as_root() {
  if [ "$(id -u)" -eq 0 ]; then
    "$@"
  elif docker info >/dev/null 2>&1; then
    # Docker daemon = root; tulis file host tanpa sudo password
    docker run --rm \
      -v /etc/nginx:/etc/nginx \
      -v /etc/letsencrypt:/etc/letsencrypt \
      -v /var/www:/var/www \
      -v /var/lib/letsencrypt:/var/lib/letsencrypt \
      -v "$ROOT:$ROOT" \
      -w "$ROOT" \
      alpine:3.20 "$@"
  else
    sudo "$@"
  fi
}

reload_nginx() {
  if sudo -n systemctl reload nginx 2>/dev/null; then
    return 0
  fi
  # Fallback: HUP master nginx di host via privileged container
  docker run --rm --privileged --pid=host alpine:3.20 \
    nsenter -t 1 -m -u -i -n -- kill -HUP "$(nsenter -t 1 -m -u -i -n -- pidof nginx | awk '{print $1}')"
}

echo "==> Ensure webroot"
as_root mkdir -p "$WEBROOT"

echo "==> Basic auth ($PMA_BASIC_USER)"
if [ -z "${PMA_BASIC_PASS:-}" ]; then
  echo "ERROR: set PMA_BASIC_PASS sebelum jalan."
  exit 1
fi
# generate htpasswd line with openssl apr1
HASH="$(openssl passwd -apr1 "$PMA_BASIC_PASS")"
LINE="${PMA_BASIC_USER}:${HASH}"
as_root sh -c "printf '%s\n' '$LINE' > '$HTPASSWD' && chmod 644 '$HTPASSWD'"

echo "==> Install nginx site (HTTP-only dulu untuk ACME)"
# Temporary HTTP site without SSL until cert exists
as_root sh -c "cat > '$CONF_DST' <<'NGINX'
server {
    listen 80;
    listen [::]:80;
    server_name phpmyadmin.hkgoldvip.com;

    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }

    location / {
        return 200 'phpmyadmin waiting for TLS';
        add_header Content-Type text/plain;
    }
}
NGINX
ln -sfn '$CONF_DST' /etc/nginx/sites-enabled/phpmyadmin.hkgoldvip.com.conf
"

echo "==> nginx -t + reload"
as_root nginx -t 2>/dev/null || docker run --rm -v /etc/nginx:/etc/nginx:ro nginx:alpine nginx -t -c /etc/nginx/nginx.conf
reload_nginx

echo "==> Issue TLS cert"
docker run --rm \
  -v /etc/letsencrypt:/etc/letsencrypt \
  -v /var/lib/letsencrypt:/var/lib/letsencrypt \
  -v "$WEBROOT:/var/www/certbot" \
  certbot/certbot certonly --webroot -w /var/www/certbot \
  --non-interactive --agree-tos \
  --email admin@hkgoldvip.com \
  -d phpmyadmin.hkgoldvip.com \
  --deploy-hook true

echo "==> Install full HTTPS site"
as_root cp "$CONF_SRC" "$CONF_DST"
as_root ln -sfn "$CONF_DST" /etc/nginx/sites-enabled/phpmyadmin.hkgoldvip.com.conf
as_root nginx -t 2>/dev/null || docker run --rm -v /etc/nginx:/etc/nginx:ro nginx:alpine nginx -t -c /etc/nginx/nginx.conf
reload_nginx

echo "==> Start phpMyAdmin container"
set -a
# shellcheck disable=SC1091
[ -f .env.staging ] && source .env.staging
set +a
docker compose \
  -f docker-compose.staging.yml \
  -f docker-compose.staging.host-nginx.yml \
  -f docker-compose.staging.phpmyadmin.yml \
  --env-file .env.staging \
  up -d phpmyadmin

echo "==> Done"
echo "URL: https://phpmyadmin.hkgoldvip.com"
echo "Nginx basic auth: $PMA_BASIC_USER"
echo "PMA Server field: db  (staging MySQL)"
echo "PMA login: root + password MySQL staging"
