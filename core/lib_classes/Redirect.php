<?php
class Redirect {
    private $urlUtil = null;

    function __construct($urlUtil) {
        $this->urlUtil = $urlUtil;
    }
    
    function redirectTo($url) {
        $newUrl = $this->urlUtil->getCurrentDirname() . '/' . $url;
        header('Location: ' . $newUrl);
        exit();
    }
}
?>
