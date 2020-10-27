<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $redirect->redirectTo('login.php');
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        if (isset($_GET['lecture'])) {
            $lectureToDownloadID = filter_input(INPUT_GET, 'lecture', FILTER_SANITIZE_ENCODED);
            if (is_numeric($lectureToDownloadID)) {
                if (userHasBorrowed($lectureToDownloadID, $dateUtil, $currentUser)) {
                    $basePath = $fileUtil->getFullPathToBaseDirectory();
                    $protocolFileIDs = $lectureSystem->getAllProtocolIDsOfLecture($lectureToDownloadID);
                    $protocolFileNames = $examProtocolSystem->getFilePathsFromProtocolIDs($protocolFileIDs);
                    $zipFilePath = $basePath . 'exam_protocols/zip_files/' . $hashUtil->generateRandomString() . '.zip';
                    $fileUtil->zipFiles($protocolFileNames, $zipFilePath);
                    $fileUtil->downloadZipFile($zipFilePath);
                } else {
                    $log->warning('download.php', 'User tried to download protocols of a lecture that he or she has not borrowed: ' . $lectureToDownloadID);
                }
            } else {
                $log->warning('download.php', 'Got invalid lecture ID to download: ' . $lectureToDownloadID);
            }
        }
    }

    echo $header->getHeader($i18n->get('title'), $i18n->get('downloadProtocols'), array('protocols.css', 'button.css', 'searchableTable.css'));
    
    echo $mainMenu->getMainMenu($i18n, $currentUser);

    echo '<div id="protocolsTable" style="padding-left: 40px; padding-bottom: 40px; padding-right: 40px; margin: 0px;">';

    $headers = array($i18n->get('lectureTitle'), $i18n->get('lectureCode'), $i18n->get('examField'), $i18n->get('borrowedUntil'), $i18n->get('download'));
    $widths = array(60, 10, 10, 10, 10);
    $textAlignments = array('left', 'left', 'left', 'left', 'center');
    
    $allLectures = $lectureSystem->getAllLectures();
    $borrowRecords = $currentUser->getBorrowRecords();
    
    $currentlyBorrowedLectureIds = array();
    foreach ($borrowRecords as &$record) {
        $currentlyBorrowedLectureIds[] = $record->getLectureID();
    }
    
    function getBorrowedUntilFromLectureId($borrowRecords, $lectureId, $dateUtil, $currentUser) {
        foreach ($borrowRecords as &$record) {
            if ($record->getLectureID() == $lectureId) {
                return $dateUtil->dateTimeToStringForDisplaying($record->getBorrowedUntilDate(), $currentUser->getLanguage());
            }
        }
        return 'Error: borrowed until not found!';
    }
    
    function userHasBorrowed($lectureId, $dateUtil, $currentUser) {
        $borrowRecords = $currentUser->getBorrowRecords();
        for ($i = 0; $i < count($borrowRecords); $i++) {
            $record = $borrowRecords[$i];
            $now = $dateUtil->getDateTimeNow();
            $borrowedUntilDate = $record->getBorrowedUntilDate();
            if ($record->getLectureID() == $lectureId && $dateUtil->isSmallerThan($now, $borrowedUntilDate)) {
                return true;
            }
        }
        return false;
    }
    
    $data = array();
    foreach ($allLectures as &$lecture) {
        if (in_array($lecture->getID(), $currentlyBorrowedLectureIds)) {
            $row = array();
            $row[] = $lecture->getLongName();
            $row[] = $lecture->getShortName();
            $row[] = $lecture->getField();
            $row[] = '<nobr>' . getBorrowedUntilFromLectureId($borrowRecords, $lecture->getID(), $dateUtil, $currentUser) . '</nobr>';
            if (userHasBorrowed($lecture->getID(), $dateUtil, $currentUser)) {
                $row[] = '<a id="styledButtonGreen" href="?lecture=' . $lecture->getID() . '"><nobr><img src="static/img/protocolDownload.png" style="height: 24px; vertical-align: middle;">&nbsp;&nbsp;' . $i18n->get('download') . '</nobr></a>';
            } else {
                $row[] = '<a id="styledButtonRed"><nobr><img src="static/img/protocolNotAvailable.png" style="height: 24px; vertical-align: middle;">&nbsp;&nbsp;' . $i18n->get('invalid') . '</nobr></a>';
            }
            $data[] = $row;
        }
    }
    echo $searchableTable->createTable($headers, $data, $widths, $textAlignments);
    
    echo '</div>';

    echo $footer->getFooter();
?>
