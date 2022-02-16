<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $log->info('recurringtasks.php', 'User was not logged in');
        $redirect->redirectTo('login.php');
    }
    if ($currentUser->getRole() != Constants::USER_ROLES['admin']) {
        $log->error('recurringtasks.php', 'User was not admin');
        $redirect->redirectTo('lectures.php');
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $recurringTaskID = filter_input(INPUT_GET, 'run', FILTER_SANITIZE_ENCODED);
        if (isset($_GET['run'])) {
            if (is_numeric($recurringTaskID)) {
                $result = $recurringTasksSystem->setUpTaskForRun($recurringTaskID);
                if ($result) {
                    $log->debug('recurringtasks.php', 'Successfully set up task to be run with ID: ' . $recurringTaskID);
                    $redirect->redirectTo('recurringtasks.php');
                } else {
                    $log->error('recurringtasks.php', 'Setting up task to be run unsuccessful with ID ' . $recurringTaskID);
                }
            } else {
                $log->error('recurringtasks.php', 'Recurring task ID is not numeric: ' . $recurringTaskID);
            }
        }
    }
    
    echo $header->getHeader($i18n->get('title'), $i18n->get('recurringTasks'), array('protocols.css', 'button.css', 'searchableTable.css'));
    
    echo $mainMenu->getMainMenu($i18n, $currentUser);
    
    echo '<div id="recurringTasksTable" style="padding-left: 40px; padding-bottom: 40px; padding-right: 40px; margin: 0px;">';
    
    $recurringTasksData = $recurringTasksSystem->getLastResults();

    $headers = array($i18n->get('ID'), $i18n->get('recurringTaskName'), $i18n->get('lastSuccessfulRun'), $i18n->get('nextRun'), $i18n->get('status'), $i18n->get('runNow'));
    $widths = array(10, 40, 10, 10, 10, 10);
    $textAlignments = array('left', 'left', 'left', 'left', 'center', 'center');
    $data = array();
    for ($i = 0; $i < count($recurringTasksData); $i++) {
        $color = Constants::FAILED_COLOR;
        if ($recurringTasksData[$i][4] == 'SUCCESS') {
            $color = Constants::SUCCESS_COLOR;
        } else if ($recurringTasksData[$i][4] == 'NOT_TO_BE_RUN') {
            $color = 'rgba(0, 0, 0, 0)';
        } else if ($recurringTasksData[$i][4] == 'NO_CHANGE') {
            $color = Constants::SUCCESS_COLOR;
        }
        $row = array();
        $id = $recurringTasksData[$i][0];
        $row[] = $id;
        $row[] = $recurringTasksData[$i][1];
        $row[] = $dateUtil->dateTimeToStringForDisplaying($recurringTasksData[$i][2], $currentUser->getLanguage());
        $row[] = $dateUtil->dateTimeToStringForDisplaying($recurringTasksData[$i][3], $currentUser->getLanguage());
        $row[] = '<div style="background-color: ' . $color . ';">' . $recurringTasksData[$i][4] . '</div>';
        $row[] = '<a id="styledButton" href="?run=' . $id . '"><img src="static/img/run.png' . $GLOBALS["VERSION_STRING"] . '" style="height: 24px; vertical-align: middle;">&nbsp;&nbsp;' . $i18n->get('runNow') . '</a>';
        $data[] = $row;
    }
    
    echo $searchableTable->createTable($headers, $data, $widths, $textAlignments);
    
    echo '</div>';
    
    echo $footer->getFooter();
?>
