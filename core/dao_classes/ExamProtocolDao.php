<?php
class ExamProtocolDao {
    private $dbConn = null;
    private $dateUtil = null;

    function __construct($dbConn, $dateUtil) {
        $this->dbConn = $dbConn;
        $this->dateUtil = $dateUtil;
    }
    
    /**
     * Returns the exam protocol from the DB according to the given unique exam protocol ID or NULL if the exam protocol was not found.
     */
    function getExamProtocol($ID) {
        $sql = "SELECT * FROM \"ExamProtocols\" WHERE \"ID\"='" . $ID . "';";
        $result = $this->getExamProtocolsImpl($sql, 'ID');
        if (count($result) > 0) {
            return $result[0];
        }
        return NULL;
    }
    
    /**
     * Returns all exam protocols from the DB.
     */
    function getAllExamProtocols() {
        $sql = "SELECT * FROM \"ExamProtocols\" ORDER BY \"ID\";";
        return $this->getExamProtocolsImpl($sql, 'ID');
    }
    
    /**
     * Returns all exam protocols from the DB with the given status or an empty array if none were not found.
     */
    function getAllExamProtocolsWithStatus($status) {
        if (!in_array($status, Constants::EXAM_PROTOCOL_STATUS)) {
            return [];
        }
        $sql = "SELECT * FROM \"ExamProtocols\" WHERE \"status\"='" . $status . "';";
        return $this->getExamProtocolsImpl($sql, 'ID');
    }
    
    /**
     * Returns the number of exam protocols that are in the DB.
     */
    function getNumberOfExamProtocolsTotal($lectureID, $uploadedByUserID, $borrowedByUserID) {
        $sql = "SELECT DISTINCT ON (\"ExamProtocols\".\"ID\") * FROM \"ExamProtocols\"
        INNER JOIN \"ExamProtocolAssignedToLectures\" ON \"ExamProtocols\".\"ID\"=\"ExamProtocolAssignedToLectures\".\"examProtocolID\"";
        if ($lectureID != '') {
            $sql = "SELECT * FROM \"ExamProtocols\"
                    INNER JOIN \"ExamProtocolAssignedToLectures\" ON \"ExamProtocols\".\"ID\"=\"ExamProtocolAssignedToLectures\".\"examProtocolID\"
                    WHERE \"lectureID\"='" . $lectureID . "'";
        } else if ($uploadedByUserID != '') {
            $sql = "SELECT * FROM \"ExamProtocols\" WHERE \"uploadedByUserID\"='" . $uploadedByUserID . "'";
        } else if ($borrowedByUserID != '') {
            $sql = "SELECT * FROM \"ExamProtocols\"
                    INNER JOIN \"ExamProtocolAssignedToLectures\" ON \"ExamProtocols\".\"ID\"=\"ExamProtocolAssignedToLectures\".\"examProtocolID\"
                    INNER JOIN \"BorrowRecords\" ON \"ExamProtocolAssignedToLectures\".\"lectureID\"=\"BorrowRecords\".\"lectureID\"
                    WHERE \"borrowedByUserID\"='" . $borrowedByUserID . "'";
        }
        $sql .= " ORDER BY \"ExamProtocols\".\"ID\" DESC";
        $sql = "SELECT COUNT(*) FROM (" . $sql . ") AS \"countingSubquery\";";
        $result = $this->dbConn->query($sql);
        if ($result == NULL || empty($result) || empty($result[0]) || !isset($result[0]['count'])) {
            return NULL;
        }
        return $result[0]['count'];
    }
    
    /**
     * Returns exam protocols from the DB according to the number of wanted results and the start page.
     */
    function getExamProtocols($numberOfResultsWanted, $page, $lectureID, $uploadedByUserID, $borrowedByUserID) {
        $offset = $numberOfResultsWanted * $page;
        $sql = "SELECT DISTINCT ON (\"ExamProtocols\".\"ID\") * FROM \"ExamProtocols\"
        INNER JOIN \"ExamProtocolAssignedToLectures\" ON \"ExamProtocols\".\"ID\"=\"ExamProtocolAssignedToLectures\".\"examProtocolID\"";
        if ($lectureID != '') {
            $sql = "SELECT * FROM \"ExamProtocols\"
                    INNER JOIN \"ExamProtocolAssignedToLectures\" ON \"ExamProtocols\".\"ID\"=\"ExamProtocolAssignedToLectures\".\"examProtocolID\"
                    WHERE \"lectureID\"='" . $lectureID . "'";
        } else if ($uploadedByUserID != '') {
            $sql = "SELECT * FROM \"ExamProtocols\" WHERE \"uploadedByUserID\"='" . $uploadedByUserID . "'";
        } else if ($borrowedByUserID != '') {
            $sql = "SELECT * FROM \"ExamProtocols\"
                    INNER JOIN \"ExamProtocolAssignedToLectures\" ON \"ExamProtocols\".\"ID\"=\"ExamProtocolAssignedToLectures\".\"examProtocolID\"
                    INNER JOIN \"BorrowRecords\" ON \"ExamProtocolAssignedToLectures\".\"lectureID\"=\"BorrowRecords\".\"lectureID\"
                    WHERE \"borrowedByUserID\"='" . $borrowedByUserID . "'";
        }
        $sql .= " ORDER BY \"ExamProtocols\".\"ID\" DESC LIMIT " . $numberOfResultsWanted . " OFFSET " . $offset . ";";
        return $this->getExamProtocolsImpl($sql, 'examProtocolID');
    }
    
    /**
     * Executes the query to get exam protocols from the DB.
     */
    function getExamProtocolsImpl($sql, $idColumn) {
        $result = $this->dbConn->query($sql);
        $retList = array();
        for ($i = 0; $i < count($result); $i++) {
            $data = $result[$i];
            if (isset($data[$idColumn])) {
                $data['ID'] = $data[$idColumn];
            }
            $retList[] = $this->createExamProtocolFromData($data);
        }
        return $retList;
    }
    
    /**
     * Constructs an exam protocol object from the given data array.
     */
    function createExamProtocolFromData($data) {
        $ID = $data['ID'];
        $status = $data['status'];
        $uploadedByUserID = $data['uploadedByUserID'];
        $collaboratorIDs = $data['collaboratorIDs'];
        $uploadedDate = $this->dateUtil->stringToDateTime($data['uploadedDate']);
        $remark = $data['remark'];
        $examiner = $data['examiner'];
        $fileName = $data['fileName'];
        $fileSize = $data['fileSize'];
        $fileType = $data['fileType'];
        $fileExtension = $data['fileExtension'];
        return new ExamProtocol($ID, $status, $uploadedByUserID, $collaboratorIDs, $uploadedDate, $remark, $examiner, $fileName, $fileSize, $fileType, $fileExtension);
    }
    
    /**
     * Inserts the new exam protocol into the DB.
     * Returns the given exam protocol with also the ID set.
     * If the operation was not successful, FALSE will be returned.
     */
    function addExamProtocol($examProtocol) {
        $sql = "INSERT INTO \"ExamProtocols\" (\"status\", \"uploadedByUserID\", \"collaboratorIDs\", \"uploadedDate\", \"remark\", \"examiner\", \"fileName\", \"fileSize\", \"fileType\", \"fileExtension\") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $result = $this->dbConn->exec($sql, [$examProtocol->getStatus(), $examProtocol->getUploadedByUserID(), $examProtocol->getCollaboratorIDs(), $this->dateUtil->dateTimeToString($examProtocol->getUploadedDate()), $examProtocol->getRemark(), $examProtocol->getExaminer(), $examProtocol->getFileName(), $examProtocol->getFileSize(), $examProtocol->getFileType(), $examProtocol->getFileExtension()]);
        $id = $result['lastInsertId'];
        if ($id < 1) {
            return false;
        }
        $examProtocol->setID($id);
        return $examProtocol;
    }
    
    /**
     * Updates the exam protocol data in the DB.
     * Returns TRUE if the transaction was successful, FALSE otherwise.
     */
    function updateExamProtocol($examProtocol) {
        $sql = "UPDATE \"ExamProtocols\" SET \"status\"=?, \"uploadedByUserID\"=?, \"collaboratorIDs\"=?, \"uploadedDate\"=?, \"remark\"=?, \"examiner\"=?, \"fileName\"=?, \"fileSize\"=?, \"fileType\"=?, \"fileExtension\"=? WHERE \"ID\"=?;";
        $result = $this->dbConn->exec($sql, [$examProtocol->getStatus(), $examProtocol->getUploadedByUserID(), $examProtocol->getCollaboratorIDs(), $this->dateUtil->dateTimeToString($examProtocol->getUploadedDate()), $examProtocol->getRemark(), $examProtocol->getExaminer(), $examProtocol->getFileName(), $examProtocol->getFileSize(), $examProtocol->getFileType(), $examProtocol->getFileExtension(), $examProtocol->getID()]);
        $rowCount = $result['rowCount'];
        if ($rowCount <= 0) {
            return false;
        }
        return true;
    }
    
    /**
     * Deletes the exam protocol from the DB according to the given unique exam protocol ID.
     * Returns TRUE if the transaction was successful, FALSE otherwise.
     */
    function deleteExamProtocol($ID) {
        $sql = "DELETE FROM \"ExamProtocols\" WHERE \"ID\"=?;";
        $result = $this->dbConn->exec($sql, [$ID]);
        $rowCount = $result['rowCount'];
        if ($rowCount <= 0) {
            return false;
        }
        return true;
    }
    
    /**
     * Returns all lecture IDs to the given exam protocol ID or an empty list if none were found.
     */
    function getLectureIDsOfExamProtocol($examProtocolID) {
        $sql = "SELECT \"lectureID\" FROM \"ExamProtocols\"
                INNER JOIN \"ExamProtocolAssignedToLectures\" ON \"ExamProtocols\".\"ID\"=\"ExamProtocolAssignedToLectures\".\"examProtocolID\"
                WHERE \"examProtocolID\"='" . $examProtocolID . "';";
        $result = $this->dbConn->query($sql);
        $retList = array();
        for ($i = 0; $i < count($result); $i++) {
            $retList[] = $result[$i]['lectureID'];
        }
        return $retList;
    }
    
    /**
     * Deletes all exam protocol assignments of lectures according to the given exam protocol ID.
     * Returns TRUE if the transaction was successful, FALSE otherwise.
     */
    function deleteProtocolAssignments($examProtocolID) {
        $sql = "DELETE FROM \"ExamProtocolAssignedToLectures\" WHERE \"examProtocolID\"=?;";
        return $this->dbConn->exec($sql, [$examProtocolID]);
    }
}
?>
