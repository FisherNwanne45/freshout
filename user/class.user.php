<?php

require_once 'dbconfig.php';

class USER
{	

	private $conn;
	private static $otpSchemaReady = false;
	
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
	
	public function create($pishure,$fname,$mname,$lname,$uname,$upass,$phone,$email,$type,$work,$acc_no,$addr,$sex,$dob,$marry,$t_bal,$a_bal)
	{
		try
		{							
			$upass = md5($upass);
			$stmt = $this->conn->prepare("INSERT INTO account(pishure,fname,mname,lname,uname,upass,phone,email,type,work,acc_no,addr,sex,dob,marry,t_bal,a_bal) 
			                                             VALUES(:pishure, :fname, :mname, :lname, :uname, :upass, :phone, :email, :type, :work, :acc_no, :addr, :sex, :dob, :marry, :t_bal, :a_bal)");
			$stmt->bindparam(":pishure",$pishure);
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
			$stmt->execute();	
			return $stmt;
		}
		catch(PDOException $ex)
		{
			echo $ex->getMessage();
		}
	}
	
	public function transfer($email,$amount,$acc_no,$acc_name,$bank_name,$swift,$routing,$type,$remarks)
	{
		try
		{							
			$acc_name = trim((string)$acc_name);
			if ($acc_name === '') {
				$acc_name = 'Beneficiary';
			}

			$allowedStatuses = ['pending', 'processing', 'completed', 'successful', 'failed', 'cancelled', 'reversed'];
			$status = 'pending';
			try {
				$cfg = $this->conn->prepare("SELECT setting_value FROM transfer_settings WHERE setting_key = :key LIMIT 1");
				$cfg->execute([':key' => 'initial_transfer_status']);
				$cfgRow = $cfg->fetch(PDO::FETCH_ASSOC);
				$candidate = strtolower(trim((string)($cfgRow['setting_value'] ?? '')));
				if (in_array($candidate, $allowedStatuses, true)) {
					$status = $candidate;
				}
			} catch (Throwable $e) {
				// Fallback to pending when transfer_settings table or key is unavailable.
			}

			$stmt = $this->conn->prepare("INSERT INTO transfer(email,amount,acc_no,acc_name,reci_name,bank_name,type,swift,routing,remarks,status) 
			                                             VALUES(:email, :amount, :acc_no, :acc_name, :reci_name, :bank_name, :type, :swift, :routing, :remarks, :status)");
			$stmt->bindparam(":email",$email);
			$stmt->bindparam(":amount",$amount);
			$stmt->bindparam(":acc_no",$acc_no);
			$stmt->bindparam(":acc_name",$acc_name);
			$reci_name = $acc_name;
			$stmt->bindparam(":reci_name",$reci_name);
			$stmt->bindparam(":bank_name",$bank_name);
			$stmt->bindparam(":type",$type);
			$stmt->bindparam(":swift",$swift);
			$stmt->bindparam(":routing",$routing);
			$stmt->bindparam(":remarks",$remarks);
			$stmt->bindparam(":status",$status);
			
			$stmt->execute();	
			return $stmt;
		}
		catch(PDOException $ex)
		{
			echo $ex->getMessage();
		}
	}
	
	public function temp($email,$amount,$acc_no,$acc_name,$bank_name,$swift,$routing,$type,$remarks)
	{
		try
		{							
			
			$stmt = $this->conn->prepare("INSERT INTO temp_transfer(email,amount,acc_no,acc_name,bank_name,type,swift,routing,remarks) 
			                                             VALUES(:email, :amount, :acc_no, :acc_name, :bank_name, :type, :swift, :routing, :remarks)");
			$stmt->bindparam(":email",$email);
			$stmt->bindparam(":amount",$amount);
			$stmt->bindparam(":acc_no",$acc_no);
			$stmt->bindparam(":acc_name",$acc_name);
			$stmt->bindparam(":bank_name",$bank_name);
			$stmt->bindparam(":type",$type);
			$stmt->bindparam(":swift",$swift);
			$stmt->bindparam(":routing",$routing);
			$stmt->bindparam(":remarks",$remarks);
			
			$stmt->execute();	
			return $stmt;
		}
		catch(PDOException $ex)
		{
			echo $ex->getMessage();
		}
	}
	
	public function ticket($tc,$sender_name,$sub,$msg,$mail='')
	{
		try
		{							
			$stmt = $this->conn->prepare("INSERT INTO ticket(tc,sender_name,mail,subject,msg,status) 
			                                             VALUES(:tc, :sender_name, :mail, :subject, :msg, :status)");
			$stmt->bindparam(":tc",$tc);
			$stmt->bindparam(":sender_name",$sender_name);
			$stmt->bindparam(":mail",$mail);
			$stmt->bindparam(":subject",$sub);
			$stmt->bindparam(":msg",$msg);
			$status = 'Pending';
			$stmt->bindparam(':status',$status);
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
	
	public function delaccount($id)
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
	
	public function update($email,$phone,$addr)
	{
		$update = "UPDATE account SET
				email = :email,
				phone = :phone,
				addr = :addr,
				
				WHERE id = :id";
		try
		{							
			$stmt = $this->conn->prepare($update); 
			                                         
			$stmt->bindparam(':email', $_POST['email'], PDO::PARAM_STR);
			$stmt->bindparam(':phone', $_POST['phone'], PDO::PARAM_STR);
			$stmt->bindparam(':addr', $_POST['addr'], PDO::PARAM_STR);
			
			$stmt->execute();	
			return $stmt;
		}
		catch(PDOException $ex)
		{
			echo $ex->getMessage();
		}
	}
	
	public function bal($t_bal)
	{
		$update = "UPDATE account SET
				t_bal = :t_bal,
				
				WHERE id = :id";
		try
		{							
			$stmt = $this->conn->prepare($update); 
			                                         
			$stmt->bindparam(':t_bal', $_POST['t_bal'], PDO::PARAM_STR);
			
			$stmt->execute();	
			return $stmt;
		}
		catch(PDOException $ex)
		{
			echo $ex->getMessage();
		}
	}
	
	public function login($acc_no,$upass)
	{
		try
		{
			$stmt = $this->conn->prepare("SELECT * FROM account WHERE acc_no=:acc_no");
			$stmt->execute(array(":acc_no"=>$acc_no));
			$userRow=$stmt->fetch(PDO::FETCH_ASSOC);
			
			if($stmt->rowCount() == 1)
			{
				if($userRow['upass']==md5($upass))
					{
						$_SESSION['userSession'] = $userRow['acc_no'];
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

	private function ensureOtpSchema()
	{
		if (self::$otpSchemaReady) {
			return;
		}

		try {
			$this->conn->exec("CREATE TABLE IF NOT EXISTS account_otp_codes (
				id INT AUTO_INCREMENT PRIMARY KEY,
				acc_no VARCHAR(50) NULL,
				email VARCHAR(190) NULL,
				purpose VARCHAR(50) NOT NULL,
				otp_code VARCHAR(10) NOT NULL,
				expires_at DATETIME NOT NULL,
				used_at DATETIME NULL,
				created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				INDEX idx_otp_acc (acc_no, purpose, otp_code, used_at),
				INDEX idx_otp_email (email, purpose, otp_code, used_at),
				INDEX idx_otp_exp (expires_at)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		} catch (Exception $e) {
		}

		self::$otpSchemaReady = true;
	}

	private function getOtpExpiryMinutes($default = 10)
	{
		try {
			$stmt = $this->conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = :k LIMIT 1");
			$stmt->execute([':k' => 'otp_expiry_minutes']);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row && isset($row['setting_value'])) {
				$mins = (int)$row['setting_value'];
				if ($mins > 0 && $mins <= 60) {
					return $mins;
				}
			}
		} catch (Exception $e) {
		}

		try {
			$stmt = $this->conn->prepare("SELECT `value` FROM site_settings WHERE `key` = :k LIMIT 1");
			$stmt->execute([':k' => 'otp_expiry_minutes']);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row && isset($row['value'])) {
				$mins = (int)$row['value'];
				if ($mins > 0 && $mins <= 60) {
					return $mins;
				}
			}
		} catch (Exception $e) {
		}

		return (int)$default;
	}

	public function createOtp($accNo, $email, $purpose = 'login', $expiryMinutes = null)
	{
		$this->ensureOtpSchema();

		$accNo = trim((string)$accNo);
		$email = trim((string)$email);
		$purpose = trim((string)$purpose);
		if ($purpose === '') {
			$purpose = 'login';
		}

		if ($expiryMinutes === null) {
			$expiryMinutes = $this->getOtpExpiryMinutes(10);
		}

		$otp = (string)random_int(100000, 999999);

		$identityWhere = [];
		$identityParams = [];
		if ($accNo !== '') {
			$identityWhere[] = 'acc_no = :acc_no';
			$identityParams[':acc_no'] = $accNo;
		}
		if ($email !== '') {
			$identityWhere[] = 'email = :email';
			$identityParams[':email'] = $email;
		}
		if (empty($identityWhere)) {
			return '';
		}

		try {
			$clear = $this->conn->prepare("UPDATE account_otp_codes
				SET used_at = NOW()
				WHERE purpose = :purpose
				AND used_at IS NULL
				AND (" . implode(' OR ', $identityWhere) . ")");
			$clearParams = array_merge([':purpose' => $purpose], $identityParams);
			$clear->execute($clearParams);

			$insert = $this->conn->prepare("INSERT INTO account_otp_codes (acc_no, email, purpose, otp_code, expires_at)
				VALUES (:acc_no, :email, :purpose, :otp, DATE_ADD(NOW(), INTERVAL :mins MINUTE))");
			$insert->bindValue(':acc_no', $accNo !== '' ? $accNo : null, PDO::PARAM_STR);
			$insert->bindValue(':email', $email !== '' ? $email : null, PDO::PARAM_STR);
			$insert->bindValue(':purpose', $purpose, PDO::PARAM_STR);
			$insert->bindValue(':otp', $otp, PDO::PARAM_STR);
			$insert->bindValue(':mins', (int)$expiryMinutes, PDO::PARAM_INT);
			$insert->execute();
		} catch (Exception $e) {
			return '';
		}

		return $otp;
	}

	public function hasActiveOtp($accNo, $email, $purpose = 'login')
	{
		$this->ensureOtpSchema();

		$accNo = trim((string)$accNo);
		$email = trim((string)$email);
		$purpose = trim((string)$purpose);
		if ($purpose === '') {
			$purpose = 'login';
		}

		$identityWhere = [];
		$identityParams = [];
		if ($accNo !== '') {
			$identityWhere[] = 'acc_no = :acc_no';
			$identityParams[':acc_no'] = $accNo;
		}
		if ($email !== '') {
			$identityWhere[] = 'email = :email';
			$identityParams[':email'] = $email;
		}
		if (empty($identityWhere)) {
			return false;
		}

		try {
			$stmt = $this->conn->prepare("SELECT id FROM account_otp_codes
				WHERE purpose = :purpose
				AND used_at IS NULL
				AND expires_at >= NOW()
				AND (" . implode(' OR ', $identityWhere) . ")
				ORDER BY id DESC LIMIT 1");
			$params = array_merge([':purpose' => $purpose], $identityParams);
			$stmt->execute($params);
			return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			return false;
		}
	}

	public function verifyOtp($accNo, $email, $purpose, $otp)
	{
		$this->ensureOtpSchema();

		$accNo = trim((string)$accNo);
		$email = trim((string)$email);
		$purpose = trim((string)$purpose);
		$otp = trim((string)$otp);
		if ($purpose === '' || $otp === '') {
			return false;
		}

		$identityWhere = [];
		$identityParams = [];
		if ($accNo !== '') {
			$identityWhere[] = 'acc_no = :acc_no';
			$identityParams[':acc_no'] = $accNo;
		}
		if ($email !== '') {
			$identityWhere[] = 'email = :email';
			$identityParams[':email'] = $email;
		}
		if (empty($identityWhere)) {
			return false;
		}

		try {
			$stmt = $this->conn->prepare("SELECT id FROM account_otp_codes
				WHERE purpose = :purpose
				AND otp_code = :otp
				AND used_at IS NULL
				AND expires_at >= NOW()
				AND (" . implode(' OR ', $identityWhere) . ")
				ORDER BY id DESC LIMIT 1");
			$params = array_merge([
				':purpose' => $purpose,
				':otp' => $otp,
			], $identityParams);
			$stmt->execute($params);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$row || empty($row['id'])) {
				return false;
			}

			$use = $this->conn->prepare("UPDATE account_otp_codes SET used_at = NOW() WHERE id = :id LIMIT 1");
			$use->execute([':id' => (int)$row['id']]);
			return true;
		} catch (Exception $e) {
			return false;
		}
	}
	
	function send_mail($email, $messag, $subject, $template_type = null, $template_data = null)
	{
		global $APP_CONFIG, $conn;

		if (!isset($APP_CONFIG) || !is_array($APP_CONFIG)) {
			include_once dirname(__DIR__) . '/config.php';
		}

		$getSetting = function ($key, $default = null) {
			try {
				$stmt = $this->conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = :k LIMIT 1");
				$stmt->execute([':k' => $key]);
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($row && array_key_exists('setting_value', $row)) {
					return $row['setting_value'];
				}
			} catch (Exception $e) {
			}

			try {
				$stmt = $this->conn->prepare("SELECT `value` FROM site_settings WHERE `key` = :k LIMIT 1");
				$stmt->execute([':k' => $key]);
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($row && array_key_exists('value', $row)) {
					return $row['value'];
				}
			} catch (Exception $e) {
			}

			return $default;
		};

		$isEnabled = function ($channel, $notificationType) use ($getSetting) {
			if (!$notificationType) {
				return true;
			}

			$key = 'notify_' . $channel . '_' . $notificationType;
			$val = $getSetting($key, null);
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

		if ($template_data) {
			require_once __DIR__ . '/class.email-template.php';
			if (class_exists('EmailTemplate')) {
				$bankName = 'Banking System';
				$siteUrl = trim((string)($APP_CONFIG['site_url'] ?? ''));
				$emailPalette = [];
				try {
					$siteStmt = $this->conn->query("SELECT name, url FROM site LIMIT 1");
					$site = $siteStmt ? $siteStmt->fetch(PDO::FETCH_ASSOC) : null;
					if (!empty($site['name'])) {
						$bankName = $site['name'];
					}
					if (!empty($site['url'])) {
						$siteUrl = trim((string)$site['url']);
					}
				} catch (Exception $e) {
				}

				if (isset($conn) && $conn instanceof mysqli) {
					require_once __DIR__ . '/auth-theme.php';
					if (function_exists('get_auth_color_scheme') && function_exists('get_auth_palette')) {
						$emailPalette = get_auth_palette(get_auth_color_scheme($conn));
					}
				}

				$template = new EmailTemplate([
					'bankName' => $bankName,
					'supportEmail' => $APP_CONFIG['smtp']['reply_to'] ?? 'support@bank.com',
					'siteUrl' => $siteUrl,
					'palette' => $emailPalette,
				]);

				$messag = $template->renderTemplate($template_type, $template_data);
			}
		}

		if ($template_type) {
			require_once __DIR__ . '/notification-template-helper.php';
			if (function_exists('notification_template_render_override')) {
				$templatePayload = is_array($template_data) ? $template_data : [];
				$context = [
					'bank_name' => isset($bankName) ? (string)$bankName : 'Banking System',
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

		$smtp_config = isset($APP_CONFIG['smtp']) && is_array($APP_CONFIG['smtp']) ? $APP_CONFIG['smtp'] : [];
		$from = $smtp_config['from'] ?? 'noreply@banking.local';
		$from_name = $smtp_config['from_name'] ?? 'Banking System';

		$emailSent = false;
		$emailTransport = 'mail()';

		// Try SMTP first so admin SMTP settings are actually used.
		try {
			// Compatibility shim for older PHPMailer code paths on newer PHP builds.
			if (!defined('FILTER_FLAG_HOST_REQUIRED')) {
				define('FILTER_FLAG_HOST_REQUIRED', 0);
			}
			if (!defined('PHPMailer\\PHPMailer\\FILTER_FLAG_HOST_REQUIRED')) {
				define('PHPMailer\\PHPMailer\\FILTER_FLAG_HOST_REQUIRED', 0);
			}

			require_once __DIR__ . '/PHPMailer-master/src/Exception.php';
			require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
			require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';

			$mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
			$mailer->isSMTP();
			$mailer->Host = (string)($smtp_config['host'] ?? 'smtp.gmail.com');
			$mailer->Port = (int)($smtp_config['port'] ?? 465);
			$mailer->SMTPAuth = true;
			$mailer->Username = (string)($smtp_config['username'] ?? '');
			$mailer->Password = (string)($smtp_config['password'] ?? '');
			$secure = (string)($smtp_config['secure'] ?? 'ssl');
			$mailer->SMTPSecure = in_array($secure, ['ssl', 'tls'], true) ? $secure : '';

			$mailer->setFrom($from, $from_name);
			$mailer->addAddress($email);
			$mailer->addReplyTo((string)($smtp_config['reply_to'] ?? $from));
			$mailer->isHTML(true);
			$mailer->Subject = $subject;
			$mailer->Body = $messag;

			$emailSent = $mailer->send();
			if ($emailSent) {
				$emailTransport = 'smtp';
			}
		} catch (\Throwable $e) {
			error_log('SMTP send failed, falling back to mail(): ' . $e->getMessage());
		}

		// Fallback to PHP mail() when SMTP fails.
		if (!$emailSent) {
			$headers = "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
			$headers .= "From: {$from_name} <{$from}>\r\n";
			$headers .= "Reply-To: {$from}\r\n";
			$emailSent = @mail($email, $subject, $messag, $headers);
		}

		// Send complementary SMS for template-based notifications when enabled.
		if ($template_type && $isEnabled('sms', $template_type)) {
			try {
				require_once __DIR__ . '/class.sms.php';
				if (class_exists('SmsGateway')) {
					$phone = '';
					if (!empty($template_data['phone'])) {
						$phone = (string)$template_data['phone'];
					} else {
						$accStmt = $this->conn->prepare("SELECT phone FROM account WHERE email = :email LIMIT 1");
						$accStmt->execute([':email' => $email]);
						$acc = $accStmt->fetch(PDO::FETCH_ASSOC);
						$phone = $acc['phone'] ?? '';
					}

					if ($phone !== '') {
						$sms = new SmsGateway($this->conn, $APP_CONFIG);
						$sms->sendTemplate($phone, $template_type, is_array($template_data) ? $template_data : [], $subject);
					}
				}
			} catch (\Throwable $e) {
				error_log('SMS send exception: ' . $e->getMessage());
			}
		}

		if (!$emailSent) {
			error_log("Email sending failed for: {$email}, Subject: {$subject}");
		} else {
			error_log("Email sent to: {$email}, Subject: {$subject}, Type: {$template_type}, Transport: {$emailTransport}");
		}

		$this->createPanelNotificationFromTemplate($email, $subject, $template_type, is_array($template_data) ? $template_data : []);

		return $emailSent;
	}

	private function createPanelNotificationFromTemplate($email, $subject, $templateType, array $templateData = [])
	{
		$templateType = strtolower(trim((string)$templateType));
		if ($templateType === '') {
			return;
		}

		$eligibleTemplates = ['registration_welcome', 'debit_alert'];
		if (!in_array($templateType, $eligibleTemplates, true)) {
			return;
		}

		try {
			$acct = $this->conn->prepare("SELECT uname, acc_no FROM account WHERE email = :email LIMIT 1");
			$acct->execute([':email' => (string)$email]);
			$user = $acct->fetch(PDO::FETCH_ASSOC);
			if (!$user || empty($user['uname'])) {
				return;
			}

			$senderName = 'Banking Team';
			try {
				$siteStmt = $this->conn->query("SELECT name FROM site ORDER BY id ASC LIMIT 1");
				$siteRow = $siteStmt ? $siteStmt->fetch(PDO::FETCH_ASSOC) : null;
				if (!empty($siteRow['name'])) {
					$senderName = trim((string)$siteRow['name']) . ' Support';
				}
			} catch (Exception $e) {
			}

			$msg = 'You have a new account update in your panel.';
			if ($templateType === 'registration_welcome') {
				$msg = 'Welcome. Your account has been created successfully and is ready to use.';
			} elseif ($templateType === 'debit_alert') {
				$currency = strtoupper(trim((string)($templateData['currency'] ?? '')));
				$amount = trim((string)($templateData['amount'] ?? ''));
				$description = trim((string)($templateData['description'] ?? 'Transaction completed'));
				$msg = 'Transaction completed: ' . $description;
				if ($currency !== '' || $amount !== '') {
					$msg .= ' (' . trim($currency . ' ' . $amount) . ')';
				}
				$msg .= '. Check your balance and activity for details.';
			}

			$insert = $this->conn->prepare("INSERT INTO message(sender_name, reci_name, subject, msg) VALUES(:sender_name, :reci_name, :subject, :msg)");
			$insert->execute([
				':sender_name' => $senderName,
				':reci_name' => (string)$user['uname'],
				':subject' => trim((string)$subject) !== '' ? (string)$subject : 'Account Notification',
				':msg' => $msg,
			]);
		} catch (Exception $e) {
			error_log('Panel message creation failed: ' . $e->getMessage());
		}
	}
}
?>