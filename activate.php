<?php
    require_once('core/Main.php');
    
    $status = NULL;
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $username = filter_input(INPUT_GET, 'user', FILTER_SANITIZE_ENCODED);
        $key = filter_input(INPUT_GET, 'key', FILTER_SANITIZE_ENCODED);
        
        if (isset($username) && isset($key)) {
            $username = strtolower($username);
            $user = $userSystem->getUserByUsername($username);
            if ($user != NULL) {
                if ($user->getRole() == Constants::USER_ROLES['notActivated']) {
                    $result = $userSystem->activateUser($username, $key);
                    if ($result) {
                        $status = 'ACTIVATE_USER_SUCCESSFUL';
                        $log->setUsername($username);
                        $log->info('activate.php', 'User activation successful');
                    } else {
                        $status = 'ACTIVATE_USER_UNSUCCESSFUL';
                        $log->setUsername($username);
                        $log->error('activate.php', 'User activation unsuccessful');
                    }
                } else {
                    $status = 'USER_ALREADY_ACTIVATED';
                    $log->setUsername($username);
                    $log->warning('activate.php', 'User activation unsuccessful, user already activated');
                }
            } else {
                $status = 'USER_NOT_FOUND';
                $log->setUsername($username);
                $log->warning('activate.php', 'User activation unsuccessful, user not found');
            }
        }
    }

    echo $header->getHeader($i18n->get('title'), $i18n->get('activateAccount'), array('login.css', 'activate.css', 'button.css'));

    function getActivationField($message, $success, $i18n) {
        $image = 'ppiLogo.png' . $GLOBALS["VERSION_STRING"];
        $goToLogin = '';
        if ($success == 'SUCCESS') {
            $image = 'activation_successful.png' . $GLOBALS["VERSION_STRING"];
            $goToLogin = '<center><a href="login.php" id="styledButton">' . $i18n->get('backToLogin') . '</a></center><br><br>';
        } else if ($success == 'NO_SUCCESS') {
            $image = 'activation_unsuccessful.png' . $GLOBALS["VERSION_STRING"];
        }
        return '<div id="loginField">
                <br>
                <center>
                    <div id="ppiLogo">
                        <img src="static/img/' . $image . $GLOBALS["VERSION_STRING"] . '" style="height: 55px;" alt="ppi logo">
                    </div>
                </center>
                <br>
                <br>
                <div id="infoText">' . $message . '</div>
                <br>
                <br>
                ' . $goToLogin . '
                <div id="forgotPassword">
                    <span style="float: left;"><a href="login.php" style="color: black;">' . $i18n->get('backToLogin') . '</a></span>
                    <span style="float: right;"><a href="recovery.php" style="color: black;">' . $i18n->get('forgotPassword') . '</a></span>
                </div>
                <br>
            </div>';
    }

    if ($status == NULL) {
        echo getActivationField($i18n->get('checkEmailForActivationLinkMessage'), 'NONE', $i18n);
    } else if ($status == 'ACTIVATE_USER_SUCCESSFUL') {
        echo getActivationField($i18n->get('userActivationSuccessfulMessage'), 'SUCCESS', $i18n);
    } else if ($status == 'ACTIVATE_USER_UNSUCCESSFUL') {
        echo getActivationField($i18n->get('userActivationUnsuccessfulMessage'), 'NO_SUCCESS', $i18n);
    } else if ($status == 'USER_NOT_FOUND') {
        echo getActivationField($i18n->get('userNotFoundMessage'), 'NO_SUCCESS', $i18n);
    } else if ($status == 'USER_ALREADY_ACTIVATED') {
        echo getActivationField($i18n->get('userAlreadyActivated'), 'NO_SUCCESS', $i18n);
    }

    echo $footer->getFooter();
?>
