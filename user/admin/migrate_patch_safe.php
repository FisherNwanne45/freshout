<?php
// Safe patch migration: additive only (no destructive changes, no content overrides).
// - Adds missing transfer columns needed by status management.
// - Creates transfer_status_history table if missing.
// - Seeds missing transfer_copy_* and transfer_settings keys only if absent.
// - Never updates existing values.

require_once dirname(__DIR__, 2) . '/config.php';

$isBrowser = php_sapi_name() !== 'cli' && isset($_SERVER['REQUEST_METHOD']);
if ($isBrowser) {
    session_start();
    if (!isset($_SESSION['email'])) {
        header('Location: login.php');
        exit();
    }
}

$didRun = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_patch'])) || isset($_GET['run']);
$logs = [];
$hadErrors = false;

$log = static function (string $line) use (&$logs): void {
    $logs[] = $line;
};

$tableExists = static function (mysqli $conn, string $table): bool {
    $t = $conn->real_escape_string($table);
    $res = $conn->query("SELECT COUNT(*) AS n FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '{$t}'");
    return $res && (int)($res->fetch_assoc()['n'] ?? 0) > 0;
};

$columnExists = static function (mysqli $conn, string $table, string $column): bool {
    $t = $conn->real_escape_string($table);
    $c = $conn->real_escape_string($column);
    $res = $conn->query("SELECT COUNT(*) AS n FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '{$t}' AND column_name = '{$c}'");
    return $res && (int)($res->fetch_assoc()['n'] ?? 0) > 0;
};

$insertSettingIfMissing = static function (mysqli $conn, string $key, string $value): bool {
    $k = $conn->real_escape_string($key);
    $v = $conn->real_escape_string($value);
    $sql = "INSERT INTO site_settings (`key`, `value`)\n"
         . "SELECT '{$k}', '{$v}' FROM DUAL\n"
         . "WHERE NOT EXISTS (SELECT 1 FROM site_settings WHERE `key` = '{$k}' LIMIT 1)";
    return (bool)$conn->query($sql);
};

$insertTransferSettingIfMissing = static function (mysqli $conn, string $key, string $value): bool {
    $k = $conn->real_escape_string($key);
    $v = $conn->real_escape_string($value);
    $sql = "INSERT INTO transfer_settings (`setting_key`, `setting_value`)\n"
         . "SELECT '{$k}', '{$v}' FROM DUAL\n"
         . "WHERE NOT EXISTS (SELECT 1 FROM transfer_settings WHERE `setting_key` = '{$k}' LIMIT 1)";
    return (bool)$conn->query($sql);
};

if ($didRun) {
    $log('[START] Safe patch migration started');

    if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_errno) {
        $hadErrors = true;
        $log('[ERROR] Database connection is unavailable. Check config.php credentials.');
    } else {
        $dbName = $conn->real_escape_string((string)($APP_CONFIG['db']['name'] ?? 'unknown'));
        $log('[OK] Connected to database: ' . $dbName);

        // 1) Ensure transfer table exists
        if (!$tableExists($conn, 'transfer')) {
            $hadErrors = true;
            $log('[ERROR] transfer table is missing. Run full migration first.');
        } else {
            $transferColumns = [
                'status_updated_by'   => "ALTER TABLE `transfer` ADD COLUMN `status_updated_by` VARCHAR(190) NULL DEFAULT NULL",
                'status_updated_at'   => "ALTER TABLE `transfer` ADD COLUMN `status_updated_at` DATETIME NULL DEFAULT NULL",
                'status_notes'        => "ALTER TABLE `transfer` ADD COLUMN `status_notes` TEXT NULL DEFAULT NULL",
                'auto_update_enabled' => "ALTER TABLE `transfer` ADD COLUMN `auto_update_enabled` TINYINT(1) NOT NULL DEFAULT 0",
                'auto_update_at'      => "ALTER TABLE `transfer` ADD COLUMN `auto_update_at` DATETIME NULL DEFAULT NULL",
                'reversal_processed'  => "ALTER TABLE `transfer` ADD COLUMN `reversal_processed` TINYINT(1) NOT NULL DEFAULT 0",
            ];

            foreach ($transferColumns as $col => $ddl) {
                if ($columnExists($conn, 'transfer', $col)) {
                    $log('[SKIP] Column transfer.' . $col . ' already exists');
                    continue;
                }
                if ($conn->query($ddl)) {
                    $log('[OK] Added column transfer.' . $col);
                } else {
                    $hadErrors = true;
                    $log('[ERROR] Failed adding transfer.' . $col . ': ' . $conn->error);
                }
            }
        }

        // 2) Ensure transfer status history table exists
        $historySql = "CREATE TABLE IF NOT EXISTS `transfer_status_history` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `transfer_id` INT NOT NULL,
            `old_status` VARCHAR(20) NULL DEFAULT NULL,
            `new_status` VARCHAR(20) NOT NULL,
            `changed_by` VARCHAR(190) NOT NULL,
            `changed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `notes` TEXT NULL DEFAULT NULL,
            KEY `idx_transfer_id` (`transfer_id`),
            KEY `idx_changed_at` (`changed_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        if ($conn->query($historySql)) {
            $log('[OK] Ensured transfer_status_history exists');
        } else {
            $hadErrors = true;
            $log('[ERROR] Failed creating transfer_status_history: ' . $conn->error);
        }

        // 3) Ensure transfer_settings exists and seed only missing keys
        $transferSettingsSql = "CREATE TABLE IF NOT EXISTS `transfer_settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `setting_key` VARCHAR(100) NOT NULL UNIQUE,
            `setting_value` TEXT NULL,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `idx_setting_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        if ($conn->query($transferSettingsSql)) {
            $log('[OK] Ensured transfer_settings exists');
        } else {
            $hadErrors = true;
            $log('[ERROR] Failed creating transfer_settings: ' . $conn->error);
        }

        $transferSettingSeeds = [
            'auto_update_enabled' => '1',
            'auto_update_delay_minutes' => '1440',
            'auto_update_target_status' => 'successful',
            'initial_transfer_status' => 'pending',
        ];
        foreach ($transferSettingSeeds as $k => $v) {
            if ($insertTransferSettingIfMissing($conn, $k, $v)) {
                $log('[OK] Ensured transfer setting ' . $k);
            } else {
                $hadErrors = true;
                $log('[ERROR] Failed ensuring transfer setting ' . $k . ': ' . $conn->error);
            }
        }

        // 4) Ensure site_settings exists
        $siteSettingsSql = "CREATE TABLE IF NOT EXISTS `site_settings` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `key` VARCHAR(191) NOT NULL,
            `value` LONGTEXT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_site_settings_key` (`key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        if ($conn->query($siteSettingsSql)) {
            $log('[OK] Ensured site_settings exists');
        } else {
            $hadErrors = true;
            $log('[ERROR] Failed creating site_settings: ' . $conn->error);
        }

        // 5) Seed per-status success copy defaults only when missing (no overwrite)
        $copyDefaults = [
            'transfer_copy_pending_title'    => 'Transfer Pending',
            'transfer_copy_pending_note'     => 'Your transfer is queued and will be processed shortly.',
            'transfer_copy_processing_title' => 'Transfer Processing',
            'transfer_copy_processing_note'  => 'Your transfer is currently being processed. This may take a moment.',
            'transfer_copy_completed_title'  => 'Transfer Completed',
            'transfer_copy_completed_note'   => 'Your transfer has been completed successfully.',
            'transfer_copy_successful_title' => 'Transfer Successful',
            'transfer_copy_successful_note'  => 'Your transfer was processed and delivered successfully.',
            'transfer_copy_failed_title'     => 'Transfer Failed',
            'transfer_copy_failed_note'      => 'This transfer could not be completed. Please contact support or try again.',
            'transfer_copy_cancelled_title'  => 'Transfer Cancelled',
            'transfer_copy_cancelled_note'   => 'This transfer has been cancelled. Any debited amount will be refunded.',
            'transfer_copy_reversed_title'   => 'Transfer Reversed',
            'transfer_copy_reversed_note'    => 'This transfer has been reversed and the amount has been credited back to your account.',
        ];

        foreach ($copyDefaults as $k => $v) {
            if ($insertSettingIfMissing($conn, $k, $v)) {
                $log('[OK] Ensured site setting ' . $k);
            } else {
                $hadErrors = true;
                $log('[ERROR] Failed ensuring site setting ' . $k . ': ' . $conn->error);
            }
        }

        // Mark patch migration completion without overwriting existing values.
        if ($insertSettingIfMissing($conn, 'db_migration_patch_safe_v1', 'done')) {
            $log('[OK] Marked db_migration_patch_safe_v1');
        } else {
            $hadErrors = true;
            $log('[ERROR] Failed setting db_migration_patch_safe_v1: ' . $conn->error);
        }
    }

    $log($hadErrors ? '[DONE] Completed with errors. Review log output.' : '[DONE] Completed successfully. No existing content was overridden.');
}

$pageTitle = 'Safe Patch Migration';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<div class="max-w-5xl space-y-6">
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="text-lg font-semibold text-gray-800">Safe Patch Migration</h2>
    <p class="text-sm text-gray-600 mt-2">Runs additive-only migration steps for transfer status updates. Existing settings/content are never overwritten.</p>
    <form method="post" class="mt-4">
      <button type="submit" name="run_patch" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer">Run Safe Patch</button>
      <a href="?run=1" class="inline-flex items-center gap-2 ml-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium px-4 py-2 rounded-lg transition-colors">Run via URL</a>
    </form>
  </div>

  <?php if ($didRun): ?>
  <div class="bg-slate-950 text-slate-100 rounded-xl border border-slate-800 p-5">
    <h3 class="text-sm font-semibold tracking-wide uppercase text-slate-300 mb-3">Patch Progress Log</h3>
    <div class="text-xs leading-6 font-mono whitespace-pre-wrap"><?php foreach ($logs as $line) { echo htmlspecialchars($line) . "\n"; } ?></div>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
