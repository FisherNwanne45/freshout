<?php
session_start();

include_once 'session.php';
require_once 'class.user.php';

if (!isset($_SESSION['acc_no'])) {
    header('Location: login.php');
    exit();
}
if (!isset($_SESSION['mname'])) {
    header('Location: passcode.php');
    exit();
}

$reg_user = new USER();
$flashMessage = '';
$flashType = 'success';

$siteStmt = $reg_user->runQuery("SELECT * FROM site WHERE id = '20' LIMIT 1");
$siteStmt->execute();
$rowp = $siteStmt->fetch(PDO::FETCH_ASSOC) ?: [];

$stmt = $reg_user->runQuery('SELECT * FROM account WHERE acc_no = :acc_no LIMIT 1');
$stmt->execute([':acc_no' => (string)$_SESSION['acc_no']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    header('Location: logout.php');
    exit();
}

$email = (string)($row['email'] ?? '');
$fname = (string)($row['fname'] ?? '');
$lname = (string)($row['lname'] ?? '');

try {
    $reg_user->runQuery("CREATE TABLE IF NOT EXISTS loan_applications (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        application_ref VARCHAR(32) NOT NULL,
        acc_no VARCHAR(50) NOT NULL,
        email VARCHAR(190) NOT NULL,
        full_name VARCHAR(190) NOT NULL,
        purpose VARCHAR(255) NOT NULL,
        amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        currency_code VARCHAR(10) NOT NULL DEFAULT 'USD',
        details TEXT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'submitted',
        admin_note TEXT NULL,
        reviewed_by VARCHAR(190) NULL,
        reviewed_at DATETIME NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        UNIQUE KEY uq_loan_ref (application_ref),
        KEY idx_loan_acc_status (acc_no, status),
        KEY idx_loan_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();
} catch (Throwable $e) {
}

if (isset($_POST['submit_loan_application'])) {
    $sender_name = trim((string)($_POST['sender_name'] ?? ''));
    $purpose = trim((string)($_POST['subject'] ?? ''));
    $details = trim((string)($_POST['msg'] ?? ''));
    $amount = (float)($_POST['amount'] ?? 0);
    $accNo = (string)($_SESSION['acc_no'] ?? '');
    $currencyCode = strtoupper(trim((string)($row['currency'] ?? 'USD')));

    if ($sender_name === '' || $purpose === '' || $details === '' || $amount <= 0) {
        $flashType = 'error';
        $flashMessage = 'Please complete all required fields and enter a valid amount.';
    } else {
        try {
            $appRef = 'LN' . date('YmdHis') . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
            $now = date('Y-m-d H:i:s');

            $insertLoan = $reg_user->runQuery('INSERT INTO loan_applications
                (application_ref, acc_no, email, full_name, purpose, amount, currency_code, details, status, created_at, updated_at)
                VALUES
                (:application_ref, :acc_no, :email, :full_name, :purpose, :amount, :currency_code, :details, :status, :created_at, :updated_at)');
            $insertLoan->execute([
                ':application_ref' => $appRef,
                ':acc_no' => $accNo,
                ':email' => $email,
                ':full_name' => $sender_name,
                ':purpose' => $purpose,
                ':amount' => $amount,
                ':currency_code' => $currencyCode,
                ':details' => $details,
                ':status' => 'submitted',
                ':created_at' => $now,
                ':updated_at' => $now,
            ]);

            $subject = 'Your Loan Application [' . $appRef . '] Has Been Received';
            $loanData = [
                'fname' => $fname,
                'lname' => $lname,
                'loan_id' => $appRef,
                'amount' => number_format($amount, 2),
                'purpose' => $purpose,
                'creation_date' => $now,
                'response_time' => '24 hours',
            ];
            $reg_user->send_mail($email, '', $subject, 'loan_alert', $loanData);

            $flashType = 'success';
            $flashMessage = 'Loan application submitted successfully. Reference: ' . $appRef;
        } catch (Throwable $e) {
            $flashType = 'error';
            $flashMessage = 'Sorry, your application could not be submitted right now. Please try again.';
        }
    }
}

$recentLoanApplications = [];
try {
    $fetchLoans = $reg_user->runQuery('SELECT application_ref, purpose, amount, currency_code, status, created_at, admin_note
        FROM loan_applications
        WHERE acc_no = :acc_no
        ORDER BY id DESC
        LIMIT 8');
    $fetchLoans->execute([':acc_no' => (string)($_SESSION['acc_no'] ?? '')]);
    $recentLoanApplications = $fetchLoans->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
}

include_once 'counter.php';
require_once __DIR__ . '/partials/shell-data.php';
$shellPageTitle = 'Loan Application';
require_once __DIR__ . '/partials/shell-open.php';
?>

<?php if ($flashMessage !== ''): ?>
<div class="mb-5 rounded-xl p-4 <?= $flashType === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' ?> text-sm">
  <?= htmlspecialchars($flashMessage) ?>
</div>
<?php endif; ?>

<div class="mb-6">
  <h1 class="text-2xl font-bold text-gray-900">Loan Application</h1>
  <p class="text-sm text-gray-500 mt-1">Submit a structured loan request to underwriting and monitor professional lifecycle statuses.</p>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 max-w-2xl">
  <form method="POST" action="">
    <input type="hidden" name="sender_name" value="<?= htmlspecialchars(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? '')) ?>">
    <div class="space-y-5">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Loan Purpose</label>
        <input type="text" name="subject" required
          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="e.g. Home improvement, Business expansion">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Loan Amount Requested</label>
        <div class="relative">
          <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">
            <?= htmlspecialchars((string)($row['currency'] ?? 'USD')) ?>
          </span>
          <input type="number" name="amount" min="0.01" step="0.01" required
            class="w-full border border-gray-300 rounded-lg pl-16 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="0.00">
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Application Details</label>
        <textarea name="msg" rows="6" required
          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
          placeholder="Describe purpose, source of repayment, expected tenor, and supporting context..."></textarea>
      </div>
    </div>
    <button type="submit" name="submit_loan_application"
      class="mt-6 w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">
      Submit Application
    </button>
  </form>
</div>

<div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 max-w-4xl">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-semibold text-gray-900">Recent Loan Applications</h2>
    <p class="text-xs text-gray-500">Submitted → Under Review → Approved/Rejected → Disbursed</p>
  </div>

  <?php if (empty($recentLoanApplications)): ?>
    <p class="text-sm text-gray-500">No loan applications yet.</p>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500">
            <th class="py-2 pr-4">Reference</th>
            <th class="py-2 pr-4">Purpose</th>
            <th class="py-2 pr-4">Amount</th>
            <th class="py-2 pr-4">Status</th>
            <th class="py-2 pr-4">Submitted</th>
            <th class="py-2 pr-4">Admin Note</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentLoanApplications as $app): ?>
            <?php
              $status = strtolower(trim((string)($app['status'] ?? 'submitted')));
              $statusClass = 'bg-slate-100 text-slate-700';
              if ($status === 'approved' || $status === 'disbursed' || $status === 'active') {
                  $statusClass = 'bg-green-100 text-green-700';
              } elseif ($status === 'under_review') {
                  $statusClass = 'bg-amber-100 text-amber-700';
              } elseif ($status === 'rejected' || $status === 'cancelled' || $status === 'closed') {
                  $statusClass = 'bg-red-100 text-red-700';
              }
            ?>
            <tr class="border-b border-gray-100">
              <td class="py-2 pr-4 font-mono text-xs text-gray-700"><?= htmlspecialchars((string)($app['application_ref'] ?? '')) ?></td>
              <td class="py-2 pr-4 text-gray-700"><?= htmlspecialchars((string)($app['purpose'] ?? '')) ?></td>
              <td class="py-2 pr-4 text-gray-700"><?= htmlspecialchars((string)($app['currency_code'] ?? 'USD')) ?> <?= number_format((float)($app['amount'] ?? 0), 2) ?></td>
              <td class="py-2 pr-4"><span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold <?= $statusClass ?>"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $status))) ?></span></td>
              <td class="py-2 pr-4 text-gray-500"><?= htmlspecialchars((string)($app['created_at'] ?? '')) ?></td>
              <td class="py-2 pr-4 text-gray-500"><?= htmlspecialchars((string)($app['admin_note'] ?? '')) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/shell-close.php'; exit(); ?>
