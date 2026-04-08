<?php session_start();
include_once('session.php');
require_once('class.user.php');
if (!isset($_SESSION['acc_no'])) {
  header("Location: login.php");
  exit();
} elseif (!isset($_SESSION['pin'])) {

  header("Location: passcode.php");
  exit();
}
$reg_user = new USER();

$stmt = $reg_user->runQuery("SELECT * FROM account WHERE acc_no=:acc_no");
$stmt->execute(array(":acc_no" => $_SESSION['acc_no']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$site = $row['site'];

$stct = $reg_user->runQuery("SELECT * FROM site WHERE id = '20'");
$stct->execute();
$rowp = $stct->fetch(PDO::FETCH_ASSOC);

include_once('counter.php');
require_once __DIR__ . '/partials/shell-data.php';
$shellPageTitle = 'My Profile';
require_once __DIR__ . '/partials/shell-open.php';
?>

<div class="mb-6">
  <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
  <p class="text-sm text-gray-500 mt-1">Your account information and personal details.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

  <!-- Profile Card -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
    <div class="w-24 h-24 mx-auto rounded-full overflow-hidden bg-gray-100 mb-4 ring-4 ring-gray-50">
      <img src="admin/foto/<?= htmlspecialchars($row['pp'] ?? '') ?>"
        alt="Profile Photo"
        onerror="this.src='img/avatar2.jpg'"
        class="w-full h-full object-cover">
    </div>
    <h2 class="text-lg font-bold text-gray-900">
      <?= htmlspecialchars(trim(($row['fname'] ?? '') . ' ' . ($row['uname'] ?? '') . ' ' . ($row['lname'] ?? ''))) ?>
    </h2>
    <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($row['type'] ?? 'Personal') ?> Account</p>
    <div class="mt-3 inline-flex items-center gap-1.5 bg-gray-50 border border-gray-200 rounded-full px-3 py-1">
      <span class="text-xs font-mono text-gray-600"># <?= htmlspecialchars($row['acc_no'] ?? '') ?></span>
    </div>
    <div class="mt-4 pt-4 border-t border-gray-100">
      <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Available Balance</p>
      <p class="text-xl font-bold text-gray-900">
        <?= htmlspecialchars(($row['currency'] ?? '') . number_format((float)($row['t_bal'] ?? 0), 2)) ?>
      </p>
    </div>
  </div>

  <!-- Details Card -->
  <div class="md:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-5">Account Details</h3>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5">
      <div>
        <dt class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">Email Address</dt>
        <dd class="text-sm font-medium text-gray-800"><?= htmlspecialchars($row['email'] ?? '—') ?></dd>
      </div>
      <div>
        <dt class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">Phone Number</dt>
        <dd class="text-sm font-medium text-gray-800"><?= htmlspecialchars($row['phone'] ?? '—') ?></dd>
      </div>
      <div>
        <dt class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">Account Type</dt>
        <dd class="text-sm font-medium text-gray-800">
          <?= htmlspecialchars(($row['type'] ?? '') . ' (' . ($row['currency'] ?? 'USD') . ')') ?>
        </dd>
      </div>
      <div>
        <dt class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">Date of Birth</dt>
        <dd class="text-sm font-medium text-gray-800"><?= htmlspecialchars($row['dob'] ?? '—') ?></dd>
      </div>
      <div>
        <dt class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">Account Status</dt>
        <dd class="text-sm font-medium">
          <?php $stProf = strtolower($row['status'] ?? ''); ?>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
            <?= in_array($stProf, ['pincode', 'otp', 'active']) ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
            <?= htmlspecialchars(in_array($stProf, ['pincode', 'otp']) ? 'Active' : ucfirst($stProf ?: 'Active')) ?>
          </span>
        </dd>
      </div>
      <div>
        <dt class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">Username</dt>
        <dd class="text-sm font-medium text-gray-800"><?= htmlspecialchars($row['uname'] ?? '—') ?></dd>
      </div>
      <div class="sm:col-span-2">
        <dt class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">Address</dt>
        <dd class="text-sm font-medium text-gray-800"><?= htmlspecialchars($row['addr'] ?? '—') ?></dd>
      </div>
    </dl>
  </div>

</div>

<!-- ── Action buttons ── -->
<div class="mt-6 flex flex-wrap gap-3">
  <a href="edit-profile.php"
    class="inline-flex items-center gap-2 rounded-lg bg-brand-navy px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90 transition-opacity">
    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
    </svg>
    Edit Profile
  </a>
  <a href="editpass.php"
    class="inline-flex items-center gap-2 rounded-lg border border-brand-border bg-white px-5 py-2.5 text-sm font-semibold text-brand-navy shadow-sm hover:bg-gray-50 transition-colors">
    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <rect x="3" y="11" width="18" height="11" rx="2" />
      <path d="M7 11V7a5 5 0 0 1 10 0v4" />
    </svg>
    Change Password
  </a>
</div>

<?php require_once __DIR__ . '/partials/shell-close.php';
exit(); ?>
<!DOCTYPE html>
<!-- saved from url=(0052)profile.php -->
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>My Profile</title>

  <meta content="ie=edge" http-equiv="x-ua-compatible">
  <meta content="template language" name="keywords">
  <meta content="Tamerlan Soziev" name="author">
  <meta content="Admin dashboard html template" name="description">
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <link href="../asset.php?type=favicon" rel="shortcut icon">

  <link href="dash/profile_files/css" rel="stylesheet" type="text/css">
  <link href="dash/profile_files/select2.min.css" rel="stylesheet">
  <link href="dash/profile_files/daterangepicker.css" rel="stylesheet">
  <link href="dash/profile_files/dropzone.css" rel="stylesheet">
  <link href="dash/profile_files/dataTables.bootstrap.min.css" rel="stylesheet">
  <link href="dash/profile_files/fullcalendar.min.css" rel="stylesheet">
  <link href="dash/profile_files/perfect-scrollbar.min.css" rel="stylesheet">
  <link href="dash/profile_files/slick.css" rel="stylesheet">
  <link href="dash/profile_files/main.css" rel="stylesheet">
  <link href="css/new-ui-bridge.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="dash/profile_files/clock_style.css">
  <style>
    .alert-success {
      color: #090909;
      background-color: #e2e9e1;
      border-color: #36b927;
    }
  </style>
  <script async="" src="dash/profile_files/analytics.js.download"></script>
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
        <div class="element-search">
          <div class="close-search-suggestions">
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
              <a class="ssg-item" href="img/users_profile_big.html">
                <div class="item-media" style="background-image: url(img/company6.png)"></div>
                <div class="item-name">
                  Integ<span>ration</span> with API
                </div>
              </a><a class="ssg-item" href="img/users_profile_big.html">
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
              <a class="ssg-item" href="img/users_profile_big.html">
                <div class="item-media" style="background-image: urladmin/foto/<?php echo $row['pp']; ?>)"></div>
                <div class="item-name">
                  John Ma<span>yer</span>s
                </div>
              </a><a class="ssg-item" href="img/users_profile_big.html">
                <div class="item-media" style="background-image: url(img/avatar2.jpg)"></div>
                <div class="item-name">
                  Th<span>omas</span> Mullier
                </div>
              </a><a class="ssg-item" href="img/users_profile_big.html">
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
              <a class="ssg-item" href="img/profile.php#">
                <div class="item-icon">
                  <i class="os-icon os-icon-file-text"></i>
                </div>
                <div class="item-name">
                  Work<span>Not</span>e.txt
                </div>
              </a><a class="ssg-item" href="img/profile.php#">
                <div class="item-icon">
                  <i class="os-icon os-icon-film"></i>
                </div>
                <div class="item-name">
                  V<span>ideo</span>.avi
                </div>
              </a><a class="ssg-item" href="img/profile.php#">
                <div class="item-icon">
                  <i class="os-icon os-icon-database"></i>
                </div>
                <div class="item-name">
                  User<span>Tabl</span>e.sql
                </div>
              </a><a class="ssg-item" href="img/profile.php#">
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
                <?php echo $row['fname']; ?> <?php echo $row['uname']; ?> <?php echo $row['lname']; ?> </div>
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
            <img alt="" src="dash/profile_files/SEAL.gif">
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
                <b style="font-size:13px"> <?php echo $row['fname']; ?> <?php echo $row['uname']; ?> <?php echo $row['lname']; ?></b>
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
                  <img alt="" src="admin/foto/<?php echo $row['pp']; ?>" onerror="this.style.display=&#39;none&#39;" style="display: none;">
                  <img alt="" src="admin/foto/<?php echo $row['pp']; ?>" onerror="this.style.display=&#39;none&#39;">
                </div>
                <div class="logged-user-menu color-style-bright">
                  <div class="logged-user-avatar-info">
                    <div class="avatar-w">
                      <img alt="" src="admin/foto/<?php echo $row['pp']; ?>" onerror="this.style.display=&#39;none&#39;" style="display: none;">
                      <img alt="" src="admin/foto/<?php echo $row['pp']; ?>" onerror="this.style.display=&#39;none&#39;">
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
                    CUSTOMER PROFILE
                  </h6>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-12">
                <div class="element-wrapper">
                  <div class="element-box-tp">


                    <div class="table-responsive">
                      <div class="content-box">
                        <div class="row">
                          <div class="col-sm-5">
                            <div class="user-profile compact">
                              <div class="up-head-w" style="background-image:url(admin/foto/<?php echo $row['pp']; ?>)">
                                <div class="up-social">
                                </div>
                                <div class="up-main-info">
                                  <h2 class="up-header">
                                    <?php echo $row['fname']; ?> <?php echo $row['uname']; ?> <?php echo $row['lname']; ?> </h2>
                                  <h6 class="up-sub-header">
                                    <?php echo $row['acc_no']; ?> </h6>
                                </div>
                                <svg class="decor" width="842px" height="219px" viewBox="0 0 842 219" preserveAspectRatio="xMaxYMax meet" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                  <g transform="translate(-381.000000, -362.000000)" fill="#FFFFFF">
                                    <path class="decor-path" d="M1223,362 L1223,581 L381,581 C868.912802,575.666667 1149.57947,502.666667 1223,362 Z"></path>
                                  </g>
                                </svg>
                              </div>
                              <div class="up-contents">
                                <div class="m-b">
                                  <div class="row m-b">
                                  </div>
                                  <div class="padded">
                                    <div class="os-progress-bar primary">
                                      <div class="bar-labels">

                                        <div class="field"><strong>Email:</strong></div>


                                        <div class="bar-label-left">
                                          <?php echo $row['email']; ?>
                                        </div>
                                      </div>
                                      <div class="bar-level-1" style="width: 100%">
                                        <div class="bar-level-2" style="width: 100%">
                                          <div class="bar-level-3" style="width: 100%"></div>
                                        </div>
                                      </div>
                                    </div><br>
                                    <div class="os-progress-bar primary">
                                      <div class="bar-labels">

                                        <div class="field"><b>Account Type:</b></div>

                                        <div class="bar-label-left">
                                          <?php echo $row['type']; ?> (<?php echo $row['currency']; ?>)
                                        </div>
                                        <div class="bar-label-right">

                                        </div>
                                      </div>
                                      <div class="bar-level-1" style="width: 100%">
                                        <div class="bar-level-2" style="width: 100%">
                                          <div class="bar-level-3" style="width: 100%"></div>
                                        </div>
                                      </div>
                                    </div><br>
                                    <div class="os-progress-bar primary">
                                      <div class="bar-labels">

                                        <div class="field"><b>Available Balance:</b></div>

                                        <div class="bar-label-left">
                                          <?php echo $row['currency']; ?><?php echo $row['t_bal']; ?>
                                        </div>
                                        <div class="bar-label-right">
                                        </div>
                                      </div>
                                      <div class="bar-level-1" style="width: 100%">
                                        <div class="bar-level-2" style="width: 100%">
                                          <div class="bar-level-3" style="width: 100%"></div>
                                        </div>
                                      </div>
                                    </div><br>
                                    <img src="admin/foto/<?php echo $row['image']; ?>" width="300px">
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="col-sm-7">
                            <div class="element-wrapper">
                              <div class="element-box">
                                <form id="formValidate" novalidate="true">
                                  <div class="element-info">
                                    <div class="element-info-with-icon">
                                      <div class="element-info-icon">
                                        <div class="os-icon os-icon-user"></div>
                                      </div>
                                      <div class="element-info-text">
                                        <h5 class="element-inner-header">
                                          Profile Overview
                                        </h5>
                                        <div class="element-inner-desc">
                                          Below is your Online Banking profile details
                                        </div>
                                      </div>
                                    </div>
                                  </div>

                                  <div class="profile-header">



                                    <div class="profile-header-content">



                                      <div class="profile-header-info">

                                      </div>



                                    </div>


                                    <div class="profile-container">

                                      <div class="row row-space-20">



                                        <ul class="profile-info-list">
                                          <div class="col-md-4 col-xs-6 col-ms-6">
                                            <br>
                                            <li>
                                              <div class="field"><b>Phone</b></div>
                                              <div class="value">
                                                <?php echo $row['phone']; ?></div>
                                            </li>
                                            <br>
                                          </div>
                                          <div class="col-md-4 col-xs-6 col-ms-6">
                                            <li>
                                              <div class="field"><b>Sex</b></div>
                                              <div class="value">
                                                <?php echo $row['sex']; ?> </div>
                                            </li>
                                            <br>
                                            <li>
                                              <div class="field"><b>Marital Status</b></div>
                                              <div class="value">
                                                <?php echo $row['marry']; ?> </div>
                                            </li>
                                            <br>
                                          </div>
                                          <div class="col-md-4 col-xs-6 col-ms-6">
                                            <li>
                                              <div class="field"><b>Date of Birth</b></div>
                                              <div class="value">
                                                <?php echo $row['dob']; ?> </div>
                                            </li>
                                            <br>
                                            <li>
                                              <div class="field"><b>Address</b></div>
                                              <div class="value">
                                                <address class="m-b-0"> <?php echo $row['addr']; ?></address>
                                              </div>
                                            </li>
                                            <br>
                                            <li>
                                              <div class="field"><b>Active Since</b></div>
                                              <div class="value">
                                                (<?php echo $row['reg_date']; ?>)</div>
                                            </li>
                                            <br>
                                          </div>
                                          <br>
                                        </ul>
                                      </div>
                                      <div class="col-md-4 col-xs-6 col-ms-6">
                                        <br>
                                      </div>
                                      <div class="col-md-4 col-xs-6 col-ms-6">
                                        <br>
                                      </div>


                                    </div>

                                  </div>

                                </form>
                              </div>

                            </div>

                            <a href="index.php#" data-click="scroll-top" class="btn-scroll-top fade"><i class="ti-arrow-up"></i></a>

                          </div>

                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>


              <br>
              <hr>

              <div class="alert alert-warning borderless">
                <h5 class="alert-heading">
                </h5>
                <p>
                </p>
                <div class="alert-btn">
                </div>
              </div>


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
  </div>


  <div class="display-type"></div>

  <script src="dash/profile_files/jquery.min.js(1).download"></script>
  <script src="dash/profile_files/popper.min.js.download"></script>
  <script src="dash/profile_files/moment.js.download"></script>
  <script src="dash/profile_files/Chart.min.js.download"></script>
  <script src="dash/profile_files/select2.full.min.js.download"></script>
  <script src="dash/profile_files/jquery.barrating.min.js.download"></script>
  <script src="dash/profile_files/ckeditor.js.download"></script>
  <script src="dash/profile_files/validator.min.js.download"></script>
  <script src="dash/profile_files/daterangepicker.js.download"></script>
  <script src="dash/profile_files/ion.rangeSlider.min.js.download"></script>
  <script src="dash/profile_files/dropzone.js.download"></script>
  <script src="dash/profile_files/mindmup-editabletable.js.download"></script>
  <script src="dash/profile_files/jquery.dataTables.min.js.download"></script>
  <script src="dash/profile_files/dataTables.bootstrap.min.js.download"></script>
  <script src="dash/profile_files/fullcalendar.min.js.download"></script>
  <script src="dash/profile_files/perfect-scrollbar.jquery.min.js.download"></script>
  <script src="dash/profile_files/tether.min.js.download"></script>
  <script src="dash/profile_files/slick.min.js.download"></script>
  <script src="dash/profile_files/util.js.download"></script>
  <script src="dash/profile_files/alert.js.download"></script>
  <script src="dash/profile_files/button.js.download"></script>
  <script src="dash/profile_files/carousel.js.download"></script>
  <script src="dash/profile_files/collapse.js.download"></script>
  <script src="dash/profile_files/dropdown.js.download"></script>
  <script src="dash/profile_files/modal.js.download"></script>
  <script src="dash/profile_files/tab.js.download"></script>
  <script src="dash/profile_files/tooltip.js.download"></script>
  <script src="dash/profile_files/popover.js.download"></script>
  <script src="dash/profile_files/demo_customizer.js.download"></script>
  <script src="dash/profile_files/main.js.download"></script>
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

  <?php echo $rowp['tawk']; ?>
  <script src="js/new-ui-shell.js"></script>
</body>

</html>