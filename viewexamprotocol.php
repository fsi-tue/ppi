<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $log->info('viewexamprotocol.php', 'User was not logged in');
        $redirect->redirectTo('login.php');
    }
    if ($currentUser->getRole() != Constants::USER_ROLES['admin']) {
        $log->error('viewexamprotocol.php', 'User was not admin');
        $redirect->redirectTo('lectures.php');
    }
    
    $replies = array();
    $replies[] = $i18n->get('noReplyToUserThatUploadedExamProtocol');
    for ($i = 0; $i <= 11; $i++) {
        $replies[] = $i18n->get('uploadReply' . $i);
    }
    
    $postStatus = NULL;
    $examProtocolID = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $examProtocolID = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_ENCODED);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_ENCODED);
        $uploadedByUserID = filter_input(INPUT_POST, 'uploadedByUserID', FILTER_SANITIZE_ENCODED);
        $uploadedByUsername = filter_input(INPUT_POST, 'uploadedByUsername', FILTER_SANITIZE_ENCODED);
        $collaboratorIDs = filter_input(INPUT_POST, 'collaboratorIDs', FILTER_SANITIZE_SPECIAL_CHARS);
        $uploadedDateString = filter_input(INPUT_POST, 'uploadedDate', FILTER_SANITIZE_SPECIAL_CHARS);
        $uploadedDate = $dateUtil->stringToDateTime($uploadedDateString);
        $remark = filter_input(INPUT_POST, 'remark', FILTER_SANITIZE_SPECIAL_CHARS);
        $examiner = filter_input(INPUT_POST, 'examiner', FILTER_SANITIZE_SPECIAL_CHARS);
        $fileName = filter_input(INPUT_POST, 'fileName', FILTER_SANITIZE_SPECIAL_CHARS);
        $fileSize = filter_input(INPUT_POST, 'fileSize', FILTER_SANITIZE_ENCODED);
        $fileType = filter_input(INPUT_POST, 'fileType', FILTER_SANITIZE_SPECIAL_CHARS);
        $fileExtension = filter_input(INPUT_POST, 'fileExtension', FILTER_SANITIZE_ENCODED);
        $lectureID1 = filter_input(INPUT_POST, 'lectureSelection1', FILTER_SANITIZE_ENCODED);
        $lectureID2 = filter_input(INPUT_POST, 'lectureSelection2', FILTER_SANITIZE_ENCODED);
        $lectureID3 = filter_input(INPUT_POST, 'lectureSelection3', FILTER_SANITIZE_ENCODED);
        $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_ENCODED);
        $reply = filter_input(INPUT_POST, 'reply', FILTER_SANITIZE_ENCODED);
        
        if ($lectureID1 != '0' && $uploadedByUserID != '' && $uploadedDate != NULL && $examiner != '' && $fileName != '' && $fileSize != '' && $fileType != '' && $fileExtension != '' && is_numeric($examProtocolID)) {
            $tokensToAdd = Constants::TOKENS_ADDED_PER_UPLOAD;
            if ($action == 'accept') {
                $status = Constants::EXAM_PROTOCOL_STATUS['accepted'];
            } else if ($action == 'acceptDoubleTokens') {
                $status = Constants::EXAM_PROTOCOL_STATUS['accepted'];
                $tokensToAdd = $tokensToAdd * 2;
            } else if ($action == 'decline') {
                $status = Constants::EXAM_PROTOCOL_STATUS['declined'];
                $tokensToAdd = 0;
            } else if ($action == 'declineButGrantTokens') {
                $status = Constants::EXAM_PROTOCOL_STATUS['declined'];
            } else if ($action == 'declineAndDeleteProtocol') {
                $status = Constants::EXAM_PROTOCOL_STATUS['toBeDeleted'];
                $tokensToAdd = 0;
            } else if ($action == 'deleteButGrantTokens') {
                $status = Constants::EXAM_PROTOCOL_STATUS['toBeDeleted'];
            } else if ($action == 'noChange') {
                $examProtocol = $examProtocolSystem->getExamProtocol($examProtocolID);
                if ($examProtocolSystem != NULL) {
                    $status = $examProtocol->getStatus();
                } else {
                    $log->error('viewexamprotocol.php', 'Could not find exam protocol with ID ' . $examProtocolID);
                }
            }
            
            $result = $examProtocolSystem->updateExamProtocolFully($examProtocolID, $collaboratorIDs, $status, $uploadedByUserID, $uploadedDate, $remark, $examiner, $fileName, $fileSize, $fileType, $fileExtension);
            if ($result) {
                $lectureIDs = array($lectureID1);
                if ($lectureID2 != '0' && $lectureID1 != $lectureID2) {
                    $lectureIDs[] = $lectureID2;
                }
                if ($lectureID3 != '0' && $lectureID1 != $lectureID3 && $lectureID2 != $lectureID3) {
                    $lectureIDs[] = $lectureID3;
                }
                if ($lectureID1 != '0') {
                    $result = $examProtocolSystem->deleteAllProtocolAssignments($examProtocolID);
                    if ($result) {
                        $result = $lectureSystem->addProtocolIDsToLecture($lectureIDs, $examProtocolID);
                        if ($result) {
                            $replyToSend = $replies[intval($reply)];
                            if (intval($reply) == 0) {
                                $replyToSend = '';
                            }
                            $result = $userSystem->grantTokensAndMailToUploader($uploadedByUsername, $tokensToAdd, $replyToSend);
                            if ($result) {
                                $postStatus = 'UPDATED_EXAM_PROTOCOL_DATA';
                                $log->debug('viewexamprotocol.php', 'Updated exam protocol data with ID ' . $examProtocolID);
                            } else {
                                $postStatus = 'ERROR_ON_REPLYING_TO_UPLOADER';
                                $log->error('viewexamprotocol.php', 'Error on replying to uploader');
                            }
                        } else {
                            $postStatus = 'ERROR_ON_INSERTING_PROTOCOL_IDS_TO_LECTURE';
                            $log->error('viewexamprotocol.php', 'Error on inserting protocol ids to lecture');
                        }
                    } else {
                        $postStatus = 'ERROR_ON_DELETING_PROTOCOL_ASSIGNMENTS';
                        $log->error('viewexamprotocol.php', 'Error on deleting protocol assignments');
                    }
                } else {
                    $postStatus = 'LECTURE_ONE_MUST_BE_SELECTED';
                    $log->info('viewexamprotocol.php', 'One lecture must be selected');
                }
            } else {
                $postStatus = 'ERROR_ON_UPDATING_EXAM_PROTOCOL_DATA';
                $log->error('viewexamprotocol.php', 'Error on updating exam protocol data: internal server error');
            }
        } else {
            $postStatus = 'ERROR_ON_UPDATING_EXAM_PROTOCOL_DATA';
            $log->error('viewexamprotocol.php', 'Error on updating exam protocol data: invalid data');
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $examProtocolIDValue = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_ENCODED);
        if (is_numeric($examProtocolIDValue)) {
            $examProtocolID = $examProtocolIDValue;
        } else {
            $log->error('viewexamprotocol.php', 'Exam protocol ID value is not numeric: ' . $examProtocolIDValue);
        }
    }
    
    echo $header->getHeader($i18n->get('title'), $i18n->get('examProtocol'), array('protocols.css', 'upload.css', 'button.css'));
    
    echo $mainMenu->getMainMenu($i18n, $currentUser);
    
    echo '<div style="width: 99%; display: inline-block; margin: 20px 0px 20px 0px; text-align: center;">
                <a id="styledButton" onclick="history.go(-1)" style="margin: 0px;">
                    <span style="font-size: 20px">&laquo;</span>&nbsp;' .  $i18n->get('back') . '
                </a>
            </div>';

    if ($postStatus == 'UPDATED_EXAM_PROTOCOL_DATA') {
        echo '<br><center>' . $i18n->get('updatedExamProtocolDataSuccessfully') . '</center><br>';
    } else if ($postStatus == 'ERROR_ON_REPLYING_TO_UPLOADER') {
        echo '<br><center>' . $i18n->get('errorOnReplyingToUploader') . '</center><br>';
    } else if ($postStatus == 'ERROR_ON_DELETING_PROTOCOL_ASSIGNMENTS') {
        echo '<br><center>' . $i18n->get('errorOnDeletingProtocolAssignments') . '</center><br>';
    } else if ($postStatus == 'ERROR_ON_INSERTING_PROTOCOL_IDS_TO_LECTURE') {
        echo '<br><center>' . $i18n->get('errorOnInsertingProtocolIDsToLecture') . '</center><br>';
    } else if ($postStatus == 'LECTURE_ONE_MUST_BE_SELECTED') {
        echo '<br><center>' . $i18n->get('lectureOneMustBeSelected') . '</center><br>';
    } else if ($postStatus == 'ERROR_ON_UPDATING_EXAM_PROTOCOL_DATA') {
        echo '<br><center>' . $i18n->get('errorOnUpdatingExamProtocolData') . '</center><br>';
    }
    
    $examProtocol = $examProtocolSystem->getExamProtocol($examProtocolID);
    if ($examProtocol != NULL) {
        $uploadedByUser = $userSystem->getUser($examProtocol->getUploadedByUserID());
        $uploadedByUsername = 'error, username to that ID not found';
        if ($uploadedByUser != NULL) {
            $uploadedByUsername = $uploadedByUser->getUsername();
        } else {
            $log->error('viewexamprotocol.php', 'Username to ID not found: ' . $examProtocol->getUploadedByUserID());
        }
        
        $color = '';
        if ($examProtocol->getStatus() == Constants::EXAM_PROTOCOL_STATUS['unchecked']) {
            $color = 'background-color: yellow; ';
        } else if ($examProtocol->getStatus() == Constants::EXAM_PROTOCOL_STATUS['declined']) {
            $color = 'background-color: orange; ';
        } else if ($examProtocol->getStatus() == Constants::EXAM_PROTOCOL_STATUS['toBeDeleted']) {
            $color = 'background-color: red; ';
        }
        
        $lectureIDsOfExamProtocol = $examProtocolSystem->getLectureIDsOfExamProtocol($examProtocolID);
        $allLectures = $lectureSystem->getAllLecturesAlphabeticalOrder();
        $allLectureOptions1 = '';
        $allLectureOptions2 = '';
        $allLectureOptions3 = '';
        for ($i = 0; $i < count($allLectures); $i++) {
            $lecture = $allLectures[$i];
            $selected1 = '';
            $selected2 = '';
            $selected3 = '';
            if (count($lectureIDsOfExamProtocol) > 0 && $lecture->getID() == $lectureIDsOfExamProtocol[0]) {
                $selected1 = 'selected ';
            }
            if (count($lectureIDsOfExamProtocol) > 1 && $lecture->getID() == $lectureIDsOfExamProtocol[1]) {
                $selected2 = 'selected ';
            }
            if (count($lectureIDsOfExamProtocol) > 2 && $lecture->getID() == $lectureIDsOfExamProtocol[2]) {
                $selected2 = 'selected ';
            }
            $allLectureOptions1 .= '<option ' . $selected1 . 'value="' . $lecture->getID() . '">' . $lecture->getName() . '</option>';
            $allLectureOptions2 .= '<option ' . $selected2 . 'value="' . $lecture->getID() . '">' . $lecture->getName() . '</option>';
            $allLectureOptions3 .= '<option ' . $selected3 . 'value="' . $lecture->getID() . '">' . $lecture->getName() . '</option>';
        }
        
        echo '<form method="POST" action="viewexamprotocol.php?id=' . $examProtocol->getID() . '">
                    <div style="margin-left: 20%; width: 60%; padding-top: 30px;">
                        <div id="uploadField">
                            <div style="width: 20%; display: inline-block;">' . $i18n->get('download') . '</div>
                            <div style="width: 79%; display: inline-block; margin: 0px 0px 40px 0px;">

                                <a href="examprotocolslist.php?download=' . $examProtocol->getID() . '" id="styledButton" style="margin: 0px;">
                                    <img src="static/img/protocolDownload.png" alt="download protocol" style="height: 24px; vertical-align: middle;">&nbsp;&nbsp;' . $i18n->get('downloadExamProtocol') . '
                                </a>
                                <a href="examprotocolslist.php?lectureID=' . $lectureIDsOfExamProtocol[0] . '" id="styledButton" style="margin: 0 0 0 18px;">
                                    <img src="static/img/run.png" alt="Show lecture" style="height: 24px; vertical-align: middle;">&nbsp;&nbsp;' . $i18n->get('showLecture') . '
                                </a>

                            </div>
                            
                            <div style="width: 20%; display: inline-block;">' . $i18n->get('ID') . '</div>
                            <div style="width: 79%; display: inline-block; padding-bottom: 10px;">' . '<input readonly type="text" name="id" value="' . $examProtocol->getID() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>
                            
                            <div style="width: 20%; ' . $color . 'display: inline-block;">' . $i18n->get('status') . '</div>
                            <div style="width: 79%; display: inline-block; padding-bottom: 10px;">' . '<input readonly type="text" name="status" value="' . $examProtocol->getStatus() . '" style="' . $color . 'display: table-cell; width: calc(100% - 18px);">' . '</div>
                            
                            <div style="width: 20%; display: inline-block;">' . $i18n->get('uploadedByUser') . '</div>
                            <div style="width: 40%; display: inline-block; padding-bottom: 10px;">' . '<input type="text" name="uploadedByUserID" value="' . $examProtocol->getUploadedByUserID() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>
                            <div style="width: 38%; display: inline-block; padding-bottom: 10px;">' . '<input type="text" name="uploadedByUsername" value="' . $uploadedByUsername . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>
                            
                            <div style="width: 20%; display: inline-block;">' . $i18n->get('collaborators') . '</div>
                            <div style="width: 79%; display: inline-block; padding-bottom: 10px;">' . '<input type="text" name="collaboratorIDs" value="' . $examProtocol->getCollaboratorIDs() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>
                            
                            <div style="width: 20%; display: inline-block;">' . $i18n->get('uploadedDate') . '</div>
                            <div style="width: 79%; display: inline-block; padding-bottom: 10px;">' . '<input type="text" name="uploadedDate" value="' . $dateUtil->dateTimeToString($examProtocol->getUploadedDate()) . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>
                            
                            <div style="width: 20%; display: inline-block;">' . $i18n->get('remark') . '</div>
                            <div style="width: 79%; display: inline-block; padding-bottom: 10px;">' . '<input type="text" name="remark" value="' . $examProtocol->getRemark() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>
                            
                            <div style="width: 20%; display: inline-block;">' . $i18n->get('examiners') . '</div>
                            <div style="width: 79%; display: inline-block; padding-bottom: 10px;">' . '<input type="text" name="examiner" value="' . $examProtocol->getExaminer() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>
                            
                            <div style="width: 20%; display: inline-block;">' . $i18n->get('fileName') . '</div>
                            <div style="width: 79%; display: inline-block; padding-bottom: 10px;">' . '<input type="text" name="fileName" value="' . $examProtocol->getFileName() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>
                            
                            <div style="width: 20%; display: inline-block;">' . $i18n->get('fileSize') . '</div>
                            <div style="width: 79%; display: inline-block; padding-bottom: 10px;">' . '<input type="text" name="fileSize" value="' . $examProtocol->getFileSize() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>
                            
                            <div style="width: 20%; display: inline-block;">' . $i18n->get('fileType') . '</div>
                            <div style="width: 79%; display: inline-block; padding-bottom: 10px;">' . '<input type="text" name="fileType" value="' . $examProtocol->getFileType() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>
                            
                            <div style="width: 20%; display: inline-block;">' . $i18n->get('fileExtension') . '</div>
                            <div style="width: 79%; display: inline-block; padding-bottom: 10px;">' . '<input type="text" name="fileExtension" value="' . $examProtocol->getFileExtension() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>
                            
                            <div style="width: 20%; display: inline-block;">' . $i18n->get('assignedToLectures') . '</div>
                            <div style="width: 79%; display: inline-block; padding-bottom: 10px;">
                                <select name="lectureSelection1" style="width: 100%;">
                                    <option value="0">'  . $i18n->get('noneChoosen') . '</option>
                                    ' . $allLectureOptions1 . '
                                </select>
                            </div>
                            <div style="width: 20%; display: inline-block;"></div>
                            <div style="width: 79%; display: inline-block; padding-bottom: 10px;">
                                <select name="lectureSelection2" style="width: 100%;">
                                    <option value="0">'  . $i18n->get('noneChoosen') . '</option>
                                    ' . $allLectureOptions2 . '
                                </select>
                            </div>
                            <div style="width: 20%; display: inline-block;"></div>
                            <div style="width: 79%; display: inline-block; padding-bottom: 50px;">
                                <select name="lectureSelection3" style="width: 100%;">
                                    <option value="0">'  . $i18n->get('noneChoosen') . '</option>
                                    ' . $allLectureOptions3 . '
                                </select>
                            </div>
                            
                            <div style="width: 20%; display: inline-block; vertical-align: top;">' . $i18n->get('action') . '</div>
                            <div style="width: 30%; display: inline-block; padding-bottom: 10px; vertical-align: top;">
                                <input checked type="radio" id="accept" name="action" value="accept" style="margin: 0px 0px 10px 0px; padding: 0px;">
                                <label for="accept">' . $i18n->get('acceptAndGrantTokens') . '</label><br>
                                <input type="radio" id="decline" name="action" value="decline" style="margin: 0px 0px 10px 0px; padding: 0px;">
                                <label for="decline">' . $i18n->get('decline') . '</label><br>
                                <input type="radio" id="acceptDoubleTokens" name="action" value="acceptDoubleTokens" style="margin: 0px 0px 10px 0px; padding: 0px;">
                                <label for="acceptDoubleTokens">' . $i18n->get('acceptAndGrantTokensDouble') . '</label><br>
                                <input type="radio" id="deleteButGrantTokens" name="action" value="deleteButGrantTokens" style="margin: 0px; padding: 0px;">
                                <label for="deleteButGrantTokens">' . $i18n->get('deleteButGrantTokens') . '</label>
                            </div>
                            <div style="width: 30%; display: inline-block; padding-bottom: 10px; vertical-align: top;">
                                <input type="radio" id="declineButGrantTokens" name="action" value="declineButGrantTokens" style="margin: 0px 0px 10px 0px; padding: 0px;">
                                <label for="declineButGrantTokens">' . $i18n->get('declineButGrantTokens') . '</label><br>
                                <input type="radio" id="declineAndDeleteProtocol" name="action" value="declineAndDeleteProtocol" style="margin: 0px 0px 10px 0px; padding: 0px;">
                                <label for="declineAndDeleteProtocol">' . $i18n->get('declineAndDeleteProtocol') . '</label><br>
                                <input type="radio" id="noChange" name="action" value="noChange" style="margin: 0px; padding: 0px;">
                                <label for="noChange">' . $i18n->get('noChange') . '</label>
                            </div>
                            <div style="width: 20%; display: inline-block;">' . $i18n->get('reply') . '</div>
                            <div style="width: 79%; display: inline-block; padding-bottom: 50px;">
                                <select name="reply" style="width: 100%;">
                                    ' . insertReplyOptions($replies) . '
                                </select>
                            </div>
                            
                            <div style="width: 100%; display: inline-block; text-align: center;">
                                <button type="submit" id="styledButton" name="id" value="' . $examProtocol->getID() . '" style="margin: 0px 0px 10px 0px; padding: 3px;">
                                    <img src="static/img/save.png" alt="submit" style="height: 24px; vertical-align: middle;">&nbsp;&nbsp;' . $i18n->get('saveChanges') . '
                                </button>
                            </div>
                        </div>
                    </div>
                </form>';
    } else {
        echo '<br><center>' . $i18n->get('examProtocolNotFound') . '</center><br>';
        $log->error('viewexamprotocol.php', 'Could not display exam protocol with ID ' . $examProtocolID);
    }
    
    echo $footer->getFooter();
    
    function insertReplyOptions($replies) {
        $retStr = '';
        for ($i = 0; $i < count($replies); $i++) {
            $retStr .= '<option value="' . $i . '">'  . $replies[$i] . '</option>';
        }
        return $retStr;
    }
?>
