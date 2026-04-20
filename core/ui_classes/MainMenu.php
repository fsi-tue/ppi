<?php
class MainMenu {
    function getMainMenu($i18n, $user) {
        $menu = '<div class="main-menu-container">';
        $menu .= '<a class="styledButton menuButton" href="lectures.php">' . $i18n->get('showLectures') . '</a>';
        $menu .= '<a class="styledButton menuButton" href="download.php">' . $i18n->get('downloadProtocols') . '</a>';
        $menu .= '<a class="styledButton menuButton" href="upload.php">' . $i18n->get('uploadProtocol') . '</a>';
        $menu .= '<a class="styledButtonGray menuButton">' . $i18n->get('numberOfTokens') . ': ' . $user->getTokens() . '</a>';
        $menu .= '<a class="styledButton menuButton" href="asktokens.php">' . $i18n->get('askForMoreTokens') . '</a>';
        $menu .= '</div>';
        if ($user->getRole() == Constants::USER_ROLES['admin']) {
            $menu .= '<div class="main-menu-container admin-menu">';
            $menu .= '<a class="styledButtonGray menuButton">' . $i18n->get('adminOptions') . ': ' . '</a>';
            $menu .= '<a class="styledButton menuButton" href="userslist.php">' . $i18n->get('usersList') . '</a>';
            $menu .= '<a class="styledButton menuButton" href="lectureslist.php">' . $i18n->get('lecturesList') . '</a>';
            $menu .= '<a class="styledButton menuButton" href="examprotocolslist.php">' . $i18n->get('examProtocolsList') . '</a>';
            $menu .= '<a class="styledButton menuButton" href="logevents.php">' . $i18n->get('logEvents') . '</a>';
            $menu .= '<a class="styledButton menuButton" href="unittests.php">' . $i18n->get('unitTests') . '</a>';
            $menu .= '<a class="styledButton menuButton" href="recurringtasks.php">' . $i18n->get('recurringTasks') . '</a>';
            $menu .= '</div>';
        }
        return $menu;
    }
}
?>
