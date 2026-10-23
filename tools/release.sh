#!/usr/bin/env bash
# Build an upload package for Plesk File Manager (the server has no git).
#   tools/release.sh [--full] [--split <MB>] [<from-ref>]
# <from-ref> defaults to the newest deploy-* tag; with no tag a FULL package is built.
set -euo pipefail

# Paths that must never reach the server (extended regexes on repo-relative paths).
NEVER_UPLOAD=(
  '^\.git(/|attributes$|ignore$)'
  '^docs/'
  '^docker/'
  '^Dockerfile$'
  '^docker-compose\.yml$'
  '^\.env'
  '^tools/'
  '^release/'
  '^nbproject/'
  '^CLAUDE\.md$'
  '^composer\.(json|lock)$'
  '^inc/secrets\.inc\.php$'
)

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

FROM="" SPLIT_MB="" FULL=0
while [ $# -gt 0 ]; do
  case "$1" in
    --split) SPLIT_MB="${2:-}"; [[ "$SPLIT_MB" =~ ^[0-9]+$ ]] && [ "$SPLIT_MB" -gt 0 ] || { echo "--split needs a number of MB" >&2; exit 1; }; shift 2 ;;
    --full) FULL=1; shift ;;
    -h|--help) sed -n '2,4p' "$0"; exit 0 ;;
    -*) echo "Unknown option $1" >&2; exit 1 ;;
    *) FROM="$1"; shift ;;
  esac
done

command -v zip >/dev/null || { echo "zip is required" >&2; exit 1; }
command -v rsync >/dev/null || { echo "rsync is required" >&2; exit 1; }

if ! git diff --quiet HEAD --; then
  echo "Uncommitted changes to tracked files. Commit or stash them first." >&2
  exit 1
fi

if [ "$FULL" = 0 ] && [ -z "$FROM" ]; then
  FROM="$(git tag -l 'deploy-*' --sort=-creatordate | sed -n 1p)"
fi
if [ "$FULL" = 1 ] || [ -z "$FROM" ]; then
  FROM=""; MODE=full
  echo "Building a FULL package (no deploy-* tag given or found)."
else
  git rev-parse --verify -q "$FROM^{commit}" >/dev/null || { echo "Unknown ref: $FROM" >&2; exit 1; }
  MODE=delta
  echo "Building a DELTA package since $FROM."
fi

filter() { # drop never-upload paths
  local re; re="$(IFS='|'; echo "${NEVER_UPLOAD[*]}")"
  grep -Ev "$re" || true
}

# Does the package need vendor/?
NEED_VENDOR=0
if [ -f composer.json ]; then
  if [ "$MODE" = full ] || ! git diff --quiet "$FROM"..HEAD -- composer.lock; then NEED_VENDOR=1; fi
else
  echo "NOTICE: no composer.json yet, skipping vendor/." >&2
fi
if [ "$NEED_VENDOR" = 1 ] && [ -z "${NO_COMPOSER:-}" ]; then
  if [ -x docker/composer.sh ]; then
    docker/composer.sh
  elif [ ! -d vendor ]; then
    echo "vendor/ is missing and docker/composer.sh was not found." >&2; exit 1
  else
    echo "NOTICE: docker/composer.sh not found, using the existing vendor/." >&2
  fi
fi
[ "$NEED_VENDOR" = 1 ] && [ ! -d vendor ] && { echo "vendor/ is missing after composer install." >&2; exit 1; }

SHA="$(git rev-parse --short HEAD)"
NAME="btb-$(date +%Y%m%d)-$SHA"
OUT="$ROOT/release"
STAGE="$(mktemp -d "${TMPDIR:-/tmp}/btb-stage.XXXXXX")"
trap 'rm -rf "$STAGE"' EXIT
mkdir -p "$OUT" "$STAGE/app" "$STAGE/vendor"

if [ "$MODE" = full ]; then
  git ls-files | filter > "$STAGE/files.txt"
  : > "$STAGE/deleted.txt"
else
  git diff --no-renames --name-only --diff-filter=ACMR "$FROM"..HEAD | filter > "$STAGE/files.txt"
  git diff --no-renames --name-only --diff-filter=D "$FROM"..HEAD | filter > "$STAGE/deleted.txt"
fi
FILES=$(wc -l < "$STAGE/files.txt" | tr -d ' ')
DELETED=$(wc -l < "$STAGE/deleted.txt" | tr -d ' ')

# Copy from the working tree (clean, equals HEAD) so modes and dirs survive.
rsync -a --files-from="$STAGE/files.txt" ./ "$STAGE/app/"
cp "$STAGE/deleted.txt" "$STAGE/app/DELETED.txt"
{
  echo "from-ref: ${FROM:-<full>}"
  echo "to-sha: $(git rev-parse HEAD)"
  echo "files: $FILES"
  echo "vendor: $([ "$NEED_VENDOR" = 1 ] && echo included || echo no)"
} > "$STAGE/app/MANIFEST.txt"

VENDOR_NOTE="no"
ZIPS=()
if [ -n "$SPLIT_MB" ]; then
  LIMIT=$((SPLIT_MB * 1024 * 1024))
  if [ "$NEED_VENDOR" = 1 ]; then
    rm -f "$OUT/vendor.zip"
    (cd "$ROOT" && zip -qrX "$OUT/vendor.zip" vendor)
    ZIPS+=("$OUT/vendor.zip"); VENDOR_NOTE="yes (separate vendor.zip)"
    [ "$(wc -c < "$OUT/vendor.zip")" -le "$LIMIT" ] || echo "WARNING: vendor.zip is larger than $SPLIT_MB MB." >&2
  fi
  rm -f "$OUT/$NAME"-part*.zip
  # Group files by uncompressed size (always >= compressed size) into parts <= limit.
  part=1; size=0; : > "$STAGE/part.txt"
  printf '%s\n' DELETED.txt MANIFEST.txt > "$STAGE/part.txt"
  flush() { (cd "$STAGE/app" && zip -qX "$OUT/$NAME-part$part.zip" -@ < "$STAGE/part.txt"); ZIPS+=("$OUT/$NAME-part$part.zip"); }
  while IFS= read -r f; do
    s=$(wc -c < "$STAGE/app/$f" | tr -d ' ')
    [ "$s" -le "$LIMIT" ] || echo "WARNING: $f alone is larger than $SPLIT_MB MB." >&2
    if [ $((size + s)) -gt "$LIMIT" ] && [ "$size" -gt 0 ]; then
      flush; part=$((part + 1)); size=0; : > "$STAGE/part.txt"
    fi
    echo "$f" >> "$STAGE/part.txt"; size=$((size + s))
  done < "$STAGE/files.txt"
  flush
else
  if [ "$NEED_VENDOR" = 1 ]; then
    rsync -a vendor "$STAGE/app/"; VENDOR_NOTE="yes"
  fi
  rm -f "$OUT/$NAME.zip"
  (cd "$STAGE/app" && zip -qrX "$OUT/$NAME.zip" .)
  ZIPS+=("$OUT/$NAME.zip")
fi

echo
echo "Package(s):"; for z in "${ZIPS[@]}"; do echo "  $z ($(du -h "$z" | cut -f1))"; done
echo "Files (excluding vendor/): $FILES"
echo "vendor/ included: $VENDOR_NOTE"
echo "DELETED.txt entries: $DELETED"
cat <<MSG

Next steps (see docs/DEPLOYMENT.md):
  1. Back up in Plesk (files and DB).
  2. File Manager > webroot > Upload the zip(s)$([ -n "$SPLIT_MB" ] && echo " (vendor.zip first, then the parts)").
  3. Extract Files on each zip (overwrite), then Remove the zip.
  4. Remove every path listed in DELETED.txt, then delete DELETED.txt and MANIFEST.txt.
  5. Test the site, then: git tag deploy-$(date +%Y-%m-%d) && git push origin deploy-$(date +%Y-%m-%d)
MSG
