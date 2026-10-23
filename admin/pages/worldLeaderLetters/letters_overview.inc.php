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
                <td>&nbsp;</td>
                <td><input type="submit" name="filterArticles" value="<?= sysTranslations::get('articles_filter') ?>" /> <input type="submit" name="resetFilter" value="<?= sysTranslations::get('global_reset_filter') ?>" /></td>
            </tr>
        </table>
    </fieldset>
</form>
<table class="sorted">
    <thead>
        <tr class="topRow">
            <td colspan="5">
                <h2><?= sysTranslations::get('letters_all') ?></h2>
                <div class="right" style="text-align:right">
                    <a class="addBtn textLeft"  href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/toevoegen" title="<?= sysTranslations::get('letters_add_tooltip') ?>" alt="<?= sysTranslations::get('letters_add_tooltip') ?>"><?= sysTranslations::get('letters_add') ?></a><br>                    
                </div>
            </td>
        </tr>
        <tr>
            <th style="width: 100px;"><?= sysTranslations::get('global_date') ?></th>
            <th ><?= sysTranslations::get('global_leader') ?></th>
            <th ><?= sysTranslations::get('global_title') ?></th>
            <th ><?= sysTranslations::get('global_status') ?></th>
            <th class="{sorter:false} nonSorted" style="width: 60px;">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($aArticles AS $oArticle) {
            echo '<tr>';
            echo '<td>' . Date::strToDate($oArticle->pubDate)->format('%d-%m-%Y') . '</td>';
            echo '<td>' . (($oArticle->getWorldLeaderLetter() && $oArticle->getWorldLeaderLetter()->getWorldLeader()) ? _e($oArticle->getWorldLeaderLetter()->getWorldLeader()->name) : '') . '</td>';            
            echo '<td>' . _e($oArticle->title) . '</td>';
            echo '<td>';
            switch ($oArticle->status) {
                case Article::ARTICLE_STATUS_PENDING:
                    echo sysTranslations::get('articles_status_dev');
                    break;
                case Article::ARTICLE_STATUS_REVIEW:
                    echo sysTranslations::get('articles_status_review');
                    break;
                case Article::ARTICLE_STATUS_PUBLISHED:
                    echo sysTranslations::get('articles_status_published');
                    break;
                case Article::ARTICLE_STATUS_OFFLINE:
                    echo sysTranslations::get('articles_status_offline');
                    break;
                case Article::ARTICLE_STATUS_REJECTED:
                    echo sysTranslations::get('articles_status_rejected');
                    break;                
            }
            echo '</td>';
 
            echo '<td>';
        if ($oArticle->isEditable())
            echo '<a title="Article edition" class="action_icon edit_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oArticle->articleId . '"></a>';
        else
            echo '<a title="Article can\'t be edited" class="action_icon edit_icon grey" href="#"></a>';

        # delete button
        if ($oArticle->isDeletable())
            echo '<a onclick="return confirmChoice(\'article ' . $oArticle->title . '\');" class="action_icon delete_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/verwijderen/' . $oArticle->articleId . '"></a>';
        else
            echo '<a class="action_icon delete_icon grey" href="#" title="Verwijder eerst alle sub pagina&lsquo;s en check de rechten"></a>';            
            echo '</td>';
            echo '</tr>';
        }
        if (count($aArticles) == 0) {
            echo '<tr><td colspan="5"><i>'.sysTranslations::get('letters_no_letters').'</i></td></tr>';
        }
        ?>
    </tbody>
    <tfoot>
        <tr class="bottomRow">
            <td colspan="5">
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