<div id="topOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<div class="cf">
    <div class="contentColumn">
        <fieldset>
            <legend><?= sysTranslations::get('rss_feed') ?></legend>
            <form method="POST" action="" id="rssFeedForm" class="validateForm">
                <input type="hidden" value="load" name="action" />
                <input type="hidden" value="<?= $oRssFeed->rssFeedId ?>" name="rssFeedId" />


                <table class="withForm">
                    
                    <tr>
                        <td>Online *</td>
                        <td>
                            <input class="alignRadio {validate:{required:true}}" title="<?= sysTranslations::get('rss_online_tooltip') ?>" type="radio" <?= $oRssFeed->online ? 'CHECKED' : '' ?> id="online_1" name="online" value="1" /> <label for="online_1"><?= sysTranslations::get('global_yes') ?></label>
                            <input class="alignRadio {validate:{required:true}}" title="<?= sysTranslations::get('rss_offline_tooltip') ?>" type="radio" <?= !$oRssFeed->online ? 'CHECKED' : '' ?> id="online_0" name="online" value="0" /> <label for="online_0"><?= sysTranslations::get('global_no') ?></label>
                        </td>
                        <td><span class="error"><?= $oRssFeed->isPropValid("online") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    
                    <tr>
                        <td class="withLabel"><label for="link"><?= sysTranslations::get('global_link') ?> *</label> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('rss_feed_link_tooltip') ?>">&nbsp;</div></td>
                        <td>
                            <input style="width:400px" title="<?= sysTranslations::get('rss_feed_link_tooltip') ?>"  id="link" class="{validate:{required:true}}" type="text" name="link" value="<?= $oRssFeed->link ?>" />
                        </td>
                        <td><span class="error"><?= $oRssFeed->isPropValid("link") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="title"><?= sysTranslations::get('global_title') ?></label></td>
                        <td colspan="2"><input style="width:400px" id="title"  type="text" autocomplete="off" name="title" value="<?= _e($oRssFeed->title) ?>" /></td>
                    </tr> 
                    <tr>
                        <td class="withLabel"><label for="lastUpdate"><?= sysTranslations::get('rss_last_update') ?></label></td>
                        <td colspan="2"><input style="width:100px" id="lastUpdate" disabled type="text" autocomplete="off" name="lastUpdate" value="<?= !empty($oRssFeed->lastUpdate) ? Date::strToDate($oRssFeed->lastUpdate)->format('%d-%m-%Y %H:%M') : '' ?>" /></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="description"><?= sysTranslations::get('global_description') ?></label></td>
                        <td colspan="2"><textarea cols="50" rows="6" name="description" id="description" class="{validate:{required:true}}"><?= $oRssFeed->description ?></textarea></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="title"><?= sysTranslations::get('rss_feed_update_interval') ?></label><div class="hasTooltip tooltip" title="<?= sysTranslations::get('rss_feed_update_interval_tooltip') ?>">&nbsp;</div></td>
                        <td><input id="updateInterval" type="text" class="{validate: {required:true, min:15, digits:true}}" name="updateInterval" title="<?= sysTranslations::get('rss_feed_update_interval') ?>" value="<?= $oRssFeed->updateInterval ?>">&nbsp;<?= sysTranslations::get('global_minutes') ?></td>
                        <td><span class="error"><?= $oRssFeed->isPropValid("updateInterval") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>                        
                    </tr>                    
                    <tr>                        
                        <td colspan="3">
                            <input type="submit" name="save" data-action="load" value="<?= sysTranslations::get('global_save') ?>">&nbsp;
                        </td>   
                    </tr>
                </table>
            </form>
        </fieldset>
        <?php if (!empty($oRssFeed->rssFeedId)) { ?>
            <br/>

            <fieldset style="margin-bottom: 20px;">
                <legend><?= sysTranslations::get('global_filter') ?></legend>
                <form method="POST">
                    <input type="hidden" name="filterForm" value="1" />
                    <table class="withForm">
                        <tr>
                            <td class="withLabel" style="width: 116px;"><?= sysTranslations::get('global_search_word') ?></td>
                            <td><input type="text" name="rssFeedItemFilter[q]" value="<?= _e($aRssFeedItemFilter['q']) ?>" /></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td><input type="submit" name="filterRssFeedItems" value="<?= sysTranslations::get('rss_feed_item_filter') ?>" /> <input type="submit" name="resetFilter" value="<?= sysTranslations::get('global_reset_filter') ?>" /></td>
                        </tr>
                    </table>
                </form>
                <table class="sorted">
                    <thead>
                        <tr class="topRow">
                            <td colspan="4"><h2><?= sysTranslations::get('rss_feed_item_all') ?></h2></td>
                        </tr>
                        <tr>
                            <th style="width: 80px"><?= sysTranslations::get('global_date') ?></th>
                            <th style="width: 200px"><?= sysTranslations::get('global_title') ?></th>
                            <th class="{sorter:false} nonSorted"><?= sysTranslations::get('global_description') ?></th>
                            <th class="{sorter:false} nonSorted" style="width: 30px;">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($aRssFeedItems AS $oRssFeedItem) {
                            echo '<tr>';
                            echo '<td>' . Date::strToDate($oRssFeedItem->pubDate)->format('%d-%m-%Y %H:%M') . '</td>';
                            echo '<td>' . $oRssFeedItem->title . '</td>';
                            echo '<td>' . $oRssFeedItem->description . '</td>';
                            echo '<td>';
                            echo '<a class="action_icon magnifying_glass_icon" target="_blank" title="' . sysTranslations::get('global_browse_link') . '" href="' . $oRssFeedItem->link . '"></a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        if (count($aRssFeedItems) == 0) {
                            echo '<tr><td colspan="4"><i>' . sysTranslations::get('news_no_news') . '</i></td></tr>';
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr class="bottomRow">
                            <td colspan="4">
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
            </fieldset>

            <br/>
        <?php } ?>
    </div>
</div>
<div id="bottomOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>