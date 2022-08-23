<?php
class TestUtil {
    private $log = null;
    private $dbConn = null;
    private $dateUtil = null;
    private $fileUtil = null;
    private $databaseUtility = null;
    private $objectsToTest = array();
    private $objectsToTestNames = array();
    
    function __construct($log, $dbConn, $dateUtil, $fileUtil) {
        $this->log = $log;
        $this->dbConn = $dbConn;
        $this->dateUtil = $dateUtil;
        $this->fileUtil = $fileUtil;
        
        $this->objectsToTestNames[] = 'Static Code Analysis with PHPStan';
        
        /*require_once(__DIR__ . '/ExamProtocolDaoTest.php');
        $this->objectsToTest[] = new ExamProtocolDaoTest(new ExamProtocolDao($this->dbConn, $this->dateUtil));
        $this->objectsToTestNames[] = 'ExamProtocolDaoTest';
        
        require_once(__DIR__ . '/LectureDaoTest.php');
        $this->objectsToTest[] = new LectureDaoTest(new LectureDao($this->dbConn));
        $this->objectsToTestNames[] = 'LectureDaoTest';*/
    }
    
    function getTestNames() {
        return $this->objectsToTestNames;
    }
    
    function runAllTests() {
        $this->databaseUtility = new DBConnDatabaseUtility($this->dbConn, $this->dateUtil);
        $this->databaseUtility->recreateDatabase();
        
        $retArray = array();
        $retArray[] = $this->runStaticCodeAnalysis();
        for ($i = 0; $i < count($this->objectsToTest); $i++) {
            $testObject = $this->objectsToTest[$i];
            $success = $testObject->test();
            if (!$success) {
                $this->log->error(get_class($testObject) . '.php -> ' . static::class . '.php', 'Unit test failed!');
            }
            if ($success == true) {
                $retArray[] = '<div style="background-color: ' . Constants::SUCCESS_COLOR . '">success<div>';
            } else {
                $retArray[] = '<div style="background-color: ' . Constants::FAILED_COLOR . '">failed<div>';
            }
        }
        return $retArray;
    }
    
    function runStaticCodeAnalysis() {
        require_once(__DIR__ . '/StaticCodeAnalysis.php');
        $ppiRootDirectory = $this->fileUtil->getFullPathToBaseDirectory();
        $phpstanDirectory = __DIR__ . '/code_analysis/';
        $staticCodeAnalysis = new StaticCodeAnalysis($this->fileUtil);
        $result = $staticCodeAnalysis->analyze($ppiRootDirectory, $phpstanDirectory);
        if (strpos($result, '[OK]') !== false) {
            $result = '<div style="background-color: ' . Constants::SUCCESS_COLOR . '">' . $result . '<div>';
        } else {
            $result = '<div style="background-color: ' . Constants::FAILED_COLOR . '">' . $result . '<div>';
        }
        return $result;
    }
    
    function listTableNames() {
        $tables = $this->databaseUtility->getAllDatabaseTableNames();
        debugPrint($tables);
    }
}
?>
