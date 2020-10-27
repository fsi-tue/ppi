<?php
class PostgresDBConn {
    //https://www.howtoforge.de/anleitung/wie-man-postgresql-und-phppgadmin-auf-ubuntu-1804-lts-installiert/
    
    private $pdo = null;
    private $mode = null; // can be 'INDICES', 'COLUMN_NAMES' or 'BOTH'
    private $log = null;

    function __construct($host, $port, $databaseName, $user, $password, $mode) {
        $dsn = 'pgsql:host=' . $host . ';port=' . $port . ';dbname=' . $databaseName . ';user=' . $user . ';password=' . $password;
        try {
            $this->pdo = new PDO($dsn);
            if (!$this->pdo){
                echo 'Could not connect to Postgres DB!';
            }
        } catch (PDOException $e){
            echo $e->getMessage();
        }
        $this->mode = $mode;
    }
    
    function setLog($log) {
        $this->log = $log;
    }
    
    function beginTransaction() {
        // TODO: make transactions work
        //$stmt = $this->pdo->prepare('BEGIN TRANSACTION;')->execute();
    }
    
    function commitTransaction() {
        // TODO: make transactions work
        //$stmt = $this->pdo->prepare('COMMIT;')->execute();
    }
    
    function rollbackTransaction() {
        // TODO: make transactions work
        //$stmt = $this->pdo->prepare('ROLLBACK;')->execute();
    }
    
    function exec($sqlString, $values) {
        $stmt = $this->pdo->prepare($sqlString);
        $stmt->execute($values);
        if (!($this->pdo->errorInfo()[0] == '00000')) {
            $this->logPdoError($this->pdo->errorInfo());
        }
        $retVal = ['lastInsertId'=>$this->pdo->lastInsertId(), 'rowCount'=>$stmt->rowCount()];
        return $retVal;
    }
    
    function query($sqlString) {
        $retArray = array();
        $query = $this->pdo->query($sqlString);
        if (!($this->pdo->errorInfo()[0] == '00000')) {
            $this->logPdoError($this->pdo->errorInfo());
        }
        if ($query) {
            foreach ($query as $row) {
                $f = array_filter(
                                    $row,
                                    function ($key) {
                                        if ($this->mode == 'INDICES') {
                                            return is_numeric($key);
                                        }
                                        if ($this->mode == 'COLUMN_NAMES') {
                                            return !is_numeric($key);
                                        }
                                        return true;
                                    },
                                    ARRAY_FILTER_USE_KEY
                                );
                $retArray[] = $f;
            }
            return $retArray;
        }
        return false;
    }
    
    function logPdoError($errorInfo) {
        $error = '[';
        $error .= 'SQLSTATE error code: ' . $errorInfo[0];
        $error .= ', Driver-specific error code: ' . $errorInfo[1];
        $error .= ', Driver-specific error message: ' . $errorInfo[2];
        $error .= ']';
        if ($this->log != null) {
            $this->log->critical(static::class . '.php', 'Database returned error: ' . $error);
        } else {
            echo static::class . '.php' . 'Database returned error: ' . $error;
        }
    }
}
?>
