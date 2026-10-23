<section>
    <!-- MAIN CONTENT -->
    <article >
        <div class="column-66 float-left rssFeeds">
            <!-- THE GREAT DEBATE & THE COMMON GROUND -->
            <?php if ($oPage->hasArticles > 0) { ?>
                <h3><?= $oPage->title ?></h3>
                               
                <hr class="clear"> 
                <!-- LIST ARTICLES -->
                <?php if (empty($oArticle)) { ?>
                    <ul>
                        <?php foreach ($aArticles AS $oArticle) { ?>
                            <li><h3><?= $oArticle->title ?></h3><br> 
                                <?php
                                $oArticleImage = $oArticle->getImages('first-online');

                                if (!empty($oArticleImage)) {
                                    ?><amp-img class="articleHeadImage" src="<?= $oArticleImage->getImageFileByReference('detail')->link ?>"   
                        data-bind="" <?= $oArticleImage->getImageFileByReference('detail')->imageSizeAttr ?> layout="responsive" alt="<?= empty($oArticleImage->getImageFileByReference('detail')->title) ? $oArticle->getShortTitle() : $oArticleImage->getImageFileByReference('detail')->title ?>">
                    </amp-img>
                                
                                    
                                <?php }
                                ?>     
                                <?php
                                $oWorldLeaderLetter = $oArticle->getWorldLeaderLetter();

                                if (!empty($oWorldLeaderLetter)) {
                                    ?>
                                    <div class="letter-description">Open letter from <?= $oArticle->getAuthor()->name ?> to <?= $oWorldLeaderLetter->getWorldLeader()->getCompleteName() ?></div>
                                    <?php
                                }
                                ?>                                     
                                <span class="pubDate"><?= ucfirst(Date::strToDate($oArticle->pubDate)->format('%B %d, %Y %H:%M')) ?></span> <?= ampify($oArticle->intro) ?>

                                <a class="readMore no-print" href="/amp<?= $oArticle->getUrlPath() ?>" target="_self">Read More</a>
                            </li>
                        <?php } ?>
                    </ul>
                                 
                    <!-- SHOW THE ARTICLE -->
                <?php } else { ?>
                
                    <h1 class="articleTitle"><?= _e($oArticle->title) ?></h1>                    
                    <div class="articleIntro">
                        <span class="pubDate"><?= legacy_strftime('%B %d, %Y', strtotime($oArticle->pubDate)) ?></span>
                        <?= ampify($oArticle->intro) ?>
                    </div>    
                    <?php
                    $oArticleImage = $oArticle->getImages('first-online');

                    if (!empty($oArticleImage)) {
                        ?>
                    <amp-img src="<?= $oArticleImage->getImageFileByReference('detail')->link ?>"   
                        data-bind=""width="960" height="700" layout="responsive" alt="<?= empty($oArticleImage->getImageFileByReference('detail')->title) ? $oArticle->getShortTitle() : $oArticleImage->getImageFileByReference('detail')->title ?>">
                    </amp-img><br />
                        
                    <?php }
                    ?>
                    <?php
                    $oWorldLeaderLetter = $oArticle->getWorldLeaderLetter();

                    if (!empty($oWorldLeaderLetter)) {
                        ?>
                        <div class="letter-description">Open letter from <?= $oArticle->getAuthor()->name ?> to <?= $oWorldLeaderLetter->getWorldLeader()->getCompleteName() ?></div>
                        <?php
                    }
                    ?> 
                    <div class="articleContent">
                        <?= ampify($oArticle->content) ?>
                    </div>
                   
                    <?php if (!empty($aFiles) || !empty($aLinks)) { ?>
                        <div class="linksAndFiles">
                            <ul>
                                <?php foreach ($aFiles AS $oFile) { ?>
                                    <?php if ($oFile->online) { ?>
                                        <li><a class="articleLink" href="<?= $oFile->link ?>" target="_self"><?= $oFile->title ? $oFile->title : $oFile->name ?></a></li>
                                    <?php } ?>
                                <?php } ?>
                                <?php foreach ($aLinks AS $oLink) { ?>
                                    <?php if ($oLink->online) { ?>
                                        <li><a class="articleLink" href="<?= $oLink->link ?>" target="<?= getLinkTarget($oLink->link) ?>"><?= $oLink->title ? $oLink->title : $oLink->link ?></a></li>
                                    <?php } ?>
                                <?php } ?>
                            </ul>                        
                        </div>
                    <?php } ?>     
                        
                     <?php if (!empty($aImages) || !empty($aYoutubeVideos)) { ?>
                        <div class="photosAndVideos clearfix">                            
                            <amp-image-lightbox id="lightbox1" layout="nodisplay"></amp-image-lightbox>
                            <div class="clearfix">
                            <?php foreach ($aImages AS $oImage) { ?>
                                <?php if ($oImage->online && ($oCropSmall = $oImage->getImageFileByReference('crop_small')) && ($oDetail = $oImage->getImageFileByReference('crop_small'))) {                                     
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
                                <?php } ?>
                            <?php } ?>
                            </div>
                            <?php foreach ($aYoutubeVideos AS $oYoutubeVideo) { ?>
                                <?php if ($oYoutubeVideo->online) { ?>
                            <div class="clearfix">
                                <amp-youtube width="480"
                                  height="270"
                                  layout="responsive"
                                  data-videoid="<?=$oYoutubeVideo->getYoutubeLinkUniqueId()?>">
                                </amp-youtube>
                            </div>        
                                <?php } ?>
                            <?php } ?> 
                        </div>
                    <?php } ?>       
                                        
                <?php } ?>
            <?php } elseif (!empty($aRssFeeds)) {
                ?>
                <h1 class="sectionTitle"><?= $oPage->title ?></h1>
                <hr>
                <ul>
                    <?php foreach ($aRssFeedItems AS $oRssFeedItem) { ?>
                        <li id="rssitem-<?= $oRssFeedItem->rssFeedItemId ?>"><h2 class="feedItemTitle"><?= ampify($oRssFeedItem->title) ?></h2>                    
                            <span class="pubDate"><?= ucfirst(Date::strToDate($oRssFeedItem->pubDate)->format('%B %d, %Y %H:%M')) ?></span> <?= ampify($oRssFeedItem->description) ?><br>
                            <a class="readMore no-print inlineblock" href="<?= $oRssFeedItem->link ?>" target="_blank">Read More</a>
                            <?php
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
                    <?php } ?>
                </ul>                        
                <?php if (count($aRssFeedItems) > 1) {
                    ?>
                    <div class="cf pagination-perPage no-print">
                        <?php
                        echo generatePaginationHTML($iPageCount, $iCurrPage);
                        
                        ?>
                    </div>
                    <br class="clear">
                <?php } ?>
                <!-- COMMON GROUND -->
            <?php } ?>
        
           
        </div>
        <?php include TEMPLATES_FOLDER . '/elements/rightColumn-AMP.inc.php'; ?>
    </article>
    <!-- /MAIN CONTENT -->
</section>
