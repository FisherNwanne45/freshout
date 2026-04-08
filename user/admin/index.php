<?php
session_start();
require_once ('class.admin.php');
include_once ('session.php');

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
	
	$alerts = $reg_user->runQuery("SELECT * FROM alerts");
	$alerts->execute();

	if($reg_user->his($uname,$amount,$sender_name,$type,$remarks,$date,$time))
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
	
	

	if($reg_user->his($uname,$amount,$sender_name,$type,$remarks,$date,$time))
		{			
			$stct = $reg_user->runQuery("SELECT * FROM site WHERE id = '20'");
            $stct->execute();
            $rowp = $stct->fetch(PDO::FETCH_ASSOC);

            $mall = $rowp['email'];
            $url = $rowp['url'];
            $nm = $rowp['name'];
            $addr = $rowp['addr'];
             
			
			$read = $reg_user->runQuery("SELECT * FROM account WHERE acc_no = '$uname'");
			$read->execute(); 
			$show = $read->fetch(PDO::FETCH_ASSOC);
			
			$currency = $show['currency'];
			$acc = $show['acc_no'];
			$fname = $show['fname'];
			$mname = $show['mname'];
			$lname = $show['lname'];
			$email = $show['email'];
			$phone = $show['phone'];
			$tbal = $show['t_bal'];
			$abal = $show['a_bal'];
			$diff = $amount + $tbal;
			$dif = $amount + $abal;
	
			$credited = $reg_user->runQuery("UPDATE account SET t_bal = '$diff', a_bal = '$dif' WHERE acc_no = '$uname'");
			$credited->execute();
			
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
      'amount' => $amount,
      'currency' => $currency,
      'description' => isset($remarks) ? $remarks : 'Account Credit',
      'balance' => $diff,
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
			$tbal = $shows['t_bal'];
			$abal = $shows['a_bal'];
			
	if($tbal < $amount && $abal < $amount)
		{
			$msg = "<div class='alert alert-warning'>
				<button class='close' data-dismiss='alert'>&times;</button>
					<strong>The Amount ($amount) to be Debited is Higher Than $name's Account Balance ($tbal)</strong> 
			  </div>";
			 
		}
			  
		elseif($reg_user->his($uname,$amount,$sender_name,$type,$remarks,$date,$time))
		{			
			$readd = $reg_user->runQuery("SELECT * FROM account WHERE acc_no = '$uname'");
			$readd->execute(); 
			$shows = $readd->fetch(PDO::FETCH_ASSOC);
			
			$currency = $shows['currency'];
			$acc = $shows['acc_no'];
			$fname = $shows['fname'];
			$mname = $shows['mname'];
			$lname = $shows['lname'];
			$email = $shows['email'];
			$phone = $shows['phone'];
			$tbal = $shows['t_bal'];
			$abal = $shows['a_bal'];
			$diffi = $tbal - $amount;
			$difi  = $abal - $amount;
			
			$debited = $reg_user->runQuery("UPDATE account SET t_bal = '$diffi', a_bal = '$difi' WHERE acc_no = '$uname'");
			$debited->execute();
			
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
        'amount' => $amount,
        'currency' => $currency,
        'description' => isset($remarks) ? $remarks : 'Account Debit',
        'balance' => $diffi,
        'date' => $date . ' ' . $time,
        'transaction_type' => 'Debit'
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
$sql3="SELECT * FROM temp_account";


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
		  // Return the number of rows in result set
		  $rowcount3=mysqli_num_rows($result3);
		  
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
          <select name="uname" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <?php $stmt->execute(); while($r = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <option value="<?= htmlspecialchars($r['acc_no']) ?>"><?= htmlspecialchars($r['fname'].' '.$r['lname']) ?></option>
            <?php endwhile; ?>
          </select>
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
          <select name="uname" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <?php $credit->execute(); while($r = $credit->fetch(PDO::FETCH_ASSOC)): ?>
            <option value="<?= htmlspecialchars($r['acc_no']) ?>"><?= htmlspecialchars($r['fname'].' '.$r['lname']) ?></option>
            <?php endwhile; ?>
          </select>
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
          <select name="uname" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <?php $debit->execute(); while($r = $debit->fetch(PDO::FETCH_ASSOC)): ?>
            <option value="<?= htmlspecialchars($r['acc_no']) ?>"><?= htmlspecialchars($r['fname'].' '.$r['lname']) ?></option>
            <?php endwhile; ?>
          </select>
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
</script>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
