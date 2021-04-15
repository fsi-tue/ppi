<?php
class LogEventSystem {
    private $logEventDao = null;
    private $email = null;
    private $dateUtil = null;
    private $log = null;
    
    private $lastDebugMessages = array();
    private $lastWarnings = array();
    private $lastErrors = array();

    function __construct($logEventDao, $email, $dateUtil) {
        $this->logEventDao = $logEventDao;
        $this->email = $email;
        $this->dateUtil = $dateUtil;
    }

    /**
     * Set the log to enable error logging.
     */
    function setLog($log) {
        $this->log = $log;
    }
    
    /**
     * Save the log event to the database if the level is greater than the one set in the constants.
     * If the given level is greater than some thresholds, display the log message to the user and/or send an email to the admins.
     */
    function logEvent($datetimeString, $origin, $level, $message, $username) {
        $logMessage = $datetimeString . ' ' . $origin . ' ' . Constants::LOG_LEVELS[$level] . ' ' . $message . ' (' . $username . ')';
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
    
    /**
     * Get the number of log messages in the database of the given level.
     */
    function getNumberOfLogEventsTotal($username, $level) {
        return $this->logEventDao->getNumberOfLogEventsTotal($username, $level);
    }
    
    /**
     * Get the number of log messages in the database of the given level.
     */
    function getLogEvents($numberOfEntriesPerPage, $page, $username, $level) {
        return $this->logEventDao->getLogEvents($numberOfEntriesPerPage, $page, $username, $level);
    }
    
    /**
     * Get the debug log messages that occured during the current script execution so far.
     */
    function getLastDebugMessages($prefix) {
        return $this->arrayToString($this->lastDebugMessages, $prefix);
    }
    
    /**
     * Get the warning log messages that occured during the current script execution so far.
     */
    function getLastWarnings($prefix) {
        return $this->arrayToString($this->lastWarnings, $prefix);
    }
    
    /**
     * Get the error log messages that occured during the current script execution so far.
     */
    function getLastErrors($prefix) {
        return $this->arrayToString($this->lastErrors, $prefix);
    }
    
    /**
     * Combine an array of strings to a single string for displaying.
     */
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
