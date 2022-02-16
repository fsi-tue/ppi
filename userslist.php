<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $log->info('userslist.php', 'User was not logged in');
        $redirect->redirectTo('login.php');
    }
    if ($currentUser->getRole() != Constants::USER_ROLES['admin']) {
        $log->error('userslist.php', 'User was not admin');
        $redirect->redirectTo('lectures.php');
    }
    
    $postStatus = NULL;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $passwordHash = filter_input(INPUT_POST, 'passwordHash', FILTER_SANITIZE_SPECIAL_CHARS);
        $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_ENCODED);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_ENCODED);
        $tokens = filter_input(INPUT_POST, 'tokens', FILTER_SANITIZE_ENCODED);
        $lastLoggedIn = filter_input(INPUT_POST, 'lastLoggedIn', FILTER_SANITIZE_ENCODED);
        $language = filter_input(INPUT_POST, 'language', FILTER_SANITIZE_ENCODED);
        $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_ENCODED);
        $userID = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_ENCODED);
        
        if ($passwordHash != '' && $role != '' && $tokens != '' && $language != '' && is_numeric($userID)) {
            $result = $userSystem->updateUser($userID, $passwordHash, $role, $status, $tokens, $lastLoggedIn, $language, $comment);
            if ($result) {
                $postStatus = 'UPDATED_USER_DATA';
                $log->debug('userslist.php', 'Successfully updated user data');
            } else {
                $postStatus = 'ERROR_ON_UPDATING_USER_DATA';
                $log->debug('userslist.php', 'Error on updating user data');
            }
        } else {
            $postStatus = 'ERROR_ON_UPDATING_USER_DATA';
            $log->debug('userslist.php', 'Missing on updating user data');
        }
    }
    
    $page = 0;
    $role = '';
    $username = '';
    $userID = '';
    $open = '';
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $pageValue = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_ENCODED);
        $role = filter_input(INPUT_GET, 'role', FILTER_SANITIZE_ENCODED);
        $username = filter_input(INPUT_GET, 'username', FILTER_SANITIZE_ENCODED);
        $userIDValue = filter_input(INPUT_GET, 'userID', FILTER_SANITIZE_ENCODED);
        $deleteID = filter_input(INPUT_GET, 'deleteID', FILTER_SANITIZE_ENCODED);
        if (is_numeric($pageValue)) {
            $page = intval($pageValue);
        } else {
            if ($pageValue != '') {
                $log->debug('userslist.php', 'Page value is not numeric: ' . $pageValue);
            }
        }
        if (isset($_GET['userID'])) {
            if (is_numeric($userIDValue)) {
                $userID = intval($userIDValue);
            } else {
                $log->info('userslist.php', 'User ID value is not numeric: ' . $userIDValue);
            }
        }
        if (isset($_GET['role']) || isset($_GET['username']) || isset($_GET['userID'])) {
            $open = ' open';
        }
        if (isset($_GET['deleteID'])) {
            if (is_numeric($deleteID)) {
                if ($deleteID != '1') {
                    $user = $userSystem->getUser($deleteID);
                    if ($user != null) {
                        $result = $userSystem->updateUser($deleteID, $user->getPasswordHash(), 'TO_BE_DELETED', $user->getStatus(), $user->getTokens(), $user->getlastLoggedIn(), $user->getLanguage(), $user->getComment());
                        if (!$result) {
                            $postStatus = 'ERROR_ON_UPDATING_USER_DATA';
                            $log->error('userslist.php', 'User could not be marked for deletion with user ID: ' . $deleteID);
                        }
                    } else {
                        $postStatus = 'ERROR_ON_UPDATING_USER_DATA';
                        $log->error('userslist.php', 'User ID to be deleted not found: ' . $deleteID);
                    }
                } else {
                    $postStatus = 'ERROR_ON_UPDATING_USER_DATA';
                    $log->error('userslist.php', 'Can not delete the admin user with ID 1');
                }
            } else {
                $postStatus = 'ERROR_ON_UPDATING_USER_DATA';
                $log->error('userslist.php', 'ID of user to be deleted is not numeric: ' . $deleteID);
            }
        }
    }
    
    echo $header->getHeader($i18n->get('title'), $i18n->get('allUsers'), array('protocols.css', 'button.css'));
    
    echo $mainMenu->getMainMenu($i18n, $currentUser);
    
    $passwordExample = $hashUtil->generateRandomString();
    
    echo '<center>
                <details' . $open . '>
                    <summary id="styledButton" style="line-height: 10px; margin: 5px;">' . $i18n->get('filter') . '</summary>
                    <div style="width: 15%; display: inline-block; text-align: left; margin: 0px;">
                        <form action="userslist.php" method="GET">
                            <input type="text" size="8" name="username" placeholder="' . $i18n->get('username') . '">
                            <input type="submit" value="' . $i18n->get('ok') . '">
                        </form>
                    </div>
                    <div style="width: 15%; display: inline-block; text-align: left; margin: 0px;">
                        <form action="userslist.php" method="GET">
                            <input type="text" size="8" name="userID" placeholder="' . $i18n->get('user') . ' ' . $i18n->get('ID') . '">
                            <input type="submit" value="' . $i18n->get('ok') . '">
                        </form>
                    </div>
                    <div style="width: 6%; display: inline-block; text-align: center; margin: 0px;">' . $i18n->get('or') . '</div>
                    <div style="left: 0%; width: 60%; display: inline-block; text-align: right; margin: 0px;">
                        <a href="?role=' . Constants::USER_ROLES['user'] . '" id="styledButtonGreen" style="margin: 0px;">' . Constants::USER_ROLES['user'] . 's</a>
                        <a href="?role=' . Constants::USER_ROLES['admin'] . '" id="styledButtonGreen" style="margin: 0px;">' . Constants::USER_ROLES['admin'] . 's</a>
                        <a href="?role=' . Constants::USER_ROLES['notActivated'] . '" id="styledButtonGreen" style="margin: 0px;">' . Constants::USER_ROLES['notActivated'] . '_USERs</a>
                        <a href="?role=' . Constants::USER_ROLES['blocked'] . '" id="styledButtonGreen" style="margin: 0px;">' . Constants::USER_ROLES['blocked'] . '_USERs</a>
                        <a href="?role=' . Constants::USER_ROLES['toBeDeleted'] . '" id="styledButtonGreen" style="margin: 0px;">' . Constants::USER_ROLES['toBeDeleted'] . '_USERs</a>
                    </div>
                    <div style="left: 0%; width: 100%; display: inline-block; margin: 0px;">
                        <br><center>' . $i18n->get('passwordExample') . ': ' . $passwordExample . ', ' . $i18n->get('hash') . ': ' . $hashUtil->hashPasswordWithSaltIncluded($passwordExample) . '</center><br>
                    </div>
                </details>
            </center>';

    if ($postStatus == 'UPDATED_USER_DATA') {
        echo '<br><center>' . $i18n->get('updatedUserDataSuccessfully') . '</center><br>';
    } else if ($postStatus == 'ERROR_ON_UPDATING_USER_DATA') {
        echo '<br><center>' . $i18n->get('errorOnUpdatingUserData') . '</center><br>';
    }
    
    $numberOfUsersTotal = $userSystem->getNumberOfUsersTotal($role, $username, $userID);
    
    echo $pagedContentUtil->getNavigation($page, Constants::NUMBER_OF_ENTRIES_PER_PAGE, $numberOfUsersTotal);
    
    echo '<br><br>';
    
    echo '<div style="width: 5%; display: inline-block; text-align: center;">' . $i18n->get('ID') . '</div>';
    echo '<div style="width: 5%; display: inline-block;">' . $i18n->get('user') . '</div>';
    echo '<div style="width: 10%; display: inline-block;">' . $i18n->get('passwordHash') . '</div>';
    echo '<div style="width: 5%; display: inline-block;">' . $i18n->get('role') . '</div>';
    echo '<div style="width: 5%; display: inline-block;">' . $i18n->get('status') . '</div>';
    echo '<div style="width: 5%; display: inline-block;">' . $i18n->get('tokens') . '</div>';
    echo '<div style="width: 10%; display: inline-block;">' . $i18n->get('lastLoggedIn') . '</div>';
    echo '<div style="width: 5%; display: inline-block;">' . $i18n->get('language') . '</div>';
    echo '<div style="width: 10%; display: inline-block;">' . $i18n->get('comment') . '</div>';
    echo '<div style="width: 10%; display: inline-block; text-align: center;">' . $i18n->get('numberOfBorrowedLectures') . '</div>';
    echo '<div style="width: 7%; display: inline-block; text-align: center;">' . $i18n->get('viewBorrowedLectures') . '</div>';
    echo '<div style="width: 7%; display: inline-block; text-align: center;">' . $i18n->get('viewUploadedLectures') . '</div>';
    echo '<div style="width: 8%; display: inline-block; text-align: center;">' . $i18n->get('markForDeletion') . '</div>';
    echo '<div style="width: 8%; display: inline-block; text-align: center;">' . $i18n->get('save') . '</div>';
    
    $allUsers = $userSystem->getUsers(Constants::NUMBER_OF_ENTRIES_PER_PAGE, $page, $role, $username, $userID);
    foreach ($allUsers as &$user) {
        echo '<form method="POST" action="userslist.php">';
        echo '<div style="width: 5%; display: inline-block; text-align: center;">' . $user->getID() . '</div>';
        echo '<div style="width: 5%; display: inline-block;">' . '<input type="text" readonly name="username" value="' . $user->getUsername() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 10%; display: inline-block;">' . '<input type="text" name="passwordHash" value="' . $user->getPasswordHash() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 5%; display: inline-block;">' . '<input type="text" name="role" value="' . $user->getRole() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 5%; display: inline-block;">' . '<input type="text" name="status" value="' . $user->getStatus() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 5%; display: inline-block;">' . '<input type="text" name="tokens" value="' . $user->getTokens() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 10%; display: inline-block;">' . '<input type="text" name="lastLoggedIn" value="' . $user->getLastLoggedIn() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 5%; display: inline-block;">' . '<input type="text" name="language" value="' . $user->getLanguage() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 10%; display: inline-block;">' . '<input type="text" name="comment" value="' . $user->getComment() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 10%; display: inline-block; text-align: center;">' . count($user->getBorrowRecords()) . '</div>';
        echo '<div style="width: 7%; display: inline-block; text-align: center;">
                    <a href="examprotocolslist.php?borrowedByUsername=' . $user->getUsername() . '" id="styledButton">
                        <img src="static/img/viewBorrowed.png' . $GLOBALS["VERSION_STRING"] . '" alt="view protocol" style="height: 24px; vertical-align: middle;">
                    </a>
                </div>';
        echo '<div style="width: 7%; display: inline-block; text-align: center;">
                    <a href="examprotocolslist.php?uploadedByUsername=' . $user->getUsername() . '" id="styledButton">
                        <img src="static/img/viewUploaded.png' . $GLOBALS["VERSION_STRING"] . '" alt="view protocol" style="height: 24px; vertical-align: middle;">
                    </a>
                </div>';
        echo '<div style="width: 8%; display: inline-block; text-align: center;">
                    <a href="?deleteID=' . $user->getID() . '" id="styledButtonRed">
                        <img src="static/img/delete.png' . $GLOBALS["VERSION_STRING"] . '" alt="view protocol" style="height: 24px; vertical-align: middle;">
                    </a>
                </div>';
        echo '<div style="width: 8%; display: inline-block; text-align: center;">' . 
                    '<button type="submit" id="styledButton" name="id" value="' . $user->getID() . '" style="padding: 3px; width: 40px; height: 40px; vertical-align: middle;">
                        <img src="static/img/save.png' . $GLOBALS["VERSION_STRING"] . '" alt="submit" style="height: 24px;">
                    </button>' .
                '</div>';
        echo '</form>';
    }
    
    echo '<br>';
    
    echo $pagedContentUtil->getNavigation($page, Constants::NUMBER_OF_ENTRIES_PER_PAGE, $numberOfUsersTotal);
    
    echo $footer->getFooter();
?>
