<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $redirect->redirectTo('login.php');
    }
    
    $status = NULL;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $lectureID1 = filter_input(INPUT_POST, 'lectureSelection1', FILTER_SANITIZE_NUMBER_INT);
        $lectureID2 = filter_input(INPUT_POST, 'lectureSelection2', FILTER_SANITIZE_NUMBER_INT);
        $lectureID3 = filter_input(INPUT_POST, 'lectureSelection3', FILTER_SANITIZE_NUMBER_INT);
        $examiner = filter_input(INPUT_POST, 'examiner', FILTER_SANITIZE_SPECIAL_CHARS);
        $remark = filter_input(INPUT_POST, 'remark', FILTER_SANITIZE_SPECIAL_CHARS);
        
        $status = 'CHECK_FILE';
        if ($lectureID1 == '') {
            $status = 'LECTURE_MISSING';
        } else if ($examiner == '') {
            $status = 'EXAMINER_MISSING';
        }
        
        if ($status == 'CHECK_FILE') {
            $file = $_FILES['protocol_file'];
            $fileName = $file['name'];
            $fileNameExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $fileNameTmp = $file['tmp_name'];
            $fileError = $file['error'];
            $fileSizeBytes = $file['size'];
            $fileType = $file['type'];
            
            if ($fileError == UPLOAD_ERR_OK) {
                if (in_array($fileNameExtension, Constants::ALLOWED_FILE_EXTENSION_UPLOAD)) {
                    $status = 'ACCEPT_FILE';
                } else {
                    $status = 'WRONG_EXTENSION';
                }
            } else {
                $status = 'NO_FILE_UPLOADED_ERROR';
            }
        }
        
        if ($status == 'ACCEPT_FILE') {
            $justAddedProtocol = $examProtocolSystem->addProtocol($currentUser, $remark, $examiner, $fileNameTmp, $fileNameExtension, $fileSizeBytes, $fileType);
            if ($justAddedProtocol == NULL) {
                $status = 'ERROR_ON_INSERTING_PROTOCOL';
            } else {
                $lectureIDs = array($lectureID1);
                if ($lectureID2 != '') {
                    $lectureIDs[] = $lectureID2;
                }
                if ($lectureID3 != '') {
                    $lectureIDs[] = $lectureID3;
                }
                $result = $lectureSystem->addProtocolIDsToLecture($lectureIDs, $justAddedProtocol->getID());
                if ($result) {
                    $status = 'PROTOCOL_UPLOADED_SUCCESSFULLY';
                } else {
                    $status = 'ERROR_ON_INSERTING_PROTOCOL';
                }
            }
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $longName = filter_input(INPUT_GET, 'long_name', FILTER_SANITIZE_SPECIAL_CHARS);
        $shortName = filter_input(INPUT_GET, 'short_name', FILTER_SANITIZE_SPECIAL_CHARS);
        $field = filter_input(INPUT_GET, 'field', FILTER_SANITIZE_SPECIAL_CHARS);
        
        if (isset($_GET['long_name']) || isset($_GET['short_name']) || isset($_GET['field'])) {
            $status = 'NEW_LECTURE_CREATION';
        }
        
        if ($status == 'NEW_LECTURE_CREATION') {
            if ($longName == '') {
                $status = 'LONG_NAME_MISSING';
            } else if ($shortName == '') {
                $status = 'SHORT_NAME_MISSING';
            } else if ($field == '') {
                $status = 'FIELD_MISSING';
            } else {
                $justAddedLecture = $lectureSystem->addLecture($longName, $shortName, $field);
                if ($justAddedLecture == NULL) {
                    $status = 'ERROR_ON_INSERTING_LECTURE';
                } else {
                    $status = 'LECTURE_ADDED_SUCCESSFULLY';
                }
            }
        }
    }

    echo $header->getHeader($i18n->get('title'), $i18n->get('uploadProtocol'), array('upload.css', 'button.css'));
    
    echo $mainMenu->getMainMenu($i18n, $currentUser);

    $allLectures = $lectureSystem->getAllLectures();
    $allLectureOptions = '';
    for ($i = 0; $i < count($allLectures); $i++) {
        $lecture = $allLectures[$i];
        $allLectureOptions .= '<option value="' . $lecture->getID() . '">' . $lecture->getLongName() . ' (' . $lecture->getShortName() . '), ' . $lecture->getField() . '</option>';
    }
    
    echo '<script type="text/javascript">
                function checkUploadFileSize() {
                    var input = document.getElementById("protocol_file");
                    if(input.files && input.files.length == 1) {           
                        if (input.files[0].size > ' . Constants::MAX_UPLOAD_FILE_SIZE_BYTES . ') {
                            alert("' . $i18n->get('uploadWillFailAsMaximumFileSizeIs') . ' " + (' . Constants::MAX_UPLOAD_FILE_SIZE_BYTES . '/1024/1024) + "MB.");
                            return false;
                        }
                    }
                    return true;
                }
            </script>';
    
    $colorSelectLecture = '';
    $colorExaminer = '';
    $colorFile = '';
    if ($status == 'LECTURE_MISSING' || $status == 'ERROR_ON_INSERTING_PROTOCOL') {
        $colorSelectLecture = ' background-color: #A11E3B;';
    }
    if ($status == 'EXAMINER_MISSING' || $status == 'ERROR_ON_INSERTING_PROTOCOL') {
        $colorExaminer = ' background-color: #A11E3B;';
    }
    if ($status == 'WRONG_EXTENSION' || $status == 'NO_FILE_UPLOADED_ERROR' || $status == 'ERROR_ON_INSERTING_PROTOCOL') {
        $colorFile = ' background-color: #A11E3B;';
    }
    
    $addLectureFieldOpen = '';
    $colorLongName = '';
    $colorShortName = '';
    $colorField = '';
    if ($status == 'LONG_NAME_MISSING' || $status == 'SHORT_NAME_MISSING' || $status == 'FIELD_MISSING' || $status == 'ERROR_ON_INSERTING_LECTURE') {
        $addLectureFieldOpen = 'open ';
    }
    if ($status == 'LONG_NAME_MISSING' || $status == 'ERROR_ON_INSERTING_LECTURE') {
        $colorLongName = ' background-color: #A11E3B;';
    }
    if ($status == 'SHORT_NAME_MISSING' || $status == 'ERROR_ON_INSERTING_LECTURE') {
        $colorShortName = ' background-color: #A11E3B;';
    }
    if ($status == 'FIELD_MISSING' || $status == 'ERROR_ON_INSERTING_LECTURE') {
        $colorField = ' background-color: #A11E3B;';
    }
    
    $lectureAddedSuccessfullyMessage = '';
    if ($status == 'LECTURE_ADDED_SUCCESSFULLY') {
        $lectureAddedSuccessfullyMessage = '<p>' . $i18n->get('lectureAddedSuccessfullyMessage') . '</p>';
    }
    
    if ($status == 'PROTOCOL_UPLOADED_SUCCESSFULLY') {
        echo '<center><br><a id="styledButton" href="lectures.php"><span style="font-size: 20px">&laquo;</span>&nbsp;' .  $i18n->get('back') . '</a><br><br>';
        echo '<p>' . $i18n->get('uploadedSuccessfullyMessage') . '</p>
              <p>' . $i18n->get('tokensWillBeGrantedAfterReviewOfProtocol') . '</p>';
    } else {
        echo '<div style="margin-left: 20%; width: 60%;">
                ' . $lectureAddedSuccessfullyMessage . '
                <p>' . $i18n->get('uploadExplanation') . '</p>
                <details open id="uploadField">
                    <summary>'  . $i18n->get('selectLecture') . '</summary>
                    <br>
                    <form enctype="multipart/form-data" action="upload.php" method="POST" onsubmit="checkUploadFileSize();">
                        <input type="hidden" name="MAX_FILE_SIZE" value="' . Constants::MAX_UPLOAD_FILE_SIZE_BYTES . '" />
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 20%;' . $colorSelectLecture . '">' . $i18n->get('lecture') . ' 1:</td>
                                <td>
                                    <select name="lectureSelection1" style="width: 100%;' . $colorSelectLecture . '" required>
                                        <option value="">'  . $i18n->get('pleaseSelect') . '</option>
                                        ' . $allLectureOptions . '
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 20%;">' . $i18n->get('lecture') . ' 2:</td>
                                <td>
                                    <select name="lectureSelection2" style="width: 100%;">
                                        <option value="">'  . $i18n->get('notMandatory') . '</option>
                                        ' . $allLectureOptions . '
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 20%;">' . $i18n->get('lecture') . ' 3:</td>
                                <td>
                                    <select name="lectureSelection3" style="width: 100%;">
                                        <option value="">'  . $i18n->get('notMandatory') . '</option>
                                        ' . $allLectureOptions . '
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 20%;' . $colorExaminer . '">' . $i18n->get('examiners') . ':</td>
                                <td><input type="text" name="examiner" placeholder="" style="display: table-cell; width: calc(100% - 18px);' . $colorExaminer . '" required></td>
                            </tr>
                            <tr>
                                <td style="width: 20%;">' . $i18n->get('remark') . ' (' . $i18n->get('optional') . '):</td>
                                <td><input type="text" name="remark" placeholder="" style="display: table-cell; width: calc(100% - 18px);"></td>
                            </tr>
                            <tr>
                                <td style="width: 20%;' . $colorFile . '">' . $i18n->get('file') . ':</td>
                                <td><input type="file" id="protocol_file" name="protocol_file" placeholder="" style="display: table-cell; width: calc(100% - 18px);' . $colorFile . '" required></td>
                            </tr>
                        </table>
                        <br>
                        <input type="submit" value="' . $i18n->get('submit') . '">
                    </form>
                </details>
                <details ' . $addLectureFieldOpen . 'id="uploadField">
                    <summary>' . $i18n->get('createNewLecture') . '</summary>
                    <br>
                    ' . $i18n->get('createNewLectureExplanation') . '
                    <br>
                    <br>
                    <form action="upload.php" method="GET">
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 20%;' . $colorLongName . '">' . $i18n->get('longName') . ':</td>
                                <td><input type="text" name="long_name" placeholder="" style="display: table-cell; width: calc(100% - 18px);' . $colorLongName . '" required></td>
                            </tr>
                            <tr>
                                <td style="width: 20%;' . $colorShortName . '">' . $i18n->get('shortName') . ':</td>
                                <td><input type="text" name="short_name" placeholder="" style="display: table-cell; width: calc(100% - 18px);' . $colorShortName . '" required></td>
                            </tr>
                            <tr>
                                <td style="width: 20%;' . $colorField . '">' . $i18n->get('field') . ':</td>
                                <td><input type="text" name="field" placeholder="" style="display: table-cell; width: calc(100% - 18px);' . $colorField . '" required></td>
                            </tr>
                        </table>
                        <br>
                        <input type="submit" value="' . $i18n->get('submit') . '">
                    </form>
                </details>
            </div>';
    }

    echo $footer->getFooter();
?>
