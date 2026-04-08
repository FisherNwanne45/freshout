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

$stmt = $reg_user->runQuery('SELECT * FROM account WHERE acc_no = :acc_no LIMIT 1');
$stmt->execute([':acc_no' => (string)$_SESSION['acc_no']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
  header('Location: logout.php');
  exit();
}

$accNo = (string)$_SESSION['acc_no'];
$baseCurrency = strtoupper(trim((string)($row['currency'] ?? 'USD')));

try {
  $reg_user->runQuery("CREATE TABLE IF NOT EXISTS investment_accounts (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      account_ref VARCHAR(32) NOT NULL,
      acc_no VARCHAR(50) NOT NULL,
      base_currency VARCHAR(10) NOT NULL DEFAULT 'USD',
      risk_profile VARCHAR(30) NOT NULL DEFAULT 'moderate',
      status VARCHAR(30) NOT NULL DEFAULT 'active',
      created_at DATETIME NOT NULL,
      updated_at DATETIME NOT NULL,
      UNIQUE KEY uq_investment_account_ref (account_ref),
      KEY idx_investment_acc_status (acc_no, status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();

  $reg_user->runQuery("CREATE TABLE IF NOT EXISTS investment_positions (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      investment_account_id BIGINT UNSIGNED NOT NULL,
      symbol VARCHAR(30) NOT NULL,
      instrument_type VARCHAR(20) NOT NULL,
      quantity DECIMAL(24,8) NOT NULL DEFAULT 0,
      avg_price DECIMAL(18,6) NOT NULL DEFAULT 0,
      market_price DECIMAL(18,6) NOT NULL DEFAULT 0,
      market_value DECIMAL(18,2) NOT NULL DEFAULT 0,
      updated_at DATETIME NOT NULL,
      KEY idx_position_account (investment_account_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();
} catch (Throwable $e) {
}

if (isset($_POST['open_investment_account'])) {
  $riskProfile = strtolower(trim((string)($_POST['risk_profile'] ?? 'moderate')));
  if (!in_array($riskProfile, ['conservative', 'moderate', 'aggressive'], true)) {
    $flashType = 'error';
    $flashMessage = 'Invalid risk profile selection.';
  } else {
    try {
      $chk = $reg_user->runQuery('SELECT id FROM investment_accounts WHERE acc_no = :acc_no ORDER BY id DESC LIMIT 1');
      $chk->execute([':acc_no' => $accNo]);
      $existing = $chk->fetch(PDO::FETCH_ASSOC);
      if ($existing) {
        $flashType = 'error';
        $flashMessage = 'Investment account already exists.';
      } else {
        $now = date('Y-m-d H:i:s');
        $ref = 'IA' . date('YmdHis') . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        $ins = $reg_user->runQuery('INSERT INTO investment_accounts
                    (account_ref, acc_no, base_currency, risk_profile, status, created_at, updated_at)
                    VALUES (:account_ref, :acc_no, :base_currency, :risk_profile, :status, :created_at, :updated_at)');
        $ins->execute([
          ':account_ref' => $ref,
          ':acc_no' => $accNo,
          ':base_currency' => $baseCurrency,
          ':risk_profile' => $riskProfile,
          ':status' => 'active',
          ':created_at' => $now,
          ':updated_at' => $now,
        ]);
        $flashType = 'success';
        $flashMessage = 'Investment account opened successfully. Ref: ' . $ref;
      }
    } catch (Throwable $e) {
      $flashType = 'error';
      $flashMessage = 'Unable to open investment account right now.';
    }
  }
}

if (isset($_POST['add_position'])) {
  $investmentAccountId = (int)($_POST['investment_account_id'] ?? 0);
  $symbol = strtoupper(trim((string)($_POST['symbol'] ?? '')));
  $instrumentType = strtolower(trim((string)($_POST['instrument_type'] ?? 'stock')));
  $quantity = (float)($_POST['quantity'] ?? 0);
  $avgPrice = (float)($_POST['avg_price'] ?? 0);
  $marketPrice = (float)($_POST['market_price'] ?? 0);

  if ($investmentAccountId <= 0 || $symbol === '' || $quantity <= 0 || $avgPrice <= 0 || $marketPrice <= 0) {
    $flashType = 'error';
    $flashMessage = 'Enter valid position details.';
  } else {
    try {
      $verify = $reg_user->runQuery('SELECT id FROM investment_accounts WHERE id = :id AND acc_no = :acc_no LIMIT 1');
      $verify->execute([':id' => $investmentAccountId, ':acc_no' => $accNo]);
      if (!$verify->fetch(PDO::FETCH_ASSOC)) {
        throw new RuntimeException('Invalid investment account selection.');
      }
      $now = date('Y-m-d H:i:s');
      $marketValue = round($quantity * $marketPrice, 2);
      $insPos = $reg_user->runQuery('INSERT INTO investment_positions
                (investment_account_id, symbol, instrument_type, quantity, avg_price, market_price, market_value, updated_at)
                VALUES
                (:investment_account_id, :symbol, :instrument_type, :quantity, :avg_price, :market_price, :market_value, :updated_at)');
      $insPos->execute([
        ':investment_account_id' => $investmentAccountId,
        ':symbol' => $symbol,
        ':instrument_type' => $instrumentType,
        ':quantity' => $quantity,
        ':avg_price' => $avgPrice,
        ':market_price' => $marketPrice,
        ':market_value' => $marketValue,
        ':updated_at' => $now,
      ]);
      $flashType = 'success';
      $flashMessage = 'Position added to investment account.';
    } catch (Throwable $e) {
      $flashType = 'error';
      $flashMessage = $e->getMessage();
    }
  }
}

$investmentAccount = null;
$positions = [];
$totalMarketValue = 0.0;
$totalCostBasis = 0.0;
try {
  $accStmt = $reg_user->runQuery('SELECT * FROM investment_accounts WHERE acc_no = :acc_no ORDER BY id DESC LIMIT 1');
  $accStmt->execute([':acc_no' => $accNo]);
  $investmentAccount = $accStmt->fetch(PDO::FETCH_ASSOC) ?: null;

  if ($investmentAccount) {
    $posStmt = $reg_user->runQuery('SELECT * FROM investment_positions WHERE investment_account_id = :id ORDER BY id DESC LIMIT 50');
    $posStmt->execute([':id' => (int)$investmentAccount['id']]);
    $positions = $posStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($positions as $p) {
      $totalMarketValue += (float)($p['market_value'] ?? 0);
      $totalCostBasis += ((float)($p['quantity'] ?? 0) * (float)($p['avg_price'] ?? 0));
    }
  }
} catch (Throwable $e) {
}

$unrealizedPnl = $totalMarketValue - $totalCostBasis;

include_once 'counter.php';
require_once __DIR__ . '/partials/shell-data.php';
$shellPageTitle = 'Investments';
require_once __DIR__ . '/partials/shell-open.php';
?>

<?php if ($flashMessage !== ''): ?>
  <div class="mb-5 rounded-xl p-4 <?= $flashType === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' ?> text-sm">
    <?= htmlspecialchars($flashMessage) ?>
  </div>
<?php endif; ?>

<div class="mb-6">
  <h1 class="text-2xl font-bold text-gray-900">Investment Accounts</h1>
  <p class="text-sm text-gray-500 mt-1">Manage positions across stocks, ETFs, and mutual funds with portfolio snapshots.</p>
</div>

<?php if (!$investmentAccount): ?>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 max-w-xl">
    <h2 class="text-lg font-semibold text-gray-900">Open Investment Account</h2>
    <form method="POST" class="mt-4 space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Risk Profile</label>
        <select name="risk_profile" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
          <option value="conservative">Conservative</option>
          <option value="moderate" selected>Moderate</option>
          <option value="aggressive">Aggressive</option>
        </select>
      </div>
      <button type="submit" name="open_investment_account" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">Open Account</button>
    </form>
  </div>
<?php else: ?>
  <div class="grid gap-6 lg:grid-cols-3">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 lg:col-span-2">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Portfolio Holdings</h2>
        <span class="text-xs text-gray-500">Ref: <?= htmlspecialchars((string)$investmentAccount['account_ref']) ?></span>
      </div>
      <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500">
              <th class="py-2 pr-4">Symbol</th>
              <th class="py-2 pr-4">Type</th>
              <th class="py-2 pr-4">Quantity</th>
              <th class="py-2 pr-4">Avg Price</th>
              <th class="py-2 pr-4">Market Price</th>
              <th class="py-2 pr-4">Market Value</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($positions as $p): ?>
              <tr class="border-b border-gray-100">
                <td class="py-2 pr-4 font-semibold text-gray-800"><?= htmlspecialchars((string)$p['symbol']) ?></td>
                <td class="py-2 pr-4 text-gray-700"><?= htmlspecialchars(strtoupper((string)$p['instrument_type'])) ?></td>
                <td class="py-2 pr-4 text-gray-700"><?= number_format((float)$p['quantity'], 4) ?></td>
                <td class="py-2 pr-4 text-gray-700"><?= htmlspecialchars($baseCurrency) ?> <?= number_format((float)$p['avg_price'], 4) ?></td>
                <td class="py-2 pr-4 text-gray-700"><?= htmlspecialchars($baseCurrency) ?> <?= number_format((float)$p['market_price'], 4) ?></td>
                <td class="py-2 pr-4 text-gray-700 font-semibold"><?= htmlspecialchars($baseCurrency) ?> <?= number_format((float)$p['market_value'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($positions)): ?>
              <tr>
                <td class="py-3 text-gray-500" colspan="6">No positions yet.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
      <h2 class="text-lg font-semibold text-gray-900">Portfolio Snapshot</h2>
      <div class="mt-4 space-y-3 text-sm">
        <div class="rounded-lg border border-gray-200 p-3 flex items-center justify-between">
          <span class="text-gray-600">Market Value</span>
          <span class="font-bold text-gray-900"><?= htmlspecialchars($baseCurrency) ?> <?= number_format($totalMarketValue, 2) ?></span>
        </div>
        <div class="rounded-lg border border-gray-200 p-3 flex items-center justify-between">
          <span class="text-gray-600">Cost Basis</span>
          <span class="font-bold text-gray-900"><?= htmlspecialchars($baseCurrency) ?> <?= number_format($totalCostBasis, 2) ?></span>
        </div>
        <div class="rounded-lg border <?= $unrealizedPnl >= 0 ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' ?> p-3 flex items-center justify-between">
          <span class="<?= $unrealizedPnl >= 0 ? 'text-green-700' : 'text-red-700' ?>">Unrealized P/L</span>
          <span class="font-bold <?= $unrealizedPnl >= 0 ? 'text-green-800' : 'text-red-800' ?>"><?= htmlspecialchars($baseCurrency) ?> <?= number_format($unrealizedPnl, 2) ?></span>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 max-w-2xl">
    <h2 class="text-lg font-semibold text-gray-900">Add Position</h2>
    <form method="POST" class="mt-4 grid gap-4 md:grid-cols-2">
      <input type="hidden" name="investment_account_id" value="<?= (int)$investmentAccount['id'] ?>">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Symbol</label>
        <input type="text" name="symbol" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="AAPL" required>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Instrument Type</label>
        <select name="instrument_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <option value="stock">Stock</option>
          <option value="etf">ETF</option>
          <option value="mutual_fund">Mutual Fund</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
        <input type="number" step="0.0001" min="0.0001" name="quantity" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Average Price</label>
        <input type="number" step="0.0001" min="0.0001" name="avg_price" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Current Market Price</label>
        <input type="number" step="0.0001" min="0.0001" name="market_price" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
      </div>
      <div class="md:col-span-2">
        <button type="submit" name="add_position" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">Save Position</button>
      </div>
    </form>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/partials/shell-close.php';
exit(); ?>