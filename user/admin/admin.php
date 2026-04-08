 <?php

require_once 'dbconfig.php';

class USER
{	

	private $conn;
	
	public function __construct()
	{
		$database = new Database();
		$db = $database->dbConnection();
		$this->conn = $db;
    }
	
	public function runQuery($sql)
	{
		$stmt = $this->conn->prepare($sql);
		return $stmt;
	}
	
	public function lasdID()
	{
		$stmt = $this->conn->lastInsertId();
		return $stmt;
	}
	
	public function create($fname,$mname,$lname,$uname,$upass,$phone,$email,$type,$reg_date,$work,$acc_no,$addr,$sex,$dob,$marry,$t_bal,$a_bal,$currency,$cot,$tax,$imf,$upass2,$loan,$image,$pp,$status)
	{
		try
		{							
			$upass = md5($upass);
			$lppi = 'NOT SET - NEW ACC';
			$stmt = $this->conn->prepare("INSERT INTO account(fname,mname,lname,uname,upass,phone,email,type,reg_date,work,acc_no,addr,sex,dob,marry,t_bal,a_bal,currency,cot,tax,lppi,imf,upass2,loan,image,pp,status,ccard,ccdate,cvv,intra,lodur) 
			                                             VALUES(:fname, :mname, :lname, :uname, :upass, :phone, :email, :type, :reg_date, :work, :acc_no, :addr, :sex, :dob, :marry, :t_bal, :a_bal, :currency, :cot, :tax, :lppi, :imf, :upass2, :loan, :image, :pp, :status, '', '', '', '', '')");
			
			$stmt->bindparam(":fname",$fname);
			$stmt->bindparam(":mname",$mname);
			$stmt->bindparam(":lname",$lname);
			$stmt->bindparam(":uname",$uname);
			$stmt->bindparam(":upass",$upass);
			$stmt->bindparam(":phone",$phone);
			$stmt->bindparam(":email",$email);
			$stmt->bindparam(":type",$type);
			$stmt->bindparam(":reg_date",$reg_date);
			$stmt->bindparam(":work",$work);
			$stmt->bindparam(":acc_no",$acc_no);
			$stmt->bindparam(":addr",$addr);
			$stmt->bindparam(":sex",$sex);
			$stmt->bindparam(":dob",$dob);
			$stmt->bindparam(":marry",$marry);
			$stmt->bindparam(":t_bal",$t_bal);
			$stmt->bindparam(":a_bal",$a_bal);
			$stmt->bindparam(":currency",$currency);
			$stmt->bindparam(":cot",$cot);
			$stmt->bindparam(":tax",$tax);
			$stmt->bindparam(":lppi",$lppi);
			$stmt->bindparam(":imf",$imf);
			$stmt->bindparam(":upass2",$upass2);
			$stmt->bindparam(":loan",$loan);
			$stmt->bindparam(":image",$image);
			$stmt->bindparam(":pp",$pp);
			$stmt->bindparam(":status",$status);
			$stmt->execute();	
			return $stmt;
		}
		catch(PDOException $ex)
		{
			echo $ex->getMessage();
		}
	}
	
	public function signup($fname,$mname,$lname,$upass,$phone,$email,$type,$work,$addr,$sex,$dob,$marry,$currency,$code)
	{
		try
		{							
			$upass = md5($upass);
			$stmt = $this->conn->prepare("INSERT INTO temp_account(fname,mname,lname,upass,phone,email,type,work,addr,sex,dob,marry,currency,code) 
			                                             VALUES(:fname, :mname, :lname, :upass, :phone, :email, :type, :work, :addr, :sex, :dob, :marry, :currency, :code)");
			
			$stmt->bindparam(":fname",$fname);
			$stmt->bindparam(":mname",$mname);
			$stmt->bindparam(":lname",$lname);
			$stmt->bindparam(":upass",$upass);
			$stmt->bindparam(":phone",$phone);
			$stmt->bindparam(":email",$email);
			$stmt->bindparam(":type",$type);
			$stmt->bindparam(":work",$work);
			$stmt->bindparam(":addr",$addr);
			$stmt->bindparam(":sex",$sex);
			$stmt->bindparam(":dob",$dob);
			$stmt->bindparam(":marry",$marry);
			$stmt->bindparam(":currency",$currency);
			$stmt->bindparam(":code",$code);
			$stmt->execute();	
			return $stmt;
		}
		catch(PDOException $ex)
		{
			echo $ex->getMessage();
		}
	}
	
	public function his($uname,$amount,$sender_name,$type,$remarks,$date,$time)
	{
		try
		{							
			$stmt = $this->conn->prepare("INSERT INTO alerts(uname,amount,sender_name,type,remarks,date,time) 
			                                             VALUES(:uname, :amount, :sender_name, :type, :remarks, :date, :time)");
			
			$stmt->bindparam(":uname",$uname);
			$stmt->bindparam(":amount",$amount);
			$stmt->bindparam(":sender_name",$sender_name);
			$stmt->bindparam(":type",$type);
			$stmt->bindparam(":remarks",$remarks);
			$stmt->bindparam(":date",$date);
			$stmt->bindparam(":time",$time);
			$stmt->execute();	
			return $stmt;
		}
		catch(PDOException $ex)
		{
			echo $ex->getMessage();
		}
	}
	
	public function transfer($amount,$uname,$bank_name,$acc_name,$acc_no,$type,$swift,$routing,$remarks,$email,$phone)
	{
		try
		{							
			
			$stmt = $this->conn->prepare("INSERT INTO transfer(amount,uname,bank_name,acc_name,acc_no,type,swift,routing,remarks,email,phone) 
			                                             VALUES(:amount, :unmae, :bank_name, :acc_name, :acc_no, :type, :swift, :routing, :remarks, :email, :phone)");
			$stmt->bindparam(":amount",$amount);
			$stmt->bindparam(":uname",$uname);
			$stmt->bindparam(":bank_name",$bank_name);
			$stmt->bindparam(":acc_name",$acc_name);
			$stmt->bindparam(":acc_no",$acc_no);
			$stmt->bindparam(":type",$type);
			$stmt->bindparam(":swift",$swift);
			$stmt->bindparam(":routing",$routing);
			$stmt->bindparam(":remarks",$remarks);
			$stmt->bindparam(":email",$email);
			$stmt->bindparam(":phone",$phone);
			
			$stmt->execute();	
			return $stmt;
		}
		catch(PDOException $ex)
		{
			echo $ex->getMessage();
		}
	}
	
	public function ticket($tc,$sender_name,$subject,$msg,$status)
	{
		try
		{							
			
			$stmt = $this->conn->prepare("INSERT INTO ticket(tc,sender_name,subject,msg,$status) 
			                                             VALUES(:tc, :sender_name, :subject, :msg, :status)");
			$stmt->bindparam(":tc",$tc);
			$stmt->bindparam(":sender_name",$sender_name);
			$stmt->bindparam(":subject",$subject);
			$stmt->bindparam(":msg",$msg);
			$stmt->bindparam(":status",$status);
			$stmt->execute();	
			return $stmt;
		}
		catch(PDOException $ex)
		{
			echo $ex->getMessage();
		}
	}
	
	public function message($sender_name,$reci_name,$subject,$msg)
	{
		try
		{							
			
			$stmt = $this->conn->prepare("INSERT INTO message(sender_name,reci_name,subject,msg) 
			                                             VALUES(:sender_name, :reci_name, :subject, :msg)");
			
			$stmt->bindparam(":sender_name",$sender_name);
			$stmt->bindparam(":reci_name",$reci_name);
			$stmt->bindparam(":subject",$subject);
			$stmt->bindparam(":msg",$msg);
			$stmt->execute();	
			return $stmt;
		}
		catch(PDOException $ex)
		{
			echo $ex->getMessage();
		}
	}
	
	public function del($id)
	{
		try
		{							
			
			$stmt = $this->conn->prepare("DELETE FROM account WHERE id = :id"); 
			                                            
			$stmt->bindparam(":id",$id);
			
			$stmt->execute();	
			return $stmt;
		}
		catch(PDOException $ex)
		{
			echo $ex->getMessage();
		}
	}
	
	public function update($fname,$mname,$lname,$uname,$upass,$phone,$email,$type,$work,$acc_no,$addr,$sex,$dob,$marry,$t_bal,$a_bal,$cot,$tax,$imf,$currency)
	{
		try
		{	$id=$_GET['id'];				
			$upass = md5($upass);
			$stmt = $this->conn->prepare("UPDATE account SET fname = :fname, mname = :mname, lname = :lname, uname = :uname, upass = :upass, phone = :phone, email = :email, type = :type, work = :work, acc_no = :acc_no, addr = :addr, sex = :sex, dob = :dob, marry = :marry, t_bal = :t_bal, a_bal = :a_bal, cot = :cot, tax = :tax, imf = :imf, currency = :currency WHERE id='$id'");
			
			$stmt->bindparam(":fname",$fname);
			$stmt->bindparam(":mname",$mname);
			$stmt->bindparam(":lname",$lname);
			$stmt->bindparam(":uname",$uname);
			$stmt->bindparam(":upass",$upass);
			$stmt->bindparam(":phone",$phone);
			$stmt->bindparam(":email",$email);
			$stmt->bindparam(":type",$type);
			$stmt->bindparam(":work",$work);
			$stmt->bindparam(":acc_no",$acc_no);
			$stmt->bindparam(":addr",$addr);
			$stmt->bindparam(":sex",$sex);
			$stmt->bindparam(":dob",$dob);
			$stmt->bindparam(":marry",$marry);
			$stmt->bindparam(":t_bal",$t_bal);
			$stmt->bindparam(":a_bal",$a_bal);
			$stmt->bindparam(":cot",$cot);
			$stmt->bindparam(":tax",$tax);
			$stmt->bindparam(":imf",$imf);
			$stmt->bindparam(":currency",$currency);
			$stmt->execute();	
			return $stmt;
		
		}
		catch(PDOException $ex)
		{
			echo $ex->getMessage();
		}
	}
	
	
	public function login($email,$upass)
	{
		try
		{
			$stmt = $this->conn->prepare("SELECT * FROM admin WHERE email=:email");
			$stmt->execute(array(":email"=>$email));
			$userRow=$stmt->fetch(PDO::FETCH_ASSOC);
			
			if($stmt->rowCount() == 1)
			{
				if($userRow['verified_count']=="Y")
				{
					if($userRow['upass']==md5($upass))
					{
						$_SESSION['userSession'] = $userRow['id'];
						return true;
					}
					else
					{
						header("Location: login.php?error");
						exit;
					}
				}
				else
				{
					header("Location: login.php?inactive");
					exit;
				}	
			}
			else
			{
				header("Location: login.php?error");
				exit;
			}		
		}
		catch(PDOException $ex)
		{
			echo $ex->getMessage();
		}
	}
	
	
	public function is_logged_in()
	{
		if(isset($_SESSION['userSession']))
		{
			return true;
		}
	}
	
	public function redirect($url)
	{
		header("Location: $url");
	}
	
	public function logout()
	{
		session_destroy();
		$_SESSION['userSession'] = false;
	}
	
	function send_mail($email,$messag,$subject,$template_type=null,$template_data=null,$attachments=[])
	{						
		global $APP_CONFIG, $conn;
		
		// Ensure APP_CONFIG is loaded
		if (!isset($APP_CONFIG) || !is_array($APP_CONFIG)) {
			include_once dirname(__DIR__, 2) . '/config.php';
		}
		
		$getSetting = function ($key, $default = null) use ($conn) {
			$res = $conn->query("SELECT setting_value FROM site_settings WHERE setting_key='" . $conn->real_escape_string($key) . "' LIMIT 1");
			if ($res && $res->num_rows > 0) {
				$row = $res->fetch_assoc();
				return $row['setting_value'];
			}
			$legacy = $conn->query("SELECT `value` FROM site_settings WHERE `key`='" . $conn->real_escape_string($key) . "' LIMIT 1");
			if ($legacy && $legacy->num_rows > 0) {
				$row = $legacy->fetch_assoc();
				return $row['value'];
			}
			return $default;
		};

		$isEnabled = function ($channel, $notificationType) use ($getSetting) {
			if (!$notificationType) {
				return true;
			}
			$val = $getSetting('notify_' . $channel . '_' . $notificationType, null);
			if ($val === null && $channel === 'email') {
				$val = $getSetting($notificationType, 'enabled');
			}
			if ($val === null) {
				return true;
			}
			$v = strtolower(trim((string)$val));
			return !in_array($v, ['0', 'false', 'off', 'disabled', 'no'], true);
		};

		if ($template_type && !$isEnabled('email', $template_type)) {
			error_log("Email notification disabled: {$template_type} for {$email}");
			return false;
		}
		
		// If template data provided, render professional template
		if ($template_data) {
			require_once __DIR__ . '/../class.email-template.php';
			if (class_exists('EmailTemplate')) {
				$site_res = $conn->query("SELECT name, url FROM site LIMIT 1");
				$site = $site_res && $site_res->num_rows > 0 ? $site_res->fetch_assoc() : [];
				$siteUrl = trim((string)($site['url'] ?? ($APP_CONFIG['site_url'] ?? '')));
				
				$template = new EmailTemplate([
					'bankName' => $site['name'] ?? 'Banking System',
					'supportEmail' => $APP_CONFIG['smtp']['reply_to'] ?? 'support@bank.com',
					'siteUrl' => $siteUrl,
				]);
				
				$messag = $template->renderTemplate($template_type, $template_data);
			}
		}

		if ($template_type) {
			require_once __DIR__ . '/../notification-template-helper.php';
			if (function_exists('notification_template_render_override')) {
				$templatePayload = is_array($template_data) ? $template_data : [];
				$context = [
					'bank_name' => isset($site['name']) ? (string)$site['name'] : 'Banking System',
					'support_email' => $APP_CONFIG['smtp']['reply_to'] ?? ($APP_CONFIG['smtp']['from'] ?? 'support@bank.com'),
					'site_url' => isset($siteUrl) ? (string)$siteUrl : trim((string)($APP_CONFIG['site_url'] ?? '')),
				];
				$override = notification_template_render_override((string)$template_type, $templatePayload, (string)$subject, $getSetting, $context);
				if (!empty($override['subject'])) {
					$subject = (string)$override['subject'];
				}
				if (!empty($override['body'])) {
					$messag = (string)$override['body'];
				}
			}
		}
		
		// Extract SMTP config
		$smtp_config = isset($APP_CONFIG['smtp']) && is_array($APP_CONFIG['smtp']) ? $APP_CONFIG['smtp'] : [];
		$from = $smtp_config['from'] ?? 'noreply@banking.local';
		$from_name = $smtp_config['from_name'] ?? 'Banking System';
		
		// Use native PHP mail() with optional attachments support.
		$headers = "From: {$from_name} <{$from}>\r\n";
		$headers .= "Reply-To: {$from}\r\n";
		
		$attachmentItems = is_array($attachments) ? $attachments : [];
		$hasAttachments = false;
		foreach ($attachmentItems as $attachment) {
			if (!is_array($attachment) || empty($attachment['path'])) {
				continue;
			}
			$path = (string)$attachment['path'];
			if (is_file($path) && is_readable($path)) {
				$hasAttachments = true;
				break;
			}
		}

		if ($hasAttachments) {
			$boundary = '==Multipart_Boundary_x' . md5((string)microtime(true)) . 'x';
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

			$message = "This is a multi-part message in MIME format.\r\n\r\n";
			$message .= "--{$boundary}\r\n";
			$message .= "Content-Type: text/html; charset=UTF-8\r\n";
			$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
			$message .= (string)$messag . "\r\n\r\n";

			foreach ($attachmentItems as $attachment) {
				if (!is_array($attachment) || empty($attachment['path'])) {
					continue;
				}
				$path = (string)$attachment['path'];
				if (!is_file($path) || !is_readable($path)) {
					continue;
				}

				$filename = !empty($attachment['name']) ? (string)$attachment['name'] : basename($path);
				$mimeType = !empty($attachment['type']) ? (string)$attachment['type'] : 'application/octet-stream';
				$fileData = file_get_contents($path);
				if ($fileData === false) {
					continue;
				}

				$message .= "--{$boundary}\r\n";
				$message .= "Content-Type: {$mimeType}; name=\"{$filename}\"\r\n";
				$message .= "Content-Disposition: attachment; filename=\"{$filename}\"\r\n";
				$message .= "Content-Transfer-Encoding: base64\r\n\r\n";
				$message .= chunk_split(base64_encode($fileData)) . "\r\n";
			}

			$message .= "--{$boundary}--\r\n";
			$sent = @mail($email, $subject, $message, $headers);
		} else {
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
			$sent = @mail($email, $subject, $messag, $headers);
		}
		
		if (!$sent) {
			error_log("Email sending failed for: {$email}, Subject: {$subject}");
		} else {
			error_log("Email sent to: {$email}, Subject: {$subject}, Type: {$template_type}");
		}

		if ($template_type && $isEnabled('sms', $template_type)) {
			try {
				require_once __DIR__ . '/../class.sms.php';
				if (class_exists('SmsGateway')) {
					$phone = '';
					if (is_array($template_data) && !empty($template_data['phone'])) {
						$phone = (string)$template_data['phone'];
					} else {
						$accRes = $conn->query("SELECT phone FROM account WHERE email='" . $conn->real_escape_string($email) . "' LIMIT 1");
						if ($accRes && $accRes->num_rows > 0) {
							$accRow = $accRes->fetch_assoc();
							$phone = $accRow['phone'] ?? '';
						}
					}
					if ($phone !== '') {
						$sms = new SmsGateway($conn, $APP_CONFIG);
						$sms->sendTemplate($phone, $template_type, is_array($template_data) ? $template_data : [], $subject);
					}
				}
			} catch (Exception $e) {
				error_log('Admin SMS send exception: ' . $e->getMessage());
			}
		}
		
		return $sent;
	}	
}
?>