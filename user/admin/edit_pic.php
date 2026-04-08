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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$row = [];
if ($id > 0) {
    $stmt = $reg_user->runQuery("SELECT * FROM account WHERE id='$id'");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

if (isset($_POST['updatepic']) && $id > 0) {
    $image = $_FILES['image']['name'] ?? '';
    $pp = $_FILES['pp']['name'] ?? '';

    if (!empty($image) && !empty($_FILES['image']['tmp_name'])) {
        move_uploaded_file($_FILES['image']['tmp_name'], "foto/" . $image);
    }
    if (!empty($pp) && !empty($_FILES['pp']['tmp_name'])) {
        move_uploaded_file($_FILES['pp']['tmp_name'], "foto/" . $pp);
    }

    if($reg_user->updatepic($image, $pp)) {
        $editaccount = $reg_user->runQuery("UPDATE account SET image = '$image', pp = '$pp' WHERE id ='$id'");
        $editaccount->execute();
        $msg = '<div class="rounded-lg bg-green-50 border border-green-200 px-4 py-2 text-green-700 text-sm mb-4">Images were successfully updated.</div>';

        $stmt = $reg_user->runQuery("SELECT * FROM account WHERE id='$id'");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}

$pageTitle = 'Edit Account Images';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if($msg) echo $msg; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-4xl">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-semibold text-gray-800">Edit Images for <?= htmlspecialchars(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? '')) ?></h2>
    <a href="update.php" class="text-sm text-blue-600 hover:underline"><i class="fa-solid fa-arrow-left mr-1"></i>Back</a>
  </div>
  <p class="text-sm text-gray-500 mb-5">Account #: <?= htmlspecialchars($row['acc_no'] ?? '') ?></p>

  <form method="POST" enctype="multipart/form-data" class="space-y-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-2">Profile Picture</label>
        <?php if(!empty($row['pp'])): ?>
          <img src="./foto/<?= htmlspecialchars($row['pp']) ?>" alt="Profile" class="w-full h-44 object-cover rounded-lg border border-gray-200 mb-2">
        <?php else: ?>
          <div class="w-full h-44 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-400 text-xs mb-2">No profile picture</div>
        <?php endif; ?>
        <input type="file" name="pp" accept="image/*" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-700 mb-2">ID Card</label>
        <?php if(!empty($row['image'])): ?>
          <img src="./foto/<?= htmlspecialchars($row['image']) ?>" alt="ID Card" class="w-full h-44 object-cover rounded-lg border border-gray-200 mb-2">
        <?php else: ?>
          <div class="w-full h-44 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-400 text-xs mb-2">No ID card image</div>
        <?php endif; ?>
        <input type="file" name="image" accept="image/*" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
    </div>

    <button type="submit" name="updatepic" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
      <i class="fa-solid fa-floppy-disk"></i> Update Images
    </button>
  </form>
</div>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
