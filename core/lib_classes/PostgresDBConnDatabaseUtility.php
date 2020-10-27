<?php
    class PostgresDBConnDatabaseUtility {
        private $dbConn = null;
    
        function __construct($dbConn) {
            $this->dbConn = $dbConn;
        }
        
        function getAllDatabaseTableNames() {
            $sql = "SELECT table_name
                    FROM information_schema.tables
                    WHERE table_schema='public'
                    AND table_type='BASE TABLE';";
            return $this->dbConn->query($sql);
        }
        
        function recreateDatabase() {
            $this->dropAllTables();
            $this->setUpEmptyUsersTable();
            $this->setUpEmptyExamProtocolsTable();
            $this->setUpEmptyLecturesTable();
            $this->setUpEmptyLogEventsTable();
            $this->setUpEmptyBorrowRecordsTable();
            $this->setUpEmptyExamProtocolAssignedToLecturesTable();
            
            $adminUsername = Constants::USERNAME_ADMIN;
            $adminPasswordHash = password_hash(Constants::PASSWORD_ADMIN, PASSWORD_DEFAULT, array('cost' => Constants::PASSWORD_COST));
            $sql = 'INSERT INTO "Users" ("username", "passwordHash", "role", "status", "tokens", "lastLoggedIn", "language", "comment") VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
            $this->dbConn->exec($sql, [$adminUsername, $adminPasswordHash, Constants::USER_ROLES['admin'], '', 123, '', 'de', 'admin user created by system']);
        }
        
        function dropAllTables() {
            $tableNames = $this->getAllDatabaseTableNames();
            foreach ($tableNames as $row) {
                foreach ($row as $tableName) {
                    $this->dbConn->exec('DROP TABLE IF EXISTS "' . $tableName . '";', array());
                }
            }
        }
        
        function setUpEmptyUsersTable() {
            $sql = 'CREATE TABLE "Users" (
    "ID"                SERIAL PRIMARY KEY UNIQUE,
    "username"          TEXT NOT NULL UNIQUE,
    "passwordHash"      TEXT NOT NULL,
    "role"              TEXT NOT NULL,
    "status"            TEXT NOT NULL,
    "tokens"            INT NOT NULL,
    "lastLoggedIn"      TEXT NOT NULL,
    "language"          TEXT NOT NULL,
    "comment"           TEXT NOT NULL
);';
            $this->dbConn->exec($sql, array());
        }
        
        function setUpEmptyExamProtocolsTable() {
            $sql = 'CREATE TABLE "ExamProtocols" (
    "ID"                SERIAL PRIMARY KEY UNIQUE,
    "status"            TEXT NOT NULL,
    "uploadedByUserID"  INT NOT NULL,
    "uploadedDate"      TEXT NOT NULL,
    "remark"            TEXT NOT NULL,
    "examiner"          TEXT NOT NULL,
    "filePath"          TEXT NOT NULL,
    "fileSize"          TEXT NOT NULL,
    "fileType"          TEXT NOT NULL,
    "fileExtension"     TEXT NOT NULL
);';
            $this->dbConn->exec($sql, array());
        }
        
        function setUpEmptyLecturesTable() {
            $sql = 'CREATE TABLE "Lectures" (
    "ID"                SERIAL PRIMARY KEY UNIQUE,
    "longName"          TEXT NOT NULL UNIQUE,
    "shortName"         TEXT NOT NULL UNIQUE,
    "field"             TEXT NOT NULL
);';
            $this->dbConn->exec($sql, array());
        }
        
        function setUpEmptyLogEventsTable() {
            $sql = 'CREATE TABLE "LogEvents" (
    "ID"                SERIAL PRIMARY KEY UNIQUE,
    "date"              TEXT NOT NULL,
    "username"          TEXT NOT NULL,
    "level"             TEXT NOT NULL,
    "remark"            TEXT NOT NULL,
    "origin"            TEXT NOT NULL
);';
            $this->dbConn->exec($sql, array());
        }
        
        function setUpEmptyBorrowRecordsTable() {
            $sql = 'CREATE TABLE "BorrowRecords" (
    "ID"                SERIAL PRIMARY KEY UNIQUE,
    "lectureID"         INT NOT NULL,
    "borrowedByUserID"  INT NOT NULL,
    "borrowedUntilDate" TEXT NOT NULL
);';
            $this->dbConn->exec($sql, array());
        }
        
        function setUpEmptyExamProtocolAssignedToLecturesTable() {
            $sql = 'CREATE TABLE "ExamProtocolAssignedToLectures" (
    "ID"                SERIAL PRIMARY KEY UNIQUE,
    "lectureID"         INT NOT NULL,
    "examProtocolID"    INT NOT NULL
);';
            $this->dbConn->exec($sql, array());
        }
    }
?>
