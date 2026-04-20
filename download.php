<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $log->info('download.php', 'User was not logged in');
        $redirect->redirectTo('login.php');
    }

    $reportLectureID = null;

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $reportLectureID = filter_input(INPUT_GET, 'report', FILTER_SANITIZE_ENCODED);
        if (isset($_GET['report'])) {
            if (is_numeric($reportLectureID)) {
                $reportingResult = $lectureSystem->reportLectureHasOutdatedProtocols($reportLectureID, $currentUser->getUsername());
                if ($reportingResult == true) {
                    $log->info('download.php', 'Successfully reported the lecture as outdated.');
                }
            } else {
                $log->error('download.php', 'Lecture ID is not numeric: ' . $reportLectureID);
            }
        }
        if (isset($_GET['lecture'])) {
            $lectureToDownloadID = filter_input(INPUT_GET, 'lecture', FILTER_SANITIZE_ENCODED);
            if (is_numeric($lectureToDownloadID)) {
                if (userHasBorrowed($lectureToDownloadID, $dateUtil, $currentUser)) {
                    $basePath = $fileUtil->getFullPathToBaseDirectory();
                    $protocolFileIDs = $lectureSystem->getAllProtocolIDsOfLecture($lectureToDownloadID);
                    $protocols = $examProtocolSystem->getExamProtocolsFromIDs($protocolFileIDs);

                    # Path for zip file
                    $zipFilePath = $basePath . Constants::TMP_ZIP_FILES_DIRECTORY . '/';
                    # Unsafe Lecture name
		    $lectureName = $lectureSystem->getLecture($lectureToDownloadID)->getName();

                    # Remove all Umlaute from ZIP filenames, also encode brackets as '__' (safer for Windows)
                    # See http://web.archive.org/web/20220105010647/https://www.lima-city.de/thread/umlaute-mit-str_replace-umwandeln, answer first answer from staymyfriend
                    # Decode possible encodings from database
                    $lectureName = html_entity_decode($lectureName);
                    $searchUmlaute = array("Ä", "Ö", "Ü", "ä", "ö", "ü", "ß", ".", "/", "(", ")", "[", "]", " ");
                    $replaceUmlaute = array("Ae", "Oe", "Ue", "ae", "oe", "ue", "ss", "", "_", "__", "__", "__", "__", "_");
                    $lectureName = str_replace($searchUmlaute, $replaceUmlaute, $lectureName);

                    # Complete Path
                    $zipFilePath = $zipFilePath .  $lectureName . "-" . $hashUtil->generateRandomString(8) . '.zip';
                    $watermarkText = 'Downloaded from ppi.fsi.uni-tuebingen.de. Do not redistribute! uid: ' . $currentUser->getID();
                    $escapeForTable = static function ($value) {
                        $sanitized = str_replace(array("\r\n", "\r", "\n"), ' ', strval($value));
                        return str_replace('|', '\\|', $sanitized);
                    };
                    $protocolFileNames = array();
                    $readmeLines = array('# Exam Protocols ' . $lectureName, '', '| File | Uploaded | Examiner |', '| --- | --- | --- |');
                    for ($i = 0; $i < count($protocols); $i++) {
                        $protocol = $protocols[$i];
                        $protocolFileNames[] = $protocol->getFileName();
                        $uploadedDate = $protocol->getUploadedDate();
                        if ($uploadedDate instanceof DateTime) {
                            $uploadedDateString = $dateUtil->dateTimeToStringForDisplaying($uploadedDate, $currentUser->getLanguage());
                        } else {
                            $uploadedDateString = strval($uploadedDate);
                        }
                        $examiner = trim(strval($protocol->getExaminer()));
                        if ($examiner === '') {
                            $examiner = '-';
                        }
                        $readmeLines[] = '| ' . $escapeForTable($protocol->getFileName()) . ' | ' . $escapeForTable($uploadedDateString) . ' | ' . $escapeForTable($examiner) . ' |';
                    }
                    if (count($protocols) === 0) {
                        $readmeLines[] = '| - | - | - |';
                    }
                    $readmeLines[] = '';
                    $readmeLines[] = $watermarkText;
                    $readmeContent = implode("\n", $readmeLines) . "\n";

                    $fileUtil->zipFiles($protocolFileNames, $zipFilePath, $watermarkText, $readmeContent);
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

    echo '<div id="protocolsTable" class="table-container">';

    $headers = array($i18n->get('lectureTitle'), $i18n->get('borrowedUntil'), $i18n->get('download'), $i18n->get('reportAsOutdated'));
    $widths = array(50, 20, 15, 15);
    $textAlignments = array('left', 'left', 'center', 'center');
    
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
            $hasValidBorrow = userHasBorrowed($lecture->getID(), $dateUtil, $currentUser);
            if ($hasValidBorrow) {
                $row[] = '<a class="styledButtonGreen compactActionButton mobileIconOnlyButton" href="?lecture=' . $lecture->getID() . '"><img src="static/img/protocolDownload.png' . $GLOBALS["VERSION_STRING"] . '" style="height: 24px; vertical-align: middle;"><span class="mobileActionText">&nbsp;&nbsp;' . $i18n->get('download') . '</span></a>';
            } else {
                $row[] = '<a class="styledButtonRed"><img src="static/img/protocolNotAvailable.png' . $GLOBALS["VERSION_STRING"] . '" style="height: 24px; vertical-align: middle;">&nbsp;&nbsp;' . $i18n->get('invalid') . '</a>';
            }

            $reportingPossible = $hasValidBorrow;
            if ($reportingPossible && isset($_GET['report']) && is_numeric($reportLectureID) && $lecture->getID() == $reportLectureID) {
                $reportingPossible = false;
            }
            if ($reportingPossible) {
                $row[] = '<a class="styledButtonRed reportActionButton" href="?report=' . $lecture->getID() . '"><img src="static/img/report.png' . $GLOBALS["VERSION_STRING"] . '" style="height: 24px; vertical-align: middle;"></a>';
            } else {
                $row[] = '<a class="styledButtonGray reportActionButton" href=""><img src="static/img/report.png' . $GLOBALS["VERSION_STRING"] . '" style="height: 24px; vertical-align: middle;"></a>';
            }

            $data[] = $row;
        }
    }
    echo $searchableTable->createTable($headers, $data, $widths, $textAlignments);
    
    echo '</div>';

    echo $footer->getFooter();
?>
