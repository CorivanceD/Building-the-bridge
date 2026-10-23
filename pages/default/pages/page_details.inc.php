<section style="margin-top: 32px;">
    <!-- MAIN CONTENT -->
    <article>
        <div class="column-66 float-left rssFeeds">
            <!-- THE GREAT DEBATE & THE COMMON GROUND -->
            <? if ($oPage->hasArticles > 0) { ?>
                <h3 class="float-left" style="margin:6px 10px 0px 0px"><?= $oPage->title ?></h3>
                <? if (getCurrentUrlPath() === '/world-leaders') { ?>
                    <form style="display:inline-block;margin-top:6px" id="contentTypeForm" method="POST" action="<?= CLIENT_HTTP_URL ?>">
                        <select id="contentType" name="contentType">
                            <option value="<?= CONTENT_HOPE ?>">Ballots Not Bullets</option>
                            <option value="<?= CONTENT_LATEST_LETTERS ?>">Just Sent</option>
                            <option value="<?= CONTENT_LUMINARIES ?>">Voices of Wisdom</option>
                            <option value="<?= CONTENT_WORLD_LEADERS ?>" selected>Letter to World Leaders</option>
                            <option value="<?= CONTENT_SEND_LETTER ?>">Send Yours</option>
                        </select>
                    </form>
                <? } ?>
                <? if (!empty($aArticleCategories)) { ?>
                    <div class="categorySelector float-left">
                        <form method="POST" id="categoryForm" action="/<?= http_get('controller') ?>">
                            <select id="articleCategoryId" name="articleCategoryId">
                                <option value="">All the categories</option>
                                <? foreach ($aArticleCategories as $oArticleCategory) { ?>
                                    <option <?= $_SESSION['articleCategoryId'][http_get('controller')] == $oArticleCategory->articleCategoryId ? 'selected' : '' ?> value="<?= $oArticleCategory->articleCategoryId ?>"><?= $oArticleCategory->getFullName() ?></option>
                                <? } ?>
                            </select>
                        </form>
                    </div>
                <? } ?>
                <hr class="clear">
                <!-- LIST ARTICLES -->
                <? if (empty($oArticle)) { ?>
                    <ul>
                        <? foreach ($aArticles as $oArticle) { ?>
                            <li>
                                <h3><?= $oArticle->title ?></h3><br>
                                <?
                                $oArticleImage = $oArticle->getImages('first-online');

                                if (!empty($oArticleImage)) {
                                ?>
                                    <img class="articleHeadImage" src="<?= $oArticleImage->getImageFileByReference('detail')->link ?>" alt="<?= empty($oArticleImage->getImageFileByReference('detail')->title) ? $oArticle->getShortTitle() : $oArticleImage->getImageFileByReference('detail')->title ?>" <?= $oArticleImage->getImageFileByReference('detail')->imageSizeAttr ?>>
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
                                <span class="pubDate"><?= ucfirst(Date::strToDate($oArticle->pubDate)->format('%B %d, %Y %H:%M')) ?></span> <?= $oArticle->intro ?>

                                <a class="readMore no-print" href="<?= $oArticle->getUrlPath() ?>" target="_self">Read More</a>
                            </li>
                        <? } ?>
                    </ul>
                    <? if (count($aArticles) > 0) {
                    ?>
                        <div class="cf pagination-perPage no-print">
                            <?
                            echo generatePaginationHTML($iPageCount, $iCurrPage);
                            include TEMPLATES_FOLDER . '/elements/perPage.inc.php';
                            ?>
                        </div>
                        <br class="clear">
                    <? } ?>
                    <!-- SHOW THE ARTICLE -->
                <? } else {
                ?>
                    <h1 class="articleTitle"><?= _e($oArticle->title) ?></h1>
                    <div class="articleIntro">
                        <span class="pubDate"><?= strftime('%B %d, %Y', strtotime($oArticle->pubDate)) ?></span>
                        <?= $oArticle->intro ?>
                    </div>
                    <?
                    $oArticleImage = $oArticle->getImages('first-online');

                    if (!empty($oArticleImage)) {
                    ?>
                        <img class="articleHeadImage" src="<?= $oArticleImage->getImageFileByReference('detail')->link ?>" alt="<?= empty($oArticleImage->getImageFileByReference('detail')->title) ? $oArticle->getShortTitle() : $oArticleImage->getImageFileByReference('detail')->title ?>" <?= $oArticleImage->getImageFileByReference('detail')->imageSizeAttr ?>>
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
                        <?= $oArticle->content ?>
                    </div>
                    <? //echo '<p><i>Author: ' . $oArticle->getAuthor()->name . '</i></p>';  
                    ?>
                    <!-- <a href="/<?= http_get('controller') ?>" class="read-more">To article list</a>  -->

                    <? if (!empty($aFiles) || !empty($aLinks)) { ?>
                        <div class="linksAndFiles">
                            <ul>
                                <? foreach ($aFiles as $oFile) { ?>
                                    <? if ($oFile->online) { ?>
                                        <li><a class="articleLink" href="<?= $oFile->link ?>" target="_self"><?= $oFile->title ? $oFile->title : $oFile->name ?></a></li>
                                    <? } ?>
                                <? } ?>
                                <? foreach ($aLinks as $oLink) { ?>
                                    <? if ($oLink->online) { ?>
                                        <li><a class="articleLink" href="<?= $oLink->link ?>" target="<?= getLinkTarget($oLink->link) ?>"><?= $oLink->title ? $oLink->title : $oLink->link ?></a></li>
                                    <? } ?>
                                <? } ?>
                            </ul>
                        </div>
                    <? } ?>
                    <? if (!empty($aImages) || !empty($aYoutubeVideos)) { ?>
                        <div class="photosAndVideos clearfix">
                            <? foreach ($aImages as $oImage) { ?>
                                <? if ($oImage->online && ($oCropSmall = $oImage->getImageFileByReference('crop_small')) && ($oDetail = $oImage->getImageFileByReference('crop_small'))) { ?>
                                    <a class="fancyBoxLink articleImage" href="<?= $oImage->getImageFileByReference('detail')->link ?>" data-fancybox-group="page" title="<?= $oCropSmall->title ?>">
                                        <img src="<?= $oCropSmall->link ?>" alt="<?= $oCropSmall->title ?>" <?= $oCropSmall->imageSizeAttr ?> />

                                    </a>
                                <? } ?>
                            <? } ?>
                            <? foreach ($aYoutubeVideos as $oYoutubeVideo) { ?>
                                <? if ($oYoutubeVideo->online) { ?>
                                    <a class="youtubeVideo fancyBoxLink fancybox.iframe" href="<?= $oYoutubeVideo->getEmbedLink() ?>" title="<?= $oYoutubeVideo->title ?>">
                                        <img class="playVideo" src="/images/layout/play-video.png" alt="Play Video" height="47" width="68" />
                                        <img src="<?= $oYoutubeVideo->getThumbLink(0) ?>" />
                                    </a>
                                <? } ?>
                            <? } ?>
                        </div>
                    <? } ?>
                    <div class="share clearfix">
                        <h3>SPREAD THE WORD: 'ENOUGH IS ENOUGH'</h3>
                        <hr>
                        <p>Enrapture those around you to say ‘YES’, to invoke Europe help Build the Bridge between the Divides. <a class="readMore no-print" href="/petitions" target="_self">Read more</a></p>
                        <br>

                        <div class="socialShare hide">
                            <div class="fb-like" data-href="<?= curPageURL() ?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="true"></div>
                            <a href="https://twitter.com/share" class="twitter-share-button" data-url="<?= curPageURL() ?>" data-text="Building the Bridge > <?= $oArticle->title ?>">Tweet</a>
                            <div class="g-plus" data-action="share" data-annotation="bubble" data-href="<?= curPageURL() ?>"></div>

                        </div>
                    </div>
                    <br><br>
                    <? if ($oArticle->authorId != User::ANONYMOUS_USER_ID) { ?>
                        <div class="aboutTheAuthor">
                            <h3>ABOUT THE AUTHOR</h3>
                            <hr>
                            <img src="<?= ($oArticle->getAuthor()->imageId === null) ? '/images/layout/no_user_photo.jpg' : $oArticle->getAuthor()->getImage()->getImageFileByReference('cms_thumb')->link ?>" width="100" height="100" alt="<?= empty($oUser) ? '' : $oUser->name ?>">
                            <p>
                                <span class="authorDescription">
                                    <b style="text-transform:uppercase"><?= $oArticle->getAuthor()->name ?></b><br>
                                    <?= _e($oArticle->getAuthor()->description) ?>
                                </span>
                                <a class="readMore no-print" href="/">READ MORE</a>
                            </p>

                        </div>
                        <br><br>
                    <? } ?>

                    <div class="newsletter-signup">
                        <h3>A FREE SUBSCRIPTION TO THE BUILDING THE BRIDGE NEWSLETTER</h3>
                        <hr>
                        <article class=" page-content contactForm">
                            <p>It’s quick and simple. Just enter your e-mail address to receive your free subscription of our newsletter. If you’d like us to know who you are, you may at your option also include your name and the name of your organization and where you are. We respect your privacy.</p>
                            <!-- Begin Mailchimp Signup Form -->
                            <div id="mc_embed_signup">
                                <form action="https://abrahamavankempen.us20.list-manage.com/subscribe/post?u=1873d0531334953a7b8b6c32a&amp;id=eb83ae447c&amp;f_id=00267be0f0" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
                                    <div id="mc_embed_signup_scroll">
                                        <div class="indicates-required"><span class="asterisk">*</span> indicates required</div>
                                        <div class="mc-field-group">
                                            <label for="mce-EMAIL">Email Address <span class="asterisk">*</span>
                                            </label>
                                            <input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL" required>
                                            <span id="mce-EMAIL-HELPERTEXT" class="helper_text"></span>
                                        </div>
                                        <div class="mc-field-group">
                                            <label for="mce-FNAME">First Name </label>
                                            <input type="text" value="" name="FNAME" class="" id="mce-FNAME">
                                            <span id="mce-FNAME-HELPERTEXT" class="helper_text"></span>
                                        </div>
                                        <div class="mc-field-group">
                                            <label for="mce-LNAME">Last Name </label>
                                            <input type="text" value="" name="LNAME" class="" id="mce-LNAME">
                                            <span id="mce-LNAME-HELPERTEXT" class="helper_text"></span>
                                        </div>
                                        <div id="mce-responses" class="clear">
                                            <div class="response" id="mce-error-response" style="display:none"></div>
                                            <div class="response" id="mce-success-response" style="display:none"></div>
                                        </div> <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
                                        <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_1873d0531334953a7b8b6c32a_eb83ae447c" tabindex="-1" value=""></div>
                                        <div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
                                    </div>
                                </form>
                            </div>
                            <script type='text/javascript' src='//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js'></script>
                            <script type='text/javascript'>
                                (function($) {
                                    window.fnames = new Array();
                                    window.ftypes = new Array();
                                    fnames[0] = 'EMAIL';
                                    ftypes[0] = 'email';
                                    fnames[1] = 'FNAME';
                                    ftypes[1] = 'text';
                                    fnames[2] = 'LNAME';
                                    ftypes[2] = 'text';
                                }(jQuery));
                                var $mcj = jQuery.noConflict(true);
                            </script>
                            <!--End mc_embed_signup-->

                        </article>
                        <br><br>
                    </div>
                    <Br>
                    <div class="relatedArticles">
                        <h3>RELATED ARTICLES</h3>
                        <hr>
                        <ul>
                            <?
                            $iFoundRows = false;

                            foreach (ArticleManager::getRelatedArticles($oArticle->articleId) as $oSelectedArticle) {
                            ?>
                                <li>
                                    <div class="pubDate"><?= Date::strToDate($oSelectedArticle->pubDate)->format('%d-%m') ?></div><a href="<?= $oSelectedArticle->getUrlPath() ?>"><?= $oSelectedArticle->title ?></a>
                                </li>
                            <? } ?>
                        </ul>
                    </div>
                    <br><br>
                    <div class="postComment hide">
                        <h3>SHARE YOUR OPINION, POST A COMMENT</h3>
                        <hr>
                        <p>Fill in the field below to share your opinion and post your comment.</p>
                        <form class="validateForm" method="POST">
                            <input type="hidden" name="action" value="postComment" />
                            <? include TEMPLATES_FOLDER . '/elements/errorBox.inc.php'; ?>
                            <input style="display:none" autocomplete="off" type="text" name="rumpelstiltskin-empty" value="" />
                            <input style="display:none" autocomplete="off" type="text" name="rumpelstiltskin-filled" value="repelsteeltje" />
                            <input type="hidden" name="articleId" value="<?= $oArticle->articleId ?>" />
                            <? if (empty($oCurrentCustomer)) { ?>
                                <input type="text" name="authorName" data-rule-required="true" data-msg-required="You must enter your name" placeholder="Your name" value="">
                                <input type="email" name="authorEmail" data-rule-required="true" data-msg-required="You must enter your email address" placeholder="Your email address" data-rule-email="true" data-msg-email="Please, enter a valid email address" value="">
                            <? } ?>
                            <textarea id="postAComment" name="comment" data-rule-required="true" data-msg-required="You must write your comment" placeholder="Your comment" rows="8" maxlength="500"><?= $sComment ?></textarea>
                            <input type="submit" name="post" value="Submit" />
                        </form>
                    </div>
                    <br><br>
                    <div class="comments hide">
                        <h3>COMMENTS</h3>
                        <hr>
                        <?
                        $bOffline = false;

                        if (!empty($oCurrentUser) && ($oCurrentUser->isAdmin() || $oCurrentUser->userProfile == User::USER_EDITOR)) {
                            $bOffline = true;
                        }
                        ?>
                        <p>This article has <?= count($oArticle->getComments($bOffline)) ?> comments at this time. We invoke you to participate the discussion and leave your comment below. Share your opinion and let the world know.</p>
                        <? foreach ($oArticle->getComments($bOffline) as $oArticleComment) { ?>
                            <div id="comment-<?= $oArticleComment->articleCommentId ?>" class="articleComment">
                                <img src="<?= (empty($oArticleComment->authorId) || empty($oArticleComment->getAuthor()->imageId)) ? '/images/layout/no_user_photo.jpg' : $oArticleComment->getAuthor()->getImage()->getImageFileByReference('cms_thumb')->link ?>" width="100" height="100">
                                <div class="commentContent">
                                    <h3><?= $oArticleComment->authorName ?></h3>
                                    <div class="postedOn">Posted on <?= Date::strToDate($oArticle->pubDate)->format('%A, %B %d, %Y') ?></div>
                                    <p><?= $oArticleComment->comment ?></p>
                                    <br>
                                    <div class="fb-like" data-href="<?= getCurrentUrl() . '#comment-' . $oArticleComment->articleCommentId ?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
                                    <form>
                                        <input class="reportButton" data-comment-id="<?= $oArticleComment->articleCommentId ?>" type="submit" name="report" value="REPORT AS INAPPROPRIATE">
                                    </form>
                                    <div class="statusUpdate"></div>
                                </div>
                            </div>
                        <? } ?>
                    </div>
                <? } ?>
                <!--- PAGE WITH RSS FEEDS -->
            <? } elseif (!empty($aRssFeeds)) {
            ?>
                <h1 class="sectionTitle"><?= $oPage->title ?></h1>
                <hr>
                <ul>
                    <? foreach ($aRssFeedItems as $oRssFeedItem) { ?>
                        <li id="rssitem-<?= $oRssFeedItem->rssFeedItemId ?>">
                            <h2 class="feedItemTitle"><?= $oRssFeedItem->title ?></h2>
                            <span class="pubDate"><?= ucfirst(Date::strToDate($oRssFeedItem->pubDate)->format('%B %d, %Y %H:%M')) ?></span> <?= $oRssFeedItem->description ?><br>
                            <a class="readMore no-print" style="display:inline-block" href="<?= $oRssFeedItem->link ?>" target="_blank">Read More</a>
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
                                    echo '<img class="feedFlag" style="border:none !important" src="' . $oLogoFile->link . '" alt="' . $oLogoFile->title . '" height="28" width="308">';
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
                        include TEMPLATES_FOLDER . '/elements/perPage.inc.php';
                        ?>
                    </div>
                    <br class="clear">
                <? } ?>
                <!-- COMMON GROUND -->
            <? } elseif ($oPage->getUrlPath() === '/common-ground') {
            ?>
                <h3><?= $oPage->title ?></h3>
                <hr>
                <ul>
                    <? foreach ($aLinks as $oLink) { ?>
                        <li>
                            <a class="pageLink" href="<?= $oLink->link ?>" target="_blank"><?= $oLink->title ?></a>
                        </li>
                    <? } ?>
                </ul>
                <!-- CONFIRM COMMENT -->
            <? } elseif ($oPage->getUrlPath() === '/confirm-comment') { ?>
                <h3><?= $oPage->title ?></h3>
                <hr>
                <?= $oPage->content ?>
                <div id="confirmComment">
                    <form method="GET" class="validateForm">
                        <? include TEMPLATES_FOLDER . '/elements/errorBox.inc.php'; ?>
                        <label for="email">E-Mail address *</label>
                        <input type="email" name="email" value="" data-rule-required="true" data-msg-required="You must enter your email address" data-rule-email="true" data-msg-email="Enter a valid e-mail address">
                        <label for="code">Confirmation code *</label>
                        <input type="text" name="code" value="" data-rule-required="true" data-msg-required="You must enter the confirmation code">
                        <input type="submit" name="submit" value="Send">
                    </form>
                </div>
                <!-- NORMAL PAGE -->
            <? } else {
            ?>
                <h1 class="sectionTitle"><?= $oPage->title ?></h1>
                <hr>
                <?= $oPage->content ?>
                <br>
                <? if ($oPage->getUrlPath() === '/please-like-us') { ?>
                    <div class="socialShare hide">
                        <div class="fb-like" data-href="<?= CLIENT_HTTPS_URL ?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="true"></div>
                        <a href="https://twitter.com/share" class="twitter-share-button" data-url="<?= CLIENT_HTTPS_URL ?>" data-text="Building the Bridge">Tweet</a>
                        <div class="g-plus" data-action="share" data-annotation="bubble" data-href="<?= CLIENT_HTTPS_URL ?>"></div>
                        <a href="<?= http_session('confirmedCommentUrl', CLIENT_HTTPS_URL) ?>" class="submitLink" target="_self">CONTINUE TO YOUR COMMENT</a>
                    </div>

                <? } ?>
                <br>
                <? if ($oPage->hasSubPages()) { ?>
                    <ul>
                        <? foreach ($oPage->getSubpages() as $oPage) { ?>
                            <li>
                                <a class="pageLink" href="<?= $oPage->getUrlPath() ?>" target="_self"><?= $oPage->title ?></a>
                            </li>
                        <? } ?>
                    </ul>
                <? } ?>
                <?
                $oPageImage = $oPage->getImages('first-online');

                if (!empty($oPageImage)) {
                ?>
                    <img class="articleHeadImage" src="<?= $oPageImage->getImageFileByReference('detail')->link ?>" alt="<?= empty($oPageImage->getImageFileByReference('detail')->title) ? $oPage->title : $oPageImage->getImageFileByReference('detail')->title ?>" <?= $oPageImage->getImageFileByReference('detail')->imageSizeAttr ?>>
                <? }
                ?>

                <? if (!empty($aFiles) || !empty($aLinks)) { ?>
                    <div class="linksAndFiles">
                        <ul>
                            <? foreach ($aFiles as $oFile) { ?>
                                <? if ($oFile->online) { ?>
                                    <li><a class="articleLink" href="<?= $oFile->link ?>" target="_self"><?= $oFile->title ? $oFile->title : $oFile->name ?></a></li>
                                <? } ?>
                            <? } ?>
                            <? foreach ($aLinks as $oLink) { ?>
                                <? if ($oLink->online) { ?>
                                    <li><a class="articleLink" href="<?= $oLink->link ?>" target="<?= getLinkTarget($oLink->link) ?>"><?= $oLink->title ? $oLink->title : $oLink->link ?></a></li>
                                <? } ?>
                            <? } ?>
                        </ul>
                    </div>
                <? } ?>
                <? if (!empty($aImages) || !empty($aYoutubeVideos)) { ?>
                    <div class="photosAndVideos clearfix">
                        <? foreach ($aImages as $oImage) { ?>
                            <? if ($oImage->online && ($oCropSmall = $oImage->getImageFileByReference('crop_small')) && ($oDetail = $oImage->getImageFileByReference('crop_small'))) { ?>
                                <a class="fancyBoxLink articleImage" href="<?= $oImage->getImageFileByReference('detail')->link ?>" data-fancybox-group="page" title="<?= $oCropSmall->title ?>">
                                    <img src="<?= $oCropSmall->link ?>" alt="<?= $oCropSmall->title ?>" <?= $oCropSmall->imageSizeAttr ?> />

                                </a>
                            <? } ?>
                        <? } ?>
                        <? foreach ($aYoutubeVideos as $oYoutubeVideo) { ?>
                            <? if ($oYoutubeVideo->online) { ?>
                                <a class="youtubeVideo fancyBoxLink fancybox.iframe" href="<?= $oYoutubeVideo->getEmbedLink() ?>" title="<?= $oYoutubeVideo->title ?>">
                                    <img class="playVideo" src="/images/layout/play-video.png" width="68" height="47" alt="Play Video" />
                                    <img src="<?= $oYoutubeVideo->getThumbLink(0) ?>" />
                                </a>
                            <? } ?>
                        <? } ?>
                    </div>
                <? } ?>
            <? } ?>
        </div>
        <? include TEMPLATES_FOLDER . '/elements/rightColumn.inc.php'; ?>
    </article>
    <!-- /MAIN CONTENT -->
</section>
<?
$sURL = getCurrentUrl();
$sJavascript = '';

if (!empty($oArticle)) {
    $sJavascript = <<<EOT
var opened = false;
            
$('.aboutTheAuthor a.readMore').click(
    function (e) {
        if (!opened) {
            $('.authorDescription').stop(true, false).animate({height:'100%'});
            opened = true;
        }
        else {
            $('.authorDescription').stop(true, false).animate({height:'82px'});
            opened = false;
        }
        
        e.preventDefault();
    }
);        
EOT;
}

if ($oPage->getUrlPath() === '/the-great-debate' || $oPage->getUrlPath() === '/common-ground') {
    $sJavascript .= <<<EOT
            $('#articleCategoryId').change(function () {
                $('#categoryForm').submit();
            });
            
            $('.reportButton').click(function (e) {                
                $.ajax({
                    type: "POST",
                    url: "$sURL",
                    data: 'action=reportComment&articleCommentId=' + $(this).data('comment-id'),
                    async: true,
                    success: function(data){
                        var dataObj = eval('(' + data + ')');

                        /* On success */
                        if (dataObj.success == true){   
                            showStatusUpdate("The comment has beeen reported to the website administration, Thanks for your collaboration");                           
                        } else {
                            showStatusUpdate("Comment cannot be reported succesfully. Try again later, please");
                        }
                    }
                });
                e.preventDefault();
            });
EOT;
}

if ($oPage->getUrlPath() === '/world-leaders') {
    $sJavascript .= "

        $('#contentType').change(
                function (e) {
                    $('#contentTypeForm').submit();
                }
        );";
}

$oPageLayout->addJavascriptBottom('<script>' . $sJavascript . '</script>');
?>