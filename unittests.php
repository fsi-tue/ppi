<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $log->info('unittests.php', 'User was not logged in');
        $redirect->redirectTo('login.php');
    }
    if ($currentUser->getRole() != Constants::USER_ROLES['admin']) {
        $log->error('unittests.php', 'User was not admin');
        $redirect->redirectTo('lectures.php');
    }
    
    echo $header->getHeader($i18n->get('title'), $i18n->get('unitTests'), array('protocols.css', 'button.css', 'searchableTable.css'));
    
    echo $mainMenu->getMainMenu($i18n, $currentUser);
    
    echo '<div id="protocolsTable" style="padding-left: 40px; padding-bottom: 40px; padding-right: 40px; margin: 0px;">';

    $headers = array($i18n->get('unitTests'), $i18n->get('status'));
    $widths = array(80, 20);
    $textAlignments = array('left', 'center');
    $data = array();
    $unitTestsNames = $testUtil->getTestNames();
    $unitTestsResults = $testUtil->runAllTests();
    for ($i = 0; $i < count($unitTestsNames); $i++) {
        $row = array();
        $row[] = $unitTestsNames[$i];
        $row[] = $unitTestsResults[$i];
        $data[] = $row;
    }
    
    echo $searchableTable->createTable($headers, $data, $widths, $textAlignments);
    
    echo '</div>';
    
    echo $footer->getFooter();
?>
