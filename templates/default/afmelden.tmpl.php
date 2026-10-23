<!DOCTYPE html>
<!--[if lt IE 7]>  <html class="no-js ie ie6 lte9 lte8 lte7" lang="nl"> <![endif]-->
<!--[if IE 7]>     <html class="no-js ie ie7 lte9 lte8 lte7" lang="nl"> <![endif]-->
<!--[if IE 8]>     <html class="no-js ie ie8 lte9 lte8" lang="nl"> <![endif]-->
<!--[if IE 9]>     <html class="no-js ie ie9 lte9" lang="nl"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html class="no-js" lang="nl"> <!--<![endif]-->
    <head>

        <meta charset="utf-8">
        <title><?= CLIENT_URL ?></title>
        <meta name="description" content="">

        <style>
            <?= file_get_contents(DOCUMENT_ROOT . CSS_FOLDER . '/style.min.css'); ?>
            <?= file_get_contents(DOCUMENT_ROOT . CSS_FOLDER . '/style_' . ltrim(PATH_PREFIX, '/') . '.min.css'); ?>
            <?= file_get_contents(DOCUMENT_ROOT . CSS_FOLDER . '/style_responsive.min.css'); ?>
            <?= file_get_contents(DOCUMENT_ROOT . CSS_FOLDER . '/fancybox/jquery.fancybox.min.css'); ?>
        </style>
        
        <link rel="stylesheet" href="<?= CSS_FOLDER ?>/print.css" media="print">

        <meta name="author" content="<?= CLIENT_NAME ?>" />

        <link rel="icon" href="/favicon.png">
        <link rel="apple-touch-icon" sizes="144x144" href="<?= IMAGE_FOLDER ?>/apple-touch-icons/apple-touch-icon-144x144-precomposed.png">
        <link rel="apple-touch-icon" sizes="114x114" href="<?= IMAGE_FOLDER ?>/apple-touch-icons/apple-touch-icon-114x114-precomposed.png">
        <link rel="apple-touch-icon" sizes="72x72" href="<?= IMAGE_FOLDER ?>/apple-touch-icons/apple-touch-icon-72x72-precomposed.png">
        <link rel="apple-touch-icon" href="<?= IMAGE_FOLDER ?>/apple-touch-icons/apple-touch-icon-precomposed.png">

        <meta name="application-name" content="<?= CLIENT_NAME ?>"/>
        <meta name="msapplication-TileColor" content="#ffffff"/>
        <meta name="msapplication-square70x70logo" content="<?= IMAGE_FOLDER ?>/window-8-tiles/tiny.png"/>
        <meta name="msapplication-square150x150logo" content="<?= IMAGE_FOLDER ?>/window-8-tiles/square.png"/>
        <meta name="msapplication-wide310x150logo" content="<?= IMAGE_FOLDER ?>/window-8-tiles/wide.png"/>
        <meta name="msapplication-square310x310logo" content="<?= IMAGE_FOLDER ?>/window-8-tiles/large.png"/>

        <!-- required js -->
        <script>
            <?= file_get_contents(DOCUMENT_ROOT . JS_FOLDER . '/modernizr-2.5.3.min.js'); ?>
        </script>
    </head>
    <body>
        <div style="width: 500px; margin: 50px auto; text-align: center">
            <?php
            switch($iMessageNumber) {
                case 1:
                    echo 'You are unsubscribed';
                    break;
                case 2:
                    echo 'You will not receive e-mails from us';
                    break;
                case 3:
                    echo 'Error while unsubscribing. Please contact us';
                default:
                    echo 'You are not subscribed. You will not be contacted';
            }
            ?>
            <p>&nbsp;</p>
        </div>
    </body>
</html>