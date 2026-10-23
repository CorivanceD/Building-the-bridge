<form action="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>" method="POST">
    <input type="hidden" name="filterForm" value="1" />
    <fieldset style="margin-bottom: 20px;">
        <legend><?= sysTranslations::get('global_filter')?></legend>
        <table class="withForm">
            <tr>
                <td class="withLabel" style="width: 116px;"><?= sysTranslations::get('global_filter_text')?> <div class="hasTooltip tooltip" title="">&nbsp;</div></td>
                <td><input type="text" name="articleCommentFilter[q]" value="<?= $aArticleCommentFilter['q'] ?>" /></td>
            </tr>
            <tr>
                <td class="withLabel" style="width: 116px;"><?= sysTranslations::get('global_show_offline')?></td>
                <td><input type="checkbox" name="articleCommentFilter[showAll]" value="true" <?= !empty($aArticleCommentFilter['showAll']) ? 'checked' : '' ?>/></td>                    
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><input type="submit" name="filterArticleComments" value="<?= sysTranslations::get('articles_filter_comments')?>" /> <input type="submit" name="resetFilter" value="<?= sysTranslations::get('global_reset_filter')?>" /></td>
            </tr>
        </table>
    </fieldset>
</form>
<table class="sorted">
    <thead>
        <tr class="topRow">
            <td colspan="7"><h2><?= sysTranslations::get('articles_comments_found')?> (<?= (count($aArticleComments) != 0 ? ($iStart + 1) : $iStart) . '-' . ($iStart + count($aArticleComments)) ?>/<?= $iFoundRows ?>)</h2><div class="right"></div></td>
        </tr>
        <tr>
            <th class="{sorter:false} nonSorted onlineOffline"></th>
            <th style="width:90px"><?= sysTranslations::get('global_date')?></th>
            <th style="width:300px"><?= sysTranslations::get('articles_article')?></th>
            <th style="width:90px"><?= sysTranslations::get('global_author')?></th>
            <th style="width:300px"><?= sysTranslations::get('global_description')?></th>
            <th><?= sysTranslations::get('articles_comment_inappropriate')?></th>
            <th class="{sorter:false} nonSorted" style="width: 60px;">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?
        foreach ($aArticleComments AS $oArticleComment) {
            echo '<tr>';
            echo '<td>';
            # online offline button
            echo '<a id="articles_' . $oArticleComment->articleCommentId . '_online_1" title="Reactie offline zetten" class="action_icon ' . ($oArticleComment->online ? '' : 'hide') . ' online_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-setOnline/' . $oArticleComment->articleCommentId . '/?online=0"></a>';
            echo '<a id="articles_' . $oArticleComment->articleCommentId . '_online_0" title="Reactie online zetten" class="action_icon ' . ($oArticleComment->online ? 'hide' : '') . ' offline_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-setOnline/' . $oArticleComment->articleCommentId . '/?online=1"></a>';
            echo '</td>';
            echo '<td>' . Date::strToDate($oArticleComment->postedOn)->format('%d-%m-%Y %H:%M') . '</td>';
            echo '<td>' . firstXWords($oArticleComment->getArticle()->title, 30) . '</td>';
            echo '<td>' . $oArticleComment->authorName . '</td>';
            echo '<td>' . firstXCharacters($oArticleComment->comment, 30) . '...</td>';
            echo '<td>' . ($oArticleComment->inappropriate == 1 ? 'Yes' : 'No') . '</td>';
            echo '<td>';
            echo '<a class="action_icon magnifying_glass_icon" title="View comment" href="'.$oArticleComment->getArticle()->getUrlPath(). '#comment-'.$oArticleComment->articleCommentId.'" target="_blank"></a>';
            echo '<a class="action_icon delete_icon" title="' . sysTranslations::get('articles_comment_delete') . '" onclick="return confirmChoice(\'' . firstXWords($oArticleComment->comment, 10) . '\');" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/verwijderen/' . $oArticleComment->articleCommentId . '"></a>';            
            echo '</td>';
            echo '</tr>';
        }
        if (empty($aArticleComments)) {
            echo '<tr><td colspan="7"><i>'.sysTranslations::get('articles_no_comments').'</i></td></tr>';
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
                        <option value=""><?= sysTranslations::get('global_all')?></option>
                        <option <?= $iPerPage == 10 ? 'SELECTED' : '' ?> value="10">10</option>
                        <option <?= $iPerPage == 25 ? 'SELECTED' : '' ?> value="25">25</option>
                        <option <?= $iPerPage == 50 ? 'SELECTED' : '' ?> value="50">50</option>
                        <option <?= $iPerPage == 100 ? 'SELECTED' : '' ?> value="100">100</option>
                    </select> <?= sysTranslations::get('global_per_page')?>
                </form>
            </td>
        </tr>
    </tfoot>
</table>
<?
# create necessary javascript
$sController = http_get('controller');
$sArticleCommentsOnline = sysTranslations::get('articles_online');
$sArticleCommentsOffline = sysTranslations::get('articles_offline');
$sArticleCommentsStatusNotChanged = sysTranslations::get('articles_status_not_changed');
        
$sBottomJavascript = <<<EOT
<script type="text/javascript">
    $("a.online_icon, a.offline_icon").click(function(e){
        $.ajax({
            type: "GET",
            url: $(this).attr('href'),
            data: 'ajax=1',
            async: true,
            success: function(data){
                var dataObj = eval('(' + data + ')');

                /* On success */
                if (dataObj.success == true){
                    $("#articles_"+dataObj.articleCommentId+"_online_0").hide(); // hide offline button
                    $("#articles_"+dataObj.articleCommentId+"_online_1").hide(); // hide online button
                    $("#articles_"+dataObj.articleCommentId+"_online_"+dataObj.online).css('display', 'inline-block'); // show button based on online value
                    if(dataObj.online == 0)    
                        showStatusUpdate("$sArticleCommentsOffline");
                    if(dataObj.online == 1)    
                        showStatusUpdate("$sArticleCommentsOnline");                            
                } else {
                    showStatusUpdate("$sArticleCommentsStatusNotChanged");
                }
            }
        });
        e.preventDefault();
    });
</script>
EOT;
$oPageLayout->addJavascriptBottom($sBottomJavascript);
?>