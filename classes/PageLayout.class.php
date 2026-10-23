<?php

/**
 * class voor page layout purposes (Admin and Normal website)
 */
class PageLayout {

    public $sWindowTitle; //window title of browser
    public $sPagePath; //path to page include
    public $sStatusUpdate; //status update
    public $sModuleName; //name of the module
    public $sMetaKeywords; //meta keywords related to the content
    public $sMetaDescription; //meta description related to the content
    public $sCanonical; //canonical (url path) tag for canonical pages (don't show this on the original page)
    public $sRelPrev; // for pagination to set previous url for google
    public $sRelNext; // for pagination to set next url for google
    public $sOGTitle; // og:title tag
    public $sOGType; // og:type tag
    public $sOGUrl; // og:url tag
    public $sOGDescription; // og:desciption tag
    public $sOGImage; // og:image tag
    public $sCrumblePath = null; //crumble path (can be set manually or by one of the 2 crumbepath functions)
    private $aJavascriptsTop = array(); //add some extra javascript lines in de head after existing javascript
    private $aJavascriptsBottom = array(); //add some extra javascript lines just before the body end tag
    private $aStylesheets = array(); //stylesheet lines with stylesheets

    /**
     * add some lines of javascript or include files
     * @param String $sJavascript (script with script tags or src)
     */

    public function addJavascriptTop($sJavascript) {
        $this->aJavascriptsTop[] = $sJavascript;
    }

    /**
     * add some lines of javascript or include files
     * @param String $sJavascript (script with script tags or src)
     */
    public function addJavascriptBottom($sJavascript) {
        $this->aJavascriptsBottom[] = $sJavascript;
    }

    /**
     * add some lines of CSSc or include css files
     * @param String $sStylesheet (styles with style tags or src)
     */
    public function addStylesheet($sStylesheet) {
        $this->aStylesheets[] = $sStylesheet;
    }

    /**
     * return the array with javascript lines
     * @return Array
     */
    public function getJavascriptsTop() {
        return $this->aJavascriptsTop;
    }

    /**
     * return the array with javascript lines
     * @return Array
     */
    public function getJavascriptsBottom() {
        return $this->aJavascriptsBottom;
    }

    /**
     * return the array with javascript lines
     * @return Array
     */
    public function getStylesheets() {
        return $this->aStylesheets;
    }

    /**
     * auto generate crumble path
     * @param bool $bAddHome wether or not to add the homepage
     * @param bool $bStartWithSep wether or not to start with a seperator (before the first crumble)
     */
    public function generateAutoCrumblePath($bAddHome = true, $sSep = '<span class="crumbleSep">&nbsp;</span>', $bStartWithSep = false) {
        if ($this->sCrumblePath === null) {
            $sCrumblePath = '';

            $sUrlPath = getCurrentUrlPath();

            # split parts from the url
            preg_match_all("#([^/]+)#", $sUrlPath, $aUrlParts);

            # unset unwanted values
            $aUrlUsableParts = array();
            $bIsAdmin = false;
            foreach ($aUrlParts[0] AS $sUrlPart) {
                if (strtolower($sUrlPart) == 'admin') {
                    $bIsAdmin = true; // if admin do some things different
                }

                if (strtolower($sUrlPart) != 'admin' && strtolower($sUrlPart) != 'home')
                    $aUrlUsableParts[] = $sUrlPart;
            }

            # set template
            $sTemplate = '<a class="crumble%s" href="%s">%s</a>';

            # add crumbles to path
            $sUrlPath = '';
            if ($bIsAdmin)
                $sUrlPath = '/admin';

            # add home
            if ($bAddHome) {
                $sCrumblePath .= ($bStartWithSep ? $sSep : '') . sprintf($sTemplate, '', ($sUrlPath == '' ? '/' : $sUrlPath), 'Home');
            }

            $iCountUrlUsableParts = count($aUrlUsableParts);
            foreach ($aUrlUsableParts AS $iIndex => $sUrlPart) {
                if ($iIndex == 0 && !$bAddHome) {
                    $sClass = ' first';
                } elseif (($iIndex + 1) == $iCountUrlUsableParts) {
                    $sClass = ' last';
                } else {
                    $sClass = '';
                }

                $sUrlPath .= '/' . $sUrlPart;
                $sCrumblePath .= ($iIndex == 0 && !$bAddHome && !$bStartWithSep ? '' : $sSep) . sprintf($sTemplate, $sClass, $sUrlPath, $sUrlPart);
            }
            $this->sCrumblePath = $sCrumblePath;
        }
        return $this->sCrumblePath;
    }

    /**
     * generate a custom crumble path based on given crumbles
     * @param array $aCrumbles
     * @param bool $bAddHome wether or not to add the homepage
     * @param bool $bStartWithSep wether or not to start with a seperator (before the first crumble, in case you set `home` crumble manually)
     * @return string
     */
    public function generateCustomCrumblePath(array $aCrumbles = array(), $bAddHome = true, $sSep = '<span class="crumbleSep"> // </span>', $bStartWithSep = false) {
        if ($this->sCrumblePath === null) {
            $sTemplate = '<span itemtype="http://data-vocabulary.org/Breadcrumb" itemscope><a class="crumble%s" itemprop="url" href="%s"><span itemprop="title">%s</span></a></span>';
            $sCrumblePath = '';

            if ($bAddHome) {
                $sCrumblePath .= ($bStartWithSep ? $sSep : '') . sprintf($sTemplate, ' first', '/', 'Home');
            }

            $iC = 1;
            $iCountCrumbles = count($aCrumbles);
            foreach ($aCrumbles AS $sName => $sLink) {
                if ($iC == 1 && !$bAddHome) {
                    $sClass = ' first';
                } elseif ($iC == $iCountCrumbles) {
                    $sClass = ' last';
                } else {
                    $sClass = '';
                }

                $sCrumblePath .= ($iC == 1 && !$bAddHome && !$bStartWithSep ? '' : $sSep) . sprintf($sTemplate, $sClass, $sLink, $sName);

                $iC++;
            }
            $this->sCrumblePath = $sCrumblePath;
        }
        return $this->sCrumblePath;
    }

}

?>