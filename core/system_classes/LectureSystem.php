<?php
class LectureSystem {
    private $lectureDao = null;
    private $email = null;
    private $log = null;

    function __construct($lectureDao, $email) {
        $this->lectureDao = $lectureDao;
        $this->email = $email;
    }

    /**
     * Set the log to enable error logging.
     */
    function setLog($log) {
        $this->log = $log;
    }
    
    /**
     * Returns the lecture from the DB according to the given unique lecture ID or NULL if the lecture was not found.
     */
    function getLecture($ID) {
        return $this->lectureDao->getLecture($ID, false);
    }
    
    /**
     * Returns all lectures from the DB with the given status or an empty array if none were not found.
     */
    function getAllLecturesWithStatus($status) {
        return $this->lectureDao->getAllLecturesWithStatus($status);
    }
    
    /**
     * Returns all lectures from the DB or an empty array if none were not found.
     */
    function getAllLectures() {
        return $this->lectureDao->getAllLectures();
    }
    
    /**
     * Returns all lectures that have accepted protocols assigned from the DB or an empty array if none were not found.
     */
    function getAllLecturesWithAcceptedProtocols() {
        return $this->lectureDao->getAllLecturesWithAcceptedProtocols();
    }
    
    /**
     * Inserts the new lecture into the DB.
     * Returns the given lecture with also the ID set.
     * If the operation was not successful, FALSE will be returned.
     */
    function addLecture($name) {
        $lecture = new Lecture(NULL, $name, Constants::LECTURE_STATUS['ok'], array());
        return $this->lectureDao->addLecture($lecture);
    }
    
    /**
     * Updates the data of the lecture in the DB.
     * This method does not change the assigned exam protocols of the lecture.
     * Returns the given lecture with also the ID set.
     * If the operation was not successful, FALSE will be returned.
     */
    function updateLecture($lectureID, $name, $status) {
        $lecture = $this->lectureDao->getLecture($lectureID, false);
        if ($lecture == NULL) {
            $this->log->error(static::class . '.php', 'Lecture to ID ' . $lectureID . ' not found!');
            return false;
        }
        $lecture->setName($name);
        $lecture->setStatus($status);
        return $this->lectureDao->updateLecture($lecture);
    }
    
    /**
     * Returns all protocol IDs of the given lecture from the DB or an empty array if none were found.
     */
    function getAllProtocolIDsOfLecture($lectureID) {
        $retArray = array();
        $lecture = $this->lectureDao->getLecture($lectureID, true);
        if ($lecture != NULL) {
            $assignedExamProtocols = $lecture->getAssignedExamProtocols();
            for ($i = 0; $i < count($assignedExamProtocols); $i++) {
                $retArray[] = $assignedExamProtocols[$i]->getExamProtocolID();
            }
        } else {
            $this->log->error(static::class . '.php', 'Lecture to ID ' . $lectureID . ' not found!');
        }
        return $retArray;
    }
    
    /**
     * Adds a list of protocols IDs to the given lecture ID.
     * Returns TRUE if the transaction was successful, FALSE otherwise.
     */
    function addProtocolIDsToLecture($lectureIDs, $examProtocolID) {
        $lectureIDs = array_unique($lectureIDs);
        for ($i = 0; $i < count($lectureIDs); $i++) {
            $lecture = $this->lectureDao->getLecture($lectureIDs[$i], false);
            if ($lecture != NULL) {
                $assignedExamProtocols = $lecture->getAssignedExamProtocols();
                $examProtocolAssignedToLecture = new ExamProtocolAssignedToLecture(NULL, $lectureIDs[$i], $examProtocolID);
                $assignedExamProtocols[] = $examProtocolAssignedToLecture;
                $lecture->setAssignedExamProtocols($assignedExamProtocols);
                $result = $this->lectureDao->updateLecture($lecture);
                if ($result == false) {
                    $this->log->error(static::class . '.php', 'Error on updating lecture data on the DB! The DB is inconsistent now! Best delete the lecture with the ID ' . $lectureIDs[$i] . ' and all protocols assigned to it!');
                    return false;
                }
            } else {
                $this->log->error(static::class . '.php', 'Lecture to ID ' . $lectureIDs[$i] . ' not found!');
            }
        }
        return true;
    }
    
    /**
     * Reports via mail to the admins that the lecture has outdated protocols.
     */
    function reportLectureHasOutdatedProtocols($lectureID, $reportingUsername) {
        if (!is_numeric($lectureID)) {    
            $this->log->error(static::class . '.php', 'Lecture ID is not numeric!');
            return false;
        }
        $lecture = $this->getLecture($lectureID);
        if ($lecture == NULL) {
            $this->log->error(static::class . '.php', 'Lecture to ID ' . $lectureID . ' not found! Can not report as outdated!');
            return false;
        }
        $message = 'The user ' . $reportingUsername . ' has reported that the lecture ' . $lecture->getName() . ' with ID ' . $lectureID . ' has outdated protocols assigned. Please verify this and handle the outdated protocols (if any).';
        $this->email->send(Constants::EMAIL_ADMIN, 'Lecture reported as outdated in PPI', $message);
        return true;
    }
}
?>
