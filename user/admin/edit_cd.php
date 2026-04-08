<?php
session_start();
include_once ('session.php');
if(!isset($_SESSION['email'])){
    header("Location: login.php");
    exit();
}
require_once 'class.admin.php';

$reg_user = new USER();
$msg = '';
$errorMsg = '';
$row = [];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    $errorMsg = 'Invalid transaction id.';
} else {
    $stmt = $reg_user->runQuery("SELECT * FROM alerts WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    if (!$row) {
        $errorMsg = 'Transaction record not found.';
    }
}

if (isset($_POST['updatecd']) && $id > 0 && !empty($row)) {
    $amount = trim($_POST['amount'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');
    $sender_name = trim($_POST['sender_name'] ?? '');

    $updateStmt = $reg_user->runQuery("UPDATE alerts SET amount = :amount, date = :date, time = :time, remarks = :remarks, sender_name = :sender_name WHERE id = :id");
    $ok = $updateStmt->execute([
        ':amount' => $amount,
        ':date' => $date,
        ':time' => $time,
        ':remarks' => $remarks,
        ':sender_name' => $sender_name,
        ':id' => $id,
    ]);

    if ($ok) {
        $msg = '<div class="rounded-lg bg-green-50 border border-green-200 px-4 py-2 text-green-700 text-sm mb-4">Transaction details updated successfully.</div>';
        $stmt = $reg_user->runQuery("SELECT * FROM alerts WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    } else {
        $errorMsg = 'Failed to update transaction details.';
    }
}

$pageTitle = 'Edit Transaction';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if($msg) echo $msg; ?>
<?php if($errorMsg): ?>
<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">
  <?= htmlspecialchars($errorMsg) ?>
</div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-4xl">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-semibold text-gray-800">Edit Transaction for <?= htmlspecialchars($row['uname'] ?? '') ?></h2>
    <a href="credit_debit_list.php" class="text-sm text-blue-600 hover:underline"><i class="fa-solid fa-arrow-left mr-1"></i>Back</a>
  </div>

  <?php if (!empty($row)): ?>
  <form method="POST" class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Amount Transferred</label>
        <input type="text" name="amount" value="<?= htmlspecialchars($row['amount'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Date</label>
        <input type="text" name="date" value="<?= htmlspecialchars($row['date'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Time</label>
        <input type="text" name="time" value="<?= htmlspecialchars($row['time'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Sender / Receiver</label>
        <input type="text" name="sender_name" value="<?= htmlspecialchars($row['sender_name'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
        <input type="text" name="remarks" value="<?= htmlspecialchars($row['remarks'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
    </div>

    <button type="submit" name="updatecd" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
      <i class="fa-solid fa-floppy-disk"></i> Update Transaction
    </button>
  </form>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
