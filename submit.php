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
<meta http-equiv="content-type" content="text/html;charset=UTF-8" /><!-- /Added by HTTrack -->
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
  	<link rel="icon" type="image/png" href="img/favicon-32x32.png" sizes="32x32">
  	<link rel="icon" type="image/png" href="android-chrome-192x192.php" sizes="192x192">
  	<link rel="icon" type="image/png" href="img/favicon-96x96.png" sizes="96x96">
  	<link rel="icon" type="image/png" href="img/favicon-16x16.png" sizes="16x16">
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
	<!-- end inc_head code --><title>Contact <?php echo $row['name']; ?></title>
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<link rel="stylesheet" href="css/custom-select.css" type="text/css" />
	<link rel="stylesheet" href="css/captcha.css" type="text/css" />
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
                        	<li> 
<i class='fa fa-language' style='color: white'></i> <!-- GTranslate: https://gtranslate.io/ -->
 <select onchange="doGTranslate(this);"><option value="">Select Language</option><option value="en|af">Afrikaans</option><option value="en|sq">Albanian</option><option value="en|ar">Arabic</option><option value="en|hy">Armenian</option><option value="en|az">Azerbaijani</option><option value="en|eu">Basque</option><option value="en|be">Belarusian</option><option value="en|bg">Bulgarian</option><option value="en|ca">Catalan</option><option value="en|zh-CN">Chinese (Simplified)</option><option value="en|zh-TW">Chinese (Traditional)</option><option value="en|hr">Croatian</option><option value="en|cs">Czech</option><option value="en|da">Danish</option><option value="en|nl">Dutch</option><option value="en|en">English</option><option value="en|et">Estonian</option><option value="en|tl">Filipino</option><option value="en|fi">Finnish</option><option value="en|fr">French</option><option value="en|gl">Galician</option><option value="en|ka">Georgian</option><option value="en|de">German</option><option value="en|el">Greek</option><option value="en|ht">Haitian Creole</option><option value="en|iw">Hebrew</option><option value="en|hi">Hindi</option><option value="en|hu">Hungarian</option><option value="en|is">Icelandic</option><option value="en|id">Indonesian</option><option value="en|ga">Irish</option><option value="en|it">Italian</option><option value="en|ja">Japanese</option><option value="en|ko">Korean</option><option value="en|lv">Latvian</option><option value="en|lt">Lithuanian</option><option value="en|mk">Macedonian</option><option value="en|ms">Malay</option><option value="en|mt">Maltese</option><option value="en|no">Norwegian</option><option value="en|fa">Persian</option><option value="en|pl">Polish</option><option value="en|pt">Portuguese</option><option value="en|ro">Romanian</option><option value="en|ru">Russian</option><option value="en|sr">Serbian</option><option value="en|sk">Slovak</option><option value="en|sl">Slovenian</option><option value="en|es">Spanish</option><option value="en|sw">Swahili</option><option value="en|sv">Swedish</option><option value="en|th">Thai</option><option value="en|tr">Turkish</option><option value="en|uk">Ukrainian</option><option value="en|ur">Urdu</option><option value="en|vi">Vietnamese</option><option value="en|cy">Welsh</option><option value="en|yi">Yiddish</option></select><div id="google_translate_element2"></div>
<script type="text/javascript">
function googleTranslateElementInit2() {new google.translate.TranslateElement({pageLanguage: 'en',autoDisplay: false}, 'google_translate_element2');}
</script><script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit2"></script>


<script type="text/javascript">
/* <![CDATA[ */
eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('6 7(a,b){n{4(2.9){3 c=2.9("o");c.p(b,f,f);a.q(c)}g{3 c=2.r();a.s(\'t\'+b,c)}}u(e){}}6 h(a){4(a.8)a=a.8;4(a==\'\')v;3 b=a.w(\'|\')[1];3 c;3 d=2.x(\'y\');z(3 i=0;i<d.5;i++)4(d[i].A==\'B-C-D\')c=d[i];4(2.j(\'k\')==E||2.j(\'k\').l.5==0||c.5==0||c.l.5==0){F(6(){h(a)},G)}g{c.8=b;7(c,\'m\');7(c,\'m\')}}',43,43,'||document|var|if|length|function|GTranslateFireEvent|value|createEvent||||||true|else|doGTranslate||getElementById|google_translate_element2|innerHTML|change|try|HTMLEvents|initEvent|dispatchEvent|createEventObject|fireEvent|on|catch|return|split|getElementsByTagName|select|for|className|goog|te|combo|null|setTimeout|500'.split('|'),0,{}))
/* ]]> */
</script>

 </li>

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
                <div id="google_translate_element"></div><script type="text/javascript">
                	function googleTranslateElementInit() {
                		new google.translate.TranslateElement({pageLanguage: 'en', layout: google.translate.TranslateElement.InlineLayout.SIMPLE, autoDisplay: false}, 'google_translate_element');
                	}
                </script><script type="text/javascript" src="../translate.google.com/translate_a/elementa0d8.js?cb=googleTranslateElementInit"></script>
                <div id="subpage-container" class="content">
                	<div class="subsection-image"></div>

                	<div class="subsection" >
                		<div class="subsection-content">
                			<h1>Contact Us</h1>
                			<h2>We want to hear from you!</h2>
                			<h5>Phone: <?php echo $row['phone']; ?>	</h5>
                			<h5>Email: <?php echo $row['email']; ?></h5>
                			<p><strong>Email communication is not secure and could be viewed by others outside <?php echo $row['name']; ?>.&nbsp; You should never disclose personal information such as account numbers, passcodes, personal identification numbers, social security numbers, or any other financial information through email.</strong></p>
                			<p>To use this form, please select the department you would like to contact and describe the general nature of your inquiry. A <?php echo $row['name']; ?> representative will contact you shortly.</p>
                			<b style="color: red;"><?php
   

   if($_POST) {
       
    
    - $dpt     = "";
    - $name            = "";
   - $email   	= "";
   - $phone        	= "";
    - $comments    		= "";
    
    
     
    if(isset($_POST['dpt'])) {
		$dpt = filter_var($_POST['dpt'], FILTER_UNSAFE_RAW);
    }
    
   
     
    if(isset($_POST['email'])) {
        $email = str_replace(array("\r", "\n", "%0a", "%0d"), '', $_POST['email']);
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
         
    }
     
   if(isset($_POST['name'])) {
		$name = filter_var($_POST['name'], FILTER_UNSAFE_RAW);
    }
     
    if(isset($_POST['phone'])) {
		$phone = filter_var($_POST['phone'], FILTER_UNSAFE_RAW);
    }
 
  if(isset($_POST['comments'])) {
		$comments = filter_var($_POST['comments'], FILTER_UNSAFE_RAW);
    }
    
    
    
	$recipient = $from;
     
    $headers  = 'MIME-Version: 1.0' . "\r\n"
    .'Content-type: text/html; charset=utf-8' . "\r\n"
    .'From: ' . $email . "\r\n";
 
    $email_content = "<html><body>";
    $email_content .= "<table style='font-family: Arial;'><tbody> <tr><td style='background: #eee; padding: 10px;'>Department</td><td style='background: #fda; padding: 10px;'>$dpt </td></tr>";
     
     $email_content .= "<tr><td style='background: #eee; padding: 10px;'>Name: </td><td style='background: #fda; padding: 10px;'>$name</td></tr>";
      $email_content .= "<tr><td style='background: #eee; padding: 10px;'>Email:  </td><td style='background: #fda; padding: 10px;'>$email</td></tr>";
       $email_content .= "<tr><td style='background: #eee; padding: 10px;'>Phone:  </td><td style='background: #fda; padding: 10px;'>$phone</td></tr>";
      
       
        $email_content .= "<tr><td style='background: #eee; padding: 10px;'>Comment: </td><td style='background: #fda; padding: 10px;'>$comments  </td></tr>";
     
     
      
       
          
        
    $email_content .= '</body></html>';
 
    
     
    if(mail($recipient, "Email from Client in Website ", $email_content, $headers)) {
        echo '<p> Your message has been forwarded to the appropriate department. </p>';
    } else {
        echo '<p> Sorry your email could not be delivered  </p>';
    }
     
} else {
    echo '<p> </p>';
}
 
?>
</b>

                			<form method="post" action=" " id="contactForm" name="contactForm" method="post"  >
                				<table style="width: 100%" border="0" cellpadding="5" cellspacing="0">
                					<tr>
                						<td>
                							<div id="contact-select">
                								<label for="emailTo" class="visuallyhidden">Send email to</label>
                								<select name="dpt" id="emailTo">
                									<option>General Inquiry</option>
                									<option>Customer Service</option>
                									<option>Online Banking</option>
                									<option>Marketing</option>
                									<option>Loan Services</option>
                									<option>Card Services</option>
                								</select>
                							</div><!--/contact-select-->
                						</td>
                					</tr>
                					<tr>
                						<td><label for="name" class="visuallyhidden">Name</label><input id="name" name="name" value="Name*" type="text" onblur="restoreText(this);" onfocus="clearText(this);" /> </td>
                					</tr>
                					<tr>
                						<td><label for="email" class="visuallyhidden">Email</label><input id="email" name="email" value="Email Address*" type="text" onblur="restoreText(this);" onfocus="clearText(this);" /></td>
                					</tr>
                					<tr>
                						<td><label for="phone" class="visuallyhidden">Telephone Number</label><input id="phone" class="tel" name="phone" value="Phone Number*" type="text" onblur="restoreText(this);" onfocus="clearText(this);" /></td>
                					</tr>
                					<tr>
                						<td><label for="comment" class="visuallyhidden">Comments or Questions</label><textarea id="comment" rows="8" cols="80" name="comments" onblur="restoreText(this);" onfocus="clearText(this);">Comments or Questions</textarea></td>
                					</tr>
                					<tr>
                						<td>
                							<div class="captcha-content">
                								<label for="acceptance">I understand that the information sent by using this form is  secure.</label>
                							</div>
                						</td>
                					</tr>
                					<tr>
                						<td class="form-buttons">
                							<button type="reset" value="Reset" class="Button1">Reset</button>
                							<button type="submit" name="submit0"  value="submit" class="Button1">Submit</button>
                						</td>
                					</tr>
                				</table>
                				<br />
                			</form>


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
              	<script src="js/vendor/mootools-core-1.4.5-full-compat.js" type="text/javascript"></script>
              	<script src="js/vendor/mootools-more-1.4.0.1.js" type="text/javascript"></script>
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