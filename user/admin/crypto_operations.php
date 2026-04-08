<?php
session_start();
require_once 'class.admin.php';
include_once 'session.php';

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$reg_user = new USER();
$flash = '';
$adminEmail = (string)($_SESSION['email'] ?? '');

function admin_crypto_flash(string $message, string $type = 'success'): string
{
    $classes = $type === 'success'
        ? 'bg-green-50 border-green-200 text-green-700'
        : 'bg-red-50 border-red-200 text-red-700';
    return '<div class="rounded-lg border px-4 py-2 text-sm mb-4 ' . $classes . '">' . htmlspecialchars($message) . '</div>';
}

function admin_store_qr_code(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [true, ''];
    }
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
      return [false, 'Unable to upload QR code image (upload error code ' . (int)($file['error'] ?? -1) . ').'];
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    $mime = '';
    if (class_exists('finfo')) {
      $fi = new finfo(FILEINFO_MIME_TYPE);
      $detectedMime = $fi->file((string)($file['tmp_name'] ?? ''));
      if (is_string($detectedMime)) {
        $mime = $detectedMime;
      }
    }
    if ($mime === '' || !isset($allowed[$mime])) {
        return [false, 'QR code must be JPG, PNG, WEBP, or GIF.'];
    }

    if ((int)($file['size'] ?? 0) > 5 * 1024 * 1024) {
        return [false, 'QR code image must be 5MB or smaller.'];
    }

    $targetDir = dirname(__DIR__) . '/uploads/crypto-qrcodes';
    if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
        return [false, 'Unable to prepare QR upload folder.'];
    }

    if (!is_writable($targetDir)) {
      @chmod($targetDir, 0777);
    }
    if (!is_writable($targetDir)) {
      return [false, 'QR upload folder is not writable by PHP.'];
    }

    $tmpPath = (string)($file['tmp_name'] ?? '');
    if ($tmpPath === '' || !is_file($tmpPath)) {
      return [false, 'Temporary upload file was not found on server.'];
    }

    $name = 'qr_' . date('YmdHis') . '_' . substr(bin2hex(random_bytes(6)), 0, 12) . '.' . $allowed[$mime];
    $targetPath = $targetDir . '/' . $name;
    if (!move_uploaded_file($tmpPath, $targetPath)) {
      // Fallback for environments where move_uploaded_file can fail unexpectedly.
      if (!@copy($tmpPath, $targetPath)) {
        return [false, 'Unable to save QR code image. Ensure upload folders are writable.'];
      }
    }

    if (!is_file($targetPath)) {
        return [false, 'Unable to save QR code image.'];
    }

    return [true, 'uploads/crypto-qrcodes/' . $name];
}

  function admin_crypto_asset_href(?string $path): string
  {
    $path = trim((string)$path);
    if ($path === '') {
      return '';
    }
    if (preg_match('#^(https?:)?//#i', $path)) {
      return $path;
    }
    while (strpos($path, '../') === 0) {
      $path = substr($path, 3);
    }
    return '../' . ltrim($path, '/');
  }

try {
    $reg_user->runQuery("CREATE TABLE IF NOT EXISTS crypto_deposit_wallets (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        currency_code VARCHAR(10) NOT NULL,
        network_name VARCHAR(60) NOT NULL DEFAULT '',
        wallet_label VARCHAR(120) NOT NULL DEFAULT '',
        wallet_address VARCHAR(255) NOT NULL DEFAULT '',
        qr_code_path VARCHAR(255) NULL DEFAULT NULL,
        instructions TEXT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        UNIQUE KEY uq_crypto_wallet_currency (currency_code),
        KEY idx_crypto_wallet_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();

    $reg_user->runQuery("CREATE TABLE IF NOT EXISTS crypto_deposit_requests (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        deposit_ref VARCHAR(32) NOT NULL,
        acc_no VARCHAR(50) NOT NULL,
        email VARCHAR(190) NOT NULL,
        currency_code VARCHAR(10) NOT NULL,
        network_name VARCHAR(60) NOT NULL DEFAULT '',
        wallet_address VARCHAR(255) NOT NULL DEFAULT '',
        sender_wallet_address VARCHAR(255) NOT NULL DEFAULT '',
        tx_hash VARCHAR(255) NOT NULL DEFAULT '',
        amount DECIMAL(20,8) NOT NULL DEFAULT 0,
        proof_path VARCHAR(255) NULL DEFAULT NULL,
        user_note TEXT NULL,
        admin_note TEXT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        approved_by VARCHAR(190) NULL DEFAULT NULL,
        approved_at DATETIME NULL DEFAULT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        UNIQUE KEY uq_crypto_deposit_ref (deposit_ref),
        KEY idx_crypto_deposit_acc_status (acc_no, status),
        KEY idx_crypto_deposit_currency (currency_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();

    $reg_user->runQuery("CREATE TABLE IF NOT EXISTS crypto_withdrawal_requests (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        withdrawal_ref VARCHAR(32) NOT NULL,
        acc_no VARCHAR(50) NOT NULL,
        email VARCHAR(190) NOT NULL,
        currency_code VARCHAR(10) NOT NULL,
        network_name VARCHAR(60) NOT NULL DEFAULT '',
        destination_address VARCHAR(255) NOT NULL DEFAULT '',
        amount DECIMAL(20,8) NOT NULL DEFAULT 0,
        user_note TEXT NULL,
        admin_note TEXT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        processed_by VARCHAR(190) NULL DEFAULT NULL,
        processed_at DATETIME NULL DEFAULT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        UNIQUE KEY uq_crypto_withdrawal_ref (withdrawal_ref),
        KEY idx_crypto_withdrawal_acc_status (acc_no, status),
        KEY idx_crypto_withdrawal_currency (currency_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();
} catch (Throwable $e) {
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_wallet_config'])) {
    $currencyCode = strtoupper(trim((string)($_POST['currency_code'] ?? '')));
    $networkName = trim((string)($_POST['network_name'] ?? ''));
    $walletLabel = trim((string)($_POST['wallet_label'] ?? ''));
    $walletAddress = trim((string)($_POST['wallet_address'] ?? ''));
    $instructions = trim((string)($_POST['instructions'] ?? ''));
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (!preg_match('/^[A-Z0-9]{2,10}$/', $currencyCode)) {
        $flash = admin_crypto_flash('Choose a valid crypto currency code.', 'error');
    } elseif ($walletAddress === '') {
        $flash = admin_crypto_flash('Wallet address is required.', 'error');
    } else {
        try {
            $existingQr = '';
            $existing = $reg_user->runQuery('SELECT qr_code_path FROM crypto_deposit_wallets WHERE currency_code = :currency_code LIMIT 1');
            $existing->execute([':currency_code' => $currencyCode]);
            $existingRow = $existing->fetch(PDO::FETCH_ASSOC) ?: [];
            $existingQr = (string)($existingRow['qr_code_path'] ?? '');

            [$ok, $qrPath] = admin_store_qr_code($_FILES['qr_code_file'] ?? []);
            if (!$ok) {
                throw new RuntimeException($qrPath);
            }
            if ($qrPath === '') {
                $qrPath = $existingQr;
            }

            $now = date('Y-m-d H:i:s');
            $upsert = $reg_user->runQuery('INSERT INTO crypto_deposit_wallets
                (currency_code, network_name, wallet_label, wallet_address, qr_code_path, instructions, is_active, created_at, updated_at)
                VALUES
                (:currency_code, :network_name, :wallet_label, :wallet_address, :qr_code_path, :instructions, :is_active, :created_at, :updated_at)
                ON DUPLICATE KEY UPDATE
                    network_name = VALUES(network_name),
                    wallet_label = VALUES(wallet_label),
                    wallet_address = VALUES(wallet_address),
                    qr_code_path = VALUES(qr_code_path),
                    instructions = VALUES(instructions),
                    is_active = VALUES(is_active),
                    updated_at = VALUES(updated_at)');
            $upsert->execute([
                ':currency_code' => $currencyCode,
                ':network_name' => $networkName,
                ':wallet_label' => $walletLabel,
                ':wallet_address' => $walletAddress,
                ':qr_code_path' => $qrPath !== '' ? $qrPath : null,
                ':instructions' => $instructions,
                ':is_active' => $isActive,
                ':created_at' => $now,
                ':updated_at' => $now,
            ]);
            $flash = admin_crypto_flash('Crypto deposit wallet saved.');
        } catch (Throwable $e) {
            $flash = admin_crypto_flash($e->getMessage() !== '' ? $e->getMessage() : 'Unable to save wallet configuration.', 'error');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['handle_deposit_request'])) {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $action = strtolower(trim((string)($_POST['action_name'] ?? '')));
    $adminNote = trim((string)($_POST['admin_note'] ?? ''));

    if ($requestId <= 0 || !in_array($action, ['approve', 'reject'], true)) {
        $flash = admin_crypto_flash('Invalid deposit request action.', 'error');
    } else {
        try {
            if ($action === 'approve') {
                $reg_user->runQuery('START TRANSACTION')->execute();
                $fetch = $reg_user->runQuery('SELECT * FROM crypto_deposit_requests WHERE id = :id FOR UPDATE');
                $fetch->execute([':id' => $requestId]);
                $req = $fetch->fetch(PDO::FETCH_ASSOC);
                if (!$req) {
                    throw new RuntimeException('Deposit request not found.');
                }
                if (strtolower((string)($req['status'] ?? 'pending')) !== 'pending') {
                    throw new RuntimeException('Only pending deposit requests can be approved.');
                }

                $accNo = (string)($req['acc_no'] ?? '');
                $cur = strtoupper((string)($req['currency_code'] ?? ''));
                $amt = (float)($req['amount'] ?? 0);
                $walletNo = $accNo . '-' . $cur;
                if ($amt <= 0 || $accNo === '' || $cur === '') {
                    throw new RuntimeException('Deposit request data is invalid.');
                }

                $reg_user->runQuery('INSERT INTO account_balances (acc_no, currency_code, balance)
                    VALUES (:acc_no, :currency_code, 0)
                    ON DUPLICATE KEY UPDATE acc_no = VALUES(acc_no)')
                    ->execute([':acc_no' => $accNo, ':currency_code' => $cur]);
                $reg_user->runQuery('UPDATE account_balances SET balance = balance + :amount WHERE acc_no = :acc_no AND currency_code = :currency_code')
                    ->execute([':amount' => $amt, ':acc_no' => $accNo, ':currency_code' => $cur]);

                $reg_user->runQuery('INSERT INTO customer_accounts (owner_acc_no, account_no, currency_code, balance, status, is_primary)
                    VALUES (:owner_acc_no, :account_no, :currency_code, 0, :status, 0)
                    ON DUPLICATE KEY UPDATE owner_acc_no = VALUES(owner_acc_no)')
                    ->execute([
                        ':owner_acc_no' => $accNo,
                        ':account_no' => $walletNo,
                        ':currency_code' => $cur,
                        ':status' => 'active',
                    ]);
                $reg_user->runQuery('UPDATE customer_accounts SET balance = balance + :amount WHERE owner_acc_no = :acc_no AND currency_code = :currency_code')
                    ->execute([':amount' => $amt, ':acc_no' => $accNo, ':currency_code' => $cur]);

                $upd = $reg_user->runQuery('UPDATE crypto_deposit_requests
                    SET status = :status, admin_note = :admin_note, approved_by = :approved_by, approved_at = :approved_at, updated_at = :updated_at
                    WHERE id = :id');
                $upd->execute([
                    ':status' => 'approved',
                    ':admin_note' => $adminNote,
                    ':approved_by' => $adminEmail,
                    ':approved_at' => date('Y-m-d H:i:s'),
                    ':updated_at' => date('Y-m-d H:i:s'),
                    ':id' => $requestId,
                ]);

                try {
                    $alert = $reg_user->runQuery('INSERT INTO alerts (uname, type, amount, sender_name, remarks, date, time)
                        VALUES (:uname, :type, :amount, :sender_name, :remarks, :date, :time)');
                    $alert->execute([
                        ':uname' => $accNo,
                        ':type' => 'Crypto Deposit Approved',
                        ':amount' => $amt,
                        ':sender_name' => $cur,
                        ':remarks' => 'Deposit ref ' . (string)($req['deposit_ref'] ?? ''),
                        ':date' => date('Y-m-d'),
                        ':time' => date('H:i:s'),
                    ]);
                } catch (Throwable $e) {
                }

                $reg_user->runQuery('COMMIT')->execute();
                $flash = admin_crypto_flash('Crypto deposit approved and credited.');
            } else {
                $upd = $reg_user->runQuery('UPDATE crypto_deposit_requests SET status = :status, admin_note = :admin_note, approved_by = :approved_by, approved_at = :approved_at, updated_at = :updated_at WHERE id = :id AND status = :current_status');
                $upd->execute([
                    ':status' => 'rejected',
                    ':admin_note' => $adminNote,
                    ':approved_by' => $adminEmail,
                    ':approved_at' => date('Y-m-d H:i:s'),
                    ':updated_at' => date('Y-m-d H:i:s'),
                    ':id' => $requestId,
                    ':current_status' => 'pending',
                ]);
                $flash = admin_crypto_flash('Crypto deposit request rejected.');
            }
        } catch (Throwable $e) {
            try {
                $reg_user->runQuery('ROLLBACK')->execute();
            } catch (Throwable $rollbackError) {
            }
            $flash = admin_crypto_flash($e->getMessage() !== '' ? $e->getMessage() : 'Unable to process deposit request.', 'error');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['handle_withdrawal_request'])) {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $action = strtolower(trim((string)($_POST['action_name'] ?? '')));
    $adminNote = trim((string)($_POST['admin_note'] ?? ''));

    if ($requestId <= 0 || !in_array($action, ['approve', 'paid', 'reject'], true)) {
        $flash = admin_crypto_flash('Invalid withdrawal request action.', 'error');
    } else {
        try {
            if ($action === 'reject') {
                $reg_user->runQuery('START TRANSACTION')->execute();
                $fetch = $reg_user->runQuery('SELECT * FROM crypto_withdrawal_requests WHERE id = :id FOR UPDATE');
                $fetch->execute([':id' => $requestId]);
                $req = $fetch->fetch(PDO::FETCH_ASSOC);
                if (!$req) {
                    throw new RuntimeException('Withdrawal request not found.');
                }
                $status = strtolower((string)($req['status'] ?? 'pending'));
                if (!in_array($status, ['pending', 'approved'], true)) {
                    throw new RuntimeException('Only pending or approved withdrawals can be rejected and refunded.');
                }

                $accNo = (string)($req['acc_no'] ?? '');
                $cur = strtoupper((string)($req['currency_code'] ?? ''));
                $amt = (float)($req['amount'] ?? 0);

                $reg_user->runQuery('INSERT INTO account_balances (acc_no, currency_code, balance)
                    VALUES (:acc_no, :currency_code, 0)
                    ON DUPLICATE KEY UPDATE acc_no = VALUES(acc_no)')
                    ->execute([':acc_no' => $accNo, ':currency_code' => $cur]);
                $reg_user->runQuery('UPDATE account_balances SET balance = balance + :amount WHERE acc_no = :acc_no AND currency_code = :currency_code')
                    ->execute([':amount' => $amt, ':acc_no' => $accNo, ':currency_code' => $cur]);
                $reg_user->runQuery('UPDATE customer_accounts SET balance = balance + :amount WHERE owner_acc_no = :acc_no AND currency_code = :currency_code')
                    ->execute([':amount' => $amt, ':acc_no' => $accNo, ':currency_code' => $cur]);

                $upd = $reg_user->runQuery('UPDATE crypto_withdrawal_requests
                    SET status = :status, admin_note = :admin_note, processed_by = :processed_by, processed_at = :processed_at, updated_at = :updated_at
                    WHERE id = :id');
                $upd->execute([
                    ':status' => 'rejected',
                    ':admin_note' => $adminNote,
                    ':processed_by' => $adminEmail,
                    ':processed_at' => date('Y-m-d H:i:s'),
                    ':updated_at' => date('Y-m-d H:i:s'),
                    ':id' => $requestId,
                ]);

                try {
                    $alert = $reg_user->runQuery('INSERT INTO alerts (uname, type, amount, sender_name, remarks, date, time)
                        VALUES (:uname, :type, :amount, :sender_name, :remarks, :date, :time)');
                    $alert->execute([
                        ':uname' => $accNo,
                        ':type' => 'Crypto Withdrawal Rejected',
                        ':amount' => $amt,
                        ':sender_name' => $cur,
                        ':remarks' => 'Funds restored for withdrawal ref ' . (string)($req['withdrawal_ref'] ?? ''),
                        ':date' => date('Y-m-d'),
                        ':time' => date('H:i:s'),
                    ]);
                } catch (Throwable $e) {
                }

                $reg_user->runQuery('COMMIT')->execute();
                $flash = admin_crypto_flash('Withdrawal rejected and funds restored.');
            } else {
                $targetStatus = $action === 'approve' ? 'approved' : 'paid';
                $allowedCurrent = $action === 'approve' ? ['pending'] : ['pending', 'approved'];

                $fetch = $reg_user->runQuery('SELECT status FROM crypto_withdrawal_requests WHERE id = :id LIMIT 1');
                $fetch->execute([':id' => $requestId]);
                $req = $fetch->fetch(PDO::FETCH_ASSOC);
                if (!$req) {
                    throw new RuntimeException('Withdrawal request not found.');
                }
                if (!in_array(strtolower((string)($req['status'] ?? 'pending')), $allowedCurrent, true)) {
                    throw new RuntimeException('Withdrawal status cannot be changed with this action.');
                }

                $upd = $reg_user->runQuery('UPDATE crypto_withdrawal_requests
                    SET status = :status, admin_note = :admin_note, processed_by = :processed_by, processed_at = :processed_at, updated_at = :updated_at
                    WHERE id = :id');
                $upd->execute([
                    ':status' => $targetStatus,
                    ':admin_note' => $adminNote,
                    ':processed_by' => $adminEmail,
                    ':processed_at' => date('Y-m-d H:i:s'),
                    ':updated_at' => date('Y-m-d H:i:s'),
                    ':id' => $requestId,
                ]);
                $flash = admin_crypto_flash($targetStatus === 'approved' ? 'Withdrawal approved for manual payout.' : 'Withdrawal marked as paid.');
            }
        } catch (Throwable $e) {
            try {
                $reg_user->runQuery('ROLLBACK')->execute();
            } catch (Throwable $rollbackError) {
            }
            $flash = admin_crypto_flash($e->getMessage() !== '' ? $e->getMessage() : 'Unable to process withdrawal request.', 'error');
        }
    }
}

$currencyStmt = $reg_user->runQuery('SELECT code, name, symbol FROM currencies WHERE is_active = 1 AND is_crypto = 1 ORDER BY sort_order, code');
$currencyStmt->execute();
$cryptoCurrencies = $currencyStmt->fetchAll(PDO::FETCH_ASSOC);

$configStmt = $reg_user->runQuery('SELECT cdw.*, c.name AS currency_name
    FROM crypto_deposit_wallets cdw
    LEFT JOIN currencies c ON c.code = cdw.currency_code
    ORDER BY cdw.currency_code');
$configStmt->execute();
$walletConfigs = $configStmt->fetchAll(PDO::FETCH_ASSOC);

$editCurrency = strtoupper(trim((string)($_GET['edit_currency'] ?? '')));
$editWallet = null;
if ($editCurrency !== '') {
  foreach ($walletConfigs as $cfg) {
    if (strtoupper((string)($cfg['currency_code'] ?? '')) === $editCurrency) {
      $editWallet = $cfg;
      break;
    }
  }
}

$depositStmt = $reg_user->runQuery('SELECT * FROM crypto_deposit_requests ORDER BY id DESC LIMIT 150');
$depositStmt->execute();
$depositRows = $depositStmt->fetchAll(PDO::FETCH_ASSOC);

$withdrawStmt = $reg_user->runQuery('SELECT * FROM crypto_withdrawal_requests ORDER BY id DESC LIMIT 150');
$withdrawStmt->execute();
$withdrawRows = $withdrawStmt->fetchAll(PDO::FETCH_ASSOC);

$pendingDeposits = 0;
foreach ($depositRows as $rowCount) {
    if (strtolower((string)($rowCount['status'] ?? '')) === 'pending') {
        $pendingDeposits++;
    }
}
$pendingWithdrawals = 0;
foreach ($withdrawRows as $rowCount) {
    if (strtolower((string)($rowCount['status'] ?? '')) === 'pending') {
        $pendingWithdrawals++;
    }
}

$pageTitle = 'Crypto Operations';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if ($flash !== '') echo $flash; ?>

<div class="grid gap-4 md:grid-cols-3 mb-6">
  <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
    <p class="text-xs uppercase tracking-wide text-gray-500">Configured Wallets</p>
    <p class="mt-2 text-2xl font-bold text-gray-900"><?= count($walletConfigs) ?></p>
  </div>
  <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
    <p class="text-xs uppercase tracking-wide text-amber-700">Pending Deposits</p>
    <p class="mt-2 text-2xl font-bold text-amber-800"><?= $pendingDeposits ?></p>
  </div>
  <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 shadow-sm">
    <p class="text-xs uppercase tracking-wide text-blue-700">Pending Withdrawals</p>
    <p class="mt-2 text-2xl font-bold text-blue-800"><?= $pendingWithdrawals ?></p>
  </div>
</div>

<div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
  <section class="space-y-6">
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-800"><?= $editWallet ? 'Edit Deposit Wallet' : 'Configure Deposit Wallet' ?></h2>
        <span class="text-xs text-gray-500"><?= $editWallet ? 'Updating ' . htmlspecialchars((string)$editWallet['currency_code']) : 'User-facing wallet + QR' ?></span>
      </div>
      <form method="post" enctype="multipart/form-data" class="grid gap-4">
        <div>
          <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Currency</label>
          <?php if ($editWallet): ?>
            <input type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-gray-50" value="<?= htmlspecialchars((string)$editWallet['currency_code']) ?> - <?= htmlspecialchars((string)($editWallet['currency_name'] ?? '')) ?>" readonly>
            <input type="hidden" name="currency_code" value="<?= htmlspecialchars((string)$editWallet['currency_code']) ?>">
          <?php else: ?>
            <select name="currency_code" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
              <option value="">Select crypto</option>
              <?php foreach ($cryptoCurrencies as $cur): ?>
                <option value="<?= htmlspecialchars((string)$cur['code']) ?>"><?= htmlspecialchars((string)$cur['code']) ?> - <?= htmlspecialchars((string)$cur['name']) ?></option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>
        </div>
        <div>
          <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Network</label>
          <input type="text" name="network_name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="ERC20, TRC20, Bitcoin, Solana..." value="<?= htmlspecialchars((string)($editWallet['network_name'] ?? '')) ?>">
        </div>
        <div>
          <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Wallet Label</label>
          <input type="text" name="wallet_label" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Main treasury wallet" value="<?= htmlspecialchars((string)($editWallet['wallet_label'] ?? '')) ?>">
        </div>
        <div>
          <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Wallet Address</label>
          <textarea name="wallet_address" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Deposit address" required><?= htmlspecialchars((string)($editWallet['wallet_address'] ?? '')) ?></textarea>
        </div>
        <div>
          <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">QR Code Image <?= $editWallet ? '(optional to replace)' : '' ?></label>
          <input type="file" name="qr_code_file" accept=".jpg,.jpeg,.png,.webp,.gif" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white">
          <?php if ($editWallet && !empty($editWallet['qr_code_path'])): ?>
            <p class="mt-1 text-xs text-gray-500">Current QR: <a href="<?= htmlspecialchars(admin_crypto_asset_href((string)$editWallet['qr_code_path'])) ?>" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline">Open current QR</a></p>
          <?php endif; ?>
        </div>
        <div>
          <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Instructions</label>
          <textarea name="instructions" rows="4" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Only send on the selected network. Minimum confirmation count, memo rules, etc."><?= htmlspecialchars((string)($editWallet['instructions'] ?? '')) ?></textarea>
        </div>
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
          <input type="checkbox" name="is_active" class="rounded border-gray-300" <?= !$editWallet || (int)($editWallet['is_active'] ?? 0) === 1 ? 'checked' : '' ?>>
          Active for user deposits
        </label>
        <div class="flex items-center gap-2">
          <button type="submit" name="save_wallet_config" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors"><?= $editWallet ? 'Update Wallet Config' : 'Save Wallet Config' ?></button>
          <?php if ($editWallet): ?>
            <a href="crypto_operations.php" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">Cancel Edit</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-800">Configured Crypto Wallets</h2>
        <span class="text-xs text-gray-500">Current user-facing deposit endpoints</span>
      </div>
      <?php if (empty($walletConfigs)): ?>
        <p class="text-sm text-gray-500">No crypto deposit wallet configured yet.</p>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($walletConfigs as $cfg): ?>
            <div class="rounded-xl border border-gray-200 p-4">
              <div class="flex items-start justify-between gap-4">
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-semibold text-gray-900"><?= htmlspecialchars((string)$cfg['currency_code']) ?> <span class="text-gray-500 font-normal">- <?= htmlspecialchars((string)($cfg['currency_name'] ?? '')) ?></span></p>
                  <p class="mt-1 text-xs text-gray-500"><?= htmlspecialchars((string)($cfg['network_name'] ?? '')) ?><?= !empty($cfg['wallet_label']) ? ' • ' . htmlspecialchars((string)$cfg['wallet_label']) : '' ?></p>
                  <p class="mt-2 break-all rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-700 border border-gray-200"><?= htmlspecialchars((string)($cfg['wallet_address'] ?? '')) ?></p>
                  <?php if (!empty($cfg['instructions'])): ?>
                    <p class="mt-2 text-xs text-gray-600"><?= nl2br(htmlspecialchars((string)$cfg['instructions'])) ?></p>
                  <?php endif; ?>
                </div>
                <div class="flex flex-col items-end gap-2">
                  <span class="rounded-full px-2 py-1 text-[11px] font-semibold <?= (int)($cfg['is_active'] ?? 0) === 1 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' ?>">
                    <?= (int)($cfg['is_active'] ?? 0) === 1 ? 'ACTIVE' : 'INACTIVE' ?>
                  </span>
                  <a href="crypto_operations.php?edit_currency=<?= urlencode((string)$cfg['currency_code']) ?>" class="text-xs text-blue-600 underline">Edit</a>
                  <?php if (!empty($cfg['qr_code_path'])): ?>
                    <a href="<?= htmlspecialchars(admin_crypto_asset_href((string)$cfg['qr_code_path'])) ?>" target="_blank" rel="noopener noreferrer" class="text-xs text-blue-600 underline">Open QR</a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="space-y-6">
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-800">Deposit Requests</h2>
        <span class="text-xs text-gray-500">Approve to credit wallet</span>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500">
              <th class="py-2 pr-4">Ref</th>
              <th class="py-2 pr-4">Account</th>
              <th class="py-2 pr-4">Asset</th>
              <th class="py-2 pr-4">Amount</th>
              <th class="py-2 pr-4">Proof</th>
              <th class="py-2 pr-4">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($depositRows)): ?>
              <tr><td colspan="6" class="py-4 text-sm text-gray-500">No crypto deposit requests yet.</td></tr>
            <?php else: ?>
              <?php foreach ($depositRows as $req): ?>
                <?php $status = strtolower((string)($req['status'] ?? 'pending')); ?>
                <tr class="border-b border-gray-100 align-top">
                  <td class="py-3 pr-4 text-xs font-mono text-gray-700"><?= htmlspecialchars((string)$req['deposit_ref']) ?></td>
                  <td class="py-3 pr-4 text-xs text-gray-700"><?= htmlspecialchars((string)$req['acc_no']) ?><br><span class="text-gray-500"><?= htmlspecialchars((string)$req['email']) ?></span></td>
                  <td class="py-3 pr-4 text-xs text-gray-700"><?= htmlspecialchars((string)$req['currency_code']) ?><br><span class="text-gray-500"><?= htmlspecialchars((string)$req['network_name']) ?></span></td>
                  <td class="py-3 pr-4 text-xs text-gray-700 font-semibold"><?= number_format((float)$req['amount'], 8) ?></td>
                  <td class="py-3 pr-4 text-xs">
                    <?php if (!empty($req['proof_path'])): ?>
                      <a href="<?= htmlspecialchars(admin_crypto_asset_href((string)$req['proof_path'])) ?>" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline">Proof</a>
                    <?php else: ?>
                      <span class="text-gray-400">None</span>
                    <?php endif; ?>
                    <div class="mt-1 rounded-full px-2 py-0.5 inline-block text-[11px] font-semibold <?= $status === 'approved' ? 'bg-green-100 text-green-700' : ($status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') ?>">
                      <?= htmlspecialchars(strtoupper($status)) ?>
                    </div>
                  </td>
                  <td class="py-3 pr-4 min-w-[220px]">
                    <form method="post" class="space-y-2">
                      <input type="hidden" name="request_id" value="<?= (int)$req['id'] ?>">
                      <textarea name="admin_note" rows="2" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-xs" placeholder="Admin note"><?= htmlspecialchars((string)($req['admin_note'] ?? '')) ?></textarea>
                      <div class="flex gap-2">
                        <button type="submit" name="handle_deposit_request" value="1" onclick="this.form.action_name.value='approve'" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">Approve</button>
                        <button type="submit" name="handle_deposit_request" value="1" onclick="this.form.action_name.value='reject'" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">Reject</button>
                      </div>
                      <input type="hidden" name="action_name" value="approve">
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-800">Withdrawal Requests</h2>
        <span class="text-xs text-gray-500">Debit already applied at request time</span>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500">
              <th class="py-2 pr-4">Ref</th>
              <th class="py-2 pr-4">Account</th>
              <th class="py-2 pr-4">Asset</th>
              <th class="py-2 pr-4">Amount</th>
              <th class="py-2 pr-4">Destination</th>
              <th class="py-2 pr-4">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($withdrawRows)): ?>
              <tr><td colspan="6" class="py-4 text-sm text-gray-500">No crypto withdrawal requests yet.</td></tr>
            <?php else: ?>
              <?php foreach ($withdrawRows as $req): ?>
                <?php $status = strtolower((string)($req['status'] ?? 'pending')); ?>
                <tr class="border-b border-gray-100 align-top">
                  <td class="py-3 pr-4 text-xs font-mono text-gray-700"><?= htmlspecialchars((string)$req['withdrawal_ref']) ?></td>
                  <td class="py-3 pr-4 text-xs text-gray-700"><?= htmlspecialchars((string)$req['acc_no']) ?><br><span class="text-gray-500"><?= htmlspecialchars((string)$req['email']) ?></span></td>
                  <td class="py-3 pr-4 text-xs text-gray-700"><?= htmlspecialchars((string)$req['currency_code']) ?><br><span class="text-gray-500"><?= htmlspecialchars((string)$req['network_name']) ?></span></td>
                  <td class="py-3 pr-4 text-xs text-gray-700 font-semibold"><?= number_format((float)$req['amount'], 8) ?></td>
                  <td class="py-3 pr-4 text-xs text-gray-500 break-all max-w-[220px]"><?= htmlspecialchars((string)$req['destination_address']) ?>
                    <div class="mt-1 rounded-full px-2 py-0.5 inline-block text-[11px] font-semibold <?= in_array($status, ['approved', 'paid'], true) ? 'bg-green-100 text-green-700' : ($status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') ?>">
                      <?= htmlspecialchars(strtoupper($status)) ?>
                    </div>
                  </td>
                  <td class="py-3 pr-4 min-w-[240px]">
                    <form method="post" class="space-y-2">
                      <input type="hidden" name="request_id" value="<?= (int)$req['id'] ?>">
                      <textarea name="admin_note" rows="2" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-xs" placeholder="Admin note"><?= htmlspecialchars((string)($req['admin_note'] ?? '')) ?></textarea>
                      <div class="flex flex-wrap gap-2">
                        <button type="submit" name="handle_withdrawal_request" value="1" onclick="this.form.action_name.value='approve'" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">Approve</button>
                        <button type="submit" name="handle_withdrawal_request" value="1" onclick="this.form.action_name.value='paid'" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">Mark Paid</button>
                        <button type="submit" name="handle_withdrawal_request" value="1" onclick="this.form.action_name.value='reject'" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">Reject + Refund</button>
                      </div>
                      <input type="hidden" name="action_name" value="approve">
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
