<?php
    // include all constants
    require_once('constants/Passwords.php');
    require_once('constants/Constants.php');

    // auto-generate version string from git commit hash of the PPI repository
    $GLOBALS["VERSION_STRING"] = "?v=" . file_get_contents(dirname(__DIR__, 1) .  "/.git/refs/heads/main", NULL, NULL, 0, 40);

    // enable html error reporting
    if (Constants::ERROR_REPORTING_IN_WEBPAGE) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }
    
    // util function for debug printing data
    function debugPrint($data) {
        echo '<br><br><pre> ';
        print_r($data);
        echo ' </pre><br><br><br><br><br><br><br><br><br><br><br><br>';
    }
    
    // start the a session (using session cookies)
    session_start();
    
    // set the timezone
    date_default_timezone_set(Constants::DEFAULT_TIMEZONE);

    // include the database connection class
    require_once('lib_classes/MySqlDBConn.php');
    $dbConn = new DBConn(Constants::DATABASE_HOST, Constants::DATABASE_PORT, Constants::DATABASE_DB_NAME, Constants::DATABASE_USER, Constants::DATABASE_PASSWORD, 'COLUMN_NAMES');
    
    // internationalization
    include_once 'i18n/I18n.php';
    $i18n = new I18n(Constants::DEFAULT_LANGUAGE);

    // include all library classes
    require_once('lib_classes/DateUtil.php');
    require_once('lib_classes/Email.php');
    require_once('lib_classes/HashUtil.php');
    require_once('lib_classes/UrlUtil.php');
    require_once('lib_classes/Redirect.php');
    require_once('lib_classes/FileUtil.php');
    $dateUtil = new DateUtil();
    $email = new Email();
    $hashUtil = new HashUtil();
    $urlUtil = new UrlUtil();
    $redirect = new Redirect($urlUtil);
    $fileUtil = new FileUtil();
    
    // include the database untility class
    require_once('lib_classes/MySqlDBConnDatabaseUtility.php');
    $databaseUtility = new DBConnDatabaseUtility($dbConn, $dateUtil);
    //$databaseUtility->recreateDatabase();

    // include all data classes
    require_once('data_classes/BorrowRecord.php');
    require_once('data_classes/ExamProtocol.php');
    require_once('data_classes/ExamProtocolAssignedToLecture.php');
    require_once('data_classes/Lecture.php');
    require_once('data_classes/LogEvent.php');
    require_once('data_classes/User.php');
    require_once('data_classes/RecurringTask.php');

    // include all dao classes
    require_once('dao_classes/ExamProtocolDao.php');
    require_once('dao_classes/LectureDao.php');
    require_once('dao_classes/LogEventDao.php');
    require_once('dao_classes/UserDao.php');
    require_once('dao_classes/RecurringTasksDao.php');
    $examProtocolDao = new ExamProtocolDao($dbConn, $dateUtil);
    $lectureDao = new LectureDao($dbConn);
    $logEventDao = new LogEventDao($dbConn, $dateUtil);
    $userDao = new UserDao($dbConn, $dateUtil);
    $recurringTasksDao = new RecurringTasksDao($dbConn, $dateUtil);

    // include all system classes
    require_once('system_classes/ExamProtocolSystem.php');
    require_once('system_classes/LectureSystem.php');
    require_once('system_classes/LogEventSystem.php');
    require_once('system_classes/UserSystem.php');
    require_once('system_classes/RecurringTasksSystem.php');
    $examProtocolSystem = new ExamProtocolSystem($examProtocolDao, $dateUtil, $fileUtil, $hashUtil);
    $lectureSystem = new LectureSystem($lectureDao, $email);
    $logEventSystem = new LogEventSystem($logEventDao, $email, $dateUtil);
    $userSystem = new UserSystem($userDao, $email, $i18n, $hashUtil, $urlUtil, $dateUtil);
    $recurringTasksSystem = new RecurringTasksSystem($recurringTasksDao, $examProtocolSystem, $lectureSystem, $dateUtil, $fileUtil);

    // include logging class
    require_once('log/Log.php');
    $log = new Log($logEventSystem, $dateUtil);
    
    // set log object reference to all classes where logging shall happen
    $dbConn->setLog($log);
    $fileUtil->setLog($log);
    $i18n->setLog($log);
    $examProtocolSystem->setLog($log);
    $lectureSystem->setLog($log);
    $logEventSystem->setLog($log);
    $userSystem->setLog($log);
    $recurringTasksSystem->setLog($log);
    
    // instantiate the unit tests so they can be run from the admin page
    require_once('test/TestUtil.php');
    $dbConnTests = new DBConn(Constants::DATABASE_HOST, Constants::DATABASE_PORT, Constants::DATABASE_DB_NAME_UNIT_TESTS, Constants::DATABASE_USER, Constants::DATABASE_PASSWORD, 'COLUMN_NAMES');
    $testUtil = new TestUtil($log, $dbConnTests, $dateUtil, $fileUtil);
    
    // show phpinfo on top of page if wanted
    if (Constants::SHOW_PHP_INFO) {
        phpinfo();
    }
    
    // get if the user wants to be logged out
    $logout = filter_input(INPUT_GET, 'logout', FILTER_SANITIZE_ENCODED);
    if ($logout == 'true') {
        $userSystem->logoutCurrentUser();
        $redirect->redirectTo('login.php');
    }

    // get the currently logged in user and set the language accordingly
    $currentUser = $userSystem->getLoggedInUser();
    if ($currentUser != NULL) {
        // get the selected language
        if (isset($_GET['language'])) {
            $selectedLanguage = filter_input(INPUT_GET, 'language', FILTER_SANITIZE_ENCODED);
            if ($selectedLanguage != $currentUser->getLanguage()) {
                $userSystem->changeLanguageOfCurrentUser($selectedLanguage);
                $redirect->redirectTo($urlUtil->getCurrentScript());
            }
        }
        // set the language to the internationalization util
        $i18n->init($currentUser->getLanguage());
        
        // set the current username to the log
        $log->setUsername($currentUser->getUsername());
    }
    
    // run the recurring tasks if they have been run the last time too far in the past
    $recurringTasksSystem->runRecurringTasks();
    
    // include all needed UI classes
    require_once('ui_classes/Errors.php');
    require_once('ui_classes/HelpMenu.php');
    require_once('ui_classes/UserMenu.php');
    require_once('ui_classes/Header.php');
    require_once('ui_classes/Footer.php');
    require_once('ui_classes/MainMenu.php');
    require_once('ui_classes/SearchableTable.php');
    require_once('ui_classes/PagedContentUtil.php');
    $errors = new Errors($i18n, $logEventSystem);
    $helpMenu = new HelpMenu($i18n);
    $userMenu = new UserMenu();
    $header = new Header($helpMenu, $userMenu, $currentUser, $i18n);
    $footer = new Footer($i18n, $errors);
    $mainMenu = new MainMenu();
    $searchableTable = new SearchableTable($i18n);
    $pagedContentUtil = new PagedContentUtil($i18n);
?>
