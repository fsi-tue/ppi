<?php
class UrlUtil {
    function getCurrentPageUrlInfo() {
        $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === FALSE ? 'http' : 'https';
        $host = $_SERVER['HTTP_HOST'];
        $script = $_SERVER['SCRIPT_NAME'];
        $params = $_SERVER['QUERY_STRING'];
        $currentLocation = $protocol . '://' . $host;
        $dirname = $currentLocation . dirname($_SERVER['SCRIPT_NAME']);
        $currentUrl = $protocol . '://' . $host . $script;
        $currentUrlWithGetParams = $protocol . '://' . $host . $script . '?' . $params;
        return ['protocol' => $protocol, 'host' => $host, 'script' => $script, 'params' => $params, 'currentLocation' => $currentLocation, 'dirname' => $dirname, 'currentUrl' => $currentUrl, 'currentUrlWithGetParams' => $currentUrlWithGetParams];
    }
    
    function getCurrentProtocol() {
        return $this->getCurrentPageUrlInfo()['protocol'];
    }
    
    function getCurrentHost() {
        return $this->getCurrentPageUrlInfo()['host'];
    }
    
    function getCurrentScript() {
        return basename($this->getCurrentPageUrlInfo()['script']);
    }
    
    function getCurrentGetParams() {
        return $this->getCurrentPageUrlInfo()['params'];
    }
    
    function getCurrentLocation() {
        return $this->getCurrentPageUrlInfo()['currentLocation'];
    }
    
    function getCurrentDirname() {
        return $this->getCurrentPageUrlInfo()['dirname'];
    }
    
    function getCurrentUrl() {
        return $this->getCurrentPageUrlInfo()['currentUrl'];
    }
    
    function getCurrentUrlWithGetParams() {
        return $this->getCurrentPageUrlInfo()['currentUrlWithGetParams'];
    }
}
?>
