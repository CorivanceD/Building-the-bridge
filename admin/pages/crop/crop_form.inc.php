<div id="topOptions">
    <a class="backBtn" href="<?= $aCropSettings[$iCropCurrent]->sReferrer ?>"><?= sysTranslations::get('global_back_to')?> `<?= $aCropSettings[$iCropCurrent]->sReferrerTekst ?>`</a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving')?>)</span>
</div>
<h1><?= sysTranslations::get('crop_cut_make')?> <?= count($aCropSettings) > 1 ? '(' . count($aCropSettings) . ') <div class="hasTooltip tooltip" title="'.sysTranslations::get('crop_this_image').' '. count($aCropSettings) . ' '.sysTranslations::get('crop_this_image').'">&nbsp;</div>' : '' ?></h1>
<div style="margin-bottom: 10px;">
    <?php if ($bHasNeededCrops && count($aCropSettings) > 1) { ?>
        <form method="POST">
            <input type="hidden" name="action" value="setCrop" />
            <?= sysTranslations::get('crop_cut')?> <select onchange="$(this).closest('form').submit();" name="crop">
                <?php
                foreach ($aCropSettings AS $iKey => $oCropSettings) {
                    echo '<option ' . ($iCropCurrent == $iKey ? 'selected' : '') . ' value="' . $iKey . '">' . ($oCropSettings->sName ? $oCropSettings->sName : 'crop ' . $iKey) . '</option>';
                }
                ?>
            </select> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('crop_choose_cut_tooltip')?>">&nbsp;</div>
        </form>
    <?php } elseif (!$bHasNeededCrops && count($aCropSettings) > 1) { ?>
        <div><?= sysTranslations::get('crop_cuts_to_make')?>: <b>
                <?php
                foreach ($aCropSettings AS $iKey => $oCropSettings) {
                    echo $iKey > 0 && $iKey < count($aCropSettings) - 1 ? ', ' : '';
                    echo $iKey == count($aCropSettings) - 1 ? ' en ' : '';
                    echo '`' . ($oCropSettings->sName ? $oCropSettings->sName : 'crop ' . $iKey) . '`';
                }
                ?>
            </b>
        </div>
    <?php } ?>
</div>
<div id="cropboxPreviewPlaceholder" class="cf">
    <div id="cropBoxPlaceholder">
        <img src="<?= $oOriginalImageFile->link ?>" id="image2Crop" />
    </div>
    <div id="cropPreviewPlaceholder" class="<?= $aCropSettings[$iCropCurrent]->bShowPreview ? '' : 'hide' ?>">
        <h2>Preview</h2>
        <div id="cropPreview">
            <img id="preview" src="<?= $oOriginalImageFile->link ?>" />
        </div>
    </div>
</div>
<?php if (!$bHasNeededCrops) { ?>
    <div id="crops2Go"><?= $iCrops2Go > 1 ? sysTranslations::get('crop_cuts_to_go') .' '. $iCrops2Go . ' '.sysTranslations::get('crop_cuts_to_go_2') : '' ?></div>
<?php } ?>
<form onsubmit="return checkCoords();" action="" id="crop_form" method="POST">
    <input type="hidden" name="action" value="crop" />

    <input type="hidden" id="x" name="x" value="" />
    <input type="hidden" id="y" name="y" value="" />
    <input type="hidden" id="w" name="w" value="" />
    <input type="hidden" id="h" name="h" value="" />
    <input type="submit" name="apply" value="<?= sysTranslations::get('crop_cut_out') ?>" />
    <?php
    if ($iCropNext !== false) {
        ?>
        <input type="submit" name="saveAndNext" value="<?= sysTranslations::get('crop_cutting_cropping') ?> `<?= $aCropSettings[$iCropNext]->sName ? $aCropSettings[$iCropNext]->sName : sysTranslations::get('crop_cutting_cropping_2').' ' . $iCropNext ?>` <?= sysTranslations::get('crop_cutting_cropping_3') ?>" />
        <?php if ($bHasNeededCrops) { ?>
            <input type="submit" name="save" value="<?= sysTranslations::get('crop_and_back_to') ?> `<?= $aCropSettings[$iCropCurrent]->sReferrerTekst ?>`" />
        <?php } ?>
    <?php } else {
        ?>
        <input type="submit" name="save" value="<?= sysTranslations::get('crop_and_back_to') ?> `<?= $aCropSettings[$iCropCurrent]->sReferrerTekst ?>`" />
        <?php
    }
    ?>
</form>
<hr class="cropSeperator" />
<?php
# display all existing crops
$sImages = '';
foreach ($aCropSettings[$iCropCurrent]->getCrops() As $aCropInfo) {
    $oImageFile = $oImage->getImageFileByReference($aCropInfo[3]);
    if ($oImageFile && $aCropInfo[4]) {
        $sImages .= '<img style="max-width: 100%;" src="' . $oImageFile->link . '?cache=' . time() . '" />';
    }
}
if (!empty($sImages)) {
    echo '<div id="imageCrops">';
    echo '<h1>'.sysTranslations::get('crop_made').'</h1>';
    echo $sImages;
    echo '</div>';
}
?>
<div id="bottomOptions">
    <a class="backBtn" href="<?= $aCropSettings[$iCropCurrent]->sReferrer ?>"><?= sysTranslations::get('global_back_to') ?> `<?= $aCropSettings[$iCropCurrent]->sReferrerTekst ?>`</a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<?php
# add jCrop javascript initiation code
$sMakeSelectionMsg = sysTranslations::get('crop_make_selection');
$sJcropJavascript = <<<EOT
<script>
    // set sizes of original image and cropbox
    var imageW = $iImageW;
    var imageH = $iImageH;

    // set variables for use in functions
    var jCropHolderW;
    var jCropHolderH;
    var resizeRatio;
    var resizeCounterRatio;
    var maxCropPreviewWidth;

    // cropform initiation
    function initCropForm(){
        jCropHolderW = $('#image2Crop').width();
        jCropHolderH = $('#image2Crop').height();


        resizeRatio = jCropHolderW/imageW; // big to small calculation
        resizeCounterRatio = 1/resizeRatio; // small to big calculation

        var previewW = $('#cropboxPreviewPlaceholder').width()-jCropHolderW-$('#cropPreviewPlaceholder').css('padding-left').replace('px','');

        // check max preview width
        if($iMaxPreviewW !== null){
            if(previewW > $iMaxPreviewW){
                previewW = $iMaxPreviewW;
            }
        }

        var previewH = (jCropHolderH * previewW) / jCropHolderW;

        $('#cropPreview').width(previewW);
        $('#cropPreview').height(previewH);

        maxCropPreviewWidth = $('#cropPreview').width();

        // set preview placeholder position
        var cropBoxPreviewPlaceholderLeft = jCropHolderW - $('#cropBoxPreviewPlaceholder').width();
        $('#cropPreviewPlaceholder').css('left', cropBoxPreviewPlaceholderLeft);
        $('#cropPreviewPlaceholder').css('top', '-' + $('#cropPreviewPlaceholder h2').height() + 'px');

        // calculate real size of cropbox
        var iCx = $iCx * resizeRatio;
        var iCy = $iCy * resizeRatio;
        var iCx2 = $iCx2 * resizeRatio;
        var iCy2 = $iCy2 * resizeRatio;

        // set default values to form
        $('#x').val(Math.round($iCx));
        $('#y').val(Math.round($iCy));
        $('#w').val(Math.round($iCx2-$iCx));
        $('#h').val(Math.round($iCy2-$iCy));

        $sMinSizeCalc
        $sMaxSizeCalc

        $('#image2Crop').Jcrop({
            $sAspectRatio
            $sMinSize
            $sMaxSize
            onSelect: updateData,
            onChange : updateData,
            onRelease : resetData,
            bgColor: "white",
            bgOpacity : 0.3,
            setSelect: [iCx,iCy,iCx2,iCy2]
        });
        
        checkCoords();
    }
    
    // update form data
    function updateData(c)
    {
        $('#x').val(Math.round(c.x * resizeCounterRatio));
        $('#y').val(Math.round(c.y * resizeCounterRatio));
        $('#w').val(Math.round(c.w * resizeCounterRatio));
        $('#h').val(Math.round(c.h * resizeCounterRatio));

        showPreview(c);
    };

    // translate data to preview
    function showPreview(c){
        
        var ratio = c.w/c.h; // crop ratio
        var cropW = c.w;
        var cropH = c.h;

        // bigger than max preview, make max
        if(cropW > maxCropPreviewWidth){
            previewW = maxCropPreviewWidth;
        }else{
            // else set crop size
            previewW = cropW;
        }

        // set height from width and resize later if needed
        previewH = previewW * (1/ratio);

        // set width and height in case of changes
        $('#cropPreview').css('width', Math.round(previewW) + 'px');
        $('#cropPreview').css('height', Math.round(previewH) + 'px');

        var rx = previewW / c.w;
	var ry = previewH / c.h;

	$('#preview').css({
		width: Math.round(rx * jCropHolderW) + 'px',
		height: Math.round(ry * jCropHolderH) + 'px',
		marginLeft: '-' + Math.round(rx * c.x) + 'px',
		marginTop: '-' + Math.round(ry * c.y) + 'px'
	});
    }

    // reset data in form
    function resetData()
    {
        $('#x').val('');
        $('#y').val('');
        $('#w').val('');
        $('#h').val('');
    };
    
    /*
     * check if coords are set
     */
    function checkCoords()
    {
        if (parseInt($('#w').val())) return true;
        alert('$sMakeSelectionMsg');
        return false;
    };
    
    $(window).load(function(){
        initCropForm(); // start crop plugin
    });
    
</script>
EOT;
$oPageLayout->addJavascriptBottom($sJcropJavascript);
?>