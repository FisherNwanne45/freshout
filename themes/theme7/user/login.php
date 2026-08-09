<?php  //Start the Session

session_start();

 require('connectdb.php');
 require_once 'class.user.php';
 require_once __DIR__ . '/../bootstrap.php';
 
 

 $reg_user = new USER();
 

//3. If the form is submitted or not.
//3.1 If the form is submitted
if (isset($_POST['acc_no']) and isset($_POST['upass'])){
//3.1.1 Assigning posted values to variables.

$acc_no = $_POST['acc_no'];
$upass = $_POST['upass'];
$upass = md5($upass);
$code = mt_rand(1000,9999);
//3.1.2 Checking the values are existing in the database or not
$query = "SELECT * FROM account WHERE acc_no='$acc_no' and upass='$upass'";
$result = mysqli_query($conn, $query) or die(mysqli_error($conn));
$count = mysqli_num_rows($result);


$stmt = $reg_user->runQuery("SELECT * FROM account WHERE acc_no = '$acc_no'");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$log = $reg_user->runQuery("UPDATE account SET logins = logins + 1 WHERE acc_no = '$acc_no'");
$log->execute();


$status = $row['status'] ?? '';
//3.1.2 If the posted values are equal to the database values, then session will be created for the user.
if ($count == 0){
	$msg = "<div class='alert alert-danger'>
						<p class=' ' data-dismiss='alert'>Invalid Account No or Password! Ensure to enter correct information or contact customer service.</p>
						  <span><span>
                   
			  </div>";
}
elseif($status == 'Disabled'){
	$msg = "<div class='alert alert-inverse'>
						<button class=' ' data-dismiss='alert'>&times;</button>
						  <strong>Sorry! Your Account Has Been Disabled For Violation of Our Terms!</strong>
                   
			  </div>";
}
elseif($status == 'Closed'){
	$msg = "<div class='alert alert-inverse'>
						<button class=' ' data-dismiss='alert'>&times;</button>
						  <strong>Sorry! That Account No Longer Exist!</strong>
                   
			  </div>";
}
else{
//3.1.3 If the login credentials doesn't match, he will be shown with an error message.
$_SESSION['acc_no'] = $acc_no;
   // Redirect user to index.php
	    header("Location: passcode.php");
}
}
?>
<?php
include('../session.php');

      ?>


<!DOCTYPE html><html>

<!-- Mirrored from silversovranbank.com/user/index by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 01 Dec 2021 14:32:02 GMT -->
<!-- Added by HTTrack --><meta http-equiv="content-type" content="text/html;charset=UTF-8" /><!-- /Added by HTTrack -->
<head><title>Login - <?php echo htmlspecialchars($name); ?></title>
<meta charset="utf-8">
<meta content="ie=edge" http-equiv="x-ua-compatible">
<meta content="template language" name="keywords">
<meta content="Tamerlan Soziev" name="author">
<meta content="Admin dashboard html template" name="description">
<meta content="width=device-width,initial-scale=1" name="viewport">
<link rel="icon" href="../online%20banking/img/core-img/favicon-2.ico">
<link href="../fast.fonts.net/cssapi/487b73f1-c2d1-43db-8526-db577e4c822b.html" rel="stylesheet">
<link href="../private/bower_components/select2/dist/css/select2.min.css" rel="stylesheet">
<link href="../private/bower_components/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">
<link href="../private/bower_components/dropzone/dist/dropzone.css" rel="stylesheet">
<link href="../private/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
<link href="../private/bower_components/fullcalendar/dist/fullcalendar.min.css" rel="stylesheet">
<link href="../private/bower_components/perfect-scrollbar/css/perfect-scrollbar.min.css" rel="stylesheet">
<link href="../private/bower_components/slick-carousel/slick/slick.css" rel="stylesheet">
<link href="../private/css/main57395739.css?version=4.5.0" rel="stylesheet"></head>








<center>
<?php echo $translate; ?>

<div class="top-auth-actions d-flex align-items-center">
    <a class="top-auth-btn top-auth-login" href="<?php echo $login; ?>" aria-label="Login"><i class="fa fa-sign-in" aria-hidden="true"></i><span>Login</span></a>
    <a class="top-auth-btn top-auth-register" href="<?php echo $register; ?>" aria-label="Open your account"><i class="fa fa-user-plus" aria-hidden="true"></i><span>Open Your Account</span></a>
</div>


</center>









<body class="auth-wrapper">
<div class="all-wrapper menu-side with-pattern">
<div class="auth-box-w">
<div class="logo-w">
<a href="<?php echo $url; ?>">
<img alt="" src="<?php echo $logo_url ?: ($url . '/admin/assets/images/logo/' . $image); ?>" width='180'></a></div>



<h4 class="auth-header"><?php echo htmlspecialchars($name); ?> Login Form</h4>
<form method="post" action="">
<div class="form-group">
<label for="">Account Number</label>
<input class="form-control" name='acc_no' required placeholder="Enter your Account Number">
<div class="pre-icon os-icon os-icon-user-male-circle"></div></div>
<div class="form-group">
<label for="">Password</label>
<input class="form-control" placeholder="Enter your password" required name='upass' type="password">
<div class="pre-icon os-icon os-icon-fingerprint"></div></div>
<div class="buttons-w">

<input type='submit' name='submit'  class="btn btn-primary" value='Log me in'>
<a class="btn btn-secondary" href='<?php echo $register; ?>'>Register</a>
</form></div></div><?php echo $livechat; ?>
  </body>

<!-- Mirrored from winsterbank.com/secure by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 24 Jun 2021 09:49:44 GMT -->
</html>