<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $log->info('examprotocolslist.php', 'User was not logged in');
        $redirect->redirectTo('login.php');
    }
    if ($currentUser->getRole() != Constants::USER_ROLES['admin']) {
        $log->error('examprotocolslist.php', 'User was not admin');
        $redirect->redirectTo('lectures.php');
    }
    
    $postStatus = NULL;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $remark = filter_input(INPUT_POST, 'remark', FILTER_SANITIZE_ENCODED);
        $examiner = filter_input(INPUT_POST, 'examiner', FILTER_SANITIZE_ENCODED);
        $examProtocolID = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_ENCODED);
        
        if ($examiner != '' && is_numeric($examProtocolID)) {
            $result = $examProtocolSystem->updateExamProtocol($examProtocolID, $remark, $examiner);
            if ($result) {
                $postStatus = 'UPDATED_EXAM_PROTOCOL_DATA';
                $log->debug('examprotocolslist.php', 'Successfully updated exam protocol: ' . $examProtocolID);
            } else {
                $postStatus = 'ERROR_ON_UPDATING_EXAM_PROTOCOL_DATA';
                $log->error('examprotocolslist.php', 'Updating exam protocol not successfull: ' . $examProtocolID);
            }
        } else {
            $postStatus = 'ERROR_ON_UPDATING_EXAM_PROTOCOL_DATA';
            $log->info('examprotocolslist.php', 'Can not update exam protocol due to invalid data: ' . $examiner . ', ' . $examProtocolID);
        }
    }
    
    $page = 0;
    $lectureID = '';
    $uploadedByUserID = '';
    $borrowedByUserID = '';
    $uploadedByUsername = '';
    $borrowedByUsername = '';
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $pageValue = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_ENCODED);
        $lectureID = filter_input(INPUT_GET, 'lectureID', FILTER_SANITIZE_NUMBER_INT);
        $uploadedByUsername = filter_input(INPUT_GET, 'uploadedByUsername', FILTER_SANITIZE_ENCODED);
        $borrowedByUsername = filter_input(INPUT_GET, 'borrowedByUsername', FILTER_SANITIZE_ENCODED);
        if (is_numeric($pageValue)) {
            $page = intval($pageValue);
        } else {
            if ($pageValue != '') {
                $log->error('examprotocolslist.php', 'Page value is not numeric: ' . $pageValue);
            }
        }
        if ($uploadedByUsername != '') {
            $uploadedByUser = $userSystem->getUserByUsername($uploadedByUsername);
            if ($uploadedByUser != NULL) {
                $uploadedByUserID = $uploadedByUser->getID();
            } else {
                $uploadedByUserID = '0';
                $log->error('examprotocolslist.php', 'Could not find uploaded by user with username ' . $uploadedByUsername);
            }
        }
        if ($borrowedByUsername != '') {
            $borrowedByUser = $userSystem->getUserByUsername($borrowedByUsername);
            if ($borrowedByUser != NULL) {
                $borrowedByUserID = $borrowedByUser->getID();
            } else {
                $borrowedByUserID = '0';
                $log->error('examprotocolslist.php', 'Could not find borrowed by user with username ' . $borrowedByUsername);
            }
        }
        if (isset($_GET['download'])) {
            $examProtocolToDownloadID = filter_input(INPUT_GET, 'download', FILTER_SANITIZE_ENCODED);
            if (is_numeric($examProtocolToDownloadID)) {
                $protocol = $examProtocolSystem->getExamProtocol($examProtocolToDownloadID);
                if ($protocol != NULL) {
                    $fileUtil->downloadFile($fileUtil->getFullPathToBaseDirectory() . Constants::UPLOADED_PROTOCOLS_DIRECTORY . '/' . $protocol->getFileName(), $protocol->getFileType());
                } else {
                    $log->error('examprotocolslist.php', 'Could not find exam protocol with ID: ' . $examProtocolToDownloadID);
                }
            } else {
                $log->error('examprotocolslist.php', 'Got invalid exam protocol ID to download: ' . $examProtocolToDownloadID);
            }
        }
    }
    
    function getLecturesOfProtocolAsString($examProtocolID, $examProtocolSystem, $allLectures) {
        $lectureIDsOfExamProtocol = $examProtocolSystem->getLectureIDsOfExamProtocol($examProtocolID);
        $retVal = '';
        for ($i = 0; $i < count($allLectures); $i++) {
            $lecture = $allLectures[$i];
            if (count($lectureIDsOfExamProtocol) > 0 && $lecture->getID() == $lectureIDsOfExamProtocol[0]) {
                $retVal .= $lecture->getName();
            }
            if (count($lectureIDsOfExamProtocol) > 1 && $lecture->getID() == $lectureIDsOfExamProtocol[1]) {
                $retVal .= ', ' . $lecture->getName();
            }
            if (count($lectureIDsOfExamProtocol) > 2 && $lecture->getID() == $lectureIDsOfExamProtocol[2]) {
                $retVal .= ', ' . $lecture->getName();
            }
        }
        return $retVal;
    }
    
    echo $header->getHeader($i18n->get('title'), $i18n->get('allExamProtocols'), array('protocols.css', 'button.css'));
    
    echo $mainMenu->getMainMenu($i18n, $currentUser);
    
    echo '<center>
                <details open>
                    <summary id="styledButton" style="line-height: 10px; margin: 5px;">' . $i18n->get('filter') . '</summary>
                    <div style="width: 33%; display: inline-block; text-align: right; margin: 0px;">
                        <form action="examprotocolslist.php" method="GET">
                            <input type="text" name="lectureID" value="' . $lectureID . '" placeholder="' . $i18n->get('lectureID') . '">
                            <input type="submit" value="' . $i18n->get('ok') . '">
                        </form>
                    </div>
                    <div style="width: 33%; display: inline-block; text-align: center; margin: 0px;">
                        <form action="examprotocolslist.php" method="GET">
                            <input type="text" name="uploadedByUsername" value="' . $uploadedByUsername . '" placeholder="' . $i18n->get('uploadedByUsername') . '">
                            <input type="submit" value="' . $i18n->get('ok') . '">
                        </form>
                    </div>
                    <div style="width: 33%; display: inline-block; text-align: left; margin: 0px;">
                        <form action="examprotocolslist.php" method="GET">
                            <input type="text" name="borrowedByUsername" value="' . $borrowedByUsername . '" placeholder="' . $i18n->get('borrowedByUsername') . '">
                            <input type="submit" value="' . $i18n->get('ok') . '">
                        </form>
                    </div>
                </details>
            </center>';

    if ($postStatus == 'UPDATED_EXAM_PROTOCOL_DATA') {
        echo '<br><center>' . $i18n->get('updatedExamProtocolDataSuccessfully') . '</center><br>';
    } else if ($postStatus == 'ERROR_ON_UPDATING_EXAM_PROTOCOL_DATA') {
        echo '<br><center>' . $i18n->get('errorOnUpdatingExamProtocolData') . '</center><br>';
    }
    
    $numberOfExamProtocolsTotal = $examProtocolSystem->getNumberOfExamProtocolsTotal($lectureID, $uploadedByUserID, $borrowedByUserID);
    
    echo $pagedContentUtil->getNavigation($page, Constants::NUMBER_OF_ENTRIES_PER_PAGE, $numberOfExamProtocolsTotal);
    
    echo '<br><br>';
    
    echo '<div style="width: 5%; display: inline-block; text-align: center;">' . $i18n->get('ID') . '</div>' .
         '<div style="width: 5%; display: inline-block;">' . $i18n->get('status') . '</div>' .
         '<div style="width: 10%; display: inline-block;">' . $i18n->get('uploadedByUserID') . '</div>' .
         '<div style="width: 10%; display: inline-block;">' . $i18n->get('uploadedDate') . '</div>' .
         '<div style="width: 10%; display: inline-block;">' . $i18n->get('remark') . '</div>' .
         '<div style="width: 10%; display: inline-block;">' . $i18n->get('examiners') . '</div>' .
         '<div style="width: 5%; display: inline-block;">' . $i18n->get('fileName') . '</div>' .
         '<div style="width: 5%; display: inline-block;">' . $i18n->get('fileSize') . '</div>' .
         '<div style="width: 10%; display: inline-block;">' . $i18n->get('fileType') . '</div>' .
         '<div style="width: 10%; display: inline-block;">' . $i18n->get('lecture') . '</div>' .
         '<div style="width: 6%; display: inline-block; text-align: center;">' . $i18n->get('download') . '</div>' .
         '<div style="width: 6%; display: inline-block; text-align: center;">' . $i18n->get('details') . '</div>' .
         '<div style="width: 6%; display: inline-block; text-align: center;">' . $i18n->get('save') . '</div>';
    
    $allProtocols = $examProtocolSystem->getExamProtocols(Constants::NUMBER_OF_ENTRIES_PER_PAGE, $page, $lectureID, $uploadedByUserID, $borrowedByUserID);
    $allLectures = $lectureSystem->getAllLecturesAlphabeticalOrder();
    
    foreach ($allProtocols as &$protocol) {
        $color = '';
        if ($protocol->getStatus() == Constants::EXAM_PROTOCOL_STATUS['unchecked']) {
            $color = 'background-color: yellow; ';
        }
        if ($protocol->getStatus() == Constants::EXAM_PROTOCOL_STATUS['declined']) {
            $color = 'background-color: orange; ';
        }
        if ($protocol->getStatus() == Constants::EXAM_PROTOCOL_STATUS['toBeDeleted']) {
            $color = 'background-color: red; ';
        }
        echo '<form method="POST" action="examprotocolslist.php" style="' . $color . '">' .
             '<div style="width: 5%; display: inline-block; text-align: center;">' . $protocol->getID() . '</div>' .
             '<div style="width: 5%; display: inline-block;">' . '<input type="text" readonly name="status" value="' . $protocol->getStatus() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>' .
             '<div style="width: 10%; display: inline-block;">' . '<input type="text" readonly name="uploadedByUserID" value="' . $protocol->getUploadedByUserID() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>' .
             '<div style="width: 10%; display: inline-block;">' . '<input type="text" readonly name="uploadedDate" value="' . $dateUtil->dateTimeToString($protocol->getUploadedDate()) . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>' .
             '<div style="width: 10%; display: inline-block;">' . '<input type="text" name="remark" value="' . $protocol->getRemark() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>' .
             '<div style="width: 10%; display: inline-block;">' . '<input type="text" name="examiner" value="' . $protocol->getExaminer() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>' .
             '<div style="width: 5%; display: inline-block;">' . '<input type="text" readonly name="fileName" value="' . $protocol->getFileName() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>' .
             '<div style="width: 5%; display: inline-block;">' . '<input type="text" readonly name="fileSize" value="' . $protocol->getFileSize() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>' .
             '<div style="width: 10%; display: inline-block;">' . '<input type="text" readonly name="fileType" value="' . $protocol->getFileType() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>' .
             '<div style="width: 10%; display: inline-block;">' . '<input type="text" readonly name="lectures" value="' . getLecturesOfProtocolAsString($protocol->getID(), $examProtocolSystem, $allLectures) . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>' .
             '<div style="width: 6%; display: inline-block; text-align: center;">
                    <a href="?download=' . $protocol->getID() . '" id="styledButton">
                        <img src="static/img/protocolDownload.png' . $GLOBALS["VERSION_STRING"] . '" alt="download protocol" style="height: 24px; vertical-align: middle;">
                    </a>
                </div>' .
             '<div style="width: 6%; display: inline-block; text-align: center;">
                    <a href="viewexamprotocol.php?id=' . $protocol->getID() . '" id="styledButton">
                        <img src="static/img/viewProtocol.png' . $GLOBALS["VERSION_STRING"] . '" alt="reply to protocol" style="height: 24px; vertical-align: middle;">
                    </a>
                </div>' .
             '<div style="width: 6%; display: inline-block; text-align: center;">' . 
                    '<button type="submit" id="styledButton" name="id" value="' . $protocol->getID() . '" style="padding: 3px; width: 40px; height: 40px; vertical-align: middle;">
                        <img src="static/img/save.png' . $GLOBALS["VERSION_STRING"] . '" alt="submit" style="height: 24px;">
                    </button>' .
                '</div>' .
             '</form>';
    }
    
    echo '<br>';
    
    echo $pagedContentUtil->getNavigation($page, Constants::NUMBER_OF_ENTRIES_PER_PAGE, $numberOfExamProtocolsTotal);
    
    echo $footer->getFooter();
?>
