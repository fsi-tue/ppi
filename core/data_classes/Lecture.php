<?php
class Lecture {
    private $ID;
    private $name;
    private $status;
    private $assignedExamProtocols;

    function __construct($ID, $name, $status, $assignedExamProtocols) {
        $this->ID = $ID;
        $this->name = $name;
        $this->status = $status;
        $this->assignedExamProtocols = $assignedExamProtocols;
    }
    
    public function getID(){
        return $this->ID;
    }

    public function setID($ID){
        $this->ID = $ID;
    }

    public function getName(){
        return $this->name;
    }

    public function setName($name){
        $this->name = $name;
    }

    public function getStatus(){
        return $this->status;
    }

    public function setStatus($status){
        $this->status = $status;
    }

    public function getAssignedExamProtocols(){
        return $this->assignedExamProtocols;
    }

    public function setAssignedExamProtocols($assignedExamProtocols){
        $this->assignedExamProtocols = $assignedExamProtocols;
    }
}
?>
