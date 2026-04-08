<?php
session_start();
require_once 'class.admin.php';
include_once ('session.php');
if(!isset($_SESSION['email'])){
	
header("Location: login.php");

exit(); 
}
$user_home = new USER();

$setupStmt = $user_home->runQuery("SELECT id, fname, lname, email, uname, acc_no, type, reg_date, status
    FROM account
    WHERE LOWER(REPLACE(TRIM(COALESCE(status, '')), ' ', '')) IN ('dormant/inactive', 'dormantinactive')
      AND UPPER(TRIM(COALESCE(cot, ''))) LIKE 'NOT SET%'
      AND UPPER(TRIM(COALESCE(tax, ''))) LIKE 'NOT SET%'
      AND UPPER(TRIM(COALESCE(lppi, ''))) LIKE 'NOT SET%'
      AND UPPER(TRIM(COALESCE(imf, ''))) LIKE 'NOT SET%'
    ORDER BY id DESC
    LIMIT 200");
$setupStmt->execute();
$pendingCodeSetup = $setupStmt->fetchAll(PDO::FETCH_ASSOC);
$pageTitle = 'Pending Accounts';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if(isset($msg)) echo $msg; ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
  <div class="flex flex-wrap items-center gap-2 mb-4">
    <h2 class="font-semibold text-gray-800">Pending Accounts</h2>
    <span class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-700">Code Setup: <?= count($pendingCodeSetup) ?></span>
  </div>

  <h3 class="text-sm font-semibold text-gray-700 mt-2 mb-2">Registered Accounts Pending Code Setup</h3>
  <p class="text-xs text-gray-500 mb-3">These accounts are Dormant/Inactive from frontend registration.</p>
  <div class="overflow-x-auto -mx-6 px-6">
    <table class="min-w-full text-sm border-collapse">
      <thead>
        <tr class="bg-gray-50 border-y border-gray-200">
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">#</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Name</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Email</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Username</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Acc No</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Status</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Date</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($pendingCodeSetup)): ?>
        <tr>
          <td colspan="8" class="px-3 py-4 text-sm text-gray-500">No registered accounts waiting for code setup.</td>
        </tr>
        <?php else: ?>
        <?php $n=0; foreach($pendingCodeSetup as $row): $n++; ?>
        <tr class="hover:bg-gray-50">
          <td class="px-3 py-3 text-sm text-gray-700 text-gray-400"><?= $n ?></td>
          <td class="px-3 py-3 text-sm text-gray-700 font-medium"><?= htmlspecialchars($row['fname'].' '.$row['lname']) ?></td>
          <td class="px-3 py-3 text-sm text-gray-700"><?= htmlspecialchars($row['email']) ?></td>
          <td class="px-3 py-3 text-sm text-gray-700"><?= htmlspecialchars($row['uname']) ?></td>
          <td class="px-3 py-3 text-sm text-gray-700 font-mono text-xs"><?= htmlspecialchars($row['acc_no']) ?></td>
          <td class="px-3 py-3 text-sm text-gray-700">
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700"><?= htmlspecialchars($row['status'] ?: 'Pending Setup') ?></span>
          </td>
          <td class="px-3 py-3 text-sm text-gray-700 text-xs text-gray-500"><?= htmlspecialchars($row['reg_date'] ?? '') ?></td>
          <td class="px-3 py-3 text-sm text-gray-700">
            <a href="edit_account.php?id=<?= (int)$row['id'] ?>" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-pen"></i> Review &amp; Set Codes</a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
