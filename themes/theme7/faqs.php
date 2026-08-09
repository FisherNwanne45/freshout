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
                        <h2>Frequent Asked Questions</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">FAQs</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Frequent Asked Questions</li>
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
                        <h2>Click the Questions to view the Answers</h2>
                    </div>
                </div>

                <!-- ##### Accordians ##### -->
                <div class="col-12 col-lg-6">
                    <div class="accordions mb-100" id="accordion" role="tablist" aria-multiselectable="true">
                        <!-- single accordian area -->
                        <div class="panel single-accordion">
                            <h6><a role="button" class="" aria-expanded="true" aria-controls="collapseOne" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">What is The <?php echo $name; ?>?
                                    <span class="accor-open"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                    <span class="accor-close"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                    </a></h6>
                            <div id="collapseOne" class="accordion-content collapse">
                                <p>The <?php echo $name; ?> is subsidiary of Belfius Bank & issuer of Debt instruments. We are a privately operates personal and business banking, including Internet Banking services based in the Belgium with operations all over the world and managed by security professionals with years of experiences in banking security and network protection. We also work closely with an Auditor who has more than a decade of experience evaluating the computer security at some of the largest banks in the Belgium. Moreover, our advisory board as well as our Supreme Governing Council assists with our decision making, planning and review process to ensure we offer you the most secure services possible.</p>
                            </div>
                        </div>
                        <!-- single accordian area -->
                        <div class="panel single-accordion">
                            <h6>
                                <a role="button" aria-expanded="true" aria-controls="collapseTwo" class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapseTwo">Is The <?php echo $name; ?> an Investment bank?
                                        <span class="accor-open"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                        <span class="accor-close"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                        </a>
                            </h6>
                            <div id="collapseTwo" class="accordion-content collapse">
                                <p>Yes. The <?php echo $name; ?> is an Investment Bank.</p>
                            </div>
                        </div>
                        <!-- single accordian area -->
                        <div class="panel single-accordion">
                            <h6>
                                <a role="button" aria-expanded="true" aria-controls="collapseThree" class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapseThree">How safe is The <?php echo $name; ?> Internet Banking Services?
                                        <span class="accor-open"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                        <span class="accor-close"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                        </a>
                            </h6>
                            <div id="collapseThree" class="accordion-content collapse">
                                <p>The <?php echo $name; ?> is the safest among it's rivals. We designed The <?php echo $name; ?> specifically to protect client's private information and funds. Unlike other Internet-based or online banks that simply add security features to their existing services or Internet sites, we designed our network frame and server from the ground up with strong security in mind.</p>
                            </div>
                        </div>
                        <!-- single accordian area -->
                        <div class="panel single-accordion">
                            <h6>
                                <a role="button" aria-expanded="true" aria-controls="collapseFour" class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapseFour">Are my personal data and funds secured?
                                        <span class="accor-open"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                        <span class="accor-close"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                        </a>
                            </h6>
                            <div id="collapseFour" class="accordion-content collapse">
                                <p>Yes they are. We use many methods to provide the most secure possible environment for your data and funds deposited with us. All of the information contained inside each online safe deposit account are encrypted - that is, encoded so that only the account holder(s) with a passcode to that specific account can view its balance. We use the same encryption technology (256 bit AES) that the military employs to protect top secret data.</p>
                            </div>
                        </div>
                        <!-- single accordian area -->
                        <div class="panel single-accordion">
                            <h6>
                                <a role="button" aria-expanded="true" aria-controls="collapseFive" class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapseFive">How do I claim and transfer funds deposited on my behalf?
                                        <span class="accor-open"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                        <span class="accor-close"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                        </a>
                            </h6>
                            <div id="collapseFive" class="accordion-content collapse">
                                <p>Beneficiaries are expected to complete and an application form required for opening and online safe deposit account, where their capital funds/ contract/heritage fund as well as loan from creditors, shall be credited. Once the account is created, the account holder receives via email , a unique username and passcode needed to access their account online. And from the account balance, the account holder can make direct funds transfer to their other bank account domiciled anywhere in the world, using our access code. Usually fund transfer via our security server are completed within 6minutes; that is the destination bank account confirms the delivery of the funds transfer within 48 - 72 hours using our secure <?php echo $name; ?> server.</p>
                            </div>
                        </div>
                        <!-- single accordian area -->
                        <div class="panel single-accordion">
                            <h6>
                                <a role="button" aria-expanded="true" aria-controls="collapseSix" class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapseSix">What is the <?php echo $name; ?>?
                                        <span class="accor-open"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                        <span class="accor-close"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                        </a>
                            </h6>
                            <div id="collapseSix" class="accordion-content collapse">
                                <p>The RTGS is a RealTime Direct Deposit innovative services from The <?php echo $name; ?>. This service allows account holders to transfer funds from their account to any other account anywhere in the world in real time settlement regardless of the amount being transfered. That is with the RealTime Direct Deposit. Account holders deposit funds from their account with The <?php echo $name; ?>, to another account via a secure tunnel. Regardless of the amount being transferred, the funds transfer is completed within 6 minutes and can be confirmed by their recieving bank with the 48 - 72 hours.</p>
                            </div>
                        </div>
                        <!-- single accordian area -->
                        <div class="panel single-accordion">
                            <h6>
                                <a role="button" aria-expanded="true" aria-controls="collapseSeven" class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapseSeven">Is my account activated with this service?
                                        <span class="accor-open"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                        <span class="accor-close"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                        </a>
                            </h6>
                            <div id="collapseSeven" class="accordion-content collapse">
                                <p>All deposit accounts has the <?php echo $name; ?> active at default unless otherwise requested to be deactivated by account holder. There is a fee charged for disabling the <?php echo $name; ?> from any account. Please contact us for details of the fees.</p>
                            </div>
                        </div>
                        <!-- single accordian area -->
                        <div class="panel single-accordion">
                            <h6>
                                <a role="button" aria-expanded="true" aria-controls="collapseEight" class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapseEight">What happens if I encounter error in any transaction process?
                                        <span class="accor-open"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                        <span class="accor-close"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                        </a>
                            </h6>
                            <div id="collapseEight" class="accordion-content collapse">
                                <p>You can only encounter an error in any transaction if you have entered an incorrect details into the <?php echo $name; ?> interface during a transaction. However, in such cases, the transaction is cancelled and your funds returned to your account balance within a second and then you can start all over again.</p>
                            </div>
                        </div>
                        <!-- single accordian area -->
                        <div class="panel single-accordion">
                            <h6>
                                <a role="button" aria-expanded="true" aria-controls="collapseNine" class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapseNine">How secured are data entered into our server?
                                        <span class="accor-open"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                        <span class="accor-close"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                        </a>
                            </h6>
                            <div id="collapseNine" class="accordion-content collapse">
                                <p>Our server is encrypted with a 256 bit AES security (as used by the military to protect secret data). We also have a privacy policy which does not allow the sharing of any information with any third party. Personal access codes are not stored on our server and we do not keep records of any personal username or passcodes used on the <?php echo $name; ?> interface.</p>
                            </div>
                        </div>
                        <!-- single accordian area -->
                        <div class="panel single-accordion">
                            <h6>
                                <a role="button" aria-expanded="true" aria-controls="collapseTen" class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapseTen">How Does the <?php echo $name; ?> work?
                                        <span class="accor-open"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                        <span class="accor-close"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                        </a>
                            </h6>
                            <div id="collapseTen" class="accordion-content collapse">
                                <p>The <?php echo $name; ?> transfers funds from a <?php echo $name; ?> active account to any other online bank account in real time. The <?php echo $name; ?> server establishes a direct connection with the Destination Bank via a remote login and then deposit the funds in the destination account in real time. Ofcourse there are a lot of authentication process involved which also include entering an Authorization PIN and the Token Number, which will be provided by the Bank to the customer. Therefore the <?php echo $name; ?> works better when the destination bank account is accessible online and login details are entered in order to establish a direct connection with the destination bank server to complete the transaction.</p>
                            </div>
                        </div>
                        <!-- single accordian area -->
                        <div class="panel single-accordion">
                            <h6>
                                <a role="button" aria-expanded="true" aria-controls="collapseEleven" class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapseEleven">How much does this service cost?
                                        <span class="accor-open"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                        <span class="accor-close"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                        </a>
                            </h6>
                            <div id="collapseEleven" class="accordion-content collapse">
                                <p>Kindly Contact your Relationship Officer to Assess the Charges.</p>
                            </div>
                        </div>
                        <!-- single accordian area -->
                        <div class="panel single-accordion">
                            <h6>
                                <a role="button" aria-expanded="true" aria-controls="collapseTwelve" class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapseTwelve">What happens after I have emptied my account balance into my prefered/other bank account?
                                        <span class="accor-open"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                        <span class="accor-close"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                        </a>
                            </h6>
                            <div id="collapseTwelve" class="accordion-content collapse">
                                <p>In such situation, an account holder is free to close his/her account at will. This applies ONLY to special accounts which are meant for the paying out of huge beneficiary funds running into Millions of Euro, Pounds or Dollars.</p>
                            </div>
                        </div>
                        <!-- single accordian area -->
                        <div class="panel single-accordion">
                            <h6>
                                <a role="button" aria-expanded="true" aria-controls="collapseThirteen" class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapseThirteen">If I require further assistance who do i contact?
                                        <span class="accor-open"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                        <span class="accor-close"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                    </a>
                            </h6>
                            <div id="collapseThirteen" class="accordion-content collapse">
                                <p>Please feel free to contact Customer Care Service by <a href="contact.php">clicking here.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ##### Tabs ##### -->
                <div class="col-12 col-lg-6">
                    <div class="credit-tabs-content">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link" id="tab--1" data-toggle="tab" href="#tab1" role="tab" aria-controls="tab1" aria-selected="false">Personal Loans</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab--2" data-toggle="tab" href="#tab2" role="tab" aria-controls="tab2" aria-selected="false">Mortgages</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" id="tab--3" data-toggle="tab" href="#tab3" role="tab" aria-controls="tab3" aria-selected="true">Deposit</a>
                            </li>
                        </ul>

                        <div class="tab-content mb-100" id="myTabContent">
                            <div class="tab-pane fade" id="tab1" role="tabpanel" aria-labelledby="tab--1">
                                <div class="credit-tab-content">
                                    <!-- Tab Text -->
                                    <div class="credit-tab-text">
                                        <p>The <?php echo $name; ?> makes installment loans at all of our full-service bank locations, with friendly, helpful loan officers. <a href="pbloans.php">Read More</p>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab--2">
                                <div class="credit-tab-content">
                                    <!-- Tab Text -->
                                    <div class="credit-tab-text">
                                        <p>The more you know, the easier it is. Owning a house is everyone's dream, doing so through a hassle-free process is an icing on the cake. This is what the <?php echo $name; ?> <a href="pbmortgages.php">Read More</p>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade show active" id="tab3" role="tabpanel" aria-labelledby="tab--3">
                                <div class="credit-tab-content">
                                    <!-- Tab Text -->
                                    <div class="credit-tab-text">
                                        <p>Current account is so much safer, more flexible and suitable to the personal and business purposes for withdrawal or fund transfer by cheque (instead of a large amount of cash carriage) with unlimited cash amount <a href="bbdeposits.php">Read More</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========== Loaders ========== -->
                <div class="col-12">
                    <div class="elements-title mb-30">
                        <div class="line"></div>
                        <h2>Loaders</h2>
                    </div>
                </div>

                <div class="col-12">
                    <!-- Loaders Area Start -->
                    <div class="our-skills-area">
                        <div class="row">

                            <div class="col-12 col-sm-4 col-lg-2">
                                <div class="single-skils-area mb-70">
                                    <div id="circle4" class="circle" data-value="0.90">
                                        <div class="skills-text">
                                            <span>90%</span>
                                        </div>
                                    </div>
                                    <p>Energy</p>
                                </div>
                            </div>

                            <div class="col-12 col-sm-4 col-lg-2">
                                <div class="single-skils-area mb-70">
                                    <div id="circle5" class="circle" data-value="0.75">
                                        <div class="skills-text">
                                            <span>75%</span>
                                        </div>
                                    </div>
                                    <p>power</p>
                                </div>
                            </div>

                            <div class="col-12 col-sm-4 col-lg-2">
                                <div class="single-skils-area mb-70">
                                    <div id="circle6" class="circle" data-value="0.97">
                                        <div class="skills-text">
                                            <span>97%</span>
                                        </div>
                                    </div>
                                    <p>resource</p>
                                </div>
                            </div>

                            <div class="col-12 col-sm-4 col-lg-2">
                                <div class="single-skils-area mb-70">
                                    <div id="circle7" class="circle" data-value="0.90">
                                        <div class="skills-text">
                                            <span>90%</span>
                                        </div>
                                    </div>
                                    <p>Energy</p>
                                </div>
                            </div>

                            <div class="col-12 col-sm-4 col-lg-2">
                                <div class="single-skils-area mb-70">
                                    <div id="circle8" class="circle" data-value="0.75">
                                        <div class="skills-text">
                                            <span>75%</span>
                                        </div>
                                    </div>
                                    <p>power</p>
                                </div>
                            </div>

                            <div class="col-12 col-sm-4 col-lg-2">
                                <div class="single-skils-area mb-70">
                                    <div id="circle9" class="circle" data-value="0.97">
                                        <div class="skills-text">
                                            <span>97%</span>
                                        </div>
                                    </div>
                                    <p>resource</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- ========== Milestones ========== -->
                <div class="col-12">
                    <div class="elements-title mb-30">
                        <div class="line"></div>
                        <h2>Milestone</h2>
                    </div>
                </div>

                <div class="col-12">
                    <div class="credit-cool-facts-area">
                        <div class="row">

                            <div class="col-12 col-sm-6 col-lg-3">
                                <!-- Single Cool Facts -->
                                <div class="single-cool-fact d-flex align-items-center mb-100">
                                    <div class="scf-icon mr-15">
                                        <i class="icon-piggy-bank"></i>
                                    </div>
                                    <div class="scf-text">
                                        <h2><span class="counter">15,710</span></h2>
                                        <p>Customers</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-lg-3">
                                <!-- Single Cool Facts -->
                                <div class="single-cool-fact d-flex align-items-center mb-100">
                                    <div class="scf-icon mr-15">
                                        <i class="icon-coin"></i>
                                    </div>
                                    <div class="scf-text">
                                        <h2><span class="counter">3,500</span></h2>
                                        <p>Creditors</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-lg-3">
                                <!-- Single Cool Facts -->
                                <div class="single-cool-fact d-flex align-items-center mb-100">
                                    <div class="scf-icon mr-15">
                                        <i class="icon-diamond"></i>
                                    </div>
                                    <div class="scf-text">
                                        <h2><span class="counter">120</span></h2>
                                        <p>awards</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-lg-3">
                                <!-- Single Cool Facts -->
                                <div class="single-cool-fact d-flex align-items-center mb-100">
                                    <div class="scf-icon mr-15">
                                        <i class="icon-wallet"></i>
                                    </div>
                                    <div class="scf-text">
                                        <h2><span class="counter">5,632</span></h2>
                                        <p>Сash loans</p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

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
 