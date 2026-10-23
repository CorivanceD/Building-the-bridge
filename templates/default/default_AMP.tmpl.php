<!doctype html>
<html amp lang="en">  
    <head>
        <meta charset="utf-8"> 
        <script async src="https://cdn.ampproject.org/v0.js"></script>               
        <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
        <title><?= $oPageLayout->sWindowTitle ?> | <?= CLIENT_NAME ?></title>
        <link rel="canonical" href="<?= str_replace("/amp","",getCurrentUrl()) ?>" />
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
        
        # add prev/next canonical
        if (!empty($oPageLayout->sRelPrev))
            echo '<link rel="prev" href="' . $oPageLayout->sRelPrev . '" />' . PHP_EOL;
        if (!empty($oPageLayout->sRelNext))
            echo '<link rel="next" href="' . $oPageLayout->sRelNext . '" />' . PHP_EOL;
        ?>
        <?php
        $sAuthor='';
        if (!empty($oArticle->pubDate)) {
            
            if ($oAuthor = $oArticle->getAuthor()) {
                $sAuthor = '"author": {
              "@type": "Person",
              "name": "'. $oAuthor->name .'"
            },';
            }
        ?>
        <script type="application/ld+json">
          {
            "@context": "http://schema.org",
            "@type": "NewsArticle",
            "headline": "<?=firstXCharacters($oPageLayout->sOGTitle,100)?>",   
            <?=$sAuthor?>
            "publisher": {
              "@type": "Organization",
              "name": "Building the Bridge",
              "logo": {
                "@type": "ImageObject",
                "url": "https://buildingthebridge.eu/admin/images/layout/login/client-logo.png",
                "width": 365,
                "height": 87
              }
            },
            "datePublished": "<?=str_replace(" ","T",$oArticle->pubDate)."Z"?>",
            "dateModified": "<?=str_replace(" ","T",$oArticle->modified)."Z"?>"<?php                
            if (!empty($oPageLayout->sOGImage)) { 
            echo ',
            "image": {
              "@type": "ImageObject",
              "url": "' . $oPageLayout->sOGImage . '"
            }'; } ?>                    
          }
        </script>
        <?php } ?>
        
        <script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>
        <?php if (!empty($aImages)) { ?>
        <script async custom-element="amp-image-lightbox" src="https://cdn.ampproject.org/v0/amp-image-lightbox-0.1.js"></script>
        <?php } ?>
        <?php if (!empty($aYoutubeVideos)) { ?>
        <script async custom-element="amp-youtube" src="https://cdn.ampproject.org/v0/amp-youtube-0.1.js"></script>
        <?php } ?>
        <script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>
        <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>

        <style amp-custom>            
            <?= file_get_contents(DOCUMENT_ROOT . CSS_FOLDER . '/style.min.css'); ?>                  
            <?= file_get_contents(DOCUMENT_ROOT . CSS_FOLDER . '/style-AMP.min.css'); ?>      
            <?= file_get_contents(DOCUMENT_ROOT . CSS_FOLDER . '/style_responsive-AMP.min.css'); ?>
        </style>
        
        <meta name="author" content="<?= CLIENT_NAME ?>" />

        <link rel="icon" href="/favicon.ico">
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
                
    </head>
    <body>  
        <amp-analytics type="googleanalytics">
        <script type="application/json">
        {
          "vars": {
            "account": "UA-58578057-1"
          },
          "triggers": {
            "trackPageview": {
              "on": "visible",
              "request": "pageview"
            }
          }
        }
        </script>
        </amp-analytics>
        <?
        $aUrl = array();
        function makeMobileMenu($aPages) {
            global $aUrl;
            if (count($aPages) > 0) {

                foreach ($aPages AS $oPage) {
                    $iLevel = $oPage->level;
                    
                    $sAmp = "";
                    if ($oPage->getUrlPath()=="/news-israel" || $oPage->getUrlPath()=="/news-palestine" || $oPage->getUrlPath()=="/global-perspective" || $oPage->getUrlPath()=="/common-ground" || $oPage->getUrlPath()=="/the-great-debate" || $oPage->getUrlPath()=="/world-leaders") {
                        $sAmp = "/amp";
                    }

                    if ($iLevel == 1) {
                        $aUrl[$iLevel] = $sAmp . '/' . http_get('controller');
                    } else {
                        $aUrl[$iLevel] = $aUrl[$iLevel - 1] . '/' . http_get('param' . ($iLevel - 1));
                    }

                    // check if controller equals page urlPath be aware of special treathment for homepage!!
                    echo '<li><a class="' . ($iLevel == 2 ? 'submenu-1' : '') . ' ' . ($aUrl[$oPage->level] == $sAmp.$oPage->getUrlPath() || '/' . http_get('controller') == $oPage->getUrlPath() . 'home' ? 'active' : '' ) . '" href="' . $sAmp.$oPage->getUrlPath() . '">' . $oPage->getShortTitle() . '</a>';
                    if ($aUrl[$iLevel] == $oPage->getUrlPath() || '/' . http_get('controller') == $oPage->getUrlPath() . 'home') {
                        makeMobileMenu($oPage->getSubPages()); //call function recursive
                    }
                    echo '</li>';
                }
            }
        }
        ?>  
        
        <amp-sidebar id="sidebar"
              layout="nodisplay"
              side="right">
              <amp-img class="amp-close-image"
                src="/images/layout/ic_close_black_18dp_2x.png"
                width="20"
                height="20"
                alt="close sidebar"
                on="tap:sidebar.close"
                role="button"
                tabindex="0"></amp-img>
              <ul>
                <?php makeMobileMenu(PageManager::getPagesByFilter(array('level' => 1)));?>                        
              </ul>
        </amp-sidebar>
        <div id="banner"></div>
        <div class="default-container-width">
            <header>     
                <div id="today" class="hide-on-mobile"><?
                    $oCurrentDate = new Date();
                    echo $oCurrentDate->format('%A, %d %B, %Y');
                ?></div>                
                <div id="upperMenu" class="no-print">                    
                    <ul>
                        <li><a href="/account/newsletter"><amp-img class="newsletter" src="<?= IMAGE_FOLDER . '/layout/newsletter.png' ?>" width="18" height="12" alt="Subscribe to our newsletter"></amp-img> NEWSLETTER</a></li>
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
                        <li><a href="/amp/world-leaders" class="worldleaders"><b>WORLD LEADERS</b></a></li>                     
                    </ul>
                </div>
                
                <div id="headerTitle">   
                    <ul>
                        <li><a class="linkedin" href="http://www.linkedin.com/company/building-the-bridge"></a></li>
                        <li><a class="facebook" href="https://www.facebook.com/pages/Building-the-Bridge/664545083667211"></a></li>
                        <li><a class="twitter" href="https://twitter.com/abraham9333"></a></li>
                        <li><a class="rss" href="/rss"></a></li>
                    </ul>
                    <a class="title" href="/" target="_self">Building the Bridge</a>
                    <div id="mobileMenu" class="hide-on-tablet hide-on-desktop show-on-mobile no-print">
                    <button on="tap:sidebar.toggle" id="navButton"></button>
                    </div>
                </div>  
                <nav class="hide-on-mobile">                   
                    <?
                    $aUrl = array();

                    function makeMainMenu($aPages) {
                        global $aUrl;
                        if (count($aPages) > 0) {
                            echo '<ul class="' . ($aPages[0]->level == 2 ? ' subPages' : '') . '">';
                            foreach ($aPages AS $oPage) {
                                $iLevel = $oPage->level;

                                $sAmp = "";
                                if ($oPage->getUrlPath()=="/news-israel" || $oPage->getUrlPath()=="/news-palestine" || $oPage->getUrlPath()=="/global-perspective" || $oPage->getUrlPath()=="/common-ground" || $oPage->getUrlPath()=="/the-great-debate" || $oPage->getUrlPath()=="/world-leaders") {
                                    $sAmp = "/amp";
                                }
                                
                                if ($iLevel == 1) {
                                    $aUrl[$iLevel] = $sAmp . '/' . http_get('controller');
                                } else {
                                    $aUrl[$iLevel] = $aUrl[$iLevel - 1] . '/' . http_get('param' . ($iLevel - 1));
                                }

                                
                                
                                // check if controller equals page urlPath be aware of special treathment for homepage!!
                                echo '<li class="' . ($oPage->hasSubpages() ? 'hasSubpages' : '') . '"><a class="' . ($aPages[0]->level == 2 ? 'subPageLink ' : '') . ($aUrl[$oPage->level] == $sAmp.$oPage->getUrlPath() || '/' . http_get('controller') == $sAmp.$oPage->getUrlPath() . 'home' ? 'active' : '' ) . '" href="' . $sAmp.$oPage->getUrlPath() . '">' . $oPage->getShortTitle() . '</a>';

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

                </nav>
                
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
                    <ul class="socialMedia no-print">
                        <li><a href="http://www.linkedin.com/company/building-the-bridge"><amp-img src="/images/layout/linkedin-header-icon.png" width="26" height="26" alt="Linkedin" ></amp-img></a></li>
                        <li><a href="https://www.facebook.com/pages/Building-the-Bridge/664545083667211"><amp-img src="/images/layout/facebook-header-icon.png" width="26" height="26" alt="Facebook"></amp-img></a></li>
                        <li><a href="https://twitter.com/abraham9333"><amp-img src="/images/layout/twitter-header-icon.png" width="26" height="26" alt="Twitter"></amp-img></a></li>
                        <li><a href="/rss"><amp-img src="/images/layout/rss-header-icon.png" width="26" height="26" alt="Rss"></amp-img></a></li>
                    </ul>
                </div>
                <div class="column-width float-left hide-on-mobile">&nbsp;</div>
                <div class="column-25 float-right  hide-on-mobile">
                    <h1>Categories</h1>
                    <nav>
                        <ul class="footerNav">
                            <?
                            foreach (PageManager::getPagesByFilter(array('level' => 1)) AS $oSubpage) {
                                if ($oSubpage->getUrlPath() != '/') {
                                    ?>
                                    <li><div class="item">&gt;</div><a href="<?= $oSubpage->getUrlPath() ?>"><?= $oSubpage->getShortTitle() ?></a></li>
                                    <?
                                }
                            }
                            ?>
                        </ul>
                    </nav>
                </div>

            </div>    
        </footer>

        
        <div id="credits" class="default-container-width">
            <div class="column-50 float-left">
                @ 2014 BUILDING THE BRIDGE. ALL RIGHTS RESERVED
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
       
    </body>
</html>