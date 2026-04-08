<?php

session_start();

include_once ('session.php');

require_once 'class.user.php';

if(!isset($_SESSION['acc_no'])){

	

header("Location: login.php");

exit(); 

}

elseif(!isset($_SESSION['mname'])){
    
header("Location: passcode.php");
exit(); 
}



$reg_user = new USER();

$site = $row['site'];

$stct = $reg_user->runQuery("SELECT * FROM site WHERE id = '20'");
$stct->execute();
$rowp = $stct->fetch(PDO::FETCH_ASSOC);

$mail = $rowp['email'];
$url = $rowp['url'];
$name = $rowp['name'];
$addr = $rowp['addr'];
$sc = $rowp['sc'];

$stmt = $reg_user->runQuery("SELECT * FROM account WHERE acc_no=:acc_no");

$stmt->execute(array(":acc_no"=>$_SESSION['acc_no']));

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$email = $row['email'];
$fname = $row['fname'];
$lname = $row['lname'];
$customerDisplayName = trim((string)$fname . ' ' . (string)$lname);
$flashMessage = '';
$flashType = 'success';

try {
  $reg_user->runQuery("CREATE TABLE IF NOT EXISTS ticket_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    sender_role VARCHAR(20) NOT NULL DEFAULT 'customer',
    sender_name VARCHAR(150) DEFAULT NULL,
    msg TEXT NOT NULL,
    is_read_user TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ticket (ticket_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();
} catch (Throwable $e) {
}

try {
  $reg_user->runQuery('ALTER TABLE ticket_replies ADD COLUMN is_read_user TINYINT(1) NOT NULL DEFAULT 0')->execute();
} catch (Throwable $e) {
}

if (isset($_POST['ticket_reply'])) {
  $ticketId = (int)($_POST['ticket_id'] ?? 0);
  $replyBody = trim((string)($_POST['reply_body'] ?? ''));

  if ($ticketId <= 0 || $replyBody === '') {
    $flashMessage = 'Please enter a follow-up message.';
    $flashType = 'error';
  } else {
    $own = $reg_user->runQuery("SELECT id FROM ticket WHERE id = :id AND (mail = :mail OR (COALESCE(mail,'') = '' AND sender_name = :sender_name)) LIMIT 1");
    $own->execute([
      ':id' => $ticketId,
      ':mail' => $email,
      ':sender_name' => $customerDisplayName,
    ]);
    $ownedTicket = $own->fetch(PDO::FETCH_ASSOC);

    if (!$ownedTicket) {
      $flashMessage = 'Ticket not found for this account.';
      $flashType = 'error';
    } else {
      $replyIns = $reg_user->runQuery('INSERT INTO ticket_replies (ticket_id, sender_role, sender_name, msg) VALUES (:ticket_id, :sender_role, :sender_name, :msg)');
      $replyIns->execute([
        ':ticket_id' => $ticketId,
        ':sender_role' => 'customer',
        ':sender_name' => $customerDisplayName,
        ':msg' => $replyBody,
      ]);

      $openAgain = $reg_user->runQuery("UPDATE ticket SET status='Pending' WHERE id = :id LIMIT 1");
      $openAgain->execute([':id' => $ticketId]);

      $flashMessage = 'Your follow-up has been added to the ticket thread.';
      $flashType = 'success';
    }
  }
}

if(isset($_POST['ticket']))

{

	$tc = rand(00000,99999);

	

	$sender_name = trim($_POST['sender_name']);

	$sender_name = strip_tags($sender_name);

	$sender_name = htmlspecialchars($sender_name);

	

	$sub = trim($_POST['subject']);

	$sub = strip_tags($sub);

	$sub = htmlspecialchars($sub);

	

  $ticketMessage = trim($_POST['msg']);

  $ticketMessage = strip_tags($ticketMessage);

  $ticketMessage = htmlspecialchars($ticketMessage);

	
	
	

	$tick = $reg_user->runQuery("SELECT * FROM ticket");

	

	$tick->execute();



	$show = $tick->fetch(PDO::FETCH_ASSOC);

	$date = $show['date'];

    if($reg_user->ticket($tc,$sender_name,$sub,$ticketMessage,$email))

		{			

			$id = $reg_user->lasdID();	

			
			$message = "
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
                                                  <p style='font-size:13px;color:#cfeafa;line-height:19px;'>Your Ticket application was successfully opened! We will respond to your request within 24 hours. Below is the transaction summary.</p>
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
									<h3><span style='color:##2196F3;'>$name</span> Ticket</h3>
                                     <table style='border:1px solid black;padding:2px;' width='400'>
										<tr>
											<th style='text-align:left;'>Ticket   Number</th>
											<td>$tc</td>
										</tr>
										<tr>
											<th style='text-align:left;'>Subject</th>
											<td>$sub</td>
										</tr>
										<tr>
											<th style='text-align:left;'>Date Opened</th>
											<td>$date</td>
										</tr>
										<tr>
											<th style='text-align:left;'>Message</th>
											<td>$msg</td>
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
                                      <p style=' font-weight:bold;font-size:13px;line-height: 30px;'>$name</p>
                                    </div>
                                  </div>
                                  <div class='contentEditableContainer contentTextEditable'>
                                    <div class='contentEditable' >
                                      <p style='color:#A8B0B6; font-size:13px;line-height: 15px;'>$addr</p>
                                    </div>
                                  </div>
                                  <div class='contentEditableContainer contentTextEditable'>
                                    <div class='contentEditable' >
                                      <a target='_blank' href='' style='line-height: 20px;color:#A8B0B6; font-size:13px;'>$mail</a>
                                    </div>
                                    </div>
									<div class='contentEditableContainer contentTextEditable'>
									<div class='contentEditable' >
                                      <a target='_blank' href='$url ' style='line-height: 20px;color:#A8B0B6; font-size:13px;'>$url </a>
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
  </html>";
			
			$subject = "Your Ticket  [$tc] Has Been Opened";
						
			// Prepare ticket creation template data
			$ticket_data = [
				'fname' => $fname,
				'lname' => $lname,
				'ticket_id' => $tc,
				'subject' => $sub,
        'message' => $ticketMessage,
				'creation_date' => date('Y-m-d H:i:s'),
				'response_time' => '24 hours'
			];
			
			// Send using professional ticket template
			$reg_user->send_mail($email, '', $subject, 'ticket_created', $ticket_data);	

			
      $flashMessage = 'Ticket successfully opened. Your request is now in pending review.';
      $flashType = 'success';

		}

		else

		{

      $flashMessage = 'Ticket could not be opened. Please try again.';
      $flashType = 'error';

		}		

}

include_once ('counter.php');

$ticketListStmt = $reg_user->runQuery("SELECT id, tc, subject, msg, status, date, sender_name, mail
  FROM ticket
  WHERE mail = :mail OR (COALESCE(mail,'') = '' AND sender_name = :sender_name)
  ORDER BY id DESC
  LIMIT 120");
$ticketListStmt->execute([
  ':mail' => $email,
  ':sender_name' => $customerDisplayName,
]);
$customerTickets = $ticketListStmt->fetchAll(PDO::FETCH_ASSOC);

$selectedTicketId = (int)($_GET['ticket_id'] ?? 0);
$selectedTicket = null;
if ($selectedTicketId > 0) {
  foreach ($customerTickets as $ticketRow) {
    if ((int)($ticketRow['id'] ?? 0) === $selectedTicketId) {
      $selectedTicket = $ticketRow;
      break;
    }
  }
}
if (!$selectedTicket && !empty($customerTickets)) {
  $selectedTicket = $customerTickets[0];
  $selectedTicketId = (int)($selectedTicket['id'] ?? 0);
}

$selectedThreadReplies = [];
if ($selectedTicketId > 0) {
  try {
    $markRead = $reg_user->runQuery("UPDATE ticket_replies SET is_read_user = 1 WHERE ticket_id = :ticket_id AND LOWER(COALESCE(sender_role, 'admin')) = 'admin' AND COALESCE(is_read_user, 0) = 0");
    $markRead->execute([':ticket_id' => $selectedTicketId]);
  } catch (Throwable $e) {
  }

  $threadStmt = $reg_user->runQuery('SELECT id, sender_role, sender_name, msg, created_at FROM ticket_replies WHERE ticket_id = :ticket_id ORDER BY id ASC');
  $threadStmt->execute([':ticket_id' => $selectedTicketId]);
  $selectedThreadReplies = $threadStmt->fetchAll(PDO::FETCH_ASSOC);
}

require_once __DIR__ . '/partials/shell-data.php';
$shellPageTitle = 'Support Tickets';
require_once __DIR__ . '/partials/shell-open.php';
?>

<?php if ($flashMessage !== ''): ?>
<div class="mb-5 rounded-xl p-4 <?= $flashType === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' ?> text-sm">
  <?= htmlspecialchars($flashMessage) ?>
</div>
<?php endif; ?>

<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
  <div>
    <h1 class="text-2xl font-bold text-gray-900">My Ticket Conversations</h1>
    <p class="text-sm text-gray-500 mt-1">Track your support requests, read responses, and send follow-ups in one place.</p>
  </div>
  <button type="button" id="openTicketModalBtn"
          class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors">
    <i class="fa-solid fa-plus"></i>
    New Ticket
  </button>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-semibold text-gray-900">My Ticket Conversations</h2>
    <span class="text-xs font-medium text-gray-500">Total: <?= count($customerTickets) ?></span>
  </div>

  <?php if (empty($customerTickets)): ?>
  <div class="rounded-lg border border-dashed border-gray-300 p-6 text-sm text-gray-500 text-center">
    No ticket history yet. Use the New Ticket button to contact support.
  </div>
  <?php else: ?>
  <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
    <div class="xl:col-span-5 border border-gray-200 rounded-xl overflow-hidden">
      <div class="max-h-[420px] overflow-y-auto divide-y divide-gray-100">
        <?php foreach ($customerTickets as $t): ?>
        <?php
          $tid = (int)($t['id'] ?? 0);
          $active = $selectedTicketId === $tid;
          $isOpen = strtolower((string)($t['status'] ?? 'pending')) === 'pending';
          $preview = trim((string)($t['msg'] ?? ''));
          if (strlen($preview) > 90) {
            $preview = substr($preview, 0, 90) . '...';
          }
        ?>
        <a href="ticket.php?ticket_id=<?= $tid ?>"
           class="block px-4 py-3 <?= $active ? 'bg-blue-50 border-l-4 border-blue-600' : 'hover:bg-gray-50 border-l-4 border-transparent' ?>">
          <div class="flex items-start justify-between gap-2">
            <p class="text-sm font-semibold text-gray-800">#<?= htmlspecialchars((string)($t['tc'] ?: $tid)) ?> - <?= htmlspecialchars((string)($t['subject'] ?? 'No subject')) ?></p>
            <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium <?= $isOpen ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' ?>">
              <?= $isOpen ? 'Pending' : 'Replied' ?>
            </span>
          </div>
          <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($preview) ?></p>
          <p class="text-[11px] text-gray-400 mt-1"><?= htmlspecialchars((string)($t['date'] ?? '')) ?></p>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="xl:col-span-7 border border-gray-200 rounded-xl p-4 bg-gray-50/40">
      <?php if (!$selectedTicket): ?>
      <p class="text-sm text-gray-500">Select a ticket to view the full thread.</p>
      <?php else: ?>
      <?php $selectedOpen = strtolower((string)($selectedTicket['status'] ?? 'pending')) === 'pending'; ?>
      <div class="mb-3">
        <h3 class="text-base font-semibold text-gray-800">Ticket #<?= htmlspecialchars((string)($selectedTicket['tc'] ?: $selectedTicket['id'])) ?></h3>
        <p class="text-xs text-gray-500">Opened on <?= htmlspecialchars((string)($selectedTicket['date'] ?? '')) ?> • Status: <?= $selectedOpen ? 'Pending' : 'Replied' ?></p>
      </div>

      <div class="space-y-3 max-h-[320px] overflow-y-auto pr-1 mb-4">
        <div class="rounded-lg border border-blue-100 bg-blue-50 p-3">
          <p class="text-[11px] font-semibold text-blue-700 mb-1">You • Initial Ticket</p>
          <p class="text-sm font-medium text-gray-800 mb-1"><?= htmlspecialchars((string)($selectedTicket['subject'] ?? 'No subject')) ?></p>
          <div class="text-sm text-gray-700 whitespace-pre-wrap leading-6"><?= htmlspecialchars((string)($selectedTicket['msg'] ?? '')) ?></div>
        </div>

        <?php foreach ($selectedThreadReplies as $rep): ?>
        <?php $isSupport = strtolower((string)($rep['sender_role'] ?? 'customer')) === 'admin'; ?>
        <div class="rounded-lg border p-3 <?= $isSupport ? 'border-emerald-100 bg-emerald-50' : 'border-gray-200 bg-white' ?>">
          <p class="text-[11px] font-semibold mb-1 <?= $isSupport ? 'text-emerald-700' : 'text-gray-600' ?>">
            <?= $isSupport ? 'Support Team' : 'You' ?><?= !empty($rep['sender_name']) ? ' • ' . htmlspecialchars((string)$rep['sender_name']) : '' ?>
            <?php if (!empty($rep['created_at'])): ?>
              <span class="font-normal text-gray-500"> • <?= htmlspecialchars((string)$rep['created_at']) ?></span>
            <?php endif; ?>
          </p>
          <div class="text-sm text-gray-700 whitespace-pre-wrap leading-6"><?= htmlspecialchars((string)($rep['msg'] ?? '')) ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <form method="post" class="space-y-2">
        <input type="hidden" name="ticket_reply" value="1">
        <input type="hidden" name="ticket_id" value="<?= (int)$selectedTicket['id'] ?>">
        <label class="block text-xs font-medium text-gray-700">Add Follow-up</label>
        <textarea name="reply_body" rows="4" required
                  placeholder="Add more details or respond to support updates..."
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        <button type="submit" class="inline-flex items-center gap-2 bg-slate-700 hover:bg-slate-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
          Send Follow-up
        </button>
      </form>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<div id="ticketModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
  <div id="ticketModalBackdrop" class="absolute inset-0 bg-slate-900/55"></div>
  <div class="relative min-h-full flex items-center justify-center p-4">
    <div class="w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <div>
          <h3 class="text-base font-semibold text-gray-900">Open New Support Ticket</h3>
          <p class="text-xs text-gray-500 mt-0.5">Provide clear details so support can resolve your request quickly.</p>
        </div>
        <button type="button" id="closeTicketModalBtn" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
      </div>

      <form method="POST" action="" class="p-6 space-y-4">
        <input type="hidden" name="sender_name" value="<?= htmlspecialchars(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? '')) ?>">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Subject</label>
          <input type="text" name="subject" required
                 class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                 placeholder="Example: Card transfer pending for over 24 hours">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Message</label>
          <textarea name="msg" rows="6" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                    placeholder="Include date, amount, reference, and any relevant details."></textarea>
        </div>

        <div class="pt-1 flex items-center justify-end gap-2">
          <button type="button" id="cancelTicketModalBtn"
                  class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            Cancel
          </button>
          <button type="submit" name="ticket"
                  class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
            Submit Ticket
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  (function () {
    const modal = document.getElementById('ticketModal');
    const openBtn = document.getElementById('openTicketModalBtn');
    const closeBtn = document.getElementById('closeTicketModalBtn');
    const cancelBtn = document.getElementById('cancelTicketModalBtn');
    const backdrop = document.getElementById('ticketModalBackdrop');

    if (!modal || !openBtn) return;

    const openModal = () => {
      modal.classList.remove('hidden');
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
      modal.classList.add('hidden');
      modal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('overflow-hidden');
    };

    openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (backdrop) backdrop.addEventListener('click', closeModal);

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
        closeModal();
      }
    });
  })();
</script>

<?php require_once __DIR__ . '/partials/shell-close.php'; exit(); ?>


<!DOCTYPE html>
<!-- saved from url=(0053)ticket.php#! -->
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Ticket Applications </title>

<meta content="ie=edge" http-equiv="x-ua-compatible">
<meta content="width=device-width, initial-scale=1" name="viewport">
<link href="../asset.php?type=favicon" rel="shortcut icon"> 
<link href="dash/ticket_files/css" rel="stylesheet" type="text/css">
<link href="dash/ticket_files/select2.min.css" rel="stylesheet">
<link href="dash/ticket_files/daterangepicker.css" rel="stylesheet">
<link href="dash/ticket_files/dropzone.css" rel="stylesheet">
<link href="dash/ticket_files/dataTables.bootstrap.min.css" rel="stylesheet">
<link href="dash/ticket_files/fullcalendar.min.css" rel="stylesheet">
<link href="dash/ticket_files/perfect-scrollbar.min.css" rel="stylesheet">
<link href="dash/ticket_files/slick.css" rel="stylesheet">
<link href="dash/ticket_files/main.css" rel="stylesheet">
<link href="css/new-ui-bridge.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="dash/ticket_files/clock_style.css">
<style>.alert-success {
    color: #090909;
    background-color: #e2e9e1;
    border-color: #36b927;
}
</style>
<script async="" src="dash/ticket_files/analytics.js.download"></script><script type="text/javascript">
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
<a class="ssg-item" href="ticket.php#">
<div class="item-icon">
<i class="os-icon os-icon-file-text"></i>
</div>
<div class="item-name">
Work<span>Not</span>e.txt
</div>
</a><a class="ssg-item" href="ticket.php#">
<div class="item-icon">
<i class="os-icon os-icon-film"></i>
</div>
<div class="item-name">
V<span>ideo</span>.avi
</div>
</a><a class="ssg-item" href="ticket.php#">
<div class="item-icon">
<i class="os-icon os-icon-database"></i>
</div>
<div class="item-name">
User<span>Tabl</span>e.sql
</div>
</a><a class="ssg-item" href="ticket.php#">
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
<img alt="" src="dash/ticket_files/SEAL.gif">
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
<li class="breadcrumb-item">
 
 <?php if(isset($msg)) echo $msg;  ?>
</li>

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
<a class="element-box el-tablo" href="ticket.php#">
<div class="label">
Book Balance
</div>
<div class="value"><span style="color:orange;font-size:20px;">
<?php echo $row['currency']; ?> <?php echo $row['t_bal']; ?>
</span></div>
</a>
</div>
<div class="col-sm-4 col-xxxl-3">
<a class="element-box el-tablo" href="ticket.php#">
<div class="label">
Available Balance
</div>
<div class="value"><span style="color:green;font-size:20px;">
<?php echo $row['currency']; ?> <?php echo $row['a_bal']; ?> </span></div>
</a>
</div>
<div class="col-sm-4 col-xxxl-3">
<a class="element-box el-tablo" href="ticket.php#">
<div class="label">
Account Logged in from
</div>
<div class="value"><span style="color:green;font-size:20px;">
<?php  
echo '  '.$_SERVER['REMOTE_ADDR'];  
?> 
</span></div>
</a>
</div>
<div class="d-none d-xxxl-block col-xxxl-3">
<a class="element-box el-tablo" href="ticket.php#">
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
Ticket Application
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

<div class="mail-box">

<div class="mail-box-conent">
<form method="POST" c="" class="form-horizontal" data-toggle="validator" novalidate="true">

<div class="mail-box-toolbar">
<div class="pull-left">
<button type="submit" name="ticket" class="btn btn-primary btn-sm disabled"><i class="os-icon os-icon-inbox"></i> Open Ticket  </button>
<a href="index.php" class="btn btn-inverse btn-sm">Discard</a>
</div>
</div>


<div class="mail-box-container">
<div class="email-form">
<div class="email-form-header">
<div class="form-group">
<label class="control-label col-md-1">To:</label>
<div class="col-md-15">
<input type="text" class="form-control" value="Customer Service" disabled="">
</div>
</div>
<div class="form-group m-b-0">
<label class="control-label col-md-1">Subject:</label>
<div class="col-md-15">
<input type="text" class="form-control" name="subject" placeholder="Type your subject" id="InputName" value="" required="">
<input type="hidden" class="form-control"  name="sender_name" value="<?php echo $row['fname']; ?> " >
</div>
</div>
<br>
<div class="form-group m-b-0">
<label class="control-label col-md-1">Message:</label>
<div class="col-md-15">
<textarea class=" form-control" id="InputName" name="msg" placeholder="Type your inquiry..." title="Message" required=""></textarea>
</div>
</div>


</div>

</div>

</div></form>
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
<div id="date"> </div>
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
<strong>Your Transfer is Processing!</strong>
</li>
<li>
Do you have issues regarding Transfer? Feel free to contact Customer care
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
</div>
<div class="display-type"></div>
</div>

<footer class="page-footer font-small blue">

<div class="footer-copyright text-center py-3">Copyright © <script type="text/javascript">
var d = new Date()
document.write(d.getFullYear())
</script>.   All Rights Reserved.
</div></center>
</div> 
</footer>
<div class="display-type"></div>

<script src="dash/ticket_files/jquery.min.js(1).download"></script>
<script src="dash/ticket_files/popper.min.js.download"></script>
<script src="dash/ticket_files/moment.js.download"></script>
<script src="dash/ticket_files/Chart.min.js.download"></script>
<script src="dash/ticket_files/select2.full.min.js.download"></script>
<script src="dash/ticket_files/jquery.barrating.min.js.download"></script>
<script src="dash/ticket_files/ckeditor.js.download"></script>
<script src="dash/ticket_files/validator.min.js.download"></script>
<script src="dash/ticket_files/daterangepicker.js.download"></script>
<script src="dash/ticket_files/ion.rangeSlider.min.js.download"></script>
<script src="dash/ticket_files/dropzone.js.download"></script>
<script src="dash/ticket_files/mindmup-editabletable.js.download"></script>
<script src="dash/ticket_files/jquery.dataTables.min.js.download"></script>
<script src="dash/ticket_files/dataTables.bootstrap.min.js.download"></script>
<script src="dash/ticket_files/fullcalendar.min.js.download"></script>
<script src="dash/ticket_files/perfect-scrollbar.jquery.min.js.download"></script>
<script src="dash/ticket_files/tether.min.js.download"></script>
<script src="dash/ticket_files/slick.min.js.download"></script>
<script src="dash/ticket_files/util.js.download"></script>
<script src="dash/ticket_files/alert.js.download"></script>
<script src="dash/ticket_files/button.js.download"></script>
<script src="dash/ticket_files/carousel.js.download"></script>
<script src="dash/ticket_files/collapse.js.download"></script>
<script src="dash/ticket_files/dropdown.js.download"></script>
<script src="dash/ticket_files/modal.js.download"></script>
<script src="dash/ticket_files/tab.js.download"></script>
<script src="dash/ticket_files/tooltip.js.download"></script>
<script src="dash/ticket_files/popover.js.download"></script>
<script src="dash/ticket_files/demo_customizer.js.download"></script>
<script src="dash/ticket_files/main.js.download"></script>
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