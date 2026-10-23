<?php

# check if there is a logged in user
if (http_get("logout")) {
    UserManager::logout(ADMIN_FOLDER . "/");
}
if (!$oCurrentUser && http_get("controller") != "login") {
    $_SESSION['loginReferrer'] = getCurrentUrlPath(true, true);
    http_redirect(ADMIN_FOLDER . "/login");
} else if (http_get("controller") != "login" && isset($_SESSION['loginReferrer'])) {
    unset($_SESSION['loginReferrer']);
}
?>