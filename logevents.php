<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $redirect->redirectTo('login.php');
    }
    if ($currentUser->getRole() != Constants::USER_ROLES['admin']) {
        $redirect->redirectTo('lectures.php');
    }
    
    $page = 0;
    $username = '';
    $level = '';
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $pageValue = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_ENCODED);
        if (is_numeric($pageValue)) {
            $page = intval($pageValue);
        }
        $username = filter_input(INPUT_GET, 'username', FILTER_SANITIZE_ENCODED);
        $level = filter_input(INPUT_GET, 'level', FILTER_SANITIZE_ENCODED);
    }
    
    echo $header->getHeader($i18n->get('title'), $i18n->get('logEvents'), array('protocols.css', 'button.css'));
    
    echo $mainMenu->getMainMenu($i18n, $currentUser);
    
    echo '<center>
                <details open>
                    <summary id="styledButton" style="line-height: 10px; margin: 5px;">' . $i18n->get('filter') . '</summary>
                    <div style="width: 50%; display: inline-block; text-align: center; margin: 0px;">
                        <form action="logevents.php" method="GET">
                            <input type="text" name="username" value="' . $username . '" placeholder="' . $i18n->get('username') . '">
                            <input type="submit" value="' . $i18n->get('ok') . '">
                        </form>
                    </div>
                    <div style="width: 49%; display: inline-block; text-align: center; margin: 0px;">
                        <a href="?level=" id="styledButtonGreen" style="margin: 0px;">' . $i18n->get('reset') . '</a>
                        <a href="?level=' . Constants::LOG_LEVELS[0] . '" id="styledButtonGreen" style="margin: 0px;">' . Constants::LOG_LEVELS[0] . '</a>
                        <a href="?level=' . Constants::LOG_LEVELS[1] . '" id="styledButtonGreen" style="margin: 0px;">' . Constants::LOG_LEVELS[1] . '</a>
                        <a href="?level=' . Constants::LOG_LEVELS[2] . '" id="styledButtonGreen" style="margin: 0px;">' . Constants::LOG_LEVELS[2] . '</a>
                        <a href="?level=' . Constants::LOG_LEVELS[3] . '" id="styledButtonGreen" style="margin: 0px;">' . Constants::LOG_LEVELS[3] . '</a>
                        <a href="?level=' . Constants::LOG_LEVELS[4] . '" id="styledButtonGreen" style="margin: 0px;">' . Constants::LOG_LEVELS[4] . '</a>
                    </div>
                </details>
            </center>';

    $numberOfLogEventsTotal = $logEventSystem->getNumberOfLogEventsTotal($username, $level);
    
    echo $pagedContentUtil->getNavigation($page, Constants::NUMBER_OF_ENTRIES_PER_PAGE, $numberOfLogEventsTotal);
    
    echo '<br><br>';
    
    echo '<div style="width: 5%; display: inline-block; text-align: center;">' . $i18n->get('ID') . '</div>' .
         '<div style="width: 20%; display: inline-block;">' . $i18n->get('date') . '</div>' .
         '<div style="width: 10%; display: inline-block;">' . $i18n->get('username') . '</div>' .
         '<div style="width: 10%; display: inline-block;">' . $i18n->get('level') . '</div>' .
         '<div style="width: 45%; display: inline-block;">' . $i18n->get('remark') . '</div>' .
         '<div style="width: 10%; display: inline-block;">' . $i18n->get('origin') . '</div>';
    
    $allLogEvents = $logEventSystem->getLogEvents(Constants::NUMBER_OF_ENTRIES_PER_PAGE, $page, $username, $level);
    
    foreach ($allLogEvents as &$event) {
        $color = '';
        if ($event->getLevel() == Constants::LOG_LEVELS[1]) {
            $color = 'background-color: #aaaafa; ';
        } else if ($event->getLevel() == Constants::LOG_LEVELS[2]) {
            $color = 'background-color: yellow; ';
        } else if ($event->getLevel() == Constants::LOG_LEVELS[3]) {
            $color = 'background-color: orange; ';
        } else if ($event->getLevel() == Constants::LOG_LEVELS[4]) {
            $color = 'background-color: red; ';
        }
        echo '<form method="POST" action="examprotocolslist.php" style="' . $color . '">' .
             '<div style="width: 5%; display: inline-block; text-align: center;">' . $event->getID() . '</div>' .
             '<div style="width: 20%; display: inline-block;">' . $dateUtil->dateTimeToString($event->getDate()) . '</div>' .
             '<div style="width: 10%; display: inline-block;">' . $event->getUsername() . '</div>' .
             '<div style="width: 10%; display: inline-block;">' . $event->getLevel() . '</div>' .
             '<div style="width: 45%; display: inline-block;">' . $event->getRemark() . '</div>' .
             '<div style="width: 10%; display: inline-block;">' . $event->getOrigin() . '</div>' .
             '</form>';
    }
    
    echo '<br>';
    
    echo $pagedContentUtil->getNavigation($page, Constants::NUMBER_OF_ENTRIES_PER_PAGE, $numberOfLogEventsTotal);
    
    echo $footer->getFooter();
?>
