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

	public function create($fname, $pin, $lname, $uname, $upass, $upass2, $phone, $email, $type, $reg_date, $work, $acc_no, $addr, $sex, $dob, $marry, $t_bal, $a_bal, $currency, $cot, $tax, $lppi, $imf, $code5, $image, $pp, $status = 'Active', $login_method = 'pin', $auth_method = 'codes')
	{
		try {
			$upass = password_hash($upass, PASSWORD_BCRYPT);
			$stmt = $this->conn->prepare("INSERT INTO account(fname,pin,pin,lname,uname,upass,upass2,phone,email,type,reg_date,work,acc_no,addr,sex,dob,marry,t_bal,a_bal,currency,cot,tax,lppi,imf,code5,image,pp,status,login_method,auth_method) 
			                                             VALUES(:fname, :pin, :pin, :lname, :uname, :upass, :upass2, :phone, :email, :type, :reg_date, :work, :acc_no, :addr, :sex, :dob, :marry, :t_bal, :a_bal, :currency, :cot, :tax, :lppi, :imf, :code5, :image, :pp, :status, :login_method, :auth_method)");

			$stmt->bindparam(":fname", $fname);
			$stmt->bindparam(":pin", $pin);
			$stmt->bindparam(":lname", $lname);
			$stmt->bindparam(":uname", $uname);
			$stmt->bindparam(":upass", $upass);
			$stmt->bindparam(":upass2", $upass2);
			$stmt->bindparam(":pin", $pin);
			$stmt->bindparam(":phone", $phone);
			$stmt->bindparam(":email", $email);
			$stmt->bindparam(":type", $type);
			$stmt->bindparam(":reg_date", $reg_date);
			$stmt->bindparam(":work", $work);
			$stmt->bindparam(":acc_no", $acc_no);
			$stmt->bindparam(":addr", $addr);
			$stmt->bindparam(":sex", $sex);
			$stmt->bindparam(":dob", $dob);
			$stmt->bindparam(":marry", $marry);
			$stmt->bindparam(":t_bal", $t_bal);
			$stmt->bindparam(":a_bal", $a_bal);
			$stmt->bindparam(":currency", $currency);
			$stmt->bindparam(":cot", $cot);
			$stmt->bindparam(":tax", $tax);
			$stmt->bindparam(":lppi", $lppi);
			$stmt->bindparam(":imf", $imf);
			$stmt->bindparam(":code5", $code5);
			$stmt->bindparam(":image", $image);
			$stmt->bindparam(":pp", $pp);
			$stmt->bindparam(":status", $status);
			$stmt->bindparam(":login_method", $login_method);
			$stmt->bindparam(":auth_method", $auth_method);
			$stmt->execute();
			return $stmt;
		} catch (PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function signup($fname, $pin, $lname, $upass, $upass2, $phone, $email, $type, $work, $addr, $sex, $dob, $marry, $currency, $code, $image, $pp)
	{
		try {
			$upass = md5($upass);
			$stmt = $this->conn->prepare("INSERT INTO temp_account(fname,pin,lname,upass,upass2,phone,email,type,work,addr,sex,dob,marry,currency,code,image,pp) 
			                                             VALUES(:fname, :pin, :lname, :upass, :upass2, :phone, :email, :type, :work, :addr, :sex, :dob, :marry, :currency, :code, :image, :pp)");

			$stmt->bindparam(":fname", $fname);
			$stmt->bindparam(":pin", $pin);
			$stmt->bindparam(":lname", $lname);
			$stmt->bindparam(":upass", $upass);
			$stmt->bindparam(":upass2", $upass2);
			$stmt->bindparam(":phone", $phone);
			$stmt->bindparam(":email", $email);
			$stmt->bindparam(":type", $type);
			$stmt->bindparam(":work", $work);
			$stmt->bindparam(":addr", $addr);
			$stmt->bindparam(":sex", $sex);
			$stmt->bindparam(":dob", $dob);
			$stmt->bindparam(":marry", $marry);
			$stmt->bindparam(":currency", $currency);
			$stmt->bindparam(":code", $code);
			$stmt->bindparam(":image", $image);
			$stmt->bindparam(":pp", $pp);
			$stmt->execute();
			return $stmt;
		} catch (PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function his($uname, $amount, $sender_name, $type, $remarks, $date, $time)
	{
		try {
			$stmt = $this->conn->prepare("INSERT INTO alerts(uname,amount,sender_name,type,remarks,date,time) 
			                                             VALUES(:uname, :amount, :sender_name, :type, :remarks, :date, :time)");

			$stmt->bindparam(":uname", $uname);
			$stmt->bindparam(":amount", $amount);
			$stmt->bindparam(":sender_name", $sender_name);
			$stmt->bindparam(":type", $type);
			$stmt->bindparam(":remarks", $remarks);
			$stmt->bindparam(":date", $date);
			$stmt->bindparam(":time", $time);
			$stmt->execute();
			return $stmt;
		} catch (PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function transfer($amount, $uname, $bank_name, $acc_name, $acc_no, $type, $swift, $routing, $remarks, $email, $phone)
	{
		try {

			$stmt = $this->conn->prepare("INSERT INTO transfer(amount,uname,bank_name,acc_name,acc_no,type,swift,routing,remarks,email,phone,status) 
			                                             VALUES(:amount, :unmae, :bank_name, :acc_name, :acc_no, :type, :swift, :routing, :remarks, :email, :phone, :status)");
			$stmt->bindparam(":amount", $amount);
			$stmt->bindparam(":uname", $uname);
			$stmt->bindparam(":bank_name", $bank_name);
			$stmt->bindparam(":acc_name", $acc_name);
			$stmt->bindparam(":acc_no", $acc_no);
			$stmt->bindparam(":type", $type);
			$stmt->bindparam(":swift", $swift);
			$stmt->bindparam(":routing", $routing);
			$stmt->bindparam(":remarks", $remarks);
			$stmt->bindparam(":email", $email);
			$stmt->bindparam(":phone", $phone);
			$status = 'pending';
			$stmt->bindparam(":status", $status);

			$stmt->execute();
			return $stmt;
		} catch (PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function ticket($tc, $sender_name, $subject, $msg, $status)
	{
		try {

			$stmt = $this->conn->prepare("INSERT INTO ticket(tc,sender_name,subject,msg,$status) 
			                                             VALUES(:tc, :sender_name, :subject, :msg, :status)");
			$stmt->bindparam(":tc", $tc);
			$stmt->bindparam(":sender_name", $sender_name);
			$stmt->bindparam(":subject", $subject);
			$stmt->bindparam(":msg", $msg);
			$stmt->bindparam(":status", $status);
			$stmt->execute();
			return $stmt;
		} catch (PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function message($sender_name, $reci_name, $subject, $msg)
	{
		try {

			$stmt = $this->conn->prepare("INSERT INTO message(sender_name,reci_name,subject,msg) 
			                                             VALUES(:sender_name, :reci_name, :subject, :msg)");

			$stmt->bindparam(":sender_name", $sender_name);
			$stmt->bindparam(":reci_name", $reci_name);
			$stmt->bindparam(":subject", $subject);
			$stmt->bindparam(":msg", $msg);
			$stmt->execute();
			return $stmt;
		} catch (PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function del($id)
	{
		try {

			$stmt = $this->conn->prepare("DELETE FROM account WHERE id = :id");

			$stmt->bindparam(":id", $id);

			$stmt->execute();
			return $stmt;
		} catch (PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function update($fname, $pin, $lname, $uname, $upass, $upass2, $phone, $email, $type, $work, $acc_no, $addr, $sex, $dob, $marry, $t_bal, $a_bal, $cot, $tax, $lppi, $imf, $currency)
	{
		try {
			$id = $_GET['id'];
			$upass = md5($upass);
			$stmt = $this->conn->prepare("UPDATE account SET fname = :fname, pin = :pin, lname = :lname, uname = :uname, upass = :upass, upass2 = :upass2, phone = :phone, email = :email, type = :type, work = :work, acc_no = :acc_no, addr = :addr, sex = :sex, dob = :dob, marry = :marry, t_bal = :t_bal, a_bal = :a_bal, cot = :cot, tax = :tax, lppi = :lppi, imf = :imf, currency = :currency, WHERE id='$id'");

			$stmt->bindparam(":fname", $fname);
			$stmt->bindparam(":pin", $pin);
			$stmt->bindparam(":lname", $lname);
			$stmt->bindparam(":uname", $uname);
			$stmt->bindparam(":upass", $upass);
			$stmt->bindparam(":upass2", $upass2);
			$stmt->bindparam(":phone", $phone);
			$stmt->bindparam(":email", $email);
			$stmt->bindparam(":type", $type);
			$stmt->bindparam(":work", $work);
			$stmt->bindparam(":acc_no", $acc_no);
			$stmt->bindparam(":addr", $addr);
			$stmt->bindparam(":sex", $sex);
			$stmt->bindparam(":dob", $dob);
			$stmt->bindparam(":marry", $marry);
			$stmt->bindparam(":t_bal", $t_bal);
			$stmt->bindparam(":a_bal", $a_bal);
			$stmt->bindparam(":cot", $cot);
			$stmt->bindparam(":tax", $tax);
			$stmt->bindparam(":lppi", $lppi);
			$stmt->bindparam(":imf", $imf);
			$stmt->bindparam(":currency", $currency);
			$stmt->execute();
			return $stmt;
		} catch (PDOException $ex) {
			echo $ex->getMessage();
		}
	}


	public function updatepic($image, $pp)
	{
		try {
			$id = $_GET['id'];
			$stmt = $this->conn->prepare("UPDATE account SET image = :image, pp = :pp WHERE id='$id'");

			$stmt->bindparam(":image", $image);
			$stmt->bindparam(":pp", $pp);
			$stmt->execute();
			return $stmt;
		} catch (PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function upstat($status)
	{
		try {
			$id = $_GET['id'];
			$stmt = $this->conn->prepare("UPDATE account SET status = :status WHERE id='$id'");

			$stmt->bindparam(":status", $status);
			$stmt->execute();
			return $stmt;
		} catch (PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function upgrade($name, $phone, $email, $addr, $tawk2, $qr)
	{
		try {
			$id = $_GET['id'];
			$stmt = $this->conn->prepare("UPDATE site SET name = :name, phone = :phone, email = :email, addr = :addr, tawk2 = :tawk2, qr = :qr WHERE id='$id'");

			$stmt->bindparam(":name", $name);
			$stmt->bindparam(":phone", $phone);
			$stmt->bindparam(":email", $email);
			$stmt->bindparam(":addr", $addr);
			$stmt->bindparam(":tawk2", $tawk2);
			$stmt->bindparam(":qr", $qr);
			$stmt->execute();
			return $stmt;
		} catch (PDOException $ex) {
			echo $ex->getMessage();
		}
	}


	public function upgradecd($code1, $code2, $code3, $code1b, $code2b, $code3b)
	{
		try {
			$id = $_GET['id'];
			$stmt = $this->conn->prepare("UPDATE site SET code1 = :code1, code2 = :code2, code3 = :code3, code1b = :code1b, code2b = :code2b, code3b = :code3b WHERE id='$id'");

			$stmt->bindparam(":code1", $code1);
			$stmt->bindparam(":code2", $code2);
			$stmt->bindparam(":code3", $code3);
			$stmt->bindparam(":code1b", $code1b);
			$stmt->bindparam(":code2b", $code2b);
			$stmt->bindparam(":code3b", $code3b);
			$stmt->execute();
			return $stmt;
		} catch (PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function updates($ccard, $ccdate, $cvv, $loan, $lodur, $intra)
	{
		try {
			$id = $_GET['id'];
			$stmt = $this->conn->prepare("UPDATE account SET ccard = :ccard, ccdate = :ccdate, cvv = :cvv, loan = :loan, lodur = :lodur, intra = :intra WHERE id='$id'");

			$stmt->bindparam(":ccard", $ccard);
			$stmt->bindparam(":ccdate", $ccdate);
			$stmt->bindparam(":cvv", $cvv);
			$stmt->bindparam(":loan", $loan);
			$stmt->bindparam(":lodur", $lodur);
			$stmt->bindparam(":intra", $intra);
			$stmt->execute();
			return $stmt;
		} catch (PDOException $ex) {
			echo $ex->getMessage();
		}
	}


	public function updatecd($amount, $date, $time, $remarks, $sender_name)
	{
		try {
			$id = $_GET['id'];
			$stmt = $this->conn->prepare("UPDATE alerts SET amount = :amount, date = :date, time = :time, remarks = :remarks, sender_name = :sender_name WHERE id='$id'");

			$stmt->bindparam(":amount", $amount);
			$stmt->bindparam(":date", $date);
			$stmt->bindparam(":time", $time);
			$stmt->bindparam(":remarks", $remarks);
			$stmt->bindparam(":sender_name", $sender_name);
			$stmt->execute();
			return $stmt;
		} catch (PDOException $ex) {
			echo $ex->getMessage();
		}
	}


	public function updatetf($amount, $date, $acc_no, $remarks, $bank_name, $acc_name)
	{
		try {
			$id = $_GET['id'];
			$stmt = $this->conn->prepare("UPDATE transfer SET amount = :amount, date = :date, acc_no = :acc_no, remarks = :remarks, bank_name = :bank_name, acc_name = :acc_name WHERE id='$id'");

			$stmt->bindparam(":amount", $amount);
			$stmt->bindparam(":date", $date);
			$stmt->bindparam(":acc_no", $acc_no);
			$stmt->bindparam(":remarks", $remarks);
			$stmt->bindparam(":bank_name", $bank_name);
			$stmt->bindparam(":acc_name", $acc_name);
			$stmt->execute();
			return $stmt;
		} catch (PDOException $ex) {
			echo $ex->getMessage();
		}
	}


	public function login($email, $upass)
	{
		try {
			$stmt = $this->conn->prepare("SELECT * FROM admin WHERE email=:email");
			$stmt->execute(array(":email" => $email));
			$userRow = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($stmt->rowCount() == 1) {
				if ($userRow['verified_count'] == "Y") {
					if ($userRow['upass'] == md5($upass)) {
						$_SESSION['userSession'] = $userRow['id'];
						return true;
					} else {
						header("Location: login.php?error");
						exit;
					}
				} else {
					header("Location: login.php?inactive");
					exit;
				}
			} else {
				header("Location: login.php?error");
				exit;
			}
		} catch (PDOException $ex) {
			echo $ex->getMessage();
		}
	}


	public function is_logged_in()
	{
		if (isset($_SESSION['userSession'])) {
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

	function send_mail($email, $messag, $subject, $template_type = null, $template_data = null)
	{
		include_once dirname(__DIR__, 2) . '/config.php';

		$getSetting = function ($key, $default = null) {
			try {
				$stmt = $this->conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = :k LIMIT 1");
				$stmt->execute(array(':k' => $key));
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($row && array_key_exists('setting_value', $row)) {
					return $row['setting_value'];
				}
			} catch (Exception $e) {
			}

			try {
				$stmt = $this->conn->prepare("SELECT `value` FROM site_settings WHERE `key` = :k LIMIT 1");
				$stmt->execute(array(':k' => $key));
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
			return !in_array($v, array('0', 'false', 'off', 'disabled', 'no'), true);
		};

		if ($template_type && !$isEnabled('email', $template_type)) {
			return false;
		}

		if ($template_data) {
			$templateClassPath = dirname(__DIR__) . '/class.email-template.php';
			if (file_exists($templateClassPath)) {
				require_once $templateClassPath;
				if (class_exists('EmailTemplate')) {
					$bankName = 'Banking System';
					$siteUrl = isset($APP_CONFIG['site_url']) ? trim((string)$APP_CONFIG['site_url']) : '';
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
						// Keep default bank name.
					}

					$template = new EmailTemplate(array(
						'bankName' => $bankName,
						'supportEmail' => isset($APP_CONFIG['smtp']['from']) ? $APP_CONFIG['smtp']['from'] : 'support@example.com',
						'siteUrl' => $siteUrl
					));

					$messag = $template->renderTemplate($template_type, $template_data);
				}
			}
		}

		if ($template_type) {
			require_once dirname(__DIR__) . '/notification-template-helper.php';
			if (function_exists('notification_template_render_override')) {
				$templatePayload = is_array($template_data) ? $template_data : array();
				$context = array(
					'bank_name' => isset($bankName) ? (string)$bankName : 'Banking System',
					'support_email' => isset($APP_CONFIG['smtp']['reply_to']) ? $APP_CONFIG['smtp']['reply_to'] : (isset($APP_CONFIG['smtp']['from']) ? $APP_CONFIG['smtp']['from'] : 'support@bank.com'),
					'site_url' => isset($siteUrl) ? (string)$siteUrl : (isset($APP_CONFIG['site_url']) ? trim((string)$APP_CONFIG['site_url']) : ''),
				);
				$override = notification_template_render_override((string)$template_type, $templatePayload, (string)$subject, $getSetting, $context);
				if (!empty($override['subject'])) {
					$subject = (string)$override['subject'];
				}
				if (!empty($override['body'])) {
					$messag = (string)$override['body'];
				}
			}
		}

		$from = isset($APP_CONFIG['smtp']['from']) ? $APP_CONFIG['smtp']['from'] : 'noreply@localhost';
		$fropin = isset($APP_CONFIG['smtp']['from_name']) ? $APP_CONFIG['smtp']['from_name'] : 'Banking System';
		$replyTo = isset($APP_CONFIG['smtp']['reply_to']) ? $APP_CONFIG['smtp']['reply_to'] : $from;

		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= "From: " . $fropin . " <" . $from . ">" . "\r\n";
		$headers .= "Reply-To: " . $replyTo . "\r\n";

		$emailSent = mail($email, $subject, $messag, $headers);

		if ($template_type && $isEnabled('sms', $template_type)) {
			try {
				require_once dirname(__DIR__) . '/class.sms.php';
				if (class_exists('SmsGateway')) {
					$phone = '';
					if (is_array($template_data) && !empty($template_data['phone'])) {
						$phone = (string)$template_data['phone'];
					} else {
						$accStmt = $this->conn->prepare("SELECT phone FROM account WHERE email = :email LIMIT 1");
						$accStmt->execute(array(':email' => $email));
						$acc = $accStmt->fetch(PDO::FETCH_ASSOC);
						$phone = $acc['phone'] ?? '';
					}

					if ($phone !== '') {
						$sms = new SmsGateway($this->conn, $APP_CONFIG);
						$sms->sendTemplate($phone, $template_type, is_array($template_data) ? $template_data : array(), $subject);
					}
				}
			} catch (Exception $e) {
				error_log('Admin SMS send exception: ' . $e->getMessage());
			}
		}

		return $emailSent;
	}	

	// ── Transfer Status Management Methods ──────────────────────────────────────
	/**
	 * Update transfer status with audit trail
	 */
	public function updateTransferStatus($transferId, $newStatus, $adminEmail, $notes = '', $autoUpdateEnabled = 0, $autoUpdateDelay = 0)
	{
		try {
			$transferId = (int)$transferId;
			$newStatus = trim((string)$newStatus);
			$adminEmail = trim((string)$adminEmail);
			$notes = trim((string)$notes);
			$autoUpdateEnabled = (int)$autoUpdateEnabled;
			$autoUpdateDelay = (int)$autoUpdateDelay;

			if ($transferId <= 0 || $newStatus === '') {
				return false;
			}

			$allowedStatuses = ['pending', 'processing', 'completed', 'successful', 'failed', 'cancelled', 'reversed'];
			if (!in_array(strtolower($newStatus), $allowedStatuses, true)) {
				return false;
			}

			// Ensure newer audit columns exist. Missing columns used to cause false failures.
			try {
				$this->conn->exec("ALTER TABLE transfer ADD COLUMN status_updated_by VARCHAR(190) NULL DEFAULT NULL");
			} catch (PDOException $e) {
			}
			try {
				$this->conn->exec("ALTER TABLE transfer ADD COLUMN status_updated_at DATETIME NULL DEFAULT NULL");
			} catch (PDOException $e) {
			}
			try {
				$this->conn->exec("ALTER TABLE transfer ADD COLUMN status_notes TEXT NULL DEFAULT NULL");
			} catch (PDOException $e) {
			}
			try {
				$this->conn->exec("ALTER TABLE transfer ADD COLUMN auto_update_enabled TINYINT(1) NOT NULL DEFAULT 0");
			} catch (PDOException $e) {
			}
			try {
				$this->conn->exec("ALTER TABLE transfer ADD COLUMN auto_update_at DATETIME NULL DEFAULT NULL");
			} catch (PDOException $e) {
			}

			// Ensure history table exists, but do not fail transfer update if logging fails.
			try {
				$this->conn->exec("CREATE TABLE IF NOT EXISTS transfer_status_history (
					id INT AUTO_INCREMENT PRIMARY KEY,
					transfer_id INT NOT NULL,
					old_status VARCHAR(20) NULL DEFAULT NULL,
					new_status VARCHAR(20) NOT NULL,
					changed_by VARCHAR(190) NULL DEFAULT NULL,
					changed_at DATETIME NOT NULL,
					notes TEXT NULL,
					INDEX idx_transfer_status_history_transfer_id (transfer_id),
					INDEX idx_transfer_status_history_changed_at (changed_at)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
			} catch (PDOException $e) {
			}

			// Get current status for audit trail
			$currentStmt = $this->conn->prepare("SELECT status FROM transfer WHERE id = :id LIMIT 1");
			$currentStmt->execute([':id' => $transferId]);
			$currentRow = $currentStmt->fetch(PDO::FETCH_ASSOC);
			if (!$currentRow) {
				return false;
			}
			$oldStatus = $currentRow['status'] ?? null;

			// Update transfer record
			$updateStmt = $this->conn->prepare("UPDATE transfer SET 
				status = :status,
				status_updated_by = :updated_by,
				status_updated_at = NOW(),
				status_notes = :notes,
				auto_update_enabled = :au_enabled,
				auto_update_at = IF(:au_enabled = 1 AND :au_delay > 0, DATE_ADD(NOW(), INTERVAL :au_delay MINUTE), NULL)
				WHERE id = :id");

			$autoUpdateAt = null;
			if ($autoUpdateEnabled && $autoUpdateDelay > 0) {
				$autoUpdateAt = date('Y-m-d H:i:s', time() + ($autoUpdateDelay * 60));
			}

			$updateStmt->execute([
				':status' => $newStatus,
				':updated_by' => $adminEmail,
				':notes' => $notes ?: null,
				':au_enabled' => $autoUpdateEnabled,
				':au_delay' => $autoUpdateDelay,
				':id' => $transferId
			]);

			// Log to transfer status history (best-effort; should not block successful status update)
			try {
				$historyStmt = $this->conn->prepare("INSERT INTO transfer_status_history 
					(transfer_id, old_status, new_status, changed_by, changed_at, notes)
					VALUES (:transfer_id, :old_status, :new_status, :changed_by, NOW(), :notes)");
				$historyStmt->execute([
					':transfer_id' => $transferId,
					':old_status' => $oldStatus,
					':new_status' => $newStatus,
					':changed_by' => $adminEmail,
					':notes' => $notes ?: null
				]);
			} catch (PDOException $e) {
				error_log('Transfer status history log warning: ' . $e->getMessage());
			}

			return true;
		} catch (PDOException $e) {
			error_log('Transfer status update error: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Get transfer status history
	 */
	public function getTransferStatusHistory($transferId)
	{
		try {
			$stmt = $this->conn->prepare("SELECT * FROM transfer_status_history 
				WHERE transfer_id = :transfer_id 
				ORDER BY changed_at DESC");
			$stmt->execute([':transfer_id' => (int)$transferId]);
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			error_log('Transfer history retrieval error: ' . $e->getMessage());
			return [];
		}
	}

	/**
	 * Get list of transfers pending auto-update
	 */
	public function getTransfersPendingAutoUpdate()
	{
		try {
			$stmt = $this->conn->prepare("SELECT id, status, auto_update_at, auto_update_enabled 
				FROM transfer 
				WHERE auto_update_enabled = 1 
				AND auto_update_at IS NOT NULL 
				AND auto_update_at <= NOW()
				AND status != 'completed' 
				AND status != 'successful'
				AND status != 'failed'
				AND status != 'cancelled'
				LIMIT 100");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			error_log('Auto-update transfer retrieval error: ' . $e->getMessage());
			return [];
		}
	}

	/**
	 * Auto-update a transfer to target status
	 */
	public function autoUpdateTransferStatus($transferId, $targetStatus = 'successful')
	{
		try {
			$transferId = (int)$transferId;
			$targetStatus = trim((string)$targetStatus);

			$updateStmt = $this->conn->prepare("UPDATE transfer SET 
				status = :status,
				status_updated_by = 'system',
				status_updated_at = NOW(),
				status_notes = 'Automatically updated by scheduled task',
				auto_update_enabled = 0,
				auto_update_at = NULL
				WHERE id = :id AND auto_update_enabled = 1 AND auto_update_at <= NOW()");

			$updateStmt->execute([
				':status' => $targetStatus,
				':id' => $transferId
			]);

			// Log to history
			$historyStmt = $this->conn->prepare("INSERT INTO transfer_status_history 
				(transfer_id, old_status, new_status, changed_by, changed_at, notes)
				SELECT id, status, :new_status, 'system', NOW(), 'Auto-update via scheduled task'
				FROM transfer WHERE id = :id");

			$historyStmt->execute([
				':new_status' => $targetStatus,
				':id' => $transferId
			]);

			return true;
		} catch (PDOException $e) {
			error_log('Auto-update transfer error: ' . $e->getMessage());
			return false;
		}
	}

	// ── IBAN Management Methods ─────────────────────────────────────────────────
	/**
	 * Update customer account IBAN
	 */
	public function updateCustomerIBAN($customerAccountId, $newIBAN, $adminEmail, $reason = '')
	{
		try {
			$customerAccountId = (int)$customerAccountId;
			$newIBAN = trim((string)$newIBAN);
			$adminEmail = trim((string)$adminEmail);
			$reason = trim((string)$reason);

			if ($customerAccountId <= 0 || $newIBAN === '') {
				return false;
			}

			// Get old IBAN for audit trail
			$currentStmt = $this->conn->prepare("SELECT iban FROM customer_accounts WHERE id = :id LIMIT 1");
			$currentStmt->execute([':id' => $customerAccountId]);
			$currentRow = $currentStmt->fetch(PDO::FETCH_ASSOC);
			$oldIBAN = $currentRow['iban'] ?? null;

			// Update customer account with schema compatibility for legacy installs.
			$colsStmt = $this->conn->query("SHOW COLUMNS FROM customer_accounts");
			$caCols = [];
			if ($colsStmt) {
				while ($c = $colsStmt->fetch(PDO::FETCH_ASSOC)) {
					$name = (string)($c['Field'] ?? '');
					if ($name !== '') {
						$caCols[$name] = true;
					}
				}
			}

			$setParts = ['iban = :iban'];
			$params = [
				':iban' => $newIBAN,
				':id' => $customerAccountId,
			];

			if (isset($caCols['iban_custom'])) {
				$setParts[] = 'iban_custom = 1';
			}
			if (isset($caCols['iban_updated_by'])) {
				$setParts[] = 'iban_updated_by = :updated_by';
				$params[':updated_by'] = $adminEmail;
			}
			if (isset($caCols['iban_updated_at'])) {
				$setParts[] = 'iban_updated_at = NOW()';
			}

			$updateSql = "UPDATE customer_accounts SET " . implode(', ', $setParts) . " WHERE id = :id";
			$updateStmt = $this->conn->prepare($updateSql);
			$updateStmt->execute($params);

			// Log to IBAN change history when table exists.
			try {
				$tblStmt = $this->conn->query("SHOW TABLES LIKE 'iban_change_history'");
				if ($tblStmt && $tblStmt->fetch(PDO::FETCH_NUM)) {
					$historyStmt = $this->conn->prepare("INSERT INTO iban_change_history 
						(customer_account_id, old_iban, new_iban, changed_by, changed_at, change_reason)
						VALUES (:account_id, :old_iban, :new_iban, :changed_by, NOW(), :reason)");
					$historyStmt->execute([
						':account_id' => $customerAccountId,
						':old_iban' => $oldIBAN,
						':new_iban' => $newIBAN,
						':changed_by' => $adminEmail,
						':reason' => $reason ?: 'Manual update by admin'
					]);
				}
			} catch (PDOException $ignored) {
				// Keep update successful even if optional audit table is unavailable.
			}

			return true;
		} catch (PDOException $e) {
			error_log('IBAN update error: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Get IBAN change history
	 */
	public function getIBANChangeHistory($customerAccountId)
	{
		try {
			$stmt = $this->conn->prepare("SELECT * FROM iban_change_history 
				WHERE customer_account_id = :account_id 
				ORDER BY changed_at DESC");
			$stmt->execute([':account_id' => (int)$customerAccountId]);
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			error_log('IBAN history retrieval error: ' . $e->getMessage());
			return [];
		}
	}

	/**
	 * Get transfer settings
	 */
	public function getTransferSetting($settingKey, $default = '')
	{
		try {
			$this->conn->exec("CREATE TABLE IF NOT EXISTS transfer_settings (
				id INT AUTO_INCREMENT PRIMARY KEY,
				setting_key VARCHAR(100) NOT NULL,
				setting_value TEXT NULL,
				updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				UNIQUE KEY uq_transfer_setting_key (setting_key)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

			$stmt = $this->conn->prepare("SELECT setting_value FROM transfer_settings 
				WHERE setting_key = :key LIMIT 1");
			$stmt->execute([':key' => trim((string)$settingKey)]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row ? ($row['setting_value'] ?? $default) : $default;
		} catch (PDOException $e) {
			error_log('Transfer setting retrieval error: ' . $e->getMessage());
			return $default;
		}
	}

	/**
	 * Update transfer setting
	 */
	public function updateTransferSetting($settingKey, $settingValue)
	{
		try {
			$this->conn->exec("CREATE TABLE IF NOT EXISTS transfer_settings (
				id INT AUTO_INCREMENT PRIMARY KEY,
				setting_key VARCHAR(100) NOT NULL,
				setting_value TEXT NULL,
				updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				UNIQUE KEY uq_transfer_setting_key (setting_key)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

			$key = trim((string)$settingKey);
			$upd = $this->conn->prepare("UPDATE transfer_settings SET setting_value = :value, updated_at = NOW() WHERE setting_key = :key");
			$upd->execute([':key' => $key, ':value' => $settingValue]);

			if ((int)$upd->rowCount() === 0) {
				$ins = $this->conn->prepare("INSERT INTO transfer_settings (setting_key, setting_value, updated_at) VALUES (:key, :value, NOW())");
				$ins->execute([':key' => $key, ':value' => $settingValue]);
			}

			return true;
		} catch (PDOException $e) {
			error_log('Transfer setting update error: ' . $e->getMessage());
			return false;
		}
	}
}
