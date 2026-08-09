<?php
include('session.php');
?>
<?php session_start(); ?>
<?php
require_once __DIR__ . '/bootstrap.php';

$theme7ContactReturnUrl = ($appBase === '' ? '' : $appBase) . '/contact.php#form';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $theme7ContactReturnUrl, true, 302);
    exit;
}

$postedToken = (string)($_POST['contact_token'] ?? '');
$sessionToken = (string)($_SESSION['theme7_contact_csrf'] ?? '');
$honeypotValue = trim((string)($_POST['website'] ?? ''));
$formStartedAt = (int)($_POST['form_started_at'] ?? 0);
$now = time();
$lastSubmitAt = (int)($_SESSION['theme7_contact_last_submit_at'] ?? 0);

$minimumFillSeconds = 3;
$cooldownSeconds = 20;

$hasValidToken = ($postedToken !== '' && $sessionToken !== '' && hash_equals($sessionToken, $postedToken));
$isHoneypotTriggered = ($honeypotValue !== '');
$isTooFast = ($formStartedAt <= 0 || ($now - $formStartedAt) < $minimumFillSeconds);
$isCoolingDown = ($lastSubmitAt > 0 && ($now - $lastSubmitAt) < $cooldownSeconds);

$visitorName = '';
$visitorEmail = '';
$visitorPhone = '';
$visitorMessage = '';

if (isset($_POST['name1'])) {
    $visitorName = trim(filter_var((string)$_POST['name1'], FILTER_SANITIZE_STRING));
}
if (isset($_POST['phone1'])) {
    $visitorPhone = trim(filter_var((string)$_POST['phone1'], FILTER_SANITIZE_STRING));
}
if (isset($_POST['email'])) {
    $candidateEmail = str_replace(array("\r", "\n", "%0a", "%0d"), '', (string)$_POST['email']);
    $visitorEmail = (string)(filter_var($candidateEmail, FILTER_VALIDATE_EMAIL) ?: '');
}
if (isset($_POST['message'])) {
    $visitorMessage = trim(filter_var((string)$_POST['message'], FILTER_SANITIZE_STRING));
}

$theme7ContactOldInput = [
    'name1' => $visitorName,
    'email' => trim((string)($_POST['email'] ?? '')),
    'phone1' => $visitorPhone,
    'message' => $visitorMessage,
];

$settingGet = static function ($conn, $key, $default = '') {
    if (!($conn instanceof mysqli)) {
        return $default;
    }

    $safeKey = $conn->real_escape_string((string)$key);

    try {
        $res = $conn->query("SELECT setting_value FROM site_settings WHERE setting_key='" . $safeKey . "' LIMIT 1");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            return (string)($row['setting_value'] ?? $default);
        }
    } catch (Throwable $e) {
    }

    try {
        $res = $conn->query("SELECT `value` FROM site_settings WHERE `key`='" . $safeKey . "' LIMIT 1");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            return (string)($row['value'] ?? $default);
        }
    } catch (Throwable $e) {
    }

    return (string)$default;
};

$recipient = filter_var((string)($email2 ?? $email ?? ''), FILTER_VALIDATE_EMAIL);
if (!$recipient) {
    $recipient = filter_var((string)($email ?? ''), FILTER_VALIDATE_EMAIL);
}

$smtpHost = trim($settingGet($conn, 'smtp_host', (string)($APP_CONFIG['smtp']['host'] ?? '')));
$smtpPort = (int)$settingGet($conn, 'smtp_port', (string)($APP_CONFIG['smtp']['port'] ?? 465));
$smtpSecureRaw = trim($settingGet($conn, 'smtp_secure', (string)($APP_CONFIG['smtp']['secure'] ?? 'ssl')));
$smtpUser = trim($settingGet($conn, 'smtp_username', (string)($APP_CONFIG['smtp']['username'] ?? '')));
$smtpPass = (string)$settingGet($conn, 'smtp_password', (string)($APP_CONFIG['smtp']['password'] ?? ''));
$smtpFrom = trim($settingGet($conn, 'smtp_from', (string)($APP_CONFIG['smtp']['from'] ?? '')));
$smtpFromName = trim($settingGet($conn, 'smtp_from_name', (string)($APP_CONFIG['smtp']['from_name'] ?? $name ?? 'Website')));
$smtpReplyTo = trim($settingGet($conn, 'smtp_reply_to', (string)($APP_CONFIG['smtp']['reply_to'] ?? $smtpFrom)));
$smtpSecure = strtolower($smtpSecureRaw) === 'starttls' ? 'tls' : strtolower($smtpSecureRaw);

$feedbackType = 'error';
$feedbackMessage = 'We are sorry, but the message failed.';

if (!$hasValidToken || $isHoneypotTriggered || $isTooFast || $isCoolingDown) {
    $feedbackMessage = 'Unable to submit message. Please wait a moment and try again.';
} elseif (!$recipient) {
    $feedbackMessage = 'We are sorry, recipient email is not configured by admin.';
} elseif ($visitorName === '' || $visitorEmail === '' || $visitorMessage === '') {
    $feedbackMessage = 'Please provide your name, valid email and message.';
} elseif ($smtpHost === '' || $smtpUser === '' || $smtpFrom === '') {
    $feedbackMessage = 'We are sorry, SMTP settings are incomplete. Please contact support.';
} else {
    $_SESSION['theme7_contact_last_submit_at'] = $now;

    $safeName = htmlspecialchars($visitorName, ENT_QUOTES, 'UTF-8');
    $safeEmail = htmlspecialchars($visitorEmail, ENT_QUOTES, 'UTF-8');
    $safePhone = htmlspecialchars($visitorPhone, ENT_QUOTES, 'UTF-8');
    $safeMessage = nl2br(htmlspecialchars($visitorMessage, ENT_QUOTES, 'UTF-8'));

    $emailContent = "<html><body>";
    $emailContent .= "<table style='font-family: Arial; border-collapse: collapse;'><tbody><tr><td style='background: #eee; padding: 10px;'>Name</td><td style='background: #fda; padding: 10px;'>" . $safeName . "</td></tr>";
    $emailContent .= "<tr><td style='background: #eee; padding: 10px;'>Email</td><td style='background: #fda; padding: 10px;'>" . $safeEmail . "</td></tr>";
    $emailContent .= "<tr><td style='background: #eee; padding: 10px;'>Phone</td><td style='background: #fda; padding: 10px;'>" . $safePhone . "</td></tr>";
    $emailContent .= "<tr><td style='background: #eee; padding: 10px;'>Message</td><td style='background: #fda; padding: 10px;'>" . $safeMessage . "</td></tr>";
    $emailContent .= "</tbody></table></body></html>";

    try {
        if (!defined('FILTER_FLAG_HOST_REQUIRED')) {
            define('FILTER_FLAG_HOST_REQUIRED', 0);
        }
        if (!defined('PHPMailer\\PHPMailer\\FILTER_FLAG_HOST_REQUIRED')) {
            define('PHPMailer\\PHPMailer\\FILTER_FLAG_HOST_REQUIRED', 0);
        }

        require_once dirname(__DIR__, 2) . '/user/PHPMailer-master/src/Exception.php';
        require_once dirname(__DIR__, 2) . '/user/PHPMailer-master/src/PHPMailer.php';
        require_once dirname(__DIR__, 2) . '/user/PHPMailer-master/src/SMTP.php';

        $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = $smtpHost;
        $mailer->Port = $smtpPort > 0 ? $smtpPort : 465;
        $mailer->SMTPAuth = true;
        $mailer->Username = $smtpUser;
        $mailer->Password = $smtpPass;
        $mailer->SMTPSecure = in_array($smtpSecure, ['ssl', 'tls'], true) ? $smtpSecure : '';
        $mailer->setFrom($smtpFrom, $smtpFromName !== '' ? $smtpFromName : ($name !== '' ? $name : 'Website'));
        $mailer->addAddress((string)$recipient);
        $mailer->addReplyTo($visitorEmail, $visitorName !== '' ? $visitorName : $visitorEmail);
        if ($smtpReplyTo !== '' && strcasecmp($smtpReplyTo, $visitorEmail) !== 0) {
            $mailer->addReplyTo($smtpReplyTo);
        }
        $mailer->isHTML(true);
        $mailer->Subject = 'New Contact Message from Client';
        $mailer->Body = $emailContent;

        if ($mailer->send()) {
            $feedbackType = 'success';
            $feedbackMessage = 'Your Message is Submitted Successfully!';
        }
    } catch (Throwable $e) {
        error_log('Theme7 contact SMTP send failed: ' . $e->getMessage());
    }
}

try {
    $_SESSION['theme7_contact_csrf'] = bin2hex(random_bytes(32));
} catch (Throwable $e) {
    $_SESSION['theme7_contact_csrf'] = hash('sha256', uniqid('theme7_contact_', true));
}

$_SESSION['theme7_contact_form_loaded_at'] = time();

if ($feedbackType === 'success' || $isHoneypotTriggered) {
    unset($_SESSION['theme7_contact_old_input']);
} else {
    $_SESSION['theme7_contact_old_input'] = $theme7ContactOldInput;
}

$_SESSION['theme7_contact_feedback'] = [
    'type' => $feedbackType,
    'message' => $feedbackMessage,
];

header('Location: ' . $theme7ContactReturnUrl, true, 302);
exit;
?>

  
<!DOCTYPE html>
<html lang="en">
 <?php require_once __DIR__ . '/bootstrap.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- The above 4 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <!-- Title -->
    <title><?php echo $name; ?> - Contact us</title>

    <!-- Favicon -->
    <link rel="icon" href="img/core-img/favicon.ico">

    <!-- Stylesheet -->
    <link rel="stylesheet" href="style.css?v=20260804h">

</head>

<body>
    <!-- Preloader -->
    <div class="preloader d-flex align-items-center justify-content-center">
        <div class="lds-ellipsis">
          <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>

    <!-- ##### Header Area Start ##### -->
    <header class="header-area">
        <!-- Top Header Area -->
        <div class="top-header-area">
            <div class="container h-100">
                <div class="row h-100 align-items-center">
                    <div class="col-12 d-flex justify-content-between">
                        <!-- Logo Area -->
                        <div class="logo">
                            <a href="index.php"><img src="<?php echo $logo_url ?: ($url . '/admin/assets/images/logo/' . $image); ?>" alt="" width='180'></a>
                        </div>

                        <!-- Top Contact Info -->
                        <div class="top-contact-info d-flex align-items-center">




<?php echo $translate; ?>

<div class="top-auth-actions d-flex align-items-center">
    <a class="top-auth-btn top-auth-login" href="<?php echo $login; ?>" aria-label="Login"><i class="fa fa-sign-in" aria-hidden="true"></i><span>Login</span></a>
    <a class="top-auth-btn top-auth-register" href="<?php echo $register; ?>" aria-label="Open your account"><i class="fa fa-user-plus" aria-hidden="true"></i><span>Open Your Account</span></a>
</div>



                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navbar Area -->
        <div class="credit-main-menu" id="sticker">
            <div class="classy-nav-container breakpoint-off">
                <div class="container">
                    <!-- Menu -->
                    <nav class="classy-navbar justify-content-between" id="creditNav">

                        <!-- Navbar Toggler -->
                        <div class="classy-navbar-toggler">
                            <span class="navbarToggler"><span></span><span></span><span></span></span>
                        </div>

                        <!-- Menu -->
                        <div class="classy-menu">

                            <!-- Close Button -->
                            <div class="classycloseIcon">
                                <div class="cross-wrap"><span class="top"></span><span class="bottom"></span></div>
                            </div>

                            
                            <!-- Nav Start -->
                            <div class="classynav">
                                <ul>
                                    <li><a class="text-warning" href="index.php">Home</a></li>
                                    
                                    <li><a href="about.php">About</a></li>
                                    <li><a href="#">Personal </a>
                                        <ul class="dropdown">
                                            <li><a href="pbccards.php">Credit Cards</a></li>
                                            <li><a href="pbcurrent.php">Current Accounts</a></li>
                                            <li><a href="pbsavings.php">Savings Accounts</a></li>
                                            <li><a href="pbloans.php">Personal Loans</a></li>
                                            <li><a href="pbmortgages.php">Mortgages</a></li>
                                            <li><a href="pbinsurance.php">Personal Insurance</a></li>
                                        </ul>
                                    </li>
                                    
                                    
                                    <li><a href="#">Business </a>
                                        <div class="megamenu">
                                            <ul class="single-mega cn-col-3">
                                                <li><a href="bbbcards.php">Bank Cards</a></li>
                                                <li><a href="bbdeposits.php">Deposit</a></li>
                                                <li><a href="bbforeigndrafts.php">Foreign Drafts</a></li>
                                                <li><a href="bbintchecking.php">Interest Checking</a></li>
                                            </ul>
                                            <ul class="single-mega cn-col-3">
                                                <li><a href="bbebanking.php">Electronic Banking</a></li>
                                                <li><a href="bbinvestbenefit.php">Investment/ Benefit Care Taking</a></li>
                                                <li><a href="bbmmaccounts.php">Money Market Account</a></li>
                                                <li><a href="bbsmallbiz.php">Small Business Checking</a></li>
                                            </ul>
                                            <ul class="single-mega cn-col-3">
                                                <li><a href="bbbizcashmgt.php">Business Cash Management</a></li>
                                                <li><a href="bbcurrencyriskmgt.php">Currency Risk Management</a></li>
                                                <li><a href="bbforeignccdept.php">Foreign Currency Call Deposit</a></li>
                                                <li><a href="bbforexriskmgt.php">Foreign Exchange Risk Management</a></li>
                                            </ul>
                                        </div>
                                    </li>
                                    <li><a href="invest.php"  >Investment Banking</a>
                                        <ul class="dropdown">
                                            <li><a href="pvbservices.php">Asset Management</a></li>
                                            <li><a href="pvbinsurance.php">Brokerage</a></li>
                                            <li><a href="pvboffshoremb.php">Corporate Finance</a></li>
                                            </ul>
                                    </li>
                                    <li><a href="ourcareers.php">Careers</a></li>
                                    <li><a href="faqs.php">FAQs</a></li>
                                    <li><a href="contact.php">Contact</a></li>
                                </ul>
                            </div>
                            <!-- Nav End --><!-- Nav End -->
                        </div>

                        <!-- Contact -->
                        <div class="contact">
                            <a href="<?php echo $login; ?>"><img src="img/core-img/call2.png"> Online Banking</a>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>    <!-- ##### Header Area End ##### -->

    <!-- ##### Breadcrumb Area Start ##### -->
    <section class="breadcrumb-area bg-img bg-overlay jarallax" style="background-image: url(img/bg-img/13cont.jpg);">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-12">
                    <div class="breadcrumb-content">
                        <h2>Contact</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Contact</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ##### Breadcrumb Area End ##### -->

    <!-- ##### Contact Area Start ##### -->
    <section class="contact-area section-padding-100-0">
        <div class="container">
            <div class="row">
                <!-- Single Contact Area -->
                <div class="col-12 col-lg-4">
                    <div class="single-contact-area mb-100">
                        <!-- Logo -->
                        <p><strong>Fill the below contact us form to send us email.</p>
                  </div>
                </div>
          </div>
          </div>
</div>
                    </div>
                </div>

                <!-- Single Contact Area -->
                <div class="col-12 col-lg-4">
                    <div class="single-contact-area mb-100">
                        <div class="contact--area contact-page">
                            <!-- Contact Content -->
                            <div class="contact-content">
                                <h5>Get in touch</h5>

                                <!-- Single Contact Content -->
                                <div class="single-contact-content d-flex align-items-center">
                                    <div class="icon">
                                        <img src="img/core-img/location.png" alt="">
                                    </div>
                                    <div class="text">
                                                                            <span><?php echo $addr; ?></span>
                                    </div>
                                </div>
                                <!-- Single Contact Content -->
                                <div class="single-contact-content d-flex align-items-center">
                                    <div class="icon">
                                        <img src="img/core-img/call.png" alt="">
                                    </div>
                                    <div class="text">
                                        
                                        <span>
                                                         <?php echo $phone; ?>
                                            <br>
                                            mon-fri , 09:00 AM - 05:00 PM</span>
                                    </div>
                                </div>
                                <!-- Single Contact Content -->
                                <div class="single-contact-content d-flex align-items-center">
                                    <div class="icon">
                                        <img src="img/core-img/message2.png" alt="">
                                    </div>
                                    <div class="text">
                                        <p><?php echo $email; ?></p>
                                        <span>we reply during the banking hrs.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- ##### Google Maps ##### -->
        <div class="map-area">
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3329.2815753294385!2d-117.59905128485785!3d33.441969880776284!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80dcf480bbfa429f%3A0x5e25b74e4752535e!2s904%20Via%20Presa%2C%20San%20Clemente%2C%20CA%2092672!5e0!3m2!1sen!2sus!4v1622412443787!5m2!1sen!2sus" width="100%" height="600" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            <!-- Contact Area -->
            <div class="contact---area">
                <div class="container">
                    <div class="row">
                        <div class="col-12 col-lg-8">
                            <!-- Contact Area -->
                            <div class="contact-form-area contact-page">
                                <h4 id="form" class="mb-50">Send a message</h4>
                                
                                <br>
                                <br>
                                
                                 
                                <form method="post" action="submit.php" method='post'>

<!-- DO NOT change ANY of the php sections -->
<?php
   

   if($_POST) {

    $name1     = "";
    
    $email     		= "";
    $phone1     = "";
    $message = "";
     
    
     
    if(isset($_POST['name1'])) {
        $name1 = filter_var($_POST['name1'], FILTER_SANITIZE_STRING);
    }
    
     if(isset($_POST['phone1'])) {
        $phone1 = filter_var($_POST['phone1'], FILTER_SANITIZE_STRING);
    }
   
     
    if(isset($_POST['email'])) {
        $email = str_replace(array("\r", "\n", "%0a", "%0d"), '', $_POST['email']);
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
         
    }
     
   
    if(isset($_POST['message'])) {
        $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
    }
  
    
 
    $recipient = filter_var((string)($email2 ?? $email ?? ''), FILTER_VALIDATE_EMAIL);

    if (!$recipient) {
        $recipient = filter_var((string)($email ?? ''), FILTER_VALIDATE_EMAIL);
    }

    $settingGet = static function ($conn, $key, $default = '') {
        if (!($conn instanceof mysqli)) {
            return $default;
        }

        $safeKey = $conn->real_escape_string((string)$key);

        try {
            $res = $conn->query("SELECT setting_value FROM site_settings WHERE setting_key='" . $safeKey . "' LIMIT 1");
            if ($res && $res->num_rows > 0) {
                $row = $res->fetch_assoc();
                return (string)($row['setting_value'] ?? $default);
            }
        } catch (Throwable $e) {
        }

        try {
            $res = $conn->query("SELECT `value` FROM site_settings WHERE `key`='" . $safeKey . "' LIMIT 1");
            if ($res && $res->num_rows > 0) {
                $row = $res->fetch_assoc();
                return (string)($row['value'] ?? $default);
            }
        } catch (Throwable $e) {
        }

        return (string)$default;
    };

    $smtpHost = trim($settingGet($conn, 'smtp_host', (string)($APP_CONFIG['smtp']['host'] ?? '')));
    $smtpPort = (int)$settingGet($conn, 'smtp_port', (string)($APP_CONFIG['smtp']['port'] ?? 465));
    $smtpSecureRaw = trim($settingGet($conn, 'smtp_secure', (string)($APP_CONFIG['smtp']['secure'] ?? 'ssl')));
    $smtpUser = trim($settingGet($conn, 'smtp_username', (string)($APP_CONFIG['smtp']['username'] ?? '')));
    $smtpPass = (string)$settingGet($conn, 'smtp_password', (string)($APP_CONFIG['smtp']['password'] ?? ''));
    $smtpFrom = trim($settingGet($conn, 'smtp_from', (string)($APP_CONFIG['smtp']['from'] ?? '')));
    $smtpFromName = trim($settingGet($conn, 'smtp_from_name', (string)($APP_CONFIG['smtp']['from_name'] ?? $name ?? 'Website')));
    $smtpReplyTo = trim($settingGet($conn, 'smtp_reply_to', (string)($APP_CONFIG['smtp']['reply_to'] ?? $smtpFrom)));
    $smtpSecure = strtolower($smtpSecureRaw) === 'starttls' ? 'tls' : strtolower($smtpSecureRaw);

    if (!$recipient) {
        echo '<p>We are sorry, recipient email is not configured by admin.</p>';
    } elseif ($name1 === '' || !$email || $message === '') {
        echo '<p>Please provide your name, valid email and message.</p>';
    } elseif ($smtpHost === '' || $smtpUser === '' || $smtpFrom === '') {
        echo '<p>We are sorry, SMTP settings are incomplete. Please contact support.</p>';
    } else {
        $safeName = htmlspecialchars((string)$name1, ENT_QUOTES, 'UTF-8');
        $safeEmail = htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8');
        $safePhone = htmlspecialchars((string)$phone1, ENT_QUOTES, 'UTF-8');
        $safeMessage = nl2br(htmlspecialchars((string)$message, ENT_QUOTES, 'UTF-8'));

        $email_content = "<html><body>";
        $email_content .= "<table style='font-family: Arial; border-collapse: collapse;'><tbody><tr><td style='background: #eee; padding: 10px;'>Name</td><td style='background: #fda; padding: 10px;'>" . $safeName . "</td></tr>";
        $email_content .= "<tr><td style='background: #eee; padding: 10px;'>Email</td><td style='background: #fda; padding: 10px;'>" . $safeEmail . "</td></tr>";
        $email_content .= "<tr><td style='background: #eee; padding: 10px;'>Phone</td><td style='background: #fda; padding: 10px;'>" . $safePhone . "</td></tr>";
        $email_content .= "<tr><td style='background: #eee; padding: 10px;'>Message</td><td style='background: #fda; padding: 10px;'>" . $safeMessage . "</td></tr>";
        $email_content .= "</tbody></table></body></html>";

        try {
            if (!defined('FILTER_FLAG_HOST_REQUIRED')) {
                define('FILTER_FLAG_HOST_REQUIRED', 0);
            }
            if (!defined('PHPMailer\\PHPMailer\\FILTER_FLAG_HOST_REQUIRED')) {
                define('PHPMailer\\PHPMailer\\FILTER_FLAG_HOST_REQUIRED', 0);
            }

            require_once dirname(__DIR__, 2) . '/user/PHPMailer-master/src/Exception.php';
            require_once dirname(__DIR__, 2) . '/user/PHPMailer-master/src/PHPMailer.php';
            require_once dirname(__DIR__, 2) . '/user/PHPMailer-master/src/SMTP.php';

            $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mailer->isSMTP();
            $mailer->Host = $smtpHost;
            $mailer->Port = $smtpPort > 0 ? $smtpPort : 465;
            $mailer->SMTPAuth = true;
            $mailer->Username = $smtpUser;
            $mailer->Password = $smtpPass;
            $mailer->SMTPSecure = in_array($smtpSecure, ['ssl', 'tls'], true) ? $smtpSecure : '';
            $mailer->setFrom($smtpFrom, $smtpFromName !== '' ? $smtpFromName : ($name !== '' ? $name : 'Website'));
            $mailer->addAddress($recipient);
            $mailer->addReplyTo($email, $name1 !== '' ? $name1 : $email);
            if ($smtpReplyTo !== '' && strcasecmp($smtpReplyTo, $email) !== 0) {
                $mailer->addReplyTo($smtpReplyTo);
            }
            $mailer->isHTML(true);
            $mailer->Subject = 'New Contact Message from Client';
            $mailer->Body = $email_content;

            if ($mailer->send()) {
                echo '<h3>Your Message is Submitted Successfully!</h3>';
            } else {
                echo '<p>We are sorry, but the message failed.</p>';
            }
        } catch (Throwable $e) {
            error_log('Theme7 contact SMTP send failed: ' . $e->getMessage());
            echo '<p>We are sorry, but the message failed.</p>';
        }
    }
     
} else {
    echo '<p>Something went wrong</p>';
}
 
?>

<br />
<input type="text" class="form-control" name="name1" placeholder="Your Name">
<br />
<input type="email" class="form-control" name="email" placeholder="Your E-mail">
<br />
<input type="text" class="form-control" name="phone1" placeholder="Your Telephone">
<br />
<textarea name="message" class="form-control" cols="30" rows="10" placeholder="Your Message"></textarea>
<br />
<input type="submit" class="btn credit-btn mt-30" name='submit' value="Send Mail" />
<br />
</form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ##### Contact Area End ##### -->

    <!-- ##### Newsletter Area Start ###### -->
    <section class="newsletter-area section-padding-100 bg-img" style="background-image: url(img/bg-img/6.jpg);">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-lg-8">
                    <div class="nl-content text-center">

                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ##### Newsletter Area End ###### -->

    <!-- ##### Footer Area Start ##### -->


 <footer class="footer-area section-padding-100-0">
        <div class="container">
            <div class="row">

                <!-- Single Footer Widget -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="single-footer-widget mb-100">
                        <h5 class="widget-title">Quick Links</h5>
                        <!-- Nav -->
                        <nav>
                            <ul>
                                <li><a href="ourcareers.php">Careers</a></li>
                                <li><a href="pbccards.php">Credit Cards</a></li>
                                <li><a href="pbloans.php">Personal Loans</a></li> 
                            </ul>
                        </nav>
                    </div>
                </div>

                <!-- Single Footer Widget -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="single-footer-widget mb-100">
                        <h5 class="widget-title">&nbsp;</h5>
                        <!-- Nav -->
                        <nav>
                            <ul>
                                <li><a href="pbcurrent.php">Current Accounts</a></li>
                                <li><a href="pbsavings.php">Savings Accounts</a></li>
                                <li><a href="pbinsurance.php">Personal Insurance</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>

                <!-- Single Footer Widget -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="single-footer-widget mb-100">
                        <h5 class="widget-title">&nbsp;</h5>
                        <!-- Nav -->
                        <nav>
                            <ul>
                                <li><a href="pbmortgages.php">Mortgages</a></li>
                                <li><a href="faqs.php">FAQs</a></li>
                                <li><a href="contact.php">Contact Us</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Copywrite Area -->
        <div class="copywrite-area">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="copywrite-content d-flex flex-wrap justify-content-between align-items-center">
                            <!-- Footer Logo -->
                            <a href="index.php" class="footer-logo"><img src="<?php echo $logo_url ?: ($url . '/admin/assets/images/logo/' . $image); ?>" alt=""width="250"></a>

                            <!-- Copywrite Text -->
                            <p class="copywrite-text"><a href="#"><!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
Copyright &copy;2005 - <script>document.write(new Date().getFullYear());</script> All rights reserved.</a>
<!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. --></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- ##### Footer Area Start ##### -->

    <!-- ##### All Javascript Script ##### -->
    <!-- jQuery-2.2.4 js -->
    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <!-- Popper js -->
    <script src="js/bootstrap/popper.min.js"></script>
    <!-- Bootstrap js -->
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <!-- All Plugins js -->
    <script src="js/plugins/plugins.js"></script>
    <!-- Active js -->
    <script src="js/active.js"></script>
<?php echo $livechat; ?>
  </body>

 
</html>
 