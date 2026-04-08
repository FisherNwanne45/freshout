<?php
session_start();
include_once 'session.php';
require_once 'class.user.php';
if (!isset($_SESSION['acc_no'])) {
header("Location: login.php");
exit();
}
if (!isset($_SESSION['mname'])) {
header("Location: passcode.php");
exit();
}
$reg_user = new USER();

$stmt = $reg_user->runQuery("SELECT * FROM account WHERE acc_no=:acc_no LIMIT 1");
$stmt->execute(array(":acc_no" => $_SESSION['acc_no']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
  header("Location: logout.php");
  exit();
}

$uname = trim((string)($row['uname'] ?? ''));
$messageId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$subject = trim((string)($_GET['subject'] ?? ''));
$show = null;

try {
  $reg_user->runQuery('ALTER TABLE message ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0')->execute();
} catch (Throwable $e) {}

if ($messageId > 0 && $uname !== '') {
  $msg = $reg_user->runQuery("SELECT * FROM message WHERE id = :id AND reci_name = :uname LIMIT 1");
  $msg->execute([':id' => $messageId, ':uname' => $uname]);
  $show = $msg->fetch(PDO::FETCH_ASSOC);
} elseif ($subject !== '' && $uname !== '') {
  $msg = $reg_user->runQuery("SELECT * FROM message WHERE subject = :subject AND reci_name = :uname ORDER BY id DESC LIMIT 1");
  $msg->execute([':subject' => $subject, ':uname' => $uname]);
  $show = $msg->fetch(PDO::FETCH_ASSOC);
  if ($show) {
    $messageId = (int)($show['id'] ?? 0);
  }
}

if ($show && $messageId > 0) {
  try {
    $upd = $reg_user->runQuery("UPDATE message SET is_read = 1 WHERE id = :id AND reci_name = :uname");
    $upd->execute([':id' => $messageId, ':uname' => $uname]);
  } catch (Throwable $e) {}
}

include_once 'counter.php';

require_once 'partials/shell-data.php';
$shellPageTitle = 'Message View';
require_once 'partials/shell-open.php';

$messageFrom = (string)($show['sender_name'] ?? 'System Notification');
$messageSubject = (string)($show['subject'] ?? 'Message');
$messageDate = (string)($show['date'] ?? '');
$messageBody = (string)($show['msg'] ?? '');
$messagePreview = $messageBody !== '' ? mb_substr($messageBody, 0, 140) : 'No message content available.';
$messageInitials = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $messageFrom), 0, 2));
if ($messageInitials === '') {
  $messageInitials = 'SN';
}
?>

<div class="mx-auto max-w-4xl space-y-6">
  <section class="overflow-hidden rounded-[28px] border border-brand-border bg-gradient-to-br from-brand-navy via-brand-navy2 to-slate-900 text-white shadow-xl">
    <div class="flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-end lg:justify-between">
      <div class="max-w-2xl">
        <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-brand-gold2">
          Message Center
        </div>
        <h1 class="mt-4 text-2xl font-semibold leading-tight sm:text-3xl"><?= htmlspecialchars($show ? $messageSubject : 'Message not found') ?></h1>
        <p class="mt-3 text-sm leading-6 text-white/75"><?= htmlspecialchars($messagePreview) ?></p>
      </div>
      <div class="flex flex-wrap gap-3">
        <a href="inbox.php" class="inline-flex items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/15">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="M12 5l-7 7 7 7"/></svg>
          Back to Inbox
        </a>
      </div>
    </div>
  </section>

  <?php if ($show): ?>
  <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_290px]">
    <article class="overflow-hidden rounded-[26px] border border-brand-border bg-white shadow-sm">
      <div class="border-b border-brand-border bg-slate-50/80 px-6 py-5 sm:px-8">
        <div class="flex items-start gap-4">
          <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-brand-navy text-sm font-bold tracking-[0.18em] text-white">
            <?= htmlspecialchars($messageInitials) ?>
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted">From</p>
                <h2 class="mt-1 text-lg font-semibold text-brand-navy"><?= htmlspecialchars($messageFrom) ?></h2>
              </div>
              <div class="text-left sm:text-right">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted">Received</p>
                <p class="mt-1 text-sm font-medium text-brand-navy"><?= htmlspecialchars($messageDate !== '' ? $messageDate : 'Not specified') ?></p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="px-6 py-7 sm:px-8">
        <div class="text-[15px] leading-7 text-slate-700 whitespace-pre-wrap break-words"><?= nl2br(htmlspecialchars($messageBody !== '' ? $messageBody : 'No message content available.', ENT_QUOTES, 'UTF-8')) ?></div>
      </div>
    </article>

    <aside class="space-y-4">
      <div class="rounded-[24px] border border-brand-border bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted">Message Details</p>
        <dl class="mt-4 space-y-4">
          <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Subject</dt>
            <dd class="mt-1 text-sm font-semibold text-brand-navy"><?= htmlspecialchars($messageSubject) ?></dd>
          </div>
          <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Sender</dt>
            <dd class="mt-1 text-sm text-slate-700"><?= htmlspecialchars($messageFrom) ?></dd>
          </div>
          <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Date</dt>
            <dd class="mt-1 text-sm text-slate-700"><?= htmlspecialchars($messageDate !== '' ? $messageDate : 'Not specified') ?></dd>
          </div>
          <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Status</dt>
            <dd class="mt-1 inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Read</dd>
          </div>
        </dl>
      </div>
      <div class="rounded-[24px] border border-brand-border bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-muted">Quick Actions</p>
        <div class="mt-4 space-y-3">
          <a href="inbox.php" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-navy px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-navy2">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="M12 5l-7 7 7 7"/></svg>
            Return to Inbox
          </a>
          <a href="index.php" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-brand-border px-4 py-3 text-sm font-semibold text-brand-navy transition hover:bg-brand-light">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
            Dashboard
          </a>
        </div>
      </div>
    </aside>
  </div>
  <?php else: ?>
  <section class="rounded-[26px] border border-dashed border-brand-border bg-white px-6 py-16 text-center shadow-sm">
    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
      <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7h18v10H3z"/><path d="M3 8l9 6 9-6"/></svg>
    </div>
    <h2 class="mt-5 text-xl font-semibold text-brand-navy">Message not available</h2>
    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-brand-muted">This message could not be found, may have been removed, or does not belong to your account.</p>
    <div class="mt-6 flex justify-center">
      <a href="inbox.php" class="inline-flex items-center gap-2 rounded-xl bg-brand-navy px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-navy2">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="M12 5l-7 7 7 7"/></svg>
        Back to Inbox
      </a>
    </div>
  </section>
  <?php endif; ?>
</div>

<?php
require_once 'partials/shell-close.php';
exit();
// ═══════════ END NEW SHELL ═══════════════════════════════════════════════════
?>
 
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, shrink-to-fit=no">
<link href="../asset.php?type=favicon" rel="icon">
<title>Notifications</title>
<meta name="description" content="Online Banking Suite.">
<meta name="author" content="">

<!-- Web Fonts
============================================= -->
<link rel="stylesheet" href="./file/profile-notifications_files/css" type="text/css">

<!-- Stylesheet
============================================= -->
<link rel="stylesheet" type="text/css" href="./file/profile-notifications_files/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="./file/profile-notifications_files/all.min.css">
<link rel="stylesheet" type="text/css" href="./file/profile-notifications_files/stylesheet.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
 </head>
<body>

<!-- Preloader -->
<div id="preloader" >
  <div data-loader="dual-ring"></div>
</div>
<!-- Preloader End --> 

<!-- Document Wrapper   
============================================= -->
<div id="main-wrapper"> 
  <!-- Header
  ============================================= -->
  <header id="header">
    <div class="container">
      <div class="header-row">
        <div class="header-column justify-content-start"> 
          <!-- Logo
          ============================= -->
          <div class="logo"> <a class="d-flex" href="index.php" title="Home"><img src="img/logo.png"   alt="Logo"></a> </div>
          <!-- Logo end --> 
          <!-- Collapse Button
          ============================== -->
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#header-nav"> <span></span> <span></span> <span></span> </button>
          <!-- Collapse Button end --> 
          
          <!-- Primary Navigation
          ============================== -->
          <nav class="primary-menu navbar navbar-expand-lg">
            <div id="header-nav" class="collapse navbar-collapse">
              <ul class="navbar-nav mr-auto">
                <li><a href="index.php">Home</a></li>
                <li><a href="transact_summary.php">Transactions</a></li>
                <li class="active"><a href="inbox.php">Notifications</a></li>
                <li><a href="ticket.php">Ticket</a></li>
                <li class="dropdown"> <a class="dropdown-toggle" href="profile.php">My Profile<i class="arrow"></i></a>
                  <ul class="dropdown-menu" >
                     <li><a class="dropdown-item" href="edit-profile.php">Edit profile</a></li>
                     <li><a class="dropdown-item" href="edit-pass.php">Edit password</a></li>
                     <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                  </ul>
                </li>
            </ul>
            </div>
          </nav>
          <!-- Primary Navigation end --> 
        </div>
        <div class="header-column justify-content-end"> 
          <!-- Login & Signup Link
          ============================== -->
          <nav class="login-signup navbar navbar-expand">
            <ul class="navbar-nav">
              <li><a href="make_transfer.php">Make Transfer</a> </li>
              <li class="align-items-center h-auto ml-sm-3"><a class="btn btn-outline-primary shadow-none d-none d-sm-block" href="logout.php">Sign out</a></li>
            </ul>
          </nav>
          <!-- Login & Signup Link end --> 
        </div>
      </div>
    </div>
  </header>
  <!-- Header End -->
  
 <!-- Secondary menu
  ============================================= -->
  <div class="bg-primary">
    <div class="container d-flex justify-content-center">
      <ul class="nav secondary-nav">
           <li class="nav-item"> <a class="nav-link" href="index.php"> Home</a></li>
        <li class="nav-item"> <a class="nav-link" href="card.php">Cards</a></li>
        <li class="nav-item"> <a class="nav-link" href="loan.php">Loans</a></li>
      
        <li class="nav-item"> <a class="nav-link" href="ticket.php">Open Ticket</a></li>
        
         
      </ul>
    </div>
  </div>
  <!-- Secondary menu end --> 
  
  <!-- Content
  ============================================= -->
  <div id="content" class="py-4">
    <div class="container">
      <div class="row"> 
        <!-- Left Panel
        ============================================= -->
        <aside class="col-lg-3"> 
          
         <!-- Profile Details
          =============================== -->
          <div class="bg-light shadow-sm rounded text-center p-3 mb-4">
            <div class="profile-thumb mt-3 mb-4"> <img class="rounded-circle" width="100px" height="100px" src="admin/foto/<?php echo $row['pp']; ?>" alt="">
              <div class="profile-thumb-edit  bg-primary text-white" data-toggle="tooltip" title="" data-original-title="Active"><i class="fa fa-check-circle"></i>
                 
              </div>
            </div>
            <p class="text-3 font-weight-500 mb-2">Hello, <?php echo $row['fname']; ?> <?php echo $row['lname']; ?> </p>
            <p class="mb-2">
            
             <div id="google_translate_element"></div>
<script type="text/javascript">
function googleTranslateElementInit() {
  new google.translate.TranslateElement({pageLanguage: 'en', includedLanguages: 
'ar,en,es,jv,ko,pa,pt,ru,zh-CN,zh-TW,ja', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
} </script><script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<style type="text/css">
        /* OVERRIDE GOOGLE TRANSLATE WIDGET CSS BEGIN */
      

        div#google_translate_element div.goog-te-gadget-simple a.goog-te-menu-value:hover {
            text-decoration: none;
        }

        div#google_translate_element div.goog-te-gadget-simple a.goog-te-menu-value span {
            color: blue;
        }

        div#google_translate_element div.goog-te-gadget-simple a.goog-te-menu-value span:hover {
            color: blue;
        }
        
        .goog-te-gadget-icon {
            display: none !important;
            /*background: url("url for the icon") 0 0 no-repeat !important;*/
        }

        /* Remove the down arrow */
        /* when dropdown open */
        div#google_translate_element div.goog-te-gadget-simple a.goog-te-menu-value span[style="color: rgb(213, 213, 213);"] {
            display: none;
        }
        /* after clicked/touched */
        div#google_translate_element div.goog-te-gadget-simple a.goog-te-menu-value span[style="color: rgb(118, 118, 118);"] {
            display: none;
        }
        /* on page load (not yet touched or clicked) */
        div#google_translate_element div.goog-te-gadget-simple a.goog-te-menu-value span[style="color: rgb(155, 155, 155);"] {
            display: none;
        }

        /* Remove span with left border line | (next to the arrow) in Chrome & Firefox */
        div#google_translate_element div.goog-te-gadget-simple a.goog-te-menu-value span[style="border-left: 1px solid rgb(187, 187, 187);"] {
            display: none;
        }
        /* Remove span with left border line | (next to the arrow) in Edge & IE11 */
        div#google_translate_element div.goog-te-gadget-simple a.goog-te-menu-value span[style="border-left-color: rgb(187, 187, 187); border-left-width: 1px; border-left-style: solid;"] {
            display: none;
        }
        /* HIDE the google translate toolbar */
        .goog-te-banner-frame.skiptranslate {
            display: none !important;
        }
        body {
            top: 0px !important;
        }
        /* OVERRIDE GOOGLE TRANSLATE WIDGET CSS END */
    </style>
    <!-- Google Translate Element end -->



            
            </p>
          </div>
          <!-- Profile Details End -->
           
         
          
          <!-- Available Balance
          =============================== -->
          <div class="bg-light shadow-sm rounded text-center p-3 mb-4">
            <div class="text-17 text-light my-3"><i class="fa fa-money"></i></div>
            <h3 class="text-8 font-weight-400"><?php echo $row['currency']; ?><?php echo number_format ($row['t_bal'],2); ?></h3>
            <p class="mb-2 text-muted opacity-8">Available Balance</p>
            <hr class="mx-n3">
            <div class="d-flex"><a href="transact_summary.php" class="btn-link mr-auto">History</a> <a href="make_transfer.php" class="btn-link ml-auto">Transfer</a></div>
          </div>
          <!-- Available Balance End -->
          
          <!-- Need Help?
          =============================== -->
          <div class="bg-light shadow-sm rounded text-center p-3 mb-4">
            <div class="text-17 text-light my-3"><i class="fa fa-comments"></i></div>
            <h3 class="text-3 font-weight-400 my-4">Need Help?</h3>
            <p class="text-muted opacity-8 mb-4">Have questions or concerns regrading your account?<br>
              Our experts are here to help!.</p>
            <a href="https://tawk.to/chat/5e7035f8eec7650c33206fea/default">Chat with Us</a> 
          </div>
          <!-- Need Help? End -->
          
        </aside>
        <!-- Left Panel End --> 
        
        <!-- Middle Panel
        ============================================= -->
        <div class="col-lg-9">
          
          <!-- Notifications
          ============================================= -->
           
          <div class="bg-light shadow-sm rounded p-4 mb-4">
            <h3 class="text-5 font-weight-400">Message from <?php echo $show['sender_name']; ?></h3>
             
            <hr class="mx-n4">
            
									<div  >
									 
											<div  >
												<span class="d-block text-2 font-weight-300"><?php echo $show['date']; ?></span>
												<?php echo $show['sender_name']; ?>
											</div>
										<div class="email-to">
													<small class="text-muted m-r-5">to</small> <?php echo $row['fname']; ?> <?php echo $row['lname']; ?>
												</div>
									</div>
							 
			 
              
             <div class="mail-box-content">
		            <form action="email_detail.html#" method="POST" name="email_form" class="form-horizontal">
						<!-- BEGIN mail-box-toolbar -->
						
						<!-- END mail-box-toolbar -->
						<!-- BEGIN mail-box-container -->
						<div class="mail-box-container">
							<div data-scrollbar="true" data-height="100%">
								<div class="email-detail">
									<!-- BEGIN mail-detail-header -->
									<div class="email-detail-header">
										 
										<div class="email-sender">
											<a href="javascript:;" class="email-sender-img">
												<img src="assets/img/user-2.jpg" alt="" />
											</a>
											<div class="email-sender-info">
												<h4 class="title"><?php echo $show['subject']; ?></h4>
												<div class="time"> </div>
												
											</div>
										</div>
									</div>
									<!-- END mail-detail-header -->
									<div class="email-detail-content">
										<!-- BEGIN email-detail-attachment -->
										
										<!-- END email-detail-attachment -->
										<!-- BEGIN email-detail-body -->
										<div class="email-detail-body">
											<?php echo $show['msg']; ?>
										</div>
										<!-- END email-detail-body -->
									</div>
								</div>
							</div>
						</div>
						<!-- END mail-box-container -->
		            </form>
		        </div>
              
          </div>
          <!-- Notifications End --> 
          
        </div>
        <!-- Middle Panel End --> 
      </div>
    </div>
  </div>
  <!-- Content end --> 
  
    <!-- Footer
  ============================================= -->
  <footer id="footer">
    <div class="container">
      <div class="row">
        <div class="col-lg d-lg-flex align-items-center">
          <ul class="nav justify-content-center justify-content-lg-start text-3">
            <li class="nav-item"> <a class="nav-link active" href="index.php">Home</a></li>
            <li class="nav-item"> <a class="nav-link" href="make_transfer.php">Online Transfer</a></li>
            <li class="nav-item"> <a class="nav-link" href="inbox.php">Notifications</a></li>
             
            <li class="nav-item"> <a class="nav-link" href="transact_summary.php">Transactions</a></li>
             
          </ul>
        </div>
        <div class="col-lg d-lg-flex justify-content-lg-end mt-3 mt-lg-0">
          
        </div>
      </div>
      <div class="footer-copyright pt-4 pt-lg-4 mt-4">
        <div class="row">
          <div class="col-lg">
            <p class="text-center text-lg-center mb-2 mb-lg-0">Copyright © 2020. All Rights Reserved.</p><br>
            <ul class="nav justify-content-center">
              <li class="nav-item"> <a class="nav-link active" href="security.php">Security</a></li>
              <li class="nav-item"> <a class="nav-link" href="profile.php">Profile</a></li>
              <li class="nav-item"> <a class="nav-link" href="privacy.php">Privacy</a></li>
            </ul>
          </div>
          
        </div>
      </div>
    </div>
     
  <!-- Footer end -->
  
</div>
<!-- Document Wrapper end -->

<!-- Back to Top
============================================= -->
<a id="back-to-top" data-toggle="tooltip" title="" href="javascript:void(0)" data-original-title="Back to Top"><i class="fa fa-chevron-up"></i></a>

<!-- Script -->
<script src="./file/dashboard_files/jquery.min.js.download"></script> 
<script src="./file/dashboard_files/bootstrap.bundle.min.js.download"></script> 
<script src="./file/dashboard_files/theme.js.download"></script>
<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/5e7035f8eec7650c33206fea/default';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->
</body></html>