<?php
class DateUtil {
    /**
     * Get the current timestamp (now) as datetime object.
     */
    function getDateTimeNow() {
        return new DateTime();
    }
    
    /**
     * Get a timestamp from the year 2000 as datetime object.
     */
    function getDateTimeFarInThePast() {
        return $this->stringToDateTime('2000-01-01 01:01:01.001');
    }
    
    /**
     * Convert a datetime object to a string in the system date format.
     */
    function dateTimeToString($dateTime) {
        return $dateTime->format(Constants::DATE_FORMAT);
    }
    
    /**
     * Convert a datetime object to a string in the date format of the given language locale.
     */
    function dateTimeToStringForDisplaying($dateTime, $language) {
        if ($language == 'en') {
            return $dateTime->format(Constants::DATE_FORMAT_ENGLISH);
        }
        return $dateTime->format(Constants::DATE_FORMAT_GERMAN);
    }
    
    /**
     * Gets a format string describing how to write a date in the current locale.
     */
    function getDateFormatString($language) {
        $retVal = Constants::DATE_FORMAT_GERMAN;
        if ($language == 'en') {
            $retVal = Constants::DATE_FORMAT_ENGLISH;
        }
        $retVal = str_replace('H', '', $retVal);
        $retVal = str_replace(':', '', $retVal);
        $retVal = str_replace('i', '', $retVal);
        $retVal = str_replace(' ', '', $retVal);
        $retVal = str_replace('d', 'dd', $retVal);
        $retVal = str_replace('m', 'mm', $retVal);
        $retVal = str_replace('Y', 'yyyy', $retVal);
        return $retVal;
    }
    
    /**
     * Convert a given string representing a date to a datetime object.
     */
    function stringToDateTime($dateString) {
        return new DateTime($dateString);
    }
    
    /**
     * Returns true if the given date string represents a valid date (e.g. '20.12.2045' or '03-16-1967').
     */
    function checkIfIsValidDateString($dateString) {
        return strtotime($dateString);
    }
    
    /**
     * Adds the given amount to the given datetime and returns a datetime object with that date. Possible units are 'WEEKS', 'DAYS', 'HOURS', 'MINUTES'.
     */
    function addToDateTime($datetime, $amount, $unit) {
        $datetimeCopy = clone $datetime;
        if ($amount == 0) {
            return $datetime;
        }
        $datetimeNew = date_add($datetimeCopy, date_interval_create_from_date_string($amount . ' ' . strtolower($unit)));
        return $datetimeNew;
    }
    
    /**
     * Returns TRUE if datetime1 is smaller than datetime2, FALSE otherwise.
     */
    function isSmallerThan($datetime1, $datetime2) {
        return $datetime1->getTimestamp() < $datetime2->getTimestamp();
    }
    
    /**
     * Get the days, hours, minutes and seconds until the given date is reached as string.
     */
    function getDifferenceToNowAsString($datetime, $invertSign, $dayString, $daysString, $hourString, $hoursString, $minuteString, $minutesString) {
        $now = $this->getDateTimeNow();
        $interval = $now->diff($datetime);
        
        if (intval($interval->format('%a')) == 1) {
        	$daysString = $dayString;
        }
        if (intval($interval->format('%h')) == 1) {
        	$hoursString = $hourString;
        }
        if (intval($interval->format('%i')) == 1) {
        	$minutesString = $minuteString;
        }
        
        $insertMinusFlag = $this->isSmallerThan($datetime, $now);
        if ($invertSign) {
            $insertMinusFlag = $this->isSmallerThan($now, $datetime);
        }
        $insertMinus = '';
        if ($insertMinusFlag) {
        	$insertMinus = '-';
        }
        
        $formatString = $insertMinus . '%a ' . $daysString . ', ' . $insertMinus . '%h ' . $hoursString . ', ' . $insertMinus .  '%i ' . $minutesString;
        return strval($interval->format($formatString));
    }
}
?>
