<table class="sorted withActionIcons">
    <thead>
        <tr class="topRow">
            <td colspan="5"><h2><?= sysTranslations::get('articles_all_categories') ?></h2><div class="right"><a class="addBtn textLeft" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/toevoegen" title="<?= sysTranslations::get('articles_category_add') ?>" alt="<?= sysTranslations::get('articles_category_add') ?>"><?= sysTranslations::get('articles_category_add') ?></a><!--<br /><a class="changeOrderBtn textLeft right" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/volgorde-wijzigen" title="<?= sysTranslations::get('global_change_order') ?>" alt="<?= sysTranslations::get('global_change_order') ?>"><?= sysTranslations::get('global_change_order') ?></a>--></div></td>
        </tr>
        <tr>
            <th class="{sorter:false} nonSorted onlineOffline">&nbsp;</th>
            <th><?= sysTranslations::get('articles_category_name') ?></th>
            <th><?= sysTranslations::get('pages_page') ?></th>            
            <th><?= sysTranslations::get('global_parent') ?></th>            
            <th class="{sorter:false} nonSorted" style="width: 60px;">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($aArticleCategories AS $oArticleCategory) {
            echo '<tr>';
            echo '<td>';
            # online offline button
            echo '<a id="articleCategory_' . $oArticleCategory->articleCategoryId . '_online_1" title="'.sysTranslations::get('articles_category_set_offline_tooltip').'" class="action_icon ' . ($oArticleCategory->online ? '' : 'hide') . ' online_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-setOnline/' . $oArticleCategory->articleCategoryId . '/?online=0"></a>';
            echo '<a id="articleCategory_' . $oArticleCategory->articleCategoryId . '_online_0" title="'.sysTranslations::get('articles_category_set_online_tooltip').'" class="action_icon ' . ($oArticleCategory->online ? 'hide' : '') . ' offline_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-setOnline/' . $oArticleCategory->articleCategoryId . '/?online=1"></a>';
            echo '</td>';
            echo '<td>' . _e($oArticleCategory->name) . '</td>';
            echo '<td>' . _e($oArticleCategory->getPage()->title) . '</td>';            
            echo '<td>' . _e($oArticleCategory->getParent() == null ? '': $oArticleCategory->getParent()->name) . '</td>';            
            echo '<td>';
            echo '<a class="action_icon edit_icon" title="'.sysTranslations::get('articles_category_edit').'" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oArticleCategory->articleCategoryId . '"></a>';
            
            if ($oArticleCategory->isDeletable()) {
                echo '<a class="action_icon delete_icon" title="'.sysTranslations::get('articles_category_delete').'" onclick="return confirmChoice(\'' . _e($oArticleCategory->name) . '\');" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/verwijderen/' . $oArticleCategory->articleCategoryId . '"></a>';
            } else {
                echo '<span class="action_icon delete_icon grey" title="'.sysTranslations::get('articles_category_not_deletable').'"></span>';
            }
            
            echo '</td>';
            echo '</tr>';
        }
        if (empty($aArticleCategories)) {
            echo '<tr><td colspan="4"><i>'.sysTranslations::get('articles_no_categories').'</i></td></tr>';
        }
        ?>
    </tbody>
</table>
<?php
# create necessary javascript
$sArticleCatOnlineMsg = sysTranslations::get('articles_category_online');
$sArticleCatOfflineMsg = sysTranslations::get('articles_category_offline');
$sArticleCatnotchangedMsg = sysTranslations::get('articles_category_not_changed');
$sController = http_get('controller');
$sBottomJavascript = <<<EOT
<script>
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
                    $("#articleCategory_"+dataObj.articleCategoryId+"_online_0").hide(); // hide offline button
                    $("#articleCategory_"+dataObj.articleCategoryId+"_online_1").hide(); // hide online button
                    $("#articleCategory_"+dataObj.articleCategoryId+"_online_"+dataObj.online).css('display', 'inline-block'); // show button based on online value
                    if(dataObj.online == 0)    
                        showStatusUpdate("$sArticleCatOfflineMsg");
                    if(dataObj.online == 1)    
                        showStatusUpdate("$sArticleCatOnlineMsg");                            
                } else {
                    showStatusUpdate("$sArticleCatnotchangedMsg");
                }
            }
        });
        e.preventDefault();
    });
</script>
EOT;
$oPageLayout->addJavascriptBottom($sBottomJavascript);
?>