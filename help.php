<?php
    require_once('core/Main.php');

    echo $header->getHeader($i18n->get('title'), $i18n->get('help'), array('button.css'));

    $page = 'generic';

    if (isset($_GET['page'])) {
        $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_ENCODED);
    }
    echo '<center><br><a id="styledButton" onclick="history.go(-1)" style="margin: 0px;"><span style="font-size: 20px">&laquo;</span>&nbsp;' .  $i18n->get('back') . '</a><br><br>';
    echo $i18n->get($page . 'HelpContent') . '</center>';

    echo $footer->getFooter();
?>
