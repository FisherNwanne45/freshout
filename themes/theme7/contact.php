<?php
include('session.php');
?>
<?php session_start(); ?>
<?php
require_once __DIR__ . '/bootstrap.php';

if (empty($_SESSION['theme7_contact_csrf']) || !is_string($_SESSION['theme7_contact_csrf'])) {
    try {
        $_SESSION['theme7_contact_csrf'] = bin2hex(random_bytes(32));
    } catch (Throwable $e) {
        $_SESSION['theme7_contact_csrf'] = hash('sha256', uniqid('theme7_contact_', true));
    }
}

$_SESSION['theme7_contact_form_loaded_at'] = time();

$theme7ContactFeedback = $_SESSION['theme7_contact_feedback'] ?? null;
unset($_SESSION['theme7_contact_feedback']);

$theme7ContactOldInput = $_SESSION['theme7_contact_old_input'] ?? [];
unset($_SESSION['theme7_contact_old_input']);

$contactOldName = htmlspecialchars((string)($theme7ContactOldInput['name1'] ?? ''), ENT_QUOTES, 'UTF-8');
$contactOldEmail = htmlspecialchars((string)($theme7ContactOldInput['email'] ?? ''), ENT_QUOTES, 'UTF-8');
$contactOldPhone = htmlspecialchars((string)($theme7ContactOldInput['phone1'] ?? ''), ENT_QUOTES, 'UTF-8');
$contactOldMessage = htmlspecialchars((string)($theme7ContactOldInput['message'] ?? ''), ENT_QUOTES, 'UTF-8');
?>

  
<!DOCTYPE html>
<html lang="en">
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
                            <!-- Nav End -->  <!-- Nav End -->
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
    <section class="contact-area">
     

                
        <!-- ##### Google Maps ##### -->
        <div class="map-area">
            
     
            <!-- Contact Area -->
            <div class="contact---area">
                <div class="container">
                    <div class="row">
                         <!-- Single Contact Area -->
                 
                    <div class="col-12 col-lg-6">
                    <div class="single-contact-area mb-100">
                        <div class="contact--area contact-page">
                            <!-- Contact Content -->
                            <div class="contact-content">
                                 <h5>Get in touch</h5>
                                 <p><strong>Reach out to us or fill the contact us form to send us email.</strong></p> <br>
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
                        <div class="col-12 col-lg-6">
                            <!-- Contact Area -->
                            <div class="contact-form-area contact-page">
                                <?php if (is_array($theme7ContactFeedback) && !empty($theme7ContactFeedback['message'])): ?>
                                <div class="alert <?php echo (($theme7ContactFeedback['type'] ?? '') === 'success') ? 'alert-success' : 'alert-danger'; ?>" role="alert" style="margin-bottom:16px;">
                                    <?php echo htmlspecialchars((string)$theme7ContactFeedback['message'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <?php endif; ?>
                               
                                
                                 
                                <form method="post" action="<?php echo ($appBase === '' ? '' : $appBase); ?>/themes/theme7/submit.php#form">

<!-- DO NOT change ANY of the php sections -->

<input type="hidden" name="contact_token" value="<?php echo htmlspecialchars((string)$_SESSION['theme7_contact_csrf'], ENT_QUOTES, 'UTF-8'); ?>">

<input type="hidden" name="form_started_at" value="<?php echo (int)($_SESSION['theme7_contact_form_loaded_at'] ?? time()); ?>">

<div class="contact-bot-field" aria-hidden="true">
<label for="theme7-contact-website">Leave this field blank</label>
<input id="theme7-contact-website" type="text" name="website" tabindex="-1" autocomplete="off" value="">
</div>

 
<input type="text" class="form-control" name="name1" placeholder="Your Name" value="<?php echo $contactOldName; ?>">
 
<input type="email" class="form-control" name="email" placeholder="Your E-mail" value="<?php echo $contactOldEmail; ?>">
 
<input type="text" class="form-control" name="phone1" placeholder="Your Telephone" value="<?php echo $contactOldPhone; ?>">
 
<textarea name="message" class="form-control" cols="30" rows="10" placeholder="Your Message"><?php echo $contactOldMessage; ?></textarea>
 
<input type="submit" class="btn credit-btn mt-30" name='submit' value="Send Mail" />
 
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
                <div class="col-12 col-sm-12 col-lg-10">
                    <div class="nl-content text-center">
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3610.457170502466!2d55.268120815009354!3d25.18780068390057!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e5f682d263316db%3A0xce83e1d4eee0c20b!2sBlue%20Bay%20Tower!5e0!3m2!1sen!2sua!4v1639328991414!5m2!1sen!2sua" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
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
 