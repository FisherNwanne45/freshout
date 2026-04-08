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

$stmt = $reg_user->runQuery('SELECT * FROM account WHERE acc_no = :acc_no LIMIT 1');
$stmt->execute([':acc_no' => (string)$_SESSION['acc_no']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    header('Location: logout.php');
    exit();
}

$accNo = (string)$_SESSION['acc_no'];
$currency = strtoupper(trim((string)($row['currency'] ?? 'USD')));

try {
    $reg_user->runQuery("CREATE TABLE IF NOT EXISTS term_deposits (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      deposit_ref VARCHAR(32) NOT NULL,
      acc_no VARCHAR(50) NOT NULL,
      principal DECIMAL(18,2) NOT NULL,
      annual_rate DECIMAL(7,4) NOT NULL,
      tenor_months INT NOT NULL,
      start_date DATE NOT NULL,
      maturity_date DATE NOT NULL,
      maturity_amount DECIMAL(18,2) NOT NULL,
      status VARCHAR(30) NOT NULL DEFAULT 'active',
      payout_mode VARCHAR(20) NOT NULL DEFAULT 'payout',
      created_at DATETIME NOT NULL,
      updated_at DATETIME NOT NULL,
      UNIQUE KEY uq_term_deposit_ref (deposit_ref),
      KEY idx_term_deposit_acc_status (acc_no, status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();
} catch (Throwable $e) {
}

if (isset($_POST['open_term_deposit'])) {
    $principal = (float)($_POST['principal'] ?? 0);
    $annualRate = (float)($_POST['annual_rate'] ?? 0);
    $tenorMonths = (int)($_POST['tenor_months'] ?? 0);
    $payoutMode = strtolower(trim((string)($_POST['payout_mode'] ?? 'payout')));

    if ($principal <= 0 || $annualRate <= 0 || $tenorMonths <= 0) {
        $flashType = 'error';
        $flashMessage = 'Enter valid principal, rate, and tenor.';
    } elseif (!in_array($payoutMode, ['payout', 'renew_principal', 'renew_all'], true)) {
        $flashType = 'error';
        $flashMessage = 'Invalid maturity instruction selected.';
    } elseif ((float)$row['a_bal'] < $principal) {
        $flashType = 'error';
        $flashMessage = 'Insufficient available balance for this placement.';
    } else {
        try {
            $reg_user->runQuery('START TRANSACTION')->execute();

            $fresh = $reg_user->runQuery('SELECT a_bal, t_bal FROM account WHERE acc_no = :acc_no FOR UPDATE');
            $fresh->execute([':acc_no' => $accNo]);
            $acct = $fresh->fetch(PDO::FETCH_ASSOC) ?: ['a_bal' => 0, 't_bal' => 0];
            if ((float)$acct['a_bal'] < $principal) {
                throw new RuntimeException('Insufficient available balance for this placement.');
            }

            $startDate = date('Y-m-d');
            $maturityDate = date('Y-m-d', strtotime('+' . $tenorMonths . ' months'));
            $interest = $principal * ($annualRate / 100) * ($tenorMonths / 12);
            $maturityAmount = round($principal + $interest, 2);
            $now = date('Y-m-d H:i:s');
            $depositRef = 'TD' . date('YmdHis') . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));

            $ins = $reg_user->runQuery('INSERT INTO term_deposits
                (deposit_ref, acc_no, principal, annual_rate, tenor_months, start_date, maturity_date, maturity_amount, status, payout_mode, created_at, updated_at)
                VALUES
                (:deposit_ref, :acc_no, :principal, :annual_rate, :tenor_months, :start_date, :maturity_date, :maturity_amount, :status, :payout_mode, :created_at, :updated_at)');
            $ins->execute([
                ':deposit_ref' => $depositRef,
                ':acc_no' => $accNo,
                ':principal' => $principal,
                ':annual_rate' => $annualRate,
                ':tenor_months' => $tenorMonths,
                ':start_date' => $startDate,
                ':maturity_date' => $maturityDate,
                ':maturity_amount' => $maturityAmount,
                ':status' => 'active',
                ':payout_mode' => $payoutMode,
                ':created_at' => $now,
                ':updated_at' => $now,
            ]);

            $upd = $reg_user->runQuery('UPDATE account SET a_bal = a_bal - :p, t_bal = t_bal - :p WHERE acc_no = :acc_no');
            $upd->execute([':p' => $principal, ':acc_no' => $accNo]);

            try {
                $alerts = $reg_user->runQuery('INSERT INTO alerts (uname, type, amount, sender_name, remarks, date, time) VALUES (:uname, :type, :amount, :sender_name, :remarks, :date, :time)');
                $alerts->execute([
                    ':uname' => $accNo,
                    ':type' => 'Term Deposit Placement',
                    ':amount' => $principal,
                    ':sender_name' => 'Treasury Desk',
                    ':remarks' => 'Ref ' . $depositRef . ', maturity ' . $maturityDate,
                    ':date' => date('Y-m-d'),
                    ':time' => date('H:i:s'),
                ]);
            } catch (Throwable $e) {
            }

            $reg_user->runQuery('COMMIT')->execute();
            $flashType = 'success';
            $flashMessage = 'Term deposit opened successfully. Reference: ' . $depositRef;

            $stmt->execute([':acc_no' => (string)$_SESSION['acc_no']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: $row;
        } catch (Throwable $e) {
            try {
                $reg_user->runQuery('ROLLBACK')->execute();
            } catch (Throwable $rollbackError) {
            }
            $flashType = 'error';
            $flashMessage = $e->getMessage();
        }
    }
}

$depositRows = [];
$depositSummary = ['active' => 0.0, 'matured' => 0.0, 'closed' => 0.0];
try {
    $list = $reg_user->runQuery('SELECT deposit_ref, principal, annual_rate, tenor_months, maturity_date, maturity_amount, status, payout_mode, created_at
        FROM term_deposits
        WHERE acc_no = :acc_no
        ORDER BY id DESC
        LIMIT 25');
    $list->execute([':acc_no' => $accNo]);
    $depositRows = $list->fetchAll(PDO::FETCH_ASSOC);
    foreach ($depositRows as $d) {
        $k = strtolower((string)($d['status'] ?? 'active'));
        if (isset($depositSummary[$k])) {
            $depositSummary[$k] += (float)($d['maturity_amount'] ?? 0);
        }
    }
} catch (Throwable $e) {
}

include_once 'counter.php';
require_once __DIR__ . '/partials/shell-data.php';
$shellPageTitle = 'Term Deposits';
require_once __DIR__ . '/partials/shell-open.php';
?>

<?php if ($flashMessage !== ''): ?>
<div class="mb-5 rounded-xl p-4 <?= $flashType === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' ?> text-sm">
  <?= htmlspecialchars($flashMessage) ?>
</div>
<?php endif; ?>

<div class="mb-6">
  <h1 class="text-2xl font-bold text-gray-900">Fixed / Term Deposits</h1>
  <p class="text-sm text-gray-500 mt-1">Place funds in fixed tenor products with maturity instructions and projected returns.</p>
</div>

<div class="grid gap-6 lg:grid-cols-3">
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 lg:col-span-2">
    <h2 class="text-lg font-semibold text-gray-900">Open New Term Deposit</h2>
    <p class="text-xs text-gray-500 mt-1">Available balance: <?= htmlspecialchars($currency) ?> <?= number_format((float)($row['a_bal'] ?? 0), 2) ?></p>
    <form method="POST" class="mt-4 grid gap-4 md:grid-cols-2">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Principal</label>
        <input type="number" step="0.01" min="0.01" name="principal" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="0.00">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Annual Rate (%)</label>
        <input type="number" step="0.01" min="0.01" name="annual_rate" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" value="4.50">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tenor (Months)</label>
        <select name="tenor_months" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <option value="3">3 months</option>
          <option value="6">6 months</option>
          <option value="12">12 months</option>
          <option value="24">24 months</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Maturity Instruction</label>
        <select name="payout_mode" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <option value="payout">Payout to account</option>
          <option value="renew_principal">Auto renew principal</option>
          <option value="renew_all">Auto renew principal + interest</option>
        </select>
      </div>
      <div class="md:col-span-2">
        <button type="submit" name="open_term_deposit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">Place Deposit</button>
      </div>
    </form>
  </div>

  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
    <h2 class="text-lg font-semibold text-gray-900">Portfolio Snapshot</h2>
    <div class="mt-4 space-y-3 text-sm">
      <div class="rounded-lg border border-green-200 bg-green-50 p-3 flex items-center justify-between">
        <span class="text-green-700 font-medium">Active</span>
        <span class="text-green-800 font-bold"><?= htmlspecialchars($currency) ?> <?= number_format((float)$depositSummary['active'], 2) ?></span>
      </div>
      <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 flex items-center justify-between">
        <span class="text-amber-700 font-medium">Matured</span>
        <span class="text-amber-800 font-bold"><?= htmlspecialchars($currency) ?> <?= number_format((float)$depositSummary['matured'], 2) ?></span>
      </div>
      <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 flex items-center justify-between">
        <span class="text-slate-700 font-medium">Closed</span>
        <span class="text-slate-800 font-bold"><?= htmlspecialchars($currency) ?> <?= number_format((float)$depositSummary['closed'], 2) ?></span>
      </div>
    </div>
  </div>
</div>

<div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
  <h2 class="text-lg font-semibold text-gray-900 mb-4">My Deposit Placements</h2>
  <?php if (empty($depositRows)): ?>
    <p class="text-sm text-gray-500">No placements yet.</p>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500">
            <th class="py-2 pr-4">Reference</th>
            <th class="py-2 pr-4">Principal</th>
            <th class="py-2 pr-4">Rate</th>
            <th class="py-2 pr-4">Tenor</th>
            <th class="py-2 pr-4">Maturity Date</th>
            <th class="py-2 pr-4">Maturity Amount</th>
            <th class="py-2 pr-4">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($depositRows as $d): ?>
            <?php $st = strtolower((string)($d['status'] ?? 'active')); ?>
            <tr class="border-b border-gray-100">
              <td class="py-2 pr-4 font-mono text-xs text-gray-700"><?= htmlspecialchars((string)$d['deposit_ref']) ?></td>
              <td class="py-2 pr-4 text-gray-700"><?= htmlspecialchars($currency) ?> <?= number_format((float)$d['principal'], 2) ?></td>
              <td class="py-2 pr-4 text-gray-700"><?= number_format((float)$d['annual_rate'], 2) ?>%</td>
              <td class="py-2 pr-4 text-gray-700"><?= (int)$d['tenor_months'] ?> months</td>
              <td class="py-2 pr-4 text-gray-700"><?= htmlspecialchars((string)$d['maturity_date']) ?></td>
              <td class="py-2 pr-4 text-gray-700 font-semibold"><?= htmlspecialchars($currency) ?> <?= number_format((float)$d['maturity_amount'], 2) ?></td>
              <td class="py-2 pr-4"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700"><?= htmlspecialchars(strtoupper($st)) ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/shell-close.php'; exit(); ?>
