<div class="column-width float-left">&nbsp;</div>
<aside class="column-25 float-left page-sidebar">
    <? if (!empty($aYoutubeVideos)) { ?>
        <div class="youtube-videos images">
            <? foreach ($aYoutubeVideos AS $oYoutubeVideo) { ?>
                <? if ($oYoutubeVideo->online) { ?>
                    <div class="youtube-video relative">
                        <a href="<?= $oYoutubeVideo->getEmbedLink() ?>" class="fancyBoxLink fancybox.iframe" title="<?= $oYoutubeVideo->title ?>">
                            <img src="/images/layout/play-video.png" class="play-video" />
                            <img src="<?= $oYoutubeVideo->getThumbLink(0) ?>" />
                        </a>
                    </div>
                <? } ?>
            <? } ?>
        </div>
    <? } ?>
    <? if (!empty($aImages)) { ?>
        <div class="page-images images">
            <? foreach ($aImages AS $oImage) { ?>
                <? if ($oImage->online && ($oCropSmall = $oImage->getImageFileByReference('crop_small')) && ($oDetail = $oImage->getImageFileByReference('crop_small'))) { ?>
                    <a href="<?= $oImage->getImageFileByReference('detail')->link ?>" class="fancyBoxLink" data-fancybox-group="page" title="<?= $oCropSmall->title ?>">
                        <img src="<?= $oCropSmall->link ?>" alt="<?= $oCropSmall->title ?>" />
                    </a>
                <? } ?>
            <? } ?>
        </div>
    <? } ?>
    <? if (!empty($aFiles)) { ?>
        <div class="page-files">
            <h4>Documenten</h4>
            <ul>
                <? foreach ($aFiles AS $oFile) { ?>
                    <? if ($oFile->online) { ?>
                        <li><a href="<?= $oFile->link ?>" target="_blank"><?= $oFile->title ? $oFile->title : $oFile->name ?></a></li>
                    <? } ?>
                <? } ?>
            </ul>
        </div>
    <? } ?>
    <? if (!empty($aLinks)) { ?>
        <div class="page-links">
            <h4>Bekijk ook eens</h4>
            <ul>
                <? foreach ($aLinks AS $oLink) { ?>
                    <? if ($oLink->online) { ?>
                        <li><a href="<?= $oLink->link ?>" target="<?= getLinkTarget($oLink->link) ?>"><?= $oLink->title ? $oLink->title : $oLink->link ?></a></li>
                    <? } ?>
                <? } ?>
            </ul>
        </div>
    <? } ?>
</aside>