<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $redirect->redirectTo('login.php');
    }

    $status = NULL;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $oldPassword = filter_input(INPUT_POST, 'old_password', FILTER_SANITIZE_ENCODED);
        $newPassword = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_ENCODED);
        $newPasswordRepeated = filter_input(INPUT_POST, 'repeat_new_password', FILTER_SANITIZE_ENCODED);
        $newPasswordHash = $hashUtil->hashPasswordWithSaltIncluded($newPassword);
        
        $status = 'CHANGE_PASSWORD';
            
        $success = $hashUtil->checkPasswordHashWithSaltIncluded($oldPassword, $currentUser->getPasswordHash());
        if (!$success) {
            $status = 'INCORRECT_PASSWORD';
        } else if ($newPassword != $newPasswordRepeated) {
            $status = 'PASSWORDS_DO_NOT_MATCH';
        }
        
        if ($status == 'CHANGE_PASSWORD') {
            $result = $userSystem->changePasswordOfCurrentUser($newPasswordHash);
            if ($result) {
                $status = 'SUCCESSFUL';
            } else {
                $status = 'NOT_SUCCESSFUL';
            }
        }
    }

    echo $header->getHeader($i18n->get('title'), $i18n->get('changePassword'), array('login.css', 'create.css', 'button.css'));

    function getLoginField($incorrectPassword, $passwordsDoNotMatch, $notSuccessful, $changed, $message, $i18n) {
        $colorOldPassword = '';
        $colorNewPasswords = '';
        if ($incorrectPassword) {
            $colorOldPassword = ' style="background-color: red;"';
        }
        if ($passwordsDoNotMatch) {
            $colorNewPasswords = ' style="background-color: red;"';
        }
        if ($notSuccessful) {
            $colorOldPassword = ' style="background-color: red;"';
            $colorNewPasswords = ' style="background-color: red;"';
        }
        $typePassword = 'password';
        $typeSubmit = 'submit';
        $backButton = '';
        if ($changed) {
            $typePassword = 'hidden';
            $typeSubmit = 'hidden';
            $backButton = '<a href="lectures.php"><div id="login">' .  $i18n->get('back') . '</div></a>';
        }
        return '<div id="loginField">
                    <div id="ppiLogo"><img src="static/img/ppiLogo.png" style="height: 55px;" alt="ppi logo"></div>
                    <div id="infoText">' . $message . '</div>
                    <form method="POST" action="">
                        <input type="' . $typePassword . '" id="username" name="old_password" placeholder="' . $i18n->get('oldPassword') . '"' . $colorOldPassword . ' required>
                        <input type="' . $typePassword . '" id="password" name="new_password" placeholder="' . $i18n->get('newPassword') . '"' . $colorNewPasswords . ' required>
                        <input type="' . $typePassword . '" id="password_repeated" name="repeat_new_password" placeholder="' . $i18n->get('repeatNewPassword') . '"' . $colorNewPasswords . ' required>
                        <input type="' . $typeSubmit . '" id="login" value="' . $i18n->get('changePassword') . '">
                    </form>
                    ' . $backButton . '
                </div>';
    }

    if ($status == NULL) {
        echo getLoginField(false, false, false, false, $i18n->get('changePasswordHowToMessage'), $i18n);
    } else if ($status == 'INCORRECT_PASSWORD') {
        echo getLoginField(true, false, false, false, $i18n->get('incorrectPassword'), $i18n);
    } else if ($status == 'PASSWORDS_DO_NOT_MATCH') {
        echo getLoginField(false, true, false, false, $i18n->get('passwordsDoNotMatch'), $i18n);
    } else if ($status == 'SUCCESSFUL') {
        echo getLoginField(false, false, false, true, $i18n->get('passwordChangedSuccessfully'), $i18n);
    } else if ($status == 'NOT_SUCCESSFUL') {
        echo getLoginField(false, false, true, false, $i18n->get('changingOfPasswordFailed'), $i18n);
    }

    echo $footer->getFooter();
?>
