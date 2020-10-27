<?php
class Errors {
    private $i18n = null;
    private $logEventSystem = null;

    function __construct($i18n, $logEventSystem) {
        $this->i18n = $i18n;
        $this->logEventSystem = $logEventSystem;
    }
    
    function getErrorsAndWarnings() {
        $debugMessages = $this->logEventSystem->getLastDebugMessages($this->i18n->get('debug'));
        $warningMessages = $this->logEventSystem->getLastWarnings($this->i18n->get('warning'));
        $errorMessages = $this->logEventSystem->getLastErrors($this->i18n->get('error'));
        
        $retVal = '';
        if ($debugMessages != '') {
            $retVal .= '<div class="debugMessage">
                            <span class="closeDebugMessage" onclick="this.parentElement.style.display=\'none\';">&times;&nbsp;</span>
                            ' . $debugMessages . '
                        </div>';
        }
        if ($warningMessages != '') {
            $retVal .= '<div class="warning">
                            <span class="closeWarning" onclick="this.parentElement.style.display=\'none\';">&times;&nbsp;</span>
                            ' . $warningMessages . '
                        </div>';
        }
        if ($errorMessages != '') {
            $retVal .= '<div class="error">
                            <span class="closeError" onclick="this.parentElement.style.display=\'none\';">&times;&nbsp;</span>
                            ' . $errorMessages . '
                        </div>';
        }
        return $retVal;
    }
}
?>
