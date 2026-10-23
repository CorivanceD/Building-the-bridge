<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8" />
        <title><?= $oPageLayout->sWindowTitle ?></title>
        <link rel="stylesheet" href="/css/default/browserwarning_style.css" />
        <link rel="shortcut icon" href="/favicon.ico" />
    </head>
    <body>
        <div id="container">
            <div id="leftColumn">
                <img id="warning" src="/images/layout/browserwarning/icon-warning.gif" alt="Warning" title="Warning" />
                <p>
                    You are trying to access this site. Unfortunately you are using an outdated browser.
                </p>
                <p>
                    We recommend that you update this browser, so you can safely open all websites. Make a choice right here to download the update to your preferred browser.
                </p>
            </div>
            <div id="middleColumn">
                <div class="browserLogoContainer firstRow firstCol">
                    <a href="http://www.microsoft.com/windows/Internet-explorer/default.aspx" onClick="target='_blank'" titel="Download Internet Explorer">
                        <img class="browserLogo" src="/images/layout/browserwarning/icon-internet-explorer.gif" alt="Internet Explorer" /><br />
                        Internet Explorer
                    </a>
                </div>
                <div class="browserLogoContainer firstRow">
                    <a href="http://www.mozilla.com/firefox/" onClick="target='_blank'" titel="Download Mozilla Firefox">
                        <img class="browserLogo" src="/images/layout/browserwarning/icon-mozilla-firefox.gif" alt="Mozilla Firefox" /><br />
                        Mozilla Firefox
                    </a>
                </div>
                <div class="browserLogoContainer firstCol">
                    <a href="http://www.google.com/chrome" onClick="target='_blank'" titel="Download Google Chrome">
                        <img class="browserLogo" src="/images/layout/browserwarning/icon-google-chrome.gif" alt="Google Chrome" /><br />
                        Google Chrome
                    </a>
                </div>
                <div class="browserLogoContainer">
                    <a href="http://www.apple.com/safari/" onClick="target='_blank'" titel="Download Safari">
                        <img src="/images/layout/browserwarning/icon-safari.gif" alt="Safari" /><br />
                        Safari
                    </a>
                </div>
            </div>
            <div id="rightColumn">
                <a href="?action=dontShowBrowserWarning">
                    Visit site with your old browser.<br />
                    <img src="/images/layout/browserwarning/icon-continue.jpg" alt="Continue with old browser" title="Continue with old browser" />
                </a>
            </div>
        </div>
        <script>
            var windowHeight = document.body.clientHeight;
            var containerHeight = 240;
            
            var containerMargin = (windowHeight-containerHeight)/2;
            
            /* set container to vertical middle */
            document.getElementById('container').style.marginTop = containerMargin;
        </script>
    </body>
</html>