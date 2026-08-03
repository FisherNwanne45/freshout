<?php 
error_reporting(0);

// Ensure mysqli connection handle is available to admin pages that rely on $conn.
if ((!isset($conn) || !($conn instanceof mysqli))) {
    try {
        require_once dirname(__DIR__, 2) . '/config.php';
    } catch (Throwable $e) {
    }
    if ((!isset($conn) || !($conn instanceof mysqli)) && isset($GLOBALS['conn']) && ($GLOBALS['conn'] instanceof mysqli)) {
        $conn = $GLOBALS['conn'];
    }
}

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    // last request was more than 30 minutes ago
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy(); 
header('Location: login.php');
echo "Timeout";	// destroy session data in storage
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
?>