<?php
class ExamProtocolSystem {
    private $examProtocolDao = null;
    private $dateUtil = null;
    private $fileUtil = null;
    private $hashUtil = null;

    function __construct($examProtocolDao, $dateUtil, $fileUtil, $hashUtil) {
        $this->examProtocolDao = $examProtocolDao;
        $this->dateUtil = $dateUtil;
        $this->fileUtil = $fileUtil;
        $this->hashUtil = $hashUtil;
    }
    
    /**
     * Returns all exam protocols from the DB or an empty array if none were not found.
     */
    function getAllExamProtocols($username) {
        return $this->examProtocolDao->getAllExamProtocols($username);
    }
    
    /**
     * Returns all currently borrowed exam protocol IDs of the given user or an empty array if none were not found.
     */
    function getCurrentlyBorrowedProtocolIds($user) {
        $retArray = array();
        for ($i = 0; $i < count($user->getBorrowRecords()); $i++) {
            $record = $user->getBorrowRecords()[$i];
            $examProtocolID = $record->getExamProtocolID();
            $borrowedDate = $record->getBorrowedUntilDate();
            $now = $this->dateUtil->getDateTimeNow();
            if ($this->dateUtil->isSmallerThan($now, $borrowedUntilDate)) {
                $retArray[] = $examProtocolID;
            }
        }
        return $retArray;
    }
    
    /**
     * Returns file paths of exam protocols from given exam protocol IDs.
     */
    function getFilePathsFromProtocolIDs($protocolIDs) {
        $retArray = array();
        for ($i = 0; $i < count($protocolIDs); $i++) {
            $protocol = $this->examProtocolDao->getExamProtocol($protocolIDs[$i]);
            if ($protocol != NULL) {
                $retArray[] = $protocol->getFilePath();
            } else {
                // TODO: log error
            }
        }
        return $retArray;
    }
    
    /**
     * Returns the exam protocol from the DB according to the given unique exam protocol ID or NULL if the exam protocol was not found.
     */
    function getExamProtocol($protocolID) {
        return $this->examProtocolDao->getExamProtocol($protocolID);
    }
    
    /**
     * Adds an exam protocol to the database and moves the protocol file to the protocols location with a randomly generated name.
     * Returns the just added protocol with the ID set if the operation was successful, NULL otherwise.
     */
    function addProtocol($currentUser, $remark, $examiner, $fileNameTmp, $fileNameExtension, $fileSize, $fileType) {
        $filePath = $this->fileUtil->getFullPathToBaseDirectory() . Constants::UPLOADED_PROTOCOLS_DIRECTORY . '/' . $this->hashUtil->generateRandomString() . '.' . $fileNameExtension;
        move_uploaded_file($fileNameTmp, $filePath);
        
        $status = Constants::EXAM_PROTOCOL_STATUS['unchecked'];
        $uploadedByUserID = $currentUser->getID();
        $uploadedDate = $this->dateUtil->getDateTimeNow();
        $examProtocol = new ExamProtocol(NULL, $status, $uploadedByUserID, $uploadedDate, $remark, $examiner, $filePath, $fileSize, $fileType, $fileNameExtension);
    
        $examProtocol = $this->examProtocolDao->addExamProtocol($examProtocol);
        if ($examProtocol == false) {
            return NULL;
        }
        return $examProtocol;
    }
    
    /**
     * Updates the exam protocol in the database with the given data.
     * Returns TRUE if the operation was successful, FALSE otherwise.
     */
    function updateExamProtocol($examProtocolID, $remark, $examiner) {
        $examProtocol = $this->examProtocolDao->getExamProtocol($examProtocolID);
        if ($examProtocol == NULL) {
            // TODO log error
            return false;
        }
        $examProtocol->setRemark($remark);
        $examProtocol->setExaminer($examiner);
        return $this->examProtocolDao->updateExamProtocol($examProtocol);
    }
    
    /**
     * Updates the exam protocol in the database with the given data.
     * Returns TRUE if the operation was successful, FALSE otherwise.
     */
    function updateExamProtocolFully($examProtocolID, $status, $uploadedByUserID, $uploadedDate, $remark, $examiner, $filePath, $fileSize, $fileType, $fileExtension) {
        $examProtocol = $this->examProtocolDao->getExamProtocol($examProtocolID);
        if ($examProtocol == NULL) {
            // TODO log error
            return false;
        }
        $examProtocol->setStatus($status);
        $examProtocol->setUploadedByUserID($uploadedByUserID);
        $examProtocol->setUploadedDate($uploadedDate);
        $examProtocol->setRemark($remark);
        $examProtocol->setExaminer($examiner);
        $examProtocol->setFilePath($filePath);
        $examProtocol->setFileSize($fileSize);
        $examProtocol->setFileType($fileType);
        $examProtocol->setFileExtension($fileExtension);
        return $this->examProtocolDao->updateExamProtocol($examProtocol);
    }
    
    /**
     * Returns the number of exam protocols that are in the DB.
     */
    function getNumberOfExamProtocolsTotal($lectureID, $uploadedByUserID, $borrowedByUserID) {
        return $this->examProtocolDao->getNumberOfExamProtocolsTotal($lectureID, $uploadedByUserID, $borrowedByUserID);
    }
    
    /**
     * Returns exam protocols from the DB according to the number of wanted results and the start page.
     */
    function getExamProtocols($numberOfResultsWanted, $page, $lectureID, $uploadedByUserID, $borrowedByUserID) {
        return $this->examProtocolDao->getExamProtocols($numberOfResultsWanted, $page, $lectureID, $uploadedByUserID, $borrowedByUserID);
    }
    
    /**
     * Returns all lecture IDs to the given exam protocol ID or an empty list if none were found.
     */
    function getLectureIDsOfExamProtocol($examProtocolID) {
        return $this->examProtocolDao->getLectureIDsOfExamProtocol($examProtocolID);
    }
    
    /**
     * Deletes all exam protocol assignments of a lecture according to the given exam protocol ID.
     * Returns TRUE if the transaction was successful, FALSE otherwise.
     */
    function deleteAllProtocolAssignments($examProtocolID) {
        return $this->examProtocolDao->deleteProtocolAssignments($examProtocolID);
    }
}
?>
