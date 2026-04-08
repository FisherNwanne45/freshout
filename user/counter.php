<?php
include_once dirname(__DIR__) . '/config.php';

$rowcount = 0;

// Counter is non-critical UI data; never allow failures here to break page rendering.
mysqli_report(MYSQLI_REPORT_OFF);

try {
  $con = @mysqli_connect($servername, $username, $password, $dbname);
  if (!$con) {
    return;
  }

  $me = isset($row['uname']) ? (string)$row['uname'] : '';
  if ($me === '') {
    mysqli_close($con);
    return;
  }

  $safeMe = mysqli_real_escape_string($con, $me);
  $sql = "SELECT * FROM message WHERE reci_name='" . $safeMe . "'";
  $result = mysqli_query($con, $sql);
  if ($result) {
    $rowcount = mysqli_num_rows($result);
    mysqli_free_result($result);
  }

  mysqli_close($con);
} catch (Throwable $e) {
  // Swallow errors to keep dependent pages available.
}
?>