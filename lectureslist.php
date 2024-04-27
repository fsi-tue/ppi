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
    
    echo '<div style="width: 5%; display: inline-block; text-align: center;">' . $i18n->get('ID') . '</div>';
    echo '<div style="width: 25%; display: inline-block;">' . $i18n->get('lectureTitle') . '</div>';
    echo '<div style="width: 10%; display: inline-block;">' . $i18n->get('status') . '</div>';
    echo '<div style="width: 20%; display: inline-block; text-align: center;">' . $i18n->get('numberOfProtocols') . '</div>';
    echo '<div style="width: 20%; display: inline-block; text-align: center;">' . $i18n->get('viewProtocols') . '</div>';
    echo '<div style="width: 10%; display: inline-block; text-align: center;">' . $i18n->get('markForDeletion') . '</div>';
    echo '<div style="width: 10%; display: inline-block; text-align: center;">' . $i18n->get('save') . '</div>';
        
    $allLectures = $lectureSystem->getAllLectures();
    foreach ($allLectures as &$lecture) {
        echo '<form method="POST" action="lectureslist.php">';
        echo '<div style="width: 5%; display: inline-block; text-align: center;">' . $lecture->getID() . '</div>';
        echo '<div style="width: 25%; display: inline-block;">' . '<input type="text" name="name" value="' . $lecture->getName() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 10%; display: inline-block;">' . '<input type="text" name="status" value="' . $lecture->getStatus() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 20%; display: inline-block; text-align: center;">' . count($lecture->getAssignedExamProtocols()) . '</div>';
        echo '<div style="width: 20%; display: inline-block; text-align: center;">
                    <a href="examprotocolslist.php?lectureID=' . $lecture->getID() . '" class="styledButton">
                        <img src="static/img/viewProtocol.png' . $GLOBALS["VERSION_STRING"] . '" alt="view protocol" style="height: 24px; vertical-align: middle;">
                    </a>
                </div>';
        echo '<div style="width: 10%; display: inline-block; text-align: center;">
                    <a href="?deleteID=' . $lecture->getID() . '" class="styledButtonRed">
                        <img src="static/img/delete.png' . $GLOBALS["VERSION_STRING"] . '" alt="view protocol" style="height: 24px; vertical-align: middle;">
                    </a>
                </div>';
        echo '<div style="width: 10%; display: inline-block; text-align: center;">' . 
                    '<button type="submit" class="styledButton" name="id" value="' . $lecture->getID() . '" style="padding: 3px; width: 40px; height: 40px; vertical-align: middle;">
                        <img src="static/img/save.png' . $GLOBALS["VERSION_STRING"] . '" alt="submit" style="height: 24px;">
                    </button>' .
                '</div>';
        echo '</form>';
    }
    
    echo $footer->getFooter();
?>
