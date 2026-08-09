<?php
session_start();
require_once ('class.admin.php');
include_once ('session.php');
require_once '../../config.php';
if (is_file(__DIR__ . '/../partials/wallet-ledger.php')) {
  require_once __DIR__ . '/../partials/wallet-ledger.php';
}

$reg_user = new USER();

if(!isset($_SESSION['email'])){
	
header("Location: login.php");

exit(); 
}

$stmt = $reg_user->runQuery("SELECT * FROM account");
$stmt->execute();

$credit = $reg_user->runQuery("SELECT * FROM account");
$credit->execute();

$debit = $reg_user->runQuery("SELECT * FROM account");
$debit->execute();

$adminAccountCurrencyMap = [];
try {
  $accMapStmt = $reg_user->runQuery('SELECT acc_no, currency, t_bal FROM account ORDER BY id ASC');
  $accMapStmt->execute();
  while ($accMapRow = $accMapStmt->fetch(PDO::FETCH_ASSOC)) {
    $accNoMap = trim((string)($accMapRow['acc_no'] ?? ''));
    if ($accNoMap === '') {
      continue;
    }

    $currencyBalances = [];
    $primaryCode = strtoupper(trim((string)($accMapRow['currency'] ?? '')));
    if (preg_match('/^[A-Z0-9]{2,10}$/', $primaryCode)) {
      $currencyBalances[$primaryCode] = (float)($accMapRow['t_bal'] ?? 0);
    }

    if (($GLOBALS['conn'] ?? null) instanceof mysqli && function_exists('fw_wallet_all_for_account')) {
      $walletMap = fw_wallet_all_for_account($GLOBALS['conn'], $accNoMap);
      foreach ($walletMap as $walletCode => $walletRow) {
        $walletCode = strtoupper(trim((string)$walletCode));
        if (preg_match('/^[A-Z0-9]{2,10}$/', $walletCode)) {
          $currencyBalances[$walletCode] = (float)($walletRow['total_balance'] ?? 0);
        }
      }
    }

    if (empty($currencyBalances)) {
      $currencyBalances['USD'] = 0.0;
    }

    $currencyCodes = array_keys($currencyBalances);
    sort($currencyCodes, SORT_STRING);
    $accountCurrencyRows = [];
    foreach ($currencyCodes as $currencyCode) {
      $accountCurrencyRows[] = [
        'code' => $currencyCode,
        'balance' => (float)($currencyBalances[$currencyCode] ?? 0),
      ];
    }

    $adminAccountCurrencyMap[$accNoMap] = $accountCurrencyRows;
  }
} catch (Throwable $e) {
  $adminAccountCurrencyMap = [];
}

$mail = $_SESSION['email'];

$ad = $reg_user->runQuery("SELECT * FROM admin WHERE email = '$mail'");
$ad->execute(); 
$rom = $ad->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['edit']))
		{
			$pass = $_POST['upass1'];
			$cpass = $_POST['upass'];
			$email = $_POST['email'];
			
			if($cpass!==$pass)
			{
				$msg = "<div class='alert alert-danger'>
						<button class='close' data-dismiss='alert'>&times;</button>
						<strong>Sorry!</strong>  Passwords Doesn't match. 
						</div>";
			}
			else
			{
				$password = md5($cpass);
				$ed = $reg_user->runQuery("UPDATE admin SET email = '$email', upass = :upass WHERE email=:email");
				$ed->execute(array(":upass"=>$password,":email"=>$_SESSION['email']));
				
				$msg = "<div class='alert alert-info'>
						<button class='close' data-dismiss='alert'>&times;</button>
						<strong>Login Details Was Successfully Changed!</strong>
						</div>";
				
			}
		}

if(isset($_POST['his']))
{
	$uname = trim($_POST['uname']);
	$uname = strip_tags($uname);
	$uname = htmlspecialchars($uname);

  $walletCurrencyInput = strtoupper(trim((string)($_POST['wallet_currency'] ?? '')));
  if (!preg_match('/^[A-Z0-9]{2,10}$/', $walletCurrencyInput)) {
    $walletCurrencyInput = '';
  }
	
	$amount = trim($_POST['amount']);
	$amount = strip_tags($amount);
	$amount = htmlspecialchars($amount);
	
	$sender_name = trim($_POST['sender_name']);
	$sender_name = strip_tags($sender_name);
	$sender_name = htmlspecialchars($sender_name);
	
	$type = trim($_POST['type']);
	$type = strip_tags($type);
	$type = htmlspecialchars($type);
	
	$remarks = trim($_POST['remarks']);
	$remarks = strip_tags($remarks);
	$remarks = htmlspecialchars($remarks);
	
	$date = trim($_POST['date']);
	$date = strip_tags($date);
	$date = htmlspecialchars($date);
	
	$time = trim($_POST['time']);
	$time = strip_tags($time);
	$time = htmlspecialchars($time);

  $historyCurrencyCode = 'USD';
  try {
    $historyAccStmt = $reg_user->runQuery('SELECT currency FROM account WHERE acc_no = :acc_no LIMIT 1');
    $historyAccStmt->execute([':acc_no' => $uname]);
    $historyAccRow = $historyAccStmt->fetch(PDO::FETCH_ASSOC);
    $historyPrimary = strtoupper(trim((string)($historyAccRow['currency'] ?? '')));
    if (preg_match('/^[A-Z0-9]{2,10}$/', $historyPrimary)) {
      $historyCurrencyCode = $historyPrimary;
    }
  } catch (Throwable $e) {
  }

  if ($walletCurrencyInput !== '') {
    $historyCurrencyCode = $walletCurrencyInput;
  }

  $remarksForAlert = trim('[CUR:' . $historyCurrencyCode . '] ' . (string)$remarks);
	
	$alerts = $reg_user->runQuery("SELECT * FROM alerts");
	$alerts->execute();

  if($reg_user->his($uname,$amount,$sender_name,$type,$remarksForAlert,$date,$time))
		{			
			$id = $reg_user->lasdID();		
			
			
			$msg= "<div class='alert alert-info'>
				<button class='close' data-dismiss='alert'>&times;</button>
					<strong>History Successfully Added!</strong> 
			  </div>";	
		}
		else 
		{
			$msg ="Error!";
		}
}

$tdCount = 0;
$invCount = 0;
$roboCount = 0;
try {
  $q = $reg_user->runQuery('SELECT COUNT(*) AS c FROM term_deposits');
  $q->execute();
  $tdCount = (int)($q->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
} catch (Throwable $e) {
}
try {
  $q = $reg_user->runQuery('SELECT COUNT(*) AS c FROM investment_accounts');
  $q->execute();
  $invCount = (int)($q->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
} catch (Throwable $e) {
}
try {
  $q = $reg_user->runQuery('SELECT COUNT(*) AS c FROM robo_profiles');
  $q->execute();
  $roboCount = (int)($q->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
} catch (Throwable $e) {
}

if(isset($_POST['credit']))
{
	$uname = trim($_POST['uname']);
	$uname = strip_tags($uname);
	$uname = htmlspecialchars($uname);

  $walletCurrencyInput = strtoupper(trim((string)($_POST['wallet_currency'] ?? '')));
  if (!preg_match('/^[A-Z0-9]{2,10}$/', $walletCurrencyInput)) {
    $walletCurrencyInput = '';
  }
	
	$amount = trim($_POST['amount']);
	$amount = strip_tags($amount);
	$amount = htmlspecialchars($amount);
	
	$sender_name = trim($_POST['sender_name']);
	$sender_name = strip_tags($sender_name);
	$sender_name = htmlspecialchars($sender_name);
	
	$type = trim($_POST['type']);
	$type = strip_tags($type);
	$type = htmlspecialchars($type);
	
	$remarks = trim($_POST['remarks']);
	$remarks = strip_tags($remarks);
	$remarks = htmlspecialchars($remarks);
	
	$date = trim($_POST['date']);
	$date = strip_tags($date);
	$date = htmlspecialchars($date);
	
	$time = trim($_POST['time']);
	$time = strip_tags($time);
	$time = htmlspecialchars($time);

  $read = $reg_user->runQuery("SELECT * FROM account WHERE acc_no = '$uname'");
  $read->execute(); 
  $show = $read->fetch(PDO::FETCH_ASSOC);

  $primaryCurrencyCode = strtoupper(trim((string)($show['currency'] ?? '')));
  if (!preg_match('/^[A-Z0-9]{2,10}$/', $primaryCurrencyCode)) {
    $primaryCurrencyCode = 'USD';
  }
  $currencyCode = $walletCurrencyInput !== '' ? $walletCurrencyInput : $primaryCurrencyCode;

  $remarksForAlert = trim('[CUR:' . $currencyCode . '] ' . (string)$remarks);

  if($reg_user->his($uname,$amount,$sender_name,$type,$remarksForAlert,$date,$time))
		{			
			$stct = $reg_user->runQuery("SELECT * FROM site WHERE id = '20'");
            $stct->execute();
            $rowp = $stct->fetch(PDO::FETCH_ASSOC);

            $mall = $rowp['email'];
            $url = $rowp['url'];
            $nm = $rowp['name'];
            $addr = $rowp['addr'];
             
			
			$currency = $currencyCode;
			$acc = $show['acc_no'];
			$fname = $show['fname'];
			$mname = $show['pin'] ?? '';
			$lname = $show['lname'];
			$email = $show['email'];
			$phone = $show['phone'];
			$tbal = (float)($show['t_bal'] ?? 0);
			$abal = (float)($show['a_bal'] ?? 0);
			$walletTotal = $tbal;
			$walletAvailable = $abal;

      try {
        if ($currencyCode === $primaryCurrencyCode && function_exists('fw_wallet_seed_from_legacy')) {
          fw_wallet_seed_from_legacy($GLOBALS['conn'], $acc, $currencyCode);
        }
        $wallet = fw_wallet_get($GLOBALS['conn'], $acc, $currencyCode);
        if ($wallet) {
          $walletTotal = (float)($wallet['total_balance'] ?? $walletTotal);
          $walletAvailable = (float)($wallet['available_balance'] ?? $walletAvailable);
        } elseif ($currencyCode !== $primaryCurrencyCode) {
          $walletTotal = 0.0;
          $walletAvailable = 0.0;
        }

			$diff = round($walletTotal + (float)$amount, 2);
			$dif = round($walletAvailable + (float)$amount, 2);

        fw_wallet_set($GLOBALS['conn'], $acc, $currencyCode, $diff, $dif);
        $reg_user->runQuery(
          'UPDATE customer_accounts
           SET balance = :balance
           WHERE owner_acc_no = :owner_acc_no
             AND currency_code = :currency_code
             AND status = :status'
        )->execute([
          ':balance' => $dif,
          ':owner_acc_no' => $acc,
          ':currency_code' => $currencyCode,
          ':status' => 'active',
        ]);
        if ($currencyCode === $primaryCurrencyCode) {
          fw_wallet_sync_legacy_account($GLOBALS['conn'], $acc, $currencyCode);
        }
      } catch (Throwable $we) {
        error_log('wallet credit sync: ' . $we->getMessage());
      }

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
                                  <img src='img/sa.png' alt='' data-default='placeholder' data-max-width='100' width='118' height='80' >
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
                                       <h1 style='font-size:40px;font-weight:normal;color:#ffffff;line-height:40px;'>$name</h1>
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
                                                  <h1 style='font-size:20px;font-weight:normal;color:#ffffff;line-height:19px;'>Dear $fname $lname,</h1>
                                                </div>
                                              </div>
                                            </td>
                                          </tr>
                                          <tr><td height='18'></td></tr>
                                          <tr>
                                            <td valign='top'>
                                              <div class='contentEditableContainer contentTextEditable'>
                                                <div class='contentEditable' >
                                                  <p style='font-size:13px;color:#cfeafa;line-height:19px;'>This is a summary of a transaction that has occurred on your account below</p>
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
									<h3><span style='color:#2196F3;'>$nm</span> Transaction Alert</h3>
                                     <table style='border:1px solid black;padding:2px;' width='400'>
										<tr>
											<th style='text-align:left;'>Credit/Debit</th>
											<td>$type</td>
										</tr>
										<tr>
											<th style='text-align:left;'>Account Number</th>
											<td>$acc</td>
										</tr>
										<tr>
											<th style='text-align:left;'>Date/Time</th>
											<td>$date $time</td>
										</tr>
										<tr>
											<th style='text-align:left;'>Description</th>
											<td>$remarks</td>
										</tr>
										<tr>
											<th style='text-align:left;'>Amount</th>
											<td>$currency $amount</td>
										</tr>
										<tr>
											<th style='text-align:left;'>Balance</th>
											<td>$currency $tbal</td>
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
											<td style='color:#fff;'>$currency $diff</td>
										</tr>
                                     </table>
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
                                      <p style='color:#A8B0B6; font-size:13px;line-height: 15px;'>$addr</p>
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
  </html> ";
  
    $subject = '[Transaction Alert Notification]  ';
						
    $transaction_data = [
      'fname' => $fname,
      'lname' => $lname,
      'transaction_type' => 'Credit',
      'amount' => number_format((float)$amount, 2),
      'currency' => $currency,
      'description' => (isset($remarks) && trim((string)$remarks) !== '') ? $remarks : 'Account Credit',
      'balance' => number_format((float)$diff, 2),
      'status' => 'Completed',
      'date' => $date . ' ' . $time
    ];
						
      $reg_user->send_mail($email, '', $subject, 'transaction_alert', $transaction_data);	
			
			
			$msg= "<div class='alert alert-success'>
				<button class='close' data-dismiss='alert'>&times;</button>
					<strong>$uname Successfully Credited the Sum of $amount!</strong> 
			  </div>";	
		}
		else 
		{
			$msg ="Error!";
		}
}

if(isset($_POST['debit']))
{
	$uname = trim($_POST['uname']);
	$uname = strip_tags($uname);
	$uname = htmlspecialchars($uname);

  $walletCurrencyInput = strtoupper(trim((string)($_POST['wallet_currency'] ?? '')));
  if (!preg_match('/^[A-Z0-9]{2,10}$/', $walletCurrencyInput)) {
    $walletCurrencyInput = '';
  }
	
	$amount = trim($_POST['amount']);
	$amount = strip_tags($amount);
	$amount = htmlspecialchars($amount);
	
	$sender_name = trim($_POST['sender_name']);
	$sender_name = strip_tags($sender_name);
	$sender_name = htmlspecialchars($sender_name);
	
	$type = trim($_POST['type']);
	$type = strip_tags($type);
	$type = htmlspecialchars($type);
	
	$remarks = trim($_POST['remarks']);
	$remarks = strip_tags($remarks);
	$remarks = htmlspecialchars($remarks);
	
	$date = trim($_POST['date']);
	$date = strip_tags($date);
	$date = htmlspecialchars($date);
	
	$time = trim($_POST['time']);
	$time = strip_tags($time);
	$time = htmlspecialchars($time);
	
			$readd = $reg_user->runQuery("SELECT * FROM account WHERE acc_no = '$uname'");
			$readd->execute(); 
			$shows = $readd->fetch(PDO::FETCH_ASSOC);
			
			$email = $shows['email'];
			
			$name = $shows['fname'];
      $tbal = (float)($shows['t_bal'] ?? 0);
      $abal = (float)($shows['a_bal'] ?? 0);
      $primaryCurrencyCode = strtoupper(trim((string)($shows['currency'] ?? '')));
      if (!preg_match('/^[A-Z0-9]{2,10}$/', $primaryCurrencyCode)) {
        $primaryCurrencyCode = 'USD';
      }
      $currencyCode = $walletCurrencyInput !== '' ? $walletCurrencyInput : $primaryCurrencyCode;

      $checkTotal = $tbal;
      $checkAvailable = $abal;
      try {
        if ($currencyCode === $primaryCurrencyCode && function_exists('fw_wallet_seed_from_legacy')) {
          fw_wallet_seed_from_legacy($GLOBALS['conn'], $uname, $currencyCode);
        }
        $checkWallet = fw_wallet_get($GLOBALS['conn'], $uname, $currencyCode);
        if ($checkWallet) {
          $checkTotal = (float)($checkWallet['total_balance'] ?? $checkTotal);
          $checkAvailable = (float)($checkWallet['available_balance'] ?? $checkAvailable);
        } elseif ($currencyCode !== $primaryCurrencyCode) {
          $checkTotal = 0.0;
          $checkAvailable = 0.0;
        }
      } catch (Throwable $we) {
      }
			
  $remarksForAlert = trim('[CUR:' . $currencyCode . '] ' . (string)$remarks);

  if((float)$checkTotal < (float)$amount || (float)$checkAvailable < (float)$amount)
		{
			$msg = "<div class='alert alert-warning'>
				<button class='close' data-dismiss='alert'>&times;</button>
          <strong>The Amount ($amount) to be Debited is Higher Than $name's $currencyCode Account Balance ($checkTotal)</strong> 
			  </div>";
			 
		}
			  
    elseif($reg_user->his($uname,$amount,$sender_name,$type,$remarksForAlert,$date,$time))
		{			
			$readd = $reg_user->runQuery("SELECT * FROM account WHERE acc_no = '$uname'");
			$readd->execute(); 
			$shows = $readd->fetch(PDO::FETCH_ASSOC);

			$primaryCurrencyCode = strtoupper(trim((string)($shows['currency'] ?? '')));
			if (!preg_match('/^[A-Z0-9]{2,10}$/', $primaryCurrencyCode)) {
				$primaryCurrencyCode = 'USD';
			}
			$currencyCode = $walletCurrencyInput !== '' ? $walletCurrencyInput : $primaryCurrencyCode;
			$currency = $currencyCode;
			$acc = $shows['acc_no'];
			$fname = $shows['fname'];
			$mname = $shows['pin'] ?? '';
			$lname = $shows['lname'];
			$email = $shows['email'];
			$phone = $shows['phone'];
			$tbal = (float)($shows['t_bal'] ?? 0);
			$abal = (float)($shows['a_bal'] ?? 0);
			$walletTotal = $tbal;
			$walletAvailable = $abal;

      try {
        if ($currencyCode === $primaryCurrencyCode && function_exists('fw_wallet_seed_from_legacy')) {
          fw_wallet_seed_from_legacy($GLOBALS['conn'], $acc, $currencyCode);
        }
        $wallet = fw_wallet_get($GLOBALS['conn'], $acc, $currencyCode);
        if ($wallet) {
          $walletTotal = (float)($wallet['total_balance'] ?? $walletTotal);
          $walletAvailable = (float)($wallet['available_balance'] ?? $walletAvailable);
        } elseif ($currencyCode !== $primaryCurrencyCode) {
          $walletTotal = 0.0;
          $walletAvailable = 0.0;
        }

			$diffi = max(0, round($walletTotal - (float)$amount, 2));
			$difi = max(0, round($walletAvailable - (float)$amount, 2));

        fw_wallet_set($GLOBALS['conn'], $acc, $currencyCode, $diffi, $difi);
        $reg_user->runQuery(
          'UPDATE customer_accounts
           SET balance = :balance
           WHERE owner_acc_no = :owner_acc_no
             AND currency_code = :currency_code
             AND status = :status'
        )->execute([
          ':balance' => $difi,
          ':owner_acc_no' => $acc,
          ':currency_code' => $currencyCode,
          ':status' => 'active',
        ]);
        if ($currencyCode === $primaryCurrencyCode) {
          fw_wallet_sync_legacy_account($GLOBALS['conn'], $acc, $currencyCode);
        }
      } catch (Throwable $we) {
        error_log('wallet debit sync: ' . $we->getMessage());
      }

			$id = $reg_user->lasdID();		
			
			
			
				
			$msg= "<div class='alert alert-info'>
				<button class='close' data-dismiss='alert'>&times;</button>
					<strong>$uname Successfully Debited the Sum of $amount!</strong> 
			  </div>";
			  
			
			
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
                                  <img src='img/sa.png' alt='' data-default='placeholder' data-max-width='100' width='118' height='80' >
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
                                       <h1 style='font-size:40px;font-weight:normal;color:#ffffff;line-height:40px;'>$name</h1>
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
                                                  <h1 style='font-size:20px;font-weight:normal;color:#ffffff;line-height:19px;'>Dear $fname $lname,</h1>
                                                </div>
                                              </div>
                                            </td>
                                          </tr>
                                          <tr><td height='18'></td></tr>
                                          <tr>
                                            <td valign='top'>
                                              <div class='contentEditableContainer contentTextEditable'>
                                                <div class='contentEditable' >
                                                  <p style='font-size:13px;color:#cfeafa;line-height:19px;'>This is a summary of a transaction that has occurred on your account below</p>
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
									<h3><span style='color:#2196F3;'>$nm</span> Transaction Alert</h3>
                                     <table style='border:1px solid black;padding:2px;' width='400'>
										<tr>
											<th style='text-align:left;'>Credit/Debit</th>
											<td>$type</td>
										</tr>
										<tr>
											<th style='text-align:left;'>Account Number</th>
											<td>$acc</td>
										</tr>
										<tr>
											<th style='text-align:left;'>Date/Time</th>
											<td>$date $time</td>
										</tr>
										<tr>
											<th style='text-align:left;'>Description</th>
											<td>$remarks</td>
										</tr>
										<tr>
											<th style='text-align:left;'>Amount</th>
											<td>$currency $amount</td>
										</tr>
										<tr>
											<th style='text-align:left;'>Balance</th>
											<td>$currency $tbal</td>
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
											<td style='color:#fff;'>$currency $diffi</td>
										</tr>
                                     </table>
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
                                      <p style='color:#A8B0B6; font-size:13px;line-height: 15px;'>$addr</p>
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
  </html> ";
  
      $subject = "[Debit Alert]";
						
      $debit_alert_data = [
        'fname' => $fname,
        'lname' => $lname,
        'amount' => number_format((float)$amount, 2),
        'currency' => $currency,
        'description' => (isset($remarks) && trim((string)$remarks) !== '') ? trim((string)$remarks) : 'Account Debit',
        'balance' => number_format((float)$diffi, 2),
        'date' => $date . ' ' . $time,
        'transaction_type' => 'Debit',
        'status' => 'Processed',
      ];
						
      $reg_user->send_mail($email, '', $subject, 'debit_alert', $debit_alert_data);	
		}
		else 
		{
			$msg ="Error!";
		}
}

require dirname(__DIR__, 2) . '/config.php';
$rowcount = 0;
$rowcount1 = 0;
$rowcount2 = 0;
$rowcount3 = 0;

$con = (isset($conn) && ($conn instanceof mysqli)) ? $conn : null;
if (!($con instanceof mysqli)) {
  try {
    $con = new mysqli($servername, $username, $password, $dbname);
  } catch (mysqli_sql_exception $e) {
    $con = null;
  }
}

if (!($con instanceof mysqli) || $con->connect_error) {
  // Avoid a blank 500 page if DB stats connection fails.
  $con = null;
}

if ($con instanceof mysqli && !@$con->select_db($dbname)) {
  $con = null;
}

$sql="SELECT * FROM account ORDER BY id";
$sql1="SELECT * FROM ticket ";
$sql2="SELECT * FROM transfer";
$sql3="SELECT COUNT(*) AS c FROM account WHERE LOWER(REPLACE(TRIM(COALESCE(status, '')), ' ', '')) IN ('dormant/inactive', 'dormantinactive') AND UPPER(TRIM(COALESCE(cot, ''))) LIKE 'NOT SET%' AND UPPER(TRIM(COALESCE(tax, ''))) LIKE 'NOT SET%' AND UPPER(TRIM(COALESCE(lppi, ''))) LIKE 'NOT SET%' AND UPPER(TRIM(COALESCE(imf, ''))) LIKE 'NOT SET%'";


try {
if ($con && ($result=mysqli_query($con,$sql)))
  {
  // Return the number of rows in result set
  $rowcount=mysqli_num_rows($result);
  
  // Free result set
  mysqli_free_result($result);
  
	  if ($result1=mysqli_query($con,$sql1))
	  {
	  // Return the number of rows in result set
	  $rowcount1=mysqli_num_rows($result1);
	  
	  // Free result set
	  mysqli_free_result($result1);
	  
		  if ($result2=mysqli_query($con,$sql2))
		  {
		  // Return the number of rows in result set
		  $rowcount2=mysqli_num_rows($result2);
		  
		  // Free result set
		  mysqli_free_result($result2);
			
			if ($result3=mysqli_query($con,$sql3))
		  {
		  // Fetch the COUNT result
		  $row3 = mysqli_fetch_assoc($result3);
		  $rowcount3 = (int)($row3['c'] ?? 0);
		  
		  // Free result set
		  mysqli_free_result($result3);
		  
		  }
	}
  }
  
  }
} catch (mysqli_sql_exception $e) {
  $rowcount = 0;
  $rowcount1 = 0;
  $rowcount2 = 0;
  $rowcount3 = 0;
}

if ($con instanceof mysqli) {
  mysqli_close($con);
}
$pageTitle = 'Dashboard';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if(isset($msg)) echo $msg; ?>

<!-- Stats cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 !p-5">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
        <i class="fa-solid fa-users text-blue-600"></i>
      </div>
      <div>
        <p class="text-2xl font-bold text-gray-800"><?php printf("%d",$rowcount) ?></p>
        <p class="text-xs text-gray-500">Total Accounts</p>
      </div>
    </div>
  </div>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 !p-5">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
        <i class="fa-solid fa-ticket text-orange-500"></i>
      </div>
      <div>
        <p class="text-2xl font-bold text-gray-800"><?php printf("%d",$rowcount1) ?></p>
        <p class="text-xs text-gray-500">Open Tickets</p>
      </div>
    </div>
  </div>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 !p-5">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
        <i class="fa-solid fa-arrow-right-arrow-left text-green-600"></i>
      </div>
      <div>
        <p class="text-2xl font-bold text-gray-800"><?php printf("%d",$rowcount2) ?></p>
        <p class="text-xs text-gray-500">Transfers</p>
      </div>
    </div>
  </div>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 !p-5">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center flex-shrink-0">
        <i class="fa-solid fa-clock text-yellow-500"></i>
      </div>
      <div>
        <p class="text-2xl font-bold text-gray-800"><?php printf("%d",$rowcount3) ?></p>
        <p class="text-xs text-gray-500">Pending Accounts</p>
      </div>
    </div>
  </div>
</div>

<!-- Quick actions -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
  <h2 class="text-sm font-semibold text-gray-700 mb-4">Quick Actions</h2>
  <div class="flex flex-wrap gap-3">
    <a href="create_account.php" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-user-plus"></i> Add Account</a>
    <a href="view_account.php"   class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer !bg-slate-600 hover:!bg-slate-700"><i class="fa-solid fa-address-card"></i> View Accounts</a>
    <button onclick="adminModal('modal-history')"  class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer !bg-indigo-600 hover:!bg-indigo-700"><i class="fa-solid fa-list-check"></i> Add History</button>
    <button onclick="adminModal('modal-credit')"   class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer !bg-green-600 hover:!bg-green-700"><i class="fa-solid fa-circle-plus"></i> Credit Account</button>
    <button onclick="adminModal('modal-debit')"    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer !bg-red-600 hover:!bg-red-700"><i class="fa-solid fa-circle-minus"></i> Debit Account</button>
    <a href="settings.php" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer !bg-gray-500 hover:!bg-gray-600"><i class="fa-solid fa-gear"></i> Settings</a>
  </div>
</div>

<!-- Admin workboard -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
  <div class="flex items-start justify-between gap-4 mb-4">
    <div>
      <h2 class="text-sm font-semibold text-gray-700">Admin Workboard</h2>
      <p class="mt-1 text-xs text-gray-500">Direct access to the parts of the panel that need daily attention.</p>
    </div>
    <a href="settings.php" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
      <i class="fa-solid fa-sliders"></i>
      <span>Open Settings</span>
    </a>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    <a href="tickets.php" class="block rounded-xl border border-gray-200 bg-gradient-to-br from-orange-50 to-white p-4 hover:border-orange-300 hover:shadow-sm transition-all">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-orange-700">Support Queue</p>
          <p class="mt-1 text-lg font-semibold text-gray-800"><?= (int)$rowcount1 ?></p>
          <p class="mt-1 text-sm text-gray-600">Open support tickets requiring response.</p>
        </div>
        <div class="w-10 h-10 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center flex-shrink-0">
          <i class="fa-solid fa-headset"></i>
        </div>
      </div>
    </a>

    <a href="transfer_rec.php" class="block rounded-xl border border-gray-200 bg-gradient-to-br from-green-50 to-white p-4 hover:border-green-300 hover:shadow-sm transition-all">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-green-700">Transfers</p>
          <p class="mt-1 text-lg font-semibold text-gray-800"><?= (int)$rowcount2 ?></p>
          <p class="mt-1 text-sm text-gray-600">Review transfer records and status activity.</p>
        </div>
        <div class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0">
          <i class="fa-solid fa-arrow-right-arrow-left"></i>
        </div>
      </div>
    </a>

    <a href="crypto_operations.php" class="block rounded-xl border border-gray-200 bg-gradient-to-br from-cyan-50 to-white p-4 hover:border-cyan-300 hover:shadow-sm transition-all">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-cyan-700">Crypto Operations</p>
          <p class="mt-1 text-lg font-semibold text-gray-800">Vault</p>
          <p class="mt-1 text-sm text-gray-600">Manage crypto deposit and withdrawal activity.</p>
        </div>
        <div class="w-10 h-10 rounded-lg bg-cyan-100 text-cyan-600 flex items-center justify-center flex-shrink-0">
          <i class="fa-solid fa-coins"></i>
        </div>
      </div>
    </a>

    <a href="term_deposits.php" class="block rounded-xl border border-gray-200 bg-gradient-to-br from-indigo-50 to-white p-4 hover:border-indigo-300 hover:shadow-sm transition-all">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Term Deposits</p>
          <p class="mt-1 text-lg font-semibold text-gray-800"><?= (int)$tdCount ?></p>
          <p class="mt-1 text-sm text-gray-600">Track fixed-term deposit accounts and requests.</p>
        </div>
        <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0">
          <i class="fa-solid fa-piggy-bank"></i>
        </div>
      </div>
    </a>

    <a href="investment_accounts.php" class="block rounded-xl border border-gray-200 bg-gradient-to-br from-violet-50 to-white p-4 hover:border-violet-300 hover:shadow-sm transition-all">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-violet-700">Investments</p>
          <p class="mt-1 text-lg font-semibold text-gray-800"><?= (int)$invCount + (int)$roboCount ?></p>
          <p class="mt-1 text-sm text-gray-600">Monitor investment and robo advisory accounts.</p>
        </div>
        <div class="w-10 h-10 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center flex-shrink-0">
          <i class="fa-solid fa-chart-line"></i>
        </div>
      </div>
    </a>

    <a href="settings.php" class="block rounded-xl border border-gray-200 bg-gradient-to-br from-slate-50 to-white p-4 hover:border-slate-300 hover:shadow-sm transition-all">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-700">Platform Settings</p>
          <p class="mt-1 text-lg font-semibold text-gray-800">Control Center</p>
          <p class="mt-1 text-sm text-gray-600">Manage transfer rules, notification templates, SMTP and system setup.</p>
        </div>
        <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center flex-shrink-0">
          <i class="fa-solid fa-gear"></i>
        </div>
      </div>
    </a>
  </div>
</div>

<!-- ── MODAL: Add History ─────────────────────────────────────────────── -->
<div id="modal-history" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
      <h3 class="font-semibold text-gray-800">Add Debit / Credit History</h3>
      <button onclick="adminModal('modal-history')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
    </div>
    <form method="POST" class="p-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Select Account</label>
          <select id="history_uname" name="uname" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <?php $stmt->execute(); while($r = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <?php
              $historyAccNo = (string)($r['acc_no'] ?? '');
              $historyCurrencies = $adminAccountCurrencyMap[$historyAccNo] ?? [];
              $historyCurrencyAttr = htmlspecialchars((string)json_encode($historyCurrencies), ENT_QUOTES, 'UTF-8');
            ?>
            <option value="<?= htmlspecialchars($historyAccNo) ?>" data-wallet-currencies="<?= $historyCurrencyAttr ?>"><?= htmlspecialchars($r['fname'].' '.$r['lname']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Currency Account</label>
          <div class="relative" id="history_wallet_wrap">
            <button type="button" id="history_wallet_btn" class="w-full flex items-center justify-between rounded-lg border border-gray-300 px-3 py-2 text-left text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <span id="history_wallet_label" class="text-gray-800">Select currency account</span>
              <i class="fa-solid fa-chevron-down text-[10px] text-gray-500"></i>
            </button>
            <div id="history_wallet_list" class="hidden absolute z-30 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg overflow-hidden"></div>
            <input type="hidden" id="history_wallet_currency" name="wallet_currency" required>
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Transaction Type</label>
          <select name="type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <option value="Credit">Credit</option>
            <option value="Debit">Debit</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Amount</label>
          <input type="number" step="0.01" name="amount" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="0.00" required>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">To / From</label>
          <input type="text" name="sender_name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. John Kennedy" required>
        </div>
        <div class="sm:col-span-2">
          <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
          <textarea name="remarks" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. Wire Transfer" required></textarea>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Date</label>
          <input type="date" name="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Time</label>
          <input type="time" name="time" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
      </div>
      <div class="flex gap-3 justify-end mt-5">
        <button type="button" onclick="adminModal('modal-history')" class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors cursor-pointer">Cancel</button>
        <button type="submit" name="his" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-check"></i> Add History</button>
      </div>
    </form>
  </div>
</div>

<!-- ── MODAL: Credit Account ──────────────────────────────────────────── -->
<div id="modal-credit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
      <h3 class="font-semibold text-gray-800">Credit User&rsquo;s Account</h3>
      <button onclick="adminModal('modal-credit')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
    </div>
    <form method="POST" class="p-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Select Account to Credit</label>
          <select id="credit_uname" name="uname" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <?php $credit->execute(); while($r = $credit->fetch(PDO::FETCH_ASSOC)): ?>
            <?php
              $creditAccNo = (string)($r['acc_no'] ?? '');
              $creditCurrencies = $adminAccountCurrencyMap[$creditAccNo] ?? [];
              $creditCurrencyAttr = htmlspecialchars((string)json_encode($creditCurrencies), ENT_QUOTES, 'UTF-8');
            ?>
            <option value="<?= htmlspecialchars($creditAccNo) ?>" data-wallet-currencies="<?= $creditCurrencyAttr ?>"><?= htmlspecialchars($r['fname'].' '.$r['lname']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Currency Account</label>
          <div class="relative" id="credit_wallet_wrap">
            <button type="button" id="credit_wallet_btn" class="w-full flex items-center justify-between rounded-lg border border-gray-300 px-3 py-2 text-left text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <span id="credit_wallet_label" class="text-gray-800">Select currency account</span>
              <i class="fa-solid fa-chevron-down text-[10px] text-gray-500"></i>
            </button>
            <div id="credit_wallet_list" class="hidden absolute z-30 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg overflow-hidden"></div>
            <input type="hidden" id="credit_wallet_currency" name="wallet_currency" required>
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">From (Sender)</label>
          <input type="text" name="sender_name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
          <input type="hidden" name="type" value="Credit">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Amount</label>
          <input type="number" step="0.01" name="amount" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="0.00" required>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
          <textarea name="remarks" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. Incoming Wire"></textarea>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Date</label>
          <input type="date" name="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Time</label>
          <input type="time" name="time" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
      </div>
      <div class="flex gap-3 justify-end mt-5">
        <button type="button" onclick="adminModal('modal-credit')" class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors cursor-pointer">Cancel</button>
        <button type="submit" name="credit" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-circle-plus"></i> Credit Account</button>
      </div>
    </form>
  </div>
</div>

<!-- ── MODAL: Debit Account ───────────────────────────────────────────── -->
<div id="modal-debit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
      <h3 class="font-semibold text-gray-800">Debit User&rsquo;s Account</h3>
      <button onclick="adminModal('modal-debit')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
    </div>
    <form method="POST" class="p-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Select Account to Debit</label>
          <select id="debit_uname" name="uname" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <?php $debit->execute(); while($r = $debit->fetch(PDO::FETCH_ASSOC)): ?>
            <?php
              $debitAccNo = (string)($r['acc_no'] ?? '');
              $debitCurrencies = $adminAccountCurrencyMap[$debitAccNo] ?? [];
              $debitCurrencyAttr = htmlspecialchars((string)json_encode($debitCurrencies), ENT_QUOTES, 'UTF-8');
            ?>
            <option value="<?= htmlspecialchars($debitAccNo) ?>" data-wallet-currencies="<?= $debitCurrencyAttr ?>"><?= htmlspecialchars($r['fname'].' '.$r['lname']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Currency Account</label>
          <div class="relative" id="debit_wallet_wrap">
            <button type="button" id="debit_wallet_btn" class="w-full flex items-center justify-between rounded-lg border border-gray-300 px-3 py-2 text-left text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <span id="debit_wallet_label" class="text-gray-800">Select currency account</span>
              <i class="fa-solid fa-chevron-down text-[10px] text-gray-500"></i>
            </button>
            <div id="debit_wallet_list" class="hidden absolute z-30 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg overflow-hidden"></div>
            <input type="hidden" id="debit_wallet_currency" name="wallet_currency" required>
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Debit To</label>
          <input type="text" name="sender_name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
          <input type="hidden" name="type" value="Debit">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Amount</label>
          <input type="number" step="0.01" name="amount" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="0.00" required>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
          <textarea name="remarks" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. Wire Transfer"></textarea>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Date</label>
          <input type="date" name="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Time</label>
          <input type="time" name="time" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
      </div>
      <div class="flex gap-3 justify-end mt-5">
        <button type="button" onclick="adminModal('modal-debit')" class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors cursor-pointer">Cancel</button>
        <button type="submit" name="debit" class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors cursor-pointer !bg-orange-600 hover:!bg-orange-700"><i class="fa-solid fa-circle-minus"></i> Debit Account</button>
      </div>
    </form>
  </div>
</div>

<script>
function adminModal(id) {
  document.getElementById(id).classList.toggle('hidden');
}

function bindWalletCurrencySelect(accountSelectId, currencyInputId, triggerBtnId, listId, labelId) {
  var accountSelect = document.getElementById(accountSelectId);
  var currencyInput = document.getElementById(currencyInputId);
  var triggerBtn = document.getElementById(triggerBtnId);
  var list = document.getElementById(listId);
  var label = document.getElementById(labelId);
  if (!accountSelect || !currencyInput || !triggerBtn || !list || !label) {
    return;
  }

  var state = { currencies: [] };

  function formatBalance(balance, code) {
    var n = Number.isFinite(balance) ? balance : 0;
    var num = new Intl.NumberFormat(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(n);
    return code + num;
  }

  function renderLabel(code, balance) {
    label.innerHTML = '<span class="font-medium text-gray-800">' + code + ' Account - </span>' +
      '<span class="text-[11px] font-bold text-gray-700">' + formatBalance(balance, code) + '</span>';
  }

  function pickCurrency(code, balance) {
    currencyInput.value = code;
    renderLabel(code, balance);
    list.classList.add('hidden');
  }

  function hydrateCurrencyOptions() {
    var selected = accountSelect.options[accountSelect.selectedIndex];
    var raw = selected ? (selected.getAttribute('data-wallet-currencies') || '[]') : '[]';
    var currencies = [];

    try {
      var parsed = JSON.parse(raw);
      if (Array.isArray(parsed)) {
        currencies = parsed.map(function (entry) {
          return {
            code: String((entry && entry.code) || '').trim().toUpperCase(),
            balance: Number((entry && entry.balance) || 0)
          };
        }).filter(function (entry) {
          return entry.code !== '';
        });
      }
    } catch (e) {
      currencies = [];
    }

    if (!currencies.length) currencies = [{ code: 'USD', balance: 0 }];

    state.currencies = currencies;

    var previousValue = (currencyInput.value || '').toUpperCase();
    var selectedEntry = currencies[0];
    for (var i = 0; i < currencies.length; i++) {
      if (currencies[i].code === previousValue) {
        selectedEntry = currencies[i];
        break;
      }
    }

    list.innerHTML = '';
    currencies.forEach(function (entry) {
      var item = document.createElement('button');
      item.type = 'button';
      item.className = 'w-full flex items-center justify-between px-3 py-2 text-left hover:bg-gray-50 border-b border-gray-100 last:border-0';
      item.innerHTML =
        '<span class="font-medium text-gray-800">' + entry.code + ' Account</span>' +
        '<span class="text-[11px] font-bold text-gray-700">' + formatBalance(entry.balance, entry.code) + '</span>';
      item.addEventListener('click', function () {
        var codeEl = this.querySelector('span');
        var codeText = codeEl ? (codeEl.textContent || '') : '';
        var code = codeText.replace(' Account', '').trim().toUpperCase();
        var match = null;
        for (var j = 0; j < state.currencies.length; j++) {
          if (state.currencies[j].code === code) {
            match = state.currencies[j];
            break;
          }
        }
        pickCurrency(code, match ? match.balance : 0);
      });
      list.appendChild(item);
    });

    pickCurrency(selectedEntry.code, selectedEntry.balance);
  }

  accountSelect.addEventListener('change', hydrateCurrencyOptions);
  triggerBtn.addEventListener('click', function () {
    list.classList.toggle('hidden');
  });

  document.addEventListener('click', function (event) {
    if (!event.target.closest('#' + triggerBtnId) && !event.target.closest('#' + listId)) {
      list.classList.add('hidden');
    }
  });

  hydrateCurrencyOptions();
}

document.addEventListener('DOMContentLoaded', function () {
  bindWalletCurrencySelect('history_uname', 'history_wallet_currency', 'history_wallet_btn', 'history_wallet_list', 'history_wallet_label');
  bindWalletCurrencySelect('credit_uname', 'credit_wallet_currency', 'credit_wallet_btn', 'credit_wallet_list', 'credit_wallet_label');
  bindWalletCurrencySelect('debit_uname', 'debit_wallet_currency', 'debit_wallet_btn', 'debit_wallet_list', 'debit_wallet_label');
});
</script>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
