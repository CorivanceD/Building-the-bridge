<form action="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>" method="POST">
    <input type="hidden" name="filterForm" value="1" />
    <fieldset style="margin-bottom: 20px;">
        <legend><?= sysTranslations::get('global_filter') ?></legend>
        <table class="withForm">
            <tr>
                <td class="withLabel" style="width: 116px;"><?= sysTranslations::get('global_search_word') ?></td>
                <td><input type="text" name="rssFeedFilter[q]" value="<?= _e($aRssFeedFilter['q']) ?>" /></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><input type="submit" name="filterRssFeeds" value="<?= sysTranslations::get('rss_feed_filter') ?>" /> <input type="submit" name="resetFilter" value="<?= sysTranslations::get('global_reset_filter') ?>" /></td>
            </tr>
        </table>
    </fieldset>
</form>
<table class="sorted">
    <thead>
        <tr class="topRow">
            <td colspan="3"><h2><?= sysTranslations::get('rss_feed_all') ?></h2><div class="right"><a class="addBtn textLeft" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/toevoegen" title="<?= sysTranslations::get('rss_add_tooltip') ?>" alt="<?= sysTranslations::get('rss_add_tooltip') ?>"><?= sysTranslations::get('rss_add') ?></a></div></td>
        </tr>
        <tr>
            <th><?= sysTranslations::get('global_title') ?></th>
            <th><?= sysTranslations::get('global_date') ?></th>
            <th class="{sorter:false} nonSorted" style="width: 60px;">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($aRssFeeds AS $oRssFeed) {
            echo '<tr>';
            echo '<td>' . _e($oRssFeed->title) . '</td>';
            echo '<td>' . Date::strToDate($oRssFeed->lastUpdate)->format('%d-%m-%Y %H:%M') . '</td>';            
            echo '<td>';
            echo '<a class="action_icon edit_icon" title="'.sysTranslations::get('rss_edit').'" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oRssFeed->rssFeedId . '"></a>';
            echo '<a class="action_icon delete_icon" title="'.sysTranslations::get('rss_delete').'" onclick="return confirmChoice(\'' . $oRssFeed->title . '\');" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/verwijderen/' . $oRssFeed->rssFeedId . '"></a>';
            echo '</td>';
            echo '</tr>';
        }
        if (count($aRssFeeds) == 0) {
            echo '<tr><td colspan="3"><i>'.sysTranslations::get('rss_no_feeds').'</i></td></tr>';
        }
        ?>
    </tbody>
    <tfoot>
        <tr class="bottomRow">
            <td colspan="3">
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