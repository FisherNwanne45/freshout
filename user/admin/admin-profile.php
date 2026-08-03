<?php
session_start();
require_once 'class.admin.php';
include_once 'session.php';

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$reg_user = new USER();
$message = '';
$messageType = 'success';

$currentEmail = (string)$_SESSION['email'];
$adminStmt = $reg_user->runQuery('SELECT id, uname, email FROM admin WHERE email = :email LIMIT 1');
$adminStmt->execute([':email' => $currentEmail]);
$admin = $adminStmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    header('Location: logout.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_admin_profile'])) {
    $adminId = (int)($admin['id'] ?? 0);
    $newUsername = trim((string)($_POST['uname'] ?? ''));
    $newEmail = trim((string)($_POST['email'] ?? ''));
    $newPassword = (string)($_POST['new_password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    $errors = [];

    if ($newUsername === '') {
        $errors[] = 'Username is required.';
    }
    if ($newEmail === '' || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }
    if ($newPassword !== '' && $newPassword !== $confirmPassword) {
        $errors[] = 'New password and confirm password do not match.';
    }

    if (empty($errors)) {
        $dupStmt = $reg_user->runQuery('SELECT id FROM admin WHERE (email = :email OR uname = :uname) AND id <> :id LIMIT 1');
        $dupStmt->bindValue(':email', $newEmail);
        $dupStmt->bindValue(':uname', $newUsername);
        $dupStmt->bindValue(':id', $adminId, PDO::PARAM_INT);
        $dupStmt->execute();
        if ($dupStmt->fetch(PDO::FETCH_ASSOC)) {
            $errors[] = 'Username or email is already used by another admin.';
        }
    }

    if (empty($errors)) {
        if ($newPassword !== '') {
            $passwordHash = md5($newPassword);
            $updateStmt = $reg_user->runQuery('UPDATE admin SET uname = :uname, email = :email, upass = :upass WHERE id = :id');
            $updateStmt->bindValue(':upass', $passwordHash);
        } else {
            $updateStmt = $reg_user->runQuery('UPDATE admin SET uname = :uname, email = :email WHERE id = :id');
        }

        $updateStmt->bindValue(':uname', $newUsername);
        $updateStmt->bindValue(':email', $newEmail);
        $updateStmt->bindValue(':id', $adminId, PDO::PARAM_INT);

        if ($updateStmt->execute()) {
            $_SESSION['email'] = $newEmail;
            $currentEmail = $newEmail;
            $messageType = 'success';
            $message = 'Admin profile updated successfully.';
        } else {
            $messageType = 'error';
            $message = 'Failed to update admin profile.';
        }
    } else {
        $messageType = 'error';
        $message = implode(' ', $errors);
    }

    $adminStmt = $reg_user->runQuery('SELECT id, uname, email FROM admin WHERE id = :id LIMIT 1');
    $adminStmt->execute([':id' => $admin['id']]);
    $admin = $adminStmt->fetch(PDO::FETCH_ASSOC) ?: $admin;
}

$pageTitle = 'Admin Profile';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<div class="max-w-3xl space-y-5">
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-1">Admin Profile</h2>
    <p class="text-sm text-gray-500 mb-5">Update admin username, login email, and password.</p>

    <?php if ($message !== ''): ?>
      <div class="mb-4 rounded-lg px-4 py-3 text-sm <?= $messageType === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700' ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Username</label>
          <input type="text" name="uname" value="<?= htmlspecialchars((string)($admin['uname'] ?? '')) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
          <input type="email" name="email" value="<?= htmlspecialchars((string)($admin['email'] ?? '')) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">New Password</label>
          <input type="password" name="new_password" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Leave blank to keep current">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Confirm New Password</label>
          <input type="password" name="confirm_password" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Re-enter new password">
        </div>
      </div>

      <div class="pt-2">
        <button type="submit" name="save_admin_profile" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer">
          Save Admin Profile
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
