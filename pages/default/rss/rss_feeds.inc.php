<section style="margin-top: 32px;">
    <!-- MAIN CONTENT -->
    <article >
        <div class="column-66 float-left rssFeeds">
            <h2>RSS FEEDS</h2>
            <hr>
            <?php if (!empty($aPages)) { ?>
                <div class="linksAndFiles">
                    <ul>
                        <?php foreach ($aPages AS $oPage) { ?>

                            <li><a class="articleLink" href="/rss/<?= $oPage->pageId ?>" target="_blank"><?= (empty($oPage->parentPageId) ? '' : $oPage->getParent()->title . ' > ') . $oPage->title ?></a></li>

                        <?php } ?>
                    </ul>                        
                </div>
            <?php } ?>
        </div>  
        <?php include TEMPLATES_FOLDER . '/elements/rightColumn.inc.php'; ?>
    </article>

</section>