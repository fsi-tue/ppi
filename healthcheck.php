<?php

if (php_sapi_name() !== 'cli') {
    http_response_code(400);
    echo('This script can only be run from the command line.');
    exit(1);
}

$HEALTHCHECK_TABLE = "HealthCheck";


// Include all constants
require_once('core/constants/Passwords.php');
require_once('core/constants/Constants.php');

// Include the database connection class
require_once('core/lib_classes/MySqlDBConn.php');
$db = new DBConn(Constants::DATABASE_HOST, Constants::DATABASE_PORT, Constants::DATABASE_DB_NAME, Constants::DATABASE_USER, Constants::DATABASE_PASSWORD, 'COLUMN_NAMES');


// Drop table $HEALTHCHECK_TABLE if it exists
$sql = "DROP TABLE IF EXISTS $HEALTHCHECK_TABLE;";
$db->exec($sql, []);

// Create table $HEALTHCHECK_TABLE
$sql = "CREATE TABLE $HEALTHCHECK_TABLE (
    check_column VARCHAR(30) NOT NULL
);";
$db->exec($sql, []);

// Insert a value
$insertValue = "initialValue";
$sql = "INSERT INTO $HEALTHCHECK_TABLE (check_column) VALUES (?);";
$db->exec($sql, [$insertValue]);

// Update the value
$updatedValue = "updatedValue";
$sql = "UPDATE $HEALTHCHECK_TABLE SET check_column = ? WHERE check_column = ?;";
$result = $db->exec($sql, [$updatedValue, $insertValue]);
if ($result['rowCount'] !== 1) {
    echo("Error updating data or data didn't match.");
    exit(1);
}

// Delete the row
$sql = "DELETE FROM $HEALTHCHECK_TABLE WHERE check_column = ?;";
$result = $db->exec($sql, [$updatedValue]);
if ($result['rowCount'] !== 1) {
    echo("Error deleting data or data didn't match.");
    exit(1);
}

// Drop the table
$sql = "DROP TABLE $HEALTHCHECK_TABLE;";
$db->exec($sql, []);


// Now check if tables Users and Lectures exist and have data in them
$sql = "SELECT COUNT(*) AS count FROM Lectures";
$result = $db->query($sql);

if (!$result || $result[0]['count'] <= 0) {
    echo("Lectures table has no rows.");
    exit(1);
}

$sql = "SELECT COUNT(*) AS count FROM Users";
$result = $db->query($sql);

if (!$result || $result[0]['count'] <= 0) {
    echo("Users table has no rows.");
    exit(1);
}


echo "Health check passed!";
exit(0);

?>
