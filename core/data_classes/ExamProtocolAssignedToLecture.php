<?php
class ExamProtocolAssignedToLecture {
    private $ID;
    private $lectureID;
    private $examProtocolID;

    function __construct($ID, $lectureID, $examProtocolID) {
        $this->ID = $ID;
        $this->lectureID = $lectureID;
        $this->examProtocolID = $examProtocolID;
    }
    
    public function getID(){
        return $this->ID;
    }

    public function setID($ID){
        $this->ID = $ID;
    }

    public function getLectureID(){
        return $this->lectureID;
    }

    public function setLectureID($lectureID){
        $this->lectureID = $lectureID;
    }

    public function getExamProtocolID(){
        return $this->examProtocolID;
    }

    public function setExamProtocolID($examProtocolID){
        $this->examProtocolID = $examProtocolID;
    }
}
?>
