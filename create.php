<?php
    require_once('core/Main.php');

    $status = NULL;
    $username = NULL;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_ENCODED);
        $username = strtolower($username);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_ENCODED);
        $passwordRepeated = filter_input(INPUT_POST, 'password_repeated', FILTER_SANITIZE_ENCODED);
        $passwordHash = $hashUtil->hashPasswordWithSaltIncluded($password);
        
        // check if the user input is correct
        $status = 'CREATE_USER';
        
        // TODO: what if the zx-format changes at some point?
        $pattern = '/zx[a-z][a-z][a-z]\d\d$/';
        if (!preg_match($pattern, $username)) {
            $status = 'USERNAME_FORMAT_DOES_NOT_MATCH';
        } else if ($userSystem->usernameExists($username)) {
            $status = 'USERNAME_ALREADY_EXISTS';
        } else if ($password != $passwordRepeated) {
            $status = 'PASSWORDS_DO_NOT_MATCH';
        } else if ($password == NULL || $password == '') {
            $status = 'PASSWORDS_ARE_EMPTY';
        }
        
        // if the user input is correct, put the user in the database
        // send a mail to him and redirect to the activation page where information on how to activate will be shown
        if ($status == 'CREATE_USER') {
            $user = $userSystem->createUserAndSendMail($username, $passwordHash);
            if ($user != NULL) {
                $log->setUsername($username);
                $log->info('create.php', 'User creation successful');
                $redirect->redirectTo('activate.php');
            } else {
                $log->setUsername($username);
                $log->error('create.php', 'User creation unsuccessful');
            }
        }
    }

    echo $header->getHeader($i18n->get('title'), $i18n->get('createAccount'), array('login.css'));

    function getLoginField($message, $username, $i18n) {
        return '<div id="loginField">
                <img src="static/img/ppiLogo.png" id="ppiLogo" alt="ppi logo">
                <div id="infoText">' . $message . '</div>
                <form method="POST" action="">
                    <input type="text" id="username" name="username" placeholder="' . $i18n->get('userZxShort') . '" value="' . $username . '" required>
                    <input type="password" id="password" name="password" placeholder="' . $i18n->get('password') . '" required>
                    <input type="password" id="password_repeated" name="password_repeated" placeholder="' . $i18n->get('repeatPassword') . '" required>
                    <input type="submit" id="login" value="' . $i18n->get('createAccount') . '">
                </form>
                <div id="leftRightLink">
                    <a href="login.php" id="leftLink">' . $i18n->get('backToLogin') . '</a>
                    <a href="recovery.php" id="rightLink">' . $i18n->get('forgotPassword') . '</a>
                </div>
            </div>';
    }

    if ($status == NULL) {
        echo getLoginField($i18n->get('createAccountMessage'), '', $i18n);
    } else if ($status == 'USERNAME_FORMAT_DOES_NOT_MATCH') {
        echo getLoginField($i18n->get('createAccountUsernameFormatDoesNotMatchMessage'), '', $i18n);
    } else if ($status == 'USERNAME_ALREADY_EXISTS') {
        echo getLoginField($i18n->get('createAccountUsernameAlreadyExistsMessage'), '', $i18n);
    } else if ($status == 'PASSWORDS_DO_NOT_MATCH') {
        echo getLoginField($i18n->get('createAccountPasswordsDoNotMatchMessage'), $username, $i18n);
    } else if ($status == 'PASSWORDS_ARE_EMPTY') {
        echo getLoginField($i18n->get('createAccountPasswordsAreEmpty'), $username, $i18n);
    }

    echo $footer->getFooter();
?>
