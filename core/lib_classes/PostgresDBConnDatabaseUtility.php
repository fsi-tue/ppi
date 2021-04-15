<?php
class PostgresDBConnDatabaseUtility {
    private $dbConn = null;
    private $dateUtil = null;

    function __construct($dbConn, $dateUtil) {
        $this->dbConn = $dbConn;
        $this->dateUtil = $dateUtil;
    }

    /**
     * Get all the table names from the database.
     */
    function getAllDatabaseTableNames() {
        $sql = "SELECT table_name
                FROM information_schema.tables
                WHERE table_schema='public'
                AND table_type='BASE TABLE';";
        return $this->dbConn->query($sql);
    }

    /**
     * Drop all tables from the database and recreate them with only the initial values.
     */
    function recreateDatabase() {
        $this->dropAllTables();
        $this->setUpEmptyUsersTable();
        $this->setUpEmptyExamProtocolsTable();
        $this->setUpEmptyLecturesTable();
        $this->setUpEmptyLogEventsTable();
        $this->setUpEmptyBorrowRecordsTable();
        $this->setUpEmptyExamProtocolAssignedToLecturesTable();
        $this->setUpEmptyRecurringTasksTable();
    }

    /**
     * Drop all tables from the database.
     */
    function dropAllTables() {
        $tableNames = $this->getAllDatabaseTableNames();
        foreach ($tableNames as $row) {
            foreach ($row as $tableName) {
                $this->dbConn->exec('DROP TABLE IF EXISTS "' . $tableName . '";', array());
            }
        }
    }

    /**
     * Set up the empty users table. Insert the admin user.
     */
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
        
        $adminUsername = Constants::USERNAME_ADMIN;
        $adminPasswordHash = password_hash(Constants::PASSWORD_ADMIN, PASSWORD_DEFAULT, array('cost' => Constants::PASSWORD_COST));
        $now = $this->dateUtil->dateTimeToString($this->dateUtil->getDateTimeNow());
        $sql = 'INSERT INTO "Users" ("username", "passwordHash", "role", "status", "tokens", "lastLoggedIn", "language", "comment") VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $this->dbConn->exec($sql, [$adminUsername, $adminPasswordHash, Constants::USER_ROLES['admin'], '', 123, $now, 'de', 'admin user created by system']);
    }

    /**
     * Set up the empty exam protocols table.
     */
    function setUpEmptyExamProtocolsTable() {
        $sql = 'CREATE TABLE "ExamProtocols" (
"ID"                SERIAL PRIMARY KEY UNIQUE,
"status"            TEXT NOT NULL,
"uploadedByUserID"  INT NOT NULL,
"collaboratorIDs"   TEXT NOT NULL,
"uploadedDate"      TEXT NOT NULL,
"remark"            TEXT NOT NULL,
"examiner"          TEXT NOT NULL,
"fileName"          TEXT NOT NULL,
"fileSize"          TEXT NOT NULL,
"fileType"          TEXT NOT NULL,
"fileExtension"     TEXT NOT NULL
);';
        $this->dbConn->exec($sql, array());
    }

    /**
     * Set up the empty lectures table.
     */
    function setUpEmptyLecturesTable() {
        $sql = 'CREATE TABLE "Lectures" (
"ID"                SERIAL PRIMARY KEY UNIQUE,
"name"              TEXT NOT NULL UNIQUE,
"status"            TEXT NOT NULL
);';
        $this->dbConn->exec($sql, array());
    }

    /**
     * Set up the empty log events table.
     */
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

    /**
     * Set up the empty borrow records table.
     */
    function setUpEmptyBorrowRecordsTable() {
        $sql = 'CREATE TABLE "BorrowRecords" (
"ID"                SERIAL PRIMARY KEY UNIQUE,
"lectureID"         INT NOT NULL,
"borrowedByUserID"  INT NOT NULL,
"borrowedUntilDate" TEXT NOT NULL
);';
        $this->dbConn->exec($sql, array());
    }

    /**
     * Set up the empty exam protocols assigned to lectures table.
     */
    function setUpEmptyExamProtocolAssignedToLecturesTable() {
        $sql = 'CREATE TABLE "ExamProtocolAssignedToLectures" (
"ID"                SERIAL PRIMARY KEY UNIQUE,
"lectureID"         INT NOT NULL,
"examProtocolID"    INT NOT NULL
);';
        $this->dbConn->exec($sql, array());
    }

    /**
     * Set up the empty recurring tasks table. Insert the recurring tasks and pretend they ran last a very long time ago.
     */
    function setUpEmptyRecurringTasksTable() {
        $sql = 'CREATE TABLE "RecurringTasks" (
"ID"                SERIAL PRIMARY KEY UNIQUE,
"name"              TEXT NOT NULL,
"lastRunDate"       TEXT NOT NULL,
"periodTimeframe"   TEXT NOT NULL,
"periodUnit"        TEXT NOT NULL
);';
        $this->dbConn->exec($sql, array());
        
        $recurringTasks = array_values(Constants::RECURRING_TASKS);
        $datetimeFarInPast = $this->dateUtil->dateTimeToString($this->dateUtil->getDateTimeFarInThePast());
        $recurringTasksTimeframes = array_values(Constants::RECURRING_TASKS_TIMEFRAMES);
        $recurringTasksUnits = array_values(Constants::RECURRING_TASKS_UNITS);
        for ($i = 0; $i < count($recurringTasks); $i++) {
            $sql = 'INSERT INTO "RecurringTasks" ("name", "lastRunDate", "periodTimeframe", "periodUnit") VALUES (?, ?, ?, ?)';
            $this->dbConn->exec($sql, [$recurringTasks[$i], $datetimeFarInPast, $recurringTasksTimeframes[$i], $recurringTasksUnits[$i]]);
        }
    }
}
?>
