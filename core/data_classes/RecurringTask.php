<?php
class RecurringTask {
    private $ID;
    private $name;
    private $lastRunDate;
    private $periodTimeframe;
    private $periodUnit;

    function __construct($ID, $name, $lastRunDate, $periodTimeframe, $periodUnit) {
        $this->ID = $ID;
        $this->name = $name;
        $this->lastRunDate = $lastRunDate;
        $this->periodUnit = $periodUnit;
        $this->periodTimeframe = $periodTimeframe;
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

	public function getLastRunDate(){
		return $this->lastRunDate;
	}

	public function setLastRunDate($lastRunDate){
		$this->lastRunDate = $lastRunDate;
	}

	public function getPeriodTimeframe(){
		return $this->periodTimeframe;
	}

	public function setPeriodTimeframe($periodTimeframe){
		$this->periodTimeframe = $periodTimeframe;
	}

	public function getPeriodUnit(){
		return $this->periodUnit;
	}

	public function setPeriodUnit($periodUnit){
		$this->periodUnit = $periodUnit;
	}
}
?>
