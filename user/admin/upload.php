<?php
session_start();
require_once 'class.admin.php';
include_once ('session.php');

if(!isset($_SESSION['email'])){
    header("Location: login.php");
    exit();
}

$msg = '';
if(isset($_FILES['image']) && !empty($_FILES['image']['name'])){
    $errors = array();
    $file_name = $_FILES['image']['name'];
    $file_size = $_FILES['image']['size'];
    $file_tmp  = $_FILES['image']['tmp_name'];
    $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $expensions = array("jpeg","jpg");
    if(!in_array($file_ext, $expensions, true)){
        $errors[] = 'Please select a JPG image.';
    }
    if($file_size > 2097152){
        $errors[] = 'File size must be 2 MB or less.';
    }

    if(empty($errors)){
        move_uploaded_file($file_tmp, "foto/".$file_name);
        $msg = '<div class="rounded-lg bg-green-50 border border-green-200 px-4 py-2 text-green-700 text-sm mb-4">Image successfully uploaded.</div>';
    } else {
        $msg = '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">' . htmlspecialchars(implode(' ', $errors)) . '</div>';
    }
}

$pageTitle = 'Upload Image';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if($msg) echo $msg; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-2xl">
  <h2 class="text-lg font-semibold text-gray-800 mb-1">Upload Image</h2>
  <p class="text-sm text-gray-500 mb-5">Use a .jpg image and save it with the target username as filename.</p>

  <form method="POST" enctype="multipart/form-data" class="space-y-4">
    <div>
      <label class="block text-xs font-medium text-gray-700 mb-1">Image File</label>
      <input type="file" accept="image/jpeg" name="image" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
    </div>
    <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
      <i class="fa-solid fa-cloud-arrow-up"></i> Upload Image
    </button>
  </form>
</div>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
