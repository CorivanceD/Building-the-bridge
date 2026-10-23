<table class="sorted">
    <thead>
        <tr class="topRow">
            <td colspan="8"><h2><?= sysTranslations::get('modules_all') ?></h2><div class="right"><a class="addBtn textLeft" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/toevoegen" title="<?= sysTranslations::get('modules_add_tooltip') ?>" alt="<?= sysTranslations::get('modules_add_tooltip') ?>"><?= sysTranslations::get('modules_add') ?></a></div></td>
        </tr>
        <tr>
            <th>#</th>
            <th><?= sysTranslations::get('global_sequence') ?></th>
            <th><?= sysTranslations::get('modules_link_name') ?></th>
            <th><?= sysTranslations::get('global_name') ?></th>
            <th><?= sysTranslations::get('modules_parent') ?></th>
            <th><?= sysTranslations::get('modules_in_menu') ?></th>
            <th class="{sorter:false} nonSorted" style="width: 60px;">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($aModules AS $oModule) {
            echo '<tr>';
            echo '<td>' . $oModule->moduleId . '</td>';
            echo '<td>' . $oModule->order . '</td>';
            echo '<td>' . sysTranslations::get($oModule->linkName) . '</td>';
            echo '<td>' . $oModule->name . '</td>';
            echo '<td>' . ($oModule->getParent() ? sysTranslations::get($oModule->getParent()->linkName) : '') . '</td>';
            echo '<td>' . ($oModule->showInMenu ? sysTranslations::get('global_yes')  : sysTranslations::get('global_no')) . '</td>';
            echo '<td>';
            echo '<a class="action_icon edit_icon" title="'.sysTranslations::get('modules_edit').'" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oModule->moduleId . '"></a>';
            echo '<a class="action_icon delete_icon" title="'.sysTranslations::get('modules_delete').'" onclick="return confirmChoice(\''.sysTranslations::get($oModule->linkName) . '\');" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/verwijderen/' . $oModule->moduleId . '"></a>';
            echo '</td>';
            echo '</tr>';
        }
        if (count($aModules) == 0) {
            echo '<tr><td colspan="8"><i>'.sysTranslations::get('modules_no_modules').'</i></td></tr>';
        }
        ?>
    </tbody>
</table>