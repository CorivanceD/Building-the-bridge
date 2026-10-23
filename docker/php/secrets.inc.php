<?php
# Docker-only: mounted over inc/secrets.inc.php inside the web container. Holds no real secrets.
define("DB_PASS", getenv('DB_PASSWORD'));
define("COMMUNICATION_API_PASSWORD", '');
define("RECAPTCHA_SECRET", '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');
