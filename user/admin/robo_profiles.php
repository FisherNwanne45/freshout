<?php
session_start();
require_once 'class.admin.php';
include_once 'session.php';

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$reg_user = new USER();
$flash = '';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_robo'])) {
    $id = (int)($_POST['profile_id'] ?? 0);
    $model = trim((string)($_POST['model_name'] ?? ''));
    $freq = strtolower(trim((string)($_POST['rebalancing_frequency'] ?? 'quarterly')));

    if ($id <= 0 || $model === '' || !in_array($freq, ['monthly', 'quarterly', 'semi_annual'], true)) {
        $flash = '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">Invalid robo profile update.</div>';
    } else {
        try {
            $up = $reg_user->runQuery('UPDATE robo_profiles SET model_name = :model_name, rebalancing_frequency = :rebalancing_frequency, updated_at = :updated_at WHERE id = :id');
            $up->execute([
                ':model_name' => $model,
                ':rebalancing_frequency' => $freq,
                ':updated_at' => date('Y-m-d H:i:s'),
                ':id' => $id,
            ]);
            $flash = '<div class="rounded-lg bg-green-50 border border-green-200 px-4 py-2 text-green-700 text-sm mb-4">Robo profile updated.</div>';
        } catch (Throwable $e) {
            $flash = '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">Unable to update robo profile.</div>';
        }
    }
}

$rows = [];
try {
    $s = $reg_user->runQuery('SELECT * FROM robo_profiles ORDER BY id DESC LIMIT 300');
    $s->execute();
    $rows = $s->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
}

$pageTitle = 'Robo Profiles';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if ($flash !== '') echo $flash; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-semibold text-gray-800">Robo-Advisory Profiles</h2>
    <span class="text-xs text-gray-500">Model governance and rebalance controls</span>
  </div>

  <div class="mb-4">
    <input type="text" id="robo-search" onkeyup="filterTable('robo-search','robo-table')" placeholder="Search by account or model"
      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 max-w-lg">
  </div>

  <div class="overflow-x-auto -mx-6 px-6">
    <table id="robo-table" class="min-w-full text-sm border-collapse">
      <thead>
        <tr class="bg-gray-50 border-y border-gray-200">
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Acc No</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Score</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Risk Band</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Model</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Frequency</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 align-top">
        <?php foreach ($rows as $r): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-3 py-3 text-xs text-gray-700"><?= htmlspecialchars((string)$r['acc_no']) ?></td>
            <td class="px-3 py-3 text-xs text-gray-700 font-semibold"><?= (int)$r['score'] ?></td>
            <td class="px-3 py-3 text-xs text-gray-700"><?= htmlspecialchars(strtoupper((string)$r['risk_band'])) ?></td>
            <td class="px-3 py-3 text-xs text-gray-700"><?= htmlspecialchars((string)$r['model_name']) ?></td>
            <td class="px-3 py-3 text-xs text-gray-700"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', (string)$r['rebalancing_frequency']))) ?></td>
            <td class="px-3 py-3 text-xs min-w-[220px]">
              <form method="post" class="space-y-2">
                <input type="hidden" name="profile_id" value="<?= (int)$r['id'] ?>">
                <input type="text" name="model_name" value="<?= htmlspecialchars((string)$r['model_name']) ?>" class="w-full rounded-lg border border-gray-300 px-2 py-1 text-xs">
                <select name="rebalancing_frequency" class="w-full rounded-lg border border-gray-300 px-2 py-1 text-xs">
                  <option value="monthly" <?= (string)$r['rebalancing_frequency'] === 'monthly' ? 'selected' : '' ?>>MONTHLY</option>
                  <option value="quarterly" <?= (string)$r['rebalancing_frequency'] === 'quarterly' ? 'selected' : '' ?>>QUARTERLY</option>
                  <option value="semi_annual" <?= (string)$r['rebalancing_frequency'] === 'semi_annual' ? 'selected' : '' ?>>SEMI ANNUAL</option>
                </select>
                <button type="submit" name="update_robo" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1 rounded-lg">Save</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function filterTable(inputId, tableId) {
  const q = document.getElementById(inputId).value.toLowerCase();
  document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}
</script>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
