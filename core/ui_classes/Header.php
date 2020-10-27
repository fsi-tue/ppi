<?php
class Header {
    private $helpMenu;
    private $userMenu;
    private $currentUser;
    private $i18n;

    function __construct($helpMenu, $userMenu, $currentUser, $i18n) {
        $this->helpMenu = $helpMenu;
        $this->userMenu = $userMenu;
        $this->currentUser = $currentUser;
        $this->i18n = $i18n;
    }

    function getHeader($pageTitle, $title, $listOfStylesheets) {
        $header = '<!DOCTYPE html>
            <html lang="de">
                <head>
                    <meta charset="utf-8">
                    <title>' . $pageTitle . '</title>
                    <link rel="icon" type="image/png" sizes="32x32" href="static/img/fsiFavicon.png">';
        array_unshift($listOfStylesheets, 'main.css');
        foreach ($listOfStylesheets as &$stylesheet) {
            $header .= ' <link rel="stylesheet" href="static/css/' . $stylesheet . '">';
        }
        $header .= '</head>

                <body>
                    <div id="container">
                        <div id="header">
                            <div id="headerLogo">
                                <a href="lectures.php">
                                    <img src="static/img/fsiLogo.png" style="height: 30px;" alt="fsi logo">
                                </a>
                            </div>
                            <div id="headerTitle">
                            ' . $title . '
                            </div>
                            <div id="headerMenues">
                                <div id="helpMenu" class="dropdown">
                                    <img src="static/img/question.png" style="height: 30px;" alt="help">
                                    <img src="static/img/arrowDown.png" style="height: 17px;" alt="arrow down">
                                    <div class="dropdown-content">
                                        ' . $this->helpMenu->getHelp(basename($_SERVER['PHP_SELF'], '.php')) . '
                                    </div>
                                </div>
                                
                                <div id="userMenu" class="dropdown">
                                    <img src="static/img/person.png" style="height: 30px;" alt="user menu">
                                    <img src="static/img/arrowDown.png" style="height: 17px;" alt="arrow down">
                                    <div class="dropdown-content">
                                        ' . $this->userMenu->getUserMenu($this->currentUser, $this->i18n) . '
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="content">';
        return $header;
    }
}
?>
