<!DOCTYPE html>
<!--[if lt IE 7]>  <html class="no-js ie ie6 lte9 lte8 lte7" lang="en"> <![endif]-->
<!--[if IE 7]>     <html class="no-js ie ie7 lte9 lte8 lte7" lang="en"> <![endif]-->
<!--[if IE 8]>     <html class="no-js ie ie8 lte9 lte8" lang="en"> <![endif]-->
<!--[if IE 9]>     <html class="no-js ie ie9 lte9" lang="en"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!-->
<html class="no-js" lang="en"> <!--<![endif]-->

<head>

    <meta charset="utf-8">

    <? #Uncomment this viewport metatag to make website responsive 
    ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $oPageLayout->sWindowTitle ?> | <?= CLIENT_NAME ?></title>
    <?
    # add meta tags
    if (!empty($oPageLayout->sMetaDescription))
        echo '<meta name="description" content="' . $oPageLayout->sMetaDescription . '">' . PHP_EOL;
    if (!empty($oPageLayout->sMetaKeywords))
        echo '<meta name="keywords" content="' . $oPageLayout->sMetaKeywords . '">' . PHP_EOL;
    # add social tags
    if (!empty($oPageLayout->sOGTitle))
        echo '<meta property="og:title" content="' . $oPageLayout->sOGTitle . '"/>' . PHP_EOL;
    if (!empty($oPageLayout->sOGType))
        echo '<meta property="og:type" content="' . $oPageLayout->sOGType . '"/>' . PHP_EOL;
    if (!empty($oPageLayout->sOGUrl))
        echo '<meta property="og:url" content="' . $oPageLayout->sOGUrl . '"/>' . PHP_EOL;
    if (!empty($oPageLayout->sOGDescription))
        echo '<meta property="og:description" content="' . $oPageLayout->sOGDescription . '"/>' . PHP_EOL;
    if (!empty($oPageLayout->sOGImage))
        echo '<meta property="og:image" content="' . $oPageLayout->sOGImage . '"/>' . PHP_EOL;
    # add AMPHTML 'canonical'
    if (http_get("controller") == "news-israel" || http_get("controller") == "news-palestine" || http_get("controller") == "global-perspective" || http_get("controller") == "world-leaders" || http_get("controller") == "common-ground" || http_get("controller") == "the-great-debate") {
        echo '<link rel="amphtml" href="' . str_replace("buildingthebridge.eu/", "buildingthebridge.eu/amp/", getCurrentUrl()) . '" />' . PHP_EOL;
    }
    # add canonical
    if (!empty($oPageLayout->sCanonical) && $oPageLayout->sCanonical != getCurrentUrl()) {
        echo '<link rel="canonical" href="' . $oPageLayout->sCanonical . '">' . PHP_EOL;
    }

    # add prev/next canonical
    if (!empty($oPageLayout->sRelPrev))
        echo '<link rel="prev" href="' . $oPageLayout->sRelPrev . '" />' . PHP_EOL;
    if (!empty($oPageLayout->sRelNext))
        echo '<link rel="next" href="' . $oPageLayout->sRelNext . '" />' . PHP_EOL;
    ?>

    <style>
        <?= file_get_contents(DOCUMENT_ROOT . CSS_FOLDER . '/style.min.css'); ?><?= file_get_contents(DOCUMENT_ROOT . CSS_FOLDER . '/style_' . ltrim(PATH_PREFIX, '/') . '.min.css'); ?><?= file_get_contents(DOCUMENT_ROOT . CSS_FOLDER . '/style_responsive.min.css'); ?><?= file_get_contents(DOCUMENT_ROOT . PLUGINS_FOLDER . '/fancybox/jquery.fancybox.min.css'); ?><?= file_get_contents(DOCUMENT_ROOT . PLUGINS_FOLDER . '/progressbar/skins/default/progressbar.min.css'); ?><?= file_get_contents(DOCUMENT_ROOT . PLUGINS_FOLDER . '/progressbar/skins/jquery-ui-like/progressbar.min.css'); ?>
    </style>

    <link rel="stylesheet" href="<?= CSS_FOLDER ?>/print.css" media="print">

    <!--[if IE 8]>
        <style>
        #banner, footer { 
            min-width: 1016px;        
        }
        </style>
        <![endif]-->

    <meta name="author" content="<?= CLIENT_NAME ?>" />

    <link rel="icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="144x144" href="<?= IMAGE_FOLDER ?>/apple-touch-icons/apple-touch-icon-144x144-precomposed.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?= IMAGE_FOLDER ?>/apple-touch-icons/apple-touch-icon-114x114-precomposed.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?= IMAGE_FOLDER ?>/apple-touch-icons/apple-touch-icon-72x72-precomposed.png">
    <link rel="apple-touch-icon" href="<?= IMAGE_FOLDER ?>/apple-touch-icons/apple-touch-icon-precomposed.png">

    <meta name="application-name" content="<?= CLIENT_NAME ?>" />
    <meta name="msapplication-TileColor" content="#ffffff" />
    <meta name="msapplication-square70x70logo" content="<?= IMAGE_FOLDER ?>/window-8-tiles/tiny.png" />
    <meta name="msapplication-square150x150logo" content="<?= IMAGE_FOLDER ?>/window-8-tiles/square.png" />
    <meta name="msapplication-wide310x150logo" content="<?= IMAGE_FOLDER ?>/window-8-tiles/wide.png" />
    <meta name="msapplication-square310x310logo" content="<?= IMAGE_FOLDER ?>/window-8-tiles/large.png" />



    <?
    # echo extra stylesheet stuff
    foreach ($oPageLayout->getStylesheets() as $sStylesheet) {
        echo $sStylesheet . "\n";
    }

    if (empty($_SESSION['bDontShowBrowserWarning'])) {
    ?>
        <!--[if lte IE 7]>
                <script>window.location = '/browserwarning';</script>
            <![endif]-->
    <?
    }

    /* echo extra javascript stuff */
    foreach ($oPageLayout->getJavascriptsTop() as $sJavascript) {
        echo $sJavascript . "\n";
    }
    ?>

    <!-- required js -->
    <script>
        <?= file_get_contents(DOCUMENT_ROOT . JS_FOLDER . '/modernizr-2.5.3.min.js'); ?>
    </script>
    <script>
        (function(i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function() {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
            a = s.createElement(o),
                m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

        ga('create', 'UA-58578057-1', 'auto');
        ga('require', 'displayfeatures');
        ga('require', 'linkid', 'linkid.js');
        ga('send', 'pageview');
    </script>

</head>

<body>
    <div id="fb-root"></div>
    <script>
        fbAppId = -1;
        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id))
                return;
            js = d.createElement(s);
            js.id = id;
            js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.4";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>
    <div id="banner"></div>
    <div class="default-container-width" style="min-height:100%">
        <header>
            <div id="today" class="hide-on-mobile"><?
                                                    $oCurrentDate = new Date();
                                                    echo $oCurrentDate->format('%A, %d %B, %Y');
                                                    ?></div>
            <!--                <div id="languages">
                                    <ul>
                                        <li><a class="eng"></a></li>
                                        <li><a class="ned"></a></li>
                                        <li><a class="pal"></a></li>
                                        <li><a class="isr"></a></li>                        
                                    </ul>
                                </div>-->
            <div id="upperMenu" class="no-print">
                <ul>
                    <li><a href="/account/newsletter"><img style="width:22px;height:14px;padding-bottom:2px;padding-right:2px" src="<?= IMAGE_FOLDER . '/layout/newsletter.png' ?>" width="28" height="18" alt="Subscribe to our newsletter">NEWSLETTER</a></li>
                    <li>·</li>
                    <? if (empty($oCurrentCustomer)) { ?>
                        <li><a class="loginLink" href="/">LOGIN</a></li>
                        <li>·</li>
                        <li><a href="/account">SIGN UP</a></li>
                        <li>·</li>
                    <? } else { ?>
                        <li><a href="/account">MY ACCOUNT</a></li>
                        <li>·</li>
                        <li><a href="/account/logout">LOGOUT</a></li>
                        <li>·</li>
                    <? } ?>
                    <li><a class="donateLink" href="/donate">DONATE</a></li>
                    <li>·</li>
                    <li><a href="/petitions">PETITIONS</a></li>
                    <li>·</li>
                    <li><a href="/links">LINKS</a></li>
                    <li>·</li>
                    <li><a href="/about-us">ABOUT US</a></li>
                    <li>·</li>
                    <li><a href="/contact">CONTACT US</a></li>
                    <li>·</li>
                    <li><a href="/action-plan">ACTION PLAN</a></li>
                    <li>·</li>
                    <li><a href="/world-leaders" style="color:#F90"><b>ARCHIVES</b></a></li>
                </ul>

            </div>
            <div id="headerTitle">
                <ul class="hide">
                    <li><a class="linkedin" href="http://www.linkedin.com/company/building-the-bridge"></a></li>
                    <li><a class="facebook" href="https://www.facebook.com/pages/Building-the-Bridge/664545083667211"></a></li>
                    <li><a class="twitter" href="https://twitter.com/abraham9333"></a></li>
                    <li><a class="rss" href="/rss"></a></li>
                </ul>
                <a class="title" href="/" target="_self">Building the Bridge</a>
            </div>
            <nav class="hide-on-mobile">

                <?
                $aUrl = array();

                function makeMainMenu($aPages)
                {
                    global $aUrl;
                    if (count($aPages) > 0) {
                        echo '<ul class="' . ($aPages[0]->level == 2 ? ' subPages' : '') . '">';
                        foreach ($aPages as $oPage) {
                            $iLevel = $oPage->level;

                            if ($iLevel == 1) {
                                $aUrl[$iLevel] = '/' . http_get('controller');
                            } else {
                                $aUrl[$iLevel] = $aUrl[$iLevel - 1] . '/' . http_get('param' . ($iLevel - 1));
                            }

                            // check if controller equals page urlPath be aware of special treathment for homepage!!
                            echo '<li class="' . ($oPage->hasSubpages() ? 'hasSubpages' : '') . '"><a class="' . ($aPages[0]->level == 2 ? 'subPageLink ' : '') . ($aUrl[$oPage->level] == $oPage->getUrlPath() || '/' . http_get('controller') == $oPage->getUrlPath() . 'home' ? 'active' : '') . '" href="' . $oPage->getUrlPath() . '">' . $oPage->getShortTitle() . '</a>';

                            //if ($aUrl[$iLevel] == $oPage->getUrlPath() || '/' . http_get('controller') == $oPage->getUrlPath() . 'home') {
                            if (count($oPage->hasSubpages()) > 0) {
                                makeMainMenu($oPage->getSubPages()); //call function recursive
                            }

                            echo '</li>';
                        }
                        echo '</ul>';
                    }
                }

                makeMainMenu(PageManager::getPagesByFilter(array('level' => 1)));
                ?>
                <a id="searchButton" href="/"></a>
                <a class="button" href="/account">
                    SUBSCRIBE NOW<Br>
                    <span>Free Trial For Life</span>
                </a>
            </nav>
            <nav id="mobileMenu" class="hide-on-tablet hide-on-desktop show-on-mobile no-print">
                <div id="navButton"></div>
                <form id="mobileSearchForm" method="POST">
                    <a id="searchButtonMobile" href="/" title="Search"></a>
                    <input type="hidden" name="action" value="search">
                    <input id="mobileQ" type="search" name="q" placeholder="Keywords">
                </form>
                <?
                $aUrl = array();

                function makeMobileMenu($aPages)
                {
                    global $aUrl;
                    if (count($aPages) > 0) {

                        foreach ($aPages as $oPage) {
                            $iLevel = $oPage->level;

                            if ($iLevel == 1) {
                                $aUrl[$iLevel] = '/' . http_get('controller');
                            } else {
                                $aUrl[$iLevel] = $aUrl[$iLevel - 1] . '/' . http_get('param' . ($iLevel - 1));
                            }

                            // check if controller equals page urlPath be aware of special treathment for homepage!!
                            echo '<li><a class="' . ($iLevel == 2 ? 'submenu-1' : '') . ' ' . ($aUrl[$oPage->level] == $oPage->getUrlPath() || '/' . http_get('controller') == $oPage->getUrlPath() . 'home' ? 'active' : '') . '" href="' . $oPage->getUrlPath() . '">' . $oPage->getShortTitle() . '</a>';
                            if ($aUrl[$iLevel] == $oPage->getUrlPath() || '/' . http_get('controller') == $oPage->getUrlPath() . 'home') {
                                makeMobileMenu($oPage->getSubPages()); //call function recursive
                            }
                            echo '</li>';
                        }
                    }
                }

                echo '<ul id="mobileMenuLinks">';
                makeMobileMenu(PageManager::getPagesByFilter(array('level' => 1)));
                echo '</ul>';
                ?>
            </nav>
            <div id="searchBox" class="hide-on-tablet hide-on-desktop">
                <div class="header">Search</div>
                <div>
                    <form method="POST" action="/">
                        <input type="hidden" name="action" value="search">
                        <input type="search" placeholder="Text" name="q">
                        <input type="submit" name="search" value="Search">
                    </form>
                </div>
            </div>
            <? if (empty($oCurrentCustomer)) { ?>
                <div id="loginForm">
                    <form method="POST" action="/account"><input type="hidden" name="action" value="login">
                        <input type="email" name="email" placeholder="Email">
                        <input type="password" name="password" placeholder="Password">
                        <input type="submit" name="login" value="ENTER">
                    </form>
                </div>
            <? } ?>
        </header>
        <div class="main-content">
            <?
            # include the actual page with changable content
            include_once $oPageLayout->sPagePath;
            ?>
        </div>

    </div>
    <footer>
        <div class="default-container-width footerContent">
            <div class="column-75 float-left">
                <h1 class="hide-on-mobile">Building the Bridge</h1>
                <p class="hide-on-mobile">
                    In case there is any question what our role in the world should be, it could be to leave it a better place. Can we do what is right?
                    As a response to ‘a tooth for a tooth, an eye for an eye’ one could choose to give a tooth or to give an eye if needed and warranted.
                    That is what bridge building is about. Be part of building the bridge.</p>
                <p class="hide-on-mobile">Get on board to envision an extraordinary adventure with life changing and life giving possibilities</p>
                <ul class="socialMedia no-print hide">
                    <li><a href="http://www.linkedin.com/company/building-the-bridge"><img style="background:white" src="/images/layout/linkedin-header-icon.png" alt="Linkedin">Linkedin</a></li>
                    <li><a href="https://www.facebook.com/pages/Building-the-Bridge/664545083667211"><img src="/images/layout/facebook-header-icon.png" alt="Facebook">Facebook</a></li>
                    <li><a href="https://twitter.com/abraham9333"><img src="/images/layout/twitter-header-icon.png" alt="Twitter">Twitter</a></li>
                    <li><a href="/rss"><img src="/images/layout/rss-header-icon.png" alt="Rss">Rss</a></li>
                </ul>
            </div>
            <div class="column-width float-left hide-on-mobile">&nbsp;</div>
            <div class="column-25 float-right  hide-on-mobile">
                <h1>Categories</h1>
                <nav>
                    <ul class="footerNav">
                        <?
                        foreach (PageManager::getPagesByFilter(array('level' => 1)) as $oSubpage) {
                            if ($oSubpage->getUrlPath() != '/') {
                        ?>
                                <li>
                                    <div class="item">&gt;</div><a href="<?= $oSubpage->getUrlPath() ?>"><?= $oSubpage->getShortTitle() ?></a>
                                </li>
                        <?
                            }
                        }
                        ?>
                    </ul>
                </nav>
            </div>

        </div>

    </footer>

    <!-- DONATION FORM -->
    <form id="donateForm" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" style="display:none">
        <input type="hidden" name="lc" value="GB">
        <input type="hidden" name="cmd" value="_s-xclick">
        <input type="hidden" name="hosted_button_id" value="8YS28UJT52BQA">
        <input id="donateButton" type="submit" name="submit" value="Donate">
    </form>
    <!-- /DONATION FORM -->

    <div id="credits" class="default-container-width">
        <div class="column-50 float-left">
            @ 2023 BUILDING THE BRIDGE. ALL RIGHTS RESERVED
        </div>
        <div class="column-width float-left hide-on-mobile no-print">&nbsp;</div>
        <div class="column-50 float-left no-print">
            <div id="lowerMenu">
                <ul>
                    <li><a href="/sitemap">SITEMAP</a></li>
                    <li>·</li>
                    <li><a href="/privacy-policy">PRIVACY POLICY</a></li>
                    <li>·</li>
                    <li><a href="/terms-and-conditions">TERMS AND CONDITIONS</a></li>
                </ul>
            </div>
        </div>
    </div>
    <script src="<?= JS_FOLDER ?>/jquery.min.js"></script>
    <script src="<?= PLUGINS_FOLDER ?>/fancybox/jquery.fancybox.pack.min.js"></script>
    <script src="<?= PLUGINS_FOLDER ?>/autoNumeric.min.js"></script>
    <script src="<?= PLUGINS_FOLDER ?>/validate/jquery.validate.min.js"></script>
    <script src="<?= PLUGINS_FOLDER ?>/progressbar/progressbar.min.js"></script>
    <script src="<?= JS_FOLDER ?>/base_functions.min.js"></script>
    <script src="<?= JS_FOLDER ?>/order_functions.min.js"></script>
    <script src="<?= JS_FOLDER ?>/matchmedia.min.js"></script>
    <script src="<?= JS_FOLDER ?>/picturefill.min.js"></script>
    <script src="<?= JS_FOLDER ?>/default.min.js"></script>
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <script>
        ! function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0],
                p = /^http:/.test(d.location) ? 'http' : 'https';
            if (!d.getElementById(id)) {
                js = d.createElement(s);
                js.id = id;
                js.src = p + '://platform.twitter.com/widgets.js';
                fjs.parentNode.insertBefore(js, fjs);
            }
        }(document, 'script', 'twitter-wjs');

        $('.donateLink').click(
            function(e) {

                $('#donateButton').trigger("click");
                e.preventDefault();
            }
        );
    </script>
    <?
    # echo extra javascript stuff
    foreach ($oPageLayout->getJavascriptsBottom() as $sJavascript) {
        echo $sJavascript . "\n";
    }
    ?>
</body>

</html>