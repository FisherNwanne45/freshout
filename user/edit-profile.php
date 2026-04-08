<?php
session_start();
include_once('session.php');
require_once('class.user.php');
if (!isset($_SESSION['acc_no'])) {
    header('Location: login.php');
    exit();
}
if (!isset($_SESSION['mname'])) {
    header('Location: passcode.php');
    exit();
}

$reg_user = new USER();

$stmt = $reg_user->runQuery('SELECT * FROM account WHERE acc_no = :acc_no');
$stmt->execute([':acc_no' => $_SESSION['acc_no']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$flashSuccess = '';
$flashError   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    // Only allow-listed fields — never touch acc_no, email, currency, type, status, balances
    $fname = trim((string)($_POST['fname'] ?? ''));
    $lname = trim((string)($_POST['lname'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $dob   = trim((string)($_POST['dob'] ?? ''));
    $addr  = trim((string)($_POST['addr'] ?? ''));

    // Basic length guards
    if (strlen($fname) > 60 || strlen($lname) > 60 || strlen($phone) > 30 || strlen($addr) > 255) {
        $flashError = 'One or more fields exceed the allowed length.';
    } else {
        try {
            $upd = $reg_user->runQuery(
                'UPDATE account SET fname = :fname, lname = :lname, phone = :phone, dob = :dob, addr = :addr WHERE acc_no = :acc_no'
            );
            $upd->execute([
                ':fname'  => $fname,
                ':lname'  => $lname,
                ':phone'  => $phone,
                ':dob'    => $dob !== '' ? $dob : null,
                ':addr'   => $addr,
                ':acc_no' => $_SESSION['acc_no'],
            ]);
            // Refresh row for display
            $stmt = $reg_user->runQuery('SELECT * FROM account WHERE acc_no = :acc_no');
            $stmt->execute([':acc_no' => $_SESSION['acc_no']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $flashSuccess = 'Profile updated successfully.';
        } catch (Throwable $e) {
            $flashError = 'An error occurred while saving. Please try again.';
        }
    }
}

include_once('counter.php');
require_once __DIR__ . '/partials/shell-data.php';
$shellPageTitle = 'Edit Profile';
require_once __DIR__ . '/partials/shell-open.php';
?>

<?php if ($flashSuccess !== ''): ?>
<div class="mb-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
  <?= htmlspecialchars($flashSuccess) ?>
</div>
<?php endif; ?>
<?php if ($flashError !== ''): ?>
<div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
  <?= htmlspecialchars($flashError) ?>
</div>
<?php endif; ?>

<div class="mb-6 flex items-center gap-3">
  <a href="profile.php" class="inline-flex items-center gap-1.5 text-sm text-brand-navy hover:underline">
    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6"/></svg>
    Back to Profile
  </a>
  <span class="text-gray-300">/</span>
  <h1 class="text-2xl font-bold text-gray-900">Edit Profile</h1>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

  <!-- Read-only identity card -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
    <div class="w-20 h-20 mx-auto rounded-full overflow-hidden bg-gray-100 mb-4 ring-4 ring-gray-50">
      <img src="admin/foto/<?= htmlspecialchars($row['pp'] ?? '') ?>"
           alt="Profile Photo"
           onerror="this.src='img/avatar2.jpg'"
           class="w-full h-full object-cover">
    </div>
    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Account ID</p>
    <p class="text-sm font-mono text-gray-700"><?= htmlspecialchars($row['acc_no'] ?? '') ?></p>
    <p class="mt-3 text-xs text-gray-400 uppercase tracking-wider mb-1">Email</p>
    <p class="text-sm text-gray-700"><?= htmlspecialchars($row['email'] ?? '—') ?></p>
    <p class="mt-3 text-xs text-gray-400 uppercase tracking-wider mb-1">Account Type</p>
    <p class="text-sm text-gray-700"><?= htmlspecialchars(($row['type'] ?? '') . ' (' . ($row['currency'] ?? 'USD') . ')') ?></p>
    <div class="mt-4 rounded-lg bg-gray-50 border border-gray-200 p-3 text-left">
      <p class="text-xs text-gray-500 leading-relaxed">These fields are managed by the bank and cannot be changed here. Contact support if you need to update your email or account type.</p>
    </div>
  </div>

  <!-- Editable fields form -->
  <div class="md:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-6">Personal Information</h3>
    <form method="POST" action="">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

        <div>
          <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1.5">First Name</label>
          <input type="text" name="fname" maxlength="60"
                 value="<?= htmlspecialchars($row['fname'] ?? '') ?>"
                 class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                 placeholder="First name">
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1.5">Last Name</label>
          <input type="text" name="lname" maxlength="60"
                 value="<?= htmlspecialchars($row['lname'] ?? '') ?>"
                 class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                 placeholder="Last name">
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1.5">Phone Number</label>
          <input type="tel" name="phone" maxlength="30"
                 value="<?= htmlspecialchars($row['phone'] ?? '') ?>"
                 class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                 placeholder="+1 555 000 0000">
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1.5">Date of Birth</label>
          <input type="date" name="dob"
                 value="<?= htmlspecialchars($row['dob'] ?? '') ?>"
                 class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="sm:col-span-2">
          <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1.5">Address</label>
          <textarea name="addr" maxlength="255" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                    placeholder="Street, City, Country"><?= htmlspecialchars($row['addr'] ?? '') ?></textarea>
        </div>

      </div>

      <div class="mt-6 flex flex-wrap items-center gap-3">
        <button type="submit" name="save_profile"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-navy px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90 transition-opacity">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          Save Changes
        </button>
        <a href="profile.php"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
          Cancel
        </a>
      </div>
    </form>
  </div>

</div>

<?php require_once __DIR__ . '/partials/shell-close.php'; exit(); ?>
