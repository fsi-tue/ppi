<?php
    require_once('core/Main.php');

    echo $header->getHeader($i18n->get('title'), $i18n->get('impressum'), array('impressum.css'));
    
    echo '<div id="impressumField">PPI - Version: ' . Constants::VERSION . '<br><br><br>' . $i18n->get('impressumContent') . '</div>';

    echo $footer->getFooter();
?>
