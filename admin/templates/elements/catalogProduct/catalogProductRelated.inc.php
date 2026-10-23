<table class="sorted" style="margin-top: 5px; margin-bottom: 10px; width: 100%;">
    <thead>
        <tr class="topRow">
            <td colspan="62"><h2><?= sysTranslations::get('catalog_related_products') ?><div style="float: right;"></div></h2></td>
        </tr>
        <tr>
            <th class="{sorter:false} nonSorted" style="width:60px"><?= sysTranslations::get('global_images') ?></th>
            <th><?= sysTranslations::get('global_products') ?></th>
            <th class="{sorter:false} nonSorted" style="width:30px"></th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($aRelatedProducts as $key => $oRelatedProduct) {
            $aProductImages = $oRelatedProduct->getImages();
            $aRelatedProductsImage[$key] = new stdClass();
            if (!empty($aProductImages)) {
                $oImage = ImageManager::getImageById($aProductImages[0]->imageId);
                $oImageFile = $oImage->getImageFileByReference('detail');
                $aRelatedProductsImage[$key]->link = $oImageFile->link;
            } else {
                $aRelatedProductsImage[$key]->link = '/images/layout/shop-no-image.jpg';
            }
            $aRelatedProductsImage[$key]->product_name = $oRelatedProduct->name;
            $aRelatedProductsImage[$key]->productId = $oRelatedProduct->catalogProductId;
        }

        if (empty($aRelatedProductsImage)) {
            ?>
            <tr>
                <td colspan=3><i><?= sysTranslations::get('catalog_no_related_products') ?></i></td>
            </tr>
            <?php
        } else {
            foreach ($aRelatedProductsImage as $oRelatedProductImage) {
                ?>
                <tr>
                    <td>
                        <div class="images-50">
                            <div class="placeholder-50">
                                <div class="imagePlaceholder-50">
                                    <div class="centered-50">
                                        <img src="<?= $oRelatedProductImage->link ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td><?= $oRelatedProductImage->product_name ?></td>
                    <td>
                        <a href="#" onclick="deleteRelatedProduct('<?= $oRelatedProductImage->productId ?>'); return false;" class="action_icon delete_icon"></a> 
                    </td>
                </tr>
                <?php
            }
        }
        ?>
    </tbody>
</table>
<?php if (http_get('ajax')) { ?>
    <!-- I re-add the sort -->
    <script>
        $("table.sorted").tablesorter();
    </script>
<?php } ?>
