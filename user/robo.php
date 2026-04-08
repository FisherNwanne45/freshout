<?php
session_start();

include_once 'session.php';
require_once 'class.user.php';

if (!isset($_SESSION['acc_no'])) {
  header('Location: login.php');
  exit();
}
if (!isset($_SESSION['pin'])) {
  header('Location: passcode.php');
  exit();
}

$reg_user = new USER();
$flashMessage = '';
$flashType = 'success';
$accNo = (string)$_SESSION['acc_no'];

try {
  $reg_user->runQuery("CREATE TABLE IF NOT EXISTS robo_profiles (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      acc_no VARCHAR(50) NOT NULL,
      score INT NOT NULL,
      risk_band VARCHAR(30) NOT NULL,
      model_name VARCHAR(60) NOT NULL,
      rebalancing_frequency VARCHAR(30) NOT NULL DEFAULT 'quarterly',
      created_at DATETIME NOT NULL,
      updated_at DATETIME NOT NULL,
      KEY idx_robo_profile_acc (acc_no)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();
} catch (Throwable $e) {
}

if (isset($_POST['save_robo_profile'])) {
  $ageBand = (int)($_POST['age_band'] ?? 0);
  $incomeStability = (int)($_POST['income_stability'] ?? 0);
  $lossTolerance = (int)($_POST['loss_tolerance'] ?? 0);
  $horizon = (int)($_POST['horizon'] ?? 0);
  $freq = strtolower(trim((string)($_POST['frequency'] ?? 'quarterly')));

  if (!in_array($freq, ['monthly', 'quarterly', 'semi_annual'], true)) {
    $flashType = 'error';
    $flashMessage = 'Invalid rebalance frequency selected.';
  } else {
    $score = max(0, min(100, $ageBand + $incomeStability + $lossTolerance + $horizon));
    $riskBand = 'conservative';
    $modelName = 'Income Shield';
    if ($score >= 70) {
      $riskBand = 'aggressive';
      $modelName = 'Growth Alpha';
    } elseif ($score >= 45) {
      $riskBand = 'moderate';
      $modelName = 'Balanced Core';
    }

    try {
      $now = date('Y-m-d H:i:s');
      $chk = $reg_user->runQuery('SELECT id FROM robo_profiles WHERE acc_no = :acc_no ORDER BY id DESC LIMIT 1');
      $chk->execute([':acc_no' => $accNo]);
      $existing = $chk->fetch(PDO::FETCH_ASSOC);

      if ($existing) {
        $up = $reg_user->runQuery('UPDATE robo_profiles
                    SET score = :score, risk_band = :risk_band, model_name = :model_name, rebalancing_frequency = :rebalancing_frequency, updated_at = :updated_at
                    WHERE id = :id');
        $up->execute([
          ':score' => $score,
          ':risk_band' => $riskBand,
          ':model_name' => $modelName,
          ':rebalancing_frequency' => $freq,
          ':updated_at' => $now,
          ':id' => (int)$existing['id'],
        ]);
      } else {
        $ins = $reg_user->runQuery('INSERT INTO robo_profiles
                    (acc_no, score, risk_band, model_name, rebalancing_frequency, created_at, updated_at)
                    VALUES
                    (:acc_no, :score, :risk_band, :model_name, :rebalancing_frequency, :created_at, :updated_at)');
        $ins->execute([
          ':acc_no' => $accNo,
          ':score' => $score,
          ':risk_band' => $riskBand,
          ':model_name' => $modelName,
          ':rebalancing_frequency' => $freq,
          ':created_at' => $now,
          ':updated_at' => $now,
        ]);
      }

      $flashType = 'success';
      $flashMessage = 'Robo-advisory profile saved. Recommended model: ' . $modelName;
    } catch (Throwable $e) {
      $flashType = 'error';
      $flashMessage = 'Unable to save robo profile right now.';
    }
  }
}

$profile = null;
try {
  $p = $reg_user->runQuery('SELECT * FROM robo_profiles WHERE acc_no = :acc_no ORDER BY id DESC LIMIT 1');
  $p->execute([':acc_no' => $accNo]);
  $profile = $p->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $e) {
}

include_once 'counter.php';
require_once __DIR__ . '/partials/shell-data.php';
$shellPageTitle = 'Robo Advisory';
require_once __DIR__ . '/partials/shell-open.php';
?>

<?php if ($flashMessage !== ''): ?>
  <div class="mb-5 rounded-xl p-4 <?= $flashType === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' ?> text-sm">
    <?= htmlspecialchars($flashMessage) ?>
  </div>
<?php endif; ?>

<div class="mb-6">
  <h1 class="text-2xl font-bold text-gray-900">Robo-Advisory Portfolios</h1>
  <p class="text-sm text-gray-500 mt-1">Complete a short risk assessment and receive automated model allocation.</p>
</div>

<div class="grid gap-6 lg:grid-cols-3">
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 lg:col-span-2">
    <h2 class="text-lg font-semibold text-gray-900">Risk Assessment</h2>
    <form method="POST" class="mt-4 grid gap-4 md:grid-cols-2">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Investment Horizon</label>
        <select name="horizon" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <option value="8">Less than 2 years</option>
          <option value="14">2 - 5 years</option>
          <option value="22">More than 5 years</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Income Stability</label>
        <select name="income_stability" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <option value="8">Variable</option>
          <option value="14">Mostly stable</option>
          <option value="22">Highly stable</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Loss Tolerance</label>
        <select name="loss_tolerance" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <option value="8">Low</option>
          <option value="14">Medium</option>
          <option value="22">High</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Age Band</label>
        <select name="age_band" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <option value="22">18 - 35</option>
          <option value="14">36 - 55</option>
          <option value="8">56+</option>
        </select>
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Rebalance Frequency</label>
        <select name="frequency" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <option value="monthly">Monthly</option>
          <option value="quarterly" selected>Quarterly</option>
          <option value="semi_annual">Semi-Annual</option>
        </select>
      </div>
      <div class="md:col-span-2">
        <button type="submit" name="save_robo_profile" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">Save Advisory Profile</button>
      </div>
    </form>
  </div>

  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
    <h2 class="text-lg font-semibold text-gray-900">Current Model</h2>
    <?php if (!$profile): ?>
      <p class="mt-3 text-sm text-gray-500">No robo profile configured yet.</p>
    <?php else: ?>
      <div class="mt-4 space-y-3 text-sm">
        <div class="rounded-lg border border-gray-200 p-3 flex items-center justify-between">
          <span class="text-gray-600">Risk Score</span>
          <span class="font-bold text-gray-900"><?= (int)$profile['score'] ?>/100</span>
        </div>
        <div class="rounded-lg border border-gray-200 p-3 flex items-center justify-between">
          <span class="text-gray-600">Risk Band</span>
          <span class="font-bold text-gray-900"><?= htmlspecialchars(strtoupper((string)$profile['risk_band'])) ?></span>
        </div>
        <div class="rounded-lg border border-gray-200 p-3 flex items-center justify-between">
          <span class="text-gray-600">Model</span>
          <span class="font-bold text-gray-900"><?= htmlspecialchars((string)$profile['model_name']) ?></span>
        </div>
        <div class="rounded-lg border border-gray-200 p-3 flex items-center justify-between">
          <span class="text-gray-600">Rebalance</span>
          <span class="font-bold text-gray-900"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', (string)$profile['rebalancing_frequency']))) ?></span>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/partials/shell-close.php';
exit(); ?>