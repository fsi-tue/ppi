<?php
class UserSystem {
    private $userDao = null;
    private $email = null;
    private $i18n = null;
    private $hashUtil = null;
    private $urlUtil = null;
    private $dateUtil = null;
    private $log = null;

    function __construct($userDao, $email, $i18n, $hashUtil, $urlUtil, $dateUtil) {
        $this->userDao = $userDao;
        $this->email = $email;
        $this->i18n = $i18n;
        $this->hashUtil = $hashUtil;
        $this->urlUtil = $urlUtil;
        $this->dateUtil = $dateUtil;
    }

    /**
     * Set the log to enable error logging.
     */
    function setLog($log) {
        $this->log = $log;
    }
    
    /**
     * Returns all users from the DB.
     */
    function getAllUsers() {
        return $this->userDao->getAllUsers();
    }
    
    /**
     * Returns the user from the DB according to the given unique user ID or NULL if the user was not found.
     */
    function getUser($ID) {
        return $this->userDao->getUser($ID);
    }
    
    /**
     * Returns the number of users that are in the DB.
     */
    function getNumberOfUsersTotal($role, $username, $userID) {
        return $this->userDao->getNumberOfUsersTotal($role, $username, $userID);
    }
    
    /**
     * Returns users from the DB according to the number of wanted results and the start page.
     */
    function getUsers($numberOfResultsWanted, $page, $role, $username, $userID) {
        return $this->userDao->getUsers($numberOfResultsWanted, $page, $role, $username, $userID);
    }
    
    /**
     * Checks username and password and logs in the user if the authentication was successful.
     * Returns TRUE id the login was successful, FALSE otherwise.
     */
    function loginUser($username, $password) {
        $user = $this->userDao->getUserByUsername($username);
        if ($user != NULL) {
            $success = $this->hashUtil->checkPasswordHashWithSaltIncluded($password, $user->getPasswordHash());
            $roleCorrect = $user->getRole() == Constants::USER_ROLES['user'] || $user->getRole() == Constants::USER_ROLES['admin'];
            if ($success && $roleCorrect) {
                $_SESSION['auth'] = $user->getID();
                $user->setLastLoggedIn($this->dateUtil->dateTimeToString($this->dateUtil->getDateTimeNow()));
                return $this->userDao->updateUser($user);
            }
        }
        return false;
    }
    
    /**
     * Logs out the currently logged in user.
     */
    function logoutCurrentUser() {
        unset($_SESSION['auth']);
        unset($_COOKIE['auth']);
    }
    
    /**
     * Returns if a user is logged in.
     */
    function isLoggedIn() {
        return $this->getLoggedInUser() != NULL;
    }
    
    /**
     * Returns the currently logged in user or NULL if no user is currently logged in.
     */
    function getLoggedInUser() {
        if (isset($_SESSION['auth'])) {
            $loggedInUserID = $_SESSION['auth'];
            return $this->userDao->getUser($loggedInUserID);
        }
        return NULL;
    }
    
    /**
     * Returns TRUE if the given username exists in the database, FALSE otherwise.
     */
    function usernameExists($username) {
        return $this->userDao->getUserByUsername($username) != NULL;
    }
    
    /**
     * Returns the user from the DB according to the given unique username or NULL if the user was not found.
     */
    function getUserByUsername($username) {
        return $this->userDao->getUserByUsername($username);
    }
    
    /**
     * Creates a user and sends an account activation mail. Returns the user if the task was successful, FALSE otherwise.
     */
    function createUserAndSendMail($username, $passwordHash) {
        $randomString = $this->hashUtil->generateRandomString();
        $user = new User(NULL, $username, $passwordHash, Constants::USER_ROLES['notActivated'], $randomString, '0', '', Constants::DEFAULT_LANGUAGE, '', array());
        $result = $this->userDao->addUser($user);
        if ($result != false) {
            $this->email->send($result->getUsername() . Constants::EMAIL_USER_DOMAIN, $this->i18n->get('activationMailSubject'), $this->i18n->getWithValues('activationMailMessage', [$result->getUsername(), $this->urlUtil->getCurrentDirname() . 'activate.php?user=' . $result->getUsername() . '&key=' . $randomString]));
        } else {
            $this->log->error(static::class . '.php', 'Error on creating user with username ' . $username . '!');
        }
        return $result;
    }
    
    /**
     * Re-sends the activation mail for a user that is already present in the system and sets the new password.
     * Returns TRUE if the task was successful, FALSE otherwise.
     */
    function resendActivationMail($username, $passwordHash) {
        $user = $this->getUserByUsername($username);
        if ($user != NULL) {
            if ($user->getRole() != Constants::USER_ROLES['notActivated']) {
                $this->log->warning(static::class . '.php', 'User with username ' . $username . ' who is already activated wanted to re-register.');
                return false;
            }
            $user->setPasswordHash($passwordHash);
            $this->email->send($user->getUsername() . Constants::EMAIL_USER_DOMAIN, $this->i18n->get('activationMailSubject'), $this->i18n->getWithValues('activationMailMessage', [$user->getUsername(), $this->urlUtil->getCurrentDirname() . 'activate.php?user=' . $user->getUsername() . '&key=' . $user->getStatus()]));
            return $this->userDao->updateUser($user);
        } else {
            $this->log->error(static::class . '.php', 'Error on re-sending activation mail to user with username ' . $username . '!');
        }
        return false;
    }
    
    /**
     * Changes the password hash of the currently logged in user. Returns TRUE if the task was successful, FALSE otherwise.
     */
    function changePasswordOfCurrentUser($passwordHash) {
        $user = $this->getLoggedInUser();
        if ($user != NULL) {
            $user->setPasswordHash($passwordHash);
            $result = $this->userDao->updateUser($user);
            return $result;
        } else {
            $this->log->error(static::class . '.php', 'Error on changing the password of the currently logged in user!');
        }
        return false;
    }
    
    /**
     * Changes the language of the currently logged in user. Returns TRUE if the task was successful, FALSE otherwise.
     */
    function changeLanguageOfCurrentUser($newLanguage) {
        $user = $this->getLoggedInUser();
        if ($user != NULL) {
            $user->setLanguage($newLanguage);
            $result = $this->userDao->updateUser($user);
            return $result;
        } else {
            $this->log->error(static::class . '.php', 'Error on changing the language of the currently logged in user!');
        }
        return false;
    }
    
    /**
     * Adds the given number of tokens to the user and saves this to the database.
     * Can also be used to subtract tokens from a user.
     * Returns TRUE if the task was successful, FALSE otherwise.
     */
    function addTokensToUser($username, $numberOfTokens) {
        $user = $this->userDao->getUserByUsername($username);
        if ($user != NULL) {
            $newTokens = $user->getTokens() + $numberOfTokens;
            if ($newTokens < 0) {
                $newTokens = 0;
            }
            $newTokens = intval($newTokens);
            $user->setTokens($newTokens);
            return $this->userDao->updateUser($user);
        } else {
            $this->log->error(static::class . '.php', 'Error on adding tokens to the currently logged in user!');
        }
        return false;
    }
    
    /**
     * Sends an email to the admin because a user asks for more tokens.
     * Returns TRUE if the task was successful, FALSE otherwise.
     */
    function askTokens($message) {
        $user = $this->getLoggedInUser();
        if ($user != NULL) {
            $messageFull = $this->i18n->getWithValues('userAsksForTokensMessage', [$this->urlUtil->getCurrentDirname() . 'asktokens.php?username=' . $user->getUsername(), $message]);
            return $this->email->send(Constants::EMAIL_ADMIN, $this->i18n->get('userAsksForTokens'), $messageFull);
        } else {
            $this->log->error(static::class . '.php', 'Error on asking for tokens by the currently logged in user!');
        }
        return false;
    }
    
    /**
     * Activates the given user. Returns the user if the task was successful, FALSE otherwise.
     */
    function activateUser($username, $key) {
        $user = $this->userDao->getUserByUsername($username);
        if ($user != NULL) {
            if ($user->getStatus() != $key) {
                return false;
            }
            $user->setRole(Constants::USER_ROLES['user']);
            $user->setStatus('');
            $user->setTokens(Constants::START_BALANCE);
            $result = $this->userDao->updateUser($user);
            if ($result == false) {
                $this->log->error(static::class . '.php', 'Error on activating the user ' . $username . '!');
                return false;
            }
        } else {
            $this->log->error(static::class . '.php', 'Error on getting the user with the username ' . $username . '!');
        }
        return $user;
    }
    
    /**
     * Resets the password of the given user to a random string and sends a mail with this password.
     * Also the user is informed to change the password in the near future. Returns TRUE if the task was successful, FALSE otherwise.
     */
    function resetPasswordAndSendMail($username) {
        $user = $this->userDao->getUserByUsername($username);
        if ($user != NULL) {
            $randomString = $this->hashUtil->generateRandomString();
            $passwordHash = $this->hashUtil->hashPasswordWithSaltIncluded($randomString);
            $user->setPasswordHash($passwordHash);
            $result = $this->userDao->updateUser($user);
            if ($result != false) {
                $this->email->send($user->getUsername() . Constants::EMAIL_USER_DOMAIN, $this->i18n->get('resetPasswordMailSubject'), $this->i18n->getWithValues('resetPasswordMailMessagePleaseLogInAndChangePassword', [$username, $randomString]));
                return true;
            } else {
                $this->log->error(static::class . '.php', 'Error on resetting the password of the user ' . $username . '!');
            }
        } else {
            $this->log->error(static::class . '.php', 'Error on getting the user with the username ' . $username . '!');
        }
        return false;
    }
    
    /**
     * Lets the given user borrow the given lecture.
     * Returns TRUE if the transaction was successful, FALSE otherwise.
     */
    function borrowLecture($user, $lectureID) {
        $borrowRecords = $user->getBorrowRecords();
        
        $borrowedLectureIDs = array();
        for ($i = 0; $i < count($borrowRecords); $i++) {
            $borrowedLectureIDs[] = $borrowRecords[$i]->getLectureID();
        }
        
        if ($user->getTokens() > 0 && !in_array($lectureID, $borrowedLectureIDs)) {
            $now = $this->dateUtil->getDateTimeNow();
            $borrowedUntil = $this->dateUtil->addToDateTime($now, Constants::EXAM_PROTOCOL_BORROWED_TIMEFRAME, Constants::EXAM_PROTOCOL_BORROWED_UNIT);
            $borrowRecords[] = new BorrowRecord(NULL, $lectureID, $user->getID(), $borrowedUntil);
            $user->setBorrowRecords($borrowRecords);
            $tokens = $user->getTokens() - 1;
            $user->setTokens($tokens);
            return $this->userDao->updateUser($user);
        }
        return false;
    }
    
    /**
     * Updates the user data in the database.
     * Returns TRUE if the transaction was successful, FALSE otherwise.
     */
    function updateUser($userID, $passwordHash, $role, $status, $tokens, $lastLoggedIn, $language, $comment) {
        $user = $this->userDao->getUser($userID);
        if ($user == NULL) {
            $this->log->error(static::class . '.php', 'Error on getting the user with the user ID ' . $userID . '!');
            return false;
        }
        $user->setPasswordHash($passwordHash);
        $user->setRole($role);
        $user->setStatus($status);
        $user->setTokens($tokens);
        $user->setLastLoggedIn($lastLoggedIn);
        $user->setLanguage($language);
        $user->setComment($comment);
        return $this->userDao->updateUser($user);
    }
    
    /**
     * Sends a reply mail to the given user. Also grants the given tokens to the given user.
     * Returns TRUE if the action was successful, FALSE otherwise.
     */
    function grantTokensAndMailToUploader($uploadedByUsername, $tokensToAdd, $reply) {
        if ($reply != '') {
            $messageFull = $this->i18n->getWithValues('uploadFeedbackMailMessage', [$uploadedByUsername, $reply]);
            $this->email->send($uploadedByUsername . Constants::EMAIL_USER_DOMAIN, $this->i18n->get('uploadedExamProtocolSubject'), $messageFull);
        }
        return $this->addTokensToUser($uploadedByUsername, $tokensToAdd);
    }
}
?>
