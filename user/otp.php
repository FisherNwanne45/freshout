<?php
session_start();

if (!isset($_SESSION['acc_no'])) {
    header('Location: login.php');
    exit();
}

header('Location: send.php');
exit();
