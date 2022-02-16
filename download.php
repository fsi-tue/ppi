<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $log->info('download.php', 'User was not logged in');
        $redirect->redirectTo('login.php');
    }

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        if (isset($_GET['lecture'])) {
            $lectureToDownloadID = filter_input(INPUT_GET, 'lecture', FILTER_SANITIZE_ENCODED);
            if (is_numeric($lectureToDownloadID)) {
                if (userHasBorrowed($lectureToDownloadID, $dateUtil, $currentUser)) {
                    $basePath = $fileUtil->getFullPathToBaseDirectory();
                    $protocolFileIDs = $lectureSystem->getAllProtocolIDsOfLecture($lectureToDownloadID);
                    $protocolFileNames = $examProtocolSystem->getFileNamesFromProtocolIDs($protocolFileIDs);

                    # Path for zip file
                    $zipFilePath = $basePath . Constants::TMP_ZIP_FILES_DIRECTORY . '/';
                    # Unsafe Lecture name
		    $lectureName = $lectureSystem->getLecture($lectureToDownloadID)->getName();

                    # Remove all Umlaute from ZIP filenames, also encode brackets as '__' (safer for Windows)
                    # See http://web.archive.org/web/20220105010647/https://www.lima-city.de/thread/umlaute-mit-str_replace-umwandeln, answer first answer from staymyfriend
                    # Decode possible encodings from database
                    $lectureName = html_entity_decode($lectureName);
                    $searchUmlaute = array("Ä", "Ö", "Ü", "ä", "ö", "ü", "ß", ".", "(", ")", "[", "]", " ");
                    $replaceUmlaute = array("Ae", "Oe", "Ue", "ae", "oe", "ue", "ss", "", "__", "__", "__", "__", "_");
                    $lectureName = str_replace($searchUmlaute, $replaceUmlaute, $lectureName);

                    # Complete Path
                    $zipFilePath = $zipFilePath .  $lectureName . "-" . $hashUtil->generateRandomString(8) . '.zip';
                    $fileUtil->zipFiles($protocolFileNames, $zipFilePath);
                    $fileUtil->downloadZipFile($zipFilePath);
                } else {
                    $log->warning('download.php', 'User tried to download protocols of a lecture that he or she has not borrowed: ' . $lectureToDownloadID);
                }
            } else {
                $log->error('download.php', 'Got invalid lecture ID to download (not numeric): ' . $lectureToDownloadID);
            }
        }
    }

    echo $header->getHeader($i18n->get('title'), $i18n->get('downloadProtocols'), array('protocols.css', 'button.css', 'searchableTable.css'));
    
    echo $mainMenu->getMainMenu($i18n, $currentUser);

    echo '<div id="protocolsTable" style="padding-left: 40px; padding-bottom: 40px; padding-right: 40px; margin: 0px;">';

    $headers = array($i18n->get('lectureTitle'), $i18n->get('borrowedUntil'), $i18n->get('download'));
    $widths = array(60, 20, 20);
    $textAlignments = array('left', 'left', 'center');
    
    $allLectures = $lectureSystem->getAllLectures();
    $borrowRecords = $currentUser->getBorrowRecords();
    
    $currentlyBorrowedLectureIds = array();
    foreach ($borrowRecords as &$record) {
        $currentlyBorrowedLectureIds[] = $record->getLectureID();
    }
    
    function getBorrowedUntilFromLectureId($borrowRecords, $lectureId, $dateUtil, $currentUser, $log) {
        foreach ($borrowRecords as &$record) {
            if ($record->getLectureID() == $lectureId) {
                return $dateUtil->dateTimeToStringForDisplaying($record->getBorrowedUntilDate(), $currentUser->getLanguage());
            }
        }
        $log->error('download.php', 'Error: borrowed until not found! Lecture ID: ' . $lectureId);
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
            $row[] = $lecture->getName();
            $row[] = getBorrowedUntilFromLectureId($borrowRecords, $lecture->getID(), $dateUtil, $currentUser, $log);
            if (userHasBorrowed($lecture->getID(), $dateUtil, $currentUser)) {
                $row[] = '<a id="styledButtonGreen" href="?lecture=' . $lecture->getID() . '"><img src="static/img/protocolDownload.png" style="height: 24px; vertical-align: middle;">&nbsp;&nbsp;' . $i18n->get('download') . '</a>';
            } else {
                $row[] = '<a id="styledButtonRed"><img src="static/img/protocolNotAvailable.png" style="height: 24px; vertical-align: middle;">&nbsp;&nbsp;' . $i18n->get('invalid') . '</a>';
            }
            $data[] = $row;
        }
    }
    echo $searchableTable->createTable($headers, $data, $widths, $textAlignments);
    
    echo '</div>';

    echo $footer->getFooter();
?>
