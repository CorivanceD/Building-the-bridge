# Deploying buildingthebridge.eu

This guide is for the site owner. Everything works with a web browser and the Plesk panel only. You do not need SSH, SFTP or git on the server.

- Plesk: `https://dhm3.savviihq.com:8443`
- Webroot: `/var/www/vhosts/buildingthebridge.eu/domains/buildingthebridge.eu/public_html`
- Docker is local-only (the developer's test setup). Nothing under `docker/` ever goes to the server, and the server does not run Docker.

## TL;DR for a routine change

1. Developer runs `tools/release.sh` and sends you `release/btb-<date>-<sha>.zip`. In Plesk, take a backup first.
2. File Manager, open the webroot, **Upload** the zip, then **Extract Files** (tick overwrite), then **Remove** the zip.
3. **Remove** every path listed in `DELETED.txt`, test the site, then the developer tags the release (`git tag deploy-<date>`).

## 1. Golden rules

- Always back up first (section 3).
- Never upload: `.git`, `docs/`, `docker/`, `Dockerfile`, `docker-compose.yml`, `.env*`, `tools/` or `release/`. The release script already leaves these out; if you ever upload a folder by hand, check it.
- Never overwrite, download or share the server's `inc/secrets.inc.php`. It holds the passwords and exists only on the server. The packages never contain it.
- Do one change at a time and test after each.

## 2. One-time setup

Do this once, **before** the first upload of the new `inc/config.inc.php` (the new code reads its passwords from `inc/secrets.inc.php`; the server still has the old config with the passwords inside it).

1. Open `https://dhm3.savviihq.com:8443` and log in.
2. Click **Websites & Domains**, find `buildingthebridge.eu`, click **File Manager**.
3. Open the folder `domains/buildingthebridge.eu/public_html` (this is the webroot; you should see `index.php`, `admin`, `inc`, `images`). Everything below is relative to it.
4. Open the `inc` folder.
5. Click **+** (New), then **Create File**. Name it `secrets.inc.php` and confirm. It opens in the editor.
6. Paste this, and put the real values between the quotes (copy them from the old `inc/config.inc.php` on the server, and the reCAPTCHA secret from your Google reCAPTCHA account):

```php
<?php
define("DB_PASS", 'REAL-DATABASE-PASSWORD');
define("COMMUNICATION_API_PASSWORD", 'REAL-COMMUNICATION-API-PASSWORD');
define("RECAPTCHA_SECRET", 'REAL-RECAPTCHA-SECRET');
```

7. Click **OK** or **Save**. Keep single quotes; if a password contains a `'` or `\`, put a `\` in front of it.
8. Click the file's row, then **Change Permissions**, and untick Write for Group and Other (644 or 640 is fine).

**Warning:** never download this file, never email it, never commit it to git. If it leaks, change all three passwords.

## 3. Backup

1. Plesk, **Websites & Domains**, `buildingthebridge.eu`, **Backup Manager** (or **Tools & Settings** then **Backup Manager**). Click **Back Up**, choose files and databases, and start it. Wait until it shows as finished.
2. **Databases**, click **phpMyAdmin** next to the site's database, tab **Export**, method **Quick**, format **SQL**, click **Export**. Save the `.sql` file on your PC.
3. In Backup Manager, click the new backup's download icon and keep a copy on your PC too.

## 4. Routine change

1. The developer builds the package with `tools/release.sh`, which writes `release/btb-<date>-<sha>.zip`. It contains only the changed files, plus `DELETED.txt` (files to remove) and `MANIFEST.txt`. Save the zip on your PC.
2. Do the backup (section 3).
3. File Manager, open the webroot, click **Upload**, choose the zip. Wait for 100%.
4. Click the zip's row menu (or tick it and use the toolbar), choose **Extract Files**, choose the webroot as destination and tick **overwrite existing files**. Confirm.
5. Select the zip and click **Remove**.
6. Open `DELETED.txt` in the webroot. For each path listed, browse to it, select it and click **Remove**. Then Remove `DELETED.txt` and `MANIFEST.txt`.
7. Test the site (home page, an article, the admin login).
8. The developer then runs `git tag deploy-<date>` and pushes the tag, so the next package starts from here.

**If the upload is too large for the browser** (the upload stalls or Plesk shows a size error): ask the developer to run `tools/release.sh --split 20` (20 is the maximum MB per file; use a smaller number if it still fails). This writes `vendor.zip` and several `btb-...-partN.zip` files. Upload and Extract Files `vendor.zip` first (only when it is included), then each part in order, removing each zip after extracting. `DELETED.txt` is inside part 1.

## 5. PHP 8.4 cutover runbook

Do this once, in a quiet period.

1. **Preflight.** Running the check while the site is on the old PHP (currently `alt-php53-fpm`, PHP 5.3) cannot verify the PHP 8.4 extensions, so the check runs with the site briefly on 8.4. On your PC, get `tools/server-check.php` from the repo or package and keep it ready to upload.
   1. In **Websites & Domains**, `buildingthebridge.eu`, **PHP Settings**, confirm that **PHP 8.4.x** is offered in the version list.
   2. In File Manager, open the webroot, **Upload** `server-check.php`.
   3. Briefly switch the domain to **PHP 8.4.x** with handler **FPM application served by Apache** (or nginx), click **OK**, then open `https://buildingthebridge.eu/server-check.php`. **Warning:** the old site shows errors for these 1–2 minutes while PHP is switched.
   4. Look for MISSING rows. If mysqli, gd (JPEG/PNG/FreeType), mbstring, zip, xml/dom/xmlwriter, fileinfo, iconv or curl is MISSING, stop and ask the hosting provider to enable it before going on.
   5. Switch PHP back to the previous version right away (**PHP Settings**, the old `alt-php53-fpm` (PHP 5.3) version, **OK**) and confirm the home page works again.
   6. **Remove** `server-check.php` from the webroot.

   The script runs on both the old PHP 5.3 and 8.4, so it is harmless to open on either; only the 8.4 run verifies the extensions.
2. **Maintenance window.** Tell users the site will be briefly unavailable.
3. **Backup** (section 3). Download it.
4. **Full package.** Upload and Extract Files the full zip (including `vendor/`; use `--split` if needed, section 4). Make sure `inc/secrets.inc.php` exists (section 2). Between this upload and the PHP switch in step 6 the site may show errors, which is why this happens in the maintenance window; keep steps 4–6 close together.
5. **Remove the three old library folders** in File Manager: `libs/mpdf571`, `libs/PHPExcel`, `inc/PHPExcel`.
6. **Switch PHP.** **Websites & Domains**, `buildingthebridge.eu`, **PHP Settings**. Choose **PHP 8.4.x** and handler **FPM application served by Apache** (or nginx), click **OK**.
7. **Scheduled Tasks.** **Websites & Domains**, then **Scheduled Tasks** (or type "Scheduled" in the Plesk search box). There are 4 tasks of type **Run a PHP script**, one per script in `admin/cronjobs/`. Before changing anything, note each task's Script path and schedule (for rollback). On the old setup every Script path started with `/opt/alt/php53/usr/bin/php `. For each task:
   1. Click the task.
   2. **Use PHP version**: choose **8.4.27**.
   3. **Script path**: remove the old PHP program and the space after it (`/opt/alt/php53/usr/bin/php `), so the field holds only the script, for example `/var/www/vhosts/buildingthebridge.eu/domains/buildingthebridge.eu/public_html/admin/cronjobs/updateRssFeeds.cron.php`. Or use the folder button to pick the file.
   4. Keep the schedule, then click **OK**.

| Script | Schedule | Script path after the change |
|---|---|---|
| updateRssFeeds | every 15 minutes | `<webroot>/admin/cronjobs/updateRssFeeds.cron.php` |
| sendBulkMail | every minute | `<webroot>/admin/cronjobs/sendBulkMail.cron.php` |
| checkBounces | daily 05:45 | `<webroot>/admin/cronjobs/checkBounces.cron.php` |
| sendCommunicationLogs | monthly (1st, 00:00) | `<webroot>/admin/cronjobs/sendCommunicationLogs.cron.php` |

`<webroot>` is `/var/www/vhosts/buildingthebridge.eu/domains/buildingthebridge.eu/public_html`. The list must no longer show `php53` anywhere. Test with **Run Now** on **updateRssFeeds** only (it just fetches news feeds); never use Run Now on checkBounces or sendBulkMail, which contact the real email service. Cron tasks run on the server directly, so they work even while the website itself is blocked or offline.

8. **Verification checklist.**
   - [ ] Home page loads
   - [ ] An article page loads, with images
   - [ ] Contact form sends and the mail arrives
   - [ ] Admin login works
   - [ ] A petition PDF downloads and opens
   - [ ] A customer import (spreadsheet) works
   - [ ] A test mail was received
   - [ ] Plesk error log shows no new PHP errors (see below)

   **Reading the logs:** **Websites & Domains**, `buildingthebridge.eu`, **Logs**. Open **error_log** (PHP and Apache errors) and look at the newest lines; **access_log** shows requests and status codes. Use **Refresh** after reproducing a problem.

## 6. Rollback

About 10 minutes, browser only. Use it if the checklist fails and the cause is not quickly fixable.

1. **Websites & Domains**, `buildingthebridge.eu`, **PHP Settings**, choose the old **alt-php53-fpm** (PHP 5.3) version, which the server ran before the cutover, click **OK**.
2. **Websites & Domains**, **Scheduled Tasks**: in each task, set **Use PHP version** back to the old version and restore the Script path you noted in section 5, step 7.
3. **Backup Manager**, pick the backup taken before the change, click **Restore**, choose **Files** only (the site files), start it. Wait for it to finish.
4. Restore the **database** only if it changed in between (new orders, imports or admin edits that should be undone): tick **Databases** as well, or use phpMyAdmin, **Import**, with the `.sql` you exported in section 3. If nobody used the admin, skip this.
5. Re-check `inc/secrets.inc.php`: if the restore brought back the old files, the old config holds the passwords itself and the file is simply unused.
6. Test the home page and admin login. Tell the developer what failed and send the error log lines.

## 7. Troubleshooting

- **500 error or white page:** open **Logs**, **error_log** (section 5) and read the newest lines. Common causes: `inc/secrets.inc.php` missing or with a typo, `vendor/` not uploaded, PHP version not switched, a file only half extracted (extract again with overwrite).
- **Images or uploads fail, "permission denied":** File Manager, tick `images`, `files` or `a-side_logs`, click **Change Permissions**, tick Read, Write (and Execute for folders) for the owner and group, and tick **Replace permissions for all subdirectories and files**.
- **Upload size limit hit:** use `--split` (section 4), or in Plesk **PHP Settings** raise `upload_max_filesize` and `post_max_size`.
- **Extract stops or times out:** use `--split` with a smaller number.
- **Changes do not show:** hard-refresh the browser (Ctrl+F5) and check that the zip was extracted in the webroot, not a subfolder.

## Appendix A: Upload with FileZilla (FTP, optional)

Only if you prefer it to the browser. Always use explicit TLS.

1. In Plesk, **Websites & Domains**, **FTP Access**: note the username, set a password if needed, and note the host.
2. In FileZilla, **File**, **Site Manager**, **New site**. Protocol **FTP**, host from step 1, port 21, encryption **Require explicit FTP over TLS**, logon type **Normal**, enter the user and password. Connect and accept the certificate.
3. On the right, go to `domains/buildingthebridge.eu/public_html` (or `httpdocs`, depending on the FTP user's home folder).
4. Extract the release zip on your PC into an empty folder, and drag its contents into the webroot, choosing **Overwrite**. Do not upload `inc/secrets.inc.php`.
5. Delete the paths in `DELETED.txt` on the right-hand side, then test.
