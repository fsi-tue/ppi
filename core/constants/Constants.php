<?php
class Constants {
    // debug and development options
    const VERSION = '0.0.1';
    const ERROR_REPORTING_IN_WEBPAGE = false;
    const SHOW_PHP_INFO = false;
    const WRITE_MAILS_TO_DISK_INSTEAD_OF_SENDING = false;
    const MAIL_TO_DISK_PATH = 'debug_mail_content/mailContents.txt';

    // log levels
    const LOG_LEVELS = [0 => 'DEBUG', 1 => 'INFO', 2 => 'WARNING', 3 => 'ERROR', 4 => 'CRITICAL'];
    const LOG_TO_DATABASE_FROM_LEVEL = 0;
    const ALERT_ADMIN_FROM_LEVEL = 4;
    
    // SQL
    const POSTGRES_HOST = 'db';
    const POSTGRES_PORT = '5432';
    const POSTGRES_DB_NAME = 'ppi';
    const POSTGRES_DB_NAME_UNIT_TESTS = 'ppi_unittests';
    const POSTGRES_USER = 'ppi';
    const POSTGRES_PASSWORD = Passwords::POSTGRES_PASSWORD;
    
    // registration
    const PASSWORD_COST = 10;
    
    // files
    const UPLOADED_PROTOCOLS_DIRECTORY = 'exam_protocols/protocols';
    const TMP_ZIP_FILES_DIRECTORY = 'exam_protocols/zip_files';
    const ALLOWED_FILE_EXTENSION_DOWNLOAD = ['zip', 'txt', 'pdf'];
    const ALLOWED_FILE_EXTENSION_UPLOAD = ['txt', 'pdf'];
    
    // user settings
    const DEFAULT_LANGUAGE = 'de';
    const START_BALANCE = 6;
    const TOKENS_ADDED_PER_UPLOAD = 3;
    const NUMBER_OF_TOKENS_TO_ADD_TO_ALL_PER_SEMESTER = 1;
    const EXAM_PROTOCOL_BORROWED_TIMEFRAME = 4;
    const EXAM_PROTOCOL_BORROWED_UNIT = 'WEEKS';
    
    // system
    const DEFAULT_TIMEZONE = 'Europe/Berlin';
    const DATE_FORMAT = 'Y-m-d H:i:s.v';
    const DATE_FORMAT_GERMAN = 'd.m.Y H:i';
    const DATE_FORMAT_ENGLISH = 'm-d-Y H:i';
    const MAX_UPLOAD_FILE_SIZE_BYTES = 4194304; // 4MB
    const SUCCESS_COLOR = '#66FF66';
    const FAILED_COLOR = '#AA0000';
    const CLEANUP_LOG_TO_NUMBER_OF_EVENTS = 10000;
    
    // recurring tasks
    const RECURRING_TASKS = ['cleanDownloadsDirectory' => 'CLEAN_DOWNLOADS_DIRECTORY', 'removeExpiredBorrowRecords' => 'REMOVE_EXPIRED_BORROW_RECORDS', 'cleanupLogs' => 'CLEANUP_LOGS', 'removeToBeDeletedProtocols' => 'REMOVE_TO_BE_DELETED_PROTOCOLS', 'removeToBeDeletedUsers' => 'REMOVE_TO_BE_DELETED_USERS', 'removeToBeDeletedLectures' => 'REMOVE_TO_BE_DELETED_LECTURES', 'addTokensToAllUsers' => 'ADD_TOKENS_TO_ALL_USERS'];
    const RECURRING_TASKS_TIMEFRAMES = ['cleanDownloadsDirectory' => '1', 'removeExpiredBorrowRecords' => '1', 'cleanupLogs' => '1', 'removeToBeDeletedProtocols' => '1', 'removeToBeDeletedUsers' => '1', 'removeToBeDeletedLectures' => '1', 'addTokensToAllUsers' => '6'];
    const RECURRING_TASKS_UNITS = ['cleanDownloadsDirectory' => 'WEEKS', 'removeExpiredBorrowRecords' => 'WEEKS', 'cleanupLogs' => 'WEEKS', 'removeToBeDeletedProtocols' => 'WEEKS', 'removeToBeDeletedUsers' => 'WEEKS', 'removeToBeDeletedLectures' => 'WEEKS', 'addTokensToAllUsers' => 'MONTHS'];

    // enum constants
    const EXAM_PROTOCOL_STATUS = ['unchecked' => 'UNCHECKED', 'accepted' => 'ACCEPTED', 'declined' => 'DECLINED', 'toBeDeleted' => 'TO_BE_DELETED'];
    const USER_ROLES = ['admin' => 'ADMIN', 'user' => 'USER', 'notActivated' => 'NOT_ACTIVATED', 'blocked' => 'BLOCKED', 'toBeDeleted' => 'TO_BE_DELETED'];
    const LECTURE_STATUS = ['ok' => 'OK', 'toBeDeleted' => 'TO_BE_DELETED'];
    
    // email
    const EMAIL_USER_DOMAIN = '@student.uni-tuebingen.de';
    const EMAIL_SENDER_DOMAIN = '@fsi.uni-tuebingen.de';
    const EMAIL_SENDER_NAME = 'PPI';
    const EMAIL_SENDER_ADDRESS = 'pruefungsprotokolle' . Constants::EMAIL_SENDER_DOMAIN;
    
    // admin
    const USERNAME_ADMIN = 'admin';
    const PASSWORD_ADMIN = Passwords::PASSWORD_ADMIN;
    const NUMBER_OF_ENTRIES_PER_PAGE = 50;
    const EMAIL_ADMIN = 'pruefungsprotokolle@fsi.uni-tuebingen.de';
}
?>
