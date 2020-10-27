<?php
class UserMenu {
    function getUserMenu($currentUser, $i18n) {
        $content = '';
        if ($currentUser != NULL) {
            $content .= '<p>' . $i18n->get('user') . ': ' . $currentUser->getUsername() . '</p>';
            $content .= '<a href="asktokens.php">' . $i18n->get('numberOfTokens') . ': ' . $currentUser->getTokens() . '</a>';
            $content .= '<a href="changepassword.php">' . $i18n->get('changePassword') . '</a>';
            if ($currentUser->getLanguage() == 'de') {
                $content .= '<a href="?language=en">' . $i18n->get('englishVersion') . '</a>';
            } else {
                $content .= '<a href="?language=de">' . $i18n->get('germanVersion') . '</a>';
            }
        } else {
            $content .= '<a href="login.php">' . $i18n->get('login') . '</a>';
        }
        $content .= '<a href="impressum.php">' . $i18n->get('impressum') . '</a>';
        if ($currentUser != NULL) {
            $content .= '<a href="?logout=true">' . $i18n->get('logout') . '</a>';
        }
        return $content;
    }
}
?>
