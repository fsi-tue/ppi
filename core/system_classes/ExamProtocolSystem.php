<?php
class ExamProtocolSystem {
    private $examProtocolDao = null;
    private $dateUtil = null;
    private $fileUtil = null;
    private $hashUtil = null;
    private $log = null;

    function __construct($examProtocolDao, $dateUtil, $fileUtil, $hashUtil) {
        $this->examProtocolDao = $examProtocolDao;
        $this->dateUtil = $dateUtil;
        $this->fileUtil = $fileUtil;
        $this->hashUtil = $hashUtil;
    }

    /**
     * Set the log to enable error logging.
     */
    function setLog($log) {
        $this->log = $log;
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
            $borrowedUntilDate = $record->getBorrowedUntilDate();
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
        $basePath = $this->fileUtil->getFullPathToBaseDirectory();
        $retArray = array();
        for ($i = 0; $i < count($protocolIDs); $i++) {
            $protocol = $this->examProtocolDao->getExamProtocol($protocolIDs[$i]);
            if ($protocol != NULL) {
                $retArray[] = $basePath . 'exam_protocols/protocols/' . $protocol->getFilePath();
            } else {
                $this->log->error(static::class . '.php', 'Protocol to ID ' . $protocolIDs[$i] . ' not found!');
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
    function addProtocol($currentUser, $collaboratorIDs, $remark, $examiner, $fileNameTmp, $fileNameExtension, $fileSize, $fileType) {
        /**
         * The full path will be generated when downloading the file. Only the filename needs to be stored in the database.
        */
        $fileName = $this->hashUtil->generateRandomString() . '.' . $fileNameExtension;
        $filePath = $this->fileUtil->getFullPathToBaseDirectory() . Constants::UPLOADED_PROTOCOLS_DIRECTORY . '/' . $fileName;
	move_uploaded_file($fileNameTmp, $filePath);

        $status = Constants::EXAM_PROTOCOL_STATUS['unchecked'];
        $uploadedByUserID = $currentUser->getID();
        $uploadedDate = $this->dateUtil->getDateTimeNow();
        $examProtocol = new ExamProtocol(NULL, $status, $uploadedByUserID, $collaboratorIDs, $uploadedDate, $remark, $examiner, $fileName, $fileSize, $fileType, $fileNameExtension);
    
        $examProtocol = $this->examProtocolDao->addExamProtocol($examProtocol);
        if ($examProtocol == false) {
            $this->log->error(static::class . '.php', 'Error on adding exam protocol!');
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
            $this->log->error(static::class . '.php', 'Protocol to ID ' . $examProtocolID . ' not found!');
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
    function updateExamProtocolFully($examProtocolID, $collaboratorIDs, $status, $uploadedByUserID, $uploadedDate, $remark, $examiner, $filePath, $fileSize, $fileType, $fileExtension) {
        $examProtocol = $this->examProtocolDao->getExamProtocol($examProtocolID);
        if ($examProtocol == NULL) {
            $this->log->error(static::class . '.php', 'Protocol to ID ' . $examProtocolID . ' not found!');
            return false;
        }
        $examProtocol->setStatus($status);
        $examProtocol->setUploadedByUserID($uploadedByUserID);
        $examProtocol->setCollaboratorIDs($collaboratorIDs);
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
