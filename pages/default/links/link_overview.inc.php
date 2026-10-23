<section style="margin-top: 32px;">
    <article>
        <div class="column-66 float-left">    
            <?php if (!is_numeric(http_get('param1'))) { ?>
                <h1 class="sectionTitle"><?= $oPage->title ?></h1>
                <hr>
                <?= $oPage->content ?>            
                <br>
                <a class="orange-button" href="/links/share-link">SHARE YOUR LINK</a>
                <br>
                <b>Links recently submitted</b><br>
                <?php
                foreach (CustomerLinkCategoryManager::getCustomerLinkCategoriesByFilter(array('onlyParent' => 1)) as $oParentCategory) {
                    if ($oParentCategory->getCustomerLinks(true, true) != null) {
                        ?>
                        <div class="link-sharing column-100 float-left">
                            <h3><?= $oParentCategory->name ?></h3>                       
                            <ul id="category_<?= $oParentCategory->customerLinkCategoryId ?>" class="customer-links">
                                <?php
                                $i = 0;
                                foreach ($oParentCategory->getCustomerLinks(true, false) as $oCustomerLink) {
                                    $i++;
                                    ?>

                                    <li><a href="<?= $oCustomerLink->url ?>" target="_blank" title="<?= $oCustomerLink->name ?>"><?= $oCustomerLink->name ?></a></li>                                      
                                    <?php
                                    if ($i === 5)
                                        break;
                                }
                                ?>
                            </ul>
                            <a href="/links/<?= $oParentCategory->customerLinkCategoryId ?>" target="_self">See all links in this category >></a>
                        </div>
                        <?php
                    }
                }
            }
            else {
                ?>
                <h1 class="sectionTitle"><?= $oPage->title ?>: <?= $oParentCategory->name ?></h1>
                <hr>
                <?= $oPage->content ?>            
                <br>                            
                <a class="back" href="/links" target="_self">Back to overview >></a>
                <br>
                <?php
                foreach ($oParentCategory->getSubCategories() as $oSubCategory) {
                    if ($oSubCategory->getCustomerLinks() == null)
                        continue;
                    ?>
                    <div class="link-sharing column-100 float-left">
                        <h3><?= $oSubCategory->name ?></h3>                       
                        <ul>
                            <?php
                            foreach ($oSubCategory->getCustomerLinks() as $oCustomerLink) {
                                ?>
                                <li><a href="<?= $oCustomerLink->url ?>" target="_blank" title="<?= $oCustomerLink->name ?>"><?= $oCustomerLink->name ?></a></li>                                      
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <?php
                }
                ?>
                <a class="back" href="/links" target="_self">Back to overview >></a>
                <?php
            }
            ?>
        </div>
        <?php include TEMPLATES_FOLDER . '/elements/rightColumn.inc.php'; ?>
    </article>
</section>
