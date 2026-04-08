<?php
session_start();
require_once 'class.admin.php';
include_once('session.php');
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$reg_user = new USER();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$row = [];
if ($id > 0) {
    $stmt = $reg_user->runQuery("SELECT * FROM temp_account WHERE id='$id'");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

if (isset($_POST['decline']) && !empty($row)) {
    $email = $row['email'] ?? '';
    $fname = $row['fname'] ?? '';
    if ($reg_user->del($id)) {
        $deleteuser = $reg_user->runQuery("DELETE FROM temp_account WHERE id = '$id'");
        $deleteuser->execute();
        $subject = "We Are Sorry $fname! - Your Account Application Was Declined!";
        $decline_data = [
            'fname' => $fname,
            'lname' => $row['lname'] ?? '',
            'reason' => 'Application does not meet current requirements',
            'reapply_info' => 'You may reapply after 3 months'
        ];
        $reg_user->send_mail($email, '', $subject, 'application_declined', $decline_data);
        header("Location: pending_accounts.php?declined");
        exit();
    }
    header("Location: pending_accounts.php?error");
    exit();
}

$pageTitle = 'Decline Account Application';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-2xl">
    <h2 class="text-lg font-semibold text-gray-800 mb-1">Decline Account Application</h2>
    <p class="text-sm text-gray-500 mb-5">You are about to decline this pending account application.</p>
    <?php if (!empty($row)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 text-sm">
            <div class="sm:col-span-2">
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Full Name</p>
                <p class="font-medium text-gray-800"><?= htmlspecialchars(($row['fname'] ?? '') . ' ' . ($row['pin'] ?? '') . ' ' . ($row['lname'] ?? '')) ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Email</p>
                <p class="font-medium text-gray-800"><?= htmlspecialchars($row['email'] ?? '') ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Phone</p>
                <p class="font-medium text-gray-800"><?= htmlspecialchars($row['phone'] ?? '') ?></p>
            </div>
        </div>
        <form method="POST" onsubmit="return confirm('Decline this account application?');" class="flex items-center gap-3">
            <button type="submit" name="decline" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors"><i class="fa-solid fa-ban"></i> Decline Account</button>
            <a href="pending_accounts.php" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">Cancel</a>
        </form>
    <?php else: ?>
        <p class="text-sm text-gray-500">No pending account found.</p>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>