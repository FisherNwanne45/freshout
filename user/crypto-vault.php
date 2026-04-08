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
if (!isset($_SESSION['pin'])) {
  header('Location: passcode.php');
  exit();
}

$reg_user = new USER();
$accNo = (string)$_SESSION['acc_no'];
$flashMessage = '';
$flashType = 'success';
$cryptoTab = strtolower(trim((string)($_GET['tab'] ?? 'deposit')));
if (!in_array($cryptoTab, ['deposit', 'withdraw'], true)) {
  $cryptoTab = 'deposit';
}

$stmt = $reg_user->runQuery('SELECT * FROM account WHERE acc_no = :acc_no LIMIT 1');
$stmt->execute([':acc_no' => $accNo]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
  header('Location: logout.php');
  exit();
}

function crypto_upload_proof(array $file): array
{
  if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    return [false, 'Upload the transaction proof file.'];
  }

  $allowed = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'application/pdf' => 'pdf',
  ];

  $mime = '';
  if (function_exists('finfo_open')) {
    $fi = finfo_open(FILEINFO_MIME_TYPE);
    if ($fi) {
      $mime = (string)finfo_file($fi, (string)($file['tmp_name'] ?? ''));
      finfo_close($fi);
    }
  }
  if ($mime === '' || !isset($allowed[$mime])) {
    return [false, 'Proof must be JPG, PNG, WEBP, or PDF.'];
  }

  if ((int)($file['size'] ?? 0) > 8 * 1024 * 1024) {
    return [false, 'Proof file must be 8MB or smaller.'];
  }

  $targetDir = __DIR__ . '/uploads/crypto-proofs';
  if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
    return [false, 'Unable to prepare proof upload folder.'];
  }

  $name = 'proof_' . date('YmdHis') . '_' . substr(bin2hex(random_bytes(6)), 0, 12) . '.' . $allowed[$mime];
  $targetPath = $targetDir . '/' . $name;
  if (!move_uploaded_file((string)$file['tmp_name'], $targetPath)) {
    return [false, 'Unable to save proof file.'];
  }

  return [true, 'uploads/crypto-proofs/' . $name];
}

function crypto_normalize_asset_path(?string $path): string
{
  $path = trim((string)$path);
  if ($path === '') {
    return '';
  }
  while (strpos($path, '../') === 0) {
    $path = substr($path, 3);
  }
  return ltrim($path, '/');
}

$depositWalletStmt = $reg_user->runQuery(
  'SELECT cdw.currency_code, cdw.network_name, cdw.wallet_label, cdw.wallet_address, cdw.qr_code_path, cdw.instructions,
            c.name AS currency_name, c.symbol, c.is_crypto
     FROM crypto_deposit_wallets cdw
     INNER JOIN currencies c ON c.code = cdw.currency_code
     WHERE cdw.is_active = 1 AND c.is_active = 1 AND c.is_crypto = 1
     ORDER BY c.sort_order, cdw.currency_code'
);
$depositWalletStmt->execute();
$depositWallets = $depositWalletStmt->fetchAll(PDO::FETCH_ASSOC);

$cryptoWalletStmt = $reg_user->runQuery(
  'SELECT ab.currency_code, ab.balance, c.name, c.symbol, c.flag_code, c.is_crypto
     FROM account_balances ab
     INNER JOIN currencies c ON c.code = ab.currency_code
     WHERE ab.acc_no = :acc_no AND c.is_active = 1 AND c.is_crypto = 1
     ORDER BY c.sort_order, ab.currency_code'
);
$cryptoWalletStmt->execute([':acc_no' => $accNo]);
$cryptoWallets = $cryptoWalletStmt->fetchAll(PDO::FETCH_ASSOC);

$walletConfigByCode = [];
foreach ($depositWallets as $walletCfg) {
  $walletConfigByCode[strtoupper((string)$walletCfg['currency_code'])] = $walletCfg;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_crypto_deposit'])) {
  $cryptoTab = 'deposit';
  $currencyCode = strtoupper(trim((string)($_POST['deposit_currency'] ?? '')));
  $amount = (float)($_POST['deposit_amount'] ?? 0);
  $senderWallet = trim((string)($_POST['sender_wallet_address'] ?? ''));
  $txHash = trim((string)($_POST['tx_hash'] ?? ''));
  $userNote = trim((string)($_POST['deposit_note'] ?? ''));

  if (!isset($walletConfigByCode[$currencyCode])) {
    $flashType = 'error';
    $flashMessage = 'Selected crypto deposit currency is not configured yet.';
  } elseif ($amount <= 0) {
    $flashType = 'error';
    $flashMessage = 'Enter a valid deposit amount.';
  } elseif ($senderWallet === '') {
    $flashType = 'error';
    $flashMessage = 'Enter the sending wallet address.';
  } elseif ($txHash === '') {
    $flashType = 'error';
    $flashMessage = 'Enter the blockchain transaction hash.';
  } else {
    [$ok, $proofResult] = crypto_upload_proof($_FILES['deposit_proof'] ?? []);
    if (!$ok) {
      $flashType = 'error';
      $flashMessage = $proofResult;
    } else {
      $cfg = $walletConfigByCode[$currencyCode];
      $depositRef = 'CD' . date('YmdHis') . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
      $now = date('Y-m-d H:i:s');
      try {
        $ins = $reg_user->runQuery('INSERT INTO crypto_deposit_requests
                    (deposit_ref, acc_no, email, currency_code, network_name, wallet_address, sender_wallet_address, tx_hash, amount, proof_path, user_note, status, created_at, updated_at)
                    VALUES
                    (:deposit_ref, :acc_no, :email, :currency_code, :network_name, :wallet_address, :sender_wallet_address, :tx_hash, :amount, :proof_path, :user_note, :status, :created_at, :updated_at)');
        $ins->execute([
          ':deposit_ref' => $depositRef,
          ':acc_no' => $accNo,
          ':email' => (string)($row['email'] ?? ''),
          ':currency_code' => $currencyCode,
          ':network_name' => (string)($cfg['network_name'] ?? ''),
          ':wallet_address' => (string)($cfg['wallet_address'] ?? ''),
          ':sender_wallet_address' => $senderWallet,
          ':tx_hash' => $txHash,
          ':amount' => $amount,
          ':proof_path' => $proofResult,
          ':user_note' => $userNote,
          ':status' => 'pending',
          ':created_at' => $now,
          ':updated_at' => $now,
        ]);
        $flashMessage = 'Crypto deposit submitted successfully. Reference: ' . $depositRef;
      } catch (Throwable $e) {
        $flashType = 'error';
        $flashMessage = 'Unable to submit crypto deposit right now.';
      }
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_crypto_withdrawal'])) {
  $cryptoTab = 'withdraw';
  $currencyCode = strtoupper(trim((string)($_POST['withdraw_currency'] ?? '')));
  $amount = (float)($_POST['withdraw_amount'] ?? 0);
  $destinationAddress = trim((string)($_POST['destination_address'] ?? ''));
  $networkName = trim((string)($_POST['withdraw_network'] ?? ''));
  $userNote = trim((string)($_POST['withdraw_note'] ?? ''));

  $walletMap = [];
  foreach ($cryptoWallets as $walletRow) {
    $walletMap[strtoupper((string)$walletRow['currency_code'])] = $walletRow;
  }

  if (!isset($walletMap[$currencyCode])) {
    $flashType = 'error';
    $flashMessage = 'Select one of your crypto wallets.';
  } elseif ($amount <= 0) {
    $flashType = 'error';
    $flashMessage = 'Enter a valid withdrawal amount.';
  } elseif ($destinationAddress === '') {
    $flashType = 'error';
    $flashMessage = 'Enter the destination wallet address.';
  } elseif ($networkName === '') {
    $flashType = 'error';
    $flashMessage = 'Enter the network for this withdrawal.';
  } else {
    try {
      $reg_user->runQuery('START TRANSACTION')->execute();

      $walletStmt = $reg_user->runQuery('SELECT balance FROM account_balances WHERE acc_no = :acc_no AND currency_code = :currency_code FOR UPDATE');
      $walletStmt->execute([':acc_no' => $accNo, ':currency_code' => $currencyCode]);
      $walletRow = $walletStmt->fetch(PDO::FETCH_ASSOC);
      if (!$walletRow) {
        throw new RuntimeException('Crypto wallet not found.');
      }
      if ((float)$walletRow['balance'] < $amount) {
        throw new RuntimeException('Insufficient crypto balance for this withdrawal.');
      }

      $withdrawRef = 'CW' . date('YmdHis') . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
      $now = date('Y-m-d H:i:s');

      $reg_user->runQuery('UPDATE account_balances SET balance = balance - :amount WHERE acc_no = :acc_no AND currency_code = :currency_code')
        ->execute([':amount' => $amount, ':acc_no' => $accNo, ':currency_code' => $currencyCode]);
      $reg_user->runQuery('UPDATE customer_accounts SET balance = balance - :amount WHERE owner_acc_no = :acc_no AND currency_code = :currency_code')
        ->execute([':amount' => $amount, ':acc_no' => $accNo, ':currency_code' => $currencyCode]);

      $ins = $reg_user->runQuery('INSERT INTO crypto_withdrawal_requests
                (withdrawal_ref, acc_no, email, currency_code, network_name, destination_address, amount, user_note, status, created_at, updated_at)
                VALUES
                (:withdrawal_ref, :acc_no, :email, :currency_code, :network_name, :destination_address, :amount, :user_note, :status, :created_at, :updated_at)');
      $ins->execute([
        ':withdrawal_ref' => $withdrawRef,
        ':acc_no' => $accNo,
        ':email' => (string)($row['email'] ?? ''),
        ':currency_code' => $currencyCode,
        ':network_name' => $networkName,
        ':destination_address' => $destinationAddress,
        ':amount' => $amount,
        ':user_note' => $userNote,
        ':status' => 'pending',
        ':created_at' => $now,
        ':updated_at' => $now,
      ]);

      try {
        $alert = $reg_user->runQuery('INSERT INTO alerts (uname, type, amount, sender_name, remarks, date, time) VALUES (:uname, :type, :amount, :sender_name, :remarks, :date, :time)');
        $alert->execute([
          ':uname' => $accNo,
          ':type' => 'Crypto Withdrawal Request',
          ':amount' => $amount,
          ':sender_name' => $currencyCode,
          ':remarks' => 'Destination ' . $destinationAddress . ', ref ' . $withdrawRef,
          ':date' => date('Y-m-d'),
          ':time' => date('H:i:s'),
        ]);
      } catch (Throwable $e) {
      }

      $reg_user->runQuery('COMMIT')->execute();
      $flashMessage = 'Withdrawal request submitted. Balance debited immediately. Reference: ' . $withdrawRef;

      try {
        $reg_user->send_mail((string)($row['email'] ?? ''), '', 'Transaction Complete: Crypto Withdrawal Request', 'debit_alert', [
          'fname' => (string)($row['fname'] ?? ''),
          'name' => trim((string)($row['fname'] ?? '') . ' ' . (string)($row['lname'] ?? '')),
          'currency' => $currencyCode,
          'amount' => number_format((float)$amount, 8),
          'description' => 'Crypto withdrawal request submitted (' . $withdrawRef . ')',
          'balance' => 'Updated in dashboard',
          'date' => date('Y-m-d H:i:s'),
          'phone' => (string)($row['phone'] ?? ''),
        ]);
      } catch (Throwable $e) {
      }

      $cryptoWalletStmt->execute([':acc_no' => $accNo]);
      $cryptoWallets = $cryptoWalletStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
      try {
        $reg_user->runQuery('ROLLBACK')->execute();
      } catch (Throwable $rollbackError) {
      }
      $flashType = 'error';
      $flashMessage = $e->getMessage();
    }
  }
}

$depositReqStmt = $reg_user->runQuery('SELECT deposit_ref, currency_code, network_name, amount, tx_hash, proof_path, status, admin_note, created_at
    FROM crypto_deposit_requests WHERE acc_no = :acc_no ORDER BY id DESC LIMIT 25');
$depositReqStmt->execute([':acc_no' => $accNo]);
$depositRequests = $depositReqStmt->fetchAll(PDO::FETCH_ASSOC);

$withdrawReqStmt = $reg_user->runQuery('SELECT withdrawal_ref, currency_code, network_name, destination_address, amount, status, admin_note, created_at
    FROM crypto_withdrawal_requests WHERE acc_no = :acc_no ORDER BY id DESC LIMIT 25');
$withdrawReqStmt->execute([':acc_no' => $accNo]);
$withdrawRequests = $withdrawReqStmt->fetchAll(PDO::FETCH_ASSOC);

$pendingDepositCount = 0;
foreach ($depositRequests as $depositRow) {
  if (strtolower((string)($depositRow['status'] ?? '')) === 'pending') {
    $pendingDepositCount++;
  }
}
$pendingWithdrawalCount = 0;
foreach ($withdrawRequests as $withdrawRow) {
  if (strtolower((string)($withdrawRow['status'] ?? '')) === 'pending') {
    $pendingWithdrawalCount++;
  }
}

$walletMetaJson = [];
foreach ($depositWallets as $cfg) {
  $code = strtoupper((string)($cfg['currency_code'] ?? ''));
  $walletMetaJson[$code] = [
    'currency_name' => (string)($cfg['currency_name'] ?? $code),
    'network_name' => (string)($cfg['network_name'] ?? ''),
    'wallet_label' => (string)($cfg['wallet_label'] ?? ''),
    'wallet_address' => (string)($cfg['wallet_address'] ?? ''),
    'qr_code_path' => crypto_normalize_asset_path((string)($cfg['qr_code_path'] ?? '')),
    'instructions' => (string)($cfg['instructions'] ?? ''),
  ];
}

include_once 'counter.php';
require_once __DIR__ . '/partials/shell-data.php';
$shellPageTitle = 'Crypto Vault';
require_once __DIR__ . '/partials/shell-open.php';
?>

<?php if ($flashMessage !== ''): ?>
  <div class="mb-5 rounded-xl border p-4 text-sm <?= $flashType === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800' ?>">
    <?= htmlspecialchars($flashMessage) ?>
  </div>
<?php endif; ?>

<div class="mb-6 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
  <div>
    <h1 class="text-2xl font-bold text-brand-navy">Crypto Vault</h1>
    <p class="mt-1 text-sm text-brand-muted">Submit crypto deposits for account credit and place withdrawal requests with status tracking.</p>
  </div>
  <div class="flex items-center gap-2 text-xs text-brand-muted">
    <span class="rounded-full bg-white px-3 py-1.5 border border-brand-border">Wallets: <?= count($cryptoWallets) ?></span>
    <span class="rounded-full bg-white px-3 py-1.5 border border-brand-border">Pending Deposits: <?= $pendingDepositCount ?></span>
    <span class="rounded-full bg-white px-3 py-1.5 border border-brand-border">Pending Withdrawals: <?= $pendingWithdrawalCount ?></span>
  </div>
</div>

<div class="mb-6 rounded-2xl border border-brand-border bg-white p-2 shadow-sm">
  <div class="flex flex-wrap gap-2">
    <a href="crypto-vault.php?tab=deposit" class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-semibold transition-colors <?= $cryptoTab === 'deposit' ? 'bg-brand-navy text-white' : 'bg-brand-light/40 text-brand-navy hover:bg-brand-light' ?>">Deposit Crypto</a>
    <a href="crypto-vault.php?tab=withdraw" class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-semibold transition-colors <?= $cryptoTab === 'withdraw' ? 'bg-brand-navy text-white' : 'bg-brand-light/40 text-brand-navy hover:bg-brand-light' ?>">Withdraw Crypto</a>
  </div>
</div>

<div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
  <section class="space-y-6">
    <?php if ($cryptoTab === 'deposit'): ?>
      <div class="rounded-2xl border border-brand-border bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between gap-4">
          <div>
            <h2 class="text-lg font-semibold text-brand-navy">Deposit Crypto</h2>
            <p class="mt-1 text-xs text-brand-muted">Pick a configured wallet, send the crypto, then upload proof for verification.</p>
          </div>
        </div>

        <form method="post" enctype="multipart/form-data" class="mt-5 grid gap-4 lg:grid-cols-2">
          <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-brand-muted">Currency</label>
            <select name="deposit_currency" id="deposit_currency" onchange="cryptoVaultSyncWallet()" class="w-full rounded-xl border border-brand-border px-3 py-2.5 text-sm" required>
              <option value="">Select crypto</option>
              <?php foreach ($depositWallets as $cfg): ?>
                <option value="<?= htmlspecialchars((string)$cfg['currency_code']) ?>"><?= htmlspecialchars((string)$cfg['currency_code']) ?> - <?= htmlspecialchars((string)$cfg['currency_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-brand-muted">Amount Sent</label>
            <input type="number" step="0.00000001" min="0.00000001" name="deposit_amount" class="w-full rounded-xl border border-brand-border px-3 py-2.5 text-sm" placeholder="0.00000000" required>
          </div>
          <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-brand-muted">Sender Wallet</label>
            <input type="text" name="sender_wallet_address" class="w-full rounded-xl border border-brand-border px-3 py-2.5 text-sm" placeholder="Your sending wallet address" required>
          </div>
          <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-brand-muted">Transaction Hash</label>
            <input type="text" name="tx_hash" class="w-full rounded-xl border border-brand-border px-3 py-2.5 text-sm" placeholder="Blockchain transaction hash" required>
          </div>
          <div class="lg:col-span-2">
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-brand-muted">Payment Proof</label>
            <input type="file" name="deposit_proof" accept=".jpg,.jpeg,.png,.webp,.pdf" class="w-full rounded-xl border border-brand-border px-3 py-2.5 text-sm bg-white" required>
          </div>
          <div class="lg:col-span-2">
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-brand-muted">Note</label>
            <textarea name="deposit_note" rows="3" class="w-full rounded-xl border border-brand-border px-3 py-2.5 text-sm" placeholder="Optional note"></textarea>
          </div>
          <div class="lg:col-span-2">
            <button type="submit" name="submit_crypto_deposit" class="inline-flex items-center gap-2 rounded-xl bg-brand-navy px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-navy2 transition-colors">
              Submit Deposit Proof
            </button>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <?php if ($cryptoTab === 'withdraw'): ?>
      <div class="rounded-2xl border border-brand-border bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between gap-4">
          <div>
            <h2 class="text-lg font-semibold text-brand-navy">Withdraw Crypto</h2>
            <p class="mt-1 text-xs text-brand-muted">Your wallet is debited immediately. The payout is processed and your request status is updated.</p>
          </div>
        </div>

        <form method="post" class="mt-5 grid gap-4 lg:grid-cols-2">
          <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-brand-muted">Source Wallet</label>
            <select name="withdraw_currency" class="w-full rounded-xl border border-brand-border px-3 py-2.5 text-sm" required>
              <option value="">Select crypto wallet</option>
              <?php foreach ($cryptoWallets as $wallet): ?>
                <option value="<?= htmlspecialchars((string)$wallet['currency_code']) ?>">
                  <?= htmlspecialchars((string)$wallet['currency_code']) ?> - Balance <?= number_format((float)$wallet['balance'], 8) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-brand-muted">Amount</label>
            <input type="number" step="0.00000001" min="0.00000001" name="withdraw_amount" class="w-full rounded-xl border border-brand-border px-3 py-2.5 text-sm" placeholder="0.00000000" required>
          </div>
          <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-brand-muted">Network</label>
            <input type="text" name="withdraw_network" class="w-full rounded-xl border border-brand-border px-3 py-2.5 text-sm" placeholder="TRC20, ERC20, Bitcoin, Solana..." required>
          </div>
          <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-brand-muted">Destination Address</label>
            <input type="text" name="destination_address" class="w-full rounded-xl border border-brand-border px-3 py-2.5 text-sm" placeholder="Recipient wallet address" required>
          </div>
          <div class="lg:col-span-2">
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-brand-muted">Note</label>
            <textarea name="withdraw_note" rows="3" class="w-full rounded-xl border border-brand-border px-3 py-2.5 text-sm" placeholder="Optional note"></textarea>
          </div>
          <div class="lg:col-span-2">
            <button type="submit" name="submit_crypto_withdrawal" class="inline-flex items-center gap-2 rounded-xl bg-brand-navy px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-navy2 transition-colors">
              Submit Withdrawal Request
            </button>
          </div>
        </form>
      </div>
    <?php endif; ?>
  </section>

  <aside class="space-y-6">
    <?php if ($cryptoTab === 'deposit'): ?>
      <div class="rounded-2xl border border-brand-border bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-brand-navy">Deposit Wallet Details</h2>
        <p class="mt-1 text-xs text-brand-muted">Use the exact wallet and network shown below.</p>

        <div class="mt-4 rounded-2xl border border-dashed border-brand-border bg-brand-light/40 p-4">
          <div class="flex items-start gap-4">
            <div id="crypto_wallet_qr_wrap" class="hidden h-28 w-28 overflow-hidden rounded-2xl border border-brand-border bg-white p-2">
              <img id="crypto_wallet_qr" src="" alt="QR code" class="h-full w-full object-contain">
            </div>
            <div class="min-w-0 flex-1">
              <p id="crypto_wallet_title" class="text-sm font-semibold text-brand-navy">Select a currency to see the wallet</p>
              <p id="crypto_wallet_network" class="mt-1 text-xs text-brand-muted"></p>
              <p id="crypto_wallet_label" class="mt-2 text-xs font-semibold uppercase tracking-wide text-brand-muted"></p>
              <p id="crypto_wallet_address" class="mt-1 break-all rounded-xl bg-white px-3 py-2 text-xs text-brand-navy border border-brand-border"></p>
            </div>
          </div>
          <p id="crypto_wallet_instructions" class="mt-4 text-xs text-brand-muted"></p>
        </div>
      </div>
    <?php else: ?>
      <div class="rounded-2xl border border-brand-border bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-brand-navy">Withdrawal Flow</h2>
        <div class="mt-4 space-y-3 text-xs text-brand-muted">
          <p>1. Choose your funded crypto wallet.</p>
          <p>2. Enter network and destination address carefully.</p>
          <p>3. Submit request: your wallet is debited immediately.</p>
          <p>4. The transfer is processed and status is updated.</p>
        </div>
      </div>
    <?php endif; ?>

    <div class="rounded-2xl border border-brand-border bg-white p-5 shadow-sm">
      <h2 class="text-lg font-semibold text-brand-navy">My Crypto Wallets</h2>
      <div class="mt-4 space-y-3">
        <?php if (empty($cryptoWallets)): ?>
          <p class="text-sm text-brand-muted">No crypto wallets opened yet. Add one from the dashboard first.</p>
        <?php else: ?>
          <?php foreach ($cryptoWallets as $wallet): ?>
            <div class="rounded-xl border border-brand-border bg-brand-light/30 p-3">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="text-sm font-semibold text-brand-navy"><?= htmlspecialchars((string)$wallet['currency_code']) ?></p>
                  <p class="text-xs text-brand-muted"><?= htmlspecialchars((string)($wallet['name'] ?? 'Crypto Wallet')) ?></p>
                </div>
                <p class="text-sm font-bold text-brand-navy"><?= htmlspecialchars((string)($wallet['symbol'] ?: $wallet['currency_code'])) ?> <?= number_format((float)$wallet['balance'], 8) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </aside>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-2">
  <?php if ($cryptoTab === 'deposit'): ?>
    <section class="rounded-2xl border border-brand-border bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-brand-navy">Deposit Requests</h2>
        <span class="text-xs text-brand-muted">Latest 25</span>
      </div>
      <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="border-b border-brand-border text-left text-xs uppercase tracking-wide text-brand-muted">
              <th class="py-2 pr-4">Reference</th>
              <th class="py-2 pr-4">Currency</th>
              <th class="py-2 pr-4">Amount</th>
              <th class="py-2 pr-4">Status</th>
              <th class="py-2 pr-4">Proof</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($depositRequests)): ?>
              <tr>
                <td colspan="5" class="py-4 text-sm text-brand-muted">No crypto deposit requests yet.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($depositRequests as $req): ?>
                <?php $status = strtolower((string)($req['status'] ?? 'pending')); ?>
                <tr class="border-b border-brand-border/60 align-top">
                  <td class="py-3 pr-4 text-xs font-mono text-brand-navy"><?= htmlspecialchars((string)$req['deposit_ref']) ?></td>
                  <td class="py-3 pr-4 text-xs text-brand-navy"><?= htmlspecialchars((string)$req['currency_code']) ?><br><span class="text-brand-muted"><?= htmlspecialchars((string)$req['network_name']) ?></span></td>
                  <td class="py-3 pr-4 text-xs text-brand-navy"><?= number_format((float)$req['amount'], 8) ?></td>
                  <td class="py-3 pr-4 text-xs">
                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold <?= $status === 'approved' ? 'bg-green-100 text-green-700' : ($status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') ?>">
                      <?= htmlspecialchars(strtoupper($status)) ?>
                    </span>
                    <?php if (!empty($req['admin_note'])): ?>
                      <p class="mt-1 text-[11px] text-brand-muted"><?= htmlspecialchars((string)$req['admin_note']) ?></p>
                    <?php endif; ?>
                  </td>
                  <td class="py-3 pr-4 text-xs">
                    <?php if (!empty($req['proof_path'])): ?>
                      <a href="<?= htmlspecialchars(crypto_normalize_asset_path((string)$req['proof_path'])) ?>" target="_blank" rel="noopener noreferrer" class="text-brand-navy underline">Open proof</a>
                    <?php else: ?>
                      <span class="text-brand-muted">No file</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($cryptoTab === 'withdraw'): ?>
    <section class="rounded-2xl border border-brand-border bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-brand-navy">Withdrawal Requests</h2>
        <span class="text-xs text-brand-muted">Latest 25</span>
      </div>
      <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="border-b border-brand-border text-left text-xs uppercase tracking-wide text-brand-muted">
              <th class="py-2 pr-4">Reference</th>
              <th class="py-2 pr-4">Currency</th>
              <th class="py-2 pr-4">Amount</th>
              <th class="py-2 pr-4">Status</th>
              <th class="py-2 pr-4">Destination</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($withdrawRequests)): ?>
              <tr>
                <td colspan="5" class="py-4 text-sm text-brand-muted">No crypto withdrawal requests yet.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($withdrawRequests as $req): ?>
                <?php $status = strtolower((string)($req['status'] ?? 'pending')); ?>
                <tr class="border-b border-brand-border/60 align-top">
                  <td class="py-3 pr-4 text-xs font-mono text-brand-navy"><?= htmlspecialchars((string)$req['withdrawal_ref']) ?></td>
                  <td class="py-3 pr-4 text-xs text-brand-navy"><?= htmlspecialchars((string)$req['currency_code']) ?><br><span class="text-brand-muted"><?= htmlspecialchars((string)$req['network_name']) ?></span></td>
                  <td class="py-3 pr-4 text-xs text-brand-navy"><?= number_format((float)$req['amount'], 8) ?></td>
                  <td class="py-3 pr-4 text-xs">
                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold <?= in_array($status, ['approved', 'paid'], true) ? 'bg-green-100 text-green-700' : ($status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') ?>">
                      <?= htmlspecialchars(strtoupper($status)) ?>
                    </span>
                    <?php if (!empty($req['admin_note'])): ?>
                      <p class="mt-1 text-[11px] text-brand-muted"><?= htmlspecialchars((string)$req['admin_note']) ?></p>
                    <?php endif; ?>
                  </td>
                  <td class="py-3 pr-4 text-xs text-brand-muted break-all"><?= htmlspecialchars((string)$req['destination_address']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  <?php endif; ?>
</div>

<script>
  const cryptoWalletMeta = <?= json_encode($walletMetaJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

  function cryptoVaultSyncWallet() {
    const code = document.getElementById('deposit_currency').value;
    const meta = code && cryptoWalletMeta[code] ? cryptoWalletMeta[code] : null;
    const title = document.getElementById('crypto_wallet_title');
    const network = document.getElementById('crypto_wallet_network');
    const label = document.getElementById('crypto_wallet_label');
    const address = document.getElementById('crypto_wallet_address');
    const instructions = document.getElementById('crypto_wallet_instructions');
    const qrWrap = document.getElementById('crypto_wallet_qr_wrap');
    const qrImg = document.getElementById('crypto_wallet_qr');

    if (!meta) {
      title.textContent = 'Select a currency to see the wallet';
      network.textContent = '';
      label.textContent = '';
      address.textContent = '';
      instructions.textContent = '';
      qrWrap.classList.add('hidden');
      qrImg.src = '';
      return;
    }

    title.textContent = code + ' deposit wallet';
    network.textContent = meta.network_name ? 'Network: ' + meta.network_name : '';
    label.textContent = meta.wallet_label || '';
    address.textContent = meta.wallet_address || '';
    instructions.textContent = meta.instructions || '';

    if (meta.qr_code_path) {
      qrImg.src = meta.qr_code_path;
      qrWrap.classList.remove('hidden');
    } else {
      qrImg.src = '';
      qrWrap.classList.add('hidden');
    }
  }
</script>

<?php require_once __DIR__ . '/partials/shell-close.php'; ?>