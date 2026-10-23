<div id="topOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<h1><?= sysTranslations::get('articles_change_structure') ?></h1>
<p>
    <i><?= sysTranslations::get('articles_drag_title') ?></i>
</p>
<div id="nestedSortableContainer">
    <?
# list pages in unordered list

    function makeListTree($aArticles) {
        if (count($aArticles) > 0)
            echo '<ol class="nestedSortable cursorMove level0" >';


        $iT = 0;
        foreach ($aArticles AS $oArticle) {
            echo '<li id="article_' . $oArticle->articleId . '">';
            echo '<div class="no-action-icons ' . ($iT % 2 == 0 ? 'even' : 'odd') . '">';
            echo _e($oArticle->getShortTitle());
            echo '</div>';           
            echo '</li>';
            $iT++;
        }
        
        if (count($aArticles) > 0)
            echo '</ol>';
    }

# start recursive displaying pages
    makeListTree($aArticles);
    ?>
</div>
<form action="" method="POST" onsubmit="return setArticleStructure();" id="articleStructureForm">
    <input type="hidden" name="action" value="saveArticleStructure" />
    <input type="hidden" name="articleStructure" id="articleStructure" value="" />
    <input type="submit" value="<?= sysTranslations::get('global_save') ?>" /> <input type="button" value="<?= sysTranslations::get('global_reset') ?>" onclick="window.location.reload(); return false;" />
</form>
<div id="bottomOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<?
# add nested sortable javascript source code
$oPageLayout->addJavascriptBottom('<script src="' . ADMIN_JS_FOLDER . '/jquery-ui-nestedSortable.min.js"></script>');

# add nested sortable javascript initiation code
$sNestedSortableJavascript = <<<EOT
<script>
    $('ol.nestedSortable').nestedSortable({
        disableNesting: 'no-nesting',
        forcePlaceholderSize: true,
        handle: 'div',
        helper: 'clone',
        listType: 'ol',
        items: 'li:not(.locked)',
        cancel: '.not-sortable',
        maxLevels: 1,
        opacity: .6,
        placeholder: 'placeholder',
        revert: 250,
        tabSize: 25,
        tolerance: 'pointer',
        toleranceElement: '> div',
        rootClass: 'root-item'
        }).disableSelection();

    function setArticleStructure(){
        serialized = $('ol.nestedSortable').nestedSortable('serialize');
        $("#articleStructure").val(serialized);
        return true;
    }
</script>
EOT;
$oPageLayout->addJavascriptBottom($sNestedSortableJavascript);
?>