<?php
class HelpMenu {
    private $i18n = null;

    function __construct($i18n) {
        $this->i18n = $i18n;
    }
    
    function getHelp($currentPage) {
        if (strcmp($currentPage, 'help') == 0) {
            return '<a href="help.php?page=faq&return=lectures">' . $this->i18n->get('faq') . '</a>';
        }
        return '<a href="help.php?page=' . $currentPage . '&return=' . $currentPage . '">' .  $this->i18n->get($currentPage . 'Help') . '</a><a href="help.php?page=faq&return=' . $currentPage . '">' . $this->i18n->get('faq') . '</a>';
    }
}
?>
