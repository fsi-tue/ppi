<?php
class LogEventSystem {
    private $logEventDao = null;
    private $email = null;
    private $dateUtil = null;
    
    private $lastDebugMessages = array();
    private $lastWarnings = array();
    private $lastErrors = array();

    function __construct($logEventDao, $email, $dateUtil) {
        $this->logEventDao = $logEventDao;
        $this->email = $email;
        $this->dateUtil = $dateUtil;
    }
    
    function logEvent($datetimeString, $origin, $level, $message, $username) {
        $logMessage = $datetimeString . ' ' . $origin . ' ' . $level . ' ' . $message . ' ' . $username;
        if ($level < 2) {
            $this->lastDebugMessages[] = $logMessage;
        } else if ($level == 2) {
            $this->lastWarnings[] = $logMessage;
        } else if ($level > 2) {
            $this->lastErrors[] = $logMessage;
        }
        
        if ($level >= Constants::LOG_TO_DATABASE_FROM_LEVEL) {
            $logEvent = new LogEvent(NULL, $datetimeString, $username, Constants::LOG_LEVELS[$level], $message, $origin);
            $this->logEventDao->addLogEvent($logEvent);
        }

        if ($level >= Constants::ALERT_ADMIN_FROM_LEVEL) {
            $this->email->send(Constants::EMAIL_ADMIN, 'Error occured in PPI', $logMessage);
        }
    }
    
    function getNumberOfLogEventsTotal($username, $level) {
        return $this->logEventDao->getNumberOfLogEventsTotal($username, $level);
    }
    
    function getLogEvents($numberOfEntriesPerPage, $page, $username, $level) {
        return $this->logEventDao->getLogEvents($numberOfEntriesPerPage, $page, $username, $level);
    }
    
    function getLastDebugMessages($prefix) {
        return $this->arrayToString($this->lastDebugMessages, $prefix);
    }
    
    function getLastWarnings($prefix) {
        return $this->arrayToString($this->lastWarnings, $prefix);
    }
    
    function getLastErrors($prefix) {
        return $this->arrayToString($this->lastErrors, $prefix);
    }
    
    function arrayToString($messages, $prefix) {
        $retVal = '';
        for ($i = 0; $i < count($messages); $i++) {
            if ($retVal != '') {
                $retVal .= '<br>';
            }
            $retVal .= $prefix . ': ' . $messages[$i];
        }
        return $retVal;
    }
}
?>
