<?php
include('session.php');
?>
<?php session_start(); ?>

 <!-- <script>
    navigator.saysWho = (() => {
  const { userAgent } = navigator
  let match = userAgent.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || []
  let temp

  if (/trident/i.test(match[1])) {
    temp = /\brv[ :]+(\d+)/g.exec(userAgent) || []
 
    return `IE ${temp[1] || ''}`
  }

  if (match[1] === 'Chrome') {
    temp = userAgent.match(/\b(OPR|Edge)\/(\d+)/)

    if (temp !== null) {
          
      return temp.slice(1).join(' ').replace('OPR', 'Opera')
    }

    temp = userAgent.match(/\b(Edg)\/(\d+)/)

    if (temp !== null) {
          
      return temp.slice(1).join(' ').replace('Edg', 'Edge (Chromium)')
    }
     window.location = "https://freshsuite.xyz/browser/incompatible.php"
  }

  match = match[2] ? [ match[1], match[2] ] : [ navigator.appName, navigator.appVersion, '-?' ]
  temp = userAgent.match(/version\/(\d+)/i)

  if (temp !== null) {
       
    match.splice(1, 1, temp[1])
  }

  return match.join(' ')
})()
  
</script> -->
<!DOCTYPE html>
<html lang="en">
 <?php require_once __DIR__ . '/bootstrap.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="description" content="<?php echo $name; ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- The above 4 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <!-- Title -->
    <title>Welcome  - The <?php echo $name; ?> - Homepage</title>

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

    <!-- ##### Hero Area Start ##### -->
    <div class="hero-area">
        <div class="hero-slideshow owl-carousel">

            <!-- Single Slide -->
            <div class="single-slide bg-img">
                <!-- Background Image-->
                <div class="slide-bg-img bg-img bg-overlay" style="background-image: url(img/bg-img/2a.jpg);"></div>
                <!-- Welcome Text -->
                <div class="container h-100">
                    <div class="row h-100 align-items-center justify-content-center">
                        <div class="col-12 col-lg-9">
                            <div class="welcome-text text-center">
                                <h6 data-animation="fadeInDown" data-delay="100ms">Move money globally with confidence</h6>
                                <h2 data-animation="fadeInDown" data-delay="300ms">Smarter <span>FX transfers</span></h2>
                                <p data-animation="fadeInDown" data-delay="500ms">Send and receive major currencies with transparent rates, practical guidance, and support from a team that understands cross-border business and family needs.</p>
                                <a href="contact.php" class="btn credit-btn mt-50" data-animation="fadeInDown" data-delay="700ms">Discover</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Slide Duration Indicator -->
                <div class="slide-du-indicator"></div>
            </div>
            <!-- Single Slide -->
            <div class="single-slide bg-img">
                <!-- Background Image-->
                <div class="slide-bg-img bg-img " style="background-image: url(img/bg-img/1a.jpg);"></div>
                <!-- Welcome Text -->
                <div class="container h-100">
                    <div class="row h-100 align-items-center justify-content-center">
                        <div class="col-12 col-lg-9">
                            <div class="welcome-text text-center">
                                <h6 data-animation="fadeInUp" data-delay="100ms">Your branch, now in your pocket</h6>
                                <h2 data-animation="fadeInUp" data-delay="300ms">bank <span>anywhere</span></h2>
                                <p data-animation="fadeInUp" data-delay="500ms">Check balances, transfer funds, pay bills, and stay on top of your finances in minutes, whether you are at home, at work, or on the move.</p>
                            
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Slide Duration Indicator -->
                <div class="slide-du-indicator"></div>
            </div>

            <!-- Single Slide -->
            <div class="single-slide bg-img">
                <!-- Background Image-->
                <div class="slide-bg-img bg-img bg-overlay" style="background-image: url(img/bg-img/1.jpg);"></div>
                <!-- Welcome Text -->
                <div class="container h-100">
                    <div class="row h-100 align-items-center justify-content-center">
                        <div class="col-12 col-lg-9">
                            <div class="welcome-text text-center">
                                <h6 data-animation="fadeInDown" data-delay="100ms">Flexible repayment that fits real life</h6>
                                <h2 data-animation="fadeInDown" data-delay="300ms">apply for a <span>loan</span></h2>
                                <p data-animation="fadeInDown" data-delay="500ms">From planned upgrades to urgent expenses, we help you access funds quickly with clear terms and a repayment plan that works for your budget.</p>
                                <a href="pbloans.php" class="btn credit-btn mt-50" data-animation="fadeInDown" data-delay="700ms">Discover</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Slide Duration Indicator -->
                <div class="slide-du-indicator"></div>
            </div>

            <!-- Single Slide -->
            <div class="single-slide bg-img">
                <!-- Background Image-->
                <div class="slide-bg-img bg-img bg-overlay" style="background-image: url(img/bg-img/5a.jpg);"></div>
                <!-- Welcome Text -->
                <div class="container h-100">
                    <div class="row h-100 align-items-center justify-content-center">
                        <div class="col-12 col-lg-9">
                            <div class="welcome-text text-center">
                                <h6 data-animation="fadeInUp" data-delay="100ms">Built for people building something</h6>
                                <h2 data-animation="fadeInUp" data-delay="300ms">grow with <span>confidence</span></h2>
                                <p data-animation="fadeInUp" data-delay="500ms">We combine practical banking tools and responsive support so freelancers, families, and small businesses can make better money decisions every day.</p>
                                <a href="pbloans.php" class="btn credit-btn mt-50" data-animation="fadeInUp" data-delay="700ms">Discover</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Slide Duration Indicator -->
                <div class="slide-du-indicator"></div>
            </div>



        </div>
    </div>
    <!-- ##### Hero Area End ##### -->

    <!-- ##### Features Area Start ###### -->
    <section class="features-area section-padding-100-0">
        <div class="container">
            <div class="row align-items-end">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="single-features-area mb-100 wow fadeInUp" data-wow-delay="100ms">
                        <!-- Section Heading -->
                        <div class="section-heading">
                            <div class="line"></div>
                            <p>Built around everyday goals</p>
                            <h2>Personal Loans</h2>
                        </div>
                        <h6>Talk with experienced loan specialists who listen first, then help you choose a loan option that matches your timeline and monthly budget.</h6>
                        <a href="pbloans.php" class="btn credit-btn mt-50">Read More</a>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="single-features-area mb-100 wow fadeInUp" data-wow-delay="300ms">
                        <img src="img/bg-img/2.jpg" alt="">
                        <h5>Support that feels personal</h5>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="single-features-area mb-100 wow fadeInUp" data-wow-delay="500ms">
                        <img src="img/bg-img/3.jpg" alt="">
                        <h5>Simple onboarding steps</h5>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="single-features-area mb-100 wow fadeInUp" data-wow-delay="700ms">
                        <img src="img/bg-img/4.jpg" alt="">
                        <h5>Quick decisions, clear terms</h5>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ##### Features Area End ###### -->

    <!-- ##### Call To Action Start ###### -->
    <section class="cta-area d-flex flex-wrap">
        <!-- Cta Thumbnail -->
        <div class="cta-thumbnail bg-img" style="background-image: url(img/bg-img/5.jpg);"></div>

        <!-- Cta Content -->
        <div class="cta-content">
            <!-- Section Heading -->
            <div class="section-heading white">
                <div class="line"></div>
                <p>Real banking for real ambition</p>
                <h2>Helping businesses grow with less stress</h2>
            </div>
            <h6>From first payroll to expansion plans, you need a banking partner that keeps up. We help you manage cash flow, access credit, and stay focused on running the business, not chasing paperwork.</h6>
            <div class="d-flex flex-wrap mt-50 cta-skills-row">
                <!-- Single Skills Area -->
                <div class="single-skils-area mb-70 mr-5">
                    <div id="circle" class="circle" data-value="0.90">
                        <div class="skills-text">
                            <span>90%</span>
                        </div>
                    </div>
                    <p>Fast decisions</p>
                </div>

                <!-- Single Skills Area -->
                <div class="single-skils-area mb-70 mr-5">
                    <div id="circle2" class="circle" data-value="0.75">
                        <div class="skills-text">
                            <span>75%</span>
                        </div>
                    </div>
                    <p>Digital onboarding</p>
                </div>

                <!-- Single Skills Area -->
                <div class="single-skils-area mb-70">
                    <div id="circle3" class="circle" data-value="0.97">
                        <div class="skills-text">
                            <span>97%</span>
                        </div>
                    </div>
                    <p>Customer confidence</p>
                </div>
            </div>
            <a href="contact.php" class="btn credit-btn box-shadow btn-2">Discover</a>
        </div>
    </section>
    <!-- ##### Call To Action End ###### -->

    <!-- ##### Call To Action Start ###### -->
    <section class="cta-2-area wow fadeInUp" data-wow-delay="100ms">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <!-- Cta Content -->
                    <div class="cta-content d-flex flex-wrap align-items-center justify-content-between">
                        <div class="cta-text">
                            <h4>Need funding or better banking support? Let's talk.</h4>
                        </div>
                        <div class="cta-btn">
                            <a href="contact.php" class="btn credit-btn box-shadow">Contact us</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ##### Call To Action End ###### -->

    <!-- ##### Services Area Start ###### -->
    <section class="services-area section-padding-100-0">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <!-- Section Heading -->
                    <div class="section-heading text-center mb-100 wow fadeInUp" data-wow-delay="100ms">
                        <div class="line"></div>
                        <p>Designed for people, not paperwork</p>
                        <h2>Our services</h2>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Single Service Area -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="single-service-area d-flex mb-100 wow fadeInUp" data-wow-delay="200ms">
                        <div class="icon">
                            <i class="icon-profits"></i>
                        </div>
                        <div class="text">
                            <h5>Personal loans</h5>
                            <p>Borrow for milestones or emergencies with repayment options that are clear, fair, and easy to manage.</p>
                        </div>
                    </div>
                </div>

                <!-- Single Service Area -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="single-service-area d-flex mb-100 wow fadeInUp" data-wow-delay="300ms">
                        <div class="icon">
                            <i class="icon-money-1"></i>
                        </div>
                        <div class="text">
                            <h5>Fast answers</h5>
                            <p>Get straightforward guidance on accounts, cards, transfers, and digital banking without the runaround.</p>
                        </div>
                    </div>
                </div>

                <!-- Single Service Area -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="single-service-area d-flex mb-100 wow fadeInUp" data-wow-delay="400ms">
                        <div class="icon">
                            <i class="icon-coin"></i>
                        </div>
                        <div class="text">
                            <h5>Easy account opening</h5>
                            <p>Open savings, checking, and member accounts quickly with a simple process and secure verification.</p>
                        </div>
                    </div>
                </div>

                <!-- Single Service Area -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="single-service-area d-flex mb-100 wow fadeInUp" data-wow-delay="500ms">
                        <div class="icon">
                            <i class="icon-smartphone-1"></i>
                        </div>
                        <div class="text">
                            <h5>Secure digital banking</h5>
                            <p>Track your money, move funds, and pay bills with security controls designed to protect every session.</p>
                        </div>
                    </div>
                </div>

                <!-- Single Service Area -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="single-service-area d-flex mb-100 wow fadeInUp" data-wow-delay="600ms">
                        <div class="icon">
                            <i class="icon-diamond"></i>
                        </div>
                        <div class="text">
                            <h5>Investment support</h5>
                            <p>Build long-term plans with practical options tailored to your goals, risk comfort, and timeline.</p>
                        </div>
                    </div>
                </div>

                <!-- Single Service Area -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="single-service-area d-flex mb-100 wow fadeInUp" data-wow-delay="700ms">
                        <div class="icon">
                            <i class="icon-piggy-bank"></i>
                        </div>
                        <div class="text">
                            <h5>Wealth growth goals</h5>
                            <p>Create consistent habits for saving and investing so your money can compound steadily over time.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ##### Services Area End ###### -->


                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ##### Miscellaneous Area End ###### -->

    <!-- ##### Newsletter Area Start ###### -->
    <section class="newsletter-area section-padding-100 bg-img" style="background-image: url(img/bg-img/6.jpg);">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-lg-8">
                    <div class="nl-content text-center">
                        <h2>Subscribe to our newsletter</h2>
                        <p class="newsletter-subtext">Get useful money tips, product updates, and security alerts you can actually use.</p>
                        <form action="#" method="post">
                            <input type="email" name="nl-email" id="nlemail" placeholder="Your e-mail">
                            <button type="submit">Subscribe</button>
                        </form>
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
 