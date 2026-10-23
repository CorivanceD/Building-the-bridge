<!--<div class="breadcrumbs">U bevindt zich hier: <?= $oPageLayout->sCrumblePath ?></div>-->
<section style="margin-top: 32px;">
    <!-- MAIN CONTENT -->

    <!-- PAGES WITH RSS FEEDS -->
    <div class="column-66 float-left rssFeeds">
        <h1 class="sectionTitle" style="display:inline-block;"><?= http_session('searchArticles', false) ? 'Found ' . $iFoundArticles . ' articles' : 'WELCOME TO' ?></h1>&nbsp;
        <? //if (!http_session('searchArticles', false)) { 
        ?>
        <form style="display:inline-block;" id="contentTypeForm" method="POST" action="<?= CLIENT_HTTP_URL ?>">
            <select id="contentType" name="contentType">
                <option value="<?= CONTENT_HOPE ?>" <?= intval(http_session('contentType')) === CONTENT_HOPE ? 'selected' : '' ?>>Ballots Not Bullets</option>
                <option value="<?= CONTENT_LATEST_LETTERS ?>" <?= intval(http_session('contentType')) === CONTENT_LATEST_LETTERS ? 'selected' : '' ?>>Just Sent</option>
                <option value="<?= CONTENT_LUMINARIES ?>" <?= intval(http_session('contentType')) === CONTENT_LUMINARIES ? 'selected' : '' ?>>Voices of Wisdom</option>
                <option value="<?= CONTENT_WORLD_LEADERS ?>" <?= intval(http_session('contentType')) === CONTENT_WORLD_LEADERS ? 'selected' : '' ?>>Letter to World Leaders</option>
                <option value="<?= CONTENT_SEND_LETTER ?>" <?= intval(http_session('contentType')) === CONTENT_SEND_LETTER ? 'selected' : '' ?>>Send Yours</option>
            </select>
        </form>
        <? //} 
        ?>
        <hr>
        <?
        $bFirst = 1;
        foreach ($aLastArticles as $oArticle) {
        ?>
            <div class="homePageArticle">
                <h2 class="articleTitle"><?= _e($oArticle->title) ?></h2>
                <?
                $oArticleImage = $oArticle->getImages('first-online');

                if (!empty($oArticleImage)) {
                ?>
                    <img class="articleHeadImage" src="<?= $oArticleImage->getImageFileByReference('detail')->link ?>" alt="<?= empty($oArticleImage->getImageFileByReference('detail')->title) ? $oArticle->getShortTitle() : $oArticleImage->getImageFileByReference('detail')->title ?>" <?= $oArticleImage->getImageFileByReference('detail')->imageSizeAttr ?>>
                <? }
                ?>
                <div class="articleIntro">
                    <? if ($bFirst !== 1) {
                    ?>
                        <span class="pubDate"><?= strftime('%B %d, %Y', strtotime($oArticle->pubDate)) ?></span>
                    <?
                    } else {
                        $bFirst = 0;
                    }
                    ?>
                    <?= $oArticle->intro ?>
                </div>
                <a class="readMore no-print" href="<?= $oArticle->getUrlPath() ?>">Read More</a>
            </div>
        <? }
        ?>
        <div style="display: none">
            <? if (!empty($aRssFeedItems)) { ?>
                <br>
                <br>
                <h3><?= http_session('searchArticles', false) ? 'Found ' . $iFoundRssFeedItems . ' rss feed items' : 'LATEST RSS FEED ITEMS UPDATED IN REAL TIME' ?></h3>
                <hr>
                <br>
                <ul>

                    <? foreach ($aRssFeedItems as $oRssFeedItem) {
                    ?>

                        <li>
                            <h3><?= $oRssFeedItem->title ?></h3>
                            <span class="pubDate"><?= ucfirst(Date::strToDate($oRssFeedItem->pubDate)->format('%B %d, %Y %H:%M')) ?></span> <?= $oRssFeedItem->description ?><br>
                            <a class="readMore no-print" style="display:inline-block" href="<?= $oRssFeedItem->link ?>" target="_blank">Read More</a><br>
                            <?
                            if (preg_match('/^.*\bisrael\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<img class="feedFlag" src="/images/layout/countryflags/israel.jpg" alt="Israel" height="28" width="42">';
                            }
                            if (preg_match('/^.*\bpalestine\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<img class="feedFlag" src="/images/layout/countryflags/palestine.jpg" alt="Palestine" height="28" width="42">';
                            }
                            if (preg_match('/^.*\bafrika\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<img class="feedFlag" src="/images/layout/countryflags/afrika.jpg" alt="Africa" height="28" width="42">';
                            }
                            if (preg_match('/^.*\bchina\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<img class="feedFlag" src="/images/layout/countryflags/asia.jpg" alt="China" height="28" width="42">';
                            }
                            if (preg_match('/^.*\brussia\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<img class="feedFlag" src="/images/layout/countryflags/rusia.jpg" alt="Russia" height="28" width="42">';
                            }
                            if (preg_match('/^.*\begypt\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<img class="feedFlag" src="/images/layout/countryflags/egypt.jpg" alt="Egypt" height="28" width="42">';
                            }
                            if (preg_match('/^.*\beurope\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<img class="feedFlag" src="/images/layout/countryflags/europe.jpg" alt="Europe" height="28" width="42">';
                            }
                            if (preg_match('/^.*\biran\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<img class="feedFlag" src="/images/layout/countryflags/iran.jpg" alt="Iran" height="28" width="42">';
                            }
                            if (preg_match('/^.*\bmiddle\-east\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<img class="feedFlag" src="/images/layout/countryflags/middle-east.jpg" alt="Middle Eats" height="28" width="42">';
                            }
                            if (preg_match('/^.*\bsweden\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<img class="feedFlag" src="/images/layout/countryflags/sweden.jpg" alt="Sweden" height="28" width="42">';
                            }
                            if (preg_match('/^.*\buk\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<img class="feedFlag" src="/images/layout/countryflags/uk.jpg" alt="UK" height="28" width="42">';
                            }
                            if (preg_match('/^.*\bus\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<img class="feedFlag" src="/images/layout/countryflags/us.jpg" alt="US" height="28" width="42">';
                            }
                            if (preg_match('/^.*\bworld\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<img class="feedFlag" src="/images/layout/countryflags/world.jpg" alt="World" height="28" width="42">';
                            }

                            $aFeedTitleParts = explode('-', $oRssFeedItem->sourceTitle);

                            $oNewsSourceLogo = NewsSourceLogoManager::getNewsSourceLogoByName(trim($aFeedTitleParts[0]));

                            if (!empty($oNewsSourceLogo)) {
                                $oLogo = $oNewsSourceLogo->getImage();

                                if (!empty($oLogo)) {
                                    $oLogoFile = $oLogo->getImageFileByReference('original');
                                    echo '<img class="feedFlag" style="border:none !important" src="' . $oLogoFile->link . '" alt="' . $oLogoFile->title . '" height="28"  width="308">';
                                }
                            }
                            ?>
                        </li>

                    <?
                    } ?>
                </ul>
            <? }
            ?>

        </div>
    </div>
    <? include TEMPLATES_FOLDER . '/elements/rightColumn.inc.php'; ?>
</section>
<?
$sJavascript = <<<EOT
<script>
$('#contentType').change(
        function (e) {
            $('#contentTypeForm').submit();
        }
);
</script>
EOT;

$oPageLayout->addJavascriptBottom($sJavascript);
?>