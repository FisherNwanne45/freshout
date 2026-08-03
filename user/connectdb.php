<?php
include_once dirname(__DIR__) . '/config.php';

$_connectPort = isset($dbport) && (int)$dbport > 0 ? (int)$dbport : 3306;
$connection = mysqli_connect($servername, $username, $password, $dbname, $_connectPort);
if (!$connection) {
    die("Database Connection Failed: " . mysqli_connect_error());
}