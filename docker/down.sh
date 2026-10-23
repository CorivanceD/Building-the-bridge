#!/usr/bin/env bash
# Remove the btb stack (the db_data volume is kept).
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/.."

docker stack rm btb
echo "Stack btb removed. The data volume btb_db_data is kept; use docker/db/reset.sh to delete it."
