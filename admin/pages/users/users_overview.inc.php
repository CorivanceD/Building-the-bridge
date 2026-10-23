<table class="sorted">
    <thead>
        <tr class="topRow">
            <td colspan="5"><h2><?= sysTranslations::get('user_all')?></h2><div class="right"><a class="addBtn textLeft" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/toevoegen" title="<?= sysTranslations::get('user_add_tooltip')?>" alt="<?= sysTranslations::get('user_add_tooltip')?>"><?= sysTranslations::get('user_add')?></a></div></td>
        </tr>
        <tr>
            <th>#</th>
            <th><?= sysTranslations::get('global_name')?></th>
            <th><?= sysTranslations::get('user_username')?></th>
            <th><?= sysTranslations::get('user_profile')?></th>
            <th class="{sorter:false} nonSorted" style="width: 60px;">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($aUsers AS $oUser) {
            $sProfile = '';
            
            switch ($oUser->userProfile) {
                case User::USER_CLIENT_ADMIN:
                    $sProfile = 'Super user';
                    break;
                case User::USER_EDITOR:
                    $sProfile = 'Editor';
                    break;                
                case User::USER_WRITER:
                    $sProfile = 'Writer';
                    break;                             
            }
            
            echo '<tr>';
            echo '<td>' . $oUser->userId . '</td>';
            echo '<td>' . $oUser->name . '</td>';
            echo '<td>' . $oUser->username . '</td>';
            echo '<td>' . $sProfile . '</td>';
            echo '<td>';
            if ($oUser->isEditable())
                echo '<a class="action_icon edit_icon" title="'. sysTranslations::get('user_edit') .'" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oUser->userId . '"></a>';
            else
                echo '<span class="action_icon edit_icon grey"></span>';

            if ($oUser->isDeletable())
                echo '<a class="action_icon delete_icon" title="'.sysTranslations::get('user_delete').'" onclick="return confirmChoice(\''.  strtolower(sysTranslations::get('user_user')) .' '. $oUser->name . '\');" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/verwijderen/' . $oUser->userId . '"></a>';
            else
                echo '<span class="action_icon delete_icon grey"></span>';

            echo '</td>';
            echo '</tr>';
        }
        if (count($aUsers) == 0) {
            echo '<tr><td colspan="5">'.sysTranslations::get('user_no_users').'</i></td></tr>';
        }
        ?>
    </tbody>
</table>