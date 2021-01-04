<?php
class RecurringTasksSystem {
    private $recurringTasksDao = null;
    private $dateUtil = null;
    private $fileUtil = null;
    private $log = null;
    private $lastResults = [];

    function __construct($recurringTasksDao, $dateUtil, $fileUtil) {
        $this->recurringTasksDao = $recurringTasksDao;
        $this->dateUtil = $dateUtil;
        $this->fileUtil = $fileUtil;
    }

    /**
     * Set the log to enable error logging.
     */
    function setLog($log) {
        $this->log = $log;
    }
    
    /**
     * Fetches all recurring tasks from the DB.
     * If the last run is too long in the past, they are run.
     * Saves the results to an internal list of the tasks and when they were run last.
     */
    function runRecurringTasks() {
        $recurringTasksList = $this->recurringTasksDao->getAllRecurringTasks();
        for ($i = 0; $i < count($recurringTasksList); $i++) {
            $recurringTask = $recurringTasksList[$i];
            $periodTimeframe = $recurringTask->getPeriodTimeframe();
            $periodUnit = $recurringTask->getPeriodUnit();
            $nextRunDate = $this->dateUtil->addToDateTime($recurringTask->getLastRunDate(), $periodTimeframe, $periodUnit);
            $now = $this->dateUtil->getDateTimeNow();
            $status = 'NOT_TO_BE_RUN';
            if ($this->dateUtil->isSmallerThan($nextRunDate, $now)) {
                $status = $this->runTask($recurringTask->getName());
                if ($status == 'SUCCESS') {
                    $recurringTask->setLastRunDate($now);
                    $result = $this->recurringTasksDao->updateRecurringTask($recurringTask);
                    if ($result == false) {
                        $status = 'FAILED_TO_UPDATE_LAST_RUN_TIME';
                    }
                }
            }
            $nextRunDate = $this->dateUtil->addToDateTime($recurringTask->getLastRunDate(), $periodTimeframe, $periodUnit);
            $this->lastResults[] = [$recurringTask->getID(), $recurringTask->getName(), $recurringTask->getLastRunDate(), $nextRunDate, $status];
        }
    }
    
    /**
     * Returns the results of the last run.
     */
    function getLastResults() {
        return $this->lastResults;
    }
    
    /**
     * Sets up a task to be run by setting its last run time far back in time.
     * Returns TRUE if the process was successful, FALSE otherwise.
     */
    function setUpTaskForRun($recurringTaskID) {
        $recurringTask = $this->recurringTasksDao->getRecurringTask($recurringTaskID);
        if ($recurringTask != NULL) {
            $recurringTask->setLastRunDate($this->dateUtil->getDateTimeFarInThePast());
            return $this->recurringTasksDao->updateRecurringTask($recurringTask);
        }
        return false;
    }
    
    /**
     * Run a recurring task.
     */
    function runTask($taskName) {
        // TODO clean up old log files, other recurring tasks...
        // TODO <br><br>max 100000 log entries, cleanup<br><br><br>delete outdated borrow records<br><br><br>delete protocols that are marked for deletion regularly<br><br><br>clean up the zip files folder<br><br><br>what else are recurring tasks?<br><br><br>

        $retVal = 'SUCCESS';
        if ($taskName == Constants::RECURRING_TASKS['cleanDownloadsDirectory']) {
            $ppiRootDirectory = $this->fileUtil->getFullPathToBaseDirectory();
            $files = glob($ppiRootDirectory . Constants::TMP_ZIP_FILES_DIRECTORY . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    if ($this->fileUtil->strEndsWith($file, Constants::ALLOWED_FILE_EXTENSION_DOWNLOAD[0])) {
                        unlink($file);
                    } else {
                        $retVal = 'WRONG_FILE_EXTENSION';
                        $this->log->error(static::class . '.php', 'Recurring task did not run successfully! Can not delete file that is not a zip file: ' . $file);
                    }
                }
            }
        }
        if ($taskName == Constants::RECURRING_TASKS['removeExpiredBorrowRecords']) {
            $result = $this->recurringTasksDao->removeExpiredBorrowRecords();
            if (!$result) {
                $retVal = 'FAILED';
                $this->log->error(static::class . '.php', 'Recurring task did not run successfully! Can not remove expired borrow records!');
            }
        }
        if ($taskName == Constants::RECURRING_TASKS['cleanupLogs']) {
            $result = $this->recurringTasksDao->cleanupLogs();
            if (!$result) {
                $retVal = 'FAILED';
                $this->log->error(static::class . '.php', 'Recurring task did not run successfully! Can not clean up logs!');
            }
        }
        if ($taskName == Constants::RECURRING_TASKS['removeToBeDeletedProtocols']) {
            // TODO get these protocols first to delete the pdf/txt files
            // also delete assignments to lectures of a protocol
            $result = $this->recurringTasksDao->removeToBeDeletedProtocols();
            if (!$result) {
                $retVal = 'FAILED';
                $this->log->error(static::class . '.php', 'Recurring task did not run successfully! Can not remove to be deleted protocols!');
            }
        }
        if ($taskName == Constants::RECURRING_TASKS['removeToBeDeletedUsers']) {
            $result = $this->recurringTasksDao->removeToBeDeletedUsers();
            if (!$result) {
                $retVal = 'FAILED';
                $this->log->error(static::class . '.php', 'Recurring task did not run successfully! Can not remove to be deleted users!');
            }
        }
        if ($taskName == Constants::RECURRING_TASKS['removeToBeDeletedLectures']) {
            // TODO remove the lecture completely and all protocols that are only assigned to this very lecture
            $result = $this->recurringTasksDao->removeToBeDeletedLectures();
            if (!$result) {
                $retVal = 'FAILED';
                $this->log->error(static::class . '.php', 'Recurring task did not run successfully! Can not remove to be deleted lectures!');
            }
        }
        if ($taskName == Constants::RECURRING_TASKS['addTokensToAllUsers']) {
            // TODO fix this
            $result = $this->recurringTasksDao->addTokensToAllUsers(Constants::NUMBER_OF_TOKENS_TO_ADD_TO_ALL_PER_SEMESTER);
            if (!$result) {
                $retVal = 'FAILED';
                $this->log->error(static::class . '.php', 'Recurring task did not run successfully! Can not add tokens to all users!');
            }
        }
        return $retVal;
    }
}
?>
