<section style="margin-top: 32px;">
    <div class="column-100 float-left">   
        <article class="column-66 page-content float-left contactForm">    
            <h3><?= _e($oPage->title) ?></h3>

            <form  style="display:inline-block;" id="contentTypeForm" method="POST" action="<?= CLIENT_HTTP_URL ?>">
                <select id="contentType" name="contentType">
                    <option value="<?= CONTENT_HOPE ?>" <?= intval(http_session('contentType')) === CONTENT_HOPE ? 'selected' : '' ?>>Ballots Not Bullets</option>
                    <option value="<?= CONTENT_LATEST_LETTERS ?>" <?= intval(http_session('contentType')) === CONTENT_LATEST_LETTERS ? 'selected' : '' ?>>Just Sent</option>
                    <option value="<?= CONTENT_LUMINARIES ?>" <?= intval(http_session('contentType')) === CONTENT_LUMINARIES ? 'selected' : '' ?>>Voices of Wisdom</option>
                    <option value="<?= CONTENT_WORLD_LEADERS ?>" <?= intval(http_session('contentType')) === CONTENT_WORLD_LEADERS ? 'selected' : '' ?>>Letter to World Leaders</option>                    
                    <option value="<?= CONTENT_SEND_LETTER ?>" <?= intval(http_session('contentType')) === CONTENT_SEND_LETTER ? 'selected' : '' ?>>Send Yours</option>
                </select>
            </form>

            <hr>            
            <?= $oPage->content ?> 
            <br>
            <br>
            <form method="post" class="validateForm" enctype="multipart/form-data"> 
                <?php include TEMPLATES_FOLDER . '/elements/errorBox.inc.php'; ?>
                <input type="hidden" name="action" value="sendForm"> 
                <div class="column-100 float-left withForm contactForm"> 
                    <div class="column-100 float-left"><label for="name"><b>Address your open letter to *</b></label></div>
                    <div class="column-100 float-left">
                        <select id="regionAddressee" name="regionId">
                            <option value="">--- Select a region ---</option>
                            <?php
                            $aRegions = CountryManager::getRegions();

                            foreach ($aRegions as $oRegion) {
                                ?>
                                <option value="<?= $oRegion->regionId ?>" ><?= $oRegion->name ?></option>    
                                <?php
                            }
                            ?>
                        </select>  

                        <select id="countryAddressee" <?= (http_post('regionId') == Region::REGION_ID_UN_NY || http_post('regionId') == Region::REGION_ID_UN_GE) ? 'style="display:hidden"' : '' ?>>
                            <option value="">--- Select a country ---</option>
                            <?php
                            if (http_post('regionId', false)) {
                                foreach (CountryManager::getCountriesByRegionId(http_post('regionId')) as $oCountry) {
                                    ?>
                                    <option value="<?= $oCountry->countryId ?>" ><?= $oCountry->name ?></option>    

                                    <?php
                                }
                            }
                            ?>
                        </select>  

                        <select id="worldLeaderId" name="worldLeaderId" data-rule-required="true" data-msg-required="You must choose an addressee">
                            <option value="">--- Choose an addressee ---</option>
                            <?php
                            $iCountryId = false;
                            if (http_post('regionId') == Region::REGION_ID_UN_NY) {
                                $iCountryId = Country::UN_NY_ID;
                            } elseif (http_post('regionId') == Region::REGION_ID_UN_GE) {
                                $iCountryId = Country::UN_GE_ID;
                            }

                            if (http_post('countryId', $iCountryId)) {
                                foreach (WorldLeaderManager::getWorldLeadersByFilter(array('countryId' => http_post('countryId'))) AS $oWorldLeader) {
                                    ?>
                                    <option value="<?= $oWorldLeader->worldLeaderId ?>" <?= ($oWorldLeader->worldLeaderId == http_post('worldLeaderId') ? 'selected' : '') ?>><?= $oWorldLeader->getCompleteName() ?></option>    
                                    <?php
                                }
                            }
                            ?>
                        </select>
                        <select id="worldLeaderAddressId" name="worldLeaderAddressId" data-rule-required="true" data-msg-required="You must choose an address">
                            <option value="">--- Choose an address ---</option>
                            <?php
                            if (http_post('worldLeaderId', false)) {
                                foreach (WorldLeaderManager::getWorldLeaderById(http_post('worldLeaderId')) AS $oWorldLeaderAddress) {
                                    ?>
                                    <option value="<?= $oWorldLeaderAddress->worldLeaderAddressId ?>" <?= ($oWorldLeaderAddress->worldLeaderAddressId == http_post('worldLeaderAddressId') ? 'selected' : '') ?>><?= $oWorldLeaderAddress->name ?></option>    
                                    <?php
                                }
                            }
                            ?>
                        </select>     
                        <div id="address" class="column-100 float-left" style="margin-top: 10px;">
                            <?php
                            if (http_post('worldLeaderAddressId', false)) {
                                $oWorldLeaderAddress = WorldLeaderAddressManager::getWorldLeaderAddressById(http_post('worldLeaderAddress'));

                                if (!empty($oWorldLeaderAddress)) {
                                    ?>
                                    <?= $oWorldLeaderAddress->address ?><br>
                                    <?= $oWorldLeaderAddress->postalCode ?>&nbsp;
                                    <?= $oWorldLeaderAddress->city ?><br>
                                    <?= $oWorldLeaderAddress->getAddressCountry()->name ?>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <div class="column-100 float-left">&nbsp;</div> 
                    <div class="column-100 float-left"><label for="name"><b>Name (not required)</b></label></div> 
                    <div class="column-100 float-left"><input id="name" name="name" type="text" value="<?= _e(http_post('name')) ?>"/></div> 
                    <div class="column-100 float-left">&nbsp;</div> 
                    <div class="column-100 float-left"><label for="companyName"><b>Name of organization (not required)</b></label></div> 
                    <div class="column-100 float-left"><input id="companyName" name="companyName" type="text" value="<?= _e(http_post('companyName')) ?>"/></div> 
                    <div class="column-100 float-left">&nbsp;</div>                     
                    <div class="column-100 float-left"><label for="alias"><b>Alias, your pseudonym *</b></label></div> 
                    <div class="column-100 float-left"><input id="alias" name="alias" type="text" value="<?= _e(http_post('alias')) ?>" data-rule-required="true" data-msg-required="You must enter an alias"/></div> 
                    <div class="column-100 float-left">&nbsp;</div> 
                    <div class="column-100 float-left"><label for="email"><b>E-mail (not required)</b></label></div> 
                    <div class="column-100 float-left"><input id="email" name="email" type="text" value="<?= _e(http_post('email')) ?>" data-rule-email="true" data-msg-email="You've entered an invalid email address"/></div> 
                    <div class="column-100 float-left">&nbsp;</div> 
                    <div class="column-100 float-left"><label for="countryId"><b>Country *</b></label></div> 
                    <div class="column-100 float-left">
                        <select data-rule-required="true" data-msg-required="You must select a country" id="signup-country" name="countryId">
                            <option value="">--- Select a country ---</option>
                            <?php
                            $aCountries = CountryManager::getCountries();
                            foreach ($aCountries as $oCountry) {
                                if (intval($oCountry->countryId) != Country::UNKNOWN_COUNTRY_ID) {
                                    ?>
                                    <option value="<?= $oCountry->countryId ?>" <?= ($oCountry->countryId == http_post('countryId')) ? 'selected' : '' ?> ><?= $oCountry->name ?></option>    
                                    <?php
                                }
                            }
                            ?>
                        </select></div> 
                    <div class="column-100 float-left">&nbsp;</div>
                    <div class="column-100 float-left"><label for="title"><b>The title of your letter *</b></label></div> 
                    <div class="column-100 float-left"><input id="title" name="title" type="text" value="<?= _e(http_post('title')) ?>" data-rule-required="true" data-msg-required="You must enter a title"/></div> 
                    <div class="column-100 float-left">&nbsp;</div>                     
                    <div class="column-100 float-left"><label for="intro"><b>Short intro text for your letter *</b></label></div> 
                    <div class="column-100 float-left"><textarea rows="6" id="intro" name="intro" placeholder="I’m writing this letter because…" data-rule-required="true" data-msg-required="You must enter a short intro for the letter"><?= _e(http_post('intro')) ?></textarea></div>
                    <div class="column-100 float-left">&nbsp;</div> 
                    <div class="column-100 float-left"><label for="text"><b>Your letter *</b></label></div> 
                    <div class="column-100 float-left"><textarea rows="12" id="text" name="text" data-rule-required="true" data-msg-required="You must enter the text of the letter"><?= _e(http_post('text')) ?></textarea></div> 
                    <div class="column-100 float-left">&nbsp</div>
                    <div class="column-100 float-left"><label for="images"><b>Upload associated  images  for your letter </b></label><br>
                        <input name="images[]" type="file" multiple accept=".jpeg,.jpg,.gif,.png"/>
                    </div>
                    <div class="column-100 float-left">&nbsp;</div> 
                    <div class="column-100 float-left"><label for="files"><b>Upload additional documents</b></label><br>
                        <input name="files[]" type="file" multiple accept=".txt,.xls,.xlsx,.rtf,.doc,.docx,.odt,.ods,.pdf"/>
                    </div>
                    <div class="column-100 float-left">&nbsp;</div>
                    <div class="column-100 float-left"><b>Links</b><br>
                        <input type="hidden" id="links" name="links" value="<?= _e(http_post('links')) ?>">
                        <input id="linkName" type="text" placeholder="Link name" value="">
                        <input id="linkUrl" type="text" placeholder="Link url" value="">
                        <input id="addLink" type="button" value="Add Link">
                    </div>
                    <div class="column-100 float-left">&nbsp;</div>
                    <div class="column-100 float-left">
                        <ul id="linkList">
                            <?php
                            $sLinks = _e(http_post('links'));
                            $aLinks = explode('^', $sLinks);

                            foreach ($aLinks AS $sLinkInfo) {
                                $aLinkInfo = explode('|', $sLinkInfo);

                                if (isset($aLinkInfo[1])) {
                                    echo '<li><a href="' . $aLinkInfo[1] . '" target="_blank">' . $aLinkInfo[0] . '</a><a href="#" class="deleteButton link"></a></li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="column-100 float-left">&nbsp;</div>   
                    <div class="column-100 float-left"><b>Youtube videos</b><br>
                        <input type="hidden" id="videos" name="videos" value="<?= _e(http_post('videos')) ?>">
                        <input id="videoName" type="text" placeholder="Video name" value="">
                        <input id="videoUrl" type="text" placeholder="Video url" value="">
                        <input id="addVideo" type="button" value="Add Video">
                    </div>
                    <div class="column-100 float-left">&nbsp;</div>
                    <div class="column-100 float-left">
                        <ul id="videoList">
                            <?php
                            $sVideos = _e(http_post('videos'));
                            $aVideos = explode('^', $sVideos);

                            foreach ($aVideos AS $sVideoInfo) {
                                $aVideoInfo = explode('|', $sVideoInfo);

                                if (isset($aVideoInfo[1])) {
                                    echo '<li><a href="' . $aVideoInfo[1] . '" target="_blank">' . $aVideoInfo[0] . '</a><a href="#" class="deleteButton video"></a></li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="column-100 float-left">&nbsp;</div>                     
                    <div class="column-100 float-left" style="margin-bottom:20px"> 
                        <input type="hidden" name="action" value="sendForm" /> 
                        <input type="submit" name="done" value="Send" /> 
                        <label style="display:none" for="rumpelstiltskin-empty">Not change this field (SPAM check)</label> 
                        <input style="display:none" autocomplete="off" type="text" name="rumpelstiltskin-empty" value="" /> 
                        <label style="display:none" for="rumpelstiltskin-filled">Not change this field (SPAM check)</label> 
                        <input style="display:none" autocomplete="off" type="text" name="rumpelstiltskin-filled" value="repelsteeltje" /> 
                    </div>                     
                </div> 
            </form>          
        </article>        
        <?php include TEMPLATES_FOLDER . '/elements/rightColumn.inc.php'; ?>       
    </div>
</section> 
<?php
$iRegionUnNyId = Region::REGION_ID_UN_NY;
$iCountryUnNyId = Country::UN_NY_ID;
$iRegionUnGeId = Region::REGION_ID_UN_GE;
$iCountryUnGeId = Country::UN_GE_ID;
$sJavascript = <<<EOT
<script>        
function rebuildLinkList() {
    var links = '';
        
    $('ul#linkList li').each(function () {
        links += '^' + $(this).children('a').text() + '|' + $(this).children('a').prop('href');
    });
        
    links = links.substr(1);
        
    $('#links').val(links);
}
        
$('#addLink').click (
    function (e) {
        var linkName = $('#linkName').val().trim();
        var linkUrl = $('#linkUrl').val().trim();
        
        if (linkName.length == 0) {
            $('#linkName').addClass('error');        
        }
        
        if (linkUrl.length == 0) {
            $('#linkUrl').addClass('error');        
        }  
        
        if (!isValidUrl(linkUrl)) {
            $('#linkUrl').addClass('error'); 
        }
        
        if (linkName.length > 0 && isValidUrl(linkUrl)) {
            $('#linkList').append('<li><a href="' + linkUrl + '" target="_blank">' + linkName + '</a><a href="#" class="deleteButton link"></a></li>');
            $('#linkName').val('');
            $('#linkUrl').val('');
            $('a.link').click(
                function (e) {
                    $(this).parent().remove();
                    rebuildLinkList();        
                    e.preventDefault();
                }
            );
            rebuildLinkList();
        }
        
        e.preventDefault();
    }
);
        
        
function rebuildVideoList() {
    var videos = '';
        
    $('ul#videoList li').each(function () {
        videos += '^' + $(this).children('a').text() + '|' + $(this).children('a').prop('href');
    });
        
    videos = videos.substr(1);
        
    $('#videos').val(videos);
}
        
$('#addVideo').click (
    function (e) {
        var videoName = $('#videoName').val().trim();
        var videoUrl = $('#videoUrl').val().trim();
        
        if (videoName.length == 0) {
            $('#videoName').addClass('error');        
        }
        
        if (videoUrl.length == 0) {
            $('#videoUrl').addClass('error');        
        }  
        
        if (!isYoutubeVideo(videoUrl)) {
            $('#videoUrl').addClass('error'); 
            e.preventDefault();
            return;
        }
        
        if (videoName.length > 0 && isYoutubeVideo(videoUrl)) {
            $('#videoList').append('<li><a href="' + videoUrl + '" target="_blank">' + videoName + '</a><a href="#" class="deleteButton video"></a></li>');
            $('#videoName').val('');
            $('#videoUrl').val('');
            $('a.video').click(
                function (e) {
                    $(this).parent().remove();
                    rebuildVideoList();
                    e.preventDefault();
                }
            );  
                
            rebuildVideoList();        
        }
        
        e.preventDefault();
    }
);
        
                       
$('#regionAddressee').change( function (e) {
    var regionId = $(this).val();
    $('#address').html('');
    $('#countryAddressee').html('<option value="">--- Choose a country ---</option>');
    $('#worldLeaderId').html('<option value="">--- Choose an addressee ---</option>');
    $('#worldLeaderAddressId').html('<option value="">--- Choose an address ---</option>');

    if (regionId == $iRegionUnNyId) {
        $('#countryAddressee').hide();
        $.ajax({
            type: 'POST',
            data: 'ajax=1&action=filterAddressees&countryId=' + $iCountryUnNyId,
            success: function (data) {
                var dataObj = eval ( '(' + data + ')');
                var options = '<option value="">--- Choose an addressee ---</option>';

                $.each(dataObj.addressees, function (index, addr) {
                    options += '<option value="' + addr.worldLeaderId + '">' + addr.name + '</option>';
                });

                $('#worldLeaderId').html(options);
            }
        });        
    }
    else if (regionId == $iRegionUnGeId) {
        $('#countryAddressee').hide();        
        $.ajax({
            type: 'POST',
            data: 'ajax=1&action=filterAddressees&countryId=' + $iCountryUnGeId,
            success: function (data) {
                var dataObj = eval ( '(' + data + ')');
                var options = '<option value="">--- Choose an addressee ---</option>';

                $.each(dataObj.addressees, function (index, addr) {
                    options += '<option value="' + addr.worldLeaderId + '">' + addr.name + '</option>';
                });

                $('#worldLeaderId').html(options);
            }
        });        
    }    
    else {
        $('#countryAddressee').show();
        $.ajax({
            type: 'POST',
            data: 'ajax=1&action=filterAddressees&regionId=' + regionId,
            success: function (data) {
                var dataObj = eval ( '(' + data + ')');
                var options = '<option value="">--- Choose a country ---</option>';

                $.each(dataObj.countries, function (index, country) {
                    options += '<option value="' + country.countryId + '">' + country.name + '</option>';
                });

                $('#countryAddressee').html(options);
            }
        });
    }
});
               
$('#countryAddressee').change( function (e) {
    var countryId = $(this).val();
    $('#address').html('');
    $('#worldLeaderId').html('<option value="">--- Choose an addressee ---</option>');
    $('#worldLeaderAddressId').html('<option value="">--- Choose an address ---</option>');

    $.ajax({
        type: 'POST',
        data: 'ajax=1&action=filterAddressees&countryId=' + countryId,
        success: function (data) {
            var dataObj = eval ( '(' + data + ')');
            var options = '<option value="">--- Choose an addressee ---</option>';

            $.each(dataObj.addressees, function (index, addr) {
                options += '<option value="' + addr.worldLeaderId + '">' + addr.name + '</option>';
            });

            $('#worldLeaderId').html(options);
        }
    });
});
        
$('#worldLeaderId').change( function (e) {
    var worldLeaderId = $(this).val();
    $('#address').html('');
    $('#worldLeaderAddressId').html('<option value="">--- Choose an address ---</option>');

    $.ajax({
        type: 'POST',
        data: 'ajax=1&action=getAddresses&worldLeaderId=' + worldLeaderId,
        success: function (data) {
            var dataObj = eval ( '(' + data + ')');
            var options = '<option value="">--- Choose an address ---</option>';

            $.each(dataObj.addresses, function (index, addr) {
                options += '<option value="' + addr.worldLeaderAddressId + '">' + addr.name + '</option>';
            });

            $('#worldLeaderAddressId').html(options);
        }
    });
});   
    
$('#worldLeaderAddressId').change( function (e) {
    var worldLeaderAddressId = $(this).val();
    $('#address').html('');

    $.ajax({
        type: 'POST',
        data: 'ajax=1&action=getAddress&worldLeaderAddressId=' + worldLeaderAddressId,
        success: function (data) {
            var dataObj = eval ( '(' + data + ')');
            if ( dataObj.address) {
                $('#address').html(
                   dataObj.address + '<br>'
                 + dataObj.postalCode + ' ' + dataObj.city + '<br>'
                 + dataObj.country
                );
            }
        }
    });
});  
    
$('#contentType').change(
    function (e) {
        $('#contentTypeForm').submit();
    }
);        
               
</script>
EOT;
$oPageLayout->addJavascriptBottom($sJavascript);
?>