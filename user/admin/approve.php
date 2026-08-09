<?php
session_start();
include_once ('session.php');
if(!isset($_SESSION['email'])){
    header("Location: login.php");
    exit();
}

require_once 'class.admin.php';
require dirname(__DIR__, 2) . '/config.php';
$conn = $GLOBALS['conn'] ?? null;
require_once dirname(__DIR__) . '/partials/auto-migrate.php';
require_once dirname(__DIR__) . '/partials/wallet-ledger.php';
require_once dirname(__DIR__) . '/partials/iban-tools.php';

$reg_user = new USER();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$row = [];
if ($id > 0) {
    $stmt = $reg_user->runQuery("SELECT * FROM temp_account WHERE id='$id'");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

if (isset($_POST['approve']) && !empty($row)) {
    $fname    = trim($_POST['fname'] ?? '');
    $pin      = trim($_POST['pin'] ?? '');
    $lname    = trim($_POST['lname'] ?? '');
    $uname    = trim($_POST['uname'] ?? '');
    $upass    = (string)($_POST['upass'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $type     = trim($_POST['type'] ?? '');
    $type     = preg_replace('/^\d+(?:\.\d+)?\s*-\s*/', '', $type);
    $work     = trim($_POST['work'] ?? '');
    $acc_no   = trim($_POST['acc_no'] ?? '');
    $addr     = trim($_POST['addr'] ?? '');
    $sex      = trim($_POST['sex'] ?? '');
    $dob      = trim($_POST['dob'] ?? '');
    $marry    = trim($_POST['marry'] ?? '');
    $t_bal    = trim($_POST['t_bal'] ?? '0');
    $a_bal    = trim($_POST['a_bal'] ?? '0');
    $cot      = trim($_POST['cot'] ?? '');
    $tax      = trim($_POST['tax'] ?? '');
    $imf      = trim($_POST['imf'] ?? '');
    $currency = strtoupper(trim($_POST['currency'] ?? 'USD'));
    if ($currency === '' || !preg_match('/^[A-Z0-9]{2,10}$/', $currency)) {
      $currency = 'USD';
    }
    $tBalFloat = is_numeric($t_bal) ? (float)$t_bal : 0.0;
    $aBalFloat = is_numeric($a_bal) ? (float)$a_bal : $tBalFloat;
    if ($aBalFloat > $tBalFloat) {
      $aBalFloat = $tBalFloat;
    }

    $upass2 = $upass;
    $reg_date = date('d/m/Y');
    $lppi = '';
    $image = '';
    $pp = '';
    $code5 = '';
    $status = 'Active';
    $login_method = 'pin';
    $auth_method = 'codes';

    if ($reg_user->create($fname,$pin,$lname,$uname,$upass,$upass2,$phone,$email,$type,$reg_date,$work,$acc_no,$addr,$sex,$dob,$marry,$tBalFloat,$aBalFloat,$currency,$cot,$tax,$lppi,$imf,$code5,$image,$pp,$status,$login_method,$auth_method)) {
      if ($conn instanceof mysqli) {
        fw_wallet_set($conn, $acc_no, $currency, $tBalFloat, $aBalFloat);
        fw_wallet_sync_legacy_account($conn, $acc_no, $currency);

        $ownerEsc = $conn->real_escape_string($acc_no);
        $curEsc = $conn->real_escape_string($currency);
        $walletNo = $acc_no . '-' . $currency;
        $walletNoEsc = $conn->real_escape_string($walletNo);
        $walletBal = (float)$aBalFloat;
        $conn->query("INSERT INTO customer_accounts (owner_acc_no, account_no, currency_code, balance, status, is_primary)
                VALUES ('{$ownerEsc}', '{$walletNoEsc}', '{$curEsc}', {$walletBal}, 'active', 1)
                ON DUPLICATE KEY UPDATE
                account_no = VALUES(account_no),
                balance = VALUES(balance),
                status = 'active',
                is_primary = 1");

        if (fw_customer_accounts_has_iban_columns($conn)) {
          $walletRowRes = $conn->query("SELECT id FROM customer_accounts WHERE owner_acc_no = '{$ownerEsc}' AND currency_code = '{$curEsc}' LIMIT 1");
          if ($walletRowRes && $walletRowRes->num_rows > 0) {
            $walletRow = $walletRowRes->fetch_assoc();
            $walletId = (int)($walletRow['id'] ?? 0);
            if ($walletId > 0) {
              $ibanCountry = fw_setting_get($conn, 'iban_country', 'GB');
              $ibanBankCode = fw_setting_get($conn, 'iban_bank_code', 'FWLT');
              $ibanData = fw_generate_iban($acc_no, $currency, $walletId, $ibanCountry, $ibanBankCode);
              $ibanEsc = $conn->real_escape_string($ibanData['iban']);
              $bbanEsc = $conn->real_escape_string($ibanData['bban']);
              $displayEsc = $conn->real_escape_string($ibanData['display']);
              $conn->query("UPDATE customer_accounts
                      SET iban = '{$ibanEsc}',
                        bban = '{$bbanEsc}',
                        account_display = '{$displayEsc}'
                      WHERE id = {$walletId}");
            }
          }
        }
      }

        $deleteuser = $reg_user->runQuery("DELETE FROM temp_account WHERE id = '$id'");
        $deleteuser->execute();

        $subject = "Congratulations $fname! - Your Account Application Was Approved!";
        $approval_data = [
            'fname' => $fname,
            'lname' => $lname,
            'acc_no' => $acc_no,
            'account_type' => $type,
            'currency' => $currency,
            'opening_balance' => $t_bal,
            'approval_date' => date('Y-m-d')
        ];

        $reg_user->send_mail($email, '', $subject, 'application_approved', $approval_data);
        header("Location: pending_accounts.php?approved");
        exit();
    }

    header("Location: pending_accounts.php?errora");
    exit();
}

$pageTitle = 'Approve Account Application';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-5xl">
  <h2 class="text-lg font-semibold text-gray-800 mb-1">Approve Account Application</h2>
  <?php if (!empty($row)): ?>
  <form method="POST" class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div><label class="block text-xs font-medium mb-1">First Name</label><input type="text" name="fname" value="<?= htmlspecialchars($row['fname'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-medium mb-1">Security PIN (4-digit)</label><input type="text" name="pin" value="<?= htmlspecialchars($row['pin'] ?? '') ?>" maxlength="4" inputmode="numeric" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-medium mb-1">Last Name</label><input type="text" name="lname" value="<?= htmlspecialchars($row['lname'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-medium mb-1">Username</label><input type="text" name="uname" value="<?= htmlspecialchars($row['uname'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-medium mb-1">Password</label><input type="text" name="upass" value="<?= htmlspecialchars($row['upass'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-medium mb-1">Phone</label><input type="text" name="phone" value="<?= htmlspecialchars($row['phone'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-medium mb-1">Email</label><input type="email" name="email" value="<?= htmlspecialchars($row['email'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-medium mb-1">Account Type</label><input type="text" name="type" value="<?= htmlspecialchars($row['type'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-medium mb-1">Occupation</label><input type="text" name="work" value="<?= htmlspecialchars($row['work'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-medium mb-1">Account Number</label><input type="text" name="acc_no" value="<?= htmlspecialchars($row['acc_no'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-medium mb-1">Gender</label><input type="text" name="sex" value="<?= htmlspecialchars($row['sex'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-medium mb-1">DOB</label><input type="text" name="dob" value="<?= htmlspecialchars($row['dob'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-medium mb-1">Marital Status</label><input type="text" name="marry" value="<?= htmlspecialchars($row['marry'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-medium mb-1">Total Balance</label><input type="text" name="t_bal" value="<?= htmlspecialchars($row['t_bal'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-medium mb-1">Available Balance</label><input type="text" name="a_bal" value="<?= htmlspecialchars($row['a_bal'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-medium mb-1">COT</label><input type="text" name="cot" value="<?= htmlspecialchars($row['cot'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-medium mb-1">TAX</label><input type="text" name="tax" value="<?= htmlspecialchars($row['tax'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-medium mb-1">IMF</label><input type="text" name="imf" value="<?= htmlspecialchars($row['imf'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-medium mb-1">Currency</label><input type="text" name="currency" value="<?= htmlspecialchars($row['currency'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
      <div class="lg:col-span-3"><label class="block text-xs font-medium mb-1">Address</label><input type="text" name="addr" value="<?= htmlspecialchars($row['addr'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
    </div>
    <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
      <button type="submit" name="approve" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors"><i class="fa-solid fa-check"></i> Approve Account</button>
      <a href="pending_accounts.php" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">Cancel</a>
    </div>
  </form>
  <?php else: ?>
  <p class="text-sm text-gray-500">No pending account found.</p>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>