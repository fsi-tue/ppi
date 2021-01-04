<?php
    require_once('core/Main.php');
    if (!$userSystem->isLoggedIn()) {
        $log->info('lectures.php', 'User was not logged in');
        $redirect->redirectTo('login.php');
    }
    else {
        $redirect->redirectTo('lectures.php');
    }
?>
