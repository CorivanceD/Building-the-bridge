<form action="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>" method="POST">
    <input type="hidden" name="filterForm" value="1" />
    <fieldset style="margin-bottom: 20px;">
        <legend>Filter</legend>
        <table class="withForm">
            <tr>
                <td class="withLabel" style="width: 150px;">Tekst <div class="hasTooltip tooltip" title="Zoek op de naam of afzender van het link of een gedeelte daarvan">&nbsp;</div></td>
                <td><input class="default" type="text" name="linkFilter[q]" value="<?= _e($aLinkFilter['q']) ?>" /></td>
            </tr>
            <tr>
                <td class="withLabel">Categorie</td>
                <td>
                    <select class="default" id="linkCategoryId" name="linkFilter[linkCategoryId]">
                        <option value="">-- alle categorieën --</option>
                        <?
                        foreach (CustomerLinkCategoryManager::getCustomerLinkCategoriesByFilter(array('onlyParent' => 1)) AS $oCategory) {
                            echo '<option ' . ($aLinkFilter['linkCategoryId'] == $oCategory->customerLinkCategoryId ? 'SELECTED' : '') . ' value="' . $oCategory->customerLinkCategoryId . '">' . _e($oCategory->name) . '</option>';

                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="withLabel">Subcategorie</td>
                <td>
                    <select class="default" id="linkSubCategoryId" name="linkFilter[linkSubCategoryId]">
                        <option value="">-- alle subcategorieën --</option>
                        <?
                        if (!empty($oParentCategory)) {
                            foreach (CustomerLinkCategoryManager::getCustomerLinkCategoriesByFilter(array('parentId' => $oParentCategory->customerLinkCategoryId)) AS $oSubCategory) {
                                echo '<option ' . ($aLinkFilter['linkSubCategoryId'] == $oSubCategory->customerLinkCategoryId ? 'SELECTED' : '') . ' value="' . $oSubCategory->customerLinkCategoryId . '">' . _e($oSubCategory->name) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>            
            <tr>
                <td>&nbsp;</td>
                <td><input type="submit" name="filterLinks" value="Filter linken" /> <input type="submit" name="resetFilter" value="Reset filter" /></td>
            </tr>
        </table>
    </fieldset>
</form>

<!-- Links -->
<table class="sorted">
    <thead>
        <tr class="topRow">
            <td colspan="7"><h2>Gevonden linken (<?= (count($aCustomerLinks) != 0 ? (db_int($iStart) + 1) : db_int($iStart)) . '-' . (db_int($iStart) + count($aCustomerLinks)) ?>/<?= $iFoundRows ?>)</h2><div class="right"><a class="addBtn textLeft" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/toevoegen" title="Link toevoegen" alt="Link toevoegen">Link toevoegen</a></div></td>
        </tr>
        <tr>
            <th class="{sorter:false} nonSorted">Actief</th>
            <th>Naam</th>
            <th>Categorie</th>
            <th>Verzender</th>
            <th>URL</th>
            <th>Return URL</th>
            <th class="{sorter:false} nonSorted" style="width: 60px;">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?
        foreach ($aCustomerLinks AS $oCustomerLink) {
            echo '<tr>';
            # online offline button
            echo '<td>';
            echo '<a id="customerLink_' . $oCustomerLink->customerLinkId . '_online_1" title="" class="action_icon ' . ($oCustomerLink->online ? '' : 'hide') . ' online_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-setOnline/' . $oCustomerLink->customerLinkId . '/?online=0"></a>';
            echo '<a id="customerLink_' . $oCustomerLink->customerLinkId . '_online_0" title="" class="action_icon ' . ($oCustomerLink->online ? 'hide' : '') . ' offline_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-setOnline/' . $oCustomerLink->customerLinkId . '/?online=1"></a>';
            echo '</td>';
            echo '<td>' . _e($oCustomerLink->name) . '</td>';
            echo '<td>' . $oCustomerLink->getFirstCategory()->name . '</td>';
            echo '<td>' . _e($oCustomerLink->sender) . '</td>';
            echo '<td><a href="' . $oCustomerLink->url . '" target="_blank">' . $oCustomerLink->url . '</a></td>';
            echo '<td><a href="' . $oCustomerLink->returnUrl . '" target="_blank">' . $oCustomerLink->returnUrl . '</a></td>';
            echo '<td>';
            echo '<a class="action_icon edit_icon" title="Bewerk link" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oCustomerLink->customerLinkId . '"></a>';
            echo '<a class="action_icon delete_icon" title="Verwijder link" onclick="return confirmChoice(\'' . _e($oCustomerLink->name) . '\');" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/verwijderen/' . $oCustomerLink->customerLinkId . '"></a>';
            echo '</td>';
            echo '</tr>';
        }
        if (empty($aCustomerLinks)) {
            echo '<tr><td colspan="7"><i>Er zijn geen linken om weer te geven met dit filter</i></td></tr>';
        }
        ?>
    </tbody>
    <tfoot>
        <tr class="bottomRow">
            <td colspan="9">
                <form method="POST">
                    <?= generatePaginationHTML($iPageCount, $iCurrPage) ?>
                    <input type="hidden" name="setPerPage" value="1" />
                    <select name="perPage" onchange="$(this).closest('form').submit();">
                        <option value="">alle</option>
                        <option <?= $iPerPage == 10 ? 'SELECTED' : '' ?> value="10">10</option>
                        <option <?= $iPerPage == 25 ? 'SELECTED' : '' ?> value="25">25</option>
                        <option <?= $iPerPage == 50 ? 'SELECTED' : '' ?> value="50">50</option>
                        <option <?= $iPerPage == 100 ? 'SELECTED' : '' ?> value="100">100</option>
                    </select> per pagina
                </form>
            </td>
        </tr>
    </tfoot>
</table>
<?
# create necessary javascript
$sURL = getCurrentUrlPath();
$sBottomJavascript = <<<EOT
<script>
    $("a.online_icon, a.offline_icon").click(function(e){
        $.ajax({
            type: "GET",
            url: $(this).attr('href'),
            data: 'ajax=1',
            async: true,
            success: function(data){
                var dataObj = eval('(' + data + ')');

                /* On success */
                if (dataObj.success == true){
                    $("#customerLink_"+dataObj.customerLinkId+"_online_0").hide(); // hide offline button
                    $("#customerLink_"+dataObj.customerLinkId+"_online_1").hide(); // hide online button
                    $("#customerLink_"+dataObj.customerLinkId+"_online_"+dataObj.online).css('display', 'inline-block'); // show button based on online value
                    if(dataObj.online == 0)    
                        showStatusUpdate("Link offline gezet");
                    if(dataObj.online == 1)    
                        showStatusUpdate("Link online gezet");                            
                } else {
                    showStatusUpdate("Link status kan niet worden gewijzigd");
                }
            }
        });
        e.preventDefault();
    });
        
    $('#linkCategoryId').change(function (e) {
        $('#linkSubCategoryId').html('');
        var options = '<option value="">-- alle subcategorieën --</option>';
        
        if ($('#linkCategoryId').val().length) {
            $.ajax(
                {
                    type: "POST",
                    url: "$sURL",
                    data: 'action=getSubCategories&linkCategoryId=' + $('#linkCategoryId').val() + '&ajax=1',
                    async: true,
                    success: function (data) {
                        var dataObj = eval( '(' + data + ')');
                        
                        $.each(dataObj.subCategories, function (index, sub) {
                            options += '<option value="' + sub.customerLinkCategoryId + '">' + sub.name + '</option>';
                        });
                        $('#linkSubCategoryId').html(options);
                    }
                }
            );
        }
        else {
            $('#linkSubCategoryId').html(options);
        }
    });
</script>
EOT;
$oPageLayout->addJavascriptBottom($sBottomJavascript);
?>