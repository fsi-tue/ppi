<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $log->info('upload.php', 'User was not logged in');
        $redirect->redirectTo('login.php');
    }
    
    $status = NULL;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $lectureID1 = filter_input(INPUT_POST, 'lectureSelection1', FILTER_SANITIZE_NUMBER_INT);
        $lectureID2 = filter_input(INPUT_POST, 'lectureSelection2', FILTER_SANITIZE_NUMBER_INT);
        $lectureID3 = filter_input(INPUT_POST, 'lectureSelection3', FILTER_SANITIZE_NUMBER_INT);
        $examiner = filter_input(INPUT_POST, 'examiner', FILTER_SANITIZE_SPECIAL_CHARS);
        $collaborators = filter_input(INPUT_POST, 'collaborators', FILTER_SANITIZE_SPECIAL_CHARS);
        $remark = filter_input(INPUT_POST, 'remark', FILTER_SANITIZE_SPECIAL_CHARS);
        $legal = filter_input(INPUT_POST, 'legal', FILTER_SANITIZE_SPECIAL_CHARS);

        $status = 'CHECK_FILE';
        if ($lectureID1 == '') {
            $status = 'LECTURE_MISSING';
            $log->warning('upload.php', 'Lecture missing for uploaded file');
        } else if ($examiner == '') {
            $status = 'EXAMINER_MISSING';
            $log->warning('upload.php', 'Examiner missing for uploaded file');
        } else if ($legal != 'checkedLegal') {
            $status = 'LEGAL_MISSING';
            $log->warning('upload.php', 'Legal missing for uploaded file');
        }
        
        if ($status == 'CHECK_FILE') {
            $file = $_FILES['protocol_file'];
            $fileName = $file['name'];
            $fileNameExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $fileNameTmp = $file['tmp_name'];
            $fileError = $file['error'];
            $fileSizeBytes = $file['size'];
            $fileType = 'application/' . $fileNameExtension;
            
            if ($fileError == UPLOAD_ERR_OK) {
                if (in_array($fileNameExtension, Constants::ALLOWED_FILE_EXTENSION_UPLOAD)) {
                    $status = 'ACCEPT_FILE';
                    $log->debug('upload.php', 'Accepting uploaded file: ' . $fileNameTmp);
                } else {
                    $status = 'WRONG_EXTENSION';
                    $log->warning('upload.php', 'Uploaded file extension forbidden: ' . $fileNameExtension);
                }
            } else {
                $status = 'NO_FILE_UPLOADED_ERROR';
                $log->warning('upload.php', 'No file uploaded error: ' . $fileError);
            }
        }
        
        if ($status == 'ACCEPT_FILE') {
            $justAddedProtocol = $examProtocolSystem->addProtocol($currentUser, $collaborators, $remark, $examiner, $fileNameTmp, $fileNameExtension, $fileSizeBytes, $fileType);
            if ($justAddedProtocol == NULL) {
                $status = 'ERROR_ON_INSERTING_PROTOCOL';
                $log->error('upload.php', 'Error on inserting exam protocol into DB');
            } else {
                $lectureIDs = array($lectureID1);
                if ($lectureID2 != '' && $lectureID1 != $lectureID2) {
                    $lectureIDs[] = $lectureID2;
                }
                if ($lectureID3 != '' && $lectureID1 != $lectureID3 && $lectureID2 != $lectureID3) {
                    $lectureIDs[] = $lectureID3;
                }
                $result = $lectureSystem->addProtocolIDsToLecture($lectureIDs, $justAddedProtocol->getID());
                if ($result) {
                    $status = 'PROTOCOL_UPLOADED_SUCCESSFULLY';
                    $log->debug('upload.php', 'Inserted protocol successfully');
                } else {
                    $status = 'ERROR_ON_INSERTING_PROTOCOL';
                    $log->error('upload.php', 'Error on inserting exam protocol IDs into lecture on DB');
                }
            }
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $name = filter_input(INPUT_GET, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
        
        if (isset($_GET['name'])) {
            if ($name == '') {
                $status = 'NAME_MISSING';
            } else {
                $justAddedLecture = $lectureSystem->addLecture($name);
                if ($justAddedLecture == NULL) {
                    $status = 'ERROR_ON_INSERTING_LECTURE';
                    $log->error('upload.php', 'Error on inserting lecture into DB');
                } else {
                    $status = 'LECTURE_ADDED_SUCCESSFULLY';
                    $log->debug('upload.php', 'Successfully inserted lecture into DB');
                }
            }
        }
    }

    echo $header->getHeader($i18n->get('title'), $i18n->get('uploadProtocol'), array('upload.css', 'button.css'));
    
    echo $mainMenu->getMainMenu($i18n, $currentUser);

    $allLectures = $lectureSystem->getAllLectures();
    #Sort list of lectures by name
    usort($allLectures, function($a, $b) {
        return strcmp($a->getName(), $b->getName());
    });

    $allLectureOptions = '';
    for ($i = 0; $i < count($allLectures); $i++) {
        $lecture = $allLectures[$i];
        $allLectureOptions .= '<option value="' . $lecture->getID() . '">' . $lecture->getName() . '</option>';
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
    $colorLegal = '';
    if ($status == 'LECTURE_MISSING' || $status == 'ERROR_ON_INSERTING_PROTOCOL') {
        $colorSelectLecture = ' background-color: #A11E3B;';
    }
    if ($status == 'EXAMINER_MISSING' || $status == 'ERROR_ON_INSERTING_PROTOCOL') {
        $colorExaminer = ' background-color: #A11E3B;';
    }
    if ($status == 'WRONG_EXTENSION' || $status == 'NO_FILE_UPLOADED_ERROR' || $status == 'ERROR_ON_INSERTING_PROTOCOL') {
        $colorFile = ' background-color: #A11E3B;';
    }
    if ($status == 'LEGAL_MISSING') {
        $colorLegal = ' background-color: #A11E3B;';
    }
    
    $addLectureFieldOpen = '';
    $colorName = '';
    if ($status == 'NAME_MISSING' || $status == 'ERROR_ON_INSERTING_LECTURE') {
        $addLectureFieldOpen = 'open ';
    }
    if ($status == 'NAME_MISSING' || $status == 'ERROR_ON_INSERTING_LECTURE') {
        $colorName = ' background-color: #A11E3B;';
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
                <div class="info">' . $i18n->get('uploadExplanation') . '
                <br><br>' . $i18n->get('templateRemark'). '</div>
            <a href="static/templates/template.tex" id="styledButton">
                <img src="static/img/protocolDownload.png" alt="download protocol" style="height: 24px; vertical-align: middle;"> Latex
            </a>
            <a href="static/templates/template.docx" id="styledButton">
                <img src="static/img/protocolDownload.png" alt="download protocol" style="height: 24px; vertical-align: middle;"> Word
            </a>
            <a href="static/templates/template.txt" id="styledButton">
                <img src="static/img/protocolDownload.png" alt="download protocol" style="height: 24px; vertical-align: middle;"> Txt
            </a>
            <br><br>
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
                                <td><input type="text" name="examiner" placeholder="' . $i18n->get('lecturersAndAssessors') . '" style="display: table-cell; width: calc(100% - 18px);' . $colorExaminer . '" required></td>
                            </tr>
                            <tr>
                                <td style="width: 20%;">' . $i18n->get('collaborators') . ':</td>
                                <td><input type="text" name="collaborators" placeholder="' . $i18n->get('zxShortsOfPeopleThatHelpedAndAlsoShallGetTokens') . ' (' . $i18n->get('optional') . ')" style="display: table-cell; width: calc(100% - 18px);"></td>
                            </tr>
                            <tr>
                                <td style="width: 20%;">' . $i18n->get('remark') . ':</td>
                                <td><input type="text" name="remark" placeholder="' . $i18n->get('remark') . ' (' . $i18n->get('optional') . ')" style="display: table-cell; width: calc(100% - 18px);"></td>
                            </tr>
                            <tr>
                                <td style="width: 20%;' . $colorFile . '">' . $i18n->get('file') . ':</td>
                                <td><input type="file" id="protocol_file" name="protocol_file" placeholder="" style="display: table-cell; width: calc(100% - 18px);' . $colorFile . '" required accept=".pdf,.txt"></td>
                            </tr>
                            <tr>
                                <td style="width: 20%;' . $colorLegal . '"><input type="checkbox" id="legal" name="legal" value="checkedLegal" required></td>
                                <td><label for="legal">' . $i18n->get('legalDisclaimer'). '</label></td>
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
                                <td style="width: 20%;' . $colorName . '">' . $i18n->get('lectureTitle') . ':</td>
                                <td><input type="text" name="name" placeholder="" style="display: table-cell; width: calc(100% - 18px);' . $colorName . '" required></td>
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
