# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

The production webroot (`public_html`) of **buildingthebridge.eu**, a legacy (2015–2017) custom PHP CMS built by the Dutch agency A-side Media. It is a plain-PHP MVC-ish app on Apache + MySQL. It has no framework, no Composer, no npm, no build step, no test suite and no git history. The repo is a copy of the live server, including ~750MB of uploaded media in `images/` and `files/`.

## Running / tooling

- Local dev runs in Docker (a single-node swarm stack `btb`): `docker/up.sh` builds the image and deploys, `docker/down.sh` removes the stack (the DB volume is kept). Both work from any cwd.
- First run: `docker/up.sh` copies `.env.example` to `.env` and stops. Set `DB_PASSWORD` and `DB_ROOT_PASSWORD` (not empty, not `change-me`), plus the DB name/user matching your dump, then re-run. `.env` is git-ignored.
- The MySQL schema is **not** in the repo. Export the production DB and drop the dump (`*.sql` or `*.sql.gz`) into `docker/db/init/` (git-ignored). MariaDB imports it only on the first start of an empty volume; to re-import, run `docker/db/reset.sh` (removes the stack and the `btb_db_data` volume), then `docker/up.sh`.
- URLs: `https://localhost`, `https://localhost/admin`, Mailpit at `http://localhost:8025`, phpMyAdmin at `http://localhost:8081`. The cert is self-signed, so accept the browser warning.
- Keep the published ports at 80/443: the root `.htaccess` forces HTTPS and the redirect keeps host:port, so other ports break it.
- PHP is 5.6 in Docker, matching production, with `short_open_tag` on (admin controllers and `admin/index.php` open with `<?`). Lint with `docker run --rm -v "$PWD":/app -w /app php:5.6-cli php -d short_open_tag=On -l <file>`. There are no build or test commands.
- DB settings come from env vars (`DB_HOST`, `DB_USER`, `DB_DATABASE`, ...) read in `inc/config.inc.php`, with production values as fallbacks. The connection is made in `classes/DBConnections.class.php`.
- Several entry points hardcode the production path `/var/www/vhosts/buildingthebridge.eu/domains/buildingthebridge.eu/public_html` as `DOCUMENT_ROOT`. These are `admin/index.php`, the `admin/cronjobs/*.cron.php` scripts and the `compress-images*.fix.php` scripts. The Docker container mounts the repo at that same path, so they work unchanged there; anywhere else, change the path first. The front-end `index.php` uses `dirname(__FILE__)`.
- `compress-images.fix.php` and `compress-images-layout.fix.php` are one-off maintenance scripts with `$bDoItNow = true`. They delete DB rows and files, so never run them casually.

## Front-end and static assets

- Templates load the **`.min.js` / `.min.css` files only**. The un-minified sources (`js/default.js`, `css/default/style.css`, etc.) sit next to them, but nothing regenerates the minified files. If you edit a source file, update its `.min` counterpart too, or edit the `.min` file directly.
- `templates/default/default.tmpl.php` inlines the main CSS through `file_get_contents(...)` into a `<style>` block. Only `print.css` and the JS files are loaded through tags.
- AMP variants live alongside the normal files. They use an `-AMP` / `_AMP` suffix: `page-AMP.cont.php`, `default_AMP.tmpl.php`, `style-AMP.css`, `pages/default/pages/AMP/` and `ampify()` in `inc/base_functions.inc.php`.

## Request flow (front end)

1. `.htaccess` forces HTTPS and the non-www host. It also blocks direct access to `classes|controllers|pages|templates|inc`. It rewrites `/a/b/c/...` to `index.php?controller=a&param1=b&param2=c...&lastparam=...`, and `/amp/...` does the same with `amp=-AMP` added.
2. `index.php` loads `inc/config.inc.php`. That file defines all the path constants, includes `inc/base_functions.inc.php`, registers the class autoloader and starts the session.
3. The `controller` value is looked up in the hardcoded `$aControllers` map in `index.php`. If it isn't there, the URL path is looked up in the DB `pages` table (`PageManager::getControllerPathByUrlPath`). After that comes the page-redirects table (301), then a 404.
4. The controller is included from `controllers/default/<file>.cont.php`, because `PATH_PREFIX` is `/default`. Controllers build a `PageLayout` object (title, meta, OG tags) and set `$oPageLayout->sPagePath` to a view in `pages/default/...`. They finish by including `templates/default/default.tmpl.php`, which includes `sPagePath` in the body.

CMS pages therefore get their URL and controller from the database. Adding a new route usually means a DB row in `pages`, or a new entry in the `$aControllers` map.

## Admin (`/admin`)

- `admin/index.php` defines `ACCESS`, which every admin controller checks (`if (!defined('ACCESS')) die;`). It then runs `admin/inc/loginControl.inc.php` and maps the controller name to a file in its own `$aControllers` array. Many route keys are Dutch, for example `paginas`, `klanten`, `instellingen` and `wereldleiders`.
- Access to anything not in `$aExceptions` requires both `$oCurrentUser->hasRightsFor($module)` and `ModuleManager::isActive($module)`. A new admin module therefore needs a map entry plus a row in the modules/rights tables.
- The layout parallels the front end: `admin/controllers`, `admin/pages` (views), `admin/templates/default.tmpl.php`.
- Cron jobs live in `admin/cronjobs/`: RSS feed updates, bulk mail, bounce checks and communication logs. They use `CronManager::isLocked`/`setCronLock` with lock files in `cronlocks/`.

## Classes / data layer (`classes/`)

- The autoloader maps `ClassName` to `classes/ClassName.class.php`. Many class files also `require_once` their related classes explicitly.
- Classes come in pairs: a **model** `Foo` that extends the abstract `Model` (public props equal DB columns; `_load()` fills them and strip-tags by default; subclasses implement `validate()`), and a **static manager** `FooManager` that does all the SQL. Some modules also have a `FooManagerHTML`.
- SQL is built by string concatenation, escaped with the `db_str()`, `db_int()`, `db_deci()` and `db_date()` helpers from `inc/base_functions.inc.php`. It runs through `DBConnections::get()->query($sql, QRY_UNIQUE_OBJECT|QRY_OBJECT|..., 'ClassName')`, where `DBConnection` extends `mysqli`. Keep using these escaping helpers in all new queries.
- Request access goes through the helpers `http_get()`, `http_post()` and `http_session()` rather than raw superglobals. Redirects use `http_redirect()` and errors use `showHttpError(404)`.
- UI strings come from `sysTranslations::get('key')` (DB-backed) and app config from `Settings::get('key')`. Email bodies are DB templates (`TemplateManager::getTemplateByName(...)->replaceVariables(...)`) sent with `MailManager::sendMail`.
- Naming convention: Hungarian-style prefixes, such as `$sString`, `$iInt`, `$aArray`, `$oObject`, `$bBool`, `$fFloat` and `$mMixed`. Follow it.

## Domain

The site is an Israel/Palestine dialogue platform. Domain modules include articles and comments, RSS news aggregation (`news-israel`, `news-palestine`), petitions and signatures, world leaders with letters to them, customers (front-end accounts and newsletter, with `afmelden` = unsubscribe), chat, and communication items (bulk email/SMS through the external A-side API and MessageBird).

## Debug and errors

`DEBUG` is on only when `isAside()` matches a hardcoded agency IP. `error_handler` and `shutdown_handler` email errors to `DEFAULT_ERROR_EMAIL` and log to `a-side_logs/` (via `Debug::logError`).

Bundled third-party libraries: `libs/PHPExcel`, `libs/mpdf571`, `inc/FeedWriter`, `inc/recaptcha`, and the jQuery plugins under `plugins/`.
