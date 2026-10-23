<div class="cf">
    <div class="contentColumn">
        <form method="POST" class="validateForm" id="chatForm">
            <input type="hidden" id="action" name="action" value="startNew">
            <fieldset>
                <legend><?= sysTranslations::get('global_chat_mgmt') ?></legend>
                <table class="withForm">
                    <tr>
                        <td class="withLabel" style="width:150px"><label for="chatTopic"><?= sysTranslations::get('chat_topic') ?> *</label> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('chat_topic_tooltip') ?>">&nbsp;</div></td>
                        <td><input style="width:120px" size="50" id="chatTopic" class="{validate:{required:true}}" title="<?= sysTranslations::get('chat_topic_tooltip') ?>" type="text" name="chatTopic" value="<?= empty($oActiveChat->topic) ? '' : $oActiveChat->topic ?>" /></td>
                        <td><input type="submit" id="startChat" value="<?= sysTranslations::get('chat_start_new_chat') ?>" name="startChat" /></td>
                    </tr>
                    <?php if (!$oChat->isPropValid("topic")) { ?>
                        <tr>
                            <td colspan="3"><span class="error"><?= sysTranslations::get('global_field_not_completed') ?></span></td>
                        </tr>
                    <?php } ?>
                    <?php if (!empty($oActiveChat)) { ?>
                        <tr>
                            <td class="withLabel" style="width:150px"><label for="closedMessage"><?= sysTranslations::get('chat_closed_message') ?> *</label> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('chat_closed_tooltip') ?>">&nbsp;</div></td>
                            <td colspan="2"><input style="width:240px" id="closedMessage" class="{validate:{required:true}}" title="<?= sysTranslations::get('chat_closed_tooltip') ?>" type="text" name="closedMessage" value="<?= empty($oActiveChat->closedMessage) ? '' : $oActiveChat->closedMessage ?>" /></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="3" class="withLabel"><label><b>Current topic:</b></label>  &nbsp;<i id="currentTopic"><?= empty($oActiveChat) ? '' : $oActiveChat->topic ?></i>                      
                            &nbsp;&nbsp;&nbsp;<label><b>Current status:</b></label>&nbsp;<i id="currentStatus"><?= empty($oActiveChat) ? '' : ($oActiveChat->opened ? '<span style="color:green">Opened</span>' : '<span style="color:red">Closed</span>') ?></i>
                            &nbsp;&nbsp;&nbsp;
                            <?php if ($oActiveChat->opened) { ?>
                                <input type="submit" id="closeChat" value="<?= sysTranslations::get('chat_close_chat') ?>" name="stopChat" />
                            <?php } else { ?>
                                <input type="submit" id="openChat" value="<?= sysTranslations::get('chat_open_chat') ?>" name="openChat" />
                            <?php } ?>

                        </td>
                    </tr>
                    <?php if (!empty($oActiveChat)) { ?>
                        <tr>
                            <td colspan="3"><div id="chatPannel"></div></td>
                        </tr>
                        <tr>
                            <td colspan="3"><input type="text" id="message" name="message" value="" style="width:345px" maxlength="144" <?= $oActiveChat->opened ? '' : 'disabled' ?>>
                                <input type="button" id="sendMessage" name="send" value="Send" <?= $oActiveChat->opened ? '' : 'disabled' ?> ></td>
                        </tr>
                    <?php } ?>
                </table>
            </fieldset>
        </form>        
    </div>
</div>
<?php
$bChatOpened = empty($oActiveChat) ? 'false' : ($oActiveChat->opened ? 'true' : 'false');
$sUrl = getCurrentUrl();
$sJavascript = <<<EOT
<script>
        
    $('#sendMessage').click(
        function (e) {
            $.ajax({
                type: 'POST',
                url: '$sUrl',
                data: 'action=ajax-sendMessage&message=' + encodeURIComponent($('#message').val()),
                success: function(data) {
                    $('#message').val('');
                    var dataObj = eval("(" + data + ")");                    
                }
            });     
                
            e.preventDefault();
        }
    );
        
    $('#message').keypress(
        function (e) {
            if (e.which == 13) {
                $.ajax({
                    type: 'POST',
                    url: '$sUrl',
                    data: 'action=ajax-sendMessage&message=' + encodeURIComponent($('#message').val()),
                    success: function(data) {
                        $('#message').val('');
                        var dataObj = eval("(" + data + ")");                    
                    }
                });     

                e.preventDefault();        
            }
        }
    );
     
    if ($bChatOpened) {
        setInterval(
            function () {
                $.ajax({
                    type: 'POST',
                    url: '$sUrl',
                    data: 'action=ajax-update',
                    success: function(data) {
                        var dataObj = eval("(" + data + ")");                      
                        $('#chatPannel').html(dataObj.lastMessages);
                        $('#chatPannel').scrollTop($("#chatPannel").get(0).scrollHeight);
                    }
                });          
            }

        , 200
        );
    }
        
    $('#closeChat').click(
        function (e) {
            $('#action').val('closeChat');
            $('#chatForm').submit();
            e.preventDefault();        
        }
    )
        
    $('#openChat').click(
        function (e) {
            $('#action').val('openChat');
            $('#chatForm').submit();
            e.preventDefault();        
        }
    )        

</script>
EOT;
$oPageLayout->addJavascriptBottom($sJavascript);
?>