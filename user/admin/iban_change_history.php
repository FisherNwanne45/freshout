<?php
// Admin: View IBAN Change History
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: login.php");
    exit;
}

require_once __DIR__ . '/connectdb.php';
require_once __DIR__ . '/class.admin.php';

$admin = new USER();

// Get selected customer account or load from query
$selectedCustAccId = (int)($_POST['customer_account_id'] ?? $_GET['customer_account_id'] ?? 0);
$history = [];
$accInfo = [];

if ($selectedCustAccId > 0) {
    // Load history
    $history = $admin->getIBANChangeHistory($selectedCustAccId);

  $hasIbanCustomCol = false;
  $hasIbanUpdatedAtCol = false;
  $colRes = $conn->query("SHOW COLUMNS FROM customer_accounts");
  if ($colRes) {
    while ($col = $colRes->fetch_assoc()) {
      $field = (string)($col['Field'] ?? '');
      if ($field === 'iban_custom') {
        $hasIbanCustomCol = true;
      } elseif ($field === 'iban_updated_at') {
        $hasIbanUpdatedAtCol = true;
      }
    }
  }

  $ibanCustomSelect = $hasIbanCustomCol ? 'ca.iban_custom' : '0 AS iban_custom';
  $ibanUpdatedAtSelect = $hasIbanUpdatedAtCol ? 'ca.iban_updated_at' : 'NULL AS iban_updated_at';
    
    // Load account info
  $accRes = $conn->query("SELECT ca.id, ca.owner_acc_no, ca.currency_code, ca.iban, {$ibanCustomSelect}, {$ibanUpdatedAtSelect},
                                     acc.fname, acc.lname, c.name as currency_name
                            FROM customer_accounts ca
                            LEFT JOIN account acc ON acc.acc_no = ca.owner_acc_no
                            LEFT JOIN currencies c ON c.code = ca.currency_code
                            WHERE ca.id = $selectedCustAccId LIMIT 1");
    if ($accRes) {
        $accInfo = $accRes->fetch_assoc();
    }
}

// Load last 100 customer accounts for dropdown
$allAccounts = [];
$allRes = $conn->query("SELECT ca.id, ca.owner_acc_no, ca.currency_code, acc.fname, acc.lname, c.name as currency_name
                        FROM customer_accounts ca
                        LEFT JOIN account acc ON acc.acc_no = ca.owner_acc_no
                        LEFT JOIN currencies c ON c.code = ca.currency_code
                        ORDER BY ca.id DESC LIMIT 100");
if ($allRes) {
    while ($row = $allRes->fetch_assoc()) {
        $allAccounts[] = $row;
    }
}

$pageTitle = 'IBAN Change History';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-6xl mb-6">
  <h2 class="font-semibold text-gray-800 mb-4">IBAN Change History</h2>
  
  <!-- Account Selector -->
  <form method="POST" class="flex gap-3 items-end mb-6">
    <div class="flex-1">
      <label class="block text-xs font-medium text-gray-700 mb-1">Select Customer Account</label>
      <select name="customer_account_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required onchange="this.form.submit()">
        <option value="">Choose account…</option>
        <?php foreach ($allAccounts as $acc): ?>
          <option value="<?= (int)$acc['id'] ?>" <?= ($selectedCustAccId === (int)$acc['id'] ? 'selected' : '') ?>>
            <?= htmlspecialchars($acc['owner_acc_no']) ?> – <?= htmlspecialchars($acc['fname'] . ' ' . $acc['lname']) ?> (<?= htmlspecialchars($acc['currency_code']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>

  <?php if ($selectedCustAccId > 0 && !empty($accInfo)): ?>
  
  <!-- Account Summary -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="rounded-lg bg-blue-50 border border-blue-200 p-3">
      <p class="text-xs text-blue-600 uppercase tracking-wide font-semibold mb-1">Account Number</p>
      <p class="text-sm font-mono text-gray-800"><?= htmlspecialchars($accInfo['owner_acc_no'] ?? '—') ?></p>
    </div>
    <div class="rounded-lg bg-blue-50 border border-blue-200 p-3">
      <p class="text-xs text-blue-600 uppercase tracking-wide font-semibold mb-1">Owner</p>
      <p class="text-sm text-gray-800"><?= htmlspecialchars(($accInfo['fname'] ?? '') . ' ' . ($accInfo['lname'] ?? '')) ?></p>
    </div>
    <div class="rounded-lg bg-blue-50 border border-blue-200 p-3">
      <p class="text-xs text-blue-600 uppercase tracking-wide font-semibold mb-1">Currency</p>
      <p class="text-sm text-gray-800"><?= htmlspecialchars($accInfo['currency_code'] ?? '—') ?></p>
    </div>
    <div class="rounded-lg bg-blue-50 border border-blue-200 p-3">
      <p class="text-xs text-blue-600 uppercase tracking-wide font-semibold mb-1">Current IBAN</p>
      <p class="text-sm font-mono text-gray-800" title="<?= htmlspecialchars($accInfo['iban_custom'] ? $accInfo['iban_custom'] : ($accInfo['iban'] ?? ''))  ?>">
        <?= htmlspecialchars($accInfo['iban_custom'] ? $accInfo['iban_custom'] : ($accInfo['iban'] ?? '—')) ?>
      </p>
    </div>
  </div>

  <!-- History Timeline -->
  <?php if (!empty($history)): ?>
  <div class="relative">
    <div class="space-y-4">
      <?php foreach ($history as $idx => $change): 
        $isLast = ($idx === count($history) - 1);
      ?>
      <div class="flex gap-4">
        <!-- Timeline Node -->
        <div class="flex flex-col items-center">
          <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">
            <?= (count($history) - $idx) ?>
          </div>
          <?php if (!$isLast): ?>
          <div class="w-0.5 h-12 bg-gray-200 mt-2"></div>
          <?php endif; ?>
        </div>

        <!-- Change Details -->
        <div class="flex-1 pb-2">
          <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <div class="flex items-start justify-between mb-2">
              <div>
                <p class="font-semibold text-gray-800">
                  <?= $change['old_iban'] ? 'IBAN Changed' : 'IBAN Created' ?>
                </p>
                <p class="text-xs text-gray-500">
                  <?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($change['changed_at']))) ?>
                </p>
              </div>
              <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-amber-100 text-amber-800">
                <?= htmlspecialchars($change['changed_by']) ?>
              </span>
            </div>

            <!-- IBAN Values -->
            <div class="space-y-2 mt-3 text-sm">
              <?php if ($change['old_iban']): ?>
              <div>
                <p class="text-xs text-gray-500 uppercase font-semibold mb-0.5">From (Old IBAN)</p>
                <p class="font-mono text-red-600 bg-red-50 rounded px-2 py-1 border border-red-200">
                  <?= htmlspecialchars($change['old_iban']) ?>
                </p>
              </div>
              <?php endif; ?>
              <div>
                <p class="text-xs text-gray-500 uppercase font-semibold mb-0.5">To (New IBAN)</p>
                <p class="font-mono text-green-600 bg-green-50 rounded px-2 py-1 border border-green-200">
                  <?= htmlspecialchars($change['new_iban']) ?>
                </p>
              </div>
            </div>

            <!-- Change Reason -->
            <?php if (!empty($change['change_reason'])): ?>
            <div class="mt-3 p-2 bg-blue-50 border border-blue-200 rounded text-sm text-blue-700">
              <strong>Reason:</strong> <?= htmlspecialchars($change['change_reason']) ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?php else: ?>
  <div class="rounded-lg bg-yellow-50 border border-yellow-200 px-4 py-3 text-yellow-700 text-sm">
    <i class="fa-solid fa-circle-info mr-2"></i> No IBAN changes recorded for this account.
  </div>
  <?php endif; ?>

  <?php else: ?>
  <div class="rounded-lg bg-gray-100 border border-gray-300 px-4 py-3 text-gray-600 text-sm">
    Select a customer account above to view its IBAN change history.
  </div>
  <?php endif; ?>

  <!-- Back Link -->
  <div class="border-t border-gray-100 mt-6 pt-4">
    <a href="edit_account.php" class="inline-flex items-center gap-2 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors border border-gray-200">
      <i class="fa-solid fa-arrow-left"></i> Back to Accounts
    </a>
  </div>
</div>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
