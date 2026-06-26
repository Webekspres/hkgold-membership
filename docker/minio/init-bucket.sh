#!/bin/sh
set -eu

MINIO_ALIAS="${MINIO_ALIAS:-local}"
MINIO_ENDPOINT="${MINIO_ENDPOINT:-http://minio:9000}"
MINIO_ROOT_USER="${MINIO_ROOT_USER:-hkgold_minio}"
MINIO_ROOT_PASSWORD="${MINIO_ROOT_PASSWORD:-hkgold_minio_secret}"
MINIO_BUCKET="${MINIO_BUCKET:-mala-emas-media}"

echo "Waiting for MinIO at ${MINIO_ENDPOINT}..."
until mc alias set "${MINIO_ALIAS}" "${MINIO_ENDPOINT}" "${MINIO_ROOT_USER}" "${MINIO_ROOT_PASSWORD}" 2>/dev/null; do
    sleep 1
done

mc mb "${MINIO_ALIAS}/${MINIO_BUCKET}" --ignore-existing
mc anonymous set download "${MINIO_ALIAS}/${MINIO_BUCKET}"

echo "Bucket '${MINIO_BUCKET}' is ready with public read policy."
