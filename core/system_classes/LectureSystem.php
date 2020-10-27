<?php
class LectureSystem {
    private $lectureDao = null;

    function __construct($lectureDao) {
        $this->lectureDao = $lectureDao;
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
    function addLecture($longName, $shortName, $field) {
        $lecture = new Lecture(NULL, $longName, $shortName, $field, array());
        return $this->lectureDao->addLecture($lecture);
    }
    
    /**
     * Updates the data of the lecture in the DB.
     * This method does not change the assigned exam protocols of the lecture.
     * Returns the given lecture with also the ID set.
     * If the operation was not successful, FALSE will be returned.
     */
    function updateLecture($lectureID, $longName, $shortName, $field) {
        $lecture = $this->lectureDao->getLecture($lectureID, false);
        if ($lecture == NULL) {
            // TODO log error
            return false;
        }
        $lecture->setLongName($longName);
        $lecture->setShortName($shortName);
        $lecture->setField($field);
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
            //TODO: log error
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
            $assignedExamProtocols = $lecture->getAssignedExamProtocols();
            $examProtocolAssignedToLecture = new ExamProtocolAssignedToLecture(NULL, $lectureIDs[$i], $examProtocolID);
            $assignedExamProtocols[] = $examProtocolAssignedToLecture;
            $lecture->setAssignedExamProtocols($assignedExamProtocols);
            $result = $this->lectureDao->updateLecture($lecture);
            if ($result == false) {
                // TODO log error, db is inconsistent now
                // best delete lecture and all protocols assigned to it now
                return false;
            }
        }
        return true;
    }
}
?>
