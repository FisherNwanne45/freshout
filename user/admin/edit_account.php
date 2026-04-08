<?php
session_start();
include_once ('session.php');
if(!isset($_SESSION['email'])){ header("Location: login.php"); exit(); }
require_once 'admin.php';
require dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__) . '/partials/auto-migrate.php';

$reg_user = new USER();

// Load transaction code settings
function ea_setting_get(mysqli $conn, string $key, string $default=''): string {
    $safe=$conn->real_escape_string($key);
    $res=$conn->query("SELECT `value` FROM site_settings WHERE `key`='$safe' LIMIT 1");
    if($res && $res->num_rows>0){ $r=$res->fetch_assoc(); return (string)($r['value']??$default); }
    return $default;
}
$txMaxCodes = (int)ea_setting_get($conn, 'tx_max_codes', '3');
$txCodeNames = [];
$defaults = [0,'COT','TAX','IMF','LPPI','Code 5'];
for ($i=1;$i<=5;$i++) $txCodeNames[$i] = ea_setting_get($conn, "tx_code{$i}_name", $defaults[$i]);

$accountStatusOptions = ['Active','Dormant/Inactive','Disabled','Closed'];
$loginMethodOptions = [
  'pin' => 'PIN',
  'otp' => 'OTP',
];
$authMethodOptions = [
    '' => 'Default (system setting)',
    'pin'       => 'PIN only',
    'otp'       => 'OTP only',
    'codes'     => 'Codes only (' . $txMaxCodes . ' code' . ($txMaxCodes===1?'':'s') . ')',
    'codes_otp' => 'Codes + OTP',
    'codes_pin' => 'Codes + PIN',
];

if(isset($_GET['id'])){
    $id = (int)$_GET['id'];
    $stmt = $reg_user->runQuery("SELECT * FROM account WHERE id='$id'");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
}

$msg = '';
$imgUpdate = '';
$walletMsg = '';

// ── Admin: upsert (add/edit) a wallet balance for this user ─────────────────
if (isset($_POST['wallet_upsert']) && isset($_GET['id'])) {
    $wId = (int)$_GET['id'];
    $wAccNo   = trim((string)($row['acc_no'] ?? ''));
    $wCode    = strtoupper(trim((string)($_POST['w_currency_code'] ?? '')));
    $wBalance = trim((string)($_POST['w_balance'] ?? '0'));
    if ($wAccNo !== '' && preg_match('/^[A-Z0-9]{2,10}$/', $wCode) && is_numeric($wBalance)) {
        $safeBal = (float)$wBalance;
        $safeAcc = $conn->real_escape_string($wAccNo);
        $safeCod = $conn->real_escape_string($wCode);
        $conn->query("INSERT INTO account_balances (acc_no, currency_code, balance)
            VALUES ('$safeAcc', '$safeCod', $safeBal)
            ON DUPLICATE KEY UPDATE balance = $safeBal");
        $walletMsg = '<div class="rounded-lg bg-green-50 border border-green-200 px-4 py-2 text-green-700 text-sm mb-4">Wallet updated: '
            . htmlspecialchars($wCode) . ' balance set to ' . number_format($safeBal, 2) . '.</div>';
    } else {
        $walletMsg = '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">Invalid wallet data.</div>';
    }
}

// ── Admin: delete a wallet row ────────────────────────────────────────────────
if (isset($_POST['wallet_delete']) && isset($_GET['id'])) {
    $dAccNo = trim((string)($row['acc_no'] ?? ''));
    $dCode  = strtoupper(trim((string)($_POST['del_currency_code'] ?? '')));
    if ($dAccNo !== '' && preg_match('/^[A-Z0-9]{2,10}$/', $dCode)) {
        $safeAcc2 = $conn->real_escape_string($dAccNo);
        $safeCod2 = $conn->real_escape_string($dCode);
        $conn->query("DELETE FROM account_balances WHERE acc_no = '$safeAcc2' AND currency_code = '$safeCod2' LIMIT 1");
        $conn->query("DELETE FROM customer_accounts WHERE owner_acc_no = '$safeAcc2' AND currency_code = '$safeCod2' LIMIT 1");
        $walletMsg = '<div class="rounded-lg bg-yellow-50 border border-yellow-200 px-4 py-2 text-yellow-700 text-sm mb-4">'
            . htmlspecialchars($dCode) . ' wallet removed.</div>';
    }
}

// ── Admin: Update IBAN for customer account ────────────────────────────────────
$ibanMsg = '';
if (isset($_POST['update_iban']) && isset($_GET['id'])) {
    $accNo = trim((string)($row['acc_no'] ?? ''));
    $custAccId = (int)($_POST['customer_account_id'] ?? 0);
    $newIban = trim((string)($_POST['new_iban'] ?? ''));
    $ibanReason = trim((string)($_POST['iban_reason'] ?? ''));
    
    if ($custAccId > 0 && $newIban !== '' && strlen($newIban) <= 34) {
        if ($reg_user->updateCustomerIBAN($custAccId, $newIban, $_SESSION['email'], $ibanReason)) {
            $ibanMsg = '<div class="rounded-lg bg-green-50 border border-green-200 px-4 py-2 text-green-700 text-sm mb-4">IBAN updated successfully: ' . htmlspecialchars($newIban) . '</div>';
        } else {
            $ibanMsg = '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">Failed to update IBAN.</div>';
        }
    } else {
        $ibanMsg = '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">Invalid IBAN or account data.</div>';
    }
}

// Update profile fields handler
if(isset($_POST['update'])){
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    $fname    = trim($_POST['fname']);
    $mname    = trim($_POST['mname']);
    $pin      = $mname;
    $lname    = trim($_POST['lname']);
    $uname    = trim($_POST['uname']);
    $upass    = $_POST['upass'];
    $upass2   = trim($_POST['upass2']);
    $phone    = trim($_POST['phone']);
    $email    = trim($_POST['email']);
    $type     = trim($_POST['type']);
    $work     = trim($_POST['work']);
    $acc_no   = trim($_POST['acc_no']);
    $addr     = trim($_POST['addr']);
    $sex      = trim($_POST['sex']);
    $dob      = trim($_POST['dob']);
    $marry    = trim($_POST['marry']);
    $t_bal    = trim($_POST['t_bal']);
    $a_bal    = trim($_POST['a_bal']);
    $currency = trim($_POST['currency']);
    $cot      = trim($_POST['cot']);
    $tax      = trim($_POST['tax']);
    $imf      = trim($_POST['imf']);
    $lppi     = trim($_POST['lppi']);
    $code5    = trim($_POST['code5'] ?? '');
    $status      = trim($_POST['status']);
    $login_method = strtolower(trim($_POST['login_method'] ?? 'pin'));
    $auth_method = trim($_POST['auth_method'] ?? '');

    if (!in_array($status, $accountStatusOptions, true)) {
      $status = 'Active';
    }
    if (!array_key_exists($login_method, $loginMethodOptions)) {
      $login_method = 'pin';
    }
    if (!array_key_exists($auth_method, $authMethodOptions)) {
      $auth_method = 'codes';
    }

    if($upass !== '') $upassHash = md5($upass);
    else {
        $cur = $reg_user->runQuery("SELECT upass FROM account WHERE id='$id'");
        $cur->execute();
        $curRow = $cur->fetch(PDO::FETCH_ASSOC);
        $upassHash = $curRow['upass'] ?? '';
    }

    $q = $reg_user->runQuery("UPDATE account SET
      fname=:fname, mname=:mname, pin=:pin, lname=:lname, uname=:uname, upass=:upass, upass2=:upass2,
        phone=:phone, email=:email, type=:type, work=:work, acc_no=:acc_no, addr=:addr,
        sex=:sex, dob=:dob, marry=:marry, t_bal=:t_bal, a_bal=:a_bal, currency=:currency,
        cot=:cot, tax=:tax, lppi=:lppi, imf=:imf, code5=:code5,
        status=:status, login_method=:login_method, auth_method=:auth_method
        WHERE id=:id");
    $q->execute([
        ':fname'=>$fname,':mname'=>$mname,':pin'=>$pin,':lname'=>$lname,':uname'=>$uname,':upass'=>$upassHash,':upass2'=>$upass2,
        ':phone'=>$phone,':email'=>$email,':type'=>$type,':work'=>$work,':acc_no'=>$acc_no,':addr'=>$addr,
        ':sex'=>$sex,':dob'=>$dob,':marry'=>$marry,':t_bal'=>$t_bal,':a_bal'=>$a_bal,':currency'=>$currency,
        ':cot'=>$cot,':tax'=>$tax,':lppi'=>$lppi,':imf'=>$imf,':code5'=>$code5,
        ':status'=>$status,':login_method'=>$login_method,':auth_method'=>$auth_method,
        ':id'=>$id,
    ]);

    $msg = '<div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-700 text-sm mb-4"><i class="fa-solid fa-circle-check mr-2"></i>Account updated successfully.</div>';

    $stmt = $reg_user->runQuery("SELECT * FROM account WHERE id='$id'");
    $stmt->execute(); $row = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Image update handler
if(isset($_POST['update_images'])){
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $curStmt = $reg_user->runQuery("SELECT image,pp FROM account WHERE id='$id'");
    $curStmt->execute(); $curRow = $curStmt->fetch(PDO::FETCH_ASSOC);
    $image = $curRow['image'] ?? '';
    $pp    = $curRow['pp']    ?? '';
    $allowed = ['jpg','jpeg','png','gif','webp'];
    $imgErrors = [];

    foreach(['image','pp'] as $field){
        if(!empty($_FILES[$field]['name'])){
            $ext=strtolower(pathinfo($_FILES[$field]['name'],PATHINFO_EXTENSION));
            if(!in_array($ext,$allowed)){ $imgErrors[]="Invalid file type for $field."; }
            elseif($_FILES[$field]['size']>2097152){ $imgErrors[]="$field must be under 2MB."; }
            else { $fn=basename($_FILES[$field]['name']); move_uploaded_file($_FILES[$field]['tmp_name'],__DIR__.'/foto/'.$fn); $$field=$fn; }
        }
    }

    if(empty($imgErrors)){
        $q = $reg_user->runQuery("UPDATE account SET image=:img, pp=:pp WHERE id=:id");
        $q->execute([':img'=>$image,':pp'=>$pp,':id'=>$id]);
        $imgUpdate = '<div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-700 text-sm mb-4"><i class="fa-solid fa-circle-check mr-2"></i>Images updated.</div>';
    } else {
        $imgUpdate = '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm mb-4">'.implode('<br>',$imgErrors).'</div>';
    }

    $stmt = $reg_user->runQuery("SELECT * FROM account WHERE id='$id'");
    $stmt->execute(); $row = $stmt->fetch(PDO::FETCH_ASSOC);
}

$pageTitle = 'Edit Account';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if($msg) echo $msg; ?>
<?php if($imgUpdate) echo $imgUpdate; ?>

<?php if(!empty($row)): ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-5xl mb-6">
  <div class="flex items-center justify-between mb-5">
    <h2 class="font-semibold text-gray-800">Edit Account &mdash; <?= htmlspecialchars(($row['fname']??'').' '.($row['lname']??'')) ?></h2>
    <a href="view_account.php" class="text-sm text-blue-600 hover:underline"><i class="fa-solid fa-arrow-left mr-1"></i>Back</a>
  </div>
  <form method="POST">
    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Personal Information</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
      <div><label class="block text-xs font-medium text-gray-700 mb-1">First Name</label>
        <input type="text" name="fname" value="<?= htmlspecialchars($row['fname']??'') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Last Name</label>
        <input type="text" name="lname" value="<?= htmlspecialchars($row['lname']??'') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Gender</label>
        <select name="sex" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <?php foreach(['Male','Female','Other'] as $s): ?><option <?= ($row['sex']??'')===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
        </select></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Date of Birth</label>
        <input type="text" name="dob" value="<?= htmlspecialchars($row['dob']??'') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Marital Status</label>
        <input type="text" name="marry" value="<?= htmlspecialchars($row['marry']??'') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Phone</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($row['phone']??'') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($row['email']??'') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Occupation</label>
        <input type="text" name="work" value="<?= htmlspecialchars($row['work']??'') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
      <div class="lg:col-span-3"><label class="block text-xs font-medium text-gray-700 mb-1">Address</label>
        <input type="text" name="addr" value="<?= htmlspecialchars($row['addr']??'') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
    </div>

    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Account Details</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Username</label>
        <input type="text" name="uname" value="<?= htmlspecialchars($row['uname']??'') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">New Password <span class="font-normal text-gray-400">(blank = keep current)</span></label>
        <input type="password" name="upass" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Secondary Password</label>
        <input type="text" name="upass2" value="<?= htmlspecialchars($row['upass2']??'') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Security PIN (4-digit)</label>
        <input type="password" name="mname" maxlength="4" inputmode="numeric" value="<?= htmlspecialchars($row['pin']??$row['mname']??'') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Account Type</label>
        <input type="text" name="type" value="<?= htmlspecialchars($row['type']??'') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Account Number</label>
        <input type="text" name="acc_no" value="<?= htmlspecialchars($row['acc_no']??'') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Currency</label>
        <input type="text" name="currency" value="<?= htmlspecialchars($row['currency']??'') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Total Balance</label>
        <input type="number" step="0.01" name="t_bal" value="<?= htmlspecialchars($row['t_bal']??'') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Available Balance</label>
        <input type="number" step="0.01" name="a_bal" value="<?= htmlspecialchars($row['a_bal']??'') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
    </div>

    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Account Status &amp; Auth Method</p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Account Status</label>
        <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <?php foreach($accountStatusOptions as $opt): ?>
          <option value="<?= htmlspecialchars($opt) ?>" <?= ($row['status']??'')===$opt?'selected':'' ?>><?= htmlspecialchars($opt) ?></option>
          <?php endforeach; ?>
        </select>
        <p class="text-xs text-gray-400 mt-1">Controls whether the user can log in and perform transactions.</p>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Login Method</label>
        <select name="login_method" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <?php foreach($loginMethodOptions as $k=>$v): ?>
          <option value="<?= htmlspecialchars($k) ?>" <?= (($row['login_method'] ?? 'pin') === $k)?'selected':'' ?>><?= htmlspecialchars($v) ?></option>
          <?php endforeach; ?>
        </select>
        <p class="text-xs text-gray-400 mt-1">Controls the second step for login: PIN or OTP.</p>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Transaction Auth Method</label>
        <select name="auth_method" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <?php foreach($authMethodOptions as $k=>$v): ?>
          <option value="<?= htmlspecialchars($k) ?>" <?= ($row['auth_method']??'')===$k?'selected':'' ?>><?= htmlspecialchars($v) ?></option>
          <?php endforeach; ?>
        </select>
        <p class="text-xs text-gray-400 mt-1">How this user must authenticate during transfers.</p>
      </div>
    </div>

    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Transaction Codes <span class="normal-case font-normal">(<?= $txMaxCodes ?> active)</span></p>
    <?php
    $colMap = [1=>'cot',2=>'tax',3=>'imf',4=>'lppi',5=>'code5'];
    ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
      <?php for($i=1;$i<=5;$i++): $col=$colMap[$i]; $inactive=$i>$txMaxCodes; ?>
      <div <?= $inactive?'class="opacity-50"':'' ?>>
        <label class="block text-xs font-medium text-gray-700 mb-1">
          <?= htmlspecialchars($txCodeNames[$i]) ?> Code
          <?php if($inactive): ?><span class="text-gray-400 font-normal">(inactive)</span><?php endif; ?>
        </label>
        <input type="text" name="<?= $col ?>" value="<?= htmlspecialchars($row[$col]??'') ?>"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          <?= $inactive?'disabled':'' ?>>
      </div>
      <?php endfor; ?>
    </div>

    <input type="hidden" name="id" value="<?= (int)($id??0) ?>">
    <div class="flex gap-3 pt-2 border-t border-gray-100">
      <button type="submit" name="update" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
      <button type="reset" class="inline-flex items-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-rotate-left"></i> Reset</button>
      <a href="view_account.php" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">Cancel</a>
    </div>
  </form>
</div>

<!-- Image editing -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-xl">
  <h2 class="font-semibold text-gray-800 mb-4">Account Images</h2>
  <form method="POST" enctype="multipart/form-data" class="space-y-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-2">ID / Document Image</label>
        <?php if(!empty($row['image'])): ?>
        <img src="foto/<?= htmlspecialchars($row['image']) ?>" alt="ID" class="w-full h-32 object-cover rounded-lg border border-gray-200 mb-2">
        <?php else: ?>
        <div class="w-full h-32 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-400 text-xs mb-2">No image</div>
        <?php endif; ?>
        <input type="file" name="image" accept="image/*" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-2">Profile Picture</label>
        <?php if(!empty($row['pp'])): ?>
        <img src="foto/<?= htmlspecialchars($row['pp']) ?>" alt="Profile" class="w-full h-32 object-cover rounded-lg border border-gray-200 mb-2">
        <?php else: ?>
        <div class="w-full h-32 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-400 text-xs mb-2">No image</div>
        <?php endif; ?>
        <input type="file" name="pp" accept="image/*" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
    </div>
    <p class="text-xs text-gray-500">Accepted: JPG, PNG, GIF, WEBP &mdash; max 2MB. Leave blank to keep current.</p>
    <button type="submit" name="update_images" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-cloud-arrow-up"></i> Upload Images</button>
  </form>
</div>

<!-- Currency Wallet Management -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-5xl mt-6">
  <div class="flex items-center justify-between mb-4">
    <div>
      <h2 class="font-semibold text-gray-800">Multi-Currency Wallets</h2>
      <p class="text-xs text-gray-500 mt-0.5">Edit or add independent currency account balances for this user.</p>
    </div>
  </div>

  <?= $walletMsg ?>

  <?php
  // Load this user's wallets
  $userWallets = [];
  if (!empty($row['acc_no'])) {
      $safeAccW = $conn->real_escape_string($row['acc_no']);
      $wRes = $conn->query(
          "SELECT ab.currency_code, ab.balance, c.symbol, c.name, c.is_crypto
           FROM account_balances ab
           LEFT JOIN currencies c ON c.code = ab.currency_code
           WHERE ab.acc_no = '$safeAccW'
           ORDER BY c.is_crypto, c.sort_order, ab.currency_code"
      );
      if ($wRes) {
          while ($wr = $wRes->fetch_assoc()) $userWallets[] = $wr;
      }
  }

  // All active currencies for the add form
  $allCurrencies = [];
  $acRes = $conn->query("SELECT code, symbol, name, is_crypto FROM currencies WHERE is_active = 1 ORDER BY is_crypto, sort_order, code");
  if ($acRes) { while ($acr = $acRes->fetch_assoc()) $allCurrencies[] = $acr; }
  ?>

  <?php if (!empty($userWallets)): ?>
  <div class="overflow-x-auto mb-6">
    <table class="w-full text-sm text-left border-collapse">
      <thead>
        <tr class="border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wide">
          <th class="pb-2 pr-4">Currency</th>
          <th class="pb-2 pr-4">Name</th>
          <th class="pb-2 pr-4">Type</th>
          <th class="pb-2 pr-4">Balance</th>
          <th class="pb-2">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($userWallets as $uw): ?>
        <tr>
          <td class="py-2.5 pr-4 font-semibold text-gray-800"><?= htmlspecialchars($uw['currency_code']) ?></td>
          <td class="py-2.5 pr-4 text-gray-600"><?= htmlspecialchars($uw['name'] ?: $uw['currency_code']) ?></td>
          <td class="py-2.5 pr-4"><span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium <?= (int)$uw['is_crypto'] ? 'bg-amber-100 text-amber-700' : 'bg-blue-50 text-blue-600' ?>"><?= (int)$uw['is_crypto'] ? 'Crypto' : 'Fiat' ?></span></td>
          <td class="py-2.5 pr-4">
            <form method="POST" class="flex items-center gap-2">
              <input type="hidden" name="w_currency_code" value="<?= htmlspecialchars($uw['currency_code']) ?>">
              <input type="number" step="0.00000001" name="w_balance"
                value="<?= htmlspecialchars(number_format((float)$uw['balance'], (int)$uw['is_crypto'] ? 8 : 2, '.', '')) ?>"
                class="w-36 rounded-lg border border-gray-300 px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <button type="submit" name="wallet_upsert" class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                <i class="fa-solid fa-floppy-disk text-[10px]"></i> Save
              </button>
            </form>
          </td>
          <td class="py-2.5">
            <form method="POST">
              <input type="hidden" name="del_currency_code" value="<?= htmlspecialchars($uw['currency_code']) ?>">
              <button type="submit" name="wallet_delete" class="inline-flex items-center gap-1 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors border border-red-200">
                <i class="fa-solid fa-trash text-[10px]"></i> Remove
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
    <p class="text-sm text-gray-500 mb-4">No currency wallets found for this user.</p>
  <?php endif; ?>

  <!-- Add new wallet -->
  <form method="POST" class="flex flex-wrap items-end gap-3 border-t border-gray-100 pt-4">
    <div>
      <label class="block text-xs font-medium text-gray-700 mb-1">Add / Top-up Currency</label>
      <select name="w_currency_code" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        <option value="">Select currency…</option>
        <?php foreach ($allCurrencies as $ac): ?>
          <option value="<?= htmlspecialchars($ac['code']) ?>"><?= htmlspecialchars($ac['code']) ?> – <?= htmlspecialchars($ac['name']) ?><?= (int)$ac['is_crypto'] ? ' (crypto)' : '' ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-700 mb-1">Balance</label>
      <input type="number" name="w_balance" value="0" step="0.00000001" min="0"
        class="w-40 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
    </div>
    <button type="submit" name="wallet_upsert" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer">
      <i class="fa-solid fa-plus"></i> Add / Update Wallet
    </button>
  </form>
</div>

<!-- IBAN Management -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-5xl mt-6">
  <div class="flex items-center justify-between mb-4">
    <div>
      <h2 class="font-semibold text-gray-800">IBAN Management</h2>
      <p class="text-xs text-gray-500 mt-0.5">Edit, regenerate, or set custom IBANs for this user's accounts.</p>
    </div>
  </div>

  <?= $ibanMsg ?>

  <?php
  $userIbans = [];
  if (!empty($row['acc_no'])) {
      $safeAccI = $conn->real_escape_string($row['acc_no']);
      $hasIbanCustomCol = false;
      $hasIbanUpdatedAtCol = false;
      $hasIbanUpdatedByCol = false;

      $colCheck = $conn->query("SHOW COLUMNS FROM customer_accounts");
      if ($colCheck) {
        while ($colRow = $colCheck->fetch_assoc()) {
          $colName = (string)($colRow['Field'] ?? '');
          if ($colName === 'iban_custom') {
            $hasIbanCustomCol = true;
          } elseif ($colName === 'iban_updated_at') {
            $hasIbanUpdatedAtCol = true;
          } elseif ($colName === 'iban_updated_by') {
            $hasIbanUpdatedByCol = true;
          }
        }
      }

      $ibanCustomSelect = $hasIbanCustomCol ? 'ca.iban_custom' : '0 AS iban_custom';
      $ibanUpdatedAtSelect = $hasIbanUpdatedAtCol ? 'ca.iban_updated_at' : 'NULL AS iban_updated_at';
      $ibanUpdatedBySelect = $hasIbanUpdatedByCol ? 'ca.iban_updated_by' : "'' AS iban_updated_by";

      $iRes = $conn->query(
        "SELECT ca.id, ca.currency_code, ca.iban, {$ibanCustomSelect}, {$ibanUpdatedAtSelect}, {$ibanUpdatedBySelect}, c.name
           FROM customer_accounts ca
           LEFT JOIN currencies c ON c.code = ca.currency_code
           WHERE ca.owner_acc_no = '$safeAccI'
           ORDER BY ca.currency_code"
      );
      if ($iRes) {
          while ($ir = $iRes->fetch_assoc()) $userIbans[] = $ir;
      }
  }
  ?>

  <?php if (!empty($userIbans)): ?>
  <div class="overflow-x-auto mb-6">
    <table class="w-full text-sm text-left border-collapse">
      <thead>
        <tr class="border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wide">
          <th class="pb-2 pr-4">Currency</th>
          <th class="pb-2 pr-4">Current IBAN</th>
          <th class="pb-2 pr-4">Last Updated</th>
          <th class="pb-2">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($userIbans as $ui): ?>
        <tr>
          <td class="py-2.5 pr-4 font-semibold text-gray-800"><?= htmlspecialchars($ui['currency_code']) ?></td>
          <td class="py-2.5 pr-4 font-mono text-gray-700 text-xs"><?= htmlspecialchars($ui['iban'] ?? 'N/A') ?> <?php if(!empty($ui['iban_custom'])): ?><span class="text-amber-600 font-normal">(custom)</span><?php endif; ?></td>
          <td class="py-2.5 pr-4 text-gray-500 text-xs"><?= !empty($ui['iban_updated_at']) ? htmlspecialchars(date('Y-m-d H:i', strtotime($ui['iban_updated_at']))) : '—' ?></td>
          <td class="py-2.5 space-x-2">
            <form method="POST" class="inline-flex items-center gap-2">
              <input type="hidden" name="customer_account_id" value="<?= (int)$ui['id'] ?>">
              <input type="hidden" name="iban_reason" value="Manual regeneration">
              <button type="submit" name="update_iban" value="regenerate" class="inline-flex items-center gap-1 bg-amber-50 hover:bg-amber-100 text-amber-600 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors border border-amber-200" title="Generate a new system IBAN">
                <i class="fa-solid fa-refresh text-[10px]"></i> Regenerate
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="border-t border-gray-100 pt-4">
    <p class="text-xs font-medium text-gray-700 mb-3">Set Custom IBAN</p>
    <form method="POST" class="space-y-3">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Select Account</label>
          <select name="customer_account_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <option value="">Choose account...</option>
            <?php foreach ($userIbans as $ui): ?>
              <option value="<?= (int)$ui['id'] ?>"><?= htmlspecialchars($ui['currency_code']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">New IBAN (max 34 chars)</label>
          <input type="text" name="new_iban" maxlength="34" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Reason / Notes</label>
          <input type="text" name="iban_reason" maxlength="255" placeholder="e.g., Customer request, correction" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
      </div>
      <button type="submit" name="update_iban" value="custom" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer">
        <i class="fa-solid fa-edit"></i> Set Custom IBAN
      </button>
    </form>
  </div>

  <?php else: ?>
    <p class="text-sm text-gray-500 mb-4">No customer accounts with IBANs found for this user.</p>
  <?php endif; ?>

  <div class="border-t border-gray-100 pt-4 mt-4">
    <a href="iban_change_history.php" class="inline-flex items-center gap-2 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors border border-gray-200">
      <i class="fa-solid fa-history"></i> View IBAN Change History
    </a>
  </div>
</div>

<?php else: ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-lg">
  <p class="text-gray-500">No account found. <a href="view_account.php" class="text-blue-600 hover:underline">Go back</a>.</p>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
