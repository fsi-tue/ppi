<?php
class Constants {
    // debug and development options
    const ERROR_REPORTING_IN_WEBPAGE = true;
    const SHOW_PHP_INFO = false;
    const WRITE_MAILS_TO_DISK_INSTEAD_OF_SENDING = true;
    const MAIL_TO_DISK_PATH = 'debug_mail_content/mailContents.txt';

    // log levels
    const LOG_LEVELS = [0 => 'DEBUG', 1 => 'INFO', 2 => 'WARNING', 3 => 'ERROR', 4 => 'CRITICAL'];
    const LOG_TO_DATABASE_FROM_LEVEL = 0;
    const ALERT_ADMIN_FROM_LEVEL = 2;
    
    // SQL
    const POSTGRES_HOST = 'localhost';
    const POSTGRES_PORT = '5432';
    const POSTGRES_DB_NAME = 'postgres';
    const POSTGRES_DB_NAME_UNIT_TESTS = 'postgresunittests';
    const POSTGRES_USER = 'postgres';
    const POSTGRES_PASSWORD = Passwords::POSTGRES_PASSWORD;
    
    // registration
    const PASSWORD_COST = 10;
    
    // files
    const UPLOADED_PROTOCOLS_DIRECTORY = 'exam_protocols/protocols';
    const TMP_ZIP_FILES_DIRECTORY = 'exam_protocols/zip_files';
    const ALLOWED_FILE_EXTENSION_DOWNLOAD = ['zip'];
    const ALLOWED_FILE_EXTENSION_UPLOAD = ['txt', 'pdf'];
    
    // user settings
    const DEFAULT_LANGUAGE = 'de';
    const START_BALANCE = 6;
    const TOKENS_ADDED_PER_UPLOAD = 3;
    const EXAM_PROTOCOL_BORROWED_TIMEFRAME = 4;
    const EXAM_PROTOCOL_BORROWED_UNIT = 'WEEKS';
    
    // system
    const DEFAULT_TIMEZONE = 'Europe/Berlin';
    const DATE_FORMAT = 'Y-m-d H:i:s.v';
    const DATE_FORMAT_GERMAN = 'd.m.Y H:i';
    const DATE_FORMAT_ENGLISH = 'm-d-Y H:i';
    const MAX_UPLOAD_FILE_SIZE_BYTES = 4194304; // 4MB

    // enum constants
    const EXAM_PROTOCOL_STATUS = ['unchecked' => 'UNCHECKED', 'accepted' => 'ACCEPTED', 'declined' => 'DECLINED', 'toBeDeleted' => 'TO_BE_DELETED'];
    const USER_ROLES = ['admin' => 'ADMIN', 'user' => 'USER', 'notActivated' => 'NOT_ACTIVATED', 'blocked' => 'BLOCKED', 'toBeDeleted' => 'TO_BE_DELETED'];
    
    // email
    const EMAIL_USER_DOMAIN = '@student.uni-tuebingen.de';
    const EMAIL_SENDER_DOMAIN = '@fsi.uni-tuebingen.de';
    const EMAIL_SENDER_NAME = 'PPI';
    const EMAIL_SENDER_ADDRESS = 'ppi' . Constants::EMAIL_SENDER_DOMAIN;
    
    // admin
    const USERNAME_ADMIN = 'admin';
    const PASSWORD_ADMIN = Passwords::PASSWORD_ADMIN;
    const NUMBER_OF_ENTRIES_PER_PAGE = 50;
    const EMAIL_ADMIN = 'fsi@fsi.uni-tuebingen.de';
}
?>
