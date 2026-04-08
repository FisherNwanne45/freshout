<?php
session_start();
include_once 'session.php';
require_once 'class.user.php';
require_once '../config.php';
require_once __DIR__ . '/partials/auto-migrate.php';

if (!isset($_SESSION['acc_no'])) {
  header('Location: login.php');
  exit();
}
if (!isset($_SESSION['pin_verified']) && !isset($_SESSION['pin'])) {
  header('Location: passcode.php');
  exit();
}

$reg_user = new USER();
$accNo    = (string)$_SESSION['acc_no'];

$stmt = $reg_user->runQuery('SELECT * FROM account WHERE acc_no = :acc_no LIMIT 1');
$stmt->execute([':acc_no' => $accNo]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
  header('Location: logout.php');
  exit();
}

$statLower = strtolower(trim($row['status'] ?? ''));
if (strpos($statLower, 'dormant') !== false || strpos($statLower, 'inactive') !== false) {
  header('Location: index.php?dormant');
  exit();
}

// Detect auth method
$authMethod = trim((string)($row['auth_method'] ?? ''));
if ($authMethod === '') {
  if ($statLower === 'pincode') $authMethod = 'pin';
  elseif ($statLower === 'otp') $authMethod = 'otp';
  else $authMethod = 'codes';
}

// ── Load TX code settings from site_settings ────────────────────────────────
$txMaxCodes  = 3;
$txCodeNames = [1 => 'COT', 2 => 'TAX', 3 => 'IMF', 4 => 'LPPI', 5 => 'Code 5'];
$codeColumns = [1 => 'cot', 2 => 'tax', 3 => 'imf', 4 => 'lppi', 5 => 'code5'];
if (isset($conn) && $conn instanceof mysqli) {
  $txSettingsRes = $conn->query(
    "SELECT `key`, `value` FROM site_settings WHERE `key` IN " .
      "('tx_max_codes','tx_code1_name','tx_code2_name','tx_code3_name','tx_code4_name','tx_code5_name')"
  );
  if ($txSettingsRes) {
    while ($txRow = $txSettingsRes->fetch_assoc()) {
      switch ($txRow['key']) {
        case 'tx_max_codes':
          $txMaxCodes = max(1, min(5, (int)$txRow['value']));
          break;
        case 'tx_code1_name':
          $txCodeNames[1] = $txRow['value'];
          break;
        case 'tx_code2_name':
          $txCodeNames[2] = $txRow['value'];
          break;
        case 'tx_code3_name':
          $txCodeNames[3] = $txRow['value'];
          break;
        case 'tx_code4_name':
          $txCodeNames[4] = $txRow['value'];
          break;
        case 'tx_code5_name':
          $txCodeNames[5] = $txRow['value'];
          break;
      }
    }
  }
}

// Progress milestones indexed by "how many codes have been verified → target %"
// milestones[0] = stop after initial page-load animation
// milestones[N] = stop after N-th code is verified correctly
$milestonesMap = [
  1 => [15, 100],
  2 => [15, 65, 100],
  3 => [15, 65, 90, 100],
  4 => [15, 50, 75, 90, 100],
  5 => [15, 45, 65, 80, 90, 100],
];
$milestones = $milestonesMap[$txMaxCodes] ?? $milestonesMap[3];

$email = $row['email'];

// Load latest temp_transfer
$tempStmt = $reg_user->runQuery("SELECT * FROM temp_transfer WHERE email = '$email' ORDER BY id DESC LIMIT 1");
$tempStmt->execute();
$tempRow  = $tempStmt->fetch(PDO::FETCH_ASSOC);

// Cancel transfer and clear any staged temp_transfer rows.
if (isset($_GET['cancel']) && $_GET['cancel'] === '1') {
  try {
    $reg_user->runQuery("DELETE FROM temp_transfer WHERE email = :email")
      ->execute([':email' => $email]);
  } catch (Throwable $e) {
  }
  unset($_SESSION['auth_step'], $_SESSION['auth_transfer_id']);
  header('Location: send.php?transfer_cancelled=1');
  exit();
}

// If no pending transfer, send back
if (!$tempRow) {
  header('Location: send.php');
  exit();
}

$tempTransferId = (int)($tempRow['id'] ?? 0);

$amount       = $tempRow['amount']        ?? 0;
$recipAcc     = $tempRow['acc_no']        ?? '';
$recipName    = $tempRow['acc_name']      ?? '';
$recipBank    = $tempRow['bank_name']     ?? '';
$recipSwift   = $tempRow['swift']         ?? '';
$recipRouting = $tempRow['routing']       ?? '';
$transferType = $tempRow['transfer_type'] ?? ($tempRow['type'] ?? 'standard');
$currencyCode = $tempRow['currency_code'] ?? ($row['currency'] ?? 'USD');
$sourceAccountNo = $tempRow['source_account_no'] ?? '';
$destinationAccountNo = $tempRow['destination_account_no'] ?? '';
$remarks      = $tempRow['remarks']       ?? '';
$origType     = $tempRow['type']          ?? $transferType;
$transferTypeLabelMap = [
  'samebank' => 'Same Bank',
  'internal' => 'Same Bank',
  'interbank' => 'Same Bank',
  'wire' => 'Wire / International',
  'domestic' => 'Domestic',
  'crypto' => 'Crypto',
];
$transferTypeLabel = $transferTypeLabelMap[strtolower((string)$transferType)] ?? ucfirst((string)$transferType);

// ── Complete transfer (called on successful auth) ──────────────────────────
if (!function_exists('transfer_auth_completeTransfer')) {
  function transfer_auth_completeTransfer($reg_user, array $row, array $tempRow, $conn): array
  {
    $email     = $row['email'];
    $amount    = $tempRow['amount']        ?? 0;
    $accNoR    = $tempRow['acc_no']        ?? '';
    $accName   = $tempRow['acc_name']      ?? '';
    $bankName  = $tempRow['bank_name']     ?? '';
    $swift     = $tempRow['swift']         ?? '';
    $routing   = $tempRow['routing']       ?? '';
    $type      = $tempRow['type']          ?? ($tempRow['transfer_type'] ?? 'standard');
    $remarks   = $tempRow['remarks']       ?? '';
    $xferType  = $tempRow['transfer_type'] ?? $type;
    $curCode   = $tempRow['currency_code'] ?? ($row['currency'] ?? 'USD');
    $sourceAccountNo = (string)($tempRow['source_account_no'] ?? '');
    $destinationAccountNo = (string)($tempRow['destination_account_no'] ?? '');

    $normalizedXferType = strtolower(trim((string)$xferType));
    if ($normalizedXferType === 'interbank' || $normalizedXferType === 'internal') {
      $normalizedXferType = 'samebank';
    }
    if ($normalizedXferType === 'samebank') {
      if ($destinationAccountNo === '') {
        return ['ok' => false, 'redirect' => 'send.php?samebank_invalid=1'];
      }

      try {
        $destCheck = $reg_user->runQuery(
          'SELECT account_no FROM customer_accounts
                 WHERE (account_no = :lookup OR iban = :lookup)
                   AND currency_code = :currency_code
                   AND status = :status
                 LIMIT 1'
        );
        $destCheck->execute([
          ':lookup' => $destinationAccountNo,
          ':currency_code' => $curCode,
          ':status' => 'active',
        ]);
        $destCheckRow = $destCheck->fetch(PDO::FETCH_ASSOC);
      } catch (Throwable $e) {
        $destCheckRow = false;
      }
      if (!$destCheckRow) {
        return ['ok' => false, 'redirect' => 'send.php?samebank_invalid=1'];
      }

      $destinationAccountNo = (string)($destCheckRow['account_no'] ?? $destinationAccountNo);
      $accNoR = $destinationAccountNo;
      $bankName = 'Same Bank Transfer';
      $xferType = 'samebank';
    }

    if ($reg_user->transfer($email, $amount, $accNoR, $accName, $bankName, $swift, $routing, $type, $remarks)) {
      try {
        $lastId = $reg_user->lasdID();
        $reg_user->runQuery("UPDATE transfer
                   SET currency_code = :cc,
                     transfer_type = :tt,
                     source_account_no = :source_account_no,
                     destination_account_no = :destination_account_no
                   WHERE id = :id")
          ->execute([
            ':cc' => $curCode,
            ':tt' => $xferType,
            ':source_account_no' => $sourceAccountNo !== '' ? $sourceAccountNo : null,
            ':destination_account_no' => $destinationAccountNo !== '' ? $destinationAccountNo : null,
            ':id' => $lastId,
          ]);
      } catch (Throwable $e) {
      }

      try {
        if ($sourceAccountNo !== '') {
          $reg_user->runQuery(
            'UPDATE customer_accounts
                     SET balance = balance - :amt
                     WHERE owner_acc_no = :owner_acc_no
                       AND account_no = :account_no
                       AND currency_code = :currency_code'
          )->execute([
            ':amt' => $amount,
            ':owner_acc_no' => $row['acc_no'],
            ':account_no' => $sourceAccountNo,
            ':currency_code' => $curCode,
          ]);
        }

        $reg_user->runQuery(
          'UPDATE account_balances SET balance = balance - :amt WHERE acc_no = :an AND currency_code = :cc'
        )->execute([':amt' => $amount, ':an' => $row['acc_no'], ':cc' => $curCode]);
      } catch (Throwable $e) {
      }

      try {
        if ($destinationAccountNo !== '') {
          $dest = $reg_user->runQuery(
            'SELECT ca.owner_acc_no, ca.currency_code, a.email, a.fname, a.lname, a.uname
                     FROM customer_accounts ca
                     LEFT JOIN account a ON a.acc_no = ca.owner_acc_no
                     WHERE (ca.account_no = :account_no OR ca.iban = :iban)
                       AND ca.status = :status LIMIT 1'
          );
          $dest->execute([
            ':account_no' => $destinationAccountNo,
            ':iban' => $destinationAccountNo,
            ':status' => 'active',
          ]);
          $destRow = $dest->fetch(PDO::FETCH_ASSOC);
          if ($destRow) {
            $destOwner = (string)($destRow['owner_acc_no'] ?? '');
            $destCurrency = (string)($destRow['currency_code'] ?? $curCode);
            $destEmail = trim((string)($destRow['email'] ?? ''));
            $destFname = trim((string)($destRow['fname'] ?? ''));
            $destLname = trim((string)($destRow['lname'] ?? ''));
            $destUname = trim((string)($destRow['uname'] ?? ''));

            $reg_user->runQuery(
              'UPDATE customer_accounts
                         SET balance = balance + :amt
                         WHERE account_no = :account_no AND owner_acc_no = :owner_acc_no'
            )->execute([
              ':amt' => $amount,
              ':account_no' => $destinationAccountNo,
              ':owner_acc_no' => $destOwner,
            ]);

            $reg_user->runQuery(
              'INSERT INTO account_balances (acc_no, currency_code, balance)
                         VALUES (:acc_no, :currency_code, 0)
                         ON DUPLICATE KEY UPDATE acc_no = VALUES(acc_no)'
            )->execute([
              ':acc_no' => $destOwner,
              ':currency_code' => $destCurrency,
            ]);

            $reg_user->runQuery(
              'UPDATE account_balances SET balance = balance + :amt WHERE acc_no = :acc_no AND currency_code = :currency_code'
            )->execute([
              ':amt' => $amount,
              ':acc_no' => $destOwner,
              ':currency_code' => $destCurrency,
            ]);

            // Notify credited recipient for internal/domestic in-bank transfers.
            if ($destEmail !== '') {
              try {
                $destBalStmt = $reg_user->runQuery(
                  'SELECT balance FROM account_balances WHERE acc_no = :acc_no AND currency_code = :currency_code LIMIT 1'
                );
                $destBalStmt->execute([':acc_no' => $destOwner, ':currency_code' => $destCurrency]);
                $destBalRow = $destBalStmt->fetch(PDO::FETCH_ASSOC);
                $destNewBalance = (float)($destBalRow['balance'] ?? 0);

                $creditData = [
                  'fname' => $destFname,
                  'lname' => $destLname,
                  'name' => trim($destFname . ' ' . $destLname),
                  'amount' => $amount,
                  'currency' => $destCurrency,
                  'transaction_type' => 'Credit',
                  'description' => 'Incoming transfer from ' . trim((string)($row['fname'] ?? '') . ' ' . (string)($row['lname'] ?? '')),
                  'status' => 'Completed',
                  'date' => date('Y-m-d H:i:s'),
                  'balance' => $destNewBalance,
                ];
                $reg_user->send_mail($destEmail, '', 'Credit Alert: Incoming Transfer', 'transaction_alert', $creditData);

                if ($destUname !== '') {
                  $panelSubject = 'Incoming Transfer Credit';
                  $panelMsg = 'Your account has been credited with ' . strtoupper((string)$destCurrency) . ' ' . number_format((float)$amount, 2) . '.';
                  $reg_user->message('System', $destUname, $panelSubject, $panelMsg);
                }
              } catch (Throwable $e) {
              }
            }
          }
        }
      } catch (Throwable $e) {
      }

      $bal   = (float)($row['t_bal'] ?? 0);
      $abal  = (float)($row['a_bal'] ?? 0);
      $total = max(0, $bal  - (float)$amount);
      $avail = max(0, $abal - (float)$amount);
      try {
        $reg_user->runQuery("UPDATE account SET t_bal = '$total', a_bal = '$avail' WHERE email = '$email'")->execute();
      } catch (Throwable $e) {
      }

      if (strtolower($xferType) === 'crypto') {
        try {
          $conn->query("INSERT INTO crypto_transfers
                    (acc_no, email, currency_code, amount, wallet_address, network, remarks, status)
                    VALUES (
                        '" . $conn->real_escape_string($row['acc_no']) . "',
                        '" . $conn->real_escape_string($email) . "',
                        '" . $conn->real_escape_string($curCode) . "',
                        " . (float)$amount . ",
                        '" . $conn->real_escape_string($swift) . "',
                        '" . $conn->real_escape_string($bankName) . "',
                        '" . $conn->real_escape_string($remarks) . "',
                        'pending'
                    )");
        } catch (Throwable $e) {
        }
      }

      try {
        $debit_data = [
          'fname'    => $row['fname'] ?? '',
          'lname'    => $row['lname'] ?? '',
          'amount'   => $amount,
          'currency' => $curCode,
          'acc_name' => $accName,
          'bank'     => $bankName,
          'date'     => date('Y-m-d H:i:s'),
          'balance'  => $total,
        ];
        $reg_user->send_mail($email, '', 'Debit Alert: Transfer Initiated', 'debit_alert', $debit_data);
      } catch (Throwable $e) {
      }

      unset($_SESSION['auth_step'], $_SESSION['auth_transfer_id']);

      try {
        $reg_user->runQuery("DELETE FROM temp_transfer WHERE email = :email")
          ->execute([':email' => $email]);
      } catch (Throwable $e) {
      }

      return ['ok' => true, 'redirect' => 'success.php'];
    }

    return ['ok' => false, 'redirect' => 'send.php'];
  }
}

// ── AJAX endpoint for code verification ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
  header('Content-Type: application/json; charset=utf-8');

  if ($_GET['action'] === 'verify_code') {
    $slot    = (int)($_POST['slot'] ?? 0);
    $codeVal = trim((string)($_POST['code'] ?? ''));

    $expectedSlot = (int)($_SESSION['auth_step'] ?? 1);
    $expectedTransferId = (int)($_SESSION['auth_transfer_id'] ?? 0);

    if ($expectedTransferId !== $tempTransferId) {
      $_SESSION['auth_transfer_id'] = $tempTransferId;
      $_SESSION['auth_step'] = 1;
      $expectedSlot = 1;
    }

    if ($slot < 1 || $slot > $txMaxCodes || $slot !== $expectedSlot) {
      echo json_encode(['ok' => false, 'error' => 'Invalid step. Please refresh and try again.']);
      exit();
    }

    $col      = $codeColumns[$slot] ?? null;
    $expected = trim($col ? (string)($row[$col] ?? '') : '');

    // Empty code slots are treated as not configured and should not block transfer verification.
    if ($expected !== '' && $codeVal !== $expected) {
      echo json_encode(['ok' => false, 'error' => 'Incorrect code. Please try again.']);
      exit();
    }

    // Code correct
    $_SESSION['auth_step'] = $slot + 1;
    $done    = ($slot >= $txMaxCodes);
    $nextPct = (int)($milestones[$slot] ?? 100);

    $finalAction = 'success';
    $result = ['redirect' => null];
    if ($done) {
      if ($authMethod === 'codes_pin')      $finalAction = 'pin';
      elseif ($authMethod === 'codes_otp')  $finalAction = 'otp';

      if ($finalAction === 'otp') {
        // Generate OTP and persist to DB so otp_auth.php can verify with expiry.
        $otp = $reg_user->createOtp((string)($row['acc_no'] ?? ''), $email, 'transfer', 10);
        try {
          $reg_user->send_mail($email, '', 'Your Transfer OTP', 'otp_code', [
            'fname'      => $row['fname'] ?? '',
            'otp'        => $otp,
            'amount'     => $amount,
            'currency'   => $currencyCode,
            'expiry_min' => 10,
          ]);
        } catch (Throwable $e) {
        }
      }

      if ($finalAction === 'success') {
        $result = transfer_auth_completeTransfer($reg_user, $row, $tempRow, $conn);
        if (!$result['ok']) {
          echo json_encode([
            'ok' => false,
            'redirect' => $result['redirect'] ?? 'send.php?samebank_invalid=1',
            'error' => 'Transfer could not be completed.',
          ]);
          exit();
        }
      }
      unset($_SESSION['auth_step']);
    }

    echo json_encode([
      'ok' => true,
      'done' => $done,
      'next_pct' => $nextPct,
      'final_action' => $finalAction,
      'redirect' => $result['redirect'] ?? null,
    ]);
    exit();
  }

  echo json_encode(['ok' => false, 'error' => 'Unknown action.']);
  exit();
}

// ── Normal GET: route pure PIN/OTP immediately, init session state ───────────
if ($authMethod === 'pin') {
  header('Location: pincode.php');
  exit();
}
if ($authMethod === 'otp') {
  if (!$reg_user->hasActiveOtp((string)($row['acc_no'] ?? ''), $email, 'transfer')) {
    $otp = $reg_user->createOtp((string)($row['acc_no'] ?? ''), $email, 'transfer', 10);
    try {
      $reg_user->send_mail($email, '', 'Your Transfer OTP', 'otp_code', [
        'fname'      => $row['fname'] ?? '',
        'otp'        => $otp,
        'amount'     => $amount,
        'currency'   => $currencyCode,
        'expiry_min' => 10,
      ]);
    } catch (Throwable $e) {
    }
  }
  header('Location: otp_auth.php');
  exit();
}

if (!isset($_SESSION['auth_step'])) {
  $_SESSION['auth_step'] = 1;
}
if (!isset($_SESSION['auth_transfer_id']) || (int)$_SESSION['auth_transfer_id'] !== $tempTransferId) {
  $_SESSION['auth_transfer_id'] = $tempTransferId;
  $_SESSION['auth_step'] = 1;
}

// Build arrays for JS config (only the names for active code slots)
$jsCodeNames = [];
for ($i = 1; $i <= $txMaxCodes; $i++) {
  $jsCodeNames[] = $txCodeNames[$i] ?? ('Code ' . $i);
}
$jsConfig = json_encode([
  'totalCodes' => $txMaxCodes,
  'milestones' => array_values($milestones),
  'codeNames'  => $jsCodeNames,
  'authMethod' => $authMethod,
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

require_once __DIR__ . '/partials/shell-data.php';
$shellPageTitle = 'Verifying Transfer';
require_once __DIR__ . '/partials/shell-open.php';
?>

<div class="max-w-lg mx-auto">

  <!-- Transfer summary card -->
  <div class="bg-white rounded-2xl shadow-sm border border-brand-border p-5 mb-6">
    <h2 class="text-xs font-semibold text-brand-muted uppercase tracking-wider mb-3">Transfer Summary</h2>
    <div class="flex justify-between items-center mb-2">
      <span class="text-sm text-brand-muted">Amount</span>
      <span class="text-lg font-bold text-brand-navy"><?= htmlspecialchars($currencyCode) ?> <?= number_format((float)$amount, 2) ?></span>
    </div>
    <?php if ($recipName): ?>
      <div class="flex justify-between items-center mb-2">
        <span class="text-sm text-brand-muted">Beneficiary</span>
        <span class="text-sm font-semibold text-brand-navy"><?= htmlspecialchars($recipName) ?></span>
      </div>
    <?php endif; ?>
    <?php if ($recipBank): ?>
      <div class="flex justify-between items-center mb-2">
        <span class="text-sm text-brand-muted">Bank</span>
        <span class="text-sm text-brand-navy"><?= htmlspecialchars($recipBank) ?></span>
      </div>
    <?php endif; ?>
    <div class="flex justify-between items-center">
      <span class="text-sm text-brand-muted">Type</span>
      <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700"><?= htmlspecialchars($transferTypeLabel) ?></span>
    </div>
  </div>

  <!-- Verification card -->
  <div class="bg-white rounded-2xl shadow-sm border border-brand-border p-6">

    <h1 class="text-xl font-bold text-brand-navy mb-1">Transaction Verification</h1>
    <p class="text-sm text-brand-muted mb-5">Please complete the security check to authorise your transfer.</p>

    <!-- Progress bar -->
    <div class="mb-6">
      <div class="flex items-center justify-between text-xs text-brand-muted mb-2">
        <span id="progressLabel">Authenticating…</span>
        <span id="pctLabel" class="font-semibold text-brand-navy">0%</span>
      </div>
      <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
        <div id="progressBar"
          class="h-2.5 rounded-full bg-gradient-to-r from-blue-500 to-brand-navy"
          style="width:0%;transition:width 0.05s linear;"></div>
      </div>
    </div>

    <!-- Spinner (shown during loading pauses) -->
    <div id="loadingState" class="flex flex-col items-center py-6">
      <div class="w-10 h-10 rounded-full border-4 border-blue-100 border-t-brand-navy animate-spin mb-3"></div>
      <p class="text-sm text-brand-muted">Authenticating transfer, please wait&hellip;</p>
    </div>

    <!-- Error message -->
    <div id="errorBox" class="hidden mb-4 rounded-xl p-3 bg-red-50 border border-red-200">
      <p class="text-sm text-red-700" id="errorMsg"></p>
    </div>

    <!-- Code input form (hidden until animation reaches first milestone) -->
    <div id="codeForm" class="hidden">
      <p class="text-sm font-semibold text-brand-navy text-center mb-4" id="codeLabel">Enter code</p>

      <div id="confirmState" class="hidden mb-4 rounded-xl border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700 text-center font-semibold">
        Code confirmed. Continuing verification...
      </div>

      <input type="password" id="codeInput" autocomplete="off" maxlength="32"
        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-center text-2xl tracking-[0.3em] font-bold focus:outline-none focus:border-blue-500 transition-colors"
        placeholder="• • • • • •">

      <button id="submitBtn" type="button"
        class="mt-4 w-full bg-brand-navy hover:bg-brand-navy2 text-white font-semibold py-3 rounded-xl transition-colors text-sm shadow-sm">
        Verify Code
      </button>
    </div>

    <!-- Completion state (shown at 100%) -->
    <div id="doneState" class="hidden flex-col items-center py-6 text-center">
      <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-100 mb-3">
        <svg class="h-7 w-7 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <p class="text-sm font-semibold text-brand-navy">Verification complete</p>
      <p class="text-xs text-brand-muted mt-1">Redirecting…</p>
    </div>

  </div>

  <div class="mt-4 text-center">
    <a href="transfer-auth.php?cancel=1" class="text-sm text-brand-muted hover:text-brand-navy transition-colors">← Cancel transfer</a>
  </div>

</div>

<script>
  (function() {
    var CFG = <?= $jsConfig ?>;

    var currentPct = 0;
    var currentSlot = 1;

    var bar = document.getElementById('progressBar');
    var pctLabel = document.getElementById('pctLabel');
    var progLabel = document.getElementById('progressLabel');
    var loadingDiv = document.getElementById('loadingState');
    var codeFormDiv = document.getElementById('codeForm');
    var doneDiv = document.getElementById('doneState');
    var errorBox = document.getElementById('errorBox');
    var errorMsg = document.getElementById('errorMsg');
    var codeInput = document.getElementById('codeInput');
    var submitBtn = document.getElementById('submitBtn');
    var codeLabelEl = document.getElementById('codeLabel');
    var confirmState = document.getElementById('confirmState');

    /* ── Animate progress bar ─────────────────────────────────────────────── */
    function animateTo(target, msPerStep, callback) {
      msPerStep = msPerStep || 30;
      if (currentPct >= target) {
        currentPct = target;
        setBar(target);
        if (callback) callback();
        return;
      }
      var id = setInterval(function() {
        if (currentPct < target) {
          currentPct++;
          setBar(currentPct);
        } else {
          clearInterval(id);
          if (callback) callback();
        }
      }, msPerStep);
    }

    function setBar(pct) {
      bar.style.width = pct + '%';
      pctLabel.textContent = pct + '%';
    }

    /* ── Show code entry form ────────────────────────────────────────────── */
    function showCodeForm(slot) {
      var name = CFG.codeNames[slot - 1] || ('Code ' + slot);
      codeLabelEl.textContent = 'Enter your ' + name + ' code';
      codeInput.value = '';
      confirmState.classList.add('hidden');
      errorBox.classList.add('hidden');
      submitBtn.disabled = false;
      submitBtn.textContent = 'Verify Code';
      loadingDiv.classList.add('hidden');
      codeFormDiv.classList.remove('hidden');
      progLabel.textContent = 'Security verification in progress';
      setTimeout(function() {
        codeInput.focus();
      }, 50);
    }

    /* ── Show error inline ────────────────────────────────────────────────── */
    function showError(msg) {
      errorMsg.textContent = msg;
      errorBox.classList.remove('hidden');
      submitBtn.disabled = false;
      submitBtn.textContent = 'Verify Code';
      codeInput.focus();
    }

    /* ── Handle completion redirect ─────────────────────────────────────── */
    function handleDone(finalAction) {
      codeFormDiv.classList.add('hidden');
      loadingDiv.classList.add('hidden');
      progLabel.textContent = 'Finalising…';
      animateTo(100, 15, function() {
        doneDiv.classList.remove('hidden');
        doneDiv.style.display = 'flex';
        setTimeout(function() {
          if (finalAction === 'pin') window.location.href = 'pincode.php';
          else if (finalAction === 'otp') window.location.href = 'otp_auth.php';
          else window.location.href = 'success.php';
        }, 800);
      });
    }

    /* ── Submit code via AJAX ────────────────────────────────────────────── */
    function submitCode() {
      var code = codeInput.value.trim();
      if (!code) {
        showError('Please enter the code before continuing.');
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = 'Verifying…';
      errorBox.classList.add('hidden');

      var body = 'slot=' + encodeURIComponent(currentSlot) + '&code=' + encodeURIComponent(code);

      fetch('transfer-auth.php?action=verify_code', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: body,
        })
        .then(function(r) {
          return r.json();
        })
        .then(function(data) {
          if (!data.ok) {
            if (data.redirect) {
              window.location.href = data.redirect;
              return;
            }
            showError(data.error || 'Incorrect code. Please try again.');
            return;
          }

          // Correct code — briefly confirm, then continue loader to next milestone.
          confirmState.classList.remove('hidden');
          submitBtn.disabled = true;
          submitBtn.textContent = 'Confirmed';

          setTimeout(function() {
            confirmState.classList.add('hidden');
            codeFormDiv.classList.add('hidden');
            loadingDiv.classList.remove('hidden');
            progLabel.textContent = 'Security verification in progress';

            var targetPct = data.next_pct;
            animateTo(targetPct, 20, function() {
              if (data.done) {
                if (data.redirect && data.final_action === 'success') {
                  window.location.href = data.redirect;
                  return;
                }
                handleDone(data.final_action);
              } else {
                currentSlot++;
                setTimeout(function() {
                  showCodeForm(currentSlot);
                }, 350);
              }
            });
          }, 450);
        })
        .catch(function() {
          showError('Network error. Please check your connection and try again.');
          submitBtn.disabled = false;
          submitBtn.textContent = 'Verify Code';
        });
    }

    /* ── Bind events ─────────────────────────────────────────────────────── */
    submitBtn.addEventListener('click', submitCode);
    codeInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        submitCode();
      }
    });

    /* ── Bootstrap: animate 0 → initial milestone, then show first form ── */
    progLabel.textContent = 'Security verification in progress';
    var initialTarget = CFG.milestones[0] || 15;
    animateTo(initialTarget, 35, function() {
      setTimeout(function() {
        showCodeForm(1);
      }, 350);
    });

  }());
</script>

<?php require_once __DIR__ . '/partials/shell-close.php'; ?>