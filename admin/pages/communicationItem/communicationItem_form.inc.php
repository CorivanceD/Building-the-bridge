<div id="topOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>">Terug naar overzicht</a><span class="backBtnInfo"> (zonder opslaan)</span>
</div>
<div class="cf">
    <div class="contentColumn">
        <form method="POST" action="" class="validateForm">
            <input type="hidden" value="save" name="action" />
            <fieldset>
                <legend>Bericht</legend>
                <table class="withForm">
                    <tr>
                        <td width="60">Type *</td>
                        <td>
                            <? if (!is_numeric($oCommunicationItem->communicationItemId)) { ?>
                                <input class="alignRadio {validate:{required:true}} typeTemplate" title="Bepaal het type bericht" type="radio" <?= $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_SMS ? 'CHECKED' : '' ?> id="type_sms" name="type" value="<?= CommunicationItem::COMMUNICATION_TYPE_SMS ?>" data-ajaxurl="<?= ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-getTemplatesByType/' . CommunicationItem::COMMUNICATION_TYPE_SMS ?>" /> <label for="type_sms">SMS</label>
                                <input class="alignRadio {validate:{required:true}} typeTemplate" title="Bepaal het type bericht" type="radio" <?= $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL ? 'CHECKED' : '' ?> id="type_email" name="type" value="<?= CommunicationItem::COMMUNICATION_TYPE_EMAIL ?>" data-ajaxurl="<?= ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-getTemplatesByType/' . CommunicationItem::COMMUNICATION_TYPE_EMAIL ?>" /> <label for="type_email">E-mail</label>
                            <? } else { ?>
                                <i><?= $oCommunicationItem->type ?></i>
                            <? } ?>
                        </td>
                        <td><span class="error"><?= $oCommunicationItem->isPropValid("type") ? '' : 'Veld niet (juist) ingevuld' ?></span></td>
                    </tr>
                </table>
                <table class="commTypeEmail withForm <? if (!is_numeric($oCommunicationItem->communicationItemId)) { ?>hide<? } ?>" <? if (!is_numeric($oCommunicationItem->communicationItemId)) { ?>id="propertiesCommunicationItem" <? } ?> style="width: 100%">
                    <? if (!is_numeric($oCommunicationItem->communicationItemId)) { ?>
                        <tr>
                            <td class="withLabel"><label for="templateId">Template</label></td>
                            <td colspan="2">
                                <select onchange="getTemplateData(this);" name="templateId" id="templateId" data-ajaxurl="<?= ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-getTemplateData/' ?>">
                                </select>
                                <img src="<?= ADMIN_IMAGE_FOLDER ?>/layout/ajax-loader.gif" alt="Loading" width="220" height="19" id="ajaxloaderTemplateChoice" class="hide">
                            </td>
                        </tr>
                    <? } ?>
                    <tr>
                        <td colspan="3">
                            <textarea name="message" id="message" class="tiny_MCE_default tiny_MCE"><?= $oCommunicationItem->message ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="title">Titel *</label></td>
                        <td><input size="50" id="title" class="{validate:{required:true}}" title="Titel" type="text" name="title" value="<?= _e($oCommunicationItem->title) ?>" /></td>
                        <td><span class="error"><?= $oCommunicationItem->isPropValid("title") ? '' : 'Veld niet (juist) ingevuld' ?></span></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="subject">Onderwerp *</label></td>
                        <td><input size="50" id="subject" class="{validate:{required:true}}" title="Vul het onderwerp in" type="text" name="subject" value="<?= _e($oCommunicationItem->subject) ?>" /></td>
                        <td><span class="error"><?= $oCommunicationItem->isPropValid("subject") ? '' : 'Veld niet (juist) ingevuld' ?></span></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="senderName">Afzender * <?= $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_SMS ? '<br>(maximale lengte ' . CommunicationItem::MAX_LENGTH_SMS_SENDERNAME . ' karakters)' : '' ?></label></td>
                        <td><input size="50" id="senderName" class="{validate:{required:true}}" title="Vul de naam van de afzender in" type="text" name="senderName" value="<?= _e($oCommunicationItem->senderName) ?>" /></td>
                        <td><span class="error"><?= $oCommunicationItem->isPropValid("senderName") ? '' : 'Veld niet (juist) ingevuld' ?></span></td>
                    </tr>
                    <tr>
                        <td class="senderEmailOption" class="withLabel"><label for="senderEmail">E-mail afzender *</label></td>
                        <td class="senderEmailOption">
                            <input size="50" readonly="readonly" id="senderEmail" class="{validate:{required:true}}" title="Vul het e-mailadres van de afzender in" type="text" name="senderEmail" value="<?= _e($oCommunicationItem->senderEmail) ?>" />
                            <?
                            if (is_numeric($oCommunicationItem->communicationItemId)) {
                                if ($oCommunicationItem->hasValidSPFrecord()) {
                                    echo '<span style="color: #88ba01">(goedgekeurd voor verzending)</span>';
                                } else {
                                    echo '<span style="color: #f00">(niet goedgekeurd voor verzending)</span>';
                                }
                            }
                            ?>
                        </td>
                        <td class="senderEmailOption"><span class="error"><?= $oCommunicationItem->isPropValid("senderEmail") ? '' : 'Veld niet (juist) ingevuld' ?></span></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="groups">Groep(en)</label></td>
                        <td>
                            <?
                            foreach (CustomerGroupManager::getAllCustomerGroups() as $oCustomerGroup) {
                                $iAmountOfClients = count($oCustomerGroup->getClients());
                                ?>
                                <label><input type="checkbox" name="customerGroupIds[]" value="<?= $oCustomerGroup->customerGroupId ?>" <?= $oCommunicationItem->isLinkedToCustomerGroup($oCustomerGroup->customerGroupId) ? 'checked="checked"' : '' ?> class="alignCheckbox"> <?= _e($oCustomerGroup->title . ' (' . $iAmountOfClients . ' klant' . ($iAmountOfClients !== 1 ? 'en' : '' ) . ')') ?></label>
                                <br>
                            <? } ?>
                        </td>
                        <td><span class="error"><?= $oCommunicationItem->isPropValid("subject") ? '' : 'Veld niet (juist) ingevuld' ?></span></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <hr>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <input type="submit" value="Opslaan" name="save" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            &nbsp;
                        </td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </div>
    <div class="contentColumn">
        <fieldset>
            <legend>Template variabelen <div class="tooltip hasTooltip" title="Gebruik deze variabelen in uw tekst/onderwerp en ze zullen worden vervangen<br/> door de echte waarde bij het versturen van de template">&nbsp;</div></legend>
            <p id="templateVariables">

            </p>
        </fieldset>
        <? if ($oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) { ?>
            <fieldset>
                <legend>Afbeeldingen</legend>
                <?
                if ($oCommunicationItem->communicationItemId !== null) {
                    $oImageManagerHTML->includeTemplate();
                } else {
                    echo '<p><i>Afbeeldingen kunnen worden geüpload nadat het bericht eerst is opgeslagen</i></p>';
                }
                ?>
            </fieldset>
            <fieldset>
                <legend>Stuur een testbericht</legend>
                <? if (!empty($aVerificationCustomers)) { ?>
                    <p>Selecteer de klanten die het testbericht mogen ontvangen</p>
                    <p class="hide" id="sendedMessage">Het bericht is naar de geselecteerde personen verstuurd</p>
                    <div class="selectedVerificationCustomers">
                        <? foreach ($aVerificationCustomers as $oTestCustomer) { ?>
                            <label>
                                <input type="checkbox" value="<?= $oTestCustomer->customerId ?>" name="customerIds[]" checked="checked">
                                <img src="<?= ADMIN_IMAGE_FOLDER ?>/layout/ajax-loader-small.gif" alt="Versturen" width="24" height="16" class="hide" id="loader_<?= $oTestCustomer->customerId ?>">
                                <?= $oTestCustomer->getFullName() . ' - ' . $oTestCustomer->email ?>
                            </label>
                            <br>
                        <? } ?>
                    </div>
                    <br>
                    <input type="submit" value="Verzend verificatie" onclick="btnSendVerification();" style="margin-bottom: 12px;">
                <? } else { ?>
                    <p>Geen klanten gevonden die verificaties uit de communicatie module mogen ontvangen.</p>
                <? } ?>
            </fieldset>
        <? } ?>
    </div>
    <?
    if (!empty($oCommunicationItem->communicationItemId) && $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL && $oCommunicationItem->getTemplate() !== null && $oCommunicationItem->getTemplate()->templateId == Template::NEWSLETTER_TEMPLATE_ID) {
        ?>
        <div class="contentColumn">
            <fieldset style="margin-bottom: 20px;">
                <legend><?= sysTranslations::get('global_articles') ?> / Open letters</legend>
                <form action="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>" method="POST" id="filterForm">                
                    <input type="hidden" id="communicationItemId" value="<?= $oCommunicationItem->communicationItemId ?>" name="newsletterItemFilter[communicationItemId]" /> 
                    <fieldset style="margin-bottom: 20px;">
                        <legend><?= sysTranslations::get('global_filter') ?></legend>
                        <table class="withForm">
                            <tr>
                                <td class="withLabel" style="width: 116px;"><?= sysTranslations::get('global_search_word') ?></td>
                                <td><input type="text" id="newsletterItemFilter" name="newsletterItemFilter[q]" value="<?= _e($aArticleFilter['q']) ?>" /></td>
                            </tr>
                            <tr>
                                <td class="withLabel" style="width: 116px;">Letters only</td>
                                <td><input type="checkbox" id="onlyLetters" name="newsletterItemFilter[onlyLetters]" value="1" <?= !empty($aArticleFilter['onlyLetters']) ? 'checked' : '' ?>/></td>
                            </tr>
                            <tr>
                                <td class="withLabel"><?= sysTranslations::get('global_author') ?></td>
                                <td>
                                    <select name="newsletterItemFilter[authorId]">
                                        <option value="">-- <?= sysTranslations::get('global_all') ?> --</option>
                                        <? foreach (UserManager::getUsersByFilter() AS $oUser) { ?>
                                            <option value="<?= $oUser->userId ?>" <?= (isset($aArticleFilter['authorId']) && ($oUser->userId == $aArticleFilter['authorId'])) ? 'selected' : '' ?> ><?= $oUser->name ?></option>
                                        <? } ?>
                                    </select>
                                </td>
                            </tr>            
                            <tr>
                                <td class="withLabel"><label><?= ucfirst(sysTranslations::get('global_category')) ?></label></td>
                                <td>
                                    <select name="newsletterItemFilter[articleCategoryId]">
                                        <option value="">-- <?= sysTranslations::get('global_all_categories') ?> --</option>
                                        <?
                                        foreach (ArticleCategoryManager::getArticleCategoriesByFilter(array('showAll' => true)) AS $oArticleCategory) {
                                            echo '<option ' . ($aArticleFilter['articleCategoryId'] === $oArticleCategory->articleCategoryId ? 'selected' : '') . ' value="' . $oArticleCategory->articleCategoryId . '">' . $oArticleCategory->name . '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="withLabel"><label><?= ucfirst(sysTranslations::get('global_status')) ?></label></td>
                                <td>
                                    <select name="newsletterItemFilter[usedBefore]">
                                        <option value="">-- <?= sysTranslations::get('global_all_status') ?> --</option>
                                        <option value="<?= Article::ARTICLE_USED_BEFORE ?>" <?= $aArticleFilter['usedBefore'] == Article::ARTICLE_USED_BEFORE ? 'selected' : '' ?> ><?= sysTranslations::get('communication_article_used') ?></option>
                                        <option value="<?= Article::ARTICLE_NOT_USED_BEFORE ?>" <?= $aArticleFilter['usedBefore'] == Article::ARTICLE_NOT_USED_BEFORE ? 'selected' : '' ?> ><?= sysTranslations::get('communication_article_not_used') ?></option>
                                    </select>
                                </td>
                            </tr>                        
                            <tr>
                                <td>&nbsp;</td>
                                <td><input type="submit" id="filterArticles" name="filterArticles" value="<?= sysTranslations::get('articles_filter') ?>" /> <input type="submit" id="resetArticles" name="resetFilter" value="<?= sysTranslations::get('global_reset_filter') ?>" /></td>
                            </tr>
                        </table>
                    </fieldset>
                </form>
                <form method="POST">
                    <table class="sorted" style="margin-bottom:10px">
                        <thead>
                            <tr class="topRow">
                                <td colspan="7"><h2><?= sysTranslations::get('articles_all') ?></h2><div class="right"></td>
                            </tr>
                            <tr>
                                <th style="width: 100px;"><?= sysTranslations::get('global_date') ?></th>
                                <th >Type</th>
                                <th ><?= sysTranslations::get('global_title') ?></th>                                
                                <th ><?= sysTranslations::get('global_author') ?></th>                
                                <th class="{sorter:false} nonSorted"><?= ucfirst(sysTranslations::get('communication_article_used')) ?></th> 
                                <th class="{sorter:false} nonSorted"><?= sysTranslations::get('global_included') ?></th> 
                                <th class="{sorter:false} nonSorted" style="width: 40px;">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody id="articles">
                            <?
                            $bOdd = true;

                            foreach ($aArticles AS $oArticle) {
                                echo '<tr class="' . ($bOdd ? 'odd' : 'even') . '">';
                                echo '<td>' . Date::strToDate($oArticle->pubDate)->format('%d-%m-%Y') . '</td>';
                                echo '<td>' . ($oArticle->getWorldLeaderLetter() == null ? 'Art.' : 'Let.') . '</td>';
                                echo '<td>' . _e($oArticle->title) . '</td>';
                                echo '<td>' . $oArticle->getAuthor()->name . '</td>';
                                echo '<td style="text-align:center"><input disabled type="checkbox" ' . ($oArticle->wasAlreadySent() ? 'checked' : '') . '></td>';
                                echo '<td style="text-align:center"><input type="checkbox" value="' . $oArticle->articleId . '" class="includeArticle" ' . ($oArticle->isIncludedInNewsletter($oCommunicationItem->communicationItemId) ? 'checked' : '') . '></td>';
                                echo '<td>';
                                echo '<a class="action_icon magnifying_glass_icon" title="' . sysTranslations::get('articles_review') . '" href="' . $oArticle->getUrlPath() . '" target="_blank"></a>';
                                echo '</td>';
                                echo '</tr>';
                                $bOdd != $bOdd;
                            }

                            if (count($aArticles) == 0) {
                                echo '<tr><td colspan="7"><i>' . sysTranslations::get('articles_no_articles') . '</i></td></tr>';
                            }
                            ?>
                        </tbody>
                </form>
                <tfoot>
                    <tr class="bottomRow">
                        <td colspan="7">
                            <form method="POST">
                                <span id="pagination"><?= generatePaginationHTML($iPageCount, $iCurrPage) ?></span>
                                <input type="hidden" name="setPerPage" value="1" />
                                <select id="perPage" name="perPage">
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

        </div>

    <? } ?>
</div>
<div id="bottomOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>">Terug naar overzicht</a><span class="backBtnInfo"> (zonder opslaan)</span>
</div>

<?
# create necessary javascript
$sAdminFolder = ADMIN_FOLDER;
$sBottomJavascript = '';

# only when being type e-mail, set tinyMce
$iRandomNumber = rand(1, 10000000);
$sCssPath = CommunicationItem::CSS_PATH;
$jsLang = empty($oCurrentUser) ? 'nl' : $oCurrentUser->getLanguage()->abbr;
$sAdminFolder = ADMIN_FOLDER;
$sController = http_get('controller');
$iCommunicationItemId = http_get('param2');
if ($oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) {
    $sBottomJavascript .= <<<EOT
<script type="text/javascript">
    initTinyMCE(".tiny_MCE_default", "/admin/communicatie/link-list", "/admin/communicatie/image-list/{$oCommunicationItem->communicationItemId}", null, null, '{$sCssPath}?cache={$iRandomNumber}',  "$jsLang");
</script>
EOT;
}

$sBottomJavascript .= <<<EOT
<script type="text/javascript">
    
    var tinyMceId = $(document).find('textarea').attr('id');
        
    $(".typeTemplate").click(function() {
        
        // clear template vars
        $('#templateVariables').html('');
        
        // get corresponding items for template type
        $.ajax({
            beforeSend: function() {
                $('#ajaxloaderTemplateChoice').show();
                $('#templateId').hide();
            },
            type: "GET",
            url: $(this).data('ajaxurl'),
            data: "",
            async: true,
            success: function(data){

                var dataObj = eval('(' + data + ')');

                /* When having result */
                $("#title").val('');
                $("#subject").val('');
                $("#message").val('');
                if(dataObj.hasOptions == true){
                    $("#templateId").html(dataObj.htmlOptions); // set template options
                } else {
                    showStatusUpdate("Geen opties gevonden voor dit type bericht");
                }
        
                $('#ajaxloaderTemplateChoice').hide();
                $('#templateId').show();

            }
        });
        
        // determine if emailfield must be shown
        if(this.value == 'email') {
            $('.senderEmailOption').show();
        } else if(this.value == 'sms') {
            $('.senderEmailOption').hide();
        }

        // show properties
        $('#propertiesCommunicationItem').show();
        
        if(this.value == 'email') {
            initTinyMCE(".tiny_MCE_default", "/admin/communicatie/link-list", "", "", '100%', '{$sCssPath}?cache={$iRandomNumber}' , "$jsLang");
        } else {
            tinymce.remove("#"+tinyMceId);
        }
        
    });
        
    // alter show/hide behaviour message field for message type
    function changeMessageFieldOptions(value) {
        if(value == 'sms') {
            $('.senderEmailOption').hide();
            tinymce.remove("#"+tinyMceId);
        } else if(value == 'email') {
            $('.senderEmailOption').show();
            initTinyMCE(".tiny_MCE_default", "/admin/communicatie/link-list", "", "", '100%', '{$sCssPath}?cache={$iRandomNumber}', "$jsLang");
        }
    }
    
    // get data from template
    function getTemplateData(e) {
        $.ajax({
            type: "GET",
            url: $(e).data('ajaxurl')+'/'+$(e).val(),
            data: "",
            async: true,
            success: function(data){
                var dataObj = eval('(' + data + ')');
        
                /* When having result */
                if(dataObj.success == true){
                    $("#title").val(dataObj.title); // prefill title
                    $("#subject").val(dataObj.subject); // prefill subject
                    $("#senderName").val(dataObj.senderName); // prefill senderName
                    $("#senderEmail").val(dataObj.senderEmail); // prefill senderEmail
                    $("#message").val(dataObj.message); // prefill message
                    if(tinyMCE !== undefined) {
                        tinyMCE.activeEditor.setContent(dataObj.message);
                    }
                } else {
                    $("#title").val('');
                    $("#subject").val('');
                    $("#message").val('');
                    if(tinyMCE !== undefined) {
                        tinyMCE.activeEditor.setContent('');
                    }
                }
                
            }
        });
    }
    
    // get template var groups when changing select options
    $('#templateId').change(function() {
        var templateGroupId = $(this).find("option:selected").data('templategroupid');
        getTemplateGroupVars(templateGroupId);
    });
    
    // get template var groups
    function getTemplateGroupVars(templateGroupId) {
        if(templateGroupId !== undefined) {
            $.ajax({
                url: '{$sAdminFolder}/templates-beheer/ajax-getTemplateVariables?templateGroupId='+templateGroupId,
                success: function(data){
                    var dataObj = eval('('+data+')');
                    if(dataObj.success){
                        $('#templateVariables').html(dataObj.sHtml);
                    }else{
                        alert('Kon template variabelen niet ophalen');
                    }
                }
            });
        } else {
            $('#templateVariables').html('');
        }    
    }

</script>
EOT;

# create necessary javascript
if (http_get('param1') == 'bewerken' && is_numeric(http_get('param2'))) {
    $sBottomJavascript .= <<<EOT
    <script type="text/javascript">
        // initialise message field
        initMessageFieldOptions('{$oCommunicationItem->type}');
        // alter show/hide behaviour message field for message type
        function initMessageFieldOptions(value) {
            if(value == 'sms') {
                $('.senderEmailOption').hide();
            } else if(value == 'email') {
                $('.senderEmailOption').show();
            }
        }
        

        var iVerificationCounter = 0;
        function btnSendVerification() {
            iVerificationCounter = 0;
            var selectedCustomers = $('.selectedVerificationCustomers input[type=checkbox]:checked');
            sendVerification(selectedCustomers);
        }

        // sends verification to test customer(s)
        function sendVerification(selectedCustomers) {
            var amountOfReceivers = selectedCustomers.length;
            var customerId = $(selectedCustomers[iVerificationCounter]).val();
            $.ajax({
                type: "POST",
                async: true,
                beforeSend: function() {
                        $('#loader_'+customerId).prev().hide();
                        $('#loader_'+customerId).fadeIn();
                },
                url: '{$sAdminFolder}/{$sController}/ajax-sendMessage',
                data: {
                    iCommunicationItemId: {$iCommunicationItemId},
                    iCustomerId: customerId,
                    bIsVerification: true
                },
                success: function(data){
                    var dataObj = eval('('+data+')');
                    if(dataObj.success == true){
                        $('#loader_'+customerId).hide();
                        $('#loader_'+customerId).prev().fadeIn();
                        iVerificationCounter++

                        // check if needs to send more
                        if(iVerificationCounter < amountOfReceivers) {
                            sendVerification(selectedCustomers);
                        } else {
                            $('#sendedMessage').fadeIn(300).delay(2500).fadeOut();
                        }
                    }
                    else if(dataObj.success == false && dataObj.errorMsg != '') {
                        alert(dataObj.errorMsg);
                        return false;
                    }
                }
            })
        }
    </script>
EOT;
}

# create necessary javascript
if (http_get('param1') == 'bewerken' && is_numeric(http_get('param2')) && !empty($oCommunicationItem->getTemplate()->templateGroupId)) {
    $sBottomJavascript .= <<<EOT
    <script type="text/javascript">
        // get template variables when linked to template
        getTemplateGroupVars({$oCommunicationItem->getTemplate()->templateGroupId});
    </script>
EOT;
}

if (!empty($oCommunicationItem->communicationItemId) && $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL && $oCommunicationItem->getTemplate() !== null && $oCommunicationItem->getTemplate()->templateId == Template::NEWSLETTER_TEMPLATE_ID) {
    $sBottomJavascript .= <<<EOT
    <script>
        function onPageClick (e) {            
            $.ajax({
                type: "POST",
                async: true,
                url: '{$sAdminFolder}/{$sController}/ajax-setArticlesPage',
                data: $(this).attr('href').substr(1) + '&communicationItemId=$oCommunicationItem->communicationItemId',
                success: 
                    function(data){
                        var dataObj = eval('('+data+')'); 
                        console.log(dataObj.articleTable);
                        $('#articles').html(dataObj.articleTable);
                        $('#pagination').html(dataObj.pagination);
                        $('.pagination a').bind('click', onPageClick);
                        $('.includeArticle').bind('change', onIncludeArticle);  
                    }
            });

            e.preventDefault();
        }  
        
        $('.pagination a').bind('click', onPageClick);
                                
        function onIncludeArticle(e) {
            $.ajax({
                type: "POST",
                async: true,
                url: '{$sAdminFolder}/{$sController}/ajax-includeArticle',
                data: 'include=' + ($(this).is(':checked') ? 1 : 0) + '&communicationItemId=$oCommunicationItem->communicationItemId' + '&articleId='+$(this).val(),
                success: 
                    function(data){
                        var dataObj = eval('('+data+')');                                 
                        showStatusUpdate(dataObj.statusUpdate);                            
                    }
            });

            e.preventDefault();        
        }              

        $('.includeArticle').bind('change', onIncludeArticle);                   
                            
        
        $('#filterArticles').click(
            function(e) {
                $.ajax({
                    type: "POST",
                    async: true,
                    url: '{$sAdminFolder}/{$sController}/ajax-filterArticles',
                    data: $('#filterForm').serialize(),
                    success: 
                        function(data){
                            var dataObj = eval('('+data+')'); 
                           
                            $('#articles').html(dataObj.articleTable);
                            $('#pagination').html(dataObj.pagination);
                            $('.pagination a').bind('click', onPageClick);
                            $('.includeArticle').bind('change', onIncludeArticle);  
                        }
                });
                    
                e.preventDefault();
            }
        );
                    
        $('#resetArticles').click(
            function(e) {
                $.ajax({
                    type: "POST",
                    async: true,
                    url: '{$sAdminFolder}/{$sController}/ajax-resetArticleFilter',
                    data: 'communicationItemId=$oCommunicationItem->communicationItemId',
                    success: 
                        function(data){
                            var dataObj = eval('('+data+')'); 
                            $('input[name="newsletterItemFilter[q]"]').val('');    
                            $('select[name="newsletterItemFilter[authorId]"]').val('');    
                            $('select[name="newsletterItemFilter[articleCategoryId]"]').val('');    
                            $('select[name="newsletterItemFilter[usedBefore]"]').val('');  
                            $('input[name="newsletterItemFilter[onlyLetters]"]').prop('checked', false); 
                            $('#articles').html(dataObj.articleTable);
                            $('#pagination').html(dataObj.pagination);
                            $('.pagination a').bind('click', onPageClick);
                            $('.includeArticle').bind('change', onIncludeArticle);  
                        }
                });
                    
                e.preventDefault();
            }
        );  
            
        $('#perPage').change(
            function (e) {
                $.ajax({
                    type: "POST",
                    async: true,
                    url: '{$sAdminFolder}/{$sController}/ajax-setArticlesPerPage',
                    data: 'perPage='+$(this).val() + '&communicationItemId=$oCommunicationItem->communicationItemId',
                    success: 
                        function(data){
                            var dataObj = eval('('+data+')'); 

                            $('#articles').html(dataObj.articleTable);
                            $('#pagination').html(dataObj.pagination);
                            $('.pagination a').bind('click', onPageClick);
                            $('.includeArticle').bind('change', onIncludeArticle);  
                        }
                });                            
            }
        );                    
    </script>
EOT;
}
# write JS
$oPageLayout->addJavascriptBottom($sBottomJavascript);
?>
