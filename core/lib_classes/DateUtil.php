<?php
class DateUtil {

    function getDateTimeNow() {
        return new DateTime();
    }
    
    function dateTimeToString($dateTime) {
        return $dateTime->format(Constants::DATE_FORMAT);
    }
    
    function dateTimeToStringForDisplaying($dateTime, $language) {
        if ($language == 'en') {
            return $dateTime->format(Constants::DATE_FORMAT_ENGLISH);
        }
        return $dateTime->format(Constants::DATE_FORMAT_GERMAN);
    }
    
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
