<?php
session_start();
require_once 'class.admin.php';
require dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__) . '/partials/auto-migrate.php';
require_once dirname(__DIR__) . '/partials/iban-tools.php';
include_once('session.php');
if (!isset($_SESSION['email'])) {

  header("Location: login.php");

  exit();
}
$reg_user = new USER();

// Load transaction code settings
function ca_setting_get(mysqli $conn, string $key, string $default = ''): string
{
  $safe = $conn->real_escape_string($key);
  $res = $conn->query("SELECT `value` FROM site_settings WHERE `key`='$safe' LIMIT 1");
  if ($res && $res->num_rows > 0) {
    $r = $res->fetch_assoc();
    return (string)($r['value'] ?? $default);
  }
  return $default;
}
$txMaxCodes = (int)ca_setting_get($conn, 'tx_max_codes', '3');
$caCodeNames = [0, 'COT', 'TAX', 'IMF', 'LPPI', 'Code 5'];
for ($i = 1; $i <= 5; $i++) $caCodeNames[$i] = ca_setting_get($conn, "tx_code{$i}_name", $caCodeNames[$i]);

$statusOptions = ['Active', 'Dormant/Inactive', 'Disabled', 'Closed'];
$loginMethodOptions = ['pin' => 'PIN', 'otp' => 'OTP'];
$authMethodOptions = [
  'pin'       => 'PIN only',
  'otp'       => 'OTP only',
  'codes'     => 'Codes only',
  'codes_otp' => 'Codes + OTP',
  'codes_pin' => 'Codes + PIN',
];

if (isset($_POST['create'])) {
  $createErrors = [];
  $allowedImageExt = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
  $uploadDir = __DIR__ . '/foto/';

  $fname = trim((string)($_POST['fname'] ?? ''));
  $lname = trim((string)($_POST['lname'] ?? ''));
  $uname = trim((string)($_POST['uname'] ?? ''));
  $upass = (string)($_POST['upass'] ?? '');
  $upass2 = trim((string)($_POST['upass2'] ?? ''));
  $phone = trim((string)($_POST['phone'] ?? ''));
  $email = trim((string)($_POST['email'] ?? ''));
  $type = trim((string)($_POST['type'] ?? ''));
  $reg_date = trim((string)($_POST['reg_date'] ?? ''));
  $work = trim((string)($_POST['work'] ?? ''));
  $acc_no = preg_replace('/\D+/', '', trim((string)($_POST['acc_no'] ?? '')));
  $addr = trim((string)($_POST['addr'] ?? ''));
  $sex = trim((string)($_POST['sex'] ?? 'Male'));
  $dob = trim((string)($_POST['dob'] ?? ''));
  $marry = trim((string)($_POST['marry'] ?? 'Single'));
  $t_bal = (float)($_POST['t_bal'] ?? 0);
  $a_bal = (float)($_POST['a_bal'] ?? 0);
  $currency = strtoupper(trim((string)($_POST['currency'] ?? 'USD')));
  $cot = trim((string)($_POST['cot'] ?? ''));
  $tax = trim((string)($_POST['tax'] ?? ''));
  $imf = trim((string)($_POST['imf'] ?? ''));
  $lppi = trim((string)($_POST['lppi'] ?? ''));
  $code5 = trim((string)($_POST['code5'] ?? ''));

  $pin = trim((string)($_POST['pin'] ?? ($_POST['pin'] ?? '')));
  $pin = $pin;

  if ($fname === '' || $lname === '' || $uname === '' || $upass === '' || $email === '' || $type === '') {
    $createErrors[] = 'Please complete all required fields.';
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $createErrors[] = 'Please provide a valid email address.';
  }
  if (!preg_match('/^\d{10}$/', (string)$acc_no)) {
    $createErrors[] = 'Account ID must be exactly 10 digits.';
  }
  if ($pin === '' || !preg_match('/^\d{4}$/', $pin)) {
    $createErrors[] = 'Security PIN must be exactly 4 digits.';
  }
  if ($upass2 !== '' && $upass !== $upass2) {
    $createErrors[] = 'Password and secondary password do not match.';
  }
  if ($currency === '') {
    $currency = 'USD';
  }

  $uploadOne = static function (string $field, string $label) use (&$createErrors, $allowedImageExt, $uploadDir): string {
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) {
      return 'user.png';
    }
    $err = (int)($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($err === UPLOAD_ERR_NO_FILE) {
      return 'user.png';
    }
    if ($err !== UPLOAD_ERR_OK) {
      $createErrors[] = $label . ' upload failed.';
      return 'user.png';
    }

    $original = (string)($_FILES[$field]['name'] ?? '');
    $tmp = (string)($_FILES[$field]['tmp_name'] ?? '');
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedImageExt, true)) {
      $createErrors[] = $label . ' must be JPG, PNG, GIF, or WEBP.';
      return 'user.png';
    }
    $size = (int)($_FILES[$field]['size'] ?? 0);
    if ($size > 2097152) {
      $createErrors[] = $label . ' size must be 2 MB or less.';
      return 'user.png';
    }

    $name = 'ca_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
    if (!@move_uploaded_file($tmp, $uploadDir . $name)) {
      $createErrors[] = $label . ' could not be saved.';
      return 'user.png';
    }
    return $name;
  };

  $pp = $uploadOne('pp', 'Profile photo');
  $image = $uploadOne('image', 'ID document');

  $status = trim((string)($_POST['status'] ?? 'Active'));
  if (!in_array($status, $statusOptions, true)) {
    $status = 'Active';
  }

  $login_method = strtolower(trim((string)($_POST['login_method'] ?? 'pin')));
  if (!array_key_exists($login_method, $loginMethodOptions)) {
    $login_method = 'pin';
  }

  $auth_method = trim((string)($_POST['auth_method'] ?? 'codes'));
  if (!array_key_exists($auth_method, $authMethodOptions)) {
    $auth_method = 'codes';
  }

  if (!empty($createErrors)) {
    $msg = "
          <div class='alert alert-danger'>
        <button class='close' data-dismiss='alert'>&times;</button>
          <strong>" . htmlspecialchars(implode(' ', $createErrors), ENT_QUOTES, 'UTF-8') . "</strong>
        </div>
        ";
  } else {
    $stct = $reg_user->runQuery("SELECT * FROM site WHERE id = '20'");
    $stct->execute();
    $rowp = $stct->fetch(PDO::FETCH_ASSOC);

    $mall = $rowp['email'];
    $url = $rowp['url'];
    $nm = $rowp['name'];
    $add = $rowp['addr'];

    $stmt = $reg_user->runQuery("SELECT * FROM account WHERE acc_no=:acc_no");
    $stmt1 = $reg_user->runQuery("SELECT * FROM account WHERE email=:email OR uname=:uname");
    $stmt->execute(array(":acc_no" => $acc_no));
    $stmt1->execute(array(":email" => $email, ":uname" => $uname));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);


    if ($stmt->rowCount() > 0 || $stmt1->rowCount() > 0) {
      $msg = "
		      <div class='alert alert-danger'>
				<button class='close' data-dismiss='alert'>&times;</button>
          <strong>Sorry!</strong>  Account ID, email, or username already exists. Please, try another one!
			  </div>
			  ";
    } else {
      if ($reg_user->create($fname, $pin, $lname, $uname, $upass, $upass2, $phone, $email, $type, $reg_date, $work, $acc_no, $addr, $sex, $dob, $marry, $t_bal, $a_bal, $currency, $cot, $tax, $lppi, $imf, $code5, $image, $pp, $status, $login_method, $auth_method)) {
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
color:#99A1A6;
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
    'color':'99A1A6',
    'bgItem':'2C94E0',
    'title':'444444'
  }
</script>

</head>
<body paddingwidth='0' paddingheight='0' bgcolor='#d1d3d4'  style=' margin-left:5px; margin-right:5px; margin-bottom:0px; margin-top:0px;padding-top: 0; padding-bottom: 0; background-repeat: repeat; width: 100% !important; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; -webkit-font-smoothing: antialiased;' offset='0' toppadding='0' leftpadding='0'>
  <table width='100%' border='0' cellspacing='0' cellpadding='0' class='tableContent bgBody' align='center'  style='font-family:Helvetica, Arial,serif;'>
  
    <!-- =============================== Header ====================================== -->

  <tr>
    <td class='movableContentContainer' >
    	<div class='movableContent' style='border: 0px; padding-top: 0px; position: relative;'>
        	<table width='100%' border='0' cellspacing='0' cellpadding='0' align='center' valign='top'>
                   <tr><td height='25'  colspan='3'></td></tr>

                    <tr>
                      <td valign='top'  colspan='3'>
                        <table width='600' border='0' bgcolor='#2196F3' cellspacing='0' cellpadding='0' align='center' valign='top' class='MainContainer'>
                          <tr>
                            <td align='left' valign='middle' width='200'>
                              <div class='contentEditableContainer contentImageEditable'>
                                <div class='contentEditable' >
                                  <img src='img/sc.png' alt='' data-default='placeholder' data-max-width='100' width='118' height='80' >
								  <b style='font-size:1.5em; color:#fff;'></b>
                                </div>
                              </div>
                            </td>

                            
                          </tr>
                        </table>
                      </td>
                    </tr>
                </table>
        </div>
        <div class='movableContent' style='border: 0px; padding-top: 0px; position: relative;'>
        	<table width='100%' border='0' cellspacing='0' cellpadding='0' align='center' valign='top'>
                        <tr><td height='25'  ></td></tr>

                        <tr>
                          <td height='290'  bgcolor='#2196F3'>
                            <table align='center' width='600' border='0' cellspacing='0' cellpadding='0' class='MainContainer'>
  <tr>
    <td height='50'></td>
  </tr>
  <tr>
    <td><table width='100%' border='0' cellspacing='0' cellpadding='0'>
  <tr>
								<td width='400' valign='top' class='specbundle2'>
                                  <div class='contentEditableContainer contentImageEditable'>
                                    <div class='contentEditable' >
                                      <h1 style='font-size:40px;font-weight:normal;color:#ffffff;line-height:40px;'>$nm</h1>
                                    </div>
                                  </div>
                                </td>
    <td class='specbundle3'>&nbsp;</td>
    <td width='250' valign='top' class='specbundle4'>
                                  <table width='250' border='0' cellspacing='0' cellpadding='0' align='center' valign='top'>
                                    <tr><td colspan='3' height='10'></td></tr>

                                    <tr>
                                      <td width='10'></td>
                                      <td width='230' valign='top'>
                                        <table width='230' border='0' cellspacing='0' cellpadding='0' align='center' valign='top'>
                                          <tr>
                                            <td valign='top'>
                                              <div class='contentEditableContainer contentTextEditable'>
                                                <div class='contentEditable' >
                                                  <h1 style='font-size:20px;font-weight:normal;color:#ffffff;line-height:19px;'>Congratulations, $fname</h1>
                                                </div>
                                              </div>
                                            </td>
                                          </tr>
                                          <tr><td height='18'></td></tr>
                                          <tr>
                                            <td valign='top'>
                                              <div class='contentEditableContainer contentTextEditable'>
                                                <div class='contentEditable' >
                                                  <p style='font-size:13px;color:#cfeafa;line-height:19px;'>Your account was successfully opened!<br>Please see the details of your account below.</p>
                                                </div>
                                              </div>
                                            </td>
                                          </tr>
                                          <tr><td height='33'></td></tr>
                                          <tr>
                                            <td>
                                              <div class='contentEditableContainer contentTextEditable'>
                                                <div class='contentEditable' >
                                                  
                                                </div>
                                              </div>
                                            </td>
                                          </tr>
                                          <tr><td height='15'></td></tr>
                                        </table>
                                      </td>
                                      <td width='10'></td>
                                    </tr>
                                  </table>
                                </td>
  </tr>
</table>
</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
</table>

                          </td>
                        </tr>

                        <tr><td height='25' ></td></tr>
                </table>
        </div>
        
        
        
        <div class='movableContent' style='border: 0px; padding-top: 0px; position: relative;'>
        	<table width='100%' border='0' cellspacing='0' cellpadding='0' align='center' valign='top'>
                  <tr>
                    <td>
                      <table width='600' border='0' cellspacing='0' cellpadding='0' align='center' valign='top' class='MainContainer'>
                        <tr>
                          <td>
                            <table width='100%' border='0' cellspacing='0' cellpadding='0' align='center' valign='top'>

                              <tr>
                                <td>
                                  <table width='600' border='0' cellspacing='0' cellpadding='0' align='center' class='MainContainer'>
                                    <tr><td height='10'>&nbsp;</td></tr>
                                    <tr><td style='border-bottom:1px solid #DDDDDD'></td></tr>
                                    <tr><td height='10'>&nbsp;</td></tr>
                                  </table>
                                </td>
                              </tr>

                              <tr><td height='28'>&nbsp;</td></tr>

                              <tr>
                                <td valign='top' align='center'>
                                  <div class='contentEditableContainer contentTextEditable'>
                                    <div class='contentEditable' >
                                      <h3><span style='color:#2196F3;'>$nm</span> Account Details</h3>
                                     <table style='border:1px solid black;padding:2px;' width='400'>
										
										<tr>
											<th style='text-align:left;'>Account Number</th>
											<td>$acc_no</td>
										</tr>
										
										<tr>
											<th style='text-align:left;'>Balance</th>
											<td>$currency $t_bal</td>
										</tr>
										<tr>
											<th style='text-align:left;'>Pending Debit</th>
											<td>$currency 0.00</td>
										</tr>
										<tr> 
											<th style='text-align:left;'>Pending Credit</th>
											<td>$currency 0.00</td>
										</tr>
										<tr style='background-color:#2196F3;'>
											<th style='text-align:left; color:#fff;'>Available Balance</th>
											<td style='color:#fff;'>$currency $a_bal</td>
										</tr>
                                     </table>
                                    </div>
									 <div class='contentEditable' ><br>
                                      <p style='font-weight:bold;font-size:13px;line-height: 30px; color:red;'>Please, note that your Internet Banking is automatically activated and you will need a combination of your account number and password to access your online banking at $url/secure </p>
                                    </div>
                                  </div>
                                </td>
                              </tr>

                              <tr><td height='28'>&nbsp;</td></tr>
                              
                              <tr>
                                <td valign='top' align='center'>
                                  <div class='contentEditableContainer contentTextEditable'>
                                    <div class='contentEditable' >
                                      <p style=' font-weight:bold;font-size:13px;line-height: 30px;'>$nm</p>
                                    </div>
                                  </div>
                                  <div class='contentEditableContainer contentTextEditable'>
                                    <div class='contentEditable' >
                                      <p style='color:#A8B0B6; font-size:13px;line-height: 15px;'>$add</p>
                                    </div>
                                  </div>
                                  <div class='contentEditableContainer contentTextEditable'>
                                    <div class='contentEditable' >
                                      <a target='_blank' href='' style='line-height: 20px;color:#A8B0B6; font-size:13px;'>$mall</a>
                                    </div>
                                    </div>
									<div class='contentEditableContainer contentTextEditable'>
									<div class='contentEditable' >
                                      <a target='_blank' href='$url' style='line-height: 20px;color:#A8B0B6; font-size:13px;'>$url</a>
                                    </div>
                                  </div>
                                  <div class='contentEditableContainer contentTextEditable'>
                                    
                                  </div>
                                </td>
                              </tr>

                              <tr><td height='28'>&nbsp;</td></tr>
                            </table>
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
        </div>
    </td>
  </tr>
</table>


  </body>
  </html>


";


        $subject = "Welcome to $nm, $fname - Your Account Has Been Created!";

        $ownerEsc = $conn->real_escape_string((string)$acc_no);
        $curCode = strtoupper(trim((string)$currency));
        if ($curCode === '') {
          $curCode = 'USD';
        }
        $curEsc = $conn->real_escape_string($curCode);
        $walletNo = $acc_no . '-' . $curCode;
        $walletNoEsc = $conn->real_escape_string($walletNo);
        $walletBal = (float)$a_bal;
        $conn->query("INSERT INTO customer_accounts (owner_acc_no, account_no, currency_code, balance, status, is_primary)
                    VALUES ('{$ownerEsc}', '{$walletNoEsc}', '{$curEsc}', {$walletBal}, 'active', 1)
                    ON DUPLICATE KEY UPDATE
                      account_no = VALUES(account_no),
                      balance = VALUES(balance),
                      status = 'active',
                      is_primary = 1");

        if (fw_customer_accounts_has_iban_columns($conn)) {
          $walletRowRes = $conn->query("SELECT id FROM customer_accounts WHERE owner_acc_no = '{$ownerEsc}' AND currency_code = '{$curEsc}' LIMIT 1");
          if ($walletRowRes && $walletRowRes->num_rows > 0) {
            $walletRow = $walletRowRes->fetch_assoc();
            $walletId = (int)($walletRow['id'] ?? 0);
            if ($walletId > 0) {
              $ibanCountry = fw_setting_get($conn, 'iban_country', 'GB');
              $ibanBankCode = fw_setting_get($conn, 'iban_bank_code', 'FWLT');
              $ibanData = fw_generate_iban((string)$acc_no, $curCode, $walletId, $ibanCountry, $ibanBankCode);
              $ibanEsc = $conn->real_escape_string($ibanData['iban']);
              $bbanEsc = $conn->real_escape_string($ibanData['bban']);
              $displayEsc = $conn->real_escape_string($ibanData['display']);
              $conn->query("UPDATE customer_accounts
                          SET iban = '{$ibanEsc}',
                              bban = '{$bbanEsc}',
                              account_display = '{$displayEsc}'
                          WHERE id = {$walletId}");
            }
          }
        }

        // Prepare account creation welcome template data
        $template_data = [
          'fname' => $fname,
          'lname' => $lname,
          'acc_no' => $acc_no,
          'type' => $type,
          'currency' => $currency,
          'balance' => $t_bal,
          'status' => $status
        ];

        $reg_user->send_mail($email, '', $subject, 'registration_welcome', $template_data);
        $msg1 = "
					<div class='alert alert-info'>
						<button class='close' data-dismiss='alert'>&times;</button>
						<strong>Success!</strong> Account Has Been Successfully Created!
                   
			  		</div>
					";
      } else {
        echo "Sorry , Query could no execute...";
      }
    }
  }
}
$pageTitle = 'Create Account';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if (isset($msg)) echo $msg; ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-4xl">
  <h2 class="font-semibold text-gray-800 mb-5">Create New Account</h2>
  <form method="POST" enctype="multipart/form-data">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">

      <div><label class="block text-xs font-medium text-gray-700 mb-1">First Name</label>
        <input type="text" name="fname" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Last Name</label>
        <input type="text" name="lname" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Username</label>
        <input type="text" name="uname" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Password</label>
        <input type="password" name="upass" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Secondary Password</label>
        <input type="password" name="upass2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Security PIN (4-digit)</label>
        <input type="password" name="pin" maxlength="4" inputmode="numeric" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. 1234">
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Phone</label>
        <input type="text" name="phone" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Account Type</label>
        <select name="type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option>Savings</option>
          <option>Checking</option>
          <option>Business</option>
          <option>Investment</option>
        </select>
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Occupation / Work</label>
        <input type="text" name="work" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Account ID</label>
        <input type="text" name="acc_no" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Address</label>
        <input type="text" name="addr" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Gender</label>
        <select name="sex" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="Male">Male</option>
          <option value="Female">Female</option>
        </select>
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Date of Birth</label>
        <input type="date" name="dob" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Marital Status</label>
        <select name="marry" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="Single">Single</option>
          <option value="Married">Married</option>
          <option value="Divorced">Divorced</option>
        </select>
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Total Balance</label>
        <input type="number" step="0.01" name="t_bal" value="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Available Balance</label>
        <input type="number" step="0.01" name="a_bal" value="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Currency</label>
        <input type="text" name="currency" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="USD">
      </div>

      <?php
      $createCodeColMap = [1 => 'cot', 2 => 'tax', 3 => 'imf', 4 => 'lppi', 5 => 'code5'];
      ?>
      <?php for ($i = 1; $i <= 5; $i++): $col = $createCodeColMap[$i];
        $inactive = $i > $txMaxCodes; ?>
        <div <?= $inactive ? 'class="opacity-50"' : '' ?>>
          <label class="block text-xs font-medium text-gray-700 mb-1"><?= htmlspecialchars($caCodeNames[$i]) ?> Code <?php if ($inactive): ?><span class="text-gray-400 font-normal">(inactive)</span><?php endif; ?></label>
          <input type="text" name="<?= htmlspecialchars($col) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" <?= $inactive ? 'disabled' : '' ?>>
        </div>
      <?php endfor; ?>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Registration Date</label>
        <input type="date" name="reg_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= date('Y-m-d') ?>">
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Account Status</label>
        <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <?php foreach ($statusOptions as $opt): ?>
            <option value="<?= htmlspecialchars($opt) ?>" <?= $opt === 'Active' ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Login Method</label>
        <select name="login_method" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <?php foreach ($loginMethodOptions as $k => $v): ?>
            <option value="<?= htmlspecialchars($k) ?>" <?= $k === 'pin' ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Transfer Auth Method</label>
        <select name="auth_method" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <?php foreach ($authMethodOptions as $k => $v): ?>
            <option value="<?= htmlspecialchars($k) ?>" <?= $k === 'codes' ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">Profile Photo</label>
        <input type="file" name="pp" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 !py-1.5" accept="image/*">
      </div>

      <div><label class="block text-xs font-medium text-gray-700 mb-1">ID Document</label>
        <input type="file" name="image" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 !py-1.5" accept="image/*">
      </div>

    </div>
    <div class="flex gap-3 pt-2">
      <button type="submit" name="create" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-user-plus"></i> Create Account</button>
      <button type="reset" class="inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-rotate-left"></i> Reset</button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>