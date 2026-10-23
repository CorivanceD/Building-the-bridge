<form action="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>" method="POST">
    <input type="hidden" name="filterForm" value="1" />
    <fieldset style="margin-bottom: 20px;">
        <legend><?= sysTranslations::get('global_filter') ?></legend>
        <table class="withForm">
            <tr>
                <td class="withLabel" style="width: 116px;"><?= sysTranslations::get('global_search_word') ?></td>
                <td><input type="text" name="articleFilter[q]" value="<?= _e($aArticleFilter['q']) ?>" /></td>
            </tr>
            <tr>
                <td class="withLabel"><?= sysTranslations::get('global_author') ?></td>
                <td>
                    <select name="articleFilter[authorId]">
                        <option value="">-- <?= sysTranslations::get('global_all') ?> --</option>
                        <?php foreach (UserManager::getUsersByFilter() AS $oUser) { ?>
                            <option value="<?= $oUser->userId ?>" <?= (isset($aArticleFilter['authorId']) && ($oUser->userId == $aArticleFilter['authorId'])) ? 'selected' : '' ?> ><?= $oUser->name ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>            
            <tr>
                <td class="withLabel"><label><?= ucfirst(sysTranslations::get('global_status')) ?></label></td>
                <td>
                    <select name="articleFilter[status]">
                        <option value="">-- <?= sysTranslations::get('global_all') ?> --</option>                                                
                        <option value="<?= Article::ARTICLE_STATUS_REVIEW ?>" <?= $aArticleFilter['status'] == Article::ARTICLE_STATUS_REVIEW ? 'selected' : '' ?>><?= sysTranslations::get('articles_status_review') ?>
                        <option value="<?= Article::ARTICLE_STATUS_PUBLISHED ?>" <?= $aArticleFilter['status'] == Article::ARTICLE_STATUS_PUBLISHED ? 'selected' : '' ?>><?= sysTranslations::get('articles_status_published') ?>
                        <option value="<?= Article::ARTICLE_STATUS_OFFLINE ?>" <?= $aArticleFilter['status'] == Article::ARTICLE_STATUS_OFFLINE ? 'selected' : '' ?>><?= sysTranslations::get('articles_status_offline') ?>
                        <option value="<?= Article::ARTICLE_STATUS_REJECTED ?>" <?= $aArticleFilter['status'] == Article::ARTICLE_STATUS_REJECTED ? 'selected' : '' ?>><?= sysTranslations::get('articles_status_rejected') ?>                            
                    </select>
                </td>                
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><input type="submit" name="filterArticles" value="<?= sysTranslations::get('letters_filter') ?>" /> <input type="submit" name="resetFilter" value="<?= sysTranslations::get('global_reset_filter') ?>" /></td>
            </tr>
        </table>
    </fieldset>
</form>
<table class="sorted">
    <thead>
        <tr class="topRow">
            <td colspan="7">
                <h2><?= sysTranslations::get('letters_all') ?></h2>
                <div class="right" style="text-align:right">                    
                    <a class="changeOrderBtn textLeft" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/structuur-wijzigen"><?= sysTranslations::get('letters_change_structure') ?></a>
                </div>                
            </td>
        </tr>
        <tr>
            <th style="width: 100px;">#</th>
            <th style="width: 100px;"><?= sysTranslations::get('global_date') ?></th>
            <th ><?= sysTranslations::get('global_title') ?></th>
            <th ><?= sysTranslations::get('global_author') ?></th>
            <th ><?= sysTranslations::get('global_addressee') ?></th>
            <th ><?= sysTranslations::get('global_status') ?></th>
            <th class="{sorter:false} nonSorted" style="width: 90px;">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($aArticles AS $oArticle) {
            echo '<tr>';
            echo '<td>' . $oArticle->order . '</td>';
            echo '<td>' . Date::strToDate($oArticle->created)->format('%d-%m-%Y') . '</td>';
            echo '<td>' . _e($oArticle->title) . '</td>';
            echo '<td>' . _e($oArticle->getAuthor()->name) . '</td>';
            echo '<td>' . _e($oArticle->getWorldLeaderLetter()->getWorldLeader()->name) . '</td>';
            echo '<td><select id="status-' . $oArticle->articleId . '"class="articleStatus" data-article-title="' . $oArticle->title . '" data-author="' . $oArticle->getAuthor()->name . '" data-article-id="' . $oArticle->articleId . '" data-anonymous="' . ($oArticle->authorId == User::ANONYMOUS_USER_ID ? '1' : '0') . '">             
            <option value="' . Article::ARTICLE_STATUS_REVIEW . '" ' . ($oArticle->status == Article::ARTICLE_STATUS_REVIEW ? 'selected' : '') . '>' . sysTranslations::get('articles_status_review') .
            '<option value="' . Article::ARTICLE_STATUS_PUBLISHED . '" ' . ($oArticle->status == Article::ARTICLE_STATUS_PUBLISHED ? 'selected' : '') . '>' . sysTranslations::get('articles_status_published') .
            '<option value="' . Article::ARTICLE_STATUS_REJECTED . '" ' . ($oArticle->status == Article::ARTICLE_STATUS_REJECTED ? 'selected' : '') . '>' . sysTranslations::get('articles_status_rejected') .
            '<option value="' . Article::ARTICLE_STATUS_OFFLINE . '" ' . ($oArticle->status == Article::ARTICLE_STATUS_OFFLINE ? 'selected' : '') . '>' . sysTranslations::get('articles_status_offline') .
            '</select></td>';
            echo '<td>';
            echo '<a class="action_icon magnifying_glass_icon" title="' . sysTranslations::get('articles_review') . '" href="' . $oArticle->getUrlPath() . '" target="_blank"></a>';
            echo '<a title="Article edition" class="action_icon edit_icon" href="' . ADMIN_FOLDER . '/correspondenties-wereldleiders/bewerken/' . $oArticle->articleId . '/' . http_get('controller') . '"></a>';
            echo '<a class="action_icon delete_icon" title="' . sysTranslations::get('articles_delete') . '" onclick="return confirmChoice(\'' . $oArticle->title . '\');" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/verwijderen/' . $oArticle->articleId . '"></a>';
            echo '</td>';
            echo '</tr>';
        }
        if (count($aArticles) == 0) {
            echo '<tr><td colspan="7"><i>' . sysTranslations::get('articles_no_articles') . '</i></td></tr>';
        }
        ?>
    </tbody>
    <tfoot>
        <tr class="bottomRow">
            <td colspan="7">
                <form method="POST">
                    <?= generatePaginationHTML($iPageCount, $iCurrPage) ?>
                    <input type="hidden" name="setPerPage" value="1" />
                    <select name="perPage" onchange="$(this).closest('form').submit();">
                        <option value=""><?= sysTranslations::get('global_all') ?></option>
                        <option <?= $iPerPage == 10 ? 'SELECTED' : '' ?> value="10">10</option>
                        <option <?= $iPerPage == 25 ? 'SELECTED' : '' ?> value="25">25</option>
                        <option <?= $iPerPage == 50 ? 'SELECTED' : '' ?> value="50">50</option>
                        <option <?= $iPerPage == 100 ? 'SELECTED' : '' ?> value="100">100</option>
                    </select> <?= sysTranslations::get('global_per_page') ?>
                </form>
            </td>
        </tr>
    </tfoot>
</table>

<?php
$sFancyboxContent = <<<EOT
<div id="editorMessage">
    <form id="messageForm" method="POST">
        <input type="hidden" name="action" value="updateStatus" />
        <input type="hidden" id="articleId" name="articleId" value="" />
        <input type="hidden" id="statusCode" name="status" value="" />
        <table class="editorForm validateForm">
            <tr>
                <td><label for="subject">Subject</label></td>
                <td><input id="subject" name="subject" type="text" value="" /></td>                
            </tr>
            <tr>
                <td><label for="to">To</label></td>
                <td><input id="to" name="to"  type="text" value="" /></td>                
            </tr>   
            <tr>
                <td><label for="message">Message</label></td>
                <td><textarea cols="50" rows="5" class="{validate:{required:true}}" name="message" id="message"></textarea></td>                
            </tr>
            <tr>
                <td>
                    
                </td>
                <td style="text-align: right">
                    <input type="submit" name="send" value="Send"> &nbsp;
                    <input type="button" id="cancel" name="cancel" value="Cancel">
                </td>
            </tr>
        </table>
    </form>
</div>
EOT;
$sJavascript = "
<script>
    var prevStatus = '';
    
    $('.articleStatus').focus(function() {
        prevStatus = $(this).val();
    });
    
    $('.articleStatus').change(
        function (e) {
            var statusName = '';  
            
            switch ($(this).val()) {
                case '" . Article::ARTICLE_STATUS_REVIEW . "':
                    statusName = 'To Review';
                    break;
                case '" . Article::ARTICLE_STATUS_PUBLISHED . "':
                    statusName = 'Published';
                    break;
                case '" . Article::ARTICLE_STATUS_REJECTED . "':
                    statusName = 'Rejected';
                    break;
                case '" . Article::ARTICLE_STATUS_OFFLINE . "':
                    statusName = 'Offline';
                    break;                    
            }
            
            if (!$(this).data('anonymous')) {
                $.fancybox.open({
                    content: '" . str_replace("\r\n", " ", $sFancyboxContent) . "',
                    modal:true
                });
                $('#statusCode').val($(this).val());                            
                $('#articleId').val($(this).data('article-id'));
                $('#to').val($(this).data('author'));
                $('#subject').val('Open letter titled \'' + $(this).data('article-title') + '\' status changed to \''+ statusName + '\'');
                $('#cancel').click(
                    function(e) {
                        var articleId = $('#articleId').val();
                        $('#status-' + articleId).val(prevStatus);
                        $.fancybox.close();
                    }
                );   
            }         
            
            if ($(this).data('anonymous')) {
                $.fancybox.open({
                    content: '" . str_replace("\r\n", " ", $sFancyboxContent) . "',
                    modal:true
                });
                $('#statusCode').val($(this).val());                            
                $('#articleId').val($(this).data('article-id'));
                $('#to').val($(this).data('author'));
                $('#subject').val('Open letter titled \'' + $(this).data('article-title') + '\' status changed to \''+ statusName + '\'');
                $('#message').val('XXXX');
                $('#messageForm').submit();
            }
            
         
    });
    

</script>";

$oPageLayout->addJavascriptBottom($sJavascript);
?>