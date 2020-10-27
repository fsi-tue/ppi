<?php
class BorrowRecord {
    private $ID;
    private $lectureID;
    private $borrowedByUserID;
    private $borrowedUntilDate;

    function __construct($ID, $lectureID, $borrowedByUserID, $borrowedUntilDate) {
        $this->ID = $ID;
        $this->lectureID = $lectureID;
        $this->borrowedByUserID = $borrowedByUserID;
        $this->borrowedUntilDate = $borrowedUntilDate;
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

    public function getBorrowedByUserID(){
        return $this->borrowedByUserID;
    }

    public function setBorrowedByUserID($borrowedByUserID){
        $this->borrowedByUserID = $borrowedByUserID;
    }

    public function getBorrowedUntilDate(){
        return $this->borrowedUntilDate;
    }

    public function setBorrowedUntilDate($borrowedUntilDate){
        $this->borrowedUntilDate = $borrowedUntilDate;
    }
}
?>
