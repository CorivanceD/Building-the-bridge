<?php

/* Models and managers used by the CropSettings model */

class Date {

    const FORMAT_DB = "yyyy-mm-dd hh:mm:ss";
    const FORMAT_NL = "dd-mm-yyyy hh:mm:ss";
    const FORMAT_DB_F = "%Y-%m-%d %H:%M:%S";
    const DAY = 86400;
    const HOUR = 3600;
    const MINUTE = 60;

    /*
     * number of seconds between the Unix Epoch (January 1 1970 00:00:00 GMT) and the time specified.
     */

    public $iTime;

    /*
     * day [0-9]+
     */
    public $iDay;

    /*
     * month [0-9]+
     */
    public $iMonth;

    /*
     * year [0-9]+
     */
    public $iYear;

    /*
     * hour [0-9]+
     */
    public $iHour;

    /*
     * minute [0-9]+
     */
    public $iMinute;

    /*
     * second [0-9]+
     */
    public $iSecond;

    function __construct($mDate = null, $sFormat = null) {
        /* create Date object from dat/time string */
        if ($mDate) {
            if (is_numeric($mDate)) {
                $this->dateFromTime($mDate); //timestamp meegegeven
            } elseif ($sFormat) {
                $this->dateFromFormat($mDate, $sFormat); //format meegegeven dus doe op basis van die
            } else {
                $this->dateFromString($mDate); //Geen idee wat de format is dus probeer wat
            }
        } else {
            /* neem de huidige datum en tijd */
            $this->iTime = time();
            $this->update();
        }
    }

    /**
     * create a Date object based on time
     * @param int $iTime
     */
    private function dateFromTime($iTime) {
        $this->iTime = $iTime;
        $this->update();
    }

    /**
     * create a Date object based on a given format
     * @param string $sDate
     * @param string $sFormat
     */
    private function dateFromFormat($sDate, $sFormat) {
        switch ($sFormat) {
            case self::FORMAT_NL:
                list($sDate, $sTime) = explode(" ", $sDate);
                list($iDay, $iMonth, $iYear) = explode("-", $sDate);
                list($iHour, $iMinute, $iSecond) = explode(":", $sTime);
                break;
            case self::FORMAT_DB:
                list($sDate, $sTime) = explode(" ", $sDate);
                list($iYear, $iMonth, $iDay) = explode("-", $sDate);
                list($iHour, $iMinute, $iSecond) = explode(":", $sTime);
                break;
        }
        $this->set($iDay, $iMonth, $iYear, $iHour, $iMinute, $iSecond);
    }

    /**
     * create a Date object from a string
     * @param string $sDate
     */
    private function dateFromString($sDate) {
        /* if dutch format : dd-mm-jjjj make international yy-mm-dd */
        if (preg_match("#^\d{1,2}[-/]?\d{1,2}[-/]?\d{4}#", $sDate)) {
            $sDate = preg_replace("#^(\d{1,2})[-/]?(\d{1,2})[-/]?(\d{4})#", "$3-$2-$1", $sDate);
        }

        $this->iTime = strtotime($sDate);
        $this->update();
    }

    /**
     * check if this date is greater than given Date
     * @param Date $oD
     * @return boolean
     */
    public function greaterThan($oD) {
        return $this->iTime > $oD->iTime;
    }

    /**
     * Date is smaller than given
     * @param Date $oD
     * @return boolean
     */
    public function lowerThan($oD) {
        return $this->iTime < $oD->iTime;
    }

    /**
     * Date is lower than of equal to given
     * @param Date $oD
     * @return boolean
     */
    public function lowerEqualTo($oD) {
        return $this->iTime <= $oD->iTime;
    }

    /**
     * Date equals given
     * @param Date $oD
     * @return boolean
     */
    public function equalTo($oD) {
        return $this->iTime == $oD->iTime;
    }

    /**
     * add x days
     * @param int $iD
     * @return Date
     */
    public function addDays($iD) {
        $this->set($this->iDay + $iD, $this->iMonth, $this->iYear, $this->iHour, $this->iMinute, $this->iSecond);
        return $this;
    }

    /**
     * add x months
     * @param int $iM
     * @return Date
     */
    public function addMonths($iM) {
        $this->set($this->iDay, $this->iMonth + $iM, $this->iYear, $this->iHour, $this->iMinute, $this->iSecond);
        return $this;
    }

    /**
     * add x years
     * @param int $iY
     * @return Date
     */
    public function addYears($iY) {
        $this->set($this->iDay, $this->iMonth, $this->iYear + $iY, $this->iHour, $this->iMinute, $this->iSecond);
        return $this;
    }

    /**
     * add x seconds
     * @param int $iS
     * @return Date
     */
    public function addSeconds($iS) {
        $this->set($this->iDay, $this->iMonth, $this->iYear, $this->iHour, $this->iMinute, $this->iSecond + $iS);
        return $this;
    }

    /**
     * add x minutes
     * @param int $iM
     * @return Date
     */
    public function addMinutes($iM) {
        $this->set($this->iDay, $this->iMonth, $this->iYear, $this->iHour, $this->iMinute + $iM, $this->iSecond);
        return $this;
    }

    /**
     * add x hours
     * @param int $iH
     * @return Date
     */
    public function addHours($iH) {
        $this->set($this->iDay, $this->iMonth, $this->iYear, $this->iHour + $iH, $this->iMinute, $this->iSecond);
        return $this;
    }

    /**
     * return days difference compared to given
     * @param Date $oDate
     * @return int
     */
    public function daysDiff($oDate) {
        return ($oDate->iTime - $this->iTime) / self::DAY;
    }

    /**
     * return years difference compared to given
     * @param Date $oDate
     * @return int
     */
    public function yearsDiff($oDate) {
        $fDaysDiff = $this->daysDiff($oDate);
        // fix leapyear
        if ($fDaysDiff >= 365 && $fDaysDiff < 365.25) {
            $fDaysDiff = 365.25;
        }
        return $fDaysDiff / 365.25; // dividing by 365.25 takes care of the leap-years
    }

    /**
     * return hours difference compared to given
     * @param Date $oDate
     * @return int
     */
    public function hoursDiff($oDate) {
        return ($oDate->iTime - $this->iTime) / self::HOUR;
    }

    /**
     * return minutes difference compared to given
     * @param Date $oDate
     * @return int
     */
    public function minutesDiff($oDate) {
        return ($oDate->iTime - $this->iTime) / self::MINUTE;
    }

    /**
     * set Date to first day of the month for this Date
     * @return Date
     */
    public function setFirstDayOfThisMonth() {
        $this->setDay(1);
        return $this;
    }

    /**
     * set Date to last day of this month
     * @return Date
     */
    public function setLastDayOfThisMonth() {
        $this->setDay(date('t', $this->iTime));
        return $this;
    }

    /**
     * set Date to the given day of the week for this Date
     * @param int $iDayOfWeek
     * @return Date
     */
    public function setDayOfWeek($iDayOfWeek) {
        $this->addDays($iDayOfWeek - $this->iDayOfWeek);
        return $this;
    }

    /**
     * set Date to first day of the year
     * @return Date
     */
    public function setFirstDayOfYear() {
        $this->set(1, 1, $this->iYear, $this->iHour, $this->iMinute, $this->iSecond);
        return $this;
    }

    /**
     * set Date to first day of the year
     * @return Date
     */
    public function setLastDayOfYear() {
        $this->set(31, 12, $this->iYear, $this->iHour, $this->iMinute, $this->iSecond);
        return $this;
    }

    /**
     * set Date to start of the day
     * @return Date
     */
    public function setStartOfDay() {
        $this->set($this->iDay, $this->iMonth, $this->iYear, 0, 0, 0);
        return $this;
    }

    /**
     * set Date to end of day
     * @return Date
     */
    public function setEndOfDay() {
        $this->set($this->iDay, $this->iMonth, $this->iYear, 23, 59, 59);
        return $this;
    }

    /**
     * set day
     * @param int $iDay
     * @return Date
     */
    public function setDay($iDay) {
        $this->set($iDay, $this->iMonth, $this->iYear, $this->iHour, $this->iMinute, $this->iSecond);
        return $this;
    }

    /**
     * set month
     * @param int $iMonth
     * @return Date
     */
    public function setMonth($iMonth) {
        $this->set($this->iDay, $iMonth, $this->iYear, $this->iHour, $this->iMinute, $this->iSecond);
        return $this;
    }

    /**
     * set Year
     * @param int $iYear
     * @return Date
     */
    public function setYear($iYear) {
        $this->set($this->iDay, $this->iMonth, $iYear, $this->iHour, $this->iMinute, $this->iSecond);
        return $this;
    }

    /**
     * check if the given date is the same as this one
     * @param Date $oD
     * @return boolean 
     */
    public function sameDateAs($oD) {
        return ($this->iDay == $oD->iDay && $this->iMonth == $oD->iMonth && $this->iYear == $oD->iYear );
    }

    /**
     * return zodiac constellation
     * @return string 
     */
    public function getZodiacConstellation() {
        $aSigns = array
            (
            array(20, 'Capricorn'), // 22 December - 20 January
            array(20, 'Aquarius'), // 21 January - 20 February
            array(20, 'Pisces'), // 21 February - 20 March
            array(20, 'Aries'), // 21 March - 20 April
            array(20, 'Taurus'), // 21 April - 20 May
            array(20, 'Gemini'), // 21 May - 20 June
            array(22, 'Cancer'), // 21 June - 22 July
            array(22, 'Leo'), // 23 July - 22 August
            array(22, 'Virgo'), // 23 August - 22 September
            array(22, 'Libra'), // 23 September - 22 October
            array(22, 'Scorpio'), // 23 October - 22 November
            array(21, 'Sagittarius'), // 23 November - 21 December
            array(20, 'Capricorn') // 22 December - 20 January
        );
        return $this->iDay <= $aSigns[$this->iMonth - 1][0] ? $aSigns[$this->iMonth - 1][1] : $aSigns[$this->iMonth][1];
    }

    /*
     * get time by values and update Object
     *
     * @param int $iD Day
     * @param int $iM Month
     * @param int $iY Year
     * @param int $iH   Hour
     * @param int $iMn  Minute
     * @param int $iS Second
     */

    private function set($iD, $iM, $iY, $iH = 0, $iMn = 0, $iS = 0) {
        $this->iTime = mktime($iH, $iMn, $iS, $iM, $iD, $iY);
        $this->update();
    }

    /*
     * update values and set in object
     */

    private function update() {
        $this->iDay = $this->format("%d");
        $this->iMonth = $this->format("%m");
        $this->iYear = $this->format("%Y");
        $this->iHour = $this->format("%H");
        $this->iMinute = $this->format("%M");
        $this->iSecond = $this->format("%S");
        $this->iDayOfYear = $this->format("%j");
        $this->iDayOfWeek = $this->format("%w");
        $this->iWeekNumber = $this->format("%W");
    }

    /**
     * return formatted Date
     * @param string $sFromat format to use
     * @return string
     */
    public function format($sFormat) {
        // hack for using %e on windows systems
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $sFormat = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $sFormat);
        }
        return strftime($sFormat, $this->iTime);
    }

    /**
     * return a Dateobject based on a string
     * @param string $sVal
     * @return Date object
     */
    public static function strToDate($sVal) {
        return new Date($sVal);
    }

}

?>
