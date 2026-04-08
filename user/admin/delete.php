<?php
session_start();
require_once 'class.admin.php';
include_once('session.php');
if (!isset($_SESSION['email'])) {

  header("Location: login.php");

  exit();
}

$reg_user = new USER();

if (isset($_GET['id'])) {

  $id = $_GET['id'];
  $stmt = $reg_user->runQuery("SELECT * FROM account WHERE id='$id'");
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
}
if (isset($_POST['delete'])) {

  if ($reg_user->del($id)) {
    $id = $_GET['id'];
    $deleteuser = $reg_user->runQuery("DELETE FROM account WHERE id = '$id'");
    $deleteuser->execute();


    header("Location: update.php?success");
  } else {

    header("Location: update.php?error");
  }
}


$pageTitle = 'Delete Account';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-2xl">
  <h2 class="text-lg font-semibold text-gray-800 mb-1">Delete Account</h2>
  <p class="text-sm text-gray-500 mb-5">Review account details before deletion.</p>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 text-sm">
    <div class="sm:col-span-3">
      <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Full Name</p>
      <p class="font-medium text-gray-800"><?= htmlspecialchars(($row['fname'] ?? '') . ' ' . ($row['pin'] ?? '') . ' ' . ($row['lname'] ?? '')) ?></p>
    </div>
    <div class="sm:col-span-2">
      <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Email</p>
      <p class="font-medium text-gray-800"><?= htmlspecialchars($row['email'] ?? '') ?></p>
    </div>
    <div>
      <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Account Number</p>
      <p class="font-medium text-gray-800"><?= htmlspecialchars($row['acc_no'] ?? '') ?></p>
    </div>
  </div>

  <form method="POST" onsubmit="return confirm('Delete this account permanently?');" class="flex items-center gap-3">
    <button type="submit" name="delete" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
      <i class="fa-solid fa-trash"></i> Delete Account
    </button>
    <a href="update.php" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">Cancel</a>
  </form>
</div>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>