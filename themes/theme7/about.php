<?php
include('session.php');
?>
<?php session_start(); ?>

  
<!DOCTYPE html>
<html lang="en">
 <?php require_once __DIR__ . '/bootstrap.php'; ?>
<head><meta http-equiv="Content-Type" content="text/html; charset=gb18030">
    
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- The above 4 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <!-- Title -->
    <title><?php echo $name; ?> - FAQs</title>

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
                            <!-- Nav End --> <!-- Nav End -->
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
    <section class="breadcrumb-area bg-img bg-overlay jarallax" style="background-image: url(img/bg-img/13.jpg);">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-12">
                    <div class="breadcrumb-content">
                        <h2>About Us</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">About Us</a></li>
                                <li class="breadcrumb-item active" aria-current="page">About Us</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ##### Breadcrumb Area End ##### -->

    <!-- ##### Elements Area Start ##### -->
    <section class="elements-area section-padding-100-0">
        <div class="container">
            <div class="row">

                <!-- ========== Buttons ========== --><!-- ========== Progress Bars & Accordions ========== -->
          <div class="col-12">
                    <div class="elements-title mb-30">
                        <div class="line"></div>
                        <h2>About Us</h2>
                    </div>
                </div>
 <!-- ##### Accordians ##### -->
                <div class="col-12 col-lg-4">
                    <div class="accordions mb-100" id="accordion" role="tablist" aria-multiselectable="true">
                         
                     <img src="img/bg-img/2.jpg" alt="">
                    </div>
                                              
                </div>
                 <!-- ##### Tabs ##### -->
                <div class="col-12 col-lg-8">
                    <div class="credit-tabs-content">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab--1" data-toggle="tab" href="#tab1" role="tab" aria-controls="tab1" aria-selected="false">Overview</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab--2" data-toggle="tab" href="#tab2" role="tab" aria-controls="tab2" aria-selected="false">Vision and Mission</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " id="tab--3" data-toggle="tab" href="#tab3" role="tab" aria-controls="tab3" aria-selected="true">
                                Our Team</a>
                            </li>
                        </ul>

                        <div class="tab-content mb-100" id="myTabContent">
                            <div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab--1">
                                <div class="credit-tab-content">
                                    <!-- Tab Text -->
                                    
                                        <p> <?php echo $name; ?> is one of the most well-respected corporate banks in the UAE, As a strategy of the National Bank of Fujairah for an all-inclusive banking service in the UAE, <?php echo $name; ?> is renowned for its award-winning services, unrivalled understanding of local markets and unwavering commitment to its customers. As the UAE’s bank of choice for over 30 years, it just makes good business sense to bank with <?php echo $name; ?>.</p>
                                        <p>By providing world class banking & trade finance services in the gulf region <?php echo $name; ?> strives to achieve its vision of becoming an innovative and preferred financial partner for all banking services in the region.</p>
                                     
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab--2">
                                <div class="credit-tab-content">
                                    <!-- Tab Text -->
                                    <div class="credit-tab-text">
                                       <h6>VISION</h6> <p>
To become an innovative and preferred financial partner for all banking services in the region.
</p><br>
 <h6>MISSION</h6><ul><li>
Become the first choice bank for customers with best-in-class products and services.</li>
<li>Be an employer of choice to attract, develop and retain high profile employees.</li>
<li>Benefit our investors with sustainable Return on Investment (ROI) and continuous success.</li>
<li>Contribute to society through active participation in the regional economic development initiatives.</li>
</ul><br>
<h6>VALUES</h6>
<ul>
<li>Customer Focus</li>
<li>Organization Commitment</li>
<li>Change Orientation</li>
<li>Quality Focus</li>
<li>Team Spirit</li></ul>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade " id="tab3" role="tabpanel" aria-labelledby="tab--3">
                                <div class="credit-tab-content">
                                    <!-- Tab Text -->
                                    <div class="credit-tab-text">
                                     <div class="container">
            <div class="row align-items-end">  
                                       
                                       <div class="col- col-sm-4 col-lg-4">
                    <div class="single-features-area mb-100 " data-wow-delay="300ms">
                        <img height="30px" src="img/bg-img/a4.png" alt="">
                        <h6>Maiko Jahwen</h6>
                        <p>Head Trust Management</p>
                    </div>
                </div>
                <div class="col- col-sm-4 col-lg-4">
                    <div class="single-features-area mb-100" data-wow-delay="500ms">
                        <img height="30px" src="img/bg-img/a2.png" alt="">
                        <h6>Hakeem Habib</h6>
                        <p>Director Operations</p>
                    </div>
                </div>
                <div class="col- col-sm-4 col-lg-4">
                    <div class="single-features-area mb-100 " data-wow-delay="300ms">
                        <img height="30px" src="img/bg-img/a3.png" alt="">
                        <h6>Stephen Herbert</h6>
                        <p>Head Foreign Accounts</p>
                    </div>
                </div>
                <div class="col- col-sm-4 col-lg-4">
                    <div class="single-features-area mb-100 " data-wow-delay="300ms">
                        <img height="30px" src="img/bg-img/a6.png" alt="">
                        <h6>Fatima Fouad-Ahmed </h6>
                        <p>Head Human Resource</p>
                    </div>
                </div>
                <div class="col- col-sm-4 col-lg-4">
                    <div class="single-features-area mb-100 " data-wow-delay="300ms">
                        <img height="30px" src="img/bg-img/a5.png" alt="">
                        <h6>Mummar Rayel</h6>
                        <p>Director</p>
                    </div>
                </div>
                <div class="col- col-sm-4 col-lg-4">
                    <div class="single-features-area mb-100" data-wow-delay="500ms">
                        <img height="30px" src="img/bg-img/a1.png" alt="">
                        <h6>Omar Hassan</h6>
                        <p>Branch Head</p>
                    </div>
                </div>
                </div>
                </div>
                                       
                                       
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> </div>
                 
               
   
                <!-- ========== Icon Boxes ========== -->

                <div class="col-12">
              <div class="row">
                    <!-- Single Service Area --><!-- Single Service Area --><!-- Single Service Area --></div>
                </div>

            <!-- ========== Web Icons ========== --></div>
        </div>
    </section>
    <!-- ##### Elements Area End ##### -->

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
 