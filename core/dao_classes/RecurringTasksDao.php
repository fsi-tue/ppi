<?php
class RecurringTasksDao {
    private $dbConn = null;
    private $dateUtil = null;

    function __construct($dbConn, $dateUtil) {
        $this->dbConn = $dbConn;
        $this->dateUtil = $dateUtil;
    }
    
    /**
     * Returns the recurring task from the DB according to the given unique recurring task ID or NULL if the recurring task was not found.
     */
    function getRecurringTask($ID) {
        $sql = "SELECT * FROM \"RecurringTasks\" WHERE \"ID\"='" . $ID . "';";
        $result = $this->getRecurringTasksImpl($sql, 'ID');
        if (count($result) > 0) {
            return $result[0];
        }
        return NULL;
    }
    
    /**
     * Returns all recurring tasks from the DB.
     */
    function getAllRecurringTasks() {
        $sql = "SELECT * FROM \"RecurringTasks\" ORDER BY \"ID\";";
        return $this->getRecurringTasksImpl($sql, 'ID');
    }
    
    /**
     * Executes the query to get recurring tasks from the DB.
     */
    function getRecurringTasksImpl($sql, $idColumn) {
        $result = $this->dbConn->query($sql);
        $retList = array();
        for ($i = 0; $i < count($result); $i++) {
            $data = $result[$i];
            if (isset($data[$idColumn])) {
                $data['ID'] = $data[$idColumn];
            }
            $retList[] = $this->createRecurringTaskFromData($data);
        }
        return $retList;
    }
    
    /**
     * Constructs a recurring task object from the given data array.
     */
    function createRecurringTaskFromData($data) {
        $ID = $data['ID'];
        $name = $data['name'];
        $lastRunDate = $this->dateUtil->stringToDateTime($data['lastRunDate']);
        $periodTimeframe = $data['periodTimeframe'];
        $periodUnit = $data['periodUnit'];
        return new RecurringTask($ID, $name, $lastRunDate, $periodTimeframe, $periodUnit);
    }
    
    /**
     * Inserts the new recurring task into the DB.
     * Returns the given recurring task with also the ID set.
     * If the operation was not successful, FALSE will be returned.
     */
    function addRecurringTask($recurringTask) {
        $sql = "INSERT INTO \"RecurringTasks\" (\"name\", \"lastRunDate\", \"periodTimeframe\", \"periodUnit\") VALUES (?, ?)";
        $result = $this->dbConn->exec($sql, [$recurringTask->getName(), $this->dateUtil->dateTimeToString($recurringTask->getLastRunDate()), $recurringTask->getPeriodTimeframe(), $recurringTask->getPeriodUnit()]);
        $id = $result['lastInsertId'];
        if ($id < 1) {
            return false;
        }
        $recurringTask->setID($id);
        return $recurringTask;
    }
    
    /**
     * Updates the recurring task data in the DB.
     * Returns TRUE if the transaction was successful, FALSE otherwise.
     */
    function updateRecurringTask($recurringTask) {
        $sql = "UPDATE \"RecurringTasks\" SET \"name\"=?, \"lastRunDate\"=?, \"periodTimeframe\"=?, \"periodUnit\"=? WHERE \"ID\"=?;";
        $result = $this->dbConn->exec($sql, [$recurringTask->getName(), $this->dateUtil->dateTimeToString($recurringTask->getLastRunDate()), $recurringTask->getPeriodTimeframe(), $recurringTask->getPeriodUnit(), $recurringTask->getID()]);
        $rowCount = $result['rowCount'];
        if ($rowCount <= 0) {
            return false;
        }
        return true;
    }
    
    /**
     * Deletes the recurring task from the DB according to the given unique recurring task ID.
     * Returns TRUE if the transaction was successful, FALSE otherwise.
     */
    function deleteRecurringTask($ID) {
        $sql = "DELETE FROM \"RecurringTasks\" WHERE \"ID\"=?;";
        $result = $this->dbConn->exec($sql, [$ID]);
        $rowCount = $result['rowCount'];
        if ($rowCount <= 0) {
            return false;
        }
        return true;
    }
    
    /**
     * Remove all expired borrow reciórds of all users from the database.
     */
    function removeExpiredBorrowRecords() {
        // TODO: implement
        return true;
    }
    
    /**
     * Remove log events so the database is not filling up endlessly.
     */
    function cleanupLogs() {
        // TODO: implement
        return true;
    }
    
    /**
     * Remove the exam protocols that are marked to be deleted.
     */
    function removeToBeDeletedProtocols() {
        // TODO: implement
        return true;
    }
    
    /**
     * Remove the users that are marked to be deleted.
     */
    function removeToBeDeletedUsers() {
        $sql = "DELETE FROM \"Users\" WHERE \"role\"=?;";
        $result = $this->dbConn->exec($sql, [Constants::USER_ROLES['toBeDeleted']]);
        $rowCount = $result['rowCount'];
        if ($rowCount <= 0) {
            return false;
        }
        return true;
    }
    
    /**
     * Remove the lectures that are marked to be deleted.
     */
    function removeToBeDeletedLectures() {
        // TODO: implement
        return true;
    }
    
    /**
     * Adds the given number of tokens to all users.
     */
    function addTokensToAllUsers($numberOfTokens) {
        // TODO: implement
        return true;
    }
}
?>
