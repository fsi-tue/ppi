<?php
class LectureDao {
    private $dbConn = null;

    function __construct($dbConn) {
        $this->dbConn = $dbConn;
    }
    
    /**
     * Returns the lecture from the DB according to the given unique lecture ID or NULL if the lecture was not found.
     */
    function getLecture($ID, $lecturesWithAcceptedExamProtocolsOnly) {
        $sql = "SELECT * FROM \"Lectures\" WHERE \"ID\"='" . $ID . "';";
        $result = $this->getLecturesImpl($sql, $lecturesWithAcceptedExamProtocolsOnly);
        if (count($result) > 0) {
            return $result[0];
        }
        return NULL;
    }
    
    /**
     * Returns all lectures from the DB.
     */
    function getAllLectures() {
        $sql = "SELECT * FROM \"Lectures\" ORDER BY \"ID\";";
        return $this->getLecturesImpl($sql, false);
    }
    
    /**
     * Returns all lectures that have accepted protocols assigned from the DB or an empty array if none were not found.
     */
    function getAllLecturesWithAcceptedProtocols() {
        $sql = "SELECT * FROM \"Lectures\" ORDER BY \"ID\";";
        return $this->getLecturesImpl($sql, true);
    }
    
    function getLecturesImpl($sql, $lecturesWithAcceptedExamProtocolsOnly) {
        $result = $this->dbConn->query($sql);
        $retList = array();
        for ($i = 0; $i < count($result); $i++) {
            $data = $result[$i];
            $sql = "SELECT * FROM \"ExamProtocolAssignedToLectures\" WHERE \"lectureID\"='" . $data['ID'] . "';";
            if ($lecturesWithAcceptedExamProtocolsOnly) {
                $sql = "SELECT * FROM \"ExamProtocolAssignedToLectures\"
                        INNER JOIN \"ExamProtocols\" ON \"ExamProtocols\".\"ID\"=\"ExamProtocolAssignedToLectures\".\"examProtocolID\"
                        WHERE \"status\"='" . Constants::EXAM_PROTOCOL_STATUS['accepted'] . "' AND \"lectureID\"='" . $data['ID'] . "';";
            }
            $assignedExamProtocolsResult = $this->dbConn->query($sql);
            $assignedExamProtocols = array();
            for ($j = 0; $j < count($assignedExamProtocolsResult); $j++) {
                $assignedExamProtocols[] = $this->createExamProtocolAssignedToLectureFromData($assignedExamProtocolsResult[$j]);
            }
            $data['assignedExamProtocols'] = $assignedExamProtocols;
            $retList[] = $this->createLectureFromData($data);
        }
        return $retList;
    }
    
    function createLectureFromData($data) {
        $ID = $data['ID'];
        $longName = $data['longName'];
        $shortName = $data['shortName'];
        $field = $data['field'];
        $assignedExamProtocols = $data['assignedExamProtocols'];
        return new Lecture($ID, $longName, $shortName, $field, $assignedExamProtocols);
    }
    
    function createExamProtocolAssignedToLectureFromData($data) {
        $ID = $data['ID'];
        $lectureID = $data['lectureID'];
        $examProtocolID = $data['examProtocolID'];
        return new ExamProtocolAssignedToLecture($ID, $lectureID, $examProtocolID);
    }
    
    /**
     * Inserts the new lecture into the DB.
     * Returns the given lecture with also the ID set.
     * If the operation was not successful, FALSE will be returned.
     */
    function addLecture($lecture) {
        $this->dbConn->beginTransaction(); 
        
        $sql = "INSERT INTO \"Lectures\" (\"longName\", \"shortName\", \"field\") VALUES (?, ?, ?)";
        $result = $this->dbConn->exec($sql, [$lecture->getLongName(), $lecture->getShortName(), $lecture->getField()]);
        $id = $result['lastInsertId'];
        if ($id < 1) {
            $this->dbConn->rollbackTransaction(); 
            return false;
        }
        
        $lecture->setID($id);
        
        for ($i = 0; $i < count($lecture->getAssignedExamProtocols()); $i++) {
            $assignedProtocol = $lecture->getAssignedExamProtocols()[$i];
            $assignedProtocol->setLectureID($lecture->getID());
            $sql = "INSERT INTO \"ExamProtocolAssignedToLectures\" (\"lectureID\", \"examProtocolID\") VALUES (?, ?)";
            $result = $this->dbConn->exec($sql, [$assignedProtocol->getLectureID(), $assignedProtocol->getExamProtocolID()]);
            $id = $result['lastInsertId'];
            if ($id < 1) {
                $this->dbConn->rollbackTransaction(); 
                return false;
            }
            $assignedProtocol->setID($id);
        }
        
        $this->dbConn->commitTransaction(); 
        
        return $lecture;
    }
    
    /**
     * Updates the lecture data in the DB.
     * Returns TRUE if the transaction was successful, FALSE otherwise.
     */
    function updateLecture($lecture) {
        $this->dbConn->beginTransaction(); 
        
        $sql = "UPDATE \"Lectures\" SET \"longName\"=?, \"shortName\"=?, \"field\"=? WHERE \"ID\"=?;";
        $result = $this->dbConn->exec($sql, [$lecture->getLongName(), $lecture->getShortName(), $lecture->getField(), $lecture->getID()]);
        $rowCount = $result['rowCount'];
        if ($rowCount <= 0) {
            $this->dbConn->rollbackTransaction(); 
            return false;
        }
        
        $sql = "DELETE FROM \"ExamProtocolAssignedToLectures\" WHERE \"lectureID\"=?;";
        $result = $this->dbConn->exec($sql, [$lecture->getID()]);
        
        for ($i = 0; $i < count($lecture->getAssignedExamProtocols()); $i++) {
            $assignedProtocol = $lecture->getAssignedExamProtocols()[$i];
            $assignedProtocol->setLectureID($lecture->getID());
            $sql = "INSERT INTO \"ExamProtocolAssignedToLectures\" (\"lectureID\", \"examProtocolID\") VALUES (?, ?)";
            $result = $this->dbConn->exec($sql, [$assignedProtocol->getLectureID(), $assignedProtocol->getExamProtocolID()]);
            $id = $result['lastInsertId'];
            if ($id < 1) {
                $this->dbConn->rollbackTransaction(); 
                return false;
            }
            $assignedProtocol->setID($id);
        }
        
        $this->dbConn->commitTransaction(); 
        
        return true;
    }
    
    /**
     * Deletes the lecture from the DB according to the given unique lecture ID.
     * Returns TRUE if the transaction was successful, FALSE otherwise.
     */
    function deleteLecture($ID) {
        $this->dbConn->beginTransaction(); 
        
        $sql = "DELETE FROM \"ExamProtocolAssignedToLectures\" WHERE \"lectureID\"=?;";
        $result = $this->dbConn->exec($sql, [$ID]);
        
        $sql = "DELETE FROM \"Lectures\" WHERE \"ID\"=?;";
        $result = $this->dbConn->exec($sql, [$ID]);
        $rowCount = $result['rowCount'];
        if ($rowCount <= 0) {
            $this->dbConn->rollbackTransaction(); 
            return false;
        }
        
        $this->dbConn->commitTransaction(); 
        
        return true;
    }
}
?>
