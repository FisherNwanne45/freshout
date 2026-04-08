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
    $reg_user->runQuery("CREATE TABLE IF NOT EXISTS term_deposits (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      deposit_ref VARCHAR(32) NOT NULL,
      acc_no VARCHAR(50) NOT NULL,
      principal DECIMAL(18,2) NOT NULL,
      annual_rate DECIMAL(7,4) NOT NULL,
      tenor_months INT NOT NULL,
      start_date DATE NOT NULL,
      maturity_date DATE NOT NULL,
      maturity_amount DECIMAL(18,2) NOT NULL,
      status VARCHAR(30) NOT NULL DEFAULT 'active',
      payout_mode VARCHAR(20) NOT NULL DEFAULT 'payout',
      created_at DATETIME NOT NULL,
      updated_at DATETIME NOT NULL,
      UNIQUE KEY uq_term_deposit_ref (deposit_ref),
      KEY idx_term_deposit_acc_status (acc_no, status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();
} catch (Throwable $e) {
}

$allowedStatuses = ['active', 'matured', 'closed', 'cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_td_status'])) {
    $id = (int)($_POST['td_id'] ?? 0);
    $nextStatus = strtolower(trim((string)($_POST['next_status'] ?? 'active')));

    if ($id <= 0 || !in_array($nextStatus, $allowedStatuses, true)) {
        $flash = '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">Invalid status update request.</div>';
    } else {
        try {
            $up = $reg_user->runQuery('UPDATE term_deposits SET status = :status, updated_at = :updated_at WHERE id = :id');
            $up->execute([
                ':status' => $nextStatus,
                ':updated_at' => date('Y-m-d H:i:s'),
                ':id' => $id,
            ]);
            $flash = '<div class="rounded-lg bg-green-50 border border-green-200 px-4 py-2 text-green-700 text-sm mb-4">Deposit status updated.</div>';
        } catch (Throwable $e) {
            $flash = '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">Unable to update deposit status.</div>';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['settle_td'])) {
    $id = (int)($_POST['td_id'] ?? 0);
    if ($id <= 0) {
        $flash = '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">Invalid settlement request.</div>';
    } else {
        try {
            $reg_user->runQuery('START TRANSACTION')->execute();
            $fetch = $reg_user->runQuery('SELECT * FROM term_deposits WHERE id = :id FOR UPDATE');
            $fetch->execute([':id' => $id]);
            $td = $fetch->fetch(PDO::FETCH_ASSOC);
            if (!$td) {
                throw new RuntimeException('Deposit not found.');
            }
            $st = strtolower((string)$td['status']);
            if (!in_array($st, ['active', 'matured'], true)) {
                throw new RuntimeException('Only active or matured deposits can be settled.');
            }

            $accNo = (string)$td['acc_no'];
            $amt = (float)$td['maturity_amount'];

            $credit = $reg_user->runQuery('UPDATE account SET a_bal = a_bal + :amt, t_bal = t_bal + :amt WHERE acc_no = :acc_no');
            $credit->execute([':amt' => $amt, ':acc_no' => $accNo]);

            $updTd = $reg_user->runQuery('UPDATE term_deposits SET status = :status, updated_at = :updated_at WHERE id = :id');
            $updTd->execute([
                ':status' => 'closed',
                ':updated_at' => date('Y-m-d H:i:s'),
                ':id' => $id,
            ]);

            try {
                $alerts = $reg_user->runQuery('INSERT INTO alerts (uname, type, amount, sender_name, remarks, date, time)
                    VALUES (:uname, :type, :amount, :sender_name, :remarks, :date, :time)');
                $alerts->execute([
                    ':uname' => $accNo,
                    ':type' => 'Term Deposit Settlement',
                    ':amount' => $amt,
                    ':sender_name' => 'Treasury Desk',
                    ':remarks' => 'Settlement for ' . (string)$td['deposit_ref'],
                    ':date' => date('Y-m-d'),
                    ':time' => date('H:i:s'),
                ]);
            } catch (Throwable $e) {
            }

            $reg_user->runQuery('COMMIT')->execute();
            $flash = '<div class="rounded-lg bg-green-50 border border-green-200 px-4 py-2 text-green-700 text-sm mb-4">Deposit settled and credited to customer account.</div>';
        } catch (Throwable $e) {
            try {
                $reg_user->runQuery('ROLLBACK')->execute();
            } catch (Throwable $rollbackError) {
            }
            $flash = '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

$listStmt = $reg_user->runQuery('SELECT id, deposit_ref, acc_no, principal, annual_rate, tenor_months, maturity_date, maturity_amount, status, payout_mode, created_at
    FROM term_deposits
    ORDER BY id DESC
    LIMIT 300');
$listStmt->execute();
$rows = $listStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Term Deposits';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if ($flash !== '') echo $flash; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-semibold text-gray-800">Term Deposits</h2>
    <span class="text-xs text-gray-500">Admin treasury operations</span>
  </div>

  <div class="mb-4">
    <input type="text" id="td-search" onkeyup="filterTable('td-search','td-table')" placeholder="Search by reference or account"
      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 max-w-lg">
  </div>

  <div class="overflow-x-auto -mx-6 px-6">
    <table id="td-table" class="min-w-full text-sm border-collapse">
      <thead>
        <tr class="bg-gray-50 border-y border-gray-200">
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Reference</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Account</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Principal</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Tenor</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Maturity</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Status</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Update</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Settle</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 align-top">
        <?php foreach ($rows as $r): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-3 py-3 text-xs font-mono text-gray-700"><?= htmlspecialchars((string)$r['deposit_ref']) ?></td>
            <td class="px-3 py-3 text-xs text-gray-700"><?= htmlspecialchars((string)$r['acc_no']) ?></td>
            <td class="px-3 py-3 text-xs text-gray-700"><?= number_format((float)$r['principal'], 2) ?></td>
            <td class="px-3 py-3 text-xs text-gray-700"><?= (int)$r['tenor_months'] ?>m @ <?= number_format((float)$r['annual_rate'], 2) ?>%</td>
            <td class="px-3 py-3 text-xs text-gray-700"><?= htmlspecialchars((string)$r['maturity_date']) ?><br><span class="text-gray-500 font-semibold"><?= number_format((float)$r['maturity_amount'], 2) ?></span></td>
            <td class="px-3 py-3 text-xs"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700"><?= htmlspecialchars(strtoupper((string)$r['status'])) ?></span></td>
            <td class="px-3 py-3 text-xs min-w-[170px]">
              <form method="post" class="flex gap-2">
                <input type="hidden" name="td_id" value="<?= (int)$r['id'] ?>">
                <select name="next_status" class="rounded-lg border border-gray-300 px-2 py-1 text-xs">
                  <?php foreach ($allowedStatuses as $opt): ?>
                    <option value="<?= htmlspecialchars($opt) ?>" <?= $opt === strtolower((string)$r['status']) ? 'selected' : '' ?>><?= htmlspecialchars(strtoupper($opt)) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" name="update_td_status" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1 rounded-lg">Save</button>
              </form>
            </td>
            <td class="px-3 py-3 text-xs">
              <form method="post">
                <input type="hidden" name="td_id" value="<?= (int)$r['id'] ?>">
                <button type="submit" name="settle_td" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-1 rounded-lg">Settle</button>
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
