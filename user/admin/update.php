<?php
session_start();
include_once ('session.php');
if(!isset($_SESSION['email'])){
	
header("Location: login.php");

exit(); 
}
require_once 'class.admin.php';
$user_home = new USER();



$stmt = $user_home->runQuery("SELECT * FROM account ORDER BY id DESC LIMIT 200");
$stmt->execute();

if(isset($_GET['id'])){
	
$id=$_GET['id'];
}
$pageTitle = 'Update Accounts';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if(isset($msg)) echo $msg; ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-semibold text-gray-800">Update Accounts</h2>
  </div>
  <div class="mb-4">
    <input type="text" id="upd-search" onkeyup="filterTable('upd-search','upd-table')"
      placeholder="Search accounts…" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 max-w-sm">
  </div>
  <div class="overflow-x-auto -mx-6 px-6">
    <table id="upd-table" class="min-w-full text-sm border-collapse">
      <thead>
        <tr class="bg-gray-50 border-y border-gray-200">
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">#</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Name</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Acc No</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Email</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Type</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Balance</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Status</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php $n=0; while($row = $stmt->fetch(PDO::FETCH_ASSOC)): $n++; ?>
        <tr class="hover:bg-gray-50">
          <td class="px-3 py-3 text-sm text-gray-700 text-gray-400"><?= $n ?></td>
          <td class="px-3 py-3 text-sm text-gray-700 font-medium"><?= htmlspecialchars($row['fname'].' '.$row['lname']) ?></td>
          <td class="px-3 py-3 text-sm text-gray-700 font-mono text-xs"><?= htmlspecialchars($row['acc_no']) ?></td>
          <td class="px-3 py-3 text-sm text-gray-700"><?= htmlspecialchars($row['email']) ?></td>
          <td class="px-3 py-3 text-sm text-gray-700"><?= htmlspecialchars($row['type']) ?></td>
          <td class="px-3 py-3 text-sm text-gray-700 text-right font-medium"><?= number_format((float)$row['t_bal'],2) ?></td>
          <td class="px-3 py-3 text-sm text-gray-700">
            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
              <?= $row['status']==='Active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
              <?= htmlspecialchars($row['status']) ?>
            </span>
          </td>
          <td class="px-3 py-3 text-sm text-gray-700">
            <a href="edit_account.php?id=<?= $row['id'] ?>" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer !py-1 !px-3 !text-xs"><i class="fa-solid fa-pen"></i> Edit</a>
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
