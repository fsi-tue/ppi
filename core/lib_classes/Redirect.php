<?php
class Redirect {
    private $urlUtil = null;

    function __construct($urlUtil) {
        $this->urlUtil = $urlUtil;
    }

    /**
     * Redirect the browser to the given URL and terminate the PHP execution.
     */
    function redirectTo($url) {
        $newUrl = $this->urlUtil->getCurrentDirname() . $url;
        header('Location: ' . $newUrl);
        exit();
    }
}
?>
