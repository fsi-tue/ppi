<?php
class TestUtil {
    private $log = null;
    private $dbConn = null;
    private $databaseUtility = null;
    private $objectsToTest = array();
    private $objectsToTestNames = array();

    function __construct($log, $dbConn) {
        $this->log = $log;
        $this->dbConn = $dbConn;
        $this->databaseUtility = new PostgresDBConnDatabaseUtility($this->dbConn);
        $this->databaseUtility->recreateDatabase();
        
        require_once(__DIR__ . '/ExamProtocolDaoTest.php');
        $this->objectsToTest[] = new ExamProtocolDaoTest(new ExamProtocolDao($this->dbConn, new DateUtil()));
        $this->objectsToTestNames[] = 'ExamProtocolDaoTest';
        
        require_once(__DIR__ . '/LectureDaoTest.php');
        $this->objectsToTest[] = new LectureDaoTest(new LectureDao($this->dbConn));
        $this->objectsToTestNames[] = 'LectureDaoTest';
    }
    
    function getTestNames() {
        return $this->objectsToTestNames;
    }
    
    function runAllTests() {
        $retArray = array();
        for ($i = 0; $i < count($this->objectsToTest); $i++) {
            $testObject = $this->objectsToTest[$i];
            $success = $testObject->test();
            if (!$success) {
                $this->log->warning(get_class($testObject) . '.php', 'Unit test failed!');
            }
            if ($success == true) {
                $retArray[] = 'success';
            } else {
                $retArray[] = '<div style="background-color: red">failed<div>';
            }
        }
        return $retArray;
    }
    
    function listTableNames() {
        $tables = $this->databaseUtility->getAllDatabaseTableNames();
        prettyPrint($tables);
    }
}
?>
