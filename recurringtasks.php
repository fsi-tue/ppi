<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $redirect->redirectTo('login.php');
    }
    if ($currentUser->getRole() != Constants::USER_ROLES['admin']) {
        $redirect->redirectTo('lectures.php');
    }
    
    echo $header->getHeader($i18n->get('title'), $i18n->get('recurringTasks'), array('protocols.css', 'button.css', 'searchableTable.css'));
    
    echo $mainMenu->getMainMenu($i18n, $currentUser);
    
    echo '<div id="protocolsTable" style="padding-left: 40px; padding-bottom: 40px; padding-right: 40px; margin: 0px;">';
    
    // TODO: remove this
    echo '<br><br>max 100000 log entries, cleanup<br><br><br>delete outdated borrow records<br><br><br>delete protocols that are marked for deletion regularly<br><br><br>clean up the zip files folder<br><br><br>what else are recurring tasks?<br><br><br>';

    $headers = array($i18n->get('recurringTasks'), $i18n->get('date'), $i18n->get('status'));
    $widths = array(60, 20, 20);
    $textAlignments = array('left', 'left', 'center');
    $data = array();
    // TODO implement
    $recurringTasksNames = array();
    $recurringTasksDates = array();
    $recurringTasksStatus = array();
    for ($i = 0; $i < count($recurringTasksNames); $i++) {
        $row = array();
        $row[] = $recurringTasksNames[$i];
        $row[] = $recurringTasksDates[$i];
        $row[] = $recurringTasksStatus[$i];
        $data[] = $row;
    }
    
    echo $searchableTable->createTable($headers, $data, $widths, $textAlignments);
    
    echo '</div>';
    
    echo $footer->getFooter();
?>
