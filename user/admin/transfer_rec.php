<?php
session_start();
require_once 'class.admin.php';

include_once ('session.php');
if(!isset($_SESSION['email'])){
	
header("Location: login.php");

exit(); 
}
$user_home = new USER();


$stmt = $user_home->runQuery("SELECT * FROM transfer ORDER BY id DESC LIMIT 200");
$stmt->execute();
if(isset($_GET['id'])){
	
$id=$_GET['id'];
}
$pageTitle = 'Transfer Records';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if(isset($msg)) echo $msg; ?>

<!-- Transfer Records -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
  <h2 class="font-semibold text-gray-800 mb-4">Transfer Records</h2>
  <div class="overflow-x-auto -mx-6 px-6">
    <table class="min-w-full text-sm border-collapse">
      <thead>
        <tr class="bg-gray-50 border-y border-gray-200">
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">#</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">From Acc</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">To Acc</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Amount</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Description</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Date</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Status</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php $n=0; while($r = $stmt->fetch(PDO::FETCH_ASSOC)): $n++; ?>
        <tr class="hover:bg-gray-50">
          <td class="px-3 py-3 text-sm text-gray-700 text-gray-400"><?= $n ?></td>
          <td class="px-3 py-3 text-sm text-gray-700 font-mono text-xs"><?= htmlspecialchars($r['acc_no'] ?? '') ?></td>
          <td class="px-3 py-3 text-sm text-gray-700 font-mono text-xs"><?= htmlspecialchars($r['reci_acc'] ?? $r['reci_name'] ?? '') ?></td>
          <td class="px-3 py-3 text-sm text-gray-700 font-medium text-right"><?= number_format((float)($r['amount'] ?? 0),2) ?></td>
          <td class="px-3 py-3 text-sm text-gray-700"><?= htmlspecialchars($r['description'] ?? $r['remarks'] ?? '') ?></td>
          <td class="px-3 py-3 text-sm text-gray-700 text-xs text-gray-500"><?= htmlspecialchars($r['date'] ?? '') ?></td>
          <td class="px-3 py-3 text-sm text-gray-700">
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
              <?= htmlspecialchars($r['status'] ?? 'Pending') ?>
            </span>
          </td>
          <td class="px-3 py-3 text-sm text-gray-700">
            <a href="edit_tf.php?id=<?= $r['id'] ?>" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer !py-1 !px-2 !text-xs"><i class="fa-solid fa-pen"></i></a>
            <a href="del.php?id=<?= $r['id'] ?>" onclick="return confirm('Delete?')" class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors cursor-pointer !py-1 !px-2"><i class="fa-solid fa-trash"></i></a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
