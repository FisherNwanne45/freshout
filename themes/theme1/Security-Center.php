<?php
include('session.php');
?>
<?php session_start(); ?>

  
<!DOCTYPE html>
<html lang="en">
 <?php
        include('config.php');
        $result = $conn->query("SELECT * FROM site");
        if(!$result->num_rows > 0){ echo '<h2 style="text-align:center;">No Data Found</h2>'; }
        while($row = $result->fetch_assoc())
        {
      ?>
<head>


<!-- start inc_head code -->
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="apple-touch-icon" sizes="57x57" href="apple-touch-icon-57x57.php">
<link rel="apple-touch-icon" sizes="60x60" href="apple-touch-icon-60x60.php">
<link rel="apple-touch-icon" sizes="72x72" href="apple-touch-icon-72x72.php">
<link rel="apple-touch-icon" sizes="76x76" href="apple-touch-icon-76x76.php">
<link rel="apple-touch-icon" sizes="114x114" href="apple-touch-icon-114x114.php">
<link rel="apple-touch-icon" sizes="120x120" href="apple-touch-icon-120x120.php">
<link rel="apple-touch-icon" sizes="144x144" href="apple-touch-icon-144x144.php">
<link rel="apple-touch-icon" sizes="152x152" href="apple-touch-icon-152x152.php">
<link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon-180x180.php">
<link rel="icon" type="image/png" href="asset.php?type=favicon" sizes="32x32">
<link rel="icon" type="image/png" href="android-chrome-192x192.php" sizes="192x192">
<link rel="icon" type="image/png" href="asset.php?type=favicon" sizes="96x96">
<link rel="icon" type="image/png" href="asset.php?type=favicon" sizes="16x16">
<link rel="manifest" href="assets/manifest.json">
<link rel="mask-icon" href="img/safari-pinned-tab.svg" color="#5bbad5">
<meta name="msapplication-TileColor" content="#581c20">
<meta name="msapplication-TileImage" content="img/mstile-144x144.png">
<meta name="theme-color" content="#581c20">

<link rel="stylesheet" href="css/normalize.css" type="text/css" />
<link href='https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700|Roboto:300,700,900,500' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="css/animate.css" type="text/css">
<link href="css/login.css" rel="stylesheet" />
<link rel="stylesheet" href="css/stylescb0ccb0c.css?v1.2" type="text/css" />
<link rel="stylesheet" href="css/mainnav.css" type="text/css" />
<link rel="stylesheet" type="text/css" href="css/lightcase.css">
<link rel="stylesheet" href="maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<script src="js/vendor/modernizr-2.8.3.min.js"></script>
<!-- [if lt IE 9]>
	<script src="https://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif] -->

<link rel="author" href="humans.txt" type="text/plain" />
<!-- end inc_head code --><title>Security Center for <?php echo $row['name']; ?></title>
<meta name="description" content="" />
<meta name="keywords" content="" />

</head>

<body class="about"><!--personal | business | electronic | loan | about -->
    <a id="top"></a>
	<div id="page">
	<header id="header">
		<div class="inner-content">
 
	<div id="logo">
                <h1><?php echo $row['name']; ?></h1>
				<a href="index-2.php">
					<img src="images/logo.png" class="logo-desktop" alt="<?php echo $row['name']; ?>" title="<?php echo $row['name']; ?> Logo">
				</a>
			</div><!--/logo-->

            <div id="login-button">
                <h3>Login</h3>
            </div>
            <div id="menuopen" class="menubutton">
                <i class="fa fa-bars"></i>
                <h2>MENU</h2>
            </div>
            <div class="clear"></div>


		</div><!--/header-inner-content-->
        <nav id="nav" class="nav">
            <div class="inner-content">
                <div id="logomark">
                    <img src="images/logo.png" alt="">
                </div><!--/logomark-->
                <nav class="secondary">
                    <ul>
                        <li><a href="https://www.facebook.com/" target="_blank"><i class="fa fa-facebook-official"></i><span>Like Us</span></a></li>
                        <li><a href="Contact-Us.php"><i class="fa fa-at"></i><span>Contact Us</span></a></li>

                        <li><a href="Locations.php"><i class="fa fa-map-marker"></i><span>Locations</span></a></li>
                        <!--<li><a href="#"><i class="fa fa-facebook"></i><span>Facebook</span></a></li>
                        <li><a href="#"><i class="fa fa-linkedin"></i><span>LinkedIn</span></a></li>-->
                        <li><a href="speedbump76407640.php?link=http://www.otcmarkets.com/stock/FISB/quote"><i class="icon-stock"></i><span>FISB Stock Quote</span></a></li>
                        <li><?php include_once dirname(__DIR__, 2) . '/private/shared-translator.php'; ?></li>

                    </ul>
                </nav>
                <div id="mainnav">
                    <ul class="panelnav">
                        <li class="menuitem1">
                            <a href="javascript:void(0)" class="category">Personal</a>
                            <div class="navpanel item1panel">
                                <div class="panel-content">
                                    <div class="panelxy">
                                        <div>
                                            <h2>Deposit Products</h2>
                                            <ul>
                                                <li><a href="Checking.php">Checking</a></li>
                                                <li><a href="Savings.php">Savings</a></li>
                                                <li><a href="Money-Market.php">Money Market</a></li>
                                                <li><a href="Certificates-of-Deposit.php">Certificates of Deposit</a></li>
                                                <li><a href="Certificates-of-Deposit.php#IRA">Individual Retirement Accounts</a></li>
                                                <li><a href="Savings.php#Health-Savings">Health Savings</a></li>
                                            </ul>
                                        </div>
                                        <div>
                                            <h2>Other Services</h2>
                                            <ul>
                                                <li><a href="Debit-Cards.php">Debit Cards</a></li>
                                                <li><a href="Check-Orders.php">Check Orders</a></li>
                                            </ul>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                        </li><!--/ li menuitem1 -->
                        <li class="menuitem2">
                            <a href="javascript:void(0)" class="category">Business</a>
                            <div class="navpanel item2panel">
                                <div class="panel-content">
                                    <div class="panelxy">
                                        <div>
                                            <h2>Deposit Products</h2>
                                            <ul>
                                                <li><a href="Business-Checking.php">Checking</a></li>
                                                <li><a href="Business-Savings.php">Savings</a></li>
                                                <li><a href="Business-Money-Market.php">Money Market</a></li>
                                                <li><a href="Business-Certificates-of-Deposit.php">Certificates of Deposit</a></li>
                                            </ul>
                                        </div>

                                        <div>
                                            <h2>Other Services</h2>
                                            <ul>
                                                <li><a href="Business-Debit-Cards.php">Debit Cards</a></li>
                                                <li><a href="Other-Business-Banking-Services.php#Check-Reorder">Check Orders</a></li>
                                            </ul>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                        </li><!--/ li menuitem2 -->
                        <li class="menuitem3">
                            <a href="javascript:void(0)" class="category">Electronic Banking</a>
                            <div class="navpanel item3panel">
                                <div class="panel-content">
                                    <div class="panelxy">
                                        <div>
                                            <h2>Online Services</h2>
                                            <ul>
                                                <li><a href="Online-Banking.php">Online Banking Personal</a></li>
                                                <li><a href="Business-Online-Banking.php">Online Banking Business</a></li>
                                                <li><a href="Online-Banking.php#eStatements">eStatements</a></li>
                                                <li><a href="Online-Banking.php#Bill-Pay">Online Bill Pay</a></li>
                                                <li><a href="Mobile-Banking.php">Mobile Banking</a></li>
                                                <li><a href="Mobile-Banking.php#Mobile-Deposit">Mobile Deposit</a></li>
                                            </ul>
                                        </div>
                                        <div>
                                            <h2>Cash Management</h2>
                                            <ul>
                                                <li><a href="Payment-and-Receivables.php">ACH Origination</a></li>
                                                <li><a href="Payment-and-Receivables.php#Online-Wire-Transfer">Online Wire Transfer</a></li>
                                                <li><a href="Payment-and-Receivables.php#Positive-Pay">Positive Pay</a></li>
                                                <li><a href="Payment-and-Receivables.php#Merchant-Card-Services">Merchant Card Services</a></li>
                                                <li><a href="Payment-and-Receivables.php#Remote-Deposit-Capture">Remote Deposit Capture</a></li>
                                            </ul>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                        </li><!--/ li menuitem3 -->
                        <li class="menuitem4">
                            <a href="javascript:void(0)" class="category">Lending</a>
                            <div class="navpanel item4panel">
                                <div class="panel-content">
                                    <div class="panelxy">
                                        <div>
                                            <h2>Personal</h2>
                                            <ul>
                                                <li><a href="Consumer-Lending.php">Consumer Lending</a></li>
                                            </ul>
                                        </div>
                                        <div>
                                            <h2>Business</h2>
                                            <ul>
                                                <li><a href="Business-Loans-and-Lines-of-Credit.php">Business Loans & Line of Credit</a></li>
                                                <li><a href="SBA-Government-Guaranteed-Loans.php">SBA/Government Guaranteed Loans</a></li>
                                            </ul>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                        </li><!--/ li menuitem4 -->
                        <li class="menuitem5">
                            <a href="javascript:void(0)" class="category">About Us</a>
                            <div class="navpanel item5panel">
                                <div class="panel-content">
                                    <div class="panelxy">
                                        <div>
                                            <h2>About Us</h2>
                                            <ul>
                                                <li><a href="Company-Profile.php">Company Profile</a></li>
                                                <li><a href="Community-1st-Partnership-Program.php">Community 1st Partnership Program</a></li>
                                                <li><a href="Bank-Notes-eNewsletter.php">Bank Notes eNewsletter</a></li>
                                                <li><a href="Locations.php">ATM & Branch Locations</a></li>
                                                <li><a href="Career-Opportunities.php">Career Opportunities</a></li>
 	                                            <li><a href="Contact-Us.php">Contact Us</a></li>
                                            </ul>
                                        </div>
                                        <div>
                                            <h2>Investor Relations</h2>
                                            <ul>
                                                <li><a href="Shareholder-Information.php">Shareholder Information</a></li>
                                                <li><a href="News-and-Press-Releases.php">News & Press Releases</a></li>
                                                <li><a href="Financial-Results.php">Financial Results</a></li>
                                            </ul>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                        </li><!--/ li menuitem5 -->
                    </ul>
                </div>
            </div><!--/nav inner content-->
        </nav><!--/nav-->
	</header><!--/header-->
	       <div id="subpage-container" class="content">
            <div class="subsection-image"></div>

            <div class="subsection" >
                <div class="subsection-content">
					<h1>Security Center</h1>
<p>At <?php echo $row['name']; ?>, we understand that your security is important to you and we make it a priority. Our system controls and products are designed to keep your information secure and protect against potential fraud. The resources on this page are here to help you be aware of potential risks to avoid and what to do if you think your information may be compromised.</p>
<p><strong>Several news agencies have recently reported a significant data compromise affecting millions of consumers.&nbsp;<a title="https://www.identitytheft.gov/Info-Lost-or-Stolen" href="https://www.identitytheft.gov/Info-Lost-or-Stolen">Click here</a> to learn about the steps you can take to protect yourself from identity theft.</strong></p>
<h2>If you suspect any of the following, please contact us immediately.</h2>
<ul>
<li>Fraud Activity</li>
<li>Unauthorized Check/Paper Draft Activity</li>
<li>Unauthorized ACH Withdrawal</li>
<li>Unauthorized Debit Card Transaction</li>
<li>Unauthorized Wire Transfer</li>
<li>Unauthorized access to Online or Mobile Banking</li>
<li>Lost or Stolen Checks</li>
<li>Lost or Stolen Debit Card or ATM Card</li>
</ul>
<p><a class="Button2" href="Privacy.php">How We Protect Your Privacy</a></p>
<h2>Fraud Prevention</h2>
<h3>Identity Theft</h3>
<p>Identity theft is a&nbsp;growing concern. By managing your personal information wisely and cautiously, you can minimize&nbsp;the risk.</p>
<p>If you suspect your identity may have been stolen, please contact us immediately so we may help safeguard your account. You should also contact the credit bureaus listed below. For&nbsp; more information&nbsp;regarding how to&nbsp;guard against or report identity theft, visit the&nbsp;<a href="https://www.consumer.ftc.gov/features/feature-0014-identity-theft">Federal Trade Commission (FTC)</a>&nbsp;web site.</p>
<h5>Credit Bureaus</h5>
<p>Equifax &ndash;&nbsp;<a href="http://www.equifax.com/home/en_us" target="_blank">www.equifax.com</a><br />Write: P.O. Box 740241<br />Atlanta, GA 30374-0241</p>
<p>Experian &ndash;&nbsp;<a href="http://www.experian.com/" target="_blank">www.experian.com</a><br />Write: P.O. Box 2104<br />Allen, TX 75013</p>
<p>TransUnion-&nbsp;<a href="https://www.transunion.com/" target="_blank">www.transunion.com</a><br />Write: P.O. Box 1000<br />Chester, PA 19022</p>
<h3>Other Resources Regarding Types of Fraud, Prevention and Reporting</h3>
<ul class="List-Checkmark">
<li>The&nbsp;<a title="FDIC Website" href="https://www.fdic.gov/consumers/assistance/protection/IdTheft.php">Federal Deposit Insurance Corporation (FDIC) webpage</a> on Cybersecurity and Identity Theft</li>
<li>The <a title="IC3 Website" href="#">Internet Crime Complaint Center (IC3) </a>- a partnership between the Federal Bureau of Investigation (FBI) and the National White Collar Crime Center (NW3C).</li>
<li><a title="Anti-Phishing Work Group Website" href="#">Anti-Phishing Working Group </a>- The Anti-Phishing Working Group (APWG) is a non-profit global pan-industrial and law enforcement association focused on eliminating the fraud, crime and identity theft that result from phishing, pharming, malware and email spoofing of all types.</li>
<li><a title="stopfraud.gov website" href="https://www.stopfraud.gov/">STOPFRAUD.GOV </a>&ndash; The Financial Fraud Enforcement Task Force Federal Trade Commission TC Consumer &amp; Privacy Resources</li>
<li><a title="onguardonline.gov website" href="#speedbump3aa93aa9.php?link=https://www.consumer.ftc.gov/features/feature-0038-onguardonline">OnGuardOnline.gov </a>- The federal government&rsquo;s website to help you be safe, secure and responsible online.&nbsp; Managed by the Federal Trade Commission.</li>
</ul>

               </div>
            </div>


            <div id="contact-stripe">
                <div class="inner-content">
                    <div>
                        <a href="Bank-Notes-eNewsletter.php#Newsletter-Signup">
                        <img src="images/eNews-icon.png" alt="Bank Notes eNewsletter" title="Bank Notes eNewsletter Signup"/>
                            <div><h3><?php echo $row['name']; ?> eNews</h3><p>Join our newsletter</p></div>
                        </a>
                    </div>
                    <div class="questions">
                        <a href="Contact-Us.php">
                        <img src="images/contact-icon.png" alt="Contact Us" title="Contact Us Envelope Icon"/>
                            <div><h3>Have Questions?</h3><p>Let us help</p></div>
                        </a>
                    </div>
                    <div class="clear"></div>
                </div><!--/inner-content-->
            </div><!--/contact-stripe-->
        </div><!--/subpage-container-->
	</div><!--/page-->
<footer id="footer">
		<section class="inner-content">
			<div id="footer-info">
				<div id="footer-logo">
					<img src="images/logo-footer.png" alt="<?php echo $row['name']; ?> Logo">
				</div>
				<p class="slogan">Your 1st Choice in<br />
                        Community Banking</p>


			</div><!--/footer-info-->
			<nav class="secondary">
                <div>
                    <h3>How Can We Help?</h3>
                    <ul>
                        <li><a href="Contact-Us.php">Contact Us</a></li>

                        <li><a href="Locations.php">Find a Location</a></li>
                        <li><a href="Debit-Cards.php#Report-a-Lost-or-Stolen-Debit-Card">Report a Lost or Stolen Debit Card</a></li>
                        <li><a href="Check-Orders.php">Check Orders</a></li>
                        <li><a href="Security-Center.php">Security Center</a></li>
                        <li><a href="Site-Map.php">Site Map</a></li>
                    </ul>
                </div>
                <div>
                    <h3>About <?php echo $row['name']; ?></h3>
                    <ul>
                        <li><a href="Company-Profile.php">Company Profile</a></li>
                        <li><a href="News-and-Press-Releases.php">News & Press Releases</a></li>
                        <li><a href="Career-Opportunities.php">Career Opportunities</a></li>
                         <li><a href="Disclosures-2.php"><?php echo $row['name']; ?> Disclosures</a></li>
                         <li><a href="Privacy.php">Privacy</a></li>
                    </ul>
                </div>
			</nav>
			<div id="newsletter">
				<div class="enews"><img src="images/icon-enews.svg" alt="eNewsletter" title="eNewsletter Subscribe"/></div>
				<h4>Don’t miss an issue of eNews!</h4>
				<p>Sign up for <?php echo $row['name']; ?>'s eNewsletter and receive quarterly updates on <?php echo $row['name']; ?> news, products and events.</p>
				<p><a class="Button1" href="Bank-Notes-eNewsletter.php#Newsletter-Signup">Sign up for eNews</a></p>
			</div>
            <!--<div id="social">
                 <h3>Connect with Us</h3>
                    <ul>
                        <li><a href="#"><i class="fa fa-facebook-official" title="Facebook Icon"></i>Like us on Facebook</a></li>
                        <li><a href="#"><i class="fa fa-linkedin" title="LinkedIn Icon"></i>Network on LinkedIn</a></li>
                    </ul>
            </div>--><!--/social-->
			<div class="clear"></div>
		</section><!--/footer inner content-->
		  <div id="footer-stripe">
                	<div class="inner-content">
                		<p class="copyright"><?php echo $row['year']; ?></p>
                		<div id="footer-utility-logos">
                			<a href="#" title="Small Business Association Preferred Lender Logo" alt="Small Business Association Preferred Lender"><img src="images/logo-sba.png" class="logo-sba" alt="Small Business Association Preferred Lender"/></a>
                			<a href="#" alt="Federal Deposit Insurance Corporation" title="Federal Deposit Insurance Corporation logo"><i class="icon-fdic" title="Federal Deposit Insurance Corporation logo"></i><span class="visuallyhidden">Federal Deposit Insurance Corporation</span></a>
                			<a href="#" alt="Equal Housing Lender Logo" title="Equal Housing Lender"><i class="icon-ehl" title="Equal Housing Lender Logo"></i><span class="visuallyhidden">Equal Housing Lender</span></a>
                		</div>
                		<div class="clear"></div>
                	</div><!--/inner-content-->
                </div><!--/footer-stripe-->
              </footer><!--/footer-->

              <a href="#top" id="gototop" class="gototophide">
              	<div id="gototopContainer">
              		<i class="fa fa-arrow-circle-up"></i>
              		<p>Back to Top</p>
              	</div></a>

              	<script src="js/vendor/jquery-1.11.3.min.js" type="text/javascript"></script>
              	<script src="js/vendor/jquery-ui-1.11.4.min.js" type="text/javascript"></script>
              	<script src="js/vendor/jquery.mobile.custom.min.js" type="text/javascript"></script>
              	<script src="js/vendor/jquery.cookie.js" type="text/javascript"></script>
              	<script src="js/plugins.js" type="text/javascript"></script>
              	<script src="js/jquery-scripts.js" type="text/javascript"></script>
              	<script type="text/javascript" src="js/vendor/lightcase.js"></script>
           	<script src="js/slideshow.js" type="text/javascript"></script>
              	<script src="js/loginf9e3f9e3.js?v=1.1"></script>
  <?php echo $row['tawk']; ?>
 
  </body>

 
</html>
 <?php } ?> 