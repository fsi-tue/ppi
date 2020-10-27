<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $redirect->redirectTo('login.php');
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $borrowLectureID = filter_input(INPUT_GET, 'borrow', FILTER_SANITIZE_ENCODED);
        if (isset($_GET['borrow'])) {
            if (is_numeric($borrowLectureID)) {
                $allProtocolIDsOfLecture = $lectureSystem->getAllProtocolIDsOfLecture($borrowLectureID);
                if (count($allProtocolIDsOfLecture) > 0) {
                    $userSystem->borrowLecture($currentUser, $borrowLectureID);
                } else {
                    $log->error('lectures.php', 'User tried to borrow lecture that has no accepted exam protocols: ' . $borrowLectureID);
                }
            }
        }
    }

    echo $header->getHeader($i18n->get('title'), $i18n->get('showLectures'), array('protocols.css', 'button.css', 'searchableTable.css'));
    
    echo $mainMenu->getMainMenu($i18n, $currentUser);

    echo '<div id="protocolsTable" style="padding-left: 40px; padding-bottom: 40px; padding-right: 40px; margin: 0px;">';

    $headers = array($i18n->get('lectureTitle'), $i18n->get('lectureCode'), $i18n->get('examField'), $i18n->get('borrow'));
    $widths = array(70, 10, 10, 10);
    $textAlignments = array('left', 'left', 'left', 'center');
    
    $allLectures = $lectureSystem->getAllLecturesWithAcceptedProtocols();
    $borrowRecords = $currentUser->getBorrowRecords();
    
    $currentlyBorrowedLectureIds = array();
    foreach ($borrowRecords as &$record) {
        $currentlyBorrowedLectureIds[] = $record->getLectureID();
    }
    
    $data = array();
    foreach ($allLectures as &$lecture) {
        $row = array();
        $row[] = $lecture->getLongName();
        $row[] = $lecture->getShortName();
        $row[] = $lecture->getField();
        if (in_array($lecture->getID(), $currentlyBorrowedLectureIds)) {
            $row[] = '<a id="styledButtonGreen" href="download.php"><nobr><img src="static/img/protocolCheckmark.png" style="height: 24px; vertical-align: middle;">&nbsp;&nbsp;' . $i18n->get('borrowed') . '</nobr></a>';
        } else {
            if ($currentUser->getTokens() <= 0) {
                $row[] = '<a id="styledButtonRed" href="?borrow=' . $lecture->getID() . '"><nobr><img src="static/img/protocol.png" style="height: 24px; vertical-align: middle;">&nbsp;&nbsp;' . $i18n->get('noTokens') . '</nobr></a>';
            } else if (count($lecture->getAssignedExamProtocols()) > 0) {
                $row[] = '<a id="styledButton" href="?borrow=' . $lecture->getID() . '"><nobr><img src="static/img/protocol.png" style="height: 24px; vertical-align: middle;">&nbsp;&nbsp;' . $i18n->get('borrow') . '</nobr></a>';
            } else {
                $row[] = '<a id="styledButtonGray" href=""><nobr><img src="static/img/protocolNotAvailable.png" style="height: 24px; vertical-align: middle;">&nbsp;&nbsp;' . $i18n->get('noProtocols') . '</nobr></a>';
            }
        }
        $data[] = $row;
    }
    echo $searchableTable->createTable($headers, $data, $widths, $textAlignments);
    
    echo '</div>';

    echo $footer->getFooter();
?>
