#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
docker run --rm -u "$(id -u):$(id -g)" -v "$PWD":/app -w /app composer:2 install --no-dev --optimize-autoloader --ignore-platform-req=ext-gd "$@"
