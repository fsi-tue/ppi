<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $log->info('lectures.php', 'User was not logged in');
        $redirect->redirectTo('login.php');
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $borrowLectureID = filter_input(INPUT_GET, 'borrow', FILTER_SANITIZE_ENCODED);
        if (isset($_GET['borrow'])) {
            if (is_numeric($borrowLectureID)) {
                $allProtocolIDsOfLecture = $lectureSystem->getAllProtocolIDsOfLecture($borrowLectureID);
                if (count($allProtocolIDsOfLecture) > 0) {
                    $userSystem->borrowLecture($currentUser, $borrowLectureID);
                    $log->debug('lectures.php', 'User ' . $currentUser->getUsername() . ' successfully borrowed lecture ' . $borrowLectureID);
                } else {
                    $log->error('lectures.php', 'User tried to borrow lecture that has no accepted exam protocols: ' . $borrowLectureID);
                }
            } else {
                $log->error('lectures.php', 'Lecture ID is not numeric: ' . $borrowLectureID);
            }
        }
        $reportLectureID = filter_input(INPUT_GET, 'report', FILTER_SANITIZE_ENCODED);
        if (isset($_GET['report'])) {
            if (is_numeric($reportLectureID)) {
                $reportingResult = $lectureSystem->reportLectureHasOutdatedProtocols($reportLectureID, $currentUser->getUsername());
                if ($reportingResult == true) {
                    $log->info('lectures.php', 'Successfully reported the lecture as outdated.');
                }
            } else {
                $log->error('lectures.php', 'Lecture ID is not numeric: ' . $borrowLectureID);
            }
        }
    }

    echo $header->getHeader($i18n->get('title'), $i18n->get('showLectures'), array('protocols.css', 'button.css', 'searchableTable.css'));
    
    echo $mainMenu->getMainMenu($i18n, $currentUser);
    echo '<div class="info">';
    echo $i18n->get('infoOnMainPage');
    echo '</div>';

    echo '<div id="protocolsTable" style="padding-left: 40px; padding-bottom: 40px; padding-right: 40px; margin: 0px;">';

    $headers = array($i18n->get('lectureTitle'), $i18n->get('numberOfProtocols'), $i18n->get('borrow'), $i18n->get('reportAsOutdated'));
    $widths = array(70, 10, 10, 10);
    $textAlignments = array('left', 'center', 'center', 'center');
    
    $allLectures = $lectureSystem->getAllLecturesWithAcceptedProtocols();
    // sort list of lectures by name
    usort($allLectures, function($a, $b) {
        return strcmp($a->getName(), $b->getName());
    });
    $borrowRecords = $currentUser->getBorrowRecords();
    
    $currentlyBorrowedLectureIds = array();
    foreach ($borrowRecords as &$record) {
        $currentlyBorrowedLectureIds[] = $record->getLectureID();
    }
    
    $data = array();
    $insertBeginningOfArray = false;
    foreach ($allLectures as &$lecture) {
        $row = array();
        $row[] = $lecture->getName();
        $row[] = count($lecture->getAssignedExamProtocols());
        $insertBeginningOfArray = false;
        if (in_array($lecture->getID(), $currentlyBorrowedLectureIds)) {
            $insertBeginningOfArray = true;
            $row[] = '<a id="styledButtonGreen" href="download.php"><img src="static/img/protocolCheckmark.png" style="height: 24px; vertical-align: middle;">&nbsp;&nbsp;' . $i18n->get('borrowed') . '</a>';
        } else {
            if ($currentUser->getTokens() <= 0) {
                $row[] = '<a id="styledButtonRed" href="?borrow=' . $lecture->getID() . '"><img src="static/img/protocol.png" style="height: 24px; vertical-align: middle;">&nbsp;&nbsp;' . $i18n->get('noTokens') . '</a>';
            } else if (count($lecture->getAssignedExamProtocols()) > 0) {
                $row[] = '<a id="styledButton" href="?borrow=' . $lecture->getID() . '"><img src="static/img/protocol.png" style="height: 24px; vertical-align: middle;">&nbsp;&nbsp;' . $i18n->get('borrow') . '</a>';
            } else {
                $row[] = '<a id="styledButtonGray" href=""><img src="static/img/protocolNotAvailable.png" style="height: 24px; vertical-align: middle;">&nbsp;&nbsp;' . $i18n->get('noProtocols') . '</a>';
            }
        }
        $reportingPossible = true;
        if (isset($_GET['report'])) {
            if (is_numeric($reportLectureID)) {
                if ($lecture->getID() == $reportLectureID) {
                    $reportingPossible = false;
                }
            }
        }
        if ($reportingPossible) {
            $row[] = '<a id="styledButtonRed" href="?report=' . $lecture->getID() . '"><img src="static/img/report.png" style="height: 24px; vertical-align: middle;"></a>';
        } else {
            $row[] = '<a id="styledButtonGray" href=""><img src="static/img/report.png" style="height: 24px; vertical-align: middle;"></a>';
        }
        
        if ($insertBeginningOfArray) {
            array_splice($data, 0, 0, array($row));
        } else {
            $data[] = $row;
        }
    }
    echo $searchableTable->createTable($headers, $data, $widths, $textAlignments);
    
    echo '</div>';

    echo $footer->getFooter();
?>
