<?php
session_start();
require_once 'admin.php';


$reg_user = new USER();
$created = false;

if (isset($_POST['register'])) {


	$fname = trim($_POST['fname']);
	$fname = strip_tags($fname);
	$fname = htmlspecialchars($fname);

	$pin = trim($_POST['pin']);
	$pin = strip_tags($pin);
	$pin = htmlspecialchars($pin);

	$lname = trim($_POST['lname']);
	$lname = strip_tags($lname);
	$lname = htmlspecialchars($lname);

	$uname = trim($_POST['uname']);
	$uname = strip_tags($uname);
	$uname = htmlspecialchars($uname);

	$upass = $_POST['upass'];
	$upass2 = $_POST['upass2'];
	$image = $_POST['image'];
	$pp = $_POST['pp'];

	$phone = trim($_POST['phone']);
	$phone = strip_tags($phone);
	$phone = htmlspecialchars($phone);

	$loan = trim($_POST['loan']);
	$loan = strip_tags($loan);
	$loan = htmlspecialchars($loan);

	$email = trim($_POST['email']);
	$email = strip_tags($email);
	$email = htmlspecialchars($email);

	$type = trim($_POST['type']);
	$type = strip_tags($type);
	$type = htmlspecialchars($type);

	$reg_date = trim($_POST['reg_date']);

	$work = trim($_POST['work']);
	$work = strip_tags($work);
	$work = htmlspecialchars($work);

	$acc_no = trim($_POST['acc_no']);
	$acc_no = strip_tags($acc_no);
	$acc_no = htmlspecialchars($acc_no);

	$addr = trim($_POST['addr']);
	$addr = strip_tags($addr);
	$addr = htmlspecialchars($addr);

	$sex = trim($_POST['sex']);
	$sex = strip_tags($sex);
	$sex = htmlspecialchars($sex);

	$dob = trim($_POST['dob']);
	$dob = strip_tags($dob);
	$dob = htmlspecialchars($dob);

	$marry = trim($_POST['marry']);
	$marry = strip_tags($marry);
	$marry = htmlspecialchars($marry);

	$t_bal = trim($_POST['t_bal']);
	$t_bal = strip_tags($t_bal);
	$t_bal = htmlspecialchars($t_bal);

	$a_bal = trim($_POST['a_bal']);
	$a_bal = strip_tags($a_bal);
	$a_bal = htmlspecialchars($a_bal);

	$currency = trim($_POST['currency']);
	$currency = strip_tags($currency);
	$currency = htmlspecialchars($currency);

	$cot = trim($_POST['cot']);
	$cot = strip_tags($cot);
	$cot = htmlspecialchars($cot);

	$tax = trim($_POST['tax']);
	$tax = strip_tags($tax);
	$tax = htmlspecialchars($tax);

	$imf = trim($_POST['imf']);
	$imf  = strip_tags($imf);
	$imf  = htmlspecialchars($imf);

	$status = trim($_POST['status']);
	$status  = strip_tags($status);
	$status  = htmlspecialchars($status);

	$stct = $reg_user->runQuery("SELECT * FROM site WHERE id = '20'");
	$stct->execute();
	$rowp = $stct->fetch(PDO::FETCH_ASSOC);

	$mall = $rowp['email'];
	$url = $rowp['url'];
	$nm = $rowp['name'];
	$add = $rowp['addr'];

	$stmt = $reg_user->runQuery("SELECT * FROM account WHERE acc_no=:acc_no");
	$stmt1 = $reg_user->runQuery("SELECT * FROM account WHERE email=:email");
	$stmt->execute(array(":acc_no" => $acc_no));
	$stmt1->execute(array(":email" => $email));
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	$row1 = $stmt1->fetch(PDO::FETCH_ASSOC);


	if ($stmt->rowCount() > 0 || $stmt1->rowCount() > 0) {
		$msg = "
		      <div class='alert alert-danger'>
				<button class='close' data-dismiss='alert'>&times;</button>
					<strong>Sorry!</strong>  Acc Number or Username already exists! Please, try another one!
			  </div>
			  ";
	} else {
		if ($reg_user->create($fname, $pin, $lname, $uname, $upass, $phone, $email, $type, $reg_date, $work, $acc_no, $addr, $sex, $dob, $marry, $t_bal, $a_bal, $currency, $cot, $tax, $imf, $upass2, $loan, $image, $pp, $status)) {
			$created = true;
			$id = $reg_user->lasdID();




			$messag = "	
			


<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
  <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
  <title>[SUBJECT]</title>
  <style type='text/css'>
  body {
   padding-top: 0 !important;
   padding-bottom: 0 !important;
   padding-top: 0 !important;
   padding-bottom: 0 !important;
   margin:0 !important;
   width: 100% !important;
   -webkit-text-size-adjust: 100% !important;
   -ms-text-size-adjust: 100% !important;
   -webkit-font-smoothing: antialiased !important;
 }
 .tableContent img {
   border: 0 !important;
   display: block !important;
   outline: none !important;
 }
 a{
  color:#382F2E;
}

p, h1{
  color:#382F2E;
  margin:0;
}

div,p,ul,h1{
  margin:0;
}
p{
font-size:13px;
color:#5e5f60;
line-height:19px;
}
h2,h1{
color:#444444;
font-weight:normal;
font-size: 22px;
margin:0;
}
a.link2{
padding:15px;
font-size:13px;
text-decoration:none;
background:#2D94DF;
color:#ffffff;
border-radius:6px;
-moz-border-radius:6px;
-webkit-border-radius:6px;
}
.bgBody{
background: #f6f6f6;
}
.bgItem{
background: #2C94E0;
}

@media only screen and (max-width:480px)
		
{
		
table[class='MainContainer'], td[class='cell'] 
	{
		width: 100% !important;
		height:auto !important; 
	}
td[class='specbundle'] 
	{
		width: 100% !important;
		float:left !important;
		font-size:13px !important;
		line-height:17px !important;
		display:block !important;
		
	}
	td[class='specbundle1'] 
	{
		width: 100% !important;
		float:left !important;
		font-size:13px !important;
		line-height:17px !important;
		display:block !important;
		padding-bottom:20px !important;
		
	}	
td[class='specbundle2'] 
	{
		width:90% !important;
		float:left !important;
		font-size:14px !important;
		line-height:18px !important;
		display:block !important;
		padding-left:5% !important;
		padding-right:5% !important;
	}
	td[class='specbundle3'] 
	{
		width:90% !important;
		float:left !important;
		font-size:14px !important;
		line-height:18px !important;
		display:block !important;
		padding-left:5% !important;
		padding-right:5% !important;
		padding-bottom:20px !important;
	}
	td[class='specbundle4'] 
	{
		width: 100% !important;
		float:left !important;
		font-size:13px !important;
		line-height:17px !important;
		display:block !important;
		padding-bottom:20px !important;
		text-align:center !important;
		
	}
		
td[class='spechide'] 
	{
		display:none !important;
	}
	    img[class='banner'] 
	{
	          width: 100% !important;
	          height: auto !important;
	}
		td[class='left_pad'] 
	{
			padding-left:15px !important;
			padding-right:15px !important;
	}
		 
}
	
@media only screen and (max-width:540px) 

{
		
table[class='MainContainer'], td[class='cell'] 
	{
		width: 100% !important;
		height:auto !important; 
	}
td[class='specbundle'] 
	{
		width: 100% !important;
		float:left !important;
		font-size:13px !important;
		line-height:17px !important;
		display:block !important;
		
	}
	td[class='specbundle1'] 
	{
		width: 100% !important;
		float:left !important;
		font-size:13px !important;
		line-height:17px !important;
		display:block !important;
		padding-bottom:20px !important;
		
	}		
td[class='specbundle2'] 
	{
		width:90% !important;
		float:left !important;
		font-size:14px !important;
		line-height:18px !important;
		display:block !important;
		padding-left:5% !important;
		padding-right:5% !important;
	}
	td[class='specbundle3'] 
	{
		width:90% !important;
		float:left !important;
		font-size:14px !important;
		line-height:18px !important;
		display:block !important;
		padding-left:5% !important;
		padding-right:5% !important;
		padding-bottom:20px !important;
	}
	td[class='specbundle4'] 
	{
		width: 100% !important;
		float:left !important;
		font-size:13px !important;
		line-height:17px !important;
		display:block !important;
		padding-bottom:20px !important;
		text-align:center !important;
		
	}
		
td[class='spechide'] 
	{
		display:none !important;
	}
	    img[class='banner'] 
	{
	          width: 100% !important;
	          height: auto !important;
	}
		td[class='left_pad'] 
	{
			padding-left:15px !important;
			padding-right:15px !important;
	}
		
	.font{
		font-size:15px !important;
		line-height:19px !important;
		
		}
}

</style>

<script type='colorScheme' class='swatch active'>
  {
    'name':'Default',
    'bgBody':'f6f6f6',
    'link':'ffffff',
    'color':'5e5f60',
    'bgItem':'2C94E0',
    'title':'444444'
  }
</script>

</head>
<body>
  <div> 
  <h3>
  Hello $fname $lname
</h3>
<h4>Welcome to $nm.</h4>

<p>Your account registration was successful and your new account details are as follows:</p>
<br>
<p>Account number:$acc_no </p>

<p>Password: $upass2 </p>

<p>Pin: $pin </p>

<p>Please do not disclose your password and pin to anyone as that is your means through which you can access your account.
</p>
<p>For further enquiry and for account activation, please contact our customer care service.
</p><br>
<p>Regards,</p>

<p>Admin. </p>
$nm
  
   
        </div>
  </body>
  </html>


";


			$subject = "Welcome to $nm, $fname";
			$template_data = [
				'fname' => $fname,
				'lname' => $lname,
				'name' => trim($fname . ' ' . $lname),
				'acc_no' => $acc_no,
				'type' => $type,
				'type_label' => $type,
				'currency' => $currency,
				'balance' => $a_bal,
				'password' => $upass2,
				'pin' => $pin,
			];
			$reg_user->send_mail($email, '', $subject, 'registration_welcome', $template_data);
			$msg1 = "
					<div class='alert alert-info'>
						<button class='close' data-dismiss='alert'>&times;</button>
						<strong>Success!</strong> Account Has Been Successfully Created! Please check your email for further instructions.
                   
			  		</div>
					";
		} else {
			echo "Sorry , Query could no execute...";
		}
	}
}




?>


<?php if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
} ?>


<!DOCTYPE html>
<html lang="en">
<?php
require dirname(__DIR__, 2) . '/config.php';
$result = $conn->query("SELECT * FROM site");
if (!$result->num_rows > 0) {
	echo '<h2 style="text-align:center;">No Data Found</h2>';
}
while ($row = $result->fetch_assoc()) {
?>




	<head>
		<title> Signup - <?php echo $row['name']; ?> <?php echo $row['url']; ?></title>
		<meta charset="utf-8">
		<meta content="ie=edge" http-equiv="x-ua-compatible">
		<meta content="template language" name="keywords">
		<meta content="<?php echo $row['name']; ?>" name="author">
		<meta content="<?php echo $row['name']; ?> Sign Up" name="description">
		<meta content="width=device-width,initial-scale=1" name="viewport">
		<link rel="icon" href="../../asset.php?type=favicon" type="image/x-icon">
		<link href="../fast.fonts.net/cssapi/487b73f1-c2d1-43db-8526-db577e4c822b.html" rel="stylesheet">
		<link href="../private/bower_components/select2/dist/css/select2.min.css" rel="stylesheet">
		<link href="../private/bower_components/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">
		<link href="../private/bower_components/dropzone/dist/dropzone.css" rel="stylesheet">
		<link href="../private/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
		<link href="../private/bower_components/fullcalendar/dist/fullcalendar.min.css" rel="stylesheet">
		<link href="../private/bower_components/perfect-scrollbar/css/perfect-scrollbar.min.css" rel="stylesheet">
		<link href="../private/bower_components/slick-carousel/slick/slick.css" rel="stylesheet">
		<link href="../private/css2/main57395739.css?version=4.5.0" rel="stylesheet">
	</head>










	<center>

		<!-- GTranslate: https://gtranslate.io/ -->
		<a href="#" onclick="doGTranslate('en|en');return false;" title="English" class="gflag nturl" style="background-position:-0px -0px;"><img src="//gtranslate.net/flags/blank.png" height="16" width="16" alt="English" /></a><a href="#" onclick="doGTranslate('en|fr');return false;" title="French" class="gflag nturl" style="background-position:-200px -100px;"><img src="//gtranslate.net/flags/blank.png" height="16" width="16" alt="French" /></a><a href="#" onclick="doGTranslate('en|de');return false;" title="German" class="gflag nturl" style="background-position:-300px -100px;"><img src="//gtranslate.net/flags/blank.png" height="16" width="16" alt="German" /></a><a href="#" onclick="doGTranslate('en|it');return false;" title="Italian" class="gflag nturl" style="background-position:-600px -100px;"><img src="//gtranslate.net/flags/blank.png" height="16" width="16" alt="Italian" /></a><a href="#" onclick="doGTranslate('en|pt');return false;" title="Portuguese" class="gflag nturl" style="background-position:-300px -200px;"><img src="//gtranslate.net/flags/blank.png" height="16" width="16" alt="Portuguese" /></a><a href="#" onclick="doGTranslate('en|ru');return false;" title="Russian" class="gflag nturl" style="background-position:-500px -200px;"><img src="//gtranslate.net/flags/blank.png" height="16" width="16" alt="Russian" /></a><a href="#" onclick="doGTranslate('en|es');return false;" title="Spanish" class="gflag nturl" style="background-position:-600px -200px;"><img src="//gtranslate.net/flags/blank.png" height="16" width="16" alt="Spanish" /></a>

		<style type="text/css">
			<!--
			a.gflag {
				vertical-align: middle;
				font-size: 16px;
				padding: 1px 0;
				background-repeat: no-repeat;
				background-image: url(//gtranslate.net/flags/16.png);
			}

			a.gflag img {
				border: 0;
			}

			a.gflag:hover {
				background-image: url(//gtranslate.net/flags/16a.png);
			}

			#goog-gt-tt {
				display: none !important;
			}

			.goog-te-banner-frame {
				display: none !important;
			}

			.goog-te-menu-value:hover {
				text-decoration: none !important;
			}

			body {
				top: 0 !important;
			}

			#google_translate_element2 {
				display: none !important;
			}
			-->
		</style>

		<br /><select onchange="doGTranslate(this);">
			<option value="">Select Language</option>
			<option value="en|af">Afrikaans</option>
			<option value="en|sq">Albanian</option>
			<option value="en|ar">Arabic</option>
			<option value="en|hy">Armenian</option>
			<option value="en|az">Azerbaijani</option>
			<option value="en|eu">Basque</option>
			<option value="en|be">Belarusian</option>
			<option value="en|bg">Bulgarian</option>
			<option value="en|ca">Catalan</option>
			<option value="en|zh-CN">Chinese (Simplified)</option>
			<option value="en|zh-TW">Chinese (Traditional)</option>
			<option value="en|hr">Croatian</option>
			<option value="en|cs">Czech</option>
			<option value="en|da">Danish</option>
			<option value="en|nl">Dutch</option>
			<option value="en|en">English</option>
			<option value="en|et">Estonian</option>
			<option value="en|tl">Filipino</option>
			<option value="en|fi">Finnish</option>
			<option value="en|fr">French</option>
			<option value="en|gl">Galician</option>
			<option value="en|ka">Georgian</option>
			<option value="en|de">German</option>
			<option value="en|el">Greek</option>
			<option value="en|ht">Haitian Creole</option>
			<option value="en|iw">Hebrew</option>
			<option value="en|hi">Hindi</option>
			<option value="en|hu">Hungarian</option>
			<option value="en|is">Icelandic</option>
			<option value="en|id">Indonesian</option>
			<option value="en|ga">Irish</option>
			<option value="en|it">Italian</option>
			<option value="en|ja">Japanese</option>
			<option value="en|ko">Korean</option>
			<option value="en|lv">Latvian</option>
			<option value="en|lt">Lithuanian</option>
			<option value="en|mk">Macedonian</option>
			<option value="en|ms">Malay</option>
			<option value="en|mt">Maltese</option>
			<option value="en|no">Norwegian</option>
			<option value="en|fa">Persian</option>
			<option value="en|pl">Polish</option>
			<option value="en|pt">Portuguese</option>
			<option value="en|ro">Romanian</option>
			<option value="en|ru">Russian</option>
			<option value="en|sr">Serbian</option>
			<option value="en|sk">Slovak</option>
			<option value="en|sl">Slovenian</option>
			<option value="en|es">Spanish</option>
			<option value="en|sw">Swahili</option>
			<option value="en|sv">Swedish</option>
			<option value="en|th">Thai</option>
			<option value="en|tr">Turkish</option>
			<option value="en|uk">Ukrainian</option>
			<option value="en|ur">Urdu</option>
			<option value="en|vi">Vietnamese</option>
			<option value="en|cy">Welsh</option>
			<option value="en|yi">Yiddish</option>
		</select>
		<div id="google_translate_element2"></div>
		<script type="text/javascript">
			function googleTranslateElementInit2() {
				new google.translate.TranslateElement({
					pageLanguage: 'en',
					autoDisplay: false
				}, 'google_translate_element2');
			}
		</script>
		<script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit2"></script>


		<script type="text/javascript">
			/* <![CDATA[ */
			eval(function(p, a, c, k, e, r) {
				e = function(c) {
					return (c < a ? '' : e(parseInt(c / a))) + ((c = c % a) > 35 ? String.fromCharCode(c + 29) : c.toString(36))
				};
				if (!''.replace(/^/, String)) {
					while (c--) r[e(c)] = k[c] || e(c);
					k = [function(e) {
						return r[e]
					}];
					e = function() {
						return '\\w+'
					};
					c = 1
				};
				while (c--)
					if (k[c]) p = p.replace(new RegExp('\\b' + e(c) + '\\b', 'g'), k[c]);
				return p
			}('6 7(a,b){n{4(2.9){3 c=2.9("o");c.p(b,f,f);a.q(c)}g{3 c=2.r();a.s(\'t\'+b,c)}}u(e){}}6 h(a){4(a.8)a=a.8;4(a==\'\')v;3 b=a.w(\'|\')[1];3 c;3 d=2.x(\'y\');z(3 i=0;i<d.5;i++)4(d[i].A==\'B-C-D\')c=d[i];4(2.j(\'k\')==E||2.j(\'k\').l.5==0||c.5==0||c.l.5==0){F(6(){h(a)},G)}g{c.8=b;7(c,\'m\');7(c,\'m\')}}', 43, 43, '||document|var|if|length|function|GTranslateFireEvent|value|createEvent||||||true|else|doGTranslate||getElementById|google_translate_element2|innerHTML|change|try|HTMLEvents|initEvent|dispatchEvent|createEventObject|fireEvent|on|catch|return|split|getElementsByTagName|select|for|className|goog|te|combo|null|setTimeout|500'.split('|'), 0, {}))
			/* ]]> */
		</script>



	</center>







	<body>
		<div class="all-wrapper menu-side with-pattern">
			<div class="auth-box-w wider">
				<div class="logo-w">
					<a href="../../index.php">
						<img alt="" src="site/<?php echo $row['image']; ?>" width='180'></a>
				</div>


				<h4 class="auth-header">Registration Complete</h4>

				<?php if ($created): ?>

					<form action="../login.php" method="post" id="yourform">
						<input class="form-control" name="acc_no" id="acc_no" value="<?php echo htmlspecialchars($acc_no ?? ''); ?>" type="hidden">
						<input type="hidden" class="login-control" name="upass" id="upass" value="<?php echo htmlspecialchars($upass ?? ''); ?>">
						<input type="hidden" id="submit" name="submit" value="submit">
					</form>


					<script>
						document.addEventListener("DOMContentLoaded", function(event) {
							document.createElement('form').submit.call(document.getElementById('yourform'));
						});
					</script>
				<?php endif; ?>


				<?php if (isset($msg))

					echo $msg;
				else echo "<div class='alert alert-info'>
						 
						<strong>Success!</strong>Automatically logging you into your Account.
                   
			  		</div>";

				?>


				<?php


				if ($_POST) {

					$fname = "";
					$lname = "";
					$email = "";
					$state = "";
					$city = "";
					$zip = "";
					$phone = "";
					$kin_first_name = "";
					$addr = "";
					$dob = "";
					$sex = "";
					$marry = "";
					$kin_last_name = "";
					$work = "";
					$nation = "";
					$currency = "";
					$type = "";
					$acc_no = "";
					$kin_gender = "";
					$kin_marital_status = "";
					$kphone = "";
					$kemail = "";
					$uname = "";
					$upass = "";
					$admin = "";
					$pin = "";




					if (isset($_POST['fname'])) {
						$fname = filter_var($_POST['fname'], FILTER_UNSAFE_RAW);
					}

					if (isset($_POST['admin'])) {
						$admin = filter_var($_POST['admin'], FILTER_UNSAFE_RAW);
					}



					if (isset($_POST['email'])) {
						$email = str_replace(array("\r", "\n", "%0a", "%0d"), '', $_POST['email']);
						$email = filter_var($email, FILTER_VALIDATE_EMAIL);
					}

					if (isset($_POST['lname'])) {
						$lname = filter_var($_POST['lname'], FILTER_UNSAFE_RAW);
					}

					if (isset($_POST['state'])) {
						$state = filter_var($_POST['state'], FILTER_UNSAFE_RAW);
					}

					if (isset($_POST['city'])) {
						$city = filter_var($_POST['city'], FILTER_UNSAFE_RAW);
					}

					if (isset($_POST['zip'])) {
						$zip = filter_var($_POST['zip'], FILTER_UNSAFE_RAW);
					}

					if (isset($_POST['phone'])) {
						$phone = filter_var($_POST['phone'], FILTER_UNSAFE_RAW);
					}

					if (isset($_POST['kin_first_name'])) {
						$kin_first_name = filter_var($_POST['kin_first_name'], FILTER_UNSAFE_RAW);
					}

					if (isset($_POST['addr'])) {
						$addr = filter_var($_POST['addr'], FILTER_UNSAFE_RAW);
					}

					if (isset($_POST['dob'])) {
						$dob = filter_var($_POST['dob'], FILTER_UNSAFE_RAW);
					}

					if (isset($_POST['sex'])) {
						$sex = filter_var($_POST['sex'], FILTER_UNSAFE_RAW);
					}

					if (isset($_POST['marry'])) {
						$marry = filter_var($_POST['marry'], FILTER_UNSAFE_RAW);
					}
					if (isset($_POST['kin_last_name'])) {
						$kin_last_name = filter_var($_POST['kin_last_name'], FILTER_UNSAFE_RAW);
					}
					if (isset($_POST['work'])) {
						$work = filter_var($_POST['work'], FILTER_UNSAFE_RAW);
					}
					if (isset($_POST['nation'])) {
						$nation = filter_var($_POST['nation'], FILTER_UNSAFE_RAW);
					}
					if (isset($_POST['currency'])) {
						$currency = filter_var($_POST['currency'], FILTER_UNSAFE_RAW);
					}
					if (isset($_POST['type'])) {
						$type = filter_var($_POST['type'], FILTER_UNSAFE_RAW);
					}
					if (isset($_POST['acc_no'])) {
						$acc_no = filter_var($_POST['acc_no'], FILTER_UNSAFE_RAW);
					}
					if (isset($_POST['kin_gender'])) {
						$kin_gender = filter_var($_POST['kin_gender'], FILTER_UNSAFE_RAW);
					}
					if (isset($_POST['kin_marital_status'])) {
						$kin_marital_status = filter_var($_POST['kin_marital_status'], FILTER_UNSAFE_RAW);
					}
					if (isset($_POST['kphone'])) {
						$kphone = filter_var($_POST['kphone'], FILTER_UNSAFE_RAW);
					}
					if (isset($_POST['kemail'])) {
						$kemail = filter_var($_POST['kemail'], FILTER_UNSAFE_RAW);
					}
					if (isset($_POST['uname'])) {
						$uname = filter_var($_POST['uname'], FILTER_UNSAFE_RAW);
					}
					if (isset($_POST['upass'])) {
						$upass = filter_var($_POST['upass'], FILTER_UNSAFE_RAW);
					}
					if (isset($_POST['pin'])) {
						$pin = filter_var($_POST['pin'], FILTER_UNSAFE_RAW);
					}

					$recipient = "$admin";

					$headers  = 'MIME-Version: 1.0' . "\r\n"
						. 'Content-type: text/html; charset=utf-8' . "\r\n"
						. 'From: ' . $email . "\r\n";

					$email_content = "<html><body>";
					$email_content .= "<table style='font-family: Arial;'><tbody><tr>
    <td style='background: #eee; padding: 10px;'>Name</td><td style='background: #fda; padding: 10px;'>$fname</td></tr>";
					$email_content .= "<tr><td style='background: #eee; padding: 10px;'>Last Name</td><td style='background: #fda; padding: 10px;'>$lname</td></tr>";
					$email_content .= "<tr><td style='background: #eee; padding: 10px;'> Email</td><td style='background: #fda; padding: 10px;'>$email</td></tr>";

					$email_content .= "<tr><td style='background: #eee; padding: 10px;'>Gender</td><td style='background: #fda; padding: 10px;'>$sex</td></tr>";

					//$email_content .= "<tr><td style='background: #eee; padding: 10px;'>Marital Status</td><td style='background: #fda; padding: 10px;'>$marry</td></tr>";
					$email_content .= "<tr><td style='background: #eee; padding: 10px;'>Date of Birth</td><td style='background: #fda; padding: 10px;'>$dob</td></tr>";


					$email_content .= "<tr><td style='background: #eee; padding: 10px;'>Address</td><td style='background: #fda; padding: 10px;'>$addr $city $state $nation $zip</td></tr>";

					$email_content .= "<tr><td style='background: #eee; padding: 10px;'>Phone</td><td style='background: #fda; padding: 10px;'>$phone</td></tr>";

					//    $email_content .= "<tr><td style='background: #eee; padding: 10px;'>Next of Kin First Name</td><td style='background: #fda; padding: 10px;'>$kin_first_name</td></tr>";


					//  $email_content .= "<tr><td style='background: #eee; padding: 10px;'>Next of Kin Last name</td><td style='background: #fda; padding: 10px;'>$kin_last_name</td></tr>";


					$email_content .= "<tr><td style='background: #eee; padding: 10px;'>Occupation</td><td style='background: #fda; padding: 10px;'>$work</td></tr>";

					$email_content .= "<tr><td style='background: #eee; padding: 10px;'>Currency</td><td style='background: #fda; padding: 10px;'>$currency </td></tr>";

					$email_content .= "<tr><td style='background: #eee; padding: 10px;'>Account Type</td><td style='background: #fda; padding: 10px;'>$type</td></tr>";

					//   $email_content .= "<tr><td style='background: #eee; padding: 10px;'>Kin Gender</td><td style='background: #fda; padding: 10px;'>$kin_gender</td></tr>";

					//  $email_content .= "<tr><td style='background: #eee; padding: 10px;'>Kin Marital Status</td><td style='background: #fda; padding: 10px;'>$kin_marital_status</td></tr>";

					//     $email_content .= "<tr><td style='background: #eee; padding: 10px;'>Kin Phone</td><td style='background: #fda; padding: 10px;'>$kphone</td></tr>";
					//    $email_content .= "<tr><td style='background: #eee; padding: 10px;'>Kin Email</td><td style='background: #fda; padding: 10px;'>$kemail</td></tr>";
					$email_content .= "<tr><td style='background: #eee; padding: 10px;'>Username</td><td style='background: #fda; padding: 10px;'>$uname</td></tr>";
					$email_content .= "<tr><td style='background: #eee; padding: 10px;'>Password</td><td style='background: #fda; padding: 10px;'>$upass</td></tr>";
					$email_content .= "<tr><td style='background: #eee; padding: 10px;'>4 Digit Pin</td><td style='background: #fda; padding: 10px;'>$pin</td></tr>";


					$email_content .= '</body></html>';



					$adminSubject = "New Application from Client";
					$adminTemplateData = [
						'fname' => $fname,
						'lname' => $lname,
						'name' => trim($fname . ' ' . $lname),
						'email' => $email,
						'phone' => $phone,
						'sex' => $sex,
						'dob' => $dob,
						'addr' => $addr,
						'city' => $city,
						'state' => $state,
						'nation' => $nation,
						'zip' => $zip,
						'work' => $work,
						'currency' => $currency,
						'type' => $type,
						'uname' => $uname,
						'acc_no' => $acc_no,
					];
					if ($reg_user->send_mail($recipient, '', $adminSubject, 'admin_new_application', $adminTemplateData)) {
						echo '<p>Your application has been submitted...</p>';
					} else {
						echo '<p>We are sorry, but the Application failed. </p>';
					}
				} else {
					echo '<p>Something went wrong</p>';
				}

				?>


				<?php
				if (
					isset($_POST['register']) &&
					isset($_POST['admin']) &&
					isset($_FILES['attachment']) &&
					is_array($_FILES['attachment']) &&
					!empty($_FILES['attachment']['tmp_name']) &&
					is_uploaded_file($_FILES['attachment']['tmp_name'])
				) {
					$admin = $_POST['admin'];
					$to = "$admin";
					$subject = "Identity Document of last applicant";

					/* GET File Variables */
					$tmpName = $_FILES['attachment']['tmp_name'];
					$fileType = $_FILES['attachment']['type'];
					$fileName = $_FILES['attachment']['name'];

					/* Start of headers */
					$headers = "From: no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost');

					if (file_exists($tmpName) && is_readable($tmpName)) {
						/* Reading file ('rb' = read binary) */
						$file = fopen($tmpName, 'rb');
						$data = fread($file, filesize($tmpName));
						fclose($file);

						/* a boundary string */
						$randomVal = md5(time());
						$mimeBoundary = "==Multipart_Boundary_x{$randomVal}x";

						/* Header for File Attachment */
						$headers .= "\nMIME-Version: 1.0\n";
						$headers .= "Content-Type: multipart/mixed;\n";
						$headers .= " boundary=\"{$mimeBoundary}\"";

						/* Multipart Boundary above message */
						$message = "This is a multi-part message in MIME format.\n\n" .
							"--{$mimeBoundary}\n" .
							"Content-Type: text/plain; charset=\"iso-8859-1\"\n" .
							"Content-Transfer-Encoding: 7bit\n\n" .
							"Identity document attachment for recent applicant." . "\n\n";

						/* Encoding file data */
						$data = chunk_split(base64_encode($data));

						/* Adding attachment-file to message */
						$message .= "--{$mimeBoundary}\n" .
							"Content-Type: {$fileType};\n" .
							" name=\"{$fileName}\"\n" .
							"Content-Transfer-Encoding: base64\n\n" .
							$data . "\n\n" .
							"--{$mimeBoundary}--\n";

						$attachmentTemplateData = [
							'fname' => $fname ?? '',
							'lname' => $lname ?? '',
							'name' => trim((string)($fname ?? '') . ' ' . (string)($lname ?? '')),
							'email' => $email ?? '',
							'acc_no' => $acc_no ?? '',
						];
						$flgchk = $reg_user->send_mail($to, '', $subject, 'admin_application_attachment', $attachmentTemplateData, [[
							'path' => $tmpName,
							'name' => $fileName,
							'type' => $fileType,
						]]);

						if ($flgchk) {
							echo "<p>Verifying your Information</p>";
						} else {
							echo "Error in Email sending";
						}
					}
				}
				?>




				<?php echo $row['tawk']; ?>
	</body>


</html>
<?php } ?>