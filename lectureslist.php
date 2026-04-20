<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $log->info('lectureslist.php', 'User was not logged in');
        $redirect->redirectTo('login.php');
    }
    if ($currentUser->getRole() != Constants::USER_ROLES['admin']) {
        $log->error('lectureslist.php', 'User was not admin');
        $redirect->redirectTo('lectures.php');
    }
    
    $status = NULL;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_ENCODED);
        $lectureID = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_ENCODED);
        
        if ($name != '' && $status != '' && $lectureID != '' && is_numeric($lectureID)) {
            $result = $lectureSystem->updateLecture($lectureID, $name, $status);
            if ($result) {
                $status = 'UPDATED_LECTURE_DATA';
                $log->debug('lectureslist.php', 'Successfully updated lecture data ' . $lectureID);
            } else {
                $status = 'ERROR_ON_UPDATING_LECTURE_DATA';
                $log->error('lectureslist.php', 'Updating lecture data failed ' . $lectureID . ' ' . $name);
            }
        } else {
            $status = 'ERROR_ON_UPDATING_LECTURE_DATA';
            $log->error('lectureslist.php', 'Updating lecture failed due to invalid data');
        }
    }
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $deleteID = filter_input(INPUT_GET, 'deleteID', FILTER_SANITIZE_ENCODED);
        if (isset($_GET['deleteID'])) {
            if (is_numeric($deleteID)) {
                $lecture = $lectureSystem->getLecture($deleteID);
                if ($lecture != null) {
                    $result = $lectureSystem->updateLecture($deleteID, $lecture->getName(), 'TO_BE_DELETED');
                    if (!$result) {
                        $status = 'ERROR_ON_UPDATING_LECTURE_DATA';
                        $log->error('lectureslist.php', 'Lecture could not be marked for deletion with ID: ' . $deleteID);
                    }
                } else {
                    $status = 'ERROR_ON_UPDATING_LECTURE_DATA';
                    $log->error('lectureslist.php', 'Lecture ID to be deleted not found: ' . $deleteID);
                }
            } else {
                $status = 'ERROR_ON_UPDATING_LECTURE_DATA';
                $log->error('lectureslist.php', 'ID of lecture to be deleted is not numeric: ' . $deleteID);
            }
        }
    }
    
    echo $header->getHeader($i18n->get('title'), $i18n->get('allLectures'), array('protocols.css', 'button.css', 'searchableTable.css'));
    
    echo $mainMenu->getMainMenu($i18n, $currentUser);

    if ($status == 'UPDATED_LECTURE_DATA') {
        echo '<br><center>' . $i18n->get('updatedLectureDataSuccessfully') . '</center><br>';
    } else if ($status == 'ERROR_ON_UPDATING_LECTURE_DATA') {
        echo '<br><center>' . $i18n->get('errorOnUpdatingLectureData') . '</center><br>';
    }
    
    echo '<div class="table-container">';
    echo '<div class="table-responsive">';
    echo '<table class="gridtable" width="100%">';
    echo '<tr>';
    echo '<th width="5%">' . $i18n->get('ID') . '</th>';
    echo '<th width="25%">' . $i18n->get('lectureTitle') . '</th>';
    echo '<th width="10%">' . $i18n->get('status') . '</th>';
    echo '<th width="20%">' . $i18n->get('numberOfProtocols') . '</th>';
    echo '<th width="20%">' . $i18n->get('viewProtocols') . '</th>';
    echo '<th width="10%">' . $i18n->get('markForDeletion') . '</th>';
    echo '<th width="10%">' . $i18n->get('save') . '</th>';
    echo '</tr>';
        
    $allLectures = $lectureSystem->getAllLectures();
    foreach ($allLectures as &$lecture) {
        echo '<form method="POST" action="lectureslist.php" style="display: table-row;">';
        echo '<td style="text-align: center;">' . $lecture->getID() . '</td>';
        echo '<td>' . '<input type="text" name="name" value="' . $lecture->getName() . '" style="width: 100%;">' . '</td>';
        echo '<td>' . '<input type="text" name="status" value="' . $lecture->getStatus() . '" style="width: 100%;">' . '</td>';
        echo '<td style="text-align: center;">' . count($lecture->getAssignedExamProtocols()) . '</td>';
        echo '<td style="text-align: center;">
                    <a href="examprotocolslist.php?lectureID=' . $lecture->getID() . '" class="styledButton" style="width: auto; padding: 5px;">
                        <img src="static/img/viewProtocol.png' . $GLOBALS["VERSION_STRING"] . '" alt="view protocol" style="height: 24px; vertical-align: middle;">
                    </a>
                </td>';
        echo '<td style="text-align: center;">
                    <a href="?deleteID=' . $lecture->getID() . '" class="styledButtonRed" style="width: auto; padding: 5px;">
                        <img src="static/img/delete.png' . $GLOBALS["VERSION_STRING"] . '" alt="view protocol" style="height: 24px; vertical-align: middle;">
                    </a>
                </td>';
        echo '<td style="text-align: center;">' . 
                    '<button type="submit" class="styledButton" name="id" value="' . $lecture->getID() . '" style="padding: 3px; width: 40px; height: 40px; vertical-align: middle;">
                        <img src="static/img/save.png' . $GLOBALS["VERSION_STRING"] . '" alt="submit" style="height: 24px;">
                    </button>' .
                '</td>';
        echo '</form>';
    }
    echo '</table>';
    echo '</div>';
    echo '</div>';
    
    echo $footer->getFooter();
?>
