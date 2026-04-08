<?php
include_once dirname(__DIR__) . '/config.php';

$connection = mysqli_connect($servername, $username, $password);
if (!$connection){
    die("Database Connection Failed" . mysqli_connect_error());
}
$select_db = mysqli_select_db($connection, $dbname);
if (!$select_db){
    die("Database Selection Failed" . mysqli_error($connection));
}