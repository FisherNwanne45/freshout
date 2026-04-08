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
<!-- end inc_head code --><title>News and Press Releases for <?php echo $row['name']; ?></title>
<meta name="description" content="" />
<meta name="keywords" content="" />

</head>

<body class="about news"><!--personal | business | electronic | loan | about -->
    <a id="top"></a>
	<div id="page">
	<header id="header">
		<div class="inner-content">
		<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window,document,'script',
'connect.facebook.net/en_US/fbevents.php');
fbq('init', '1698598123567796');
fbq('track', 'PageView');
</script>
<noscript>
<img height="1" width="1"
src="https://www.facebook.com/tr?id=1698598123567796&amp;ev=PageView&amp;noscript=1"/>
</noscript>
<!-- End Facebook Pixel Code -->
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
					<h1>Welcome to the News Room</h1>
<h2>Recent Press Releases</h2>
<table class="Table-Simple" style="width: 100%;">
<tbody>
<tr>
<td>
<p><strong>August 2, 2019</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#FISB%20Earnings%20Release_2ndQtr2019e9cde9cd.php?documentId=60950"><?php echo $row['name']; ?> Announces Second Quarter 2019 Financial Results; Record First Half Earnings </a>&nbsp;</p>
</td>
</tr>
<tr>
<td>
<p><strong>July 25, 2019</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#FCB_PreRelease_SantaCruzLPO_final9f509f50.php?documentId=60868"><?php echo $row['name']; ?> Opens Loan Production Office (LPO) in Santa Cruz </a>&nbsp;</p>
</td>
</tr>
<tr>
<td>
<p><strong>May 14, 2019</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#FCB_PreRelease_Austin2019_final38783878.php?documentId=58987">Dean Austin Joins <?php echo $row['name']; ?> as Senior Vice President, Managing Director</a>&nbsp;</p>
</td>
</tr>
<tr>
<td>
<p><strong>April 30, 2019</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#FISB%20Earnings%20Release%2004%2030%2019_Final697f697f.php?documentId=58793"><?php echo $row['name']; ?> Announces First Quarter 2019 Financial Results; Record Average Earning Assets </a></p>
</td>
</tr>
<tr>
<td>
<p><strong>January 31, 2019</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#FISB%20Earnings%20Release%2020181231%2001%2031%2019a756a756.php?documentId=40569"><?php echo $row['name']; ?> Announces Fourth Quarter 2018 Financial Results; Record Annual Net Income </a></p>
</td>
</tr>
<tr>
<td>
<p><strong>January 2, 2019</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#Phil%20Morreale_PRc9f0c9f0.php?documentId=57307"><?php echo $row['name']; ?> Announces Appointment of Phil Morreale as SVP, Managing Director in San Luis Obispo</a></p>
</td>
</tr>
<tr>
<td>
<p><strong>December 3, 2018</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#Demetri%20Snow_PRd86cd86c.php?documentId=57129"><?php echo $row['name']; ?> Announces Appointment of Demetri Snow as Regional President </a></p>
</td>
</tr>
<tr>
<td>
<p><strong>October 30, 2018</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#FISB%20Earnings%20Release%2020180930-FINALa9aa29aa2.php?documentId=56849"><?php echo $row['name']; ?> Announces Third Quarter 2018 Financial Results; Record Loan Portfolio</a></p>
</td>
</tr>
<tr>
<td>
<p><strong>October 5, 2018</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#FISB%20Press%20Release%2020180926%20Stock%20Dividend36163616.php?documentId=56560"><?php echo $row['name']; ?> Announces Declaration of 7.00% Stock Dividend</a></p>
</td>
</tr>
<tr>
<td>
<p><strong>September 11, 2018</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#Anita%20Robinson_PRb1eab1ea.php?documentId=56293"><?php echo $row['name']; ?> Announces Appointment of Anita Robinson as Market President, San Luis Obispo County</a></p>
</td>
</tr>
<tr>
<td>
<p><strong>August 15, 2018</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#EverFi%20PR1bb81bb8.php?documentId=55894"><?php echo $row['name']; ?> Delivers Financial Literacy Curriculum to Local High Schools</a></p>
</td>
</tr>
<tr>
<td>
<p><strong>July 31, 2018</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#FISB%20Earnings%20Release%2020180630-Final18151815.php?documentId=55762"><?php echo $row['name']; ?> Announces Second Quarter 2018 Financial Results; Record Quarterly Pre-Tax Earnings</a></p>
</td>
</tr>
<tr>
<td>
<p><strong>July 20, 2018</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#ABA%20Bank%20Video%20Awards%20PR_0720efe3efe3.php?documentId=55622"><?php echo $row['name']; ?> Announces &ldquo;People Bank with People&rdquo; Commercial is Finalist for an American Bankers Association Bank Marketing Video Award</a></p>
</td>
</tr>
<tr>
<td>
<p><strong>April 26, 2018</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#FISB%20Earnings%20Release%2020180331db8fdb8f.php?documentId=54566"><?php echo $row['name']; ?> Announces First Quarter 2018 Financial Results; Record Average Earning Assets</a></p>
</td>
</tr>
<tr>
<td>
<p><strong>April 10, 2018</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#speedbumpbe006ac0.php?link=https://www.clearinghousecdfi.com/Capital-Treasure-Bank-invests-500000-in-clearinghouse-cdfi/"><?php echo $row['name']; ?> Invests $500,000 in Clearinghouse CDFI</a></p>
</td>
</tr>
<tr>
<td>
<p><strong>March 23, 2018</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#David%20Cohen%20Joins%20Capital%20Treasure%20Bank%20as%20Vice%20President%2c%20Senior%20Relationship%20Manager0a580a58.php?documentId=54126">David Cohen Joins <?php echo $row['name']; ?> as Vice President, Senior Relationship Manager<br /></a></p>
</td>
</tr>
<tr>
<td>
<p><strong>January 31, 2018</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#FISB%20Earnings%20Release%2020171231ad86ad86.php?documentId=52478"><?php echo $row['name']; ?> Announces Fourth Quarter 2017 Financial Results; </a><a href="#FISB%20Earnings%20Release%2020171231ad86ad86.php?documentId=52478">Record Quarterly Pre-Tax Income of $1.79 Million; Quarterly Net Income of $182 Thousand<br /></a></p>
</td>
</tr>
<tr>
<td>
<p><strong>October 31, 2017</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#FISB%20Earnings%20Release%2020170930e116e116.php?documentId=51170"><?php echo $row['name']; ?> Announces Third Quarter 2017 Financial Results; Record Loan Portfolio</a></p>
</td>
</tr>
<tr>
<td>
<p><strong>October 26, 2017</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#FISB%20Press%20Release%2020171025%20Stock%20Dividendcf1ccf1c.php?documentId=51124"><?php echo $row['name']; ?> Announces Declaration of 5.00% Stock Dividend</a></p>
</td>
</tr>
<tr>
<td>
<p><strong>July&nbsp;28, 2017</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#FISB%20Earnings%20Release%2020170630046a046a.php?documentId=48947"><?php echo $row['name']; ?> Announces Second Quarter 2017 Financial Results; Record Loan Portfolio</a></p>
</td>
</tr>
<tr>
<td>
<p><strong>May 25, 2017</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#1st%20Capital%20PR%205-25-17_website24be24be.php?documentId=47726"><?php echo $row['name']; ?> Announces Move of Corporate Headquarters and Opening of City Center Branch</a></p>
</td>
</tr>
<tr>
<td>
<p><strong>April 28, 2017</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#FISB%20Earnings%20Release%2020170331%20v.378cc78cc.php?documentId=47285"><?php echo $row['name']; ?> Announces First Quarter 2017 Financial Results; Record Average Earning Assets</a></p>
</td>
</tr>
<tr>
<td>
<p><strong>January 31, 2017</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#FISB%20Earnings%20Release%20201612317b2f7b2f.php?documentId=45791"><?php echo $row['name']; ?> Announces Fourth Quarter 2016 Financial Results; Record Quarterly Net Income of $1.03 Million and Earnings per Share (Diluted) of $0.23</a></p>
</td>
</tr>
<tr>
<td>
<p><strong>December 9, 2016</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#Capital%20Treasure%20Bank_PR120920168e358e35.php?documentId=45147"><?php echo $row['name']; ?> Announces Expansion of Lending Program to Include Mortgages and Home Equity Loans; </a><br /><a href="#Capital%20Treasure%20Bank_PR120920168e358e35.php?documentId=45147">Appointment of Erin King as Consumer Lending Manager</a></p>
</td>
</tr>
<tr>
<td>
<p><strong>October 27, 2016</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#FISB%20Earnings%20Release%20201609300edf0edf.php?documentId=43412"><?php echo $row['name']; ?> Announces Third Quarter 2016 Financial Results; Record Loan Portfolio</a></p>
</td>
</tr>
<tr>
<td style="width: 175px !important;">
<p><strong>October 27, 2016</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#FISB%20Press%20Release%2020161026%20Stock%20Dividend87768776.php?documentId=43388"><?php echo $row['name']; ?> Announces Declaration of 5.00% Stock Dividend</a></p>
</td>
</tr>
<tr>
<td>
<p><strong>August 2, 2016</strong></p>
</td>
<td>
<p style="text-align: left;"><a href="#FISB%20Press%20Release_Capital%20Treasure%20Bank%20to%20Relocate%20Corportate%20Headquarters%20to%20Taylor%20Building2d86ad86a.php?documentId=42284"><?php echo $row['name']; ?> to Move Corporate Headquarters to Taylor Building in Downtown Salinas</a></p>
</td>
</tr>
</tbody>
</table>
<p><a class="Button2" href="#Prior-Year-Press-Releases.php">Prior Year Press Releases</a></p>
<h2>Media Contacts</h2>
<table class="Table-Simple" style="width: 100%;">
<tbody>
<tr>
 
</tr>
</tbody>
</table>

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