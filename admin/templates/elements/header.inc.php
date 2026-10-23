<header>
    <div id="loggedinUser">
        <?= $oCurrentUser ? sysTranslations::get('global_logged_as') .' '. $oCurrentUser->name : sysTranslations::get('global_must_login') ?>
    </div>
    <div id="statusUpdate"><noscript><?= $oPageLayout->sStatusUpdate ?></noscript></div>
    <div id="logout">
        <?= $oCurrentUser ? '<a href="?logout=1">'.sysTranslations::get('global_logout').'</a>' : '' ?>
    </div>
</header>