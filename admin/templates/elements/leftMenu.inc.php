<div id="menuContainer" class="main-box">
    <div class="main-box-header-dark"><?= sysTranslations::get('global_main_menu') ?></div>
    <div class="main-box-header-light"></div>
    <div class="main-box-content">
        <ul class="menu">
            <?php
            $iT = 1;
            foreach ($oCurrentUser->getModules() AS $oMainModuleForMenu) {

                $bHasChildren = count($oMainModuleForMenu->getChildren()) > 0;
                $bIsActive = strtolower($oMainModuleForMenu->name) == strtolower(http_get("controller", ''));
                $bAfterWithChildren = false;

                $sClasses = '';
                $sClasses .= $bAfterWithChildren ? 'after-with-children ' : ''; //item after one with children
                $sClasses .= $bHasChildren ? 'hasChildren ' : 'no-children '; //check children
                $sClasses .= $iT == 1 ? 'first ' : ''; //first listitem
                $sClasses .= $iT == count($oCurrentUser->getModules()) ? 'last ' : ''; // last list item
                $sClasses .= $bIsActive || $oMainModuleForMenu->hasChild(strtolower(http_get("controller", ''))) ? 'active ' : ''; // this item is active

                echo'<li class="' . $sClasses . '">';
                echo '<div><a href="' . ADMIN_FOLDER . '/' . $oMainModuleForMenu->name . '">' . sysTranslations::get($oMainModuleForMenu->linkName) . '</a></div>';

                $bAfterWithChildren = false;
                if ($bIsActive || ($bHasChildren && $oMainModuleForMenu->hasChild(strtolower(http_get("controller", ''))))) {

                    echo '<ul class="subItems">';

                    foreach ($oMainModuleForMenu->getChildren() AS $oChild) {
                        $sClassesSub = '';
                        $sClassesSub .= strtolower($oChild->name) == strtolower(http_get("controller", '')) ? 'active ' : ''; // this item is active
                        echo '<li class="' . $sClassesSub . '">';
                        echo '<div><a href="' . ADMIN_FOLDER . '/' . $oChild->name . '">' . sysTranslations::get($oChild->linkName) . '</a></div>';
                        echo '</li>';
                    }
                    echo '</ul>';
                    echo '</li>';
                    $bAfterWithChildren = true;
                }
                $iT++;
            }
            ?>
        </ul>
    </div>
</div>