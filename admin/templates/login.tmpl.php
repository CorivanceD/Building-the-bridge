<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?= $oPageLayout->sWindowTitle ?> - <?= CLIENT_NAME ?></title>
        <link rel="stylesheet" href="<?= ADMIN_CSS_FOLDER ?>/style.css" type="text/css" />
        <link rel="stylesheet" href="<?= ADMIN_PLUGINS_FOLDER ?>/jquery-ui-1.8.17.custom/jquery-ui-1.8.17.custom.css" type="text/css" />
        <link rel="shortcut icon" href="/favicon.ico" />
        <script src="<?= ADMIN_JS_FOLDER ?>/jquery.min.js"></script>
        <script src="<?= ADMIN_PLUGINS_FOLDER ?>/jquery-ui-1.8.17.custom/jquery-ui-1.8.17.custom.min.js"></script>
        <!-- make IE 8 and lower html5 compatible  -->
        <!--[if lt IE 9]>
            <script src="<?= ADMIN_JS_FOLDER ?>/html5.min.js"></script>
        <![endif]-->
    </head>
    <body>
        <?php include_once ADMIN_TEMPLATES_FOLDER . '/elements/header.inc.php'; ?>
        <div id="header-pusher"></div>
        <div class="styled-box" id="login-container">
            <div class="box-header">
                <span><?= sysTranslations::get('global_login') ?> <?= CLIENT_URL ?></span>
            </div>
            <div class="box-content cf">
                <div id="leftColumn">
                    <div id="logo-positioner">
                        <img src="<?= ADMIN_IMAGE_FOLDER ?>/layout/login/client-logo.png" alt="<?= CLIENT_NAME ?>" width="260"/>
                    </div>
                </div>
                <div id="rightColumn">
                    <div id="login-form-container">
                        <form action="" method="POST">
                            <input type="text" class="usernameField" hint="<?= sysTranslations::get('user_username')?>" name="username" id="username" value="" title="<?= sysTranslations::get('user_username')?>" />
                            <input type="password" class="passwordField" hint="<?= sysTranslations::get('user_password')?>" name="password" id="password" value="" title="<?= sysTranslations::get('user_password')?>" />
                            <input type="hidden" value="send" name="login_form" />
                            <input type="submit" value="Login" name="verzendBtn" />
                        </form>
                    </div>
                </div>
            </div>
            <footer>
                &copy; <?= strftime("%Y") ?> <a onclick="target='_blank';" href="http://www.a-side.nl">A-side media</a> || De online specialisten
            </footer>
        </div>
        <script src="<?= ADMIN_JS_FOLDER ?>/base_functions.js"></script>
        <script>
                
            /**
             * reposition login box
             * set to vertical middle
             */
            function repositionLoginBox(){
                var windowHeight = $(window).height();
                var loginBoxHeight = $(".styled-box#login-container").height();
                var headerPusherHeight = $("#header-pusher").height();
                var positionLoginBox = Math.round(((windowHeight-headerPusherHeight)/2)-(loginBoxHeight/2));
                $(".styled-box#login-container").css("margin-top", positionLoginBox+"px");
            }
                
            // position onload
            repositionLoginBox();
                
            // reposition loginbox on window resize
            $(window).resize(function(){
                repositionLoginBox();
            });
            
            // add witHint Plugin to inputs
            $("#password,#username").withHint();
            
            // if there is a statusUpdate, show it
            if('<?= $oPageLayout->sStatusUpdate ?>' != ''){
                showStatusUpdate('<?= $oPageLayout->sStatusUpdate ?>');
            }

        </script>
    </body>
</html>