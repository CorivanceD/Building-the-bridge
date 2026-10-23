<?php

/* Everything needed to send an email */

class MailManager {
    /* set a few */

    public static $sFrom = DEFAULT_EMAIL_FROM;
    public static $sReplyTo = DEFAULT_EMAIL_REPLY_TO;
    public static $sMimeBoundary = "boundary van de email ";

    /**
     * Send an email with a given body
     * @param String $sTo (receiver)
     * @param String $sSubject (subject)
     * @param String $sMail (HTML version of the email)
     * @param string $sFrom (sender, optional)
     * @param String $sReplyTo (reply to, optional)
     * @param String $sCc (carbon copy of the mail send to, optional)
     * @param String $sBcc (blind darbon copy of the mail send to, optional)
     * @param String $sMimeBoundary (unique string for boundary in email, optional)
     * @param String $sTextMail (specific text email, optional)
     * @param Array $aAttachments (array($sName => $sPath), optional)
     * @param bool $bLogErrors, optional
     * @param Array $aCustomerHeaders (array($sName => $sValue), optional)
     * @param bool $bAddHtmlTags, default true
     * @param Array $aCustomerHeaders (array($sName => $sValue), optional)
     * @return Boolean true,false
     */
    public static function sendMail($sTo, $sSubject, $sMail, $sFrom = null, $sReplyTo = null, $sCc = null, $sBcc = null, $sMimeBoundary = null, $sTextMail = null, $aAttachments = array(), $bLogErrors = true, $aCustomerHeaders = array(), $bAddHtmlTags = true, $aCssStyles = array()) {

        /* Make a text version or take the given one */
        $sTextVersion = $sTextMail ? $sTextMail : strip_tags($sMail);

        /* Use a predefined boundary or the given one */
        $sMimeBoundary = $sMimeBoundary ? $sMimeBoundary : self::$sMimeBoundary;
        $sMimeBoundary = mb_strcut($sMimeBoundary, 0, 25); // mime boundary must not be longer than 70 characters in total (including md5 string and hyphens)
        $sMimeBoundary2 = $sMimeBoundary;
        $sMimeBoundary = "----$sMimeBoundary----" . md5(time());
        $sMimeBoundary2 = "----$sMimeBoundary2----" . md5(time() * 2);

        /* Take the predefined sender email addres or the given one */
        $sFrom = $sFrom ? $sFrom : self::$sFrom;

        /* take the predefined reply to address or the given one */
        $sReplyTo = $sReplyTo ? $sReplyTo : self::$sReplyTo;

        /* BEGIN email headers */
        $sHeaders = "From:$sFrom" . PHP_EOL;
        $sHeaders .= "Reply-To:$sReplyTo" . PHP_EOL;
        $sHeaders .= "Cc:$sCc" . PHP_EOL;
        $sHeaders .= "Bcc:$sBcc" . PHP_EOL;
        # return address for bounces
        if(!empty($aCustomerHeaders)) {
            $sHeaders .= "Errors-To:<" . COMMUNICATION_BOUNCE_EMAIL . ">" . PHP_EOL;
        }
        # add custom headers
        foreach($aCustomerHeaders as $sHeaderTitle => $sHeaderValue) {
            $sHeaders .= "$sHeaderTitle:$sHeaderValue" . PHP_EOL;
        }
        $sHeaders .= "MIME-Version: 1.0" . PHP_EOL;
        $sHeaders .= "Content-Type: multipart/mixed; boundary=\"$sMimeBoundary\"" . PHP_EOL;
        /* END email headers */

        /* BEGIN tekstversion of email */
        $sMessage = "--$sMimeBoundary" . PHP_EOL;

        $sMessage .= "Content-Type: multipart/alternative; boundary=\"$sMimeBoundary2\"" . PHP_EOL . PHP_EOL;
        $sMessage .= "--$sMimeBoundary2" . PHP_EOL;

        $sMessage .= "Content-Type: text/plain; charset=UTF-8" . PHP_EOL;
        $sMessage .= "Content-Transfer-Encoding: 8bit" . PHP_EOL . PHP_EOL;

        $sMessage .= self::wrapLine($sTextVersion, 78) . PHP_EOL;

        /* END tekstversion of email */

        /* BEGIN file headers */
        $sMessage .= "--$sMimeBoundary2" . PHP_EOL;
        $sMessage .= "Content-Type: text/html; charset=UTF-8" . PHP_EOL;
        $sMessage .= "Content-Transfer-Encoding: 8bit" . PHP_EOL . PHP_EOL;
        /* END file headers */

        # add custom css
        $sCssStyle = '';
        if(!empty($aCssStyles)) {
            foreach($aCssStyles as $sCssStyle) {
                $sCssStyle .= $sCssStyle . PHP_EOL;
            }
        }
        
        /* BEGIN HTML email */
        if($bAddHtmlTags) {
        $sMessage .= "<html>" . PHP_EOL;
        $sMessage .= "<head>" . PHP_EOL;
        $sMessage .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>";
        $sMessage .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/>";
            # add customer CSS if defined
            $sMessage .= $sCssStyle;            
        $sMessage .= "</head>" . PHP_EOL;
        $sMessage .= "<body>" . PHP_EOL;
        }
        # wraplines
        $sMessage .= self::wrapLine($sMail, 78);
        if($bAddHtmlTags) {
        $sMessage .= "</body>" . PHP_EOL;
        $sMessage .= "</html>" . PHP_EOL;
        }
        /* END HTML email */

        $sMessage .= "--$sMimeBoundary2--" . PHP_EOL;

        /* if is no array, do make array */
        if (!is_array($aAttachments)) {
            $aAttachments = array();
        }

        /* BEGIN attachments */
        foreach ($aAttachments AS $sName => $sPath) {
            if (is_file($sPath)) {
                $sMessage .= "--$sMimeBoundary" . PHP_EOL;

                /* read file */
                $rFp = @fopen($sPath, "rb");
                $sData = @fread($rFp, filesize($sPath));
                @fclose($rFp);

                /* split in chunks for sending with email */
                $sData = chunk_split(base64_encode($sData));
                $sMessage .= "Content-Type: application/octet-stream; name=\"" . $sName . "\"" . PHP_EOL;
                $sMessage .= "Content-Description: " . $sName . PHP_EOL;
                $sMessage .= "Content-Disposition: attachment;" . PHP_EOL . " filename=\"" . $sName . "\"; size=" . filesize($sPath) . ";" . PHP_EOL;
                $sMessage .= "Content-Transfer-Encoding: base64" . PHP_EOL . PHP_EOL . $sData . PHP_EOL;
            }
        }
        /* END attachments */

        /* last mime boundary */
        $sMessage .= "--$sMimeBoundary--";

        $bResult = @mail($sTo, $sSubject, $sMessage, $sHeaders, "-f".COMMUNICATION_BOUNCE_EMAIL);

        /* send email and return result */
        if ($bResult) {
            return true;
        } else {
            if ($bLogErrors)
                Debug::logError("EMAIL", "An email could not be send", __FILE__, __LINE__, "To: $sTo" . PHP_EOL . "Subject: $sSubject" . PHP_EOL . "$sTextVersion", Debug::LOG_IN_DATABASE); //log in database
            return false;
        }
    }

    /**
     * Aims to wrap a string to specified length per line, doing so leniently
     * Does not cut words in half or break html tags
     * 
     * @param string $sInput string to wrap
     * @param int $iMaxLength max. line length
     * @param string $sBreak string to append to end of line
     * @return string
     */
    public static function wrapLine($sInput, $iMaxLength, $sBreak = PHP_EOL) {
        $sReturn = '';
        $openTag = false;
        $length = strlen($sInput);
        $count = 0;

        // loop trough all characters
        for ($i = 0; $i < $length; $i++) {
            $count++;
            $sReturn .= $sInput[$i];

            // opening sign of HTML tag
            if ($sInput[$i] == '<') {
                $openTag = true;
                continue;
            }

            // closing sign of HTML tag
            if ($openTag && $sInput[$i] == '>') {
                if ($count > $iMaxLength) {
                    $sReturn .= $sBreak;
                    $count = 0;
                }
                $openTag = false;
                continue;
            }

            // tag is not opened and character is a space, cut string
            if (!$openTag && $sInput[$i] == ' ') {
                if ($count == 0) {
                    $sReturn = substr($sReturn, 0, -1);
                    continue;
                } else if ($count > $iMaxLength) {
                    $sReturn .= $sBreak;
                    $count = 0;
                    continue;
                }
            }

            // tag is not opened and 
            if (!$openTag && $sInput[$i] != ' ' && $count >= 950) {
                $sReturn .= $sBreak;
                $count = 0;
                continue;
            }
        }

        return $sReturn;
    }

}

?>
