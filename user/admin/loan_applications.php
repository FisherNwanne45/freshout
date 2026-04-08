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
    $reg_user->runQuery("CREATE TABLE IF NOT EXISTS loan_applications (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        application_ref VARCHAR(32) NOT NULL,
        acc_no VARCHAR(50) NOT NULL,
        email VARCHAR(190) NOT NULL,
        full_name VARCHAR(190) NOT NULL,
        purpose VARCHAR(255) NOT NULL,
        amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        currency_code VARCHAR(10) NOT NULL DEFAULT 'USD',
        details TEXT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'submitted',
        admin_note TEXT NULL,
        reviewed_by VARCHAR(190) NULL,
        reviewed_at DATETIME NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        UNIQUE KEY uq_loan_ref (application_ref),
        KEY idx_loan_acc_status (acc_no, status),
        KEY idx_loan_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();
} catch (Throwable $e) {
}

$allowedStatuses = [
    'submitted',
    'under_review',
    'approved',
    'rejected',
    'disbursed',
    'active',
    'closed',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_loan_status'])) {
    $loanId = (int)($_POST['loan_id'] ?? 0);
    $nextStatus = strtolower(trim((string)($_POST['next_status'] ?? 'submitted')));
    $adminNote = trim((string)($_POST['admin_note'] ?? ''));

    if ($loanId <= 0 || !in_array($nextStatus, $allowedStatuses, true)) {
        $flash = '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">Invalid loan update request.</div>';
    } else {
        try {
            $now = date('Y-m-d H:i:s');
            $up = $reg_user->runQuery('UPDATE loan_applications
                SET status = :status,
                    admin_note = :admin_note,
                    reviewed_by = :reviewed_by,
                    reviewed_at = :reviewed_at,
                    updated_at = :updated_at
                WHERE id = :id');
            $up->execute([
                ':status' => $nextStatus,
                ':admin_note' => $adminNote,
                ':reviewed_by' => (string)($_SESSION['email'] ?? ''),
                ':reviewed_at' => $now,
                ':updated_at' => $now,
                ':id' => $loanId,
            ]);
            $flash = '<div class="rounded-lg bg-green-50 border border-green-200 px-4 py-2 text-green-700 text-sm mb-4">Loan application updated successfully.</div>';
        } catch (Throwable $e) {
            $flash = '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">Unable to update loan application right now.</div>';
        }
    }
}

$listStmt = $reg_user->runQuery('SELECT id, application_ref, acc_no, email, full_name, purpose, amount, currency_code, status, admin_note, created_at, reviewed_by, reviewed_at
    FROM loan_applications
    ORDER BY id DESC
    LIMIT 300');
$listStmt->execute();
$loanRows = $listStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Loan Applications';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if ($flash !== '') echo $flash; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-semibold text-gray-800">Loan Application Queue</h2>
    <span class="text-xs text-gray-500">Lifecycle: submitted → under_review → approved/rejected → disbursed → active/closed</span>
  </div>

  <div class="mb-4">
    <input type="text" id="loan-search" onkeyup="filterTable('loan-search','loan-table')"
      placeholder="Search by reference, account, email, name, purpose"
      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 max-w-lg">
  </div>

  <div class="overflow-x-auto -mx-6 px-6">
    <table id="loan-table" class="min-w-full text-sm border-collapse">
      <thead>
        <tr class="bg-gray-50 border-y border-gray-200">
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Reference</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Account</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Customer</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Purpose</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Amount</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Status</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Submitted</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 align-top">
        <?php foreach ($loanRows as $loan): ?>
          <?php
            $status = strtolower((string)($loan['status'] ?? 'submitted'));
            $statusClass = 'bg-slate-100 text-slate-700';
            if ($status === 'approved' || $status === 'disbursed' || $status === 'active') {
              $statusClass = 'bg-green-100 text-green-700';
            } elseif ($status === 'under_review') {
              $statusClass = 'bg-amber-100 text-amber-700';
            } elseif ($status === 'rejected' || $status === 'closed') {
              $statusClass = 'bg-red-100 text-red-700';
            }
          ?>
          <tr class="hover:bg-gray-50">
            <td class="px-3 py-3 text-xs font-mono text-gray-700"><?= htmlspecialchars((string)$loan['application_ref']) ?></td>
            <td class="px-3 py-3 text-xs text-gray-700"><?= htmlspecialchars((string)$loan['acc_no']) ?></td>
            <td class="px-3 py-3 text-xs text-gray-700">
              <div class="font-medium"><?= htmlspecialchars((string)$loan['full_name']) ?></div>
              <div class="text-gray-500"><?= htmlspecialchars((string)$loan['email']) ?></div>
            </td>
            <td class="px-3 py-3 text-xs text-gray-700"><?= htmlspecialchars((string)$loan['purpose']) ?></td>
            <td class="px-3 py-3 text-xs text-gray-700 font-semibold"><?= htmlspecialchars((string)$loan['currency_code']) ?> <?= number_format((float)$loan['amount'], 2) ?></td>
            <td class="px-3 py-3 text-xs text-gray-700">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold <?= $statusClass ?>"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $status))) ?></span>
              <?php if (!empty($loan['reviewed_by'])): ?>
                <div class="mt-1 text-[11px] text-gray-500">By <?= htmlspecialchars((string)$loan['reviewed_by']) ?></div>
              <?php endif; ?>
            </td>
            <td class="px-3 py-3 text-xs text-gray-500"><?= htmlspecialchars((string)$loan['created_at']) ?></td>
            <td class="px-3 py-3 text-xs text-gray-700 min-w-[250px]">
              <form method="post" class="space-y-2">
                <input type="hidden" name="loan_id" value="<?= (int)$loan['id'] ?>">
                <select name="next_status" class="w-full rounded-lg border border-gray-300 px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <?php foreach ($allowedStatuses as $opt): ?>
                    <option value="<?= htmlspecialchars($opt) ?>" <?= $opt === $status ? 'selected' : '' ?>><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $opt))) ?></option>
                  <?php endforeach; ?>
                </select>
                <textarea name="admin_note" rows="2" class="w-full rounded-lg border border-gray-300 px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Admin note"><?= htmlspecialchars((string)($loan['admin_note'] ?? '')) ?></textarea>
                <button type="submit" name="update_loan_status" class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">Update</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if (empty($loanRows)): ?>
      <p class="py-5 text-sm text-gray-500">No loan applications found.</p>
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
