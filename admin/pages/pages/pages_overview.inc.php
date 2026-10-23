<div class="likeTopRow cf">
    <h2><?= sysTranslations::get('pages_all') ?></h2>
    <a class="addBtn textLeft" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/toevoegen"><?= sysTranslations::get('pages_add_main') ?></a><br />
    <a class="changeOrderBtn textLeft" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/structuur-wijzigen"><?= sysTranslations::get('pages_change_structure') ?></a>
</div>
<?
# list pages in unordered list

function makeListTree($aPages, $iLevel, $iMaxLevels) {
    if (count($aPages) > 0 && $iLevel == 1)
        echo '<ol class="nestedSortable level' . $iLevel . '">';
    elseif (count($aPages) > 0)
        echo '<ol class="level' . $iLevel . '">';

    $iT = 0;
    foreach ($aPages AS $oPage) {
        echo '<li id="page_' . $oPage->pageId . '">';

        $sClasses = '';
        if ($iT > 0 && $iLevel == 1)
            $sClasses .= ' mainPage';
        if ($iT == 0 && $iLevel == 1)
            $sClasses .= ' first-mainPage';
        if ($iT > 0 && $iLevel > 1)
            $sClasses .= ' sub';
        if ($iT == 0 && $iLevel > 1)
            $sClasses .= ' first-sub';

        # add sub page
        echo '<div class="' . $sClasses . (($iLevel < $iMaxLevels && $oPage->mayHaveSub()) ? '"><a class="action_icon add_icon" alt="'.sysTranslations::get('pages_add_sub').'" title="'.sysTranslations::get('pages_add_sub_tooltip').'" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/toevoegen?parentPageId=' . $oPage->pageId . '"></a>' : ' no-action-icons">');

        echo $oPage->getShortTitle() ? $oPage->getShortTitle() : $oPage->title . ($oPage->getInMenu() ? '' : '<span class="brackedComment"> ('.sysTranslations::get('not_shown_in_menu').')</span>');

        echo '<div class="actionIconsHolder">';

        if ($oPage->isOnlineChangeable()) {
            # online offline button
            echo '<a id="page_' . $oPage->pageId . '_online_1" pageTitle="' . _e($oPage->title) . '" title="'.sysTranslations::get('pages_offline_tooltip').'" class="action_icon ' . ($oPage->online ? '' : 'hide') . ' online_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-setOnline/' . $oPage->pageId . '/?online=0"></a>';
            echo '<a id="page_' . $oPage->pageId . '_online_0" pageTitle="' . _e($oPage->title) . '" title="'.sysTranslations::get('pages_online_tooltip').'" class="action_icon ' . ($oPage->online ? 'hide' : '') . ' offline_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-setOnline/' . $oPage->pageId . '/?online=1"></a>';
        } else {
            echo '<span title="'.sysTranslations::get('pages_not_offline').'" class="action_icon online_icon grey"></span>';
        }
        #edit button
        if ($oPage->isEditable())
            echo '<a title="'.sysTranslations::get('pages_edit').'" class="action_icon edit_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oPage->pageId . '"></a>';
        else
            echo '<a title="'.sysTranslations::get('pages_not_editable').'" class="action_icon edit_icon grey" href="#"></a>';

        # delete button
        if ($oPage->isDeletable())
            echo '<a onclick="return confirmChoice(\''. $oPage->title . '\');" class="action_icon delete_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/verwijderen/' . $oPage->pageId . '"></a>';
        else
            echo '<a class="action_icon delete_icon grey" href="#" title="'.sysTranslations::get('pages_not_deletable').'"></a>';

        echo '</div>';
        echo '</div>';

        makeListTree($oPage->getSubPages('all'), $iLevel + 1, $iMaxLevels); //call function recursive
        echo '</li>';
        $iT++;
    }
    if (count($aPages) > 0)
        echo '</ol>';
}

if (count($aAllLevel1Pages) == 0) {
    echo '<div class="likeSorterTr"><i>'.sysTranslations::get('pages_no_pages').'</i></div>';
}

# start recursive displaying pages
makeListTree($aAllLevel1Pages, 1, $iMaxLevels);
?>
<?
$sPageOnlineMsg = sysTranslations::get('pages_online');
$sPageOfflineMsg = sysTranslations::get('pages_offline');
$sPageNotChangedMsg = sysTranslations::get('pages_not_changed');
# add ajax code for online/offline handling
$sNestedSortableJavascript = <<<EOT
<script>
    $("a.online_icon, a.offline_icon").click(function(e){
        $.ajax({
            type: "GET",
            url: this.href,
            data: "ajax=1",
            async: true,
            success: function(data){
                var dataObj = eval('(' + data + ')');

                /* On success */
                if(dataObj.success == true){
                    $("#page_"+dataObj.pageId+"_online_0").hide(); // hide offline button
                    $("#page_"+dataObj.pageId+"_online_1").hide(); // hide online button
                    $("#page_"+dataObj.pageId+"_online_"+dataObj.online).css('display', 'inline-block'); // show button based on online value
                    if(dataObj.online == 0)
                        showStatusUpdate("$sPageOfflineMsg");
                    if(dataObj.online == 1)
                        showStatusUpdate("$sPageOnlineMsg");
                }else{
                        showStatusUpdate("$sPageNotChangedMsg");
                }
            }
        });
        e.preventDefault();
    });
</script>
EOT;
$oPageLayout->addJavascriptBottom($sNestedSortableJavascript);
?>