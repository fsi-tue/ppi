<?php
class Lecture {
    private $ID;
    private $longName;
    private $shortName;
    private $field;
    private $assignedExamProtocols;

    function __construct($ID, $longName, $shortName, $field, $assignedExamProtocols) {
        $this->ID = $ID;
        $this->longName = $longName;
        $this->shortName = $shortName;
        $this->field = $field;
        $this->assignedExamProtocols = $assignedExamProtocols;
    }
    
    public function getID(){
        return $this->ID;
    }

    public function setID($ID){
        $this->ID = $ID;
    }

    public function getLongName(){
        return $this->longName;
    }

    public function setLongName($longName){
        $this->longName = $longName;
    }

    public function getShortName(){
        return $this->shortName;
    }

    public function setShortName($shortName){
        $this->shortName = $shortName;
    }

    public function getField(){
        return $this->field;
    }

    public function setField($field){
        $this->field = $field;
    }

    public function getAssignedExamProtocols(){
        return $this->assignedExamProtocols;
    }

    public function setAssignedExamProtocols($assignedExamProtocols){
        $this->assignedExamProtocols = $assignedExamProtocols;
    }
}
?>
