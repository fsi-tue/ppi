<?php
class MainMenu {
    function getMainMenu($i18n, $user) {
        $menu = '<div style="padding: 10px; text-align: center;">';
        $menu .= '<a id="styledButton" href="lectures.php">' . $i18n->get('showLectures') . '</a>';
        $menu .= '<a id="styledButton" href="download.php">' . $i18n->get('downloadProtocols') . '</a>';
        $menu .= '<a id="styledButton" href="upload.php">' . $i18n->get('uploadProtocol') . '</a>';
        $menu .= ' <a id="styledButtonTransparent" style="font-size: 20px;">|</a> ';
        $menu .= '<a id="styledButtonGray">' . $i18n->get('numberOfTokens') . ': ' . $user->getTokens() . '</a>';
        $menu .= '<a id="styledButton" href="asktokens.php">' . $i18n->get('askForMoreTokens') . '</a>';
        if ($user->getRole() == Constants::USER_ROLES['admin']) {
            $menu .= '<br>';
            $menu .= '<a id="styledButtonGray">' . $i18n->get('adminOptions') . ': ' . '</a>';
            $menu .= '<a id="styledButtonRed" href="userslist.php">' . $i18n->get('usersList') . '</a>';
            $menu .= '<a id="styledButtonRed" href="lectureslist.php">' . $i18n->get('lecturesList') . '</a>';
            $menu .= '<a id="styledButtonRed" href="examprotocolslist.php">' . $i18n->get('examProtocolsList') . '</a>';
            $menu .= '<a id="styledButtonRed" href="logevents.php">' . $i18n->get('logEvents') . '</a>';
            $menu .= '<a id="styledButtonRed" href="unittests.php">' . $i18n->get('unitTests') . '</a>';
            $menu .= '<a id="styledButtonRed" href="recurringtasks.php">' . $i18n->get('recurringTasks') . '</a>';
        }
        $menu .= '</div>';
        return $menu;
    }
}
?>
