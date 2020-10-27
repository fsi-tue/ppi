<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $redirect->redirectTo('login.php');
    }
    if ($currentUser->getRole() != Constants::USER_ROLES['admin']) {
        $redirect->redirectTo('lectures.php');
    }
    
    $status = NULL;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $longName = filter_input(INPUT_POST, 'longName', FILTER_SANITIZE_ENCODED);
        $shortName = filter_input(INPUT_POST, 'shortName', FILTER_SANITIZE_ENCODED);
        $field = filter_input(INPUT_POST, 'field', FILTER_SANITIZE_ENCODED);
        $lectureID = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_ENCODED);
        
        if ($longName != '' && $shortName != '' && $field != '' && $lectureID != '' && is_numeric($lectureID)) {
            $result = $lectureSystem->updateLecture($lectureID, $longName, $shortName, $field);
            if ($result) {
                $status = 'UPDATED_LECTURE_DATA';
            } else {
                $status = 'ERROR_ON_UPDATING_LECTURE_DATA';
            }
        } else {
            $status = 'ERROR_ON_UPDATING_LECTURE_DATA';
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
    echo '<div style="width: 25%; display: inline-block;">' . $i18n->get('longName') . '</div>';
    echo '<div style="width: 20%; display: inline-block;">' . $i18n->get('shortName') . '</div>';
    echo '<div style="width: 20%; display: inline-block;">' . $i18n->get('field') . '</div>';
    echo '<div style="width: 10%; display: inline-block; text-align: center;">' . $i18n->get('numberOfProtocols') . '</div>';
    echo '<div style="width: 10%; display: inline-block; text-align: center;">' . $i18n->get('viewProtocols') . '</div>';
    echo '<div style="width: 10%; display: inline-block; text-align: center;">' . $i18n->get('save') . '</div>';
        
    $allLectures = $lectureSystem->getAllLectures();
    foreach ($allLectures as &$lecture) {
        echo '<form method="POST" action="lectureslist.php">';
        echo '<div style="width: 5%; display: inline-block; text-align: center;">' . $lecture->getID() . '</div>';
        echo '<div style="width: 25%; display: inline-block;">' . '<input type="text" name="longName" value="' . $lecture->getLongName() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 20%; display: inline-block;">' . '<input type="text" name="shortName" value="' . $lecture->getShortName() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 20%; display: inline-block;">' . '<input type="text" name="field" value="' . $lecture->getField() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 10%; display: inline-block; text-align: center;">' . count($lecture->getAssignedExamProtocols()) . '</div>';
        echo '<div style="width: 10%; display: inline-block; text-align: center;">
                    <a href="examprotocolslist.php?lectureID=' . $lecture->getID() . '" id="styledButton">
                        <img src="static/img/viewProtocol.png" alt="view protocol" style="height: 24px; vertical-align: middle;">
                    </a>
                </div>';
        echo '<div style="width: 10%; display: inline-block; text-align: center;">' . 
                    '<button type="submit" id="styledButton" name="id" value="' . $lecture->getID() . '" style="padding: 3px; width: 40px; height: 40px; vertical-align: middle;">
                        <img src="static/img/save.png" alt="submit" style="height: 24px;">
                    </button>' .
                '</div>';
        echo '</form>';
    }
    
    echo $footer->getFooter();
?>
