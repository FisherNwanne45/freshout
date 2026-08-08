<?php
include('session.php');
?>
<?php session_start(); ?>

  
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
    <title><?php echo $name; ?> - Business Banking - Foreign Exchange Risk Management</title>

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
                            <!-- Nav End -->
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
    <section class="breadcrumb-area bg-img bg-overlay jarallax" style="background-image: url(img/bg-img/1a.jpg);">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-12">
                    <div class="breadcrumb-content">
                        <h2>Business Banking</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Overview</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Business Banking</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ##### Breadcrumb Area End ##### -->

    <!-- ##### Post Details Area Start ##### -->
    <section class="post-news-area section-padding-100-0">
        <div class="container">
            <div class="row">
                <!-- Post Details Content Area -->
                <div class="col-12 col-lg-8">
                  <div class="post-details-content mb-100">
                        <img src="img/bg-img/22bbdft.jpg" alt="">
                    <div>
                          <p>Protect your business abroad<br>
                          Guard against the risks of currency fluctuation. Foreign Exchange Risk Management Solutions from the <?php echo $name; ?> can help minimise the dangers associated with overseas trade. These options help protect against exposure to exchange rate fluctuations and allow better management of your business cash flow.</p>
                          <p>Flexible strategies to help you plan and project costs<br>
                            Competitive market exchange rates<br>
                            Available for one-off or regular transactions</p>
                          <p><strong>More Benefits</strong></p>
                          <p>Personal attention - dedicated pro-active relationship management<br>
                            Protection - against adverse movements<br>
                            Certainty - exchange rate can be locked in for future delivery, helping to assist budgeting</p>
                          <p><strong>Further Features</strong></p>
                          <p>Available for Spot, Forward Contracts, Option-Dated Forward Contracts or FX Swap transactions<br>
                            Competitive market exchange rates<br>
                            Dedicated experts looking after the exchange risk with you<br>
                            Although normal transaction charges apply there are no additional costs for the service<br>
                            Electronic dealing available via our web-based system</p>
                          <p><strong>Complementary Solutions</strong></p>
                          <p>Currency Risk Management<br>
                            Foreign Currency Current Account</p>
                          <p>Important Information</p>
                          <p>Rates available for up to two years (longer upon request)<br>
                            Credit facilities may be required for this service<br>
                            Normal transaction charges apply, please speak to your Treasury Specialist for full details<br>
                            Full terms and conditions are available on request</p>
                          <p>CHANGES IN THE EXCHANGE RATE MAY INCREASE THE EURO EQUIVALENT OF YOUR DEBT.</p>
                    </div>
</div>

                    <!-- Comment Area Start -->
                    <div class="comment_area clearfix mb-100">
                        <h4 class="mb-50"></h4>
							 <div class="comment-content d-flex">
                                    <!-- Comment Author -->
                                    <div class="comment-meta">
                                        <div class="d-flex">
                                        </div>
                                        </p>
                                    </div>
                                </div>
                            </li>
                        </ol>
                    </div>

                    <div class="post-a-comment-area mb-100 clearfix">
                        <h4 class="mb-50"></h4>

                        <!-- Reply Form -->
                        <div class="contact-form-area">
                            
                        </div>
                    </div>
                </div>

                <!-- Sidebar Widget -->
                <div class="col-12 col-sm-9 col-md-6 col-lg-4">
                    <div class="sidebar-area mb-100">

                        <!-- Single Sidebar Widget -->
                        <!-- Single Sidebar Widget -->
                  <div class="single-widget-area cata-widget">
<div class="widget-heading">
                                <div class="line"></div>
                                <h4>Quick Links</h4>

                            <ul>
                                <li><a href="bbbcards.php">Bank Cards</a></li>
                                <li><a href="bbdeposits.php">Deposit</a></li>
                                <li><a href="bbforeigndrafts.php">Foreign Drafts</a></li>
                                <li><a href="bbintchecking.php">Interest Checking</a></li>
                                <li><a href="bbebanking.php">Electronic Banking</a></li>
                                <li><a href="bbinvestbenefit.php">Investment/ Benefit Care Taking</a></li>
                                <li><a href="bbmmaccounts.php">Money Market Account</a></li>
                                <li><a href="bbsmallbiz.php">Small Business Checking</a></li>
                                <li><a href="bbbizcashmgt.php">Business Cash Management</a></li>
                                <li><a href="bbcurrencyriskmgt.php">Currency Risk Management</a></li>
                                <li><a href="bbforeignccdept.php">Foreign Currency Call Deposit</a></li>
                                <li><a href="bbforexriskmgt.php" class="text-warning">Foreign Exchange Risk Management</a></li>
                            </ul>
                        </div>

                        <!-- Single Sidebar Widget -->
                        
                                    </li>
                                </ul>

                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade" id="tab1" role="tabpanel" aria-labelledby="tab--1">
                                        <div class="credit-tab-content">
                                            <!-- Single News Area -->
                                            <div class="single-news-area d-flex align-items-center">
                                                <div class="news-thumbnail">
                                                    <img src="img/bg-img/10.jpg" alt="">
                                                </div>
                                                <div class="news-content">
                                                    <span>July 18, 2018</span>
                                                    <a href="#">How to get the best loan online</a>
                                                    <div class="news-meta">
                                                        <a href="#" class="post-author"><img src="img/core-img/pencil.png" alt=""> Jane Smith</a>
                                                        <a href="#" class="post-date"><img src="img/core-img/calendar.png" alt=""> April 26</a>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Single News Area -->
                                            <div class="single-news-area d-flex align-items-center">
                                                <div class="news-thumbnail">
                                                    <img src="img/bg-img/11.jpg" alt="">
                                                </div>
                                                <div class="news-content">
                                                    <span>July 18, 2018</span>
                                                    <a href="#">A new way to finance your dream home</a>
                                                    <div class="news-meta">
                                                        <a href="#" class="post-author"><img src="img/core-img/pencil.png" alt=""> Jane Smith</a>
                                                        <a href="#" class="post-date"><img src="img/core-img/calendar.png" alt=""> April 26</a>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Single News Area -->
                                            <div class="single-news-area d-flex align-items-center">
                                                <div class="news-thumbnail">
                                                    <img src="img/bg-img/12.jpg" alt="">
                                                </div>
                                                <div class="news-content">
                                                    <span>July 18, 2018</span>
                                                    <a href="#">10 tips to get the best loan for you</a>
                                                    <div class="news-meta">
                                                        <a href="#" class="post-author"><img src="img/core-img/pencil.png" alt=""> Jane Smith</a>
                                                        <a href="#" class="post-date"><img src="img/core-img/calendar.png" alt=""> April 26</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade show active" id="tab2" role="tabpanel" aria-labelledby="tab--2">
                                        <div class="credit-tab-content">
                                            <!-- Single News Area -->
                                            <div class="single-news-area d-flex align-items-center">
                                                <div class="news-thumbnail">
                                                    <img src="img/bg-img/10.jpg" alt="">
                                                </div>
                                                <div class="news-content">
                                                    <span>July 18, 2018</span>
                                                    <a href="#">How to get the best loan online</a>
                                                    <div class="news-meta">
                                                        <a href="#" class="post-author"><img src="img/core-img/pencil.png" alt=""> Jane Smith</a>
                                                        <a href="#" class="post-date"><img src="img/core-img/calendar.png" alt=""> April 26</a>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Single News Area -->
                                            <div class="single-news-area d-flex align-items-center">
                                                <div class="news-thumbnail">
                                                    <img src="img/bg-img/11.jpg" alt="">
                                                </div>
                                                <div class="news-content">
                                                    <span>July 18, 2018</span>
                                                    <a href="#">A new way to finance your dream home</a>
                                                    <div class="news-meta">
                                                        <a href="#" class="post-author"><img src="img/core-img/pencil.png" alt=""> Jane Smith</a>
                                                        <a href="#" class="post-date"><img src="img/core-img/calendar.png" alt=""> April 26</a>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Single News Area -->
                                            <div class="single-news-area d-flex align-items-center">
                                                <div class="news-thumbnail">
                                                    <img src="img/bg-img/12.jpg" alt="">
                                                </div>
                                                <div class="news-content">
                                                    <span>July 18, 2018</span>
                                                    <a href="#">10 tips to get the best loan for you</a>
                                                    <div class="news-meta">
                                                        <a href="#" class="post-author"><img src="img/core-img/pencil.png" alt=""> Jane Smith</a>
                                                        <a href="#" class="post-date"><img src="img/core-img/calendar.png" alt=""> April 26</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="tab3" role="tabpanel" aria-labelledby="tab--3">
                                        <div class="credit-tab-content">
                                            <!-- Single News Area -->
                                            <div class="single-news-area d-flex align-items-center">
                                                <div class="news-thumbnail">
                                                    <img src="img/bg-img/10.jpg" alt="">
                                                </div>
                                                <div class="news-content">
                                                    <span>July 18, 2018</span>
                                                    <a href="#">How to get the best loan online</a>
                                                    <div class="news-meta">
                                                        <a href="#" class="post-author"><img src="img/core-img/pencil.png" alt=""> Jane Smith</a>
                                                        <a href="#" class="post-date"><img src="img/core-img/calendar.png" alt=""> April 26</a>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Single News Area -->
                                            <div class="single-news-area d-flex align-items-center">
                                                <div class="news-thumbnail">
                                                    <img src="img/bg-img/11.jpg" alt="">
                                                </div>
                                                <div class="news-content">
                                                    <span>July 18, 2018</span>
                                                    <a href="#">A new way to finance your dream home</a>
                                                    <div class="news-meta">
                                                        <a href="#" class="post-author"><img src="img/core-img/pencil.png" alt=""> Jane Smith</a>
                                                        <a href="#" class="post-date"><img src="img/core-img/calendar.png" alt=""> April 26</a>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Single News Area -->
                                            <div class="single-news-area d-flex align-items-center">
                                                <div class="news-thumbnail">
                                                    <img src="img/bg-img/12.jpg" alt="">
                                                </div>
                                                <div class="news-content">
                                                    <span>July 18, 2018</span>
                                                    <a href="#">10 tips to get the best loan for you</a>
                                                    <div class="news-meta">
                                                        <a href="#" class="post-author"><img src="img/core-img/pencil.png" alt=""> Jane Smith</a>
                                                        <a href="#" class="post-date"><img src="img/core-img/calendar.png" alt=""> April 26</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ##### Post Details Area End ##### -->

    <!-- ##### Newsletter Area Start ###### -->
    <section class="newsletter-area section-padding-100 bg-img" style="background-image: url(img/bg-img/6.jpg);">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-lg-8">
                    <div class="nl-content text-center">
                        <h2></h2>
                        </p>
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
 