#!/usr/bin/env bash
# Remove the btb stack and its DB volume so the next up.sh re-imports docker/db/init/*.
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/../.."

echo "Removing stack btb..."
docker stack rm btb

echo "Waiting for stack containers to be removed (up to 90s)..."
for _ in $(seq 1 45); do
  [ -z "$(docker ps -aq --filter label=com.docker.stack.namespace=btb)" ] && break
  sleep 2
done
if [ -n "$(docker ps -aq --filter label=com.docker.stack.namespace=btb)" ]; then
  echo "Containers still present after 90s; aborting." >&2
  exit 1
fi

if ! docker volume inspect btb_db_data >/dev/null 2>&1; then
  echo "Volume btb_db_data does not exist; nothing to remove."
  exit 0
fi

# Stopped task containers can still hold the volume; remove any left.
holders="$(docker ps -aq --filter volume=btb_db_data)"
if [ -n "$holders" ]; then
  echo "Removing containers still using btb_db_data..."
  echo "$holders" | xargs docker rm -f
fi

echo "Removing volume btb_db_data..."
docker volume rm btb_db_data
echo "Done. Run docker/up.sh to start fresh; the dump in docker/db/init/ will be imported."
