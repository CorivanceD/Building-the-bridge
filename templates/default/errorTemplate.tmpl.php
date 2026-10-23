<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8" />
        <title><?= $oPageLayout->sWindowTitle ?></title>
        <link rel="stylesheet" href="<?= CSS_FOLDER ?>/error_style.css" />
        <link rel="shortcut icon" href="/favicon.ico" />
    </head>
    <body>
        <div id="container">
            <div id="containerLeft">
                <img src="/images/layout/client-logo.png" alt="<?= CLIENT_NAME ?> - HTTP-statuscode: <?= $iErrorNr ?>" width="200" height="44" />
                <h1>HTTP-statuscode: <span><?= $iErrorNr ?></span></h1>
                <h2>&lsquo;<?= $iErrorMessage ?>&rsquo;</h2>
            </div>
            <div id="containerRight">
                <h3><?= $iErrorDescription ?></h3>
                <h4>Go back to our <a href="/" title="Go to the homepage <?= CLIENT_NAME ?>">homepage</a></h4>
            </div>
        </div>
    </body>
</html>