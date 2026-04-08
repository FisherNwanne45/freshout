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

try {
    $reg_user->runQuery("CREATE TABLE IF NOT EXISTS card_requests (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        request_ref VARCHAR(32) NOT NULL,
        acc_no VARCHAR(50) NOT NULL,
        card_type VARCHAR(30) NOT NULL DEFAULT 'debit',
        card_tier VARCHAR(30) NOT NULL DEFAULT 'standard',
        status VARCHAR(30) NOT NULL DEFAULT 'requested',
        issue_note TEXT NULL,
        requested_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        UNIQUE KEY uq_card_request_ref (request_ref),
        KEY idx_card_request_acc_status (acc_no, status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();

    $reg_user->runQuery("CREATE TABLE IF NOT EXISTS cards (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        card_ref VARCHAR(32) NOT NULL,
        acc_no VARCHAR(50) NOT NULL,
        request_id BIGINT UNSIGNED NULL,
        masked_pan VARCHAR(24) NOT NULL,
        token_ref VARCHAR(128) NULL,
        expiry_mm_yy VARCHAR(10) NOT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'issued',
        card_limit DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        currency_code VARCHAR(10) NOT NULL DEFAULT 'USD',
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        UNIQUE KEY uq_card_ref (card_ref),
        KEY idx_cards_acc_status (acc_no, status),
        KEY idx_cards_request (request_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();
} catch (Throwable $e) {
}

$allowedStatuses = ['requested','kyc_check','approved','rejected','issued','activated','blocked','closed'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_card_request'])) {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $nextStatus = strtolower(trim((string)($_POST['next_status'] ?? 'requested')));
    $issueNote = trim((string)($_POST['issue_note'] ?? ''));

    if ($requestId <= 0 || !in_array($nextStatus, $allowedStatuses, true)) {
        $flash = '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">Invalid card request update.</div>';
    } else {
        try {
            $now = date('Y-m-d H:i:s');
            $up = $reg_user->runQuery('UPDATE card_requests SET status = :status, issue_note = :issue_note, updated_at = :updated_at WHERE id = :id');
            $up->execute([
                ':status' => $nextStatus,
                ':issue_note' => $issueNote,
                ':updated_at' => $now,
                ':id' => $requestId,
            ]);
            $flash = '<div class="rounded-lg bg-green-50 border border-green-200 px-4 py-2 text-green-700 text-sm mb-4">Card request updated successfully.</div>';
        } catch (Throwable $e) {
            $flash = '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">Unable to update card request.</div>';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue_card'])) {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $accNo = trim((string)($_POST['acc_no'] ?? ''));
    $cardLimit = (float)($_POST['card_limit'] ?? 0);
    $currencyCode = strtoupper(trim((string)($_POST['currency_code'] ?? 'USD')));

    if ($requestId <= 0 || $accNo === '') {
        $flash = '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">Invalid card issuance data.</div>';
    } else {
        try {
            $now = date('Y-m-d H:i:s');
            $last4 = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $maskedPan = '5301 98** **** ' . $last4;
            $cardRef = 'CD' . date('YmdHis') . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
            $expiry = date('m/y', strtotime('+3 years'));

            $insCard = $reg_user->runQuery('INSERT INTO cards
                (card_ref, acc_no, request_id, masked_pan, token_ref, expiry_mm_yy, status, card_limit, currency_code, created_at, updated_at)
                VALUES
                (:card_ref, :acc_no, :request_id, :masked_pan, :token_ref, :expiry_mm_yy, :status, :card_limit, :currency_code, :created_at, :updated_at)');
            $insCard->execute([
                ':card_ref' => $cardRef,
                ':acc_no' => $accNo,
                ':request_id' => $requestId,
                ':masked_pan' => $maskedPan,
                ':token_ref' => null,
                ':expiry_mm_yy' => $expiry,
                ':status' => 'issued',
                ':card_limit' => $cardLimit,
                ':currency_code' => $currencyCode,
                ':created_at' => $now,
                ':updated_at' => $now,
            ]);

            $upReq = $reg_user->runQuery('UPDATE card_requests SET status = :status, issue_note = :issue_note, updated_at = :updated_at WHERE id = :id');
            $upReq->execute([
                ':status' => 'issued',
                ':issue_note' => 'Card issued: ' . $cardRef,
                ':updated_at' => $now,
                ':id' => $requestId,
            ]);

            $upAccount = $reg_user->runQuery('UPDATE account SET ccard = :ccard, ccdate = :ccdate WHERE acc_no = :acc_no');
            $upAccount->execute([
                ':ccard' => str_replace(' ', '', $maskedPan),
                ':ccdate' => $expiry,
                ':acc_no' => $accNo,
            ]);

            $flash = '<div class="rounded-lg bg-green-50 border border-green-200 px-4 py-2 text-green-700 text-sm mb-4">Card issued successfully.</div>';
        } catch (Throwable $e) {
            $flash = '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">Unable to issue card.</div>';
        }
    }
}

$listStmt = $reg_user->runQuery('SELECT id, request_ref, acc_no, card_type, card_tier, status, issue_note, requested_at, updated_at
    FROM card_requests
    ORDER BY id DESC
    LIMIT 300');
$listStmt->execute();
$rows = $listStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Card Requests';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if ($flash !== '') echo $flash; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-semibold text-gray-800">Card Issuance Queue</h2>
    <span class="text-xs text-gray-500">requested → kyc_check → approved/rejected → issued → activated</span>
  </div>

  <div class="mb-4">
    <input type="text" id="card-search" onkeyup="filterTable('card-search','card-table')"
      placeholder="Search by request ref, account, type, tier"
      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 max-w-lg">
  </div>

  <div class="overflow-x-auto -mx-6 px-6">
    <table id="card-table" class="min-w-full text-sm border-collapse">
      <thead>
        <tr class="bg-gray-50 border-y border-gray-200">
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Request Ref</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Account</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Card</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Status</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Requested</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Update</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Issue</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 align-top">
        <?php foreach ($rows as $row): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-3 py-3 text-xs font-mono text-gray-700"><?= htmlspecialchars((string)$row['request_ref']) ?></td>
            <td class="px-3 py-3 text-xs text-gray-700"><?= htmlspecialchars((string)$row['acc_no']) ?></td>
            <td class="px-3 py-3 text-xs text-gray-700"><?= htmlspecialchars(strtoupper((string)$row['card_type'])) ?> / <?= htmlspecialchars(strtoupper((string)$row['card_tier'])) ?></td>
            <td class="px-3 py-3 text-xs text-gray-700"><span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', (string)$row['status']))) ?></span></td>
            <td class="px-3 py-3 text-xs text-gray-500"><?= htmlspecialchars((string)$row['requested_at']) ?></td>
            <td class="px-3 py-3 text-xs text-gray-700 min-w-[240px]">
              <form method="post" class="space-y-2">
                <input type="hidden" name="request_id" value="<?= (int)$row['id'] ?>">
                <select name="next_status" class="w-full rounded-lg border border-gray-300 px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <?php foreach ($allowedStatuses as $opt): ?>
                    <option value="<?= htmlspecialchars($opt) ?>" <?= $opt === strtolower((string)$row['status']) ? 'selected' : '' ?>><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $opt))) ?></option>
                  <?php endforeach; ?>
                </select>
                <textarea name="issue_note" rows="2" class="w-full rounded-lg border border-gray-300 px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Admin note"><?= htmlspecialchars((string)($row['issue_note'] ?? '')) ?></textarea>
                <button type="submit" name="update_card_request" class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">Update</button>
              </form>
            </td>
            <td class="px-3 py-3 text-xs text-gray-700 min-w-[220px]">
              <form method="post" class="space-y-2">
                <input type="hidden" name="request_id" value="<?= (int)$row['id'] ?>">
                <input type="hidden" name="acc_no" value="<?= htmlspecialchars((string)$row['acc_no']) ?>">
                <input type="hidden" name="currency_code" value="USD">
                <input type="number" step="0.01" min="0" name="card_limit" placeholder="Card limit" class="w-full rounded-lg border border-gray-300 px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" name="issue_card" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">Issue Card</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if (empty($rows)): ?>
      <p class="py-5 text-sm text-gray-500">No card requests found.</p>
    <?php endif; ?>
  </div>
</div>

<script>
function filterTable(inputId, tableId) {
  const q = document.getElementById(inputId).value.toLowerCase();
  document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}
</script>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
