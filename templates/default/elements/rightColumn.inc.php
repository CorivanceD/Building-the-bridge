<div class="column-width float-left hide-on-mobile no-print">&nbsp;</div>
<div class="column-33 float-left rightColumn no-print">
    <h3>LATEST OPEN LETTERS</h3>
    <hr>
    <ul class="worldLeaders">
        <?
        $iFoundRows = false;

        foreach (ArticleManager::getArticlesByFilter(array('articleCategoryId' => ArticleCategory::WORLD_LEADER_LETTERS_CATEGORY, 'status' => Article::ARTICLE_STATUS_PUBLISHED, 'parentPageId' => Page::WORLD_LEADER_PAGE), 10, 0, $iFoundRows, array('pubDate' => 'DESC')) as $oArticle) {
        ?>
            <li>
                <div class="pubDate"><?= Date::strToDate($oArticle->pubDate)->format('%d-%m') ?></div><a href="<?= $oArticle->getUrlPath() ?>"><?= $oArticle->title ?></a>
            </li>
        <? } ?>
        <a class="button" href="/send-letter">VIRTUAL POST OFFICE</a>
    </ul>

    <h3 style="clear:both">PETITIONS</h3>
    <hr>
    <div class="worldLeaders">
        <a class="button" href="/petitions">PETITIONS</a>
        <br />
        <a class="button" href="/petitions/new">START PETITION</a>
        <br />
    </div>

    <h3 style="clear:both">LINKS</h3>
    <hr>
    <div class="worldLeaders">
        <a class="button" href="/links/share-link">SHARE YOUR LINK</a>
        <br>
    </div>

    <h3>DONATION</h3>
    <hr>
    <div class="block helpUsDonate">
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
            <input type="hidden" name="lc" value="GB">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="8YS28UJT52BQA">
            <input class="button" type="submit" name="submit" value="PLEASE DONATE">
        </form>
    </div>
    <h3>Latest Blog Articles</h3>
    <hr>
    <ul class="latestArticles">
        <?
        $iFoundRows = false;

        foreach (ArticleManager::getArticlesByFilter(array('notLetters' => 1, 'status' => Article::ARTICLE_STATUS_PUBLISHED, 'notParentPageId' => Page::WORLD_LEADER_PAGE), 11, 0, $iFoundRows, array('pubDate' => 'DESC')) as $oArticle) {
        ?>
            <li>
                <div class="pubDate"><?= Date::strToDate($oArticle->pubDate)->format('%d-%m') ?></div><a href="<?= $oArticle->getUrlPath() ?>"><?= $oArticle->title ?></a>
            </li>
        <? } ?>
    </ul>
    <div class="hide">

        <h3>Latest Comments</h3>
        <hr>
        <ul class="comments">
            <? foreach (ArticleManager::getArticleCommentsByFilter(array(), 7) as $oArticleComment) { ?>
                <li><img class="commentAvatar" src="<?= (empty($oArticleComment->authorId) || empty($oArticleComment->getAuthor()->imageId)) ? '/images/layout/no_user_photo.jpg' : $oArticleComment->getAuthor()->getImage()->getImageFileByReference('cms_thumb')->link ?>">
                    <a href="<?= $oArticleComment->getArticle()->getUrlPath() ?>#comment-<?= $oArticleComment->articleCommentId ?>">
                        <span class="commentDesc"><?= firstXWords($oArticleComment->comment, 10); ?></span>
                        <br>
                        <span class="commentFirm">Comment by <?= $oArticleComment->authorName ?></span>
                    </a>
                </li>
            <? } ?>
        </ul>
    </div>
    <?
    $oChat = ChatManager::getActiveChat();
    if (!empty($oChat)) {
    ?>
        <div id="liveChat" class="hide">
            <h3>LIVE CHAT</h3>
            <hr>
            <? if ($oChat->opened) { ?>
                <h3 class="chatTopic"><span id="topic"><?= $oChat->topic ?></span></h3>
                <div id="chatPannel">
                </div>
                <div id="chatControls">
                    <form method="POST" action="/chat">
                        <? if (empty($oCurrentCustomer)) { ?>
                            <input type="hidden" name="action" value="loginFirst">
                        <?
                        } else {
                        ?>
                            <input type="hidden" name="action" value="sendMessage">
                        <?
                        }
                        ?>
                        <input type="text" id="message" name="message" value="" maxlength="144">
                        <input type="submit" id="sendMessage" name="send" value="Send">
                    </form>
                </div>
            <? } else { ?>
                <div class="block">
                    <?= $oChat->closedMessage ?>
                </div>
            <? } ?>
        </div>
    <? } ?>
    <br>
    <br>
    <!-- <div  class="facebook_container">
        <div class="fb-like-box " data-href="<?= Settings::get("socialMediaFacebook") ?>" data-colorscheme="light" data-show-faces="true" data-header="false" data-stream="false" data-show-border="false" ></div>
    </div>  -->

</div>
<?
$sJavascript = '<script>';

if (!empty($oCurrentCustomer)) {
    $sJavascript .= <<<EOT

        $('#sendMessage').click(
            function (e) {
                $.ajax({
                    type: 'POST',
                    url: '/chat',
                    data: 'ajax=1&action=sendMessage&message=' + encodeURIComponent($('#message').val()),
                    success: function(data) {
                        $('#message').val('');
                        var dataObj = eval("(" + data + ")");   
                            
                    }
                });     
                e.preventDefault();
                
            }
        );

        $('#message').keypress(
            function (e) {
                if (e.which == 13) {
                    $.ajax({
                        type: 'POST',
                        url: '/chat',
                        data: 'ajax=1&action=sendMessage&message=' + encodeURIComponent($('#message').val()),
                        success: function(data) {
                            $('#message').val('');
                            var dataObj = eval("(" + data + ")");                    
                        }
                    });   
                    e.preventDefault();
                }                 
            }
        );
;
EOT;
}

$sJavascript .= <<<EOT
var interval = setInterval(
            function () {
                $.ajax({
                    type: 'POST',
                    url: '/chat',
                    data: 'action=ajax-update',
                    success: function(data) {
                        var dataObj = eval("(" + data + ")");  
                            
                        if (dataObj.closed == true) {
                            $('#liveChat').css('display', 'none');
                            clearInterval(interval);
                        }
                        else {
                            $('#chatPannel').html(dataObj.lastMessages);
                            $('#chatPannel').scrollTop($("#chatPannel").get(0).scrollHeight);  
                            $('#topic').html(dataObj.topic); 
                        }
                    }
                });          
            }   
        , 1000
        );        
EOT;


$sJavascript .= "
    $('.donate, .donateLink').click(
        function(e) {
            e.preventDefault(); 
            $('#donateForm').submit();
        }
    );
</script>";


$oPageLayout->addJavascriptBottom($sJavascript);
?>