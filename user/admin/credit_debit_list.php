<?php
session_start();
require_once 'class.admin.php';

include_once ('session.php');
if(!isset($_SESSION['email'])){
	
header("Location: login.php");

exit(); 
}
$user_home = new USER();


$stmt = $user_home->runQuery("SELECT * FROM account");
$stmt->execute();

$credit = $user_home->runQuery(
  "SELECT a.*, ac.acc_no AS account_no, ac.fname AS account_fname, ac.lname AS account_lname
   FROM alerts a
  LEFT JOIN account ac ON (ac.acc_no = a.uname OR ac.uname = a.uname)
   ORDER BY a.id DESC
   LIMIT 200"
);
$credit->execute();
if(isset($_GET['id'])){
	
$id=$_GET['id'];
}
$pageTitle = 'Credit/Debit History';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if(isset($msg)) echo $msg; ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-semibold text-gray-800">Credit / Debit History</h2>
  </div>
  <div class="mb-4">
    <input type="text" id="cd-search" onkeyup="filterTable('cd-search','cd-table')"
      placeholder="Search history…" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 max-w-sm">
  </div>
  <div class="overflow-x-auto -mx-6 px-6">
    <table id="cd-table" class="min-w-full text-sm border-collapse">
      <thead>
        <tr class="bg-gray-50 border-y border-gray-200">
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">#</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Account No</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Name</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Type</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Amount</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">From / To</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Description</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Date</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php $n=0; while($r = $credit->fetch(PDO::FETCH_ASSOC)): $n++; ?>
        <tr class="hover:bg-gray-50">
          <td class="px-3 py-3 text-sm text-gray-700 text-gray-400"><?= $n ?></td>
          <td class="px-3 py-3 text-sm text-gray-700 font-mono text-xs"><?= htmlspecialchars($r['account_no'] ?? ($r['uname'] ?? '')) ?></td>
          <td class="px-3 py-3 text-sm text-gray-700"><?= htmlspecialchars(trim((string)(($r['account_fname'] ?? '') . ' ' . ($r['account_lname'] ?? ''))) ?: ($r['sender_name'] ?? 'Unknown')) ?></td>
          <td class="px-3 py-3 text-sm text-gray-700">
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
              <?= ($r['type'] ?? '') === 'Credit' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' ?>">
              <?= htmlspecialchars($r['type'] ?? '') ?>
            </span>
          </td>
          <td class="px-3 py-3 text-sm text-gray-700 text-right font-medium"><?= number_format((float)($r['amount'] ?? 0), 2) ?></td>
          <td class="px-3 py-3 text-sm text-gray-700"><?= htmlspecialchars($r['sender_name'] ?? '') ?></td>
          <td class="px-3 py-3 text-sm text-gray-700 max-w-xs truncate text-gray-500"><?= htmlspecialchars($r['remarks'] ?? $r['description'] ?? '') ?></td>
          <td class="px-3 py-3 text-sm text-gray-700 text-xs text-gray-500"><?= htmlspecialchars(($r['date'] ?? '').' '.($r['time'] ?? '')) ?></td>
          <td class="px-3 py-3 text-sm text-gray-700">
            <a href="edit_cd.php?id=<?= $r['id'] ?>" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer !py-1 !px-2 !text-xs"><i class="fa-solid fa-pen"></i></a>
            <a href="delete.php?id=<?= $r['id'] ?>" onclick="return confirm('Delete?')" class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors cursor-pointer !py-1 !px-2"><i class="fa-solid fa-trash"></i></a>
          </td>
        </tr>
        <?php endwhile; ?>
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
