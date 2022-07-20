<?php
    require_once('core/Main.php');
    if (!$userSystem->isLoggedIn()) {
        $redirect->redirectTo('login.php');
    }
    else {
        $redirect->redirectTo('lectures.php');
    }
?>
