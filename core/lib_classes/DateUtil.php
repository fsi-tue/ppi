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
     * Convert a given string representing a date to a datetime object.
     */
    function stringToDateTime($dateString) {
        return new DateTime($dateString);
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
}
?>
