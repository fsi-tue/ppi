<?php
class Log {
    private $logEventSystem = null;
    private $dateUtil = null;
    private $username = 'none';

    function __construct($logEventSystem, $dateUtil) {
        $this->logEventSystem = $logEventSystem;
        $this->dateUtil = $dateUtil;
    }
    
    function setUsername($username) {
        $this->username = $username;
    }
    
    function debug($origin, $message) {
        $nowString = $this->dateUtil->dateTimeToString($this->dateUtil->getDateTimeNow());
        $this->logEventSystem->logEvent($nowString, $origin, 0, $message, $this->username);
    }
    
    function info($origin, $message) {
        $nowString = $this->dateUtil->dateTimeToString($this->dateUtil->getDateTimeNow());
        $this->logEventSystem->logEvent($nowString, $origin, 1, $message, $this->username);
    }
    
    function warning($origin, $message) {
        $nowString = $this->dateUtil->dateTimeToString($this->dateUtil->getDateTimeNow());
        $this->logEventSystem->logEvent($nowString, $origin, 2, $message, $this->username);
    }
    
    function error($origin, $message) {
        $nowString = $this->dateUtil->dateTimeToString($this->dateUtil->getDateTimeNow());
        $this->logEventSystem->logEvent($nowString, $origin, 3, $message, $this->username);
    }
    
    function critical($origin, $message) {
        $nowString = $this->dateUtil->dateTimeToString($this->dateUtil->getDateTimeNow());
        $this->logEventSystem->logEvent($nowString, $origin, 4, $message, $this->username);
    }
}
?>
