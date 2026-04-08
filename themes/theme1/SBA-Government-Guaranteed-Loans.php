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
	<!-- end inc_head code --><title>SBA Government Guaranteed Loans from <?php echo $row['name']; ?></title>
	<meta name="description" content="" />
	<meta name="keywords" content="" />

</head>

<body class="loan"><!--personal | business | electronic | loan | about -->
	<a id="top"></a>
	<div id="page">
		<header id="header">
			<div class="inner-content">
				<!-- Facebook Pixel Code -->
			 
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
                			<p>To discuss your small business banking needs, contact <a title="email link" href="<?php echo $row['email']; ?>">Roger Gilbert</a>, VP Business Development Officer. <a href="tel:<?php echo $row['phone']; ?>"><?php echo $row['phone']; ?></a></p>
                			<h1>SBA &amp; Government Guaranteed Lending Solutions</h1>
                			<p><?php echo $row['name']; ?> understands that a business needs readily available capital to grow and be competitive. When your business needs financing for new equipment, working capital or to purchase a commercial building, consider an SBA Loan with <?php echo $row['name']; ?>. We are deeply committed to providing an exceptional level of service through fast, in-house decision making.</p>
                			<h2>Small Business Administration Loan Options</h2>
                			<p>The Small Business Administration (SBA) does not make direct loans to small businesses. Rather, SBA sets the guidelines for loans, which are then made by partners like <?php echo $row['name']; ?>. So when a business applies for an SBA loan, it is actually applying for a commercial loan, structured according to SBA requirements with an SBA guaranty.</p>
                			<h3>7(a) Loans</h3>
                			<p>If you are approved for a 7(a) loan, the loan proceeds may be used to establish a new business or to assist in the acquisition, operation, or expansion of an existing business. Some eligible uses of a 7(a) loan include: commercial real estate acquisition, new construction or facility expansion, equipment purchase, long or short term working capital, and purchase of an existing business.</p>
                			<h3>504 Loans</h3>
                			<p>The 504 Loan Program provides approved small businesses with long-term, fixed-rate financing used to acquire fixed assets for expansion or modernization. These loans are typically structured so that the borrower's obligation is only 10% of the project costs, though under certain circumstances a borrower may be obligated to up to 20%.</p>
                			<h3>What you need to get started:</h3>
                			<ul class="List-Checkmark">
                				<li>Proof of past and future earnings</li>
                				<li>A personal and business credit history</li>
                				<li>A clearly defined business plan</li>
                				<li>A completed loan application</li>
                			</ul>
                			<h2>USDA Business and Industry Loan Options</h2>
                			<p>The US Department of Agriculture (USDA) Business and Industry Loan Guarantee (USDA B&amp;I) program guarantees loans made by <?php echo $row['name']; ?> to businesses that benefit rural communities. The program's primary purpose is to create and maintain employment while helping improve the economic climate of the rural communities <?php echo $row['name']; ?> serves. Please note: A company's inability to obtain other credit is NOT a requirement.</p>
                			<h3>To be eligible, a business must meet the following basic criteria:</h3>
                			<ul class="List-Checkmark">
                				<li>Provide new employment opportunities or maintain an existing workforce</li>
                				<li>Projects must be located in a rural area - beyond any 50,000 + population city and its urbanized periphery</li>
                				<li>Proprietorships, Partnerships, Corporations, LLC's, Co-Ops, Trusts, Non-Profits, Tribes, and Public Bodies are eligible</li>
                				<li>Majority ownership must be held by US Citizens or Permanent Residents</li>
                				<li>Any ownership by government or military employees must be less than 20%</li>
                			</ul>
                			<h3>B&amp;I Loan Proceeds can be used for:</h3>
                			<ul class="List-Checkmark">
                				<li>Business and industrial acquisitions, construction, conversion, expansion, repair, modernization or development costs</li>
                				<li>Purchase of equipment, machinery or supplies</li>
                				<li>Start-up costs and working capital</li>
                				<li>Processing and marketing facilities</li>
                				<li>Pollution control and abatement</li>
                				<li>Refinancing for viable projects, under certain conditions</li>
                			</ul>
                			<p>Loan guarantees are limited to a maximum of $10 million per borrower, although the Rural Business-Cooperative Programs Administrator can grant up to $25 million.</p>
                			<h2>Contact a <?php echo $row['name']; ?> SBA Loan Specialist to Get Started</h2>
                			<p>This is the time to take advantage of low interest rates with a lender who has money to lend. We can offer you a no-cost, no obligation loan evaluation.</p>
                			<p>Contact  Roger Gilbert, Vice President and&nbsp;Business Development Officer&nbsp;of Government Guaranteed Lending to schedule an appointment that fits your schedule.</p>
                			<p>&nbsp;</p>
                			<p>Loans subject to credit approval.</p>

                		</div>
                	</div>
                	<table class="Subsection-Callout-Table" style="background-image: url('img/ContentImageHandler4a724a72.jpg?imageId=85331');">
                		<tbody>
                			<tr>
                				<td>
                					<table width="100%">
                						<tbody>
                							<tr>
                								<td>
                									<h2>Explore Your Digital Toolbox</h2>
                									<p>Find the tool that fits your business needs. With convenient cash management options like business online banking, online wire origination, online ACH origination, remote deposit capture and positive pay, we put digital solutions at your fingertips.</p>
                									<p><a class="Button1" href="#CustomContent5d6ce7b1.php?name=Payments+and+Collection">Learn More</a></p>
                								</td>
                							</tr>
                						</tbody>
                					</table>
                				</td>
                			</tr>
                		</tbody>
                	</table>
                	<p>&nbsp;</p>

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