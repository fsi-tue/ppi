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
                $log->debug('userslist.php', 'Successfully updated data of user ' . $userID);
            } else {
                $postStatus = 'ERROR_ON_UPDATING_USER_DATA';
                $log->debug('userslist.php', 'Error on updating data of user ' . $userID);
            }
        } else {
            $postStatus = 'ERROR_ON_UPDATING_USER_DATA';
            $log->debug('userslist.php', 'Missing on updating data of user ' . $userID);
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
                    <summary class="styledButton" style="line-height: 1.5; margin: 5px; width: auto; padding: 10px 20px;">' . $i18n->get('filter') . '</summary>
                    <div class="main-menu-container" style="padding: 10px; border-bottom: 1px solid #ddd; margin-bottom: 10px;">
                        <form action="userslist.php" method="GET" style="display: inline-block; margin: 5px;">
                            <input type="text" name="username" placeholder="' . $i18n->get('username') . '" style="width: 150px; display: inline-block;">
                            <input type="submit" value="' . $i18n->get('ok') . '" style="width: auto; height: auto; padding: 10px;">
                        </form>
                        <form action="userslist.php" method="GET" style="display: inline-block; margin: 5px;">
                            <input type="text" name="userID" placeholder="' . $i18n->get('user') . ' ' . $i18n->get('ID') . '" style="width: 150px; display: inline-block;">
                            <input type="submit" value="' . $i18n->get('ok') . '" style="width: auto; height: auto; padding: 10px;">
                        </form>
                    </div>
                    <div class="main-menu-container" style="padding: 10px;">
                        <a href="?role=' . Constants::USER_ROLES['user'] . '" class="styledButtonGreen" style="margin: 5px; width: auto;">' . Constants::USER_ROLES['user'] . 's</a>
                        <a href="?role=' . Constants::USER_ROLES['admin'] . '" class="styledButtonGreen" style="margin: 5px; width: auto;">' . Constants::USER_ROLES['admin'] . 's</a>
                        <a href="?role=' . Constants::USER_ROLES['notActivated'] . '" class="styledButtonGreen" style="margin: 5px; width: auto;">' . Constants::USER_ROLES['notActivated'] . '_USERs</a>
                        <a href="?role=' . Constants::USER_ROLES['blocked'] . '" class="styledButtonGreen" style="margin: 5px; width: auto;">' . Constants::USER_ROLES['blocked'] . '_USERs</a>
                        <a href="?role=' . Constants::USER_ROLES['toBeDeleted'] . '" class="styledButtonGreen" style="margin: 5px; width: auto;">' . Constants::USER_ROLES['toBeDeleted'] . '_USERs</a>
                    </div>
                    <div style="width: 100%; padding: 10px;">
                        ' . $i18n->get('passwordExample') . ': ' . $passwordExample . ', ' . $i18n->get('hash') . ': ' . $hashUtil->hashPasswordWithSaltIncluded($passwordExample) . '
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
    
    echo '<div class="table-container">';
    echo '<div class="flex-table">';
    echo '<div class="flex-table-header">';
    echo '<div class="flex-table-cell" style="width: 5%;">' . $i18n->get('ID') . '</div>';
    echo '<div class="flex-table-cell" style="width: 5%;">' . $i18n->get('user') . '</div>';
    echo '<div class="flex-table-cell" style="width: 10%;">' . $i18n->get('passwordHash') . '</div>';
    echo '<div class="flex-table-cell" style="width: 5%;">' . $i18n->get('role') . '</div>';
    echo '<div class="flex-table-cell" style="width: 5%;">' . $i18n->get('status') . '</div>';
    echo '<div class="flex-table-cell" style="width: 5%;">' . $i18n->get('tokens') . '</div>';
    echo '<div class="flex-table-cell" style="width: 10%;">' . $i18n->get('lastLoggedIn') . '</div>';
    echo '<div class="flex-table-cell" style="width: 5%;">' . $i18n->get('language') . '</div>';
    echo '<div class="flex-table-cell" style="width: 10%;">' . $i18n->get('comment') . '</div>';
    echo '<div class="flex-table-cell" style="width: 10%;">' . $i18n->get('numberOfBorrowedLectures') . '</div>';
    echo '<div class="flex-table-cell" style="width: 7%;">' . $i18n->get('viewBorrowedLectures') . '</div>';
    echo '<div class="flex-table-cell" style="width: 7%;">' . $i18n->get('viewUploadedLectures') . '</div>';
    echo '<div class="flex-table-cell" style="width: 8%;">' . $i18n->get('markForDeletion') . '</div>';
    echo '<div class="flex-table-cell" style="width: 8%;">' . $i18n->get('save') . '</div>';
    echo '</div>';
    
    $allUsers = $userSystem->getUsers(Constants::NUMBER_OF_ENTRIES_PER_PAGE, $page, $role, $username, $userID);
    foreach ($allUsers as &$user) {
        echo '<form method="POST" action="userslist.php" class="flex-table-row">';
        echo '<div class="flex-table-cell" style="width: 5%;">' . $user->getID() . '</div>';
        echo '<div class="flex-table-cell" style="width: 5%;">' . '<input type="text" readonly name="username" value="' . $user->getUsername() . '">' . '</div>';
        echo '<div class="flex-table-cell" style="width: 10%;">' . '<input type="text" name="passwordHash" value="' . $user->getPasswordHash() . '">' . '</div>';
        echo '<div class="flex-table-cell" style="width: 5%;">' . '<input type="text" name="role" value="' . $user->getRole() . '">' . '</div>';
        echo '<div class="flex-table-cell" style="width: 5%;">' . '<input type="text" name="status" value="' . $user->getStatus() . '">' . '</div>';
        echo '<div class="flex-table-cell" style="width: 5%;">' . '<input type="text" name="tokens" value="' . $user->getTokens() . '">' . '</div>';
        echo '<div class="flex-table-cell" style="width: 10%;">' . '<input type="text" name="lastLoggedIn" value="' . $user->getLastLoggedIn() . '">' . '</div>';
        echo '<div class="flex-table-cell" style="width: 5%;">' . '<input type="text" name="language" value="' . $user->getLanguage() . '">' . '</div>';
        echo '<div class="flex-table-cell" style="width: 10%;">' . '<input type="text" name="comment" value="' . $user->getComment() . '">' . '</div>';
        echo '<div class="flex-table-cell" style="width: 10%;">' . count($user->getBorrowRecords()) . '</div>';
        echo '<div class="flex-table-cell" style="width: 7%;">
                    <a href="examprotocolslist.php?borrowedByUsername=' . $user->getUsername() . '" class="styledButton" style="min-width: 40px; padding: 5px;">
                        <img src="static/img/viewBorrowed.png' . $GLOBALS["VERSION_STRING"] . '" alt="view borrowed" style="height: 24px; vertical-align: middle;">
                    </a>
                </div>';
        echo '<div class="flex-table-cell" style="width: 7%;">
                    <a href="examprotocolslist.php?uploadedByUsername=' . $user->getUsername() . '" class="styledButton" style="min-width: 40px; padding: 5px;">
                        <img src="static/img/viewUploaded.png' . $GLOBALS["VERSION_STRING"] . '" alt="view uploaded" style="height: 24px; vertical-align: middle;">
                    </a>
                </div>';
        echo '<div class="flex-table-cell" style="width: 8%;">
                    <a href="?deleteID=' . $user->getID() . '" class="styledButtonRed" style="min-width: 40px; padding: 5px;">
                        <img src="static/img/delete.png' . $GLOBALS["VERSION_STRING"] . '" alt="delete" style="height: 24px; vertical-align: middle;">
                    </a>
                </div>';
        echo '<div class="flex-table-cell" style="width: 8%;">' . 
                    '<button type="submit" class="styledButton" name="id" value="' . $user->getID() . '" style="padding: 3px; min-width: 40px; height: 40px; vertical-align: middle;">
                        <img src="static/img/save.png' . $GLOBALS["VERSION_STRING"] . '" alt="submit" style="height: 24px;">
                    </button>' .
                '</div>';
        echo '</form>';
    }
    echo '</div>';
    echo '</div>';
    
    echo '<br>';
    
    echo $pagedContentUtil->getNavigation($page, Constants::NUMBER_OF_ENTRIES_PER_PAGE, $numberOfUsersTotal);
    
    echo $footer->getFooter();
?>
