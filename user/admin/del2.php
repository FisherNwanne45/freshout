<?php
session_start();
require_once 'class.admin.php';
include_once ('session.php');

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$reg_user = new USER();
$row = [];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $reg_user->runQuery("SELECT * FROM transfer WHERE id='$id'");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

if (isset($_POST['delete']) && $id > 0) {
    if ($reg_user->del($id)) {
        $deleteuser = $reg_user->runQuery("DELETE FROM transfer WHERE id = '$id'");
        $deleteuser->execute();
        header("Location: transfer_rec.php?success");
        exit();
    }

    header("Location: transfer_rec.php?error");
    exit();
}

$pageTitle = 'Delete Transfer Record';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-2xl">
  <h2 class="text-lg font-semibold text-gray-800 mb-1">Delete Transfer Record</h2>
  <p class="text-sm text-gray-500 mb-5">Confirm this transfer record before deleting it.</p>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 text-sm">
    <div class="sm:col-span-3">
      <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Description</p>
      <p class="font-medium text-gray-800"><?= htmlspecialchars($row['remarks'] ?? '') ?></p>
    </div>
    <div>
      <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Amount</p>
      <p class="font-medium text-gray-800"><?= htmlspecialchars($row['amount'] ?? '') ?></p>
    </div>
    <div class="sm:col-span-2">
      <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Account Name</p>
      <p class="font-medium text-gray-800"><?= htmlspecialchars($row['acc_name'] ?? '') ?></p>
    </div>
  </div>

  <form method="POST" onsubmit="return confirm('Delete this transfer record permanently?');" class="flex items-center gap-3">
    <button type="submit" name="delete" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
      <i class="fa-solid fa-trash"></i> Delete Transfer
    </button>
    <a href="transfer_rec.php" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">Cancel</a>
  </form>
</div>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
