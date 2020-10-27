<?php
class LogEventDao {
    private $dbConn = null;
    private $dateUtil = null;

    function __construct($dbConn, $dateUtil) {
        $this->dbConn = $dbConn;
        $this->dateUtil = $dateUtil;
    }
    
    /**
     * Returns the log event from the DB according to the given unique log event ID or NULL if the log event was not found.
     */
    function getLogEvent($ID) {
        $sql = "SELECT * FROM \"LogEvents\" WHERE \"ID\"='" . $ID . "';";
        $result = $this->getLogEventsImpl($sql);
        if (count($result) > 0) {
            return $result[0];
        }
        return NULL;
    }
    
    /**
     * Returns all logEvents from the DB.
     */
    function getAllLogEvents() {
        $sql = "SELECT * FROM \"LogEvents\" ORDER BY \"ID\";";
        return $this->getLogEventsImpl($sql);
    }
    
    function getNumberOfLogEventsTotal($username, $level) {
        $sql = "SELECT COUNT(*) FROM \"LogEvents\"";
        if ($username != '') {
            $sql .= " WHERE \"username\"='" . $username . "'";
        } else if ($level != '') {
            $sql .= " WHERE \"level\"='" . $level . "'";
        }
        $sql .= ";";
        $result = $this->dbConn->query($sql);
        return $result[0]['count'];
    }
    
    function getLogEvents($numberOfResultsWanted, $page, $username, $level) {
        $offset = $numberOfResultsWanted * $page;
        $sql = "SELECT * FROM \"LogEvents\"";
        if ($username != '') {
            $sql .= " WHERE \"username\"='" . $username . "'";
        } else if ($level != '') {
            $sql .= " WHERE \"level\"='" . $level . "'";
        }
        $sql .= " ORDER BY \"ID\" DESC LIMIT " . $numberOfResultsWanted . " OFFSET " . $offset . ";";
        return $this->getLogEventsImpl($sql);
    }
    
    function getLogEventsImpl($sql) {
        $result = $this->dbConn->query($sql);
        $retList = array();
        for ($i = 0; $i < count($result); $i++) {
            $retList[] = $this->createLogEventFromData($result[$i]);
        }
        return $retList;
    }
    
    function createLogEventFromData($data) {
        $ID = $data['ID'];
        $date = $this->dateUtil->stringToDateTime($data['date']);
        $username = $data['username'];
        $level = $data['level'];
        $remark = $data['remark'];
        $origin = $data['origin'];
        return new LogEvent($ID, $date, $username, $level, $remark, $origin);
    }
    
    /**
     * Inserts the new log event into the DB.
     * Returns the given log event with also the ID set.
     * If the operation was not successful, FALSE will be returned.
     */
    function addLogEvent($logEvent) {
        $sql = "INSERT INTO \"LogEvents\" (\"date\", \"username\", \"level\", \"remark\", \"origin\") VALUES (?, ?, ?, ?, ?)";
        $result = $this->dbConn->exec($sql, [$logEvent->getDate(), $logEvent->getUsername(), $logEvent->getLevel(), $logEvent->getRemark(), $logEvent->getOrigin()]);
        $id = $result['lastInsertId'];
        if ($id < 1) {
            return false;
        }
        $logEvent->setID($id);
        return $logEvent;
    }
    
    /**
     * Updates the log event data in the DB.
     * Returns TRUE if the transaction was successful, FALSE otherwise.
     */
    function updateLogEvent($logEvent) {
        $sql = "UPDATE \"LogEvents\" SET \"date\"=?, \"username\"=?, \"level\"=?, \"remark\"=?, \"origin\"=? WHERE \"ID\"=?;";
        $result = $this->dbConn->exec($sql, [$logEvent->getDate(), $logEvent->getUsername(), $logEvent->getLevel(), $logEvent->getRemark(), $logEvent->getOrigin(), $logEvent->getID()]);
        $rowCount = $result['rowCount'];
        return $rowCount > 0;
    }
    
    /**
     * Deletes the log event from the DB according to the given unique log event ID.
     * Returns TRUE if the transaction was successful, FALSE otherwise.
     */
    function deleteLogEvent($ID) {
        $sql = "DELETE FROM \"LogEvents\" WHERE \"ID\"=?;";
        $result = $this->dbConn->exec($sql, [$ID]);
        $rowCount = $result['rowCount'];
        return $rowCount > 0;
    }
}
?>
