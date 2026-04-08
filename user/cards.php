<?php
session_start();

include_once 'session.php';
require_once 'class.user.php';

if (!isset($_SESSION['acc_no'])) {
    header('Location: login.php');
    exit();
}
if (!isset($_SESSION['mname'])) {
    header('Location: passcode.php');
    exit();
}

$reg_user = new USER();
$flashMessage = '';
$flashType = 'success';

$stmt = $reg_user->runQuery('SELECT * FROM account WHERE acc_no = :acc_no LIMIT 1');
$stmt->execute([':acc_no' => (string)$_SESSION['acc_no']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    header('Location: logout.php');
    exit();
}

$accNo = (string)$_SESSION['acc_no'];
$currencyCode = strtoupper(trim((string)($row['currency'] ?? 'USD')));

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

if (isset($_POST['submit_card_request'])) {
    $cardType = strtolower(trim((string)($_POST['card_type'] ?? 'debit')));
    $cardTier = strtolower(trim((string)($_POST['card_tier'] ?? 'standard')));
    $allowedTypes = ['debit', 'virtual', 'credit', 'prepaid'];
    $allowedTiers = ['standard', 'gold', 'platinum'];

    if (!in_array($cardType, $allowedTypes, true) || !in_array($cardTier, $allowedTiers, true)) {
        $flashType = 'error';
        $flashMessage = 'Invalid card request options selected.';
    } else {
        try {
            $requestRef = 'CR' . date('YmdHis') . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
            $now = date('Y-m-d H:i:s');
            $ins = $reg_user->runQuery('INSERT INTO card_requests
                (request_ref, acc_no, card_type, card_tier, status, issue_note, requested_at, updated_at)
                VALUES (:request_ref, :acc_no, :card_type, :card_tier, :status, :issue_note, :requested_at, :updated_at)');
            $ins->execute([
                ':request_ref' => $requestRef,
                ':acc_no' => $accNo,
                ':card_type' => $cardType,
                ':card_tier' => $cardTier,
                ':status' => 'requested',
                ':issue_note' => null,
                ':requested_at' => $now,
                ':updated_at' => $now,
            ]);

            $flashType = 'success';
            $flashMessage = 'Card request submitted successfully. Reference: ' . $requestRef;
        } catch (Throwable $e) {
            $flashType = 'error';
            $flashMessage = 'Unable to submit card request right now. Please try again.';
        }
    }
}

$requestRows = [];
$issuedCards = [];
try {
    $reqStmt = $reg_user->runQuery('SELECT request_ref, card_type, card_tier, status, issue_note, requested_at, updated_at
        FROM card_requests
        WHERE acc_no = :acc_no
        ORDER BY id DESC
        LIMIT 8');
    $reqStmt->execute([':acc_no' => $accNo]);
    $requestRows = $reqStmt->fetchAll(PDO::FETCH_ASSOC);

    $cardStmt = $reg_user->runQuery('SELECT card_ref, masked_pan, expiry_mm_yy, status, card_limit, currency_code, created_at
        FROM cards
        WHERE acc_no = :acc_no
        ORDER BY id DESC
        LIMIT 5');
    $cardStmt->execute([':acc_no' => $accNo]);
    $issuedCards = $cardStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
}

include_once 'counter.php';
require_once __DIR__ . '/partials/shell-data.php';
$shellPageTitle = 'Cards';
require_once __DIR__ . '/partials/shell-open.php';
?>

<?php if ($flashMessage !== ''): ?>
<div class="mb-5 rounded-xl p-4 <?= $flashType === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' ?> text-sm">
  <?= htmlspecialchars($flashMessage) ?>
</div>
<?php endif; ?>

<div class="mb-6">
  <h1 class="text-2xl font-bold text-gray-900">Card Center</h1>
  <p class="text-sm text-gray-500 mt-1">Request cards and track issuance stages from request to activation.</p>
</div>

<div class="grid gap-6 lg:grid-cols-2">
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
    <h2 class="text-lg font-semibold text-gray-900">Request New Card</h2>
    <form method="POST" class="mt-4 space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Card Type</label>
        <select name="card_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
          <option value="debit">Debit</option>
          <option value="virtual">Virtual</option>
          <option value="credit">Credit</option>
          <option value="prepaid">Prepaid</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Card Tier</label>
        <select name="card_tier" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
          <option value="standard">Standard</option>
          <option value="gold">Gold</option>
          <option value="platinum">Platinum</option>
        </select>
      </div>
      <button type="submit" name="submit_card_request" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">Submit Card Request</button>
    </form>
  </div>

  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
    <h2 class="text-lg font-semibold text-gray-900">Issued Cards</h2>
    <?php if (empty($issuedCards)): ?>
      <p class="mt-3 text-sm text-gray-500">No issued cards yet.</p>
    <?php else: ?>
      <div class="mt-4 space-y-3">
        <?php foreach ($issuedCards as $card): ?>
          <div class="rounded-xl border border-gray-200 p-4">
            <div class="flex items-center justify-between">
              <p class="font-mono text-sm text-gray-800"><?= htmlspecialchars((string)$card['masked_pan']) ?></p>
              <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700"><?= htmlspecialchars(strtoupper((string)$card['status'])) ?></span>
            </div>
            <p class="mt-1 text-xs text-gray-500">Ref: <?= htmlspecialchars((string)$card['card_ref']) ?> | Exp: <?= htmlspecialchars((string)$card['expiry_mm_yy']) ?></p>
            <p class="mt-1 text-xs text-gray-500">Limit: <?= htmlspecialchars((string)$card['currency_code']) ?> <?= number_format((float)$card['card_limit'], 2) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-semibold text-gray-900">Card Request Timeline</h2>
    <p class="text-xs text-gray-500">Requested → KYC Check → Approved → Issued → Activated</p>
  </div>
  <?php if (empty($requestRows)): ?>
    <p class="text-sm text-gray-500">No card requests yet.</p>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500">
            <th class="py-2 pr-4">Reference</th>
            <th class="py-2 pr-4">Type</th>
            <th class="py-2 pr-4">Tier</th>
            <th class="py-2 pr-4">Status</th>
            <th class="py-2 pr-4">Requested</th>
            <th class="py-2 pr-4">Note</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($requestRows as $req): ?>
            <tr class="border-b border-gray-100">
              <td class="py-2 pr-4 font-mono text-xs text-gray-700"><?= htmlspecialchars((string)$req['request_ref']) ?></td>
              <td class="py-2 pr-4 text-gray-700"><?= htmlspecialchars(strtoupper((string)$req['card_type'])) ?></td>
              <td class="py-2 pr-4 text-gray-700"><?= htmlspecialchars(strtoupper((string)$req['card_tier'])) ?></td>
              <td class="py-2 pr-4"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', (string)$req['status']))) ?></span></td>
              <td class="py-2 pr-4 text-gray-500"><?= htmlspecialchars((string)$req['requested_at']) ?></td>
              <td class="py-2 pr-4 text-gray-500"><?= htmlspecialchars((string)($req['issue_note'] ?? '')) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/shell-close.php'; exit(); ?>
