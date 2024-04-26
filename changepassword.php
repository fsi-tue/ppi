<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $log->info('changepassword.php', 'User was not logged in');
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
            $log->info('changepassword.php', 'User entered invalid password on password change: ' . $currentUser->getUsername());
        } else if ($newPassword != $newPasswordRepeated) {
            $status = 'PASSWORDS_DO_NOT_MATCH';
            $log->info('changepassword.php', 'User did not enter new matching passwords on password change: ' . $currentUser->getUsername());
        }
        
        if ($status == 'CHANGE_PASSWORD') {
            $result = $userSystem->changePasswordOfCurrentUser($newPasswordHash);
            if ($result) {
                $status = 'SUCCESSFUL';
                $log->info('changepassword.php', 'User successfully changed password: ' . $currentUser->getUsername());
            } else {
                $status = 'NOT_SUCCESSFUL';
                $log->error('changepassword.php', 'Password change was not successful: ' . $currentUser->getUsername());
            }
        }
    }

    echo $header->getHeader($i18n->get('title'), $i18n->get('changePassword'), array('login.css', 'create.css', 'button.css'));

    function getChangePasswordField($incorrectPassword, $passwordsDoNotMatch, $notSuccessful, $changed, $message, $i18n) {
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
            $backButton = '<center><a href="lectures.php" class="styledButton">' .  $i18n->get('back') . '</a></center>';
        }
        return '<div id="loginField">
                    <center><div id="ppiLogo"><img src="static/img/ppiLogo.png' . $GLOBALS["VERSION_STRING"] . '" style="height: 55px;" alt="ppi logo"></div></center>
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
        echo getChangePasswordField(false, false, false, false, $i18n->get('changePasswordHowToMessage'), $i18n);
    } else if ($status == 'INCORRECT_PASSWORD') {
        echo getChangePasswordField(true, false, false, false, $i18n->get('incorrectPassword'), $i18n);
    } else if ($status == 'PASSWORDS_DO_NOT_MATCH') {
        echo getChangePasswordField(false, true, false, false, $i18n->get('passwordsDoNotMatch'), $i18n);
    } else if ($status == 'SUCCESSFUL') {
        echo getChangePasswordField(false, false, false, true, $i18n->get('passwordChangedSuccessfully'), $i18n);
    } else if ($status == 'NOT_SUCCESSFUL') {
        echo getChangePasswordField(false, false, true, false, $i18n->get('changingOfPasswordFailed'), $i18n);
    }

    echo $footer->getFooter();
?>
