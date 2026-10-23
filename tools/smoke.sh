#!/usr/bin/env bash
# Smoke crawler for the local docker stack (plan 03, decision M8).
#
# Crawls every online page, the 20 newest articles, RSS/sitemap/AMP variants and
# every admin screen, then exercises the admin flows (petition PDF, customer import,
# world-leader import). Fails on HTTP 5xx, PHP error text in a body, new rows in
# `error_logging` or PHP error lines in `docker service logs btb_web`. New lines in
# a-side_logs/php-deprecated.log are listed but do not fail the run.
#
# Usage: tools/smoke.sh [--url-file <file>]
#   --url-file  re-check only the URLs (paths like /contact or /admin/klanten) listed in <file>
# Env:   SMOKE_ADMIN_PASS  password for the local-only `smoke-admin` user (random when empty)
#
# Local stack only: refuses to run unless DB_HOST in .env is `db`. Bash 3.2 compatible.
set -uo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/.."

URL_FILE=""
while [ $# -gt 0 ]; do
  case "$1" in
    --url-file) URL_FILE="${2:-}"; shift 2 ;;
    -h|--help) sed -n '2,13p' "$0"; exit 0 ;;
    *) echo "Unknown option: $1" >&2; exit 2 ;;
  esac
done
if [ -n "$URL_FILE" ] && [ ! -f "$URL_FILE" ]; then
  echo "URL file not found: $URL_FILE" >&2
  exit 2
fi

if [ ! -f .env ]; then
  echo ".env not found" >&2
  exit 2
fi
set -a
. ./.env
set +a

if [ "${DB_HOST:-}" != "db" ]; then
  echo "DB_HOST in .env is not 'db'; smoke.sh only runs against the local docker stack." >&2
  exit 2
fi

BASE="https://localhost"
if [ -n "${WEB_HTTPS_PORT:-}" ] && [ "$WEB_HTTPS_PORT" != "443" ]; then
  BASE="https://localhost:${WEB_HTTPS_PORT}"
fi

DB_CT="$(docker ps -q -f name=btb_db | head -n 1)"
WEB_CT="$(docker ps -q -f name=btb_web | head -n 1)"
if [ -z "$DB_CT" ] || [ -z "$WEB_CT" ]; then
  echo "btb_db or btb_web container is not running (docker/up.sh)." >&2
  exit 2
fi
WEB_ROOT="/var/www/vhosts/buildingthebridge.eu/domains/buildingthebridge.eu/public_html"

WORK="$(mktemp -d "${TMPDIR:-/tmp}/btb-smoke.XXXXXX")"
trap 'rm -rf "$WORK"' EXIT
JAR="$WORK/cookies.txt"
URLS="$WORK/urls.txt"
ROWS="$WORK/rows.txt"
: > "$URLS"
: > "$ROWS"

sql() {
  docker exec -e MYSQL_PWD="$DB_PASSWORD" "$DB_CT" mariadb -N -B -u"$DB_USER" "$DB_DATABASE" -e "$1" < /dev/null
}

START="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
ERR_ID="$(sql 'SELECT COALESCE(MAX(`id`), 0) FROM `error_logging`;')"
ERR_START="$ERR_ID"
# deprecations go to this file (M6), not to the docker log
DEPR_LOG="a-side_logs/php-deprecated.log"
depr_lines() { if [ -f "$DEPR_LOG" ]; then wc -l < "$DEPR_LOG" | tr -d ' '; else echo 0; fi; }
DEPR_START="$(depr_lines)"
DEPR_N="$DEPR_START"
FAIL=0
TOTAL=0
BAD=0

# --- local-only admin user ---------------------------------------------------
SMOKE_PASS="${SMOKE_ADMIN_PASS:-}"
if [ -z "$SMOKE_PASS" ]; then
  SMOKE_PASS="$(LC_ALL=C tr -dc 'A-Za-z0-9' < /dev/urandom | head -c 24)"
fi
# same as hashPasswordForDb(): sha512 hex, first 100 chars
if command -v shasum >/dev/null 2>&1; then
  PASS_HASH="$(printf '%s' "$SMOKE_PASS" | shasum -a 512 | cut -c1-100)"
else
  PASS_HASH="$(printf '%s' "$SMOKE_PASS" | sha512sum | cut -c1-100)"
fi
sql "INSERT INTO \`users\` (\`username\`, \`password\`, \`email\`, \`name\`, \`administrator\`, \`seo\`, \`hasBackendAccess\`, \`systemLanguageId\`, \`created\`)
     SELECT 'smoke-admin', '$PASS_HASH', 'smoke-admin@localhost', 'Smoke admin', 1, 1, 1, MIN(\`systemLanguageId\`), NOW() FROM \`systemLanguages\`
     ON DUPLICATE KEY UPDATE \`password\` = VALUES(\`password\`), \`administrator\` = 1, \`seo\` = 1, \`hasBackendAccess\` = 1;
     DELETE FROM \`usersModules\` WHERE \`userId\` = (SELECT \`userId\` FROM \`users\` WHERE \`username\` = 'smoke-admin');
     INSERT INTO \`usersModules\` (\`userId\`, \`moduleId\`)
     SELECT \`u\`.\`userId\`, \`m\`.\`moduleId\` FROM \`users\` AS \`u\`, \`modules\` AS \`m\` WHERE \`u\`.\`username\` = 'smoke-admin';" || {
  echo "Could not create the smoke-admin user." >&2
  exit 2
}

curl -sk -c "$JAR" -b "$JAR" -o /dev/null "$BASE/admin/login"
curl -sk -c "$JAR" -b "$JAR" -o /dev/null \
  --data-urlencode "username=smoke-admin" --data-urlencode "password=$SMOKE_PASS" \
  --data-urlencode "login_form=send" "$BASE/admin/login"
LOGIN_CODE="$(curl -sk -c "$JAR" -b "$JAR" -o /dev/null -w '%{http_code}' "$BASE/admin/")"
if [ "$LOGIN_CODE" != "200" ]; then
  echo "Admin login failed (GET /admin/ returned $LOGIN_CODE)." >&2
  FAIL=1
fi

# --- URL list ------------------------------------------------------------------
if [ -n "$URL_FILE" ]; then
  grep -v '^[[:space:]]*\(#\|$\)' "$URL_FILE" | sed -e 's#^https\{0,1\}://[^/]*##' > "$URLS"
else
  # online pages (internal paths only)
  sql "SELECT \`urlPath\` FROM \`pages\` WHERE \`online\` = 1 AND \`urlPath\` LIKE '/%' ORDER BY \`pageId\`;" >> "$URLS"

  # 20 newest published articles, plus the 20 newest on the pages index.php routes to the
  # article controllers (the edition pages are not routed, so their article links 404),
  # built like Article::getUrlPath()
  ROUTED="$(sed -n 's#^[[:space:]]*"\([^"]*\)"[[:space:]]*=>[[:space:]]*"/page" \. http_get("amp").*#/\1#p' index.php | awk '{ printf "%s\047%s\047", (NR > 1 ? "," : ""), $0 }')"
  for sWhere in "" "AND \`p\`.\`urlPath\` IN (${ROUTED:-''})"; do
  sql "SELECT \`p\`.\`urlPath\`, \`c\`.\`urlPart\`, \`a\`.\`articleId\`, \`a\`.\`title\`
       FROM \`articles\` AS \`a\`
       JOIN \`articleCategories\` AS \`c\` ON \`c\`.\`articleCategoryId\` = \`a\`.\`articleCategoryId\`
       JOIN \`pages\` AS \`p\` ON \`p\`.\`pageId\` = \`c\`.\`parentPageId\`
       WHERE \`a\`.\`status\` = 3 $sWhere
       ORDER BY \`a\`.\`pubDate\` DESC, \`a\`.\`articleId\` DESC LIMIT 20;"
  done | sort -u -t "$(printf '\t')" -k3,3n > "$WORK/articles.tsv"
  docker exec -i "$WEB_CT" php -r '
    require "'"$WEB_ROOT"'/inc/base_functions.inc.php";
    while (($sLine = fgets(STDIN)) !== false) {
        $a = explode("\t", rtrim($sLine, "\n"), 4);
        if (count($a) < 4) continue;
        echo $a[0] . "/" . $a[1] . "/" . prettyUrlPart(stripcslashes($a[3])) . "/" . $a[2] . "\n";
    }' < "$WORK/articles.tsv" > "$WORK/article-urls.txt"
  cat "$WORK/article-urls.txt" >> "$URLS"

  # RSS of 3 article pages, sitemaps, AMP variants of 3 pages and 3 articles
  sql "SELECT CONCAT('/rss/', \`pageId\`) FROM \`pages\` WHERE \`online\` = 1 AND \`hasArticles\` = 1 ORDER BY \`pageId\` LIMIT 3;" >> "$URLS"
  printf '%s\n' /sitemap.xml /amp/the-great-debate /amp/common-ground /amp/world-leaders >> "$URLS"
  grep -E "^/($(printf '%s' "$ROUTED" | tr -d "'" | tr ',' '|' | sed -e 's#/##g'))/" "$WORK/article-urls.txt" | head -n 3 | sed -e 's#^#/amp#' >> "$URLS"

  # every admin screen from admin/index.php $aControllers, except import-world-leaders:
  # that is a one-off data migration that rewrites articles and world leaders on GET
  sed -n 's#^[[:space:]]*"\([^"]*\)"[[:space:]]*=>[[:space:]]*ADMIN_FOLDER.*#/admin/\1#p' admin/index.php \
    | grep -v '^/admin/import-world-leaders$' >> "$URLS"
fi

# --- crawl ------------------------------------------------------------------------
BODY_RE='Fatal error|Warning:|Deprecated:|Notice:|Error DQF|Er is een fatale fout'

# check one response: url, code, bytes, content-type, body file, expected (optional)
check() {
  local sUrl="$1" sCode="$2" sBytes="$3" sType="$4" sBody="$5" sIssues="" sNewId sHits sNote sModule
  case "$sCode" in
    5*|000) sIssues="http-$sCode" ;;
  esac
  case "$sType" in
    text/*|application/xml*|application/rss*|application/json*|"")
      sHits="$(grep -a -o -E "$BODY_RE" "$sBody" 2>/dev/null | sort -u | tr '\n' ',' | sed -e 's/,$//')"
      [ -n "$sHits" ] && sIssues="${sIssues:+$sIssues;}body:$sHits"
      ;;
  esac
  sNote=""
  case "$sUrl" in
    /admin/login*) ;;
    /admin/*)
      if [ "$sCode" = "403" ]; then
        # index.php answers 403 for modules that are missing or inactive in `modules`: expected
        sModule="${sUrl#/admin/}"
        sModule="${sModule%%[/?]*}"
        if [ "$(sql "SELECT COUNT(*) FROM \`modules\` WHERE \`active\` = 1 AND \`name\` = '$sModule';")" = "0" ]; then
          sNote="(module inactive)"
        else
          sIssues="${sIssues:+$sIssues;}forbidden"
        fi
      fi
      ;;
  esac
  sNewId="$(sql 'SELECT COALESCE(MAX(`id`), 0) FROM `error_logging`;')"
  if [ "$sNewId" != "$ERR_ID" ]; then
    sIssues="${sIssues:+$sIssues;}error_logging:$(sql "SELECT COUNT(*) FROM \`error_logging\` WHERE \`id\` > $ERR_ID;")"
    ERR_ID="$sNewId"
  fi
  sNewId="$(depr_lines)"
  if [ "$sNewId" != "$DEPR_N" ]; then
    sNote="${sNote:+$sNote }(deprecated:$((sNewId - DEPR_N)))"
    DEPR_N="$sNewId"
  fi
  if [ -n "${6:-}" ]; then
    sIssues="${sIssues:+$sIssues;}$6"
  fi
  TOTAL=$((TOTAL + 1))
  if [ -n "$sIssues" ]; then
    BAD=$((BAD + 1))
    FAIL=1
  fi
  printf '%-4s %9s  %-70s %s\n' "$sCode" "$sBytes" "$sUrl" "${sIssues:-${sNote:--}}${sIssues:+${sNote:+ $sNote}}" >> "$ROWS"
}

fetch() {
  local sUrl="$1" sOut
  sOut="$(curl -sk -c "$JAR" -b "$JAR" --max-time 120 -o "$WORK/body" -w '%{http_code} %{size_download} %{content_type}' "$BASE$sUrl")"
  set -- $sOut
  check "$sUrl" "${1:-000}" "${2:-0}" "${3:-}" "$WORK/body"
}

while IFS= read -r sUrl; do
  [ -z "$sUrl" ] && continue
  fetch "$sUrl" < /dev/null
done < "$URLS"

# --- admin flows (full run only) -------------------------------------------------------
if [ -z "$URL_FILE" ]; then
  # petition PDF
  PETITION_ID="$(sql 'SELECT `petitionId` FROM `petitions` ORDER BY `petitionId` DESC LIMIT 1;')"
  if [ -n "$PETITION_ID" ]; then
    sOut="$(curl -sk -c "$JAR" -b "$JAR" --max-time 120 -o "$WORK/petition.pdf" -w '%{http_code} %{size_download} %{content_type}' "$BASE/admin/petitions/export/$PETITION_ID")"
    set -- $sOut
    sExtra=""
    case "${3:-}" in application/pdf*) ;; *) sExtra="not-pdf:${3:-none}" ;; esac
    [ "${2:-0}" -gt 1024 ] 2>/dev/null || sExtra="${sExtra:+$sExtra;}pdf-too-small"
    check "/admin/petitions/export/$PETITION_ID [pdf]" "${1:-000}" "${2:-0}" "${3:-}" "$WORK/petition.pdf" "$sExtra"
  fi

  # imports: snapshot the touched tables, import, restore (never keep imported rows)
  SNAP_TABLES="customers customerGroupRelations worldLeaders worldLeaderAddresses"
  if docker exec -e MYSQL_PWD="$DB_PASSWORD" "$DB_CT" mariadb-dump --single-transaction -u"$DB_USER" "$DB_DATABASE" $SNAP_TABLES > "$WORK/snapshot.sql" 2> "$WORK/dump.err"; then
    cp admin/temp/state-of-israel.xlsx "$WORK/smoke-import.xlsx"

    sOut="$(curl -sk -c "$JAR" -b "$JAR" --max-time 300 -o "$WORK/body" -w '%{http_code} %{size_download} %{content_type}' \
      -F action=import -F "file=@$WORK/smoke-import.xlsx" "$BASE/admin/klanten-import")"
    set -- $sOut
    check "/admin/klanten-import [import]" "${1:-000}" "${2:-0}" "${3:-}" "$WORK/body"

    REGION_ID="$(sql 'SELECT `regionId` FROM `regions` ORDER BY `regionId` LIMIT 1;')"
    sOut="$(curl -sk -c "$JAR" -b "$JAR" --max-time 300 -o "$WORK/body" -w '%{http_code} %{size_download} %{content_type}' \
      -F action=import -F "regionId=$REGION_ID" -F "importFile=@$WORK/smoke-import.xlsx" "$BASE/admin/wereldleiders")"
    set -- $sOut
    check "/admin/wereldleiders [import]" "${1:-000}" "${2:-0}" "${3:-}" "$WORK/body"
    # the import redirects back to the list, which shows the import log
    fetch "/admin/wereldleiders"

    docker exec -i -e MYSQL_PWD="$DB_PASSWORD" "$DB_CT" mariadb -u"$DB_USER" "$DB_DATABASE" < "$WORK/snapshot.sql" || {
      echo "Restoring the import tables failed; run docker/db/reset.sh." >&2
      FAIL=1
    }
    # remove the uploaded copies from admin/temp
    rm -f admin/temp/smoke-import*.xlsx
  else
    echo "Snapshot of import tables failed, imports skipped: $(head -n 1 "$WORK/dump.err")" >&2
    FAIL=1
  fi
fi

# --- report -------------------------------------------------------------------------
printf '%-4s %9s  %-70s %s\n' "code" "bytes" "url" "issues"
cat "$ROWS"

if [ "$ERR_ID" != "$ERR_START" ]; then
  echo
  echo "New error_logging rows:"
  sql "SELECT CONCAT(\`errno\`, ' ', \`file\`, ':', \`line\`, ' ', REPLACE(REPLACE(\`error\`, '<br />', ''), '\n', ' ')) FROM \`error_logging\` WHERE \`id\` > $ERR_START ORDER BY \`id\`;" | sort | uniq -c
fi

if [ "$DEPR_N" != "$DEPR_START" ]; then
  echo
  echo "New $DEPR_LOG lines (reported, not failing):"
  tail -n "+$((DEPR_START + 1))" "$DEPR_LOG" | sed -e 's/^\[[^]]*\] //' -e "s#^$WEB_ROOT/##" | sort | uniq -c | sort -rn
fi

sleep 2
LOG_HITS="$(docker service logs --since "$START" btb_web 2>&1 | grep -E 'PHP (Fatal|Warning|Deprecated|Notice)' || true)"
if [ -n "$LOG_HITS" ]; then
  FAIL=1
  echo
  echo "PHP lines in docker service logs btb_web:"
  printf '%s\n' "$LOG_HITS" | sed -e 's/^[^|]*| //' -e 's/\\nStack trace:.*//' | cut -c1-400 | sort | uniq -c
fi

echo
echo "URLs: $TOTAL  with issues: $BAD  error_logging rows: $(sql "SELECT COUNT(*) FROM \`error_logging\` WHERE \`id\` > $ERR_START;")  log lines: $(printf '%s' "$LOG_HITS" | grep -c . || true)  deprecations: $((DEPR_N - DEPR_START))"
if [ "$FAIL" -eq 0 ]; then
  echo "SMOKE: PASS"
  exit 0
fi
echo "SMOKE: FAIL"
exit 1
