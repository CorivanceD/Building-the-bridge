<div id="topOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<div class="cf">
    <div class="contentColumn">
        <fieldset>
            <legend><?= sysTranslations::get('pages_info') ?></legend>
            <form method="POST" action="" class="validateForm">
                <input type="hidden" value="save" name="action" />
                <input type="hidden" value="<?= $oPage->parentPageId ?>" name="parentPageId" />                
                <table class="withForm">
                    <?php if ($oPage->isOnlineChangeable()) { ?>
                        <tr>
                            <td>Online *</td>
                            <td>
                                <input class="alignRadio {validate:{required:true}}" title="<?= sysTranslations::get('pages_online_tooltip') ?>" type="radio" <?= $oPage->online ? 'CHECKED' : '' ?> id="online_1" name="online" value="1" /> <label for="online_1"><?= sysTranslations::get('global_yes') ?></label>
                                <input class="alignRadio {validate:{required:true}}" title="<?= sysTranslations::get('pages_offline_tooltip') ?>" type="radio" <?= !$oPage->online ? 'CHECKED' : '' ?> id="online_0" name="online" value="0" /> <label for="online_0"><?= sysTranslations::get('global_no') ?></label>
                            </td>
                            <td><span class="error"><?= $oPage->isPropValid("online") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td class="withLabel" style="width: 116px;"><label for="title"><?= sysTranslations::get('global_title') ?> *</label></td>
                        <td><input id="title" class="{validate:{required:true}} autofocus default" title="<?= sysTranslations::get('pages_title_tooltip') ?>" type="text" autocomplete="off" name="title" value="<?= _e($oPage->title) ?>" /></td>
                        <td><span class="error"><?= $oPage->isPropValid("title") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="shortTitle"><?= sysTranslations::get('global_menu_link') ?> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('pages_link_tooltip') ?>">&nbsp;</div></label></td>
                        <td><input class="default" id="shortTitle" type="text" name="shortTitle" value="<?= _e($oPage->shortTitle) ?>" /></td>
                        <td></td>
                    </tr>
                    <?php if (!$oPage->getHideRssManagement()) { ?>
                        <tr>
                            <td colspan="3">
                                <fieldset>
                                    <legend>Rss Feeds</legend>
                                    <table>
                                        <tr>
                                            <td class="withLabel">
                                                <label for="rssFeed"><?= sysTranslations::get('rss_feed') ?>
                                                    <div class="hasTooltip tooltip" title="<?= sysTranslations::get('pages_rss_feed_tooltip') ?>">&nbsp;</div>
                                                </label>
                                            </td>
                                            <td coslpan="2">
                                                <select id="rssFeedDropdown">
                                                    <option style="text-align:center" value="">-- <?= sysTranslations::get('global_none') ?> --</option>
                                                    <?php foreach (RssFeedManager::getRssFeedsByFilter() AS $oRssFeed) { ?>
                                                        <?php if (!$oPage->hasRssFeed($oRssFeed->rssFeedId)) { ?>
                                                            <option value="<?= $oRssFeed->rssFeedId ?>"><?= $oRssFeed->title ?></option>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </select>                                
                                            </td>        
                                        </tr>
                                        <tr>
                                            <td class="withLabel">
                                                <label for="rssFeed"><?= sysTranslations::get('global_filter') ?>
                                                    <div class="hasTooltip tooltip" title="<?= sysTranslations::get('page_rss_filter') ?>">&nbsp;</div>
                                                </label>
                                            </td>
                                            <td colspan="2">
                                                <input class="default" style="width: 120px" id="filter" type="text" name="filter" value="" />
                                                <input type="radio" class="logicOperator" name="logicOperator" value="AND" checked />AND
                                                <input type="radio" class="logicOperator" name="logicOperator" value="OR"  />OR
                                                <input type="checkbox" class="negative" name="negative" value="1"  />NOT<br>                                                                             
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td colspan="2"><input id="addRssFeed" type="button" value="Add" /> </td>
                                        </tr>
                                        <tr><td colspan="3">&nbsp;</td></tr>
                                        <tr>
                                            <td colspan="3">

                                                <table id="feedTable" class="sorted">
                                                    <thead>
                                                        <tr>                                            
                                                            <th><?= sysTranslations::get('rss_feed') ?></th>
                                                            <th style="width:420px"><?= sysTranslations::get('global_filter') ?></th>
                                                            <th>Logic operator</th>
                                                            <th>Negative</th>
                                                            <th class="{sorter:false} nonSorted" style="width: 30px;">&nbsp;</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $aRssFeeds = array();
                                                        foreach ($oPage->getRssFeeds() AS $oRssFeed) {
                                                            $aRssFeeds[] = $oRssFeed->rssFeedId . ":" . $oRssFeed->filter;

                                                            echo '<tr data-feed-id="' . $oRssFeed->rssFeedId . '">';
                                                            echo '<td>' . $oRssFeed->title . '</td>';
                                                            echo '<td>' . $oRssFeed->filter . '</td>';
                                                            echo '<td>' . $oRssFeed->logicOperator . '</td>';
                                                            echo '<td>' . ($oRssFeed->negative == 1 ? 'Yes' : 'No') . '</td>';
                                                            echo '<td><a class="action_icon delete_icon" onclick="removeFeed(this)" href="#"></a></td>';
                                                            echo '</tr>';
                                                        }
                                                        if (count($oPage->getRssFeeds()) == 0) {
                                                            echo '<tr class="nofeeds"><td colspan="5">No Rss Feeds</i></td></tr>';
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>                                 
                                                <input type="hidden" id="rssFeeds" name="rssFeeds" value="<?= implode(',', $aRssFeeds) ?>" />
                                            </td>
                                        </tr>
                                    </table>
                                </fieldset>
                            </td>
                        </tr>
                        <tr><td colspan="3">&nbsp;</td></tr>
                    <?php } ?>
                    <tr>
                        <td colspan="3"><label for="content"><?= sysTranslations::get('global_content') ?></label></td>
                    </tr>
                    <tr>
                        <td colspan="3"><textarea name="content" id="content" class="tiny_MCE_page tiny_MCE"><?= $oPage->content ?></textarea></td>
                    </tr>
                    <?php if ($oCurrentUser->isSEO()) { ?>
                        <tr>
                            <td colspan="3" style="padding-top: 10px;"><h2><?= sysTranslations::get('global_seo') ?></h2></td>
                        </tr>
                        <?php if ($oPage->pageId) { ?>
                            <tr>
                                <td><?= sysTranslations::get('global_current_url') ?></td>
                                <td><?= $oPage->getUrlPath() ?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td class="withLabel"><label for="windowTitle"><?= sysTranslations::get('global_window_title') ?></label> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('global_title_seo') ?>">&nbsp;</div></td>
                            <td colspan="2">
                                <div class="inline-block">
                                    <input class="default charCounterWindowTitle" id="windowTitle" type="text" name="windowTitle" value="<?= _e($oPage->windowTitle) ?>" />
                                    <div id="windowTitleCounter" style="text-align: right;"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="metaDescription"><?= sysTranslations::get('global_description') ?></label> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('global_description_seo') ?>">&nbsp;</div></td>
                            <td colspan="2">
                                <div class="inline-block">
                                    <textarea class="charCounterMetaDescription default" id="metaDescription" name="metaDescription"><?= _e($oPage->metaDescription) ?></textarea>
                                    <div id="metaDescriptionCounter" style="text-align: right;"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="withLabel"><label for="metaKeywords"><?= sysTranslations::get('global_keywords') ?></label> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('global_keywords_seo') ?>">&nbsp;</div></td>
                            <td colspan="2"><input class="default" id="metaKeywords" type="text" name="metaKeywords" value="<?= _e($oPage->metaKeywords) ?>" /> (<?= sysTranslations::get('global_optional') ?>)</td>
                        </tr>
                        <?php if (!$oPage->getLockUrlPath()) { ?>
                            <tr>
                                <td class="withLabel"><label for="urlPart"><?= sysTranslations::get('global_seo_url') ?></label> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('global_seo_url_tooltip') ?>: <?= CLIENT_HTTPS_URL ?><?= $oPage->parentPageId ? $oPage->getParent()->getUrlPath() : '' ?>/<?= sysTranslations::get('global_seo_url_tooltip_2') ?>">&nbsp;</div></td>
                                <td><input  class="default" id="urlPart" type="text" name="urlPart" value="<?= _e($oPage->getUrlPart()) ?>" /></td>
                                <td></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td class="withLabel"><label for="urlParameters"><?= sysTranslations::get('pages_url_parameters') ?></label> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('pages_url_parameters_tooltip_1') ?> <?= CLIENT_HTTPS_URL ?><?= $oPage->parentPageId ? $oPage->getParent()->getUrlPath() : '' ?><?= sysTranslations::get('pages_url_parameters_tooltip_1') ?>">&nbsp;</div></td>
                            <td><input  class="default" id="urlParameters" type="text" name="urlParameters" value="<?= _e($oPage->getUrlParameters()) ?>" /></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td><?= sysTranslations::get('pages_in_menu') ?></td>
                            <td>
                                <input class="alignRadio" title="<?= sysTranslations::get('pages_in_menu_tooltip') ?>" type="radio" <?= $oPage->getInMenu() ? 'CHECKED' : '' ?> id="inMenu_1" name="inMenu" value="1" /> <label for="inMenu_1"><?= sysTranslations::get('global_yes') ?></label>
                                <input style="margin-left: 5px;" class="alignRadio" title="<?= sysTranslations::get('pages_in_menu_tooltip') ?>" type="radio" <?= !$oPage->getInMenu() ? 'CHECKED' : '' ?> id="inMenu_0" name="inMenu" value="0" /> <label for="inMenu_0"><?= sysTranslations::get('global_no') ?></label>
                            </td>
                            <td><span class="error"><?= $oPage->isPropValid("inMenu") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                        </tr>
                    <?php } ?>
                    <?php if ($oCurrentUser->isAdmin()) { ?>
                        <tr>
                            <td colspan="3" style="padding-top: 10px;"><h2><?= sysTranslations::get('global_admin_settings') ?></h2></td>
                        </tr>
                        <tr>
                            <td class="withLabel"><label for="controllerPath"><?= sysTranslations::get('pages_controller_path') ?> *</label></td>
                            <td><input class="{validate:{required:true}} default" id="controllerPath" name="controllerPath" type="text" value="<?= _e($oPage->getControllerPath()) ?>" /></td>
                            <td><span class="error"><?= $oPage->isPropValid("controllerPath") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                        </tr>
                        <tr>
                            <td><?= sysTranslations::get('pages_online_changeable') ?></td>
                            <td>
                                <input class="alignRadio" title="<?= sysTranslations::get('pages_online_changeable_tooltip') ?>" type="radio" <?= $oPage->getOnlineChangeable() ? 'CHECKED' : '' ?> id="onlineChangeable_1" name="onlineChangeable" value="1" /> <label for="onlineChangeable_1"><?= sysTranslations::get('global_yes') ?></label>
                                <input style="margin-left: 5px;" class="alignRadio" title="<?= sysTranslations::get('pages_online_changeable_tooltip') ?>" type="radio" <?= !$oPage->getOnlineChangeable() ? 'CHECKED' : '' ?> id="onlineChangeable_0" name="onlineChangeable" value="0" /> <label for="onlineChangeable_0"><?= sysTranslations::get('global_no') ?></label>
                            </td>
                            <td><span class="error"><?= $oPage->isPropValid("online") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                        </tr>
                        <tr>
                            <td><?= sysTranslations::get('global_editable') ?></td>
                            <td>
                                <input class="alignRadio" title="<?= sysTranslations::get('pages_editable_tooltip') ?>" type="radio" <?= $oPage->getEditable() ? 'CHECKED' : '' ?> id="editable_1" name="editable" value="1" /> <label for="editable_1"><?= sysTranslations::get('global_yes') ?></label>
                                <input style="margin-left: 5px;" class="alignRadio" title="<?= sysTranslations::get('pages_editable_tooltip') ?>" type="radio" <?= !$oPage->getEditable() ? 'CHECKED' : '' ?> id="editable_0" name="editable" value="0" /> <label for="editable_0"><?= sysTranslations::get('global_no') ?></label>
                            </td>
                            <td><span class="error"><?= $oPage->isPropValid("editable") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                        </tr>
                        <tr>
                            <td><?= sysTranslations::get('global_deletable') ?></td>
                            <td>
                                <input class="alignRadio" title="<?= sysTranslations::get('pages_deletable_tooltip') ?>" type="radio" <?= $oPage->getDeletable() ? 'CHECKED' : '' ?> id="deletable_1" name="deletable" value="1" /> <label for="deletable_1"><?= sysTranslations::get('global_yes') ?></label>
                                <input style="margin-left: 5px;" class="alignRadio" title="<?= sysTranslations::get('pages_deletable_tooltip') ?>" type="radio" <?= !$oPage->getDeletable() ? 'CHECKED' : '' ?> id="deletable_0" name="deletable" value="0" /> <label for="deletable_0"><?= sysTranslations::get('global_no') ?></label>
                            </td>
                            <td><span class="error"><?= $oPage->isPropValid("deletable") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                        </tr>
                        <tr>
                            <td><?= sysTranslations::get('pages_has_subpages') ?></td>
                            <td>
                                <input class="alignRadio" title="<?= sysTranslations::get('page_has_subpages_tooltip') ?>" type="radio" <?= $oPage->getMayHaveSub() ? 'CHECKED' : '' ?> id="mayHaveSub_1" name="mayHaveSub" value="1" /> <label for="mayHaveSub_1"><?= sysTranslations::get('global_yes') ?></label>
                                <input style="margin-left: 5px;" class="alignRadio" title="<?= sysTranslations::get('page_has_sub_tooltip') ?>" type="radio" <?= !$oPage->getMayHaveSub() ? 'CHECKED' : '' ?> id="mayHaveSub_0" name="mayHaveSub" value="0" /> <label for="mayHaveSub_0"><?= sysTranslations::get('global_no') ?></label>
                            </td>
                            <td><span class="error"><?= $oPage->isPropValid("mayHaveSub") ? '' : sysTranslations::get('global_field_not_completed') ?> </span></td>
                        </tr>
                        <tr>
                            <td><?= sysTranslations::get('pages_lock_path') ?></td>
                            <td>
                                <input class="alignRadio" title="<?= sysTranslations::get('pages_lock_path_tooltip') ?>" type="radio" <?= $oPage->getLockUrlPath() ? 'CHECKED' : '' ?> id="lockUrlPath_1" name="lockUrlPath" value="1" /> <label for="lockUrlPath_1"><?= sysTranslations::get('global_yes') ?></label>
                                <input style="margin-left: 5px;" class="alignRadio" title="<?= sysTranslations::get('pages_lock_path_tooltip') ?>" type="radio" <?= !$oPage->getLockUrlPath() ? 'CHECKED' : '' ?> id="lockUrlPath_0" name="lockUrlPath" value="0" /> <label for="lockUrlPath_0"><?= sysTranslations::get('global_no') ?></label>
                            </td>
                            <td><span class="error"><?= $oPage->isPropValid("lockUrlPath") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                        </tr>
                        <tr>
                            <td><?= sysTranslations::get('pages_lock_parent') ?></td>
                            <td>
                                <input class="alignRadio" title="<?= sysTranslations::get('pages_lock_parent_tooltip') ?>" type="radio" <?= $oPage->getLockParent() ? 'CHECKED' : '' ?> id="lockParent_1" name="lockParent" value="1" /> <label for="lockParent_1"><?= sysTranslations::get('global_yes') ?></label>
                                <input style="margin-left: 5px;" class="alignRadio" title="<?= sysTranslations::get('pages_lock_parent_tooltip') ?>" type="radio" <?= !$oPage->getLockParent() ? 'CHECKED' : '' ?> id="lockParent_0" name="lockParent" value="0" /> <label for="lockParent_0"><?= sysTranslations::get('global_no') ?></label>
                            </td>
                            <td><span class="error"><?= $oPage->isPropValid("lockUrlPath") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                        </tr>
                        <tr>
                            <td><?= sysTranslations::get('pages_no_images') ?></td>
                            <td>
                                <input class="alignRadio" title="<?= sysTranslations::get('pages_no_images_tooltip') ?>" type="radio" <?= $oPage->getHideImageManagement() ? 'CHECKED' : '' ?> id="hideImageManagement_1" name="hideImageManagement" value="1" /> <label for="hideImageManagement_1"><?= sysTranslations::get('global_yes') ?></label>
                                <input style="margin-left: 5px;" class="alignRadio" title="<?= sysTranslations::get('pages_no_images_tooltip') ?>" type="radio" <?= !$oPage->getHideImageManagement() ? 'CHECKED' : '' ?> id="hideImageManagement_0" name="hideImageManagement" value="0" /> <label for="hideImageManagement_0"><?= sysTranslations::get('global_no') ?></label>
                            </td>
                            <td><span class="error"><?= $oPage->isPropValid("hideImageManagement") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                        </tr>
                        <tr>
                            <td><?= sysTranslations::get('pages_no_files') ?></td>
                            <td>
                                <input class="alignRadio" title="<?= sysTranslations::get('pages_no_files_tooltip') ?>" type="radio" <?= $oPage->getHideFileManagement() ? 'CHECKED' : '' ?> id="hideFileManagement_1" name="hideFileManagement" value="1" /> <label for="hideFileManagement_1"><?= sysTranslations::get('global_yes') ?></label>
                                <input style="margin-left: 5px;" class="alignRadio" title="<?= sysTranslations::get('pages_no_files_tooltip') ?>" type="radio" <?= !$oPage->getHideFileManagement() ? 'CHECKED' : '' ?> id="hideFileManagement_0" name="hideFileManagement" value="0" /> <label for="hideFileManagement_0"><?= sysTranslations::get('global_no') ?></label>
                            </td>
                            <td><span class="error"><?= $oPage->isPropValid("hideFileManagement") ? '' : sysTranslations::get('page_has_no_file_management') ?></span></td>
                        </tr>
                        <tr>
                            <td><?= sysTranslations::get('pages_no_links') ?></td>
                            <td>
                                <input class="alignRadio" title="<?= sysTranslations::get('pages_no_links_tooltip') ?>" type="radio" <?= $oPage->getHideLinkManagement() ? 'CHECKED' : '' ?> id="hideLinkManagement_1" name="hideLinkManagement" value="1" /> <label for="hideLinkManagement_1"><?= sysTranslations::get('global_yes') ?></label>
                                <input style="margin-left: 5px;" class="alignRadio" title="<?= sysTranslations::get('pages_no_links_tooltip') ?>" type="radio" <?= !$oPage->getHideLinkManagement() ? 'CHECKED' : '' ?> id="hideLinkManagement_0" name="hideLinkManagement" value="0" /> <label for="hideLinkManagement_0"><?= sysTranslations::get('global_no') ?></label>
                            </td>
                            <td><span class="error"><?= $oPage->isPropValid("hideLinkManagement") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                        </tr>
                        <tr>
                            <td><?= sysTranslations::get('pages_no_youtube') ?></td>
                            <td>
                                <input class="alignRadio" title="<?= sysTranslations::get('pages_no_youtube_tooltip') ?>" type="radio" <?= $oPage->getHideYoutubeLinkManagement() ? 'CHECKED' : '' ?> id="hideYoutubeLinkManagement_1" name="hideYoutubeLinkManagement" value="1" /> <label for="hideYoutubeLinkManagement_1"><?= sysTranslations::get('global_yes') ?></label>
                                <input style="margin-left: 5px;" class="alignRadio" title="<?= sysTranslations::get('page_has_no_youtube_management') ?>" type="radio" <?= !$oPage->getHideYoutubeLinkManagement() ? 'CHECKED' : '' ?> id="hideYoutubeLinkManagement_0" name="hideYoutubeLinkManagement" value="0" /> <label for="hideYoutubeLinkManagement_0"><?= sysTranslations::get('global_no') ?></label>
                            </td>
                            <td><span class="error"><?= $oPage->isPropValid("hideYoutubeLinkManagement") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                        </tr>
                        <tr>
                            <td><?= sysTranslations::get('pages_no_rss') ?></td>
                            <td>
                                <input class="alignRadio" title="<?= sysTranslations::get('pages_no_rss_tooltip') ?>" type="radio" <?= $oPage->getHideRssManagement() ? 'CHECKED' : '' ?> id="hideRssManagement_1" name="hideRssManagement" value="1" /> <label for="hideRssManagement_1"><?= sysTranslations::get('global_yes') ?></label>
                                <input style="margin-left: 5px;" class="alignRadio" title="<?= sysTranslations::get('page_has_no_rss_management') ?>" type="radio" <?= !$oPage->getHideRssManagement() ? 'CHECKED' : '' ?> id="hideRssManagement_0" name="hideRssManagement" value="0" /> <label for="hideRssManagement_0"><?= sysTranslations::get('global_no') ?></label>
                            </td>
                            <td><span class="error"><?= $oPage->isPropValid("hideRssManagement") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                        </tr>                        
                    <?php } ?>
                    <tr>
                        <td colspan="3">
                            <input type="submit" value="<?= sysTranslations::get('global_save') ?>" name="save" />
                        </td>
                    </tr>
                </table>
        </fieldset>
        </form>
    </div>
    <?php if (!$oPage->hideImageManagement()) { ?>
        <div class="contentColumn">
            <fieldset>
                <legend><?= sysTranslations::get('global_images') ?></legend>
                <?php
                if ($oPage->pageId !== null) {
                    $oImageManagerHTML->includeTemplate();
                } else {
                    echo '<p><i>' . sysTranslations::get('pages_images_warning') . '</i></p>';
                }
                ?>
            </fieldset>
        </div>
        <?php
    }
    if (!$oPage->hideFileManagement()) {
        ?>
        <div class="contentColumn">
            <fieldset>
                <legend><?= sysTranslations::get('global_files') ?></legend>
                <?php
                if ($oPage->pageId !== null) {
                    $oFileManagerHTML->includeTemplate();
                } else {
                    echo '<p><i>' . sysTranslations::get('pages_files_warning') . '</i></p>';
                }
                ?>
            </fieldset>
        </div>
        <?php
    }
    if (!$oPage->hideLinkManagement()) {
        ?>

        <div class="contentColumn">
            <fieldset>
                <legend><?= sysTranslations::get('global_links') ?></legend>
                <?php
                if ($oPage->pageId !== null) {
                    $oLinkManagerHTML->includeTemplate();
                } else {
                    echo '<p><i>' . sysTranslations::get('pages_links_warning') . '</i></p>';
                }
                ?>
            </fieldset>
        </div>
        <?php
    }
    if (!$oPage->hideYoutubeLinkManagement()) {
        ?>
        <div class="contentColumn">
            <fieldset>
                <legend><?= sysTranslations::get('global_youtube') ?></legend>
                <?php
                if ($oPage->pageId !== null) {
                    $oYoutubeLinkManagerHTML->includeTemplate();
                } else {
                    echo '<p><i>' . sysTranslations::get('pages_youtube_warning') . '</i></p>';
                }
                ?>
            </fieldset>
        </div>
        <?php
    }
    ?>
</div>
<div id="bottomOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<?php
$oPageLayout->addJavascriptBottom('
<script>
    initTinyMCE(".tiny_MCE_page", "/admin/paginas/link-list", "/admin/paginas/image-list/' . $oPage->pageId . '");
        
    function setRssFeeds() {
        var rssFeeds = [];
        $("#feedTable tr").each( function(index)  { 
            if (index > 0) {
                var filter = $(this).children("td").eq(1).html().trim();
                var negative = $(this).children("td").eq(3).html().trim();
                var logiOp = $(this).children("td").eq(2).html().trim();
                var feedId = $(this).data("feed-id");
                
                if (filter.length > 0) {
                    rssFeeds.push(feedId + ":" + filter + ":" + logiOp + ":" + negative);
                }
                else {
                    rssFeeds.push(feedId);
                }
            }
        });
        $("#rssFeeds").val(rssFeeds.join("^"));
    }
    
    $("#addRssFeed").click(
        function (e) {
            e.preventDefault();
            var rssFeedId = $("#rssFeedDropdown option:selected").val();
            var trClass = "odd";
            
            if ($("#feedTable tr").last().hasClass("odd")) {
                trClass = "even";
            }
            
            if (rssFeedId) {                
                var negative = "NO";
                
                if ($(".negative").prop("checked")) {
                    negative = "YES";
                }
                $("#feedTable").append("<tr data-feed-id=\"" + rssFeedId+ "\" class=\"" + trClass + "\"><td>" + $("#rssFeedDropdown option:selected").html() + "</td><td>" + $("#filter").val() + "</td><td>" + $(".logicOperator:checked").val() + "</td><td>" + negative + "</td><td><a class=\"action_icon delete_icon\" onclick=\"removeFeed($(this))\" href=\"#\"></a></td>");
                $("#rssFeedDropdown option:selected").remove();
                $("#filter").val("");
                $(".nofeeds").remove();
                setRssFeeds();
                
            }
        }
    );
    
    function removeFeed(element) {
        var row = $(element).parent().parent();

        var option = "<option value=\"" + row.data("feed-id") + "\">" + row.children("td").eq(0).html() + "</option>";
        if ($(element).parent().parent().hasClass("odd")) {
            $(element).parent().parent().prev("tr").removeClass("even").addClass("odd");
        }
        else {
            $(element).parent().parent().prev("tr").removeClass("odd").addClass("even");
        }
        $(element).parent().parent().remove();
        $("#rssFeedDropdown").append(option);
        setRssFeeds();
        
        if ($("#feedTable tr").length === 1) {
            $("#feedTable").append("<td class=\"nofeeds\" colspan=\"3\">No Rss Feeds</td>");
        }
    };    
</script>
');
?>