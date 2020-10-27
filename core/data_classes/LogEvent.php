<?php
class LogEvent {
    private $ID;
    private $date;
    private $username;
    private $level;
    private $remark;
    private $origin;

    function __construct($ID, $date, $username, $level, $remark, $origin) {
        $this->ID = $ID;
        $this->date = $date;
        $this->username = $username;
        $this->level = $level;
        $this->remark = $remark;
        $this->origin = $origin;
    }
    
    public function getID(){
        return $this->ID;
    }

    public function setID($ID){
        $this->ID = $ID;
    }

    public function getDate(){
        return $this->date;
    }

    public function setDate($date){
        $this->date = $date;
    }

    public function getUsername(){
        return $this->username;
    }

    public function setUsername($username){
        $this->username = $username;
    }

    public function getLevel(){
        return $this->level;
    }

    public function setLevel($level){
        $this->level = $level;
    }

    public function getRemark(){
        return $this->remark;
    }

    public function setRemark($remark){
        $this->remark = $remark;
    }

    public function getOrigin(){
        return $this->origin;
    }

    public function setOrigin($origin){
        $this->origin = $origin;
    }
}
?>
