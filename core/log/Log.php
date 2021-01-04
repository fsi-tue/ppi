<?php
class Log {
    private $logEventSystem = null;
    private $dateUtil = null;
    private $username = 'no user logged in';

    function __construct($logEventSystem, $dateUtil) {
        $this->logEventSystem = $logEventSystem;
        $this->dateUtil = $dateUtil;
    }
    
    /**
     * Set the username of the current user. This will appear at the end of all log messages.
     */
    function setUsername($username) {
        $this->username = $username;
    }
    
    /**
     * Add a debug log message.
     */
    function debug($origin, $message) {
        $origin .= ' (line ' . debug_backtrace()[0]['line'] . ')';
        $nowString = $this->dateUtil->dateTimeToString($this->dateUtil->getDateTimeNow());
        $this->logEventSystem->logEvent($nowString, $origin, 0, $message, '(' . $this->username . ')');
    }
    
    /**
     * Add a info log message.
     */
    function info($origin, $message) {
        $origin .= ' (line ' . debug_backtrace()[0]['line'] . ')';
        $nowString = $this->dateUtil->dateTimeToString($this->dateUtil->getDateTimeNow());
        $this->logEventSystem->logEvent($nowString, $origin, 1, $message, '(' . $this->username . ')');
    }
    
    /**
     * Add a warning log message.
     */
    function warning($origin, $message) {
        $origin .= ' (line ' . debug_backtrace()[0]['line'] . ')';
        $nowString = $this->dateUtil->dateTimeToString($this->dateUtil->getDateTimeNow());
        $this->logEventSystem->logEvent($nowString, $origin, 2, $message, '(' . $this->username . ')');
    }
    
    /**
     * Add a error log message.
     */
    function error($origin, $message) {
        $origin .= ' (line ' . debug_backtrace()[0]['line'] . ')';
        $nowString = $this->dateUtil->dateTimeToString($this->dateUtil->getDateTimeNow());
        $this->logEventSystem->logEvent($nowString, $origin, 3, $message, '(' . $this->username . ')');
    }
    
    /**
     * Add a critical log message.
     */
    function critical($origin, $message) {
        $origin .= ' (line ' . debug_backtrace()[0]['line'] . ')';
        $nowString = $this->dateUtil->dateTimeToString($this->dateUtil->getDateTimeNow());
        $this->logEventSystem->logEvent($nowString, $origin, 4, $message, '(' . $this->username . ')');
    }
}
?>
