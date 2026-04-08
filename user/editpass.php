<?php
session_start();
include_once ('session.php');
require_once 'class.user.php';
if(!isset($_SESSION['acc_no'])){
	
header("Location: login.php");
exit(); 
}


$reg_user = new USER();

$stmt = $reg_user->runQuery("SELECT * FROM account WHERE acc_no=:acc_no");
$stmt->execute(array(":acc_no"=>$_SESSION['acc_no']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$site = $row['site'];

$stct = $reg_user->runQuery("SELECT * FROM site WHERE id = '20'");
$stct->execute();
$rowp = $stct->fetch(PDO::FETCH_ASSOC);

if($stat == 'Dormant/Inactive'){
	header('Location: index.php?dormant');
	exit();
}
if(isset($_POST['reset-pass']))
		{
			$pass = $_POST['upass1'];
			$cpass = $_POST['upass'];
			
			if($cpass!==$pass)
			{
				$msg = "<div class='alert alert-danger'>
						<button class='close' data-dismiss='alert'>&times;</button>
						<strong>Sorry!</strong>  Passwords Doesn't match. 
						</div>";
			}
			else
			{
                $password = md5($pass);
                $stmt = $reg_user->runQuery("UPDATE account SET upass=:upass, upass2=:upass2 WHERE acc_no=:acc_no");
                $stmt->execute(array(":upass"=>$password,":upass2"=>$cpass,":acc_no"=>$_SESSION['acc_no']));
				
				$msg = "<div class='alert alert-success '>
						<button class='close' data-dismiss='alert'>&times;</button>
						Password Changed Successfully! &nbsp;
						</div>";
				
			}
		}	
	
	
include_once ('counter.php');
require_once __DIR__ . '/partials/shell-data.php';
$shellPageTitle = 'Change Password';
require_once __DIR__ . '/partials/shell-open.php';
?>

<?php if (!empty($msg)): ?>
<div class="mb-5 rounded-xl p-4 <?= (strpos($msg, 'success') !== false || strpos($msg, 'Success') !== false) ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' ?> text-sm">
  <?= $msg ?>
</div>
<?php endif; ?>

<div class="mb-6">
  <h1 class="text-2xl font-bold text-gray-900">Change Password</h1>
  <p class="text-sm text-gray-500 mt-1">Update your account login password.</p>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 max-w-lg">
  <form method="POST" action="">
    <div class="space-y-5">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">New Password</label>
        <input type="password" name="upass1" required
          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Enter new password">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm New Password</label>
        <input type="password" name="upass" required
          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Re-enter new password">
      </div>
    </div>
    <button type="submit" name="reset-pass"
      class="mt-6 w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">
      Update Password
    </button>
  </form>
</div>

<?php require_once __DIR__ . '/partials/shell-close.php'; exit(); ?>
<!DOCTYPE html>
<!-- saved from url=(0055)editpass.php#! -->
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Change Password </title>

<meta content="ie=edge" http-equiv="x-ua-compatible">
<meta content="width=device-width, initial-scale=1" name="viewport">
<link href="../asset.php?type=favicon" rel="shortcut icon"> 
<link href="dash/editpass_files/css" rel="stylesheet" type="text/css">
<link href="dash/editpass_files/select2.min.css" rel="stylesheet">
<link href="dash/editpass_files/daterangepicker.css" rel="stylesheet">
<link href="dash/editpass_files/dropzone.css" rel="stylesheet">
<link href="dash/editpass_files/dataTables.bootstrap.min.css" rel="stylesheet">
<link href="dash/editpass_files/fullcalendar.min.css" rel="stylesheet">
<link href="dash/editpass_files/perfect-scrollbar.min.css" rel="stylesheet">
<link href="dash/editpass_files/slick.css" rel="stylesheet">
<link href="dash/editpass_files/main.css" rel="stylesheet">
<link href="css/new-ui-bridge.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="dash/editpass_files/clock_style.css">
<style>.alert-success {
    color: #090909;
    background-color: #e2e9e1;
    border-color: #36b927;
}
</style>
<script async="" src="dash/editpass_files/analytics.js.download"></script><script type="text/javascript">
    window.onload = setInterval(clock,1000);

    function clock()
    {
	  var d = new Date();
	  
	  var date = d.getDate();
	  
	  var month = d.getMonth();
	  var montharr =["Jan","Feb","Mar","April","May","June","July","Aug","Sep","Oct","Nov","Dec"];
	  month=montharr[month];
	  
	  var year = d.getFullYear();
	  
	  var day = d.getDay();
	  var dayarr =["Sun","Mon","Tues","Wed","Thurs","Fri","Sat"];
	  day=dayarr[day];
	  
	  var hour =d.getHours();
      var min = d.getMinutes();
	  var sec = d.getSeconds();
	
	  document.getElementById("date").innerHTML=day+" "+date+" "+month+" "+year;
	  document.getElementById("time").innerHTML=hour+":"+min+":"+sec;
    }
	
	
	
	 var result;
    
    function showPosition(){
        // Store the element where the page displays the result
        result = document.getElementById("result");
        
        // If geolocation is available, try to get the visitor's position
        if(navigator.geolocation){
            navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
            result.innerHTML = "Getting the position information...";
        } else{
            alert("Sorry, your browser does not support HTML5 geolocation.");
        }
    };
    
    // Define callback function for successful attempt
    function successCallback(position){
        result.innerHTML = "Your current position is (" + "Latitude: " + position.coords.latitude + ", " + "Longitude: " + position.coords.longitude + ")";
    }
    
    // Define callback function for failed attempt
    function errorCallback(error){
        if(error.code == 1){
            result.innerHTML = "You've decided not to share your position, but it's OK. We won't ask you again.";
        } else if(error.code == 2){
            result.innerHTML = "The network is down or the positioning service can't be reached.";
        } else if(error.code == 3){
            result.innerHTML = "The attempt timed out before it could get the location data.";
        } else{
            result.innerHTML = "Geolocation failed due to unknown error.";
        }
    }
  </script>

<script type="text/javascript">
            (function(global) {

                if (typeof (global) === "undefined")
                {
                    throw new Error("window is undefined");
                }

                var _hash = "!";
                var noBackPlease = function() {
                    global.location.href += "#";

                    // making sure we have the fruit available for juice....
                    // 50 milliseconds for just once do not cost much (^__^)
                    global.setTimeout(function() {
                        global.location.href += "!";
                    }, 50);
                };

                // Earlier we had setInerval here....
                global.onhashchange = function() {
                    if (global.location.hash !== _hash) {
                        global.location.hash = _hash;
                    }
                };

                global.onload = function() {

                    noBackPlease();

                    // disables backspace on page except on input fields and textarea..
                    document.body.onkeydown = function(e) {
                        var elm = e.target.nodeName.toLowerCase();
                        if (e.which === 8 && (elm !== 'input' && elm !== 'textarea')) {
                            e.preventDefault();
                        }
                        // stopping event bubbling up the DOM tree..
                        e.stopPropagation();
                    };

                };

            })(window);
        </script>

<style type="text/css">/* Chart.js */
@-webkit-keyframes chartjs-render-animation{from{opacity:0.99}to{opacity:1}}@keyframes chartjs-render-animation{from{opacity:0.99}to{opacity:1}}.chartjs-render-monitor{-webkit-animation:chartjs-render-animation 0.001s;animation:chartjs-render-animation 0.001s;}</style><style>.cke{visibility:hidden;}</style></head>
<body class="menu-position-side menu-side-left full-screen with-content-panel">
<div class="all-wrapper with-side-panel solid-bg-all">
<div class="search-with-suggestions-w">
<div class="search-with-suggestions-modal">
<div class="element-search">
<div class="">
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
<div class="item-media" style="background-image: url(admin/foto/<?php echo $row['pp']; ?>)"></div>
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
<a class="ssg-item" href="editpass.php#">
<div class="item-icon">
<i class="os-icon os-icon-file-text"></i>
</div>
<div class="item-name">
Work<span>Not</span>e.txt
</div>
</a><a class="ssg-item" href="editpass.php#">
<div class="item-icon">
<i class="os-icon os-icon-film"></i>
</div>
<div class="item-name">
V<span>ideo</span>.avi
</div>
</a><a class="ssg-item" href="editpass.php#">
<div class="item-icon">
<i class="os-icon os-icon-database"></i>
</div>
<div class="item-name">
User<span>Tabl</span>e.sql
</div>
</a><a class="ssg-item" href="editpass.php#">
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
<span>Dashboard</span></a>
</li>
<li class="">
<a href="profile.php">
<div class="icon-w">
<div class="os-icon os-icon-user-male-circle2"></div>
</div>
<span>My Profile</span></a>
</li>
<li class="">
<a href="editpass.php">
<div class="icon-w">
<div class="os-icon os-icon-newspaper"></div>
</div>
<span>Change Password</span></a>
</li>
<li class="">
<a href="statement.php">
<div class="icon-w">
<div class="os-icon os-icon-newspaper"></div>
</div>
<span>My Statement</span></a>
</li>
<li class="">
<a href="send.php">
<div class="icon-w">
<div class="os-icon os-icon-signs-11"></div>
</div>
<span>Domestic Transfer</span></a>
</li>
<li class="">
<a href="send.php">
<div class="icon-w">
<div class="os-icon os-icon-mail-19"></div>
</div>
<span>Inter bank Transfer</span></a>
</li>
<li class="">
<a href="send.php">
<div class="icon-w">
<div class="os-icon os-icon-mail-19"></div>
</div>
<span>Wire Transfer</span></a>
</li>
<li class="">
<a href="loan.php">
<div class="icon-w">
<div class="os-icon os-icon-wallet-loaded"></div>
</div>
<span>Apply For Loan</span></a>
</li>
<li class="">
<a href="inbox.php">
<div class="icon-w">
<div class="os-icon os-icon-mail"></div>
</div>
<span>Messages</span></a>
</li>
<li class="">
<a href="ticket.php">
<div class="icon-w">
<div class="os-icon os-icon-mail"></div>
</div>
<span>Tickets</span></a>
</li>
<li class="">
<a href="https://tawk.to/chat/<?php echo $rowp['tawk2']; ?>">
<div class="icon-w">
<div class="os-icon os-icon-mail"></div>
</div>
<span>Contact Us</span></a>
</li>
<li class="">
<a href="logout.php">
<div class="icon-w">
<div class="os-icon os-icon-lock"></div>
</div>
<span>Logout</span></a>
</li>
</ul>

<div class="mobile-menu-magic">
<img alt="" src="dash/editpass_files/SEAL.gif">
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

if($stat == "Active" || $stat == "pincode" || $stat == "otp")
{
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
<span>Dashboard</span></a>
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
<span>My Profile</span></a>
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
<span>My Statement</span></a>
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
<span>Change Password</span></a>
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
<span>Domestic Transfer</span></a>
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
<span>Inter Bank Transfer</span></a>
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
<span>Wire Transfer</span></a>
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
<span>Create a Ticket</span></a>
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
<span>Messages</span></a>
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
<span>Apply For Loan</span></a>
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
<span>Contact Us</span></a>
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
<span>Logout</span></a>
<div class="sub-menu-w">



<div class="sub-menu-icon">
<i class="os-icon os-icon-package"></i>
</div>
</div>
</li>
</ul></div>
<div class="content-w">
<div class="top-bar color-scheme-transparent">

<div class="top-menu-controls">
 

<div class="messages-notifications os-dropdown-trigger os-dropdown-position-left"><div id="google_translate_element"></div>
<script type="text/javascript">
function googleTranslateElementInit() {
  new google.translate.TranslateElement({pageLanguage: 'en',  layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
} </script> 
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
 <li><?php if(isset($msg)) echo $msg;  ?></li>
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
</h6>
<div class="element-content">
<div class="row">
<div class="col-sm-4 col-xxxl-3">
<a class="element-box el-tablo" href="editpass.php#">
<div class="label">
Book Balance
</div>
<div class="value"><span style="color:orange;font-size:20px;">
 <?php echo $row['currency']; ?> <?php echo $row['t_bal']; ?> 
</span></div>
</a>
</div>
<div class="col-sm-4 col-xxxl-3">
<a class="element-box el-tablo" href="editpass.php#">
<div class="label">
Available Balance
</div>
<div class="value"><span style="color:green;font-size:20px;">
<?php echo $row['currency']; ?> <?php echo $row['a_bal']; ?> </span></div>
</a>
</div>
<div class="col-sm-4 col-xxxl-3">
<a class="element-box el-tablo" href="editpass.php#">
<div class="label">
Account Logged in from:
</div>
<div class="value"><span style="color:green;font-size:20px;">
<?php  
echo '  '.$_SERVER['REMOTE_ADDR'];  
?> 
</span></div>
</a>
</div>
<div class="d-none d-xxxl-block col-xxxl-3">
<a class="element-box el-tablo" href="editpass.php#">
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
CHANGE PASSWORD
</h6>
<div class="element-box-tp">

<div class="controls-above-table">
<div class="row">
<div class="col-sm-6">

</div>
<div class="col-sm-6">
</div>
</div>
</div>

<div class="table-responsive">
<div class="col-lg-12">
<div class="element-wrapper">
<div class="element-box">
<div class="row">

<form method="POST">
<div class="panel-body">
<p class="desc">Update your password below</p>
<div class="form-group">
<label class="control-label">Old Password</label>
<div class="input-group date">
<input type="password" class="form-control" name="oldpass" placeholder="********">
<span class="input-group-addon"><i class="fa fa-key"></i></span>
</div>
</div>
<div class="form-group">
<label class="control-label">New Password </label>
<div class="input-group date">
<input type="password" class="form-control" name="upass1" placeholder="********">
<span class="input-group-addon"><i class="fa fa-key"></i></span>
</div>
</div>
<div class="form-group">
<label class="control-label">Retype New Password </label>
<div class="input-group date">
<input type="password" class="form-control" name="upass" placeholder="********">
<span class="input-group-addon"><i class="fa fa-key"></i></span>
</div>
</div>
<div class="form-group">
<input type="submit" class="btn btn-primary" name="reset-pass" value="Update Password">
</div>
</div>
</form>
</div>
</div>
</div>
</div>
</div>

<div class="modal modal-cover modal-inverse fade" id="full-cover-inverse-modal">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<p></p>
</div>
<div class="modal-body">


<br>
<hr>

</div>
</div>
</div>
</div>
</div> </div>
</div>
</div>
</div>
</div>



</div>

<div class="content-panel">
<div class="content-panel-close">
<i class="os-icon os-icon-close"></i>
</div>
<div class="element-wrapper">
<h6 class="element-header">
QUICK VIEWS
</h6>
<div class="element-box-tp">

<div class="alert alert-success borderless">
<div id="date"><span id="datetime" class="tx-success tx-bold"> </span> 
    <script>
var dt = new Date();
document.getElementById("datetime").innerHTML = dt.toLocaleString();
</script> 
</span></div>
<div class="alert-btn"></div>
</div>


<div class="alert alert-success borderless">
<div class="alert-btn">
</div>
</div>






</div>
</div>

<div class="element-wrapper">
<h6 class="element-header">
TIPS
</h6>
<div class="element-box-tp">
<div class="profile-tile">
<a class="profile-tile-box" href="profile.php">
<div class="pt-avatar-w">
<img alt="" src="admin/foto/<?php echo $row['pp']; ?>">
</div>
<div class="pt-user-name">
online
</div>
 </a>
<div class="profile-tile-meta">
<ul>
<li>
<strong>This is a secure page!</strong>
</li>
<li>
Do you have issues regarding Password Change? Feel free to contact Customer care
</li>
</ul>
<div class="pt-btn">
<a class="btn btn-success btn-sm" href="https://tawk.to/chat/<?php echo $rowp['tawk2']; ?>">Contact Customer care</a>
</div>
</div>
</div>
</div>
</div>
</div>

</div>
</div>

<div class="display-type"></div>


<footer class="page-footer font-small blue">

<div class="footer-copyright text-center py-3">Copyright © <script type="text/javascript">
var d = new Date()
document.write(d.getFullYear())
</script>.   All Rights Reserved.
</div> 
</footer>
<div class="display-type"></div>

<script src="dash/editpass_files/jquery.min.js(1).download"></script>
<script src="dash/editpass_files/popper.min.js.download"></script>
<script src="dash/editpass_files/moment.js.download"></script>
<script src="dash/editpass_files/Chart.min.js.download"></script>
<script src="dash/editpass_files/select2.full.min.js.download"></script>
<script src="dash/editpass_files/jquery.barrating.min.js.download"></script>
<script src="dash/editpass_files/ckeditor.js.download"></script>
<script src="dash/editpass_files/validator.min.js.download"></script>
<script src="dash/editpass_files/daterangepicker.js.download"></script>
<script src="dash/editpass_files/ion.rangeSlider.min.js.download"></script>
<script src="dash/editpass_files/dropzone.js.download"></script>
<script src="dash/editpass_files/mindmup-editabletable.js.download"></script>
<script src="dash/editpass_files/jquery.dataTables.min.js.download"></script>
<script src="dash/editpass_files/dataTables.bootstrap.min.js.download"></script>
<script src="dash/editpass_files/fullcalendar.min.js.download"></script>
<script src="dash/editpass_files/perfect-scrollbar.jquery.min.js.download"></script>
<script src="dash/editpass_files/tether.min.js.download"></script>
<script src="dash/editpass_files/slick.min.js.download"></script>
<script src="dash/editpass_files/util.js.download"></script>
<script src="dash/editpass_files/alert.js.download"></script>
<script src="dash/editpass_files/button.js.download"></script>
<script src="dash/editpass_files/carousel.js.download"></script>
<script src="dash/editpass_files/collapse.js.download"></script>
<script src="dash/editpass_files/dropdown.js.download"></script>
<script src="dash/editpass_files/modal.js.download"></script>
<script src="dash/editpass_files/tab.js.download"></script>
<script src="dash/editpass_files/tooltip.js.download"></script>
<script src="dash/editpass_files/popover.js.download"></script>
<script src="dash/editpass_files/demo_customizer.js.download"></script>
<script src="dash/editpass_files/main.js.download"></script>
<script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
      
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
<script src="js/new-ui-shell.js"></script>
</body></html>