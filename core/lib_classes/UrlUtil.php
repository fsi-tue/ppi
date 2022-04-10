<?php
class UrlUtil {
    private $slashAtEnd = '';

    function getCurrentPageUrlInfo() {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') === FALSE ? 'http' : 'https';
        $host = $_SERVER['HTTP_HOST'];
        if ($host == 'localhost') {
            $this->slashAtEnd = '/';
        }
        $script = $_SERVER['SCRIPT_NAME'];
        $params = $_SERVER['QUERY_STRING'];
        $currentLocation = $protocol . '://' . $host;
        $dirname = $currentLocation . dirname($_SERVER['SCRIPT_NAME']);
        $currentUrl = $protocol . '://' . $host . $script;
        $currentUrlWithGetParams = $protocol . '://' . $host . $script . '?' . $params;
        return ['protocol' => $protocol, 'host' => $host, 'script' => $script, 'params' => $params, 'currentLocation' => $currentLocation, 'dirname' => $dirname, 'currentUrl' => $currentUrl, 'currentUrlWithGetParams' => $currentUrlWithGetParams];
    }
    
    /**
     * Get the current protocol ('http' or 'https').
     */
    function getCurrentProtocol() {
        return $this->getCurrentPageUrlInfo()['protocol'];
    }
    
    /**
     * Get the current host ('localhost', 'abc.defghij.kl', ...).
     */
    function getCurrentHost() {
        return $this->getCurrentPageUrlInfo()['host'];
    }
    
    /**
     * Get the currently executed script ('login.php', 'recurringtasks.php', ...).
     */
    function getCurrentScript() {
        return basename($this->getCurrentPageUrlInfo()['script']);
    }
    
    /**
     * Get the current GET params from the browser's address bar (e.g. 'a=5&b=6').
     */
    function getCurrentGetParams() {
        return $this->getCurrentPageUrlInfo()['params'];
    }
    
    /**
     * Get the current location (e.g. 'http://localhost').
     */
    function getCurrentLocation() {
        return $this->getCurrentPageUrlInfo()['currentLocation'];
    }
    
    /**
     * Get the current location (e.g. 'http://localhost/ppi').
     */
    function getCurrentDirname() {
        return $this->getCurrentPageUrlInfo()['dirname'] . $this->slashAtEnd;
    }
    
    /**
     * Get the current URL (e.g. 'http://localhost/ppi/recurringtasks.php').
     */
    function getCurrentUrl() {
        return $this->getCurrentPageUrlInfo()['currentUrl'];
    }
    
    /**
     * Get the current URL with the GET params (e.g. 'http://localhost/ppi/recurringtasks.php?a=5&b=6').
     */
    function getCurrentUrlWithGetParams() {
        return $this->getCurrentPageUrlInfo()['currentUrlWithGetParams'];
    }
}
?>
