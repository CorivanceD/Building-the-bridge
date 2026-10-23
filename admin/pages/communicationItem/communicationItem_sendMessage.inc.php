<div id="topOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>">Terug naar het berichten overzicht</a><span class="backBtnInfo"> (zonder opslaan)</span>
</div>
<div class="cf">
    <div class="contentColumn">
        <fieldset>
            <legend>Berichteigenschappen</legend>
            <table>
                <tr>
                    <td style="width: 100px">Titel:</td><td><?= _e($oCommunicationItem->title) ?></td>
                </tr>
                <tr>
                    <td>Type:</td><td><?= $oCommunicationItem->type ?></td>
                </tr>
                <?php if ($oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) { ?>
                    <tr>
                        <td>Onderwerp:</td><td><?= _e($oCommunicationItem->subject) ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td>Afzendernaam:</td><td><?= _e($oCommunicationItem->senderName) ?></td>
                </tr>
                <?php if ($oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) { ?>
                    <tr>
                        <td>Afzender e-mail:</td><td><?= _e($oCommunicationItem->senderEmail) ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td>Bericht:</td><td><a class="pointer fancyBoxLinkIframe fancybox.iframe" href="<?= ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-getMessage?communicationItemId=' . $oCommunicationItem->communicationItemId ?>">Toon bericht</a></td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <?php if ($oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_SMS) { ?>
                <tr>
                    <td colspan="3">
                        <input type="submit" value="Verzend bericht naar geselecteerde ontvangers" name="buttonSubmit" id="sendMessage" />
                        <img src="<?= ADMIN_IMAGE_FOLDER ?>/layout/ajax-loader.gif" alt="Versturen" width="220" height="19" id="ajaxSendMessageImage" class="hide">
                        <p class="hide" id="ajaxSendMessageText" style="font-weight: bold">Het bericht wordt verzonden, laat dit scherm open staan</p>
                    </td>
                </tr>
                <?php } else { ?>
                <tr>
                    <td colspan="3">
                        <?php if($oCommunicationItem->hasValidSPFrecord()) { ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="sendTrusted" />
                                <button id="buttonSendTrusted" class="send">Verzend bericht naar geselecteerde ontvangers</button>
                                <input type="submit" class="hide" value="Verzend bericht naar geselecteerde ontvangers" name="buttonSubmit" id="buttonSubmit" />
                            </form>
                        <?php } else { ?>
                            <p class="error">Het SPF record is niet (goed) ingesteld. U kunt daarom het bericht niet versturen.</p>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
            </table>
        </fieldset>
    </div>
    <div class="contentColumn">
        <br>
        <div id="progressBarWidthParent">
            <div id="progressBarWidth">0%</div>
        </div>
        <div id="contentColumnRight">
            <fieldset>
                <br>
                <legend>
                    <?php if ($oCommunicationItem->sending) { ?>
                        Het bericht zal gecontinueerd worden naar de volgende klant<?= !empty($aUniqueReceivers) && count($aUniqueReceivers) > 1 ? 'en' : '' ?>:
                    <?php } else { ?>
                        Dit bericht zal verzonden worden naar de volgende klant<?= !empty($aUniqueReceivers) && count($aUniqueReceivers) > 1 ? 'en' : '' ?>:
                    <?php } ?>
                </legend>
                <?php
                # write out receivers of this message
                if (!empty($aUniqueReceivers)) {
                    echo '<ul id="receiversList" style="margin-left: 20px">';
                    switch ($oCommunicationItem->type) {
                        case CommunicationItem::COMMUNICATION_TYPE_EMAIL:
                            $iAmountCounter = 0;
                            foreach ($aUniqueReceivers as $oUniqueReceiver) {
                                $iAmountCounter++;
                                echo '<li data-customerid=' . $oUniqueReceiver->customerId . ' data-amountid=' . $iAmountCounter . '><a href="' . ADMIN_FOLDER . '/klanten/bewerken/' . $oUniqueReceiver->customerId . '">' . $oUniqueReceiver->getFullName() . '</a> - <a href="mailto:' . $oUniqueReceiver->email . '">' . $oUniqueReceiver->email . '</a></li>';
                            }
                            break;

                        case CommunicationItem::COMMUNICATION_TYPE_SMS:

                            # check whether replacement of values is needed
                            $bNeedReplacement = false;
                            if (!empty($oCommunicationItem->templateId) && is_numeric($oCommunicationItem->templateId)) {
                                $oTemplate = TemplateManager::getTemplateById($oCommunicationItem->templateId);
                                if (!empty($oTemplate)) {
                                    $oTemplateGroup = TemplateManager::getTemplateGroupById($oTemplate->templateGroupId);
                                    $bNeedReplacement = !empty($oTemplateGroup) ? true : false;
                                }
                            }

                            # loop trough users and check if SMS will be split
                            foreach ($aUniqueReceivers as $oUniqueReceiver) {

                                # fetch message
                                $oCommunicationItem = CommunicationItemManager::getCommunicationItemById(http_get('param2'));

                                $sMessagePreview = $oCommunicationItem->message;
                                
                                # when linked to template, replace vars are available
                                if ($bNeedReplacement) {
                                    $oCommunicationItem->replaceVariables($oUniqueReceiver);
                                    $sMessagePreview = $oCommunicationItem->message;
                                }

                                # calculate length of message
                                $iCharCountMessage = strlen($sMessagePreview);

                                # check whether special chars are found (encoding these requires two chars)
                                $aSpecialChars = array("^", "{", "}", "\\", "[", "~", "]", "|", "€");
                                $iExtraCharsTotal = 0;
                                foreach ($aSpecialChars as $sSpecialChar) {
                                    $iExtraChars = substr_count($sMessagePreview, $sSpecialChar);
                                    $iExtraCharsTotal += $iExtraChars;
                                }
                                # add special chars count
                                $iCharCount = $iCharCountMessage + $iExtraCharsTotal;

                                # needs to split over more than one message?
                                $iAmountOfSmsItems = ceil($iCharCount / 160);

                                $bWarning = $iAmountOfSmsItems > 1 ? true : false;

                                echo '<li data-customerid=' . $oUniqueReceiver->customerId . '><a href="' . ADMIN_FOLDER . '/klanten/bewerken/' . $oUniqueReceiver->customerId . '">' . $oUniqueReceiver->getFullName() . '</a> - ' . $oUniqueReceiver->mobilePhone . ' ' . ($bWarning === true ? '(<span>bericht wordt over ' . $iAmountOfSmsItems . ' SMSjes verdeelt <div class="action_icon magnifying_glass_icon" title="' . $sMessagePreview . '" style="margin-top: -2px"></div></span>)' : '' ) . '</li>';
                            }
                            break;
                    }
                    echo '</ul>';
                }
                # no receivers
                else {
                    echo '<p>Er zijn geen ontvangers gevonden</p>';
                }
                ?>
                <br>
            </fieldset>
        </div>
        <div id="sendedMessage" class="hide">Het bericht is verzonden</div>
    </div>
</div>
<div id="bottomOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>">Terug naar het berichten overzicht</a><span class="backBtnInfo"> (zonder opslaan)</span>
</div>

<?php
$sAdminFolder = ADMIN_FOLDER;
$sController = http_get('controller');
$iCommunicationItemId = http_get('param2');
$sBottomJavascript = '';

if( $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_SMS ) {

$sJsArrayValues = '';
foreach ($aUniqueReceivers as $oUniqueReceiver) {
    $sJsArrayValues .= '"' . $oUniqueReceiver->customerId . '",';
}
$sJsArrayValues = rtrim($sJsArrayValues, ',');
?>

<script>
   // set progressCounter
    var counter = 0;
    var customerIds = [<?= $sJsArrayValues ?>];
</script>

<?php
# error? define where to go from
if ($oCommunicationItem->sending == 1) {

    // calculate progress (aborted/failed)
    $iTotalCustomerAmount = CommunicationItemManager::getTotalAmountOfCustomersFromSendlistByCommunicationId($iCommunicationItemId);
    $iTotalCustomerAmountSended = CommunicationItemManager::getTotalAmountOfCustomersFromSendlistByCommunicationId($iCommunicationItemId, true);
    $sBottomJavascript .= <<<EOT
<script>
   var logStart = false;
   // get total amount of customers marked for mailing
    var customerIds = [{$sJsArrayValues}];
    var totalCustomerAmount = {$iTotalCustomerAmount};
    var customersAlreadySended = totalCustomerAmount - customerIds.length;
    var progressBarWidth = (customersAlreadySended / totalCustomerAmount) * 100;
    $('#progressBarWidth')
        .width(progressBarWidth + "%")
        .html(Math.round(progressBarWidth*100)/100 + "%");
    var customerAmount = totalCustomerAmount;
    var progressCounter = {$iTotalCustomerAmountSended};
    
</script>
EOT;
} else {
$sBottomJavascript .= <<<EOT
<script>
    var logStart = true;
    var progressCounter = 0;
    var customerAmount = customerIds.length;
</script>
EOT;
}

$sBottomJavascript .= <<<EOT
<script>

    $('#sendMessage').click(function(e){
        $(this).hide();
        $('#ajaxSendMessageImage').show();
        $('#ajaxSendMessageText').show();
        e.preventDefault();
        setTimeout(function(){
            sendMessage();
        }, 250);
    });
                    
    
    function sendMessage(){
        $.ajax({
         type: "POST",
         async: true,
         beforeSend: function() {
             if(counter == 0 && logStart == true) {
                 markMessageAsSending();
             }
         },
         url: '{$sAdminFolder}/{$sController}/ajax-sendMessage',
         data: {
             iCommunicationItemId: {$iCommunicationItemId},
             iCustomerId: customerIds[counter]
         },
         success: function(data){
             var dataObj = eval('('+data+')');
             if(dataObj.success == true){
                 // update view
                 progressCounter++;
                 $('#receiversList').find("[data-customerid='" + dataObj.customerId + "']").remove();
                 var progressBarWidth = 100 * (progressCounter / customerAmount);
                 $('#progressBarWidth')
                     .width(progressBarWidth + "%")
                     .html(Math.round(progressBarWidth*100)/100 + "%");
                 // detect when totally sended. Update status of comminucationItem
                 if(progressCounter == customerAmount) {
                     markMessageAsSend();
                } else {
                    counter++;
                    sendMessage();
                }
             }
             else if(dataObj.success == false && dataObj.errorMsg != '') {
                 $('#ajaxSendMessageImage').hide();
                 $('#sendMessage').fadeIn();
                 alert(dataObj.errorMsg);
                 return false;
             }
         }
     }); 
   }
                
   // mark message as send after completion
    function markMessageAsSend() {
        $.ajax({
            type: "POST",
            async: false,
            url: '{$sAdminFolder}/{$sController}/ajax-markMessageAsComplete',
            data: {
                iCommunicationItemId: {$iCommunicationItemId}
            },
            success: function(data){
                var dataObj = eval('('+data+')');
                if(dataObj.success == true) {
                    $('#contentColumnRight').hide();
                    $('#ajaxSendMessageImage, #ajaxSendMessageText').fadeOut(500, function() {
                        $('#progressBarWidth').parent().fadeOut();                                         
                        $('#sendedMessage').fadeIn(400, function() {
                            $('#sendedMessage').delay(5000).fadeOut(400, function() {
                                // send to statistics 
                                window.location='{$sAdminFolder}/{$sController}/gebruikers/{$iCommunicationItemId}';                                
                            });
                        });
                    });
                } else {
                    alert('Mailing kon niet worden gemarkeerd als "Verzonden"');
                }
            }
        });
    }
                
   // mark message as sending
    function markMessageAsSending() {
        $.ajax({
            type: "POST",
            async: false,
            url: '{$sAdminFolder}/{$sController}/ajax-markMessageAsSending',
            data: {
                iCommunicationItemId: {$iCommunicationItemId}
            },
            success: function(data){
                var dataObj = eval('('+data+')');
                if(dataObj.success == false) {
                    alert('Mailing kon niet worden gemarkeerd als "Wordt verzonden"');
                }
            }
        });
    }
</script>
EOT;

# write JS
$oPageLayout->addJavascriptBottom($sBottomJavascript);
}

elseif($oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) {
    $sBottomJavascript .= <<<EOT
    <script>
        $('#progressBarWidthParent').hide().prev().hide();
        $('#buttonSendTrusted').click(function(e){
            $(this).hide();
            e.preventDefault();
            $(this).closest('form').submit();
        });  
    </script>
EOT;

    # write JS
    $oPageLayout->addJavascriptBottom($sBottomJavascript);
}
?>