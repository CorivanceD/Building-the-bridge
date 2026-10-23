<?php

# set xml declaration
$sXML = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

if (http_get('param1')) {

    $oToday = new Date();
    $aUrls = array();
    if (class_exists('ArticleManager') && http_get('param1') == 'articles') {
        $aArticles = ArticleManager::getArticlesByFilter(array('status'=>Article::ARTICLE_STATUS_PUBLISHED));
        foreach ($aArticles AS $oArticle) {
            $oUrl = new stdClass(); // object for sorting on property later
            $oUrl->url = CLIENT_HTTPS_URL . $oArticle->getUrlPath();
            $date = new DateTime(($oArticle->pubDate === null ? $oArticle->modified : $oArticle->pubDate));
            $oUrl->date = $date->format('c');
            $aUrls[] = clone $oUrl;
        }    
    } elseif (http_get('param1') == 'pages') {
        $aPages = PageManager::getPagesByFilter(array('showAll' => 1, 'online' => 1), null, 0, $iFoundRows, array('IFNULL(`p`.`modified`, `p`.`created`)' => 'DESC'));
        foreach ($aPages AS $oPage) {
            $oUrl = new stdClass(); // object for sorting on property later
            $oUrl->url = CLIENT_HTTPS_URL . $oPage->getUrlPath();
            $date = new DateTime(($oPage->modified === null ? $oPage->created : $oPage->modified));
            $oUrl->date = $date->format('c');
            $aUrls[] = clone $oUrl;
        }
    }

    $sXML .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    foreach ($aUrls As $oUrl) {

        $iDaysDiff = ceil(Date::strToDate($oUrl->date)->daysDiff($oToday));
        if ($iDaysDiff == 1) {
            $fPrio = 1.0;
        } elseif ($iDaysDiff == 2) {
            $fPrio = 0.7;
        } else {
            $fPrio = 0.5;
        }

        $sXML .= '  <url>' . PHP_EOL;
        $sXML .= '      <loc>' . $oUrl->url . '</loc>' . PHP_EOL;
        $sXML .= '      <lastmod>' . $oUrl->date . '</lastmod>' . PHP_EOL;
        $sXML .= '      <priority>' . $fPrio . '</priority>' . PHP_EOL;
        $sXML .= '  </url>' . PHP_EOL;
    }

# end urlset
    $sXML .= '</urlset>';
} else {

    # set sitemapindex
    $sXML .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
    $sXML .= '<sitemap><loc>' . CLIENT_HTTPS_URL . '/sitemap-pages.xml.gz</loc></sitemap>' . PHP_EOL;
    if (class_exists('ArticleManager')) {
        $sXML .= '<sitemap><loc>' . CLIENT_HTTPS_URL . '/sitemap-articles.xml.gz</loc></sitemap>' . PHP_EOL;
    }

    # end sitemap index
    $sXML .= '</sitemapindex>';
}

// make gzip or regular XML
if (http_get('param2') == 'gz') {
    header('content-type: application/x-gzip');
} else {
    header("Content-type: text/xml");
}
echo $sXML;
?>