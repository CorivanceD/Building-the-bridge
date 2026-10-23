#!/usr/bin/env bash
# Build the web image and deploy the btb stack locally.
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/.."

if ! docker info >/dev/null 2>&1; then
  echo "Docker is not running (docker info failed). Start Docker and retry." >&2
  exit 1
fi

if [ ! -f .env ]; then
  cp .env.example .env
  echo "Created .env from .env.example. Edit it (set DB_PASSWORD and DB_ROOT_PASSWORD) and re-run." >&2
  exit 1
fi

set -a
. ./.env
set +a

for var in DB_PASSWORD DB_ROOT_PASSWORD; do
  if [ -z "${!var:-}" ] || [ "${!var}" = "change-me" ]; then
    echo "$var in .env is empty or still change-me. Set a real value and re-run." >&2
    exit 1
  fi
done

if [ "$(docker info --format '{{.Swarm.LocalNodeState}}')" != "active" ]; then
  docker swarm init
fi

docker build --build-arg PHP_VERSION="${PHP_VERSION:-5.6}" -t btb-web:local .
docker stack deploy -c docker-compose.yml --resolve-image never btb

echo
echo "Stack btb deployed. Give the containers a minute to start."
echo "  Site:    https://localhost  (self-signed cert, accept the browser warning)"
echo "  Admin:   https://localhost/admin"
echo "  Mail:    http://localhost:${MAIL_UI_PORT:-8025}"
echo "  phpMyAdmin: http://localhost:${PMA_PORT:-8081}  (log in with DB_USER/DB_PASSWORD or root)"
echo
echo "Note: a dump in docker/db/init/ is only imported into an empty db_data volume (see docker/db/reset.sh)."
