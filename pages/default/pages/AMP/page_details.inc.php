<section>
    <!-- MAIN CONTENT -->
    <article >
        <div class="column-66 float-left rssFeeds">
            <!-- THE GREAT DEBATE & THE COMMON GROUND -->
            <? if ($oPage->hasArticles > 0) { ?>
                <h3><?= $oPage->title ?></h3>
                               
                <hr class="clear"> 
                <!-- LIST ARTICLES -->
                <? if (empty($oArticle)) { ?>
                    <ul>
                        <? foreach ($aArticles AS $oArticle) { ?>
                            <li><h3><?= $oArticle->title ?></h3><br> 
                                <?
                                $oArticleImage = $oArticle->getImages('first-online');

                                if (!empty($oArticleImage)) {
                                    ?><amp-img class="articleHeadImage" src="<?= $oArticleImage->getImageFileByReference('detail')->link ?>"   
                        data-bind="" <?= $oArticleImage->getImageFileByReference('detail')->imageSizeAttr ?> layout="responsive" alt="<?= empty($oArticleImage->getImageFileByReference('detail')->title) ? $oArticle->getShortTitle() : $oArticleImage->getImageFileByReference('detail')->title ?>">
                    </amp-img>
                                
                                    
                                <? }
                                ?>     
                                <?
                                $oWorldLeaderLetter = $oArticle->getWorldLeaderLetter();

                                if (!empty($oWorldLeaderLetter)) {
                                    ?>
                                    <div class="letter-description">Open letter from <?= $oArticle->getAuthor()->name ?> to <?= $oWorldLeaderLetter->getWorldLeader()->getCompleteName() ?></div>
                                    <?
                                }
                                ?>                                     
                                <span class="pubDate"><?= ucfirst(Date::strToDate($oArticle->pubDate)->format('%B %d, %Y %H:%M')) ?></span> <?= ampify($oArticle->intro) ?>

                                <a class="readMore no-print" href="/amp<?= $oArticle->getUrlPath() ?>" target="_self">Read More</a>
                            </li>
                        <? } ?>
                    </ul>
                                 
                    <!-- SHOW THE ARTICLE -->
                <? } else { ?>
                
                    <h1 class="articleTitle"><?= _e($oArticle->title) ?></h1>                    
                    <div class="articleIntro">
                        <span class="pubDate"><?= strftime('%B %d, %Y', strtotime($oArticle->pubDate)) ?></span>
                        <?= ampify($oArticle->intro) ?>
                    </div>    
                    <?
                    $oArticleImage = $oArticle->getImages('first-online');

                    if (!empty($oArticleImage)) {
                        ?>
                    <amp-img src="<?= $oArticleImage->getImageFileByReference('detail')->link ?>"   
                        data-bind=""width="960" height="700" layout="responsive" alt="<?= empty($oArticleImage->getImageFileByReference('detail')->title) ? $oArticle->getShortTitle() : $oArticleImage->getImageFileByReference('detail')->title ?>">
                    </amp-img><br />
                        
                    <? }
                    ?>
                    <?
                    $oWorldLeaderLetter = $oArticle->getWorldLeaderLetter();

                    if (!empty($oWorldLeaderLetter)) {
                        ?>
                        <div class="letter-description">Open letter from <?= $oArticle->getAuthor()->name ?> to <?= $oWorldLeaderLetter->getWorldLeader()->getCompleteName() ?></div>
                        <?
                    }
                    ?> 
                    <div class="articleContent">
                        <?= ampify($oArticle->content) ?>
                    </div>
                   
                    <? if (!empty($aFiles) || !empty($aLinks)) { ?>
                        <div class="linksAndFiles">
                            <ul>
                                <? foreach ($aFiles AS $oFile) { ?>
                                    <? if ($oFile->online) { ?>
                                        <li><a class="articleLink" href="<?= $oFile->link ?>" target="_self"><?= $oFile->title ? $oFile->title : $oFile->name ?></a></li>
                                    <? } ?>
                                <? } ?>
                                <? foreach ($aLinks AS $oLink) { ?>
                                    <? if ($oLink->online) { ?>
                                        <li><a class="articleLink" href="<?= $oLink->link ?>" target="<?= getLinkTarget($oLink->link) ?>"><?= $oLink->title ? $oLink->title : $oLink->link ?></a></li>
                                    <? } ?>
                                <? } ?>
                            </ul>                        
                        </div>
                    <? } ?>     
                        
                     <? if (!empty($aImages) || !empty($aYoutubeVideos)) { ?>
                        <div class="photosAndVideos clearfix">                            
                            <amp-image-lightbox id="lightbox1" layout="nodisplay"></amp-image-lightbox>
                            <div class="clearfix">
                            <?php foreach ($aImages AS $oImage) { ?>
                                <? if ($oImage->online && ($oCropSmall = $oImage->getImageFileByReference('crop_small')) && ($oDetail = $oImage->getImageFileByReference('crop_small'))) {                                     
                                    ?>
                                <div class="articleImage">
                                    <amp-img on="tap:lightbox1"
                                           role="button"
                                           tabindex="0"
                                           src="<?= $oImage->getImageFileByReference('detail')->link ?>"
                                           alt="<?= $oCropSmall->title ?>"
                                           title="<?= $oCropSmall->title ?>"                                           
                                           <?= $oCropSmall->imageSizeAttr ?>></amp-img>                                 
                                </div>        
                                <? } ?>
                            <? } ?>
                            </div>
                            <? foreach ($aYoutubeVideos AS $oYoutubeVideo) { ?>
                                <? if ($oYoutubeVideo->online) { ?>
                            <div class="clearfix">
                                <amp-youtube width="480"
                                  height="270"
                                  layout="responsive"
                                  data-videoid="<?=$oYoutubeVideo->getYoutubeLinkUniqueId()?>">
                                </amp-youtube>
                            </div>        
                                <? } ?>
                            <? } ?> 
                        </div>
                    <? } ?>       
                                        
                <? } ?>
            <? } elseif (!empty($aRssFeeds)) {
                ?>
                <h1 class="sectionTitle"><?= $oPage->title ?></h1>
                <hr>
                <ul>
                    <? foreach ($aRssFeedItems AS $oRssFeedItem) { ?>
                        <li id="rssitem-<?= $oRssFeedItem->rssFeedItemId ?>"><h2 class="feedItemTitle"><?= ampify($oRssFeedItem->title) ?></h2>                    
                            <span class="pubDate"><?= ucfirst(Date::strToDate($oRssFeedItem->pubDate)->format('%B %d, %Y %H:%M')) ?></span> <?= ampify($oRssFeedItem->description) ?><br>
                            <a class="readMore no-print inlineblock" href="<?= $oRssFeedItem->link ?>" target="_blank">Read More</a>
                            <?
                            if (preg_match('/^.*\bisrael\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<amp-img class="feedFlag" src="/images/layout/countryflags/israel.jpg" alt="Israel" height="28" width="42"></amp-img>';                                 
                            }
                            if (preg_match('/^.*\bpalestine\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<amp-img class="feedFlag" src="/images/layout/countryflags/palestine.jpg" alt="Palestine" height="28" width="42"></amp-img>';
                            }
                            if (preg_match('/^.*\bafrika\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<amp-img class="feedFlag" src="/images/layout/countryflags/afrika.jpg" alt="Africa" height="28" width="42"></amp-img>';
                            }
                            if (preg_match('/^.*\bchina\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<amp-img class="feedFlag" src="/images/layout/countryflags/asia.jpg" alt="China" height="28" width="42"></amp-img>';
                            }
                            if (preg_match('/^.*\brussia\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<amp-img class="feedFlag" src="/images/layout/countryflags/rusia.jpg" alt="Russia" height="28" width="42"></amp-img>';
                            }
                            if (preg_match('/^.*\begypt\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<amp-img class="feedFlag" src="/images/layout/countryflags/egypt.jpg" alt="Egypt" height="28" width="42"></amp-img>';
                            }
                            if (preg_match('/^.*\beurope\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<amp-img class="feedFlag" src="/images/layout/countryflags/europe.jpg" alt="Europe" height="28" width="42"></amp-img>';
                            }
                            if (preg_match('/^.*\biran\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<amp-img class="feedFlag" src="/images/layout/countryflags/iran.jpg" alt="Iran" height="28" width="42"></amp-img>';
                            }
                            if (preg_match('/^.*\bmiddle\-east\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<amp-img class="feedFlag" src="/images/layout/countryflags/middle-east.jpg" alt="Middle Eats" height="28" width="42"></amp-img>';
                            }
                            if (preg_match('/^.*\bsweden\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<amp-img class="feedFlag" src="/images/layout/countryflags/sweden.jpg" alt="Sweden" height="28" width="42"></amp-img>';
                            }
                            if (preg_match('/^.*\buk\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<amp-img class="feedFlag" src="/images/layout/countryflags/uk.jpg" alt="UK" height="28" width="42"></amp-img>';
                            }
                            if (preg_match('/^.*\bus\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<amp-img class="feedFlag" src="/images/layout/countryflags/us.jpg" alt="US" height="28" width="42"></amp-img>';
                            }
                            if (preg_match('/^.*\bworld\b.*$/i', $oRssFeedItem->description) === 1) {
                                echo '<amp-img class="feedFlag" src="/images/layout/countryflags/world.jpg" alt="World" height="28" width="42"></amp-img>';
                            }

                            $aFeedTitleParts = explode('-', $oRssFeedItem->sourceTitle);

                            $oNewsSourceLogo = NewsSourceLogoManager::getNewsSourceLogoByName(trim($aFeedTitleParts[0]));

                            if (!empty($oNewsSourceLogo)) {
                                $oLogo = $oNewsSourceLogo->getImage();

                                if (!empty($oLogo)) {
                                    $oLogoFile = $oLogo->getImageFileByReference('original');
                                    echo '<amp-img class="feedFlag" src="' . $oLogoFile->link . '" alt="' . $oLogoFile->title . '" height="28" width="308"></amp-img>';
                                }
                            }
                            ?>

                        </li>
                    <? } ?>
                </ul>                        
                <? if (count($aRssFeedItems) > 1) {
                    ?>
                    <div class="cf pagination-perPage no-print">
                        <?
                        echo generatePaginationHTML($iPageCount, $iCurrPage);
                        
                        ?>
                    </div>
                    <br class="clear">
                <? } ?>
                <!-- COMMON GROUND -->
            <? } ?>
        
           
        </div>
        <? include TEMPLATES_FOLDER . '/elements/rightColumn-AMP.inc.php'; ?>
    </article>
    <!-- /MAIN CONTENT -->
</section>
