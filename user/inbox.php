<?php
session_start();
include_once('session.php');
require_once 'class.user.php';
if (!isset($_SESSION['acc_no'])) {

  header("Location: login.php");
  exit();
} elseif (!isset($_SESSION['pin'])) {

  header("Location: passcode.php");
  exit();
}
$user_home = new USER();

$stmt = $user_home->runQuery("SELECT * FROM account WHERE acc_no=:acc_no");
$stmt->execute(array(":acc_no" => $_SESSION['acc_no']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$site = $row['site'];

$stct = $user_home->runQuery("SELECT * FROM site WHERE id = '20'");
$stct->execute();
$rowp = $stct->fetch(PDO::FETCH_ASSOC);


include_once('counter.php');

// Load all messages for this user
$_inboxUser = $row['uname'] ?? '';
$allMsgs = $user_home->runQuery("SELECT message.*, account.uname FROM message INNER JOIN account ON message.reci_name = account.uname WHERE account.uname = '$_inboxUser' ORDER BY message.id DESC");
$allMsgs->execute();
$msgList = $allMsgs->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/partials/shell-data.php';
$shellPageTitle = 'Inbox';
require_once __DIR__ . '/partials/shell-open.php';
?>

<div class="mb-6">
  <h1 class="text-2xl font-bold text-gray-900">Inbox</h1>
  <p class="text-sm text-gray-500 mt-1">Messages sent to your account by our team.</p>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
  <?php if (empty($msgList)): ?>
    <div class="text-center py-16 text-gray-400">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
      </svg>
      <p class="text-sm">No messages yet</p>
    </div>
  <?php else: ?>
    <ul class="divide-y divide-gray-100">
      <?php foreach ($msgList as $m): ?>
        <?php $isUnread = (int)($m['is_read'] ?? 0) !== 1; ?>
        <li>
          <a href="message_view.php?id=<?= (int)($m['id'] ?? 0) ?>"
            class="flex items-start gap-4 px-6 py-4 transition-colors group <?= $isUnread ? 'bg-blue-50/40 hover:bg-blue-50 border-l-2 border-blue-500' : 'hover:bg-gray-50' ?>">
            <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 mt-0.5">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8" />
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-baseline gap-2">
                <span class="text-sm font-semibold truncate transition-colors <?= $isUnread ? 'text-blue-900 group-hover:text-blue-700' : 'text-gray-900 group-hover:text-blue-700' ?>">
                  <?= htmlspecialchars($m['subject'] ?? '(No subject)') ?>
                </span>
                <span class="text-xs text-gray-400 whitespace-nowrap flex-shrink-0">
                  <?= htmlspecialchars($m['date'] ?? '') ?>
                </span>
              </div>
              <p class="text-xs text-gray-500 mt-0.5">
                From: <?= htmlspecialchars($m['sender_name'] ?? 'Support') ?>
              </p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-300 group-hover:text-blue-400 flex-shrink-0 mt-1 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/shell-close.php';
exit(); ?>

<!DOCTYPE html>
<!-- saved from url=(0050)inbox.php -->
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Inbox</title>

  <meta content="ie=edge" http-equiv="x-ua-compatible">
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <link href="../asset.php?type=favicon" rel="shortcut icon">
  <link href="dash/inbox_files/css" rel="stylesheet" type="text/css">
  <link href="dash/inbox_files/select2.min.css" rel="stylesheet">
  <link href="dash/inbox_files/daterangepicker.css" rel="stylesheet">
  <link href="dash/inbox_files/dropzone.css" rel="stylesheet">
  <link href="dash/inbox_files/dataTables.bootstrap.min.css" rel="stylesheet">
  <link href="dash/inbox_files/fullcalendar.min.css" rel="stylesheet">
  <link href="dash/inbox_files/perfect-scrollbar.min.css" rel="stylesheet">
  <link href="dash/inbox_files/slick.css" rel="stylesheet">
  <link href="dash/inbox_files/main.css" rel="stylesheet">
  <link href="css/new-ui-bridge.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="dash/inbox_files/clock_style.css">
  <script async="" src="dash/inbox_files/analytics.js.download"></script>
  <script type="text/javascript">
    window.onload = setInterval(clock, 1000);

    function clock() {
      var d = new Date();

      var date = d.getDate();

      var month = d.getMonth();
      var montharr = ["Jan", "Feb", "Mar", "April", "May", "June", "July", "Aug", "Sep", "Oct", "Nov", "Dec"];
      month = montharr[month];

      var year = d.getFullYear();

      var day = d.getDay();
      var dayarr = ["Sun", "Mon", "Tues", "Wed", "Thurs", "Fri", "Sat"];
      day = dayarr[day];

      var hour = d.getHours();
      var min = d.getMinutes();
      var sec = d.getSeconds();

      document.getElementById("date").innerHTML = day + " " + date + " " + month + " " + year;
      document.getElementById("time").innerHTML = hour + ":" + min + ":" + sec;
    }



    var result;

    function showPosition() {
      // Store the element where the page displays the result
      result = document.getElementById("result");

      // If geolocation is available, try to get the visitor's position
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
        result.innerHTML = "Getting the position information...";
      } else {
        alert("Sorry, your browser does not support HTML5 geolocation.");
      }
    };

    // Define callback function for successful attempt
    function successCallback(position) {
      result.innerHTML = "Your current position is (" + "Latitude: " + position.coords.latitude + ", " + "Longitude: " + position.coords.longitude + ")";
    }

    // Define callback function for failed attempt
    function errorCallback(error) {
      if (error.code == 1) {
        result.innerHTML = "You've decided not to share your position, but it's OK. We won't ask you again.";
      } else if (error.code == 2) {
        result.innerHTML = "The network is down or the positioning service can't be reached.";
      } else if (error.code == 3) {
        result.innerHTML = "The attempt timed out before it could get the location data.";
      } else {
        result.innerHTML = "Geolocation failed due to unknown error.";
      }
    }
  </script>
  <style>
    .alert-success {
      color: #090909;
      background-color: #e2e9e1;
      border-color: #36b927;
    }
  </style>
  <style type="text/css">
    /* Chart.js */
    @-webkit-keyframes chartjs-render-animation {
      from {
        opacity: 0.99
      }

      to {
        opacity: 1
      }
    }

    @keyframes chartjs-render-animation {
      from {
        opacity: 0.99
      }

      to {
        opacity: 1
      }
    }

    .chartjs-render-monitor {
      -webkit-animation: chartjs-render-animation 0.001s;
      animation: chartjs-render-animation 0.001s;
    }
  </style>
  <style>
    .cke {
      visibility: hidden;
    }
  </style>
</head>

<body class="menu-position-side menu-side-left full-screen with-content-panel">
  <div class="all-wrapper with-side-panel solid-bg-all">
    <div class="search-with-suggestions-w">
      <div class="search-with-suggestions-modal">
        <div class="">
          <div class="close-search-suggestions">
            <i class="os-icon os-icon-x"></i>
          </div>

        </div>
        <div class="search-suggestions-group">
          <div class="ssg-header">
            <div class="ssg-icon">
              <div class="os-icon os-icon-box"></div>
            </div>
            <div class="ssg-name">
              Projects
            </div>
            <div class="ssg-info">
              24 Total
            </div>
          </div>
          <div class="ssg-content">
            <div class="ssg-items ssg-items-boxed">
              <a class="ssg-item" href="users_profile_big.html">
                <div class="item-media" style="background-image: url(img/company6.png)"></div>
                <div class="item-name">
                  Integ<span>ration</span> with API
                </div>
              </a><a class="ssg-item" href="users_profile_big.html">
                <div class="item-media" style="background-image: url(img/company7.png)"></div>
                <div class="item-name">
                  Deve<span>lopm</span>ent Project
                </div>
              </a>
            </div>
          </div>
        </div>
        <div class="search-suggestions-group">
          <div class="ssg-header">
            <div class="ssg-icon">
              <div class="os-icon os-icon-users"></div>
            </div>
            <div class="ssg-name">
              Customers
            </div>
            <div class="ssg-info">
              12 Total
            </div>
          </div>
          <div class="ssg-content">
            <div class="ssg-items ssg-items-list">
              <a class="ssg-item" href="users_profile_big.html">
                <div class="item-media" style="background-image: url(admin/pic/Stevenandrews.jpg)"></div>
                <div class="item-name">
                  John Ma<span>yer</span>s
                </div>
              </a><a class="ssg-item" href="users_profile_big.html">
                <div class="item-media" style="background-image: url(img/avatar2.jpg)"></div>
                <div class="item-name">
                  Th<span>omas</span> Mullier
                </div>
              </a><a class="ssg-item" href="users_profile_big.html">
                <div class="item-media" style="background-image: url(img/avatar3.jpg)"></div>
                <div class="item-name">
                  Kim C<span>olli</span>ns
                </div>
              </a>
            </div>
          </div>
        </div>
        <div class="search-suggestions-group">
          <div class="ssg-header">
            <div class="ssg-icon">
              <div class="os-icon os-icon-folder"></div>
            </div>
            <div class="ssg-name">
              Files
            </div>
            <div class="ssg-info">
              17 Total
            </div>
          </div>
          <div class="ssg-content">
            <div class="ssg-items ssg-items-blocks">
              <a class="ssg-item" href="inbox.php#">
                <div class="item-icon">
                  <i class="os-icon os-icon-file-text"></i>
                </div>
                <div class="item-name">
                  Work<span>Not</span>e.txt
                </div>
              </a><a class="ssg-item" href="inbox.php#">
                <div class="item-icon">
                  <i class="os-icon os-icon-film"></i>
                </div>
                <div class="item-name">
                  V<span>ideo</span>.avi
                </div>
              </a><a class="ssg-item" href="inbox.php#">
                <div class="item-icon">
                  <i class="os-icon os-icon-database"></i>
                </div>
                <div class="item-name">
                  User<span>Tabl</span>e.sql
                </div>
              </a><a class="ssg-item" href="inbox.php#">
                <div class="item-icon">
                  <i class="os-icon os-icon-image"></i>
                </div>
                <div class="item-name">
                  wed<span>din</span>g.jpg
                </div>
              </a>
            </div>
            <div class="ssg-nothing-found">
              <div class="icon-w">
                <i class="os-icon os-icon-eye-off"></i>
              </div>
              <span>No files were found. Try changing your query...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="layout-w">


      <div class="menu-mobile menu-activated-on-click color-scheme-dark">
        <div class="mm-logo-buttons-w">
          <a class="mm-logo" href="index.php"><span>ONLINE BANKING</span></a>
          <div class="mm-buttons">
            <div class="">
            </div>
            <div class="mobile-menu-trigger">
              <div class="os-icon os-icon-hamburger-menu-1"></div>
            </div>
          </div>
        </div>
        <div class="menu-and-user">
          <div class="logged-user-w">
            <div class="avatar-w">
              <img src="admin/foto/<?php echo $row['pp']; ?>" onerror="this.style.display=&#39;none&#39;" style="display: none;">
              <img src="admin/foto/<?php echo $row['pp']; ?>" onerror="this.style.display=&#39;none&#39;">
            </div>
            <div class="logged-user-info-w">
              <div class="logged-user-name">
                <?php echo $row['fname']; ?> <?php echo $row['lname']; ?> </div>
              <div class="logged-user-role">
                Account #: <?php echo $row['acc_no']; ?> </div>
            </div>
          </div>

          <ul class="main-menu">
            <li class="">
              <a href="index.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-layout"></div>
                </div>
                <span>Dashboard</span>
              </a>
            </li>
            <li class="">
              <a href="profile.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-user-male-circle2"></div>
                </div>
                <span>My Profile</span>
              </a>
            </li>
            <li class="">
              <a href="editpass.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-newspaper"></div>
                </div>
                <span>Change Password</span>
              </a>
            </li>
            <li class="">
              <a href="statement.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-newspaper"></div>
                </div>
                <span>My Statement</span>
              </a>
            </li>
            <li class="">
              <a href="send.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-signs-11"></div>
                </div>
                <span>Domestic Transfer</span>
              </a>
            </li>
            <li class="">
              <a href="send.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-mail-19"></div>
                </div>
                <span>Inter bank Transfer</span>
              </a>
            </li>
            <li class="">
              <a href="send.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-mail-19"></div>
                </div>
                <span>Wire Transfer</span>
              </a>
            </li>
            <li class="">
              <a href="loan.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-wallet-loaded"></div>
                </div>
                <span>Apply For Loan</span>
              </a>
            </li>
            <li class="">
              <a href="inbox.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-mail"></div>
                </div>
                <span>Messages</span>
              </a>
            </li>
            <li class="">
              <a href="ticket.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-mail"></div>
                </div>
                <span>Tickets</span>
              </a>
            </li>
            <li class="">
              <a href="https://tawk.to/chat/<?php echo $rowp['tawk2']; ?>">
                <div class="icon-w">
                  <div class="os-icon os-icon-mail"></div>
                </div>
                <span>Contact Us</span>
              </a>
            </li>
            <li class="">
              <a href="logout.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-lock"></div>
                </div>
                <span>Logout</span>
              </a>
            </li>
          </ul>

          <div class="mobile-menu-magic">
            <img alt="" src="dash/inbox_files/SEAL.gif">
            <div class="btn-w">
            </div>
          </div>
        </div>
      </div>

      <div class="menu-w color-scheme-dark color-style-bright menu-position-side menu-side-left menu-layout-full sub-menu-style-over sub-menu-color-bright selected-menu-color-light menu-activated-on-hover menu-has-selected-link">
        <center> <img src="img/logo.png" alt="logo" width="170" height="50"></center>
        <div class="logo-w">
          <a class="logo">
            <div class="logo-element"></div>
            <div class="logo-label">
              MY ONLINE BANKING
            </div>
          </a>
        </div>
        <div class="logged-user-w avatar-inline">
          <div class="logged-user-i">
            <div class="avatar-w">
              <img alt="" src="admin/foto/<?php echo $row['pp']; ?>">

            </div>
            <div class="logged-user-info-w">
              <div class="logged-user-name">
                <b style="font-size:13px"> <?php echo $row['fname']; ?> <?php echo $row['lname']; ?></b>
              </div>
              <div class="logged-user-role">
                <b style="color:white;font-size:13px;font-family: inherit;">Account No: <?php echo $row['acc_no']; ?></b>
              </div>
            </div>
          </div>
        </div>
        <div class="menu-actions">































































































          <div class="messages-notifications os-dropdown-trigger os-dropdown-position-right">
            <h5 style="color:white; font-size:14px; padding-top:10px;"> ACC STATUS: <span style="color:white; font-weight:bolder;"><?php


                                                                                                                                    $stat = $row['status'];

                                                                                                                                    if ($stat == "Active" || $stat == "pincode" || $stat == "otp") {
                                                                                                                                      echo ' <div class="os-icon os-icon-check-circle"> Active</div>  ';
                                                                                                                                    } else {
                                                                                                                                      echo "<span class='tx-warning tx-bold'>";
                                                                                                                                      echo "<div class='os-icon os-icon-alert-triangle'> &nbsp;";
                                                                                                                                      echo $row['status'];
                                                                                                                                      echo "</div>";
                                                                                                                                      echo "</span>";
                                                                                                                                    }

                                                                                                                                    ?></span></h5>
          </div>

        </div>



        <h1 class="menu-page-header">
          Page Header
        </h1>
        <ul class="main-menu">
          <li class="sub-header">
            <span>PERSONAL MENU</span>
          </li>
          <li class="selected has-sub-menu">
            <a href="index.php">
              <div class="icon-w">
                <div class="os-icon os-icon-layout"></div>
              </div>
              <span>Dashboard</span>
            </a>
            <div class="sub-menu-w">
              <div class="sub-menu-icon">
              </div>
              <div class="sub-menu-i">




















              </div>
            </div>
          </li>
          <li class=" has-sub-menu">
            <a href="profile.php">
              <div class="icon-w">
                <div class="os-icon os-icon-user-male-circle2"></div>
              </div>
              <span>My Profile</span>
            </a>
            <div class="sub-menu-w">
              <div class="sub-menu-icon">
                <i class="os-icon os-icon-layers"></i>
              </div>
            </div>
          </li>
          <li class=" has-sub-menu">
            <a href="statement.php">
              <div class="icon-w">
                <div class="os-icon os-icon-newspaper"></div>
              </div>
              <span>My Statement</span>
            </a>
            <div class="sub-menu-w">
              <div class="sub-menu-icon">
                <i class="os-icon os-icon-layers"></i>
              </div>

























































            </div>
          </li>
          <li class=" has-sub-menu">
            <a href="editpass.php">
              <div class="icon-w">
                <div class="os-icon os-icon-newspaper"></div>
              </div>
              <span>Change Password</span>
            </a>
            <div class="sub-menu-w">
              <div class="sub-menu-icon">
                <i class="os-icon os-icon-layers"></i>
              </div>

























































            </div>
          </li>
          <li class="sub-header">
            <span>Transfers</span>
          </li>
          <li class=" has-sub-menu">
            <a href="send.php">
              <div class="icon-w">
                <div class="os-icon os-icon-signs-11"></div>
              </div>
              <span>Domestic Transfer</span>
            </a>
            <div class="sub-menu-w">



              <div class="sub-menu-icon">
                <i class="os-icon os-icon-package"></i>
              </div>
            </div>
          </li>
          <li class=" has-sub-menu">
            <a href="send.php">
              <div class="icon-w">
                <div class="os-icon os-icon-signs-11"></div>
              </div>
              <span>Inter Bank Transfer</span>
            </a>
            <div class="sub-menu-w">



              <div class="sub-menu-icon">
                <i class="os-icon os-icon-package"></i>
              </div>
            </div>
          </li>
          <li class=" has-sub-menu">
            <a href="send.php">
              <div class="icon-w">
                <div class="os-icon os-icon-mail-19"></div>
              </div>
              <span>Wire Transfer</span>
            </a>
            <div class="sub-menu-w">



              <div class="sub-menu-icon">
                <i class="os-icon os-icon-package"></i>
              </div>
            </div>
          </li>
          <li class="sub-header">
            <span>Personal Banking</span>
          </li>
          <li class=" has-sub-menu">
            <a href="ticket.php">
              <div class="icon-w">
                <div class="os-icon os-icon-wallet-loaded"></div>
              </div>
              <span>Create a Ticket</span>
            </a>
            <div class="sub-menu-w">



              <div class="sub-menu-icon">
                <i class="os-icon os-icon-transfer"></i>
              </div>
            </div>
          </li>
          <li class=" has-sub-menu">
            <a href="inbox.php">
              <div class="icon-w">
                <div class="os-icon os-icon-inbox"></div>
              </div>
              <span>Messages</span>
            </a>
            <div class="sub-menu-w">



              <div class="sub-menu-icon">
                <i class="os-icon os-icon-money"></i>
              </div>
            </div>
          </li>
          <li class=" has-sub-menu">
            <a href="loan.php">
              <div class="icon-w">
                <div class="os-icon os-icon-wallet-loaded"></div>
              </div>
              <span>Apply For Loan</span>
            </a>
            <div class="sub-menu-w">



              <div class="sub-menu-icon">
                <i class="os-icon os-icon-money"></i>
              </div>
            </div>
          </li>
          <li class=" has-sub-menu">
            <a href="https://tawk.to/chat/<?php echo $rowp['tawk2']; ?>">
              <div class="icon-w">
                <div class="os-icon os-icon-mail"></div>
              </div>
              <span>Contact Us</span>
            </a>
            <div class="sub-menu-w">



              <div class="sub-menu-icon">
                <i class="os-icon os-icon-package"></i>
              </div>
            </div>
          </li>
          <li class=" has-sub-menu">
            <a href="logout.php">
              <div class="icon-w">
                <div class="os-icon os-icon-lock"></div>
              </div>
              <span>Logout</span>
            </a>
            <div class="sub-menu-w">



              <div class="sub-menu-icon">
                <i class="os-icon os-icon-package"></i>
              </div>
            </div>
          </li>
        </ul>
      </div>
      <div class="content-w">
        <div class="top-bar color-scheme-transparent">

          <div class="top-menu-controls">


            <div class="messages-notifications os-dropdown-trigger os-dropdown-position-left">
              <div id="google_translate_element"></div>
              <script type="text/javascript">
                function googleTranslateElementInit() {
                  new google.translate.TranslateElement({
                    pageLanguage: 'en',
                    layout: google.translate.TranslateElement.InlineLayout.SIMPLE
                  }, 'google_translate_element');
                }
              </script>
              <!-- Google Translate Element end -->


              <script type="text/javascript" src="dash/index_files/f.txt"></script>



            </div>

            <div class="messages-notifications os-dropdown-trigger os-dropdown-position-left">

              <a href="inbox.php"><i class="os-icon os-icon-mail-14"></i></a>


            </div>

            <div class="top-icon top-settings os-dropdown-trigger os-dropdown-position-left">
              <i class="os-icon os-icon-ui-46"></i>
              <div class="os-dropdown">
                <div class="icon-w">
                  <i class="os-icon os-icon-ui-46"></i>
                </div>
                <ul>
                  <li>
                    <a href="profile.php"><i class="os-icon os-icon-ui-49"></i><span>My Profile</span></a>
                  </li>
                  <li>
                    <a href="logout.php"><i class="os-icon os-icon-lock"></i><span>Logout</span></a>
                  </li>
                </ul>
              </div>
            </div>


            <div class="logged-user-w">
              <div class="">
                <div class="avatar-w">
                  <img alt="" src="admin/foto/<?php echo $row['pp']; ?>">
                </div>
                <div class="logged-user-menu color-style-bright">
                  <div class="logged-user-avatar-info">
                    <div class="avatar-w">
                      <img alt="" src="admin/foto/<?php echo $row['pp']; ?>">
                    </div>
                    <div class="logged-user-info-w">
                      <div class="logged-user-name">
                        Logout
                      </div>
                    </div>
                  </div>
                  <div class="bg-icon">
                    <i class="os-icon os-icon-wallet-loaded"></i>
                  </div>
                </div>
              </div>
            </div>

          </div>

        </div>

        <ul class="breadcrumb">
          <marquee bgcolor="#0076b6" style="color:white;">Notice: We are committed to providing a secured and convenient banking experience to all our customers through excellent services powered by state of the art technologies, however, if you notice anything <span style="color:orange;">SUSPICIOUS</span> with your online banking portal, kindly contact your account manager for immediate action <span style="color:WHITE" ;="">|</span> Thank you for banking with us! </marquee>
          <br>


        </ul>
        <div class="content-panel-toggler">
          <i class="os-icon os-icon-grid-squares-22"></i><span>Sidebar</span>
        </div>
        <div class="content-i">
          <div class="content-box">
            <div class="row">
              <div class="col-sm-12">
                <div class="element-wrapper">
                  <div class="element-actions">













                  </div>
                  <h6 class="element-header">
                    INBOX
                  </h6>
                  <div class="element-content">
                    <div class="row">
                      <div class="col-sm-4 col-xxxl-3">
                        <a class="element-box el-tablo" href="inbox.php#">
                          <div class="label">
                            Book Balance
                          </div>
                          <div class="value"><span style="color:orange;font-size:20px;">
                              <?php echo $row['currency']; ?> <?php echo $row['t_bal']; ?>
                            </span></div>
                        </a>
                      </div>
                      <div class="col-sm-4 col-xxxl-3">
                        <a class="element-box el-tablo" href="inbox.php#">
                          <div class="label">
                            Available Balance
                          </div>
                          <div class="value"><span style="color:green;font-size:20px;">
                              <?php echo $row['currency']; ?> <?php echo $row['a_bal']; ?> </span></div>
                        </a>
                      </div>
                      <div class="col-sm-4 col-xxxl-3">
                        <a class="element-box el-tablo" href="inbox.php#">
                          <div class="label">
                            Account Logged in from:
                          </div>
                          <div class="value"><span style="color:green;font-size:15px;">
                              <?php
                              echo '  ' . $_SERVER['REMOTE_ADDR'];
                              ?>
                            </span></div>
                        </a>
                      </div>
                      <div class="d-none d-xxxl-block col-xxxl-3">
                        <a class="element-box el-tablo" href="inbox.php#">
                          <div class="label">
                            Refunds Processed
                          </div>
                          <div class="value">
                            $294
                          </div>
                          <div class="trending trending-up-basic">
                            <span>12%</span><i class="os-icon os-icon-arrow-up2"></i>
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-12">
                <div class="element-wrapper">
                  <h6 class="element-header">
                    You can see messages sent from/to our 24/7 Customer care here.
                  </h6>
                  <div class="element-box-tp">

                    <div class="controls-above-table">
                      <div class="row">
                        <div class="col-sm-6">

                        </div>
                        <div class="col-sm-6">
                          <br>
                        </div>
                      </div>
                    </div>

                    <div id="content" class="content p-0">

                      <div class="mail-box">

                        <h4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Inbox</h4>

                        <div class="mail-box-container">

                          <div data-scrollbar="true" data-height="100%">

                            <hr class="mx-n4">

                            <?php
                            $reci_name = $row['uname'];
                            $msg = $user_home->runQuery("SELECT * FROM message INNER JOIN account ON message.reci_name= account.uname  WHERE account.uname = '$reci_name'");
                            $msg->execute(array(":reci_name" => $_SESSION['uname']));
                            while ($show = $msg->fetch(PDO::FETCH_ASSOC)) { ?>




                              <div class="form-check custom-control custom-checkbox">
                                <a href="message_view.php?id=<?php echo (int)($show['id'] ?? 0); ?>">
                                  <div class="custom-control-label text-3" for="announcements">
                                    <span class="d-block text-2 font-weight-300"><?php echo $show['date']; ?></span>
                                    <?php echo $show['sender_name']; ?>
                                  </div>
                                  <p class="text-muted line-height-3 mt-2"><?php echo $show['subject']; ?></p>
                                </a>
                              </div>





                              <hr class="mx-n4">
                            <?php } ?>



                          </div>

                        </div>


                      </div>

                    </div>

                    <br>
                    <hr>

                  </div>
                </div>
              </div>
            </div>



          </div>

          <footer class="page-footer font-small blue">

            <div class="footer-copyright text-center py-3">Copyright © <script type="text/javascript">
                var d = new Date()
                document.write(d.getFullYear())
              </script>. All Rights Reserved.
            </div>
          </footer>
          <div class="display-type"></div>
        </div>
        <script src="dash/inbox_files/jquery.min.js(1).download"></script>
        <script src="dash/inbox_files/popper.min.js.download"></script>
        <script src="dash/inbox_files/moment.js.download"></script>
        <script src="dash/inbox_files/Chart.min.js.download"></script>
        <script src="dash/inbox_files/select2.full.min.js.download"></script>
        <script src="dash/inbox_files/jquery.barrating.min.js.download"></script>
        <script src="dash/inbox_files/ckeditor.js.download"></script>
        <script src="dash/inbox_files/validator.min.js.download"></script>
        <script src="dash/inbox_files/daterangepicker.js.download"></script>
        <script src="dash/inbox_files/ion.rangeSlider.min.js.download"></script>
        <script src="dash/inbox_files/dropzone.js.download"></script>
        <script src="dash/inbox_files/mindmup-editabletable.js.download"></script>
        <script src="dash/inbox_files/jquery.dataTables.min.js.download"></script>
        <script src="dash/inbox_files/dataTables.bootstrap.min.js.download"></script>
        <script src="dash/inbox_files/fullcalendar.min.js.download"></script>
        <script src="dash/inbox_files/perfect-scrollbar.jquery.min.js.download"></script>
        <script src="dash/inbox_files/tether.min.js.download"></script>
        <script src="dash/inbox_files/slick.min.js.download"></script>
        <script src="dash/inbox_files/util.js.download"></script>
        <script src="dash/inbox_files/alert.js.download"></script>
        <script src="dash/inbox_files/button.js.download"></script>
        <script src="dash/inbox_files/carousel.js.download"></script>
        <script src="dash/inbox_files/collapse.js.download"></script>
        <script src="dash/inbox_files/dropdown.js.download"></script>
        <script src="dash/inbox_files/modal.js.download"></script>
        <script src="dash/inbox_files/tab.js.download"></script>
        <script src="dash/inbox_files/tooltip.js.download"></script>
        <script src="dash/inbox_files/popover.js.download"></script>
        <script src="dash/inbox_files/demo_customizer.js.download"></script>
        <script src="dash/inbox_files/main.js.download"></script>
        <script>
          (function(i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function() {
              (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
            a = s.createElement(o),
              m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
          })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

          ga('create', 'UA-XXXXXXX-9', 'auto');
          ga('send', 'pageview');
        </script>
        <script>
          $(document).ready(function() {
            App.init();
            FormWizards.init();
          });
        </script>

        <?php echo $rowp['tawk']; ?>
        <!--End of Tawk.to Script-->
      </div>
    </div>
  </div>
  <script src="js/new-ui-shell.js"></script>
</body>

</html>