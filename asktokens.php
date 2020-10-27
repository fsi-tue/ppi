<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $redirect->redirectTo('login.php');
    }
    
    $status = NULL;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS);
        if (isset($_POST['message'])) {
            $result = $userSystem->askTokens($message);
            if ($result) {
                $status = 'ASKED_FOR_TOKENS';
            } else {
                $status = 'ASKING_FOR_TOKENS_FAILED';
            }
        }
    }
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $username = filter_input(INPUT_GET, 'username', FILTER_SANITIZE_ENCODED);
        if (isset($_GET['username'])) {
            if ($currentUser->getRole() == Constants::USER_ROLES['admin']) {
                $result = $userSystem->addTokensToUser($username, Constants::TOKENS_ADDED_PER_UPLOAD);
                if ($result) {
                    $status = 'ADDED_TOKENS_TO_USER';
                } else {
                    $status = 'FAILED_ADDING_TOKENS_TO_USER';
                }
            } else {
                $status = 'CAN_NOT_ADD_TOKENS_NO_ADMIN';
            }
        }
    }

    echo $header->getHeader($i18n->get('title'), $i18n->get('askForMoreTokens'), array('upload.css', 'button.css'));
    
    echo $mainMenu->getMainMenu($i18n, $currentUser);

    $inputField = $i18n->get('whyShouldWeGiveYouTokens') .
                    '<br><br>
                    <form action="asktokens.php" method="POST">
                        <textarea id="message" name="message" rows="10" style="width: 100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;" required></textarea><br><br>
                        <input type="submit" value="' . $i18n->get('submit') . '">
                    </form>';
    $message = '';
    if ($status == 'ASKED_FOR_TOKENS') {
        $message = $i18n->get('successfullyAskedForTokensMessage');
        $inputField = '';
    } else if ($status == 'ASKING_FOR_TOKENS_FAILED') {
        $message = $i18n->get('askingForTokensFailedMessage');
    } else if ($status == 'ADDED_TOKENS_TO_USER') {
        $message = $i18n->get('successfullyAddedTokensToUserMessage');
        $inputField = '';
    } else if ($status == 'FAILED_ADDING_TOKENS_TO_USER') {
        $message = $i18n->get('failedAddingTokensToUser');
        $inputField = '';
    } else if ($status == 'CAN_NOT_ADD_TOKENS_NO_ADMIN') {
        $message = $i18n->get('canNotAddTokensNoAdminMessage');
        $inputField = '';
    }

    echo '<div style="margin-left: 20%; width: 60%; padding-top: 30px;">
                <div id="uploadField">'
                    . $message . $inputField . 
                '</div>
            </div>';

    echo $footer->getFooter();
?>
