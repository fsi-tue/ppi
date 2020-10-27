<?php
    require_once('core/Main.php');

    echo $header->getHeader($i18n->get('title'), $i18n->get('impressum'), array('impressum.css'));

    echo '<div id="impressumField">' . $i18n->get('impressumContent') . '</div>';

    echo $footer->getFooter();
?>
