#!/usr/bin/env bash
set -euo pipefail

# Run on the Hetzner server from the project root:
#   ./deploy.sh

if [ ! -f .env ]; then
  echo "Missing .env — create /opt/cutcost/.env on the server (see DEPLOY.md)."
  exit 1
fi

if ! grep -qE '^APP_KEY=base64:.+' .env; then
  echo "Generating APP_KEY..."
  KEY="base64:$(openssl rand -base64 32 | tr -d '\n')"
  if grep -q '^APP_KEY=' .env; then
    sed "s|^APP_KEY=.*|APP_KEY=${KEY}|" .env > .env.tmp && mv .env.tmp .env
  else
    echo "APP_KEY=${KEY}" >> .env
  fi
  echo "APP_KEY written to .env"
fi

# Export compose variables from .env
set -a
# shellcheck disable=SC1091
source .env
set +a

if [ -z "${APP_DOMAIN:-}" ]; then
  echo "APP_DOMAIN is required in .env (e.g. 203.0.113.10.sslip.io)"
  exit 1
fi

if [ -z "${DB_PASSWORD:-}" ] || [ "${DB_PASSWORD}" = "change-me-strong-password" ]; then
  echo "Set a real DB_PASSWORD in .env before deploying."
  exit 1
fi

echo "Building and starting Cutcost..."
docker compose up -d --build

echo ""
echo "App URL: ${APP_URL:-https://${APP_DOMAIN}}"
echo "Check:   curl -fsS ${APP_URL:-https://${APP_DOMAIN}}/up"
echo ""
echo "Logs:    docker compose logs -f"
