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
    $reg_user->runQuery("CREATE TABLE IF NOT EXISTS investment_accounts (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      account_ref VARCHAR(32) NOT NULL,
      acc_no VARCHAR(50) NOT NULL,
      base_currency VARCHAR(10) NOT NULL DEFAULT 'USD',
      risk_profile VARCHAR(30) NOT NULL DEFAULT 'moderate',
      status VARCHAR(30) NOT NULL DEFAULT 'active',
      created_at DATETIME NOT NULL,
      updated_at DATETIME NOT NULL,
      UNIQUE KEY uq_investment_account_ref (account_ref),
      KEY idx_investment_acc_status (acc_no, status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();

    $reg_user->runQuery("CREATE TABLE IF NOT EXISTS investment_positions (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      investment_account_id BIGINT UNSIGNED NOT NULL,
      symbol VARCHAR(30) NOT NULL,
      instrument_type VARCHAR(20) NOT NULL,
      quantity DECIMAL(24,8) NOT NULL DEFAULT 0,
      avg_price DECIMAL(18,6) NOT NULL DEFAULT 0,
      market_price DECIMAL(18,6) NOT NULL DEFAULT 0,
      market_value DECIMAL(18,2) NOT NULL DEFAULT 0,
      updated_at DATETIME NOT NULL,
      KEY idx_position_account (investment_account_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();
} catch (Throwable $e) {
}

$allowedStatuses = ['active', 'restricted', 'suspended', 'closed'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_invest_status'])) {
    $id = (int)($_POST['account_id'] ?? 0);
    $status = strtolower(trim((string)($_POST['next_status'] ?? 'active')));
    if ($id <= 0 || !in_array($status, $allowedStatuses, true)) {
        $flash = '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">Invalid investment account update.</div>';
    } else {
        try {
            $up = $reg_user->runQuery('UPDATE investment_accounts SET status = :status, updated_at = :updated_at WHERE id = :id');
            $up->execute([
                ':status' => $status,
                ':updated_at' => date('Y-m-d H:i:s'),
                ':id' => $id,
            ]);
            $flash = '<div class="rounded-lg bg-green-50 border border-green-200 px-4 py-2 text-green-700 text-sm mb-4">Investment account status updated.</div>';
        } catch (Throwable $e) {
            $flash = '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">Unable to update status.</div>';
        }
    }
}

$rows = [];
try {
    $s = $reg_user->runQuery('SELECT ia.*, COALESCE(SUM(ip.market_value),0) AS total_value
      FROM investment_accounts ia
      LEFT JOIN investment_positions ip ON ip.investment_account_id = ia.id
      GROUP BY ia.id
      ORDER BY ia.id DESC
      LIMIT 300');
    $s->execute();
    $rows = $s->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
}

$pageTitle = 'Investment Accounts';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if ($flash !== '') echo $flash; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-semibold text-gray-800">Investment Accounts</h2>
    <span class="text-xs text-gray-500">Operations and supervision</span>
  </div>

  <div class="mb-4">
    <input type="text" id="inv-search" onkeyup="filterTable('inv-search','inv-table')" placeholder="Search by ref or account"
      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 max-w-lg">
  </div>

  <div class="overflow-x-auto -mx-6 px-6">
    <table id="inv-table" class="min-w-full text-sm border-collapse">
      <thead>
        <tr class="bg-gray-50 border-y border-gray-200">
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Account Ref</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Acc No</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Risk</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Base CCY</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Market Value</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Status</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 align-top">
        <?php foreach ($rows as $r): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-3 py-3 text-xs font-mono text-gray-700"><?= htmlspecialchars((string)$r['account_ref']) ?></td>
            <td class="px-3 py-3 text-xs text-gray-700"><?= htmlspecialchars((string)$r['acc_no']) ?></td>
            <td class="px-3 py-3 text-xs text-gray-700"><?= htmlspecialchars(strtoupper((string)$r['risk_profile'])) ?></td>
            <td class="px-3 py-3 text-xs text-gray-700"><?= htmlspecialchars((string)$r['base_currency']) ?></td>
            <td class="px-3 py-3 text-xs text-gray-700 font-semibold"><?= htmlspecialchars((string)$r['base_currency']) ?> <?= number_format((float)$r['total_value'], 2) ?></td>
            <td class="px-3 py-3 text-xs"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700"><?= htmlspecialchars(strtoupper((string)$r['status'])) ?></span></td>
            <td class="px-3 py-3 text-xs min-w-[180px]">
              <form method="post" class="flex gap-2">
                <input type="hidden" name="account_id" value="<?= (int)$r['id'] ?>">
                <select name="next_status" class="rounded-lg border border-gray-300 px-2 py-1 text-xs">
                  <?php foreach ($allowedStatuses as $opt): ?>
                    <option value="<?= htmlspecialchars($opt) ?>" <?= $opt === strtolower((string)$r['status']) ? 'selected' : '' ?>><?= htmlspecialchars(strtoupper($opt)) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" name="update_invest_status" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1 rounded-lg">Save</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
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
