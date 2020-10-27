<?php
    require_once('core/Main.php');

    $status = NULL;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_ENCODED);
        $username = strtolower($username);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_ENCODED);
        
        $result = $userSystem->loginUser($username, $password);
        if ($result) {
            $log->setUsername($username);
            $log->debug('login.php', 'User login successful');
            $redirect->redirectTo('lectures.php');
        } else {
            $status = 'LOGIN_FAILED';
            $log->setUsername($username);
            $log->info('login.php', 'User login unsuccessful');
        }
    }

    echo $header->getHeader($i18n->get('title'), $i18n->get('login'), array('login.css'));

    function getLoginField($unsuccessful, $i18n) {
        $colorStyle = '';
        if ($unsuccessful) {
            $colorStyle = ' style="background-color: red;"';
        }
        return '<div id="loginField">
                    <img src="static/img/ppiLogo.png" id="ppiLogo" alt="ppi logo">
                    <form method="POST" action="">
                        <input type="text" id="username" name="username" placeholder="' . $i18n->get('userZxShort') . '"' . $colorStyle . ' required>
                        <input type="password" id="password" name="password" placeholder="' . $i18n->get('password') . '"' . $colorStyle . ' required>
                        <input type="submit" id="login" value="' . $i18n->get('login') . '">
                    </form>
                    <div id="leftRightLink">
                        <a href="recovery.php" id="leftLink">' . $i18n->get('forgotPassword') . '</a>
                        <a href="create.php" id="rightLink">' . $i18n->get('createAccount') . '</a>
                    </div>
                </div>';
    }

    if ($status == NULL) {
        echo getLoginField(false, $i18n);
    } else if ($status == 'LOGIN_FAILED') {
        echo getLoginField(true, $i18n);
    }

    echo $footer->getFooter();
