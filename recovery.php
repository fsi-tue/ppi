<?php
    require_once('core/Main.php');

    $status = NULL;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_ENCODED);
        $username = strtolower($username);
        
        $status = 'RESET_PASSWORD';
        
        if (!$userSystem->usernameExists($username)) {
            $status = 'USER_DOES_NOT_EXIST';
            $log->warning('recovery.php', 'Tried to reset passwort for non-existent user name: ' . $username);
        }
        
        if ($status == 'RESET_PASSWORD') {
            $user = $userSystem->resetPasswordAndSendMail($username);
            if ($user != NULL) {
                $log->setUsername($username);
                $log->info('recovery.php', 'Passwort reset and mail sent successfully for user ' . $username);
                $redirect->redirectTo('login.php');
            } else {
                $log->setUsername($username);
                $log->error('recovery.php', 'Passwort reset and mail sent not successfully for user ' . $username);
            }
        }
    }

    echo $header->getHeader($i18n->get('title'), $i18n->get('resetPassword'), array('login.css'));

    function getResetPasswordField($message, $success, $i18n) {
        $color = '';
        if (!$success) {
            $color = ' style="background-color: red;"';
        }
        return '<div id="loginField">
                    <br>
                    <img src="static/img/ppiLogo.png" id="ppiLogo" alt="ppi logo">
                    <br>
                    <br>
                    <div id="infoText">' . $message . '</div>
                    <form method="POST" action="">
                        <input type="text" id="username" name="username" placeholder="' . $i18n->get('userZxShort') . '"' . $color . ' maxlength="7" required>
                        <input type="submit" id="login" value="' . $i18n->get('resetPassword') . '">
                    </form>
                    <br>
                    <br>
                    <div id="leftRightLink">
                        <a href="login.php" id="leftLink">' . $i18n->get('backToLogin') . '</a>
                        <a href="create.php" id="rightLink">' . $i18n->get('createAccount') . '</a>
                    </div>
                    <br>
                </div>';
    }

    if ($status == NULL) {
        echo getResetPasswordField($i18n->get('resetPasswordMessage'), true, $i18n);
    } else if ($status == 'RESET_PASSWORD') {
        echo getResetPasswordField($i18n->get('resetPasswordSuccessfulMessage'), true, $i18n);
    } else if ($status == 'USER_DOES_NOT_EXIST') {
        echo getResetPasswordField($i18n->get('userDoesNotExist'), false, $i18n);
    }

    echo $footer->getFooter();
