<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8" />
        <title><?= $oPageLayout->sWindowTitle ?> - <?= CLIENT_NAME ?></title>
        <link rel="stylesheet" href="<?= ADMIN_CSS_FOLDER ?>/style.css" />
        <link rel="stylesheet" href="<?= ADMIN_PLUGINS_FOLDER ?>/fancybox/jquery.fancybox.css" />
        <!--[if lte IE 8]>
          <link rel="stylesheet" href="<?= ADMIN_CSS_FOLDER ?>/lteIE8_style.css" />
          <![endif]-->
        <!--[if lte IE 7]>
            <link rel="stylesheet" href="<?= ADMIN_CSS_FOLDER ?>/lteIE7_style.css" />
        <![endif]-->
        <link rel="stylesheet" href="<?= ADMIN_PLUGINS_FOLDER ?>/jquery-ui-1.8.17.custom/jquery-ui-1.8.17.custom.css" />
        <link rel="shortcut icon" href="/favicon.ico" />
        <?
        # extra stylesheet stuff
        foreach ($oPageLayout->getStylesheets() AS $sStylesheet) {
            echo $sStylesheet . "\n";
        }
        ?>
        <!-- make IE 8 and lower html5 compatible  -->
        <!--[if lt IE 9]>
            <script src="<?= ADMIN_JS_FOLDER ?>/html5.min.js"></script>
        <![endif]-->

        <?
        # extra javascript stuff
        foreach ($oPageLayout->getJavascriptsTop() AS $sJavascript) {
            echo $sJavascript . "\n";
        }
        ?>
    </head>
    <body>
        <? include_once ADMIN_TEMPLATES_FOLDER . '/elements/header.inc.php'; ?>
        <div id="header-pusher"></div>
        <div id="container">
            <div id="main-leftColumn">
                <? include_once ADMIN_TEMPLATES_FOLDER . '/elements/leftMenu.inc.php'; ?>
            </div>
            <div id="main-rightColumn">
                <div id="contentContainer" class="main-box">
                    <div class="main-box-header-dark"><?= $oPageLayout->sModuleName ?></div>
                    <div class="main-box-header-light crumblePath"><?= sysTranslations::get('global_youre_here') ?> <?= $oPageLayout->generateAutoCrumblePath() ?></div>
                    <div class="main-box-content cf">
                        <?
                        # include the actual page with changable content
                        include_once $oPageLayout->sPagePath;
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- constructie voor standaard form validatie in een fancybox -->
        <div class="hide">
            <div id="validationErrors">
                <h2><?= sysTranslations::get('global_no_correctly_completed') ?></h2>
                <ul id="errors">
                </ul>
            </div>
            <a class="fancyBoxLink" id="validationErrorsLink" href="#validationErrors">test</a>
        </div>
        <script>var DEBUG = <?= DEBUG ? '1' : '0'; ?>;</script>
        <script src="<?= ADMIN_JS_FOLDER ?>/jquery.min.js"></script>
        <script src="<?= ADMIN_PLUGINS_FOLDER ?>/jquery-ui-1.8.17.custom/jquery-ui-1.8.17.custom.min.js"></script>
        <script src="<?= ADMIN_JS_FOLDER ?>/jquery-touch-punch.min.js"></script>
        <script src="<?= ADMIN_JS_FOLDER ?>/jquery-tablesorter.min.js"></script>
        <script src="<?= ADMIN_JS_FOLDER ?>/jquery-metadata.min.js"></script>
        <script src="<?= ADMIN_JS_FOLDER ?>/jquery-validate.min.js"></script>
        <script src="<?= ADMIN_JS_FOLDER ?>/jquery-tooltip.pack.js"></script>
        <script src="<?= ADMIN_PLUGINS_FOLDER ?>/fancybox/jquery-fancybox.pack.js"></script>
        <!--<script src="<?= ADMIN_PLUGINS_FOLDER ?>/tiny_mce/tiny_mce.js"></script>-->
        <script src="<?= ADMIN_PLUGINS_FOLDER ?>/tinyMCE/tinymce.min.js"></script>
        <?
        $jsTranslations = json_encode(sysTranslations::getTranslations('js'));
        $jsLang = empty($oCurrentUser) ? 'nl' : $oCurrentUser->getLanguage()->abbr;
        ?>
        <script>
            var jsTranslations = <?= $jsTranslations ?>;
        </script>
        <script src="<?= ADMIN_JS_FOLDER ?>/base_functions.js"></script>
        <script>
            // if there is a statusUpdate, show it
            if ('<?= $oPageLayout->sStatusUpdate ?>' != '') {
                showStatusUpdate('<?= $oPageLayout->sStatusUpdate ?>');
            }

            //add fancybox to fancyBoxLink elements
            $(".fancyBoxLink").fancybox();

            //add fancybox to fancyBoxLink elements
            $(".fancyBoxLinkIframe").fancybox({
                width: <?= COMMUNICATION_EMAIL_WIDTH ?>
            });

            //add tooltip to tooltip elements
            $(".tooltip").tooltip({
                delay: 0
            });

            // set region values like daynames etc
            setDatepickerRegionValues();
            setTimepickerRegionValues();

            var region = '<?= $jsLang ?>';
            //add datepicker to datePickerDefault elements
            $(".datePickerDefault").datepicker($.datepicker.regional[region]);
            $(".timePickerDefault").timepicker($.timepicker.regional[region]);

            //autofocus element
            $(".autofocus").focus();

            // init char/wordcounter for windowTitle  fields and metaDescription fields
            $('.charCounterWindowTitle').charWordCounter({
                type: 'chars',
                max: 69,
                min: 8,
                format: '<i><?= sysTranslations::get('global_num_chars') ?> {0}</i>',
                errorClass: '',
                validClass: '',
                counterID: 'windowTitleCounter'
            });
            $('.charCounterMetaDescription').charWordCounter({
                type: 'chars',
                max: 156,
                min: 46,
                format: '<i><?= sysTranslations::get('global_num_chars') ?> {0}</i>',
                errorClass: '',
                validClass: '',
                counterID: 'metaDescriptionCounter'
            });

            var globalFunctions = {}; // global functions object for functions with variable 

            //set some stuff for validation and tablesorter
            setDefaultValidationStuff();
            setTableSorterStuff();
        </script>
        <?
        # extra javascript stuff
        foreach ($oPageLayout->getJavascriptsBottom() AS $sJavascript) {
            echo $sJavascript . "\n";
        }
        ?>
    </body>
</html>