<?php
// ── Auto-Migration v9 ──────────────────────────────────────────────────────────
// v2: currency_code + transfer_type + auth_method + crypto_transfers.
// v3: login_method + database OTP table + legacy status normalization.
// v4: account.pin column + mname/pin data backfill for phased rename.
// v5: real per-currency customer accounts + transfer source/destination account refs.
// v6: currency normalization for legacy symbol values before account backfill.
// v7: enforce currencies-table integrity for account_balances/customer_accounts.
// v8: compatibility cleanup using NOT IN for broader MySQL support.
// v9: hex-based cleanup for misencoded symbols + wallet mirror sync.
// v10: decimal temp_transfer amount + account type key cleanup.
// v11: IBAN fields for customer_accounts and backfill.
// v12: crypto treasury config + deposit/withdrawal request workflow.
// Safe to include on every request – skips if already applied.
if (!isset($conn) || !($conn instanceof mysqli)) { return; }
require_once __DIR__ . '/iban-tools.php';

$_ssKeyCol = 'setting_key';
$_ssValCol = 'setting_value';
try {
    $ssCols = [];
    $ssColsRes = $conn->query('SHOW COLUMNS FROM site_settings');
    if ($ssColsRes) {
        while ($ssCol = $ssColsRes->fetch_assoc()) {
            $f = strtolower((string)($ssCol['Field'] ?? ''));
            if ($f !== '') {
                $ssCols[$f] = true;
            }
        }
    }
    if (isset($ssCols['key']) && isset($ssCols['value'])) {
        $_ssKeyCol = 'key';
        $_ssValCol = 'value';
    }
} catch (Throwable $e) {}

try {
    $r = $conn->query("SELECT `" . $_ssValCol . "` AS migration_value FROM site_settings WHERE `" . $_ssKeyCol . "`='db_migration_v12' LIMIT 1");
    if ($r && $r->num_rows > 0 && ($r->fetch_assoc()['migration_value'] ?? '') === 'done') { return; }
} catch (Throwable $e) {}

// Helper: does column exist?
$_colExists = static function (mysqli $c, string $tbl, string $col): bool {
    $r = $c->query("SELECT COUNT(*) AS n FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='$tbl' AND COLUMN_NAME='$col'");
    return $r && (int)($r->fetch_assoc()['n'] ?? 0) > 0;
};

$_alters = [
    ['transfer',      'currency_code', "ALTER TABLE `transfer`      ADD COLUMN `currency_code` VARCHAR(10) NOT NULL DEFAULT 'USD'"],
    ['transfer',      'transfer_type', "ALTER TABLE `transfer`      ADD COLUMN `transfer_type` VARCHAR(20) NOT NULL DEFAULT 'standard'"],
    ['transfer',      'source_account_no', "ALTER TABLE `transfer`   ADD COLUMN `source_account_no` VARCHAR(40) NULL DEFAULT NULL"],
    ['transfer',      'destination_account_no', "ALTER TABLE `transfer` ADD COLUMN `destination_account_no` VARCHAR(40) NULL DEFAULT NULL"],
    ['temp_transfer', 'currency_code', "ALTER TABLE `temp_transfer` ADD COLUMN `currency_code` VARCHAR(10) NOT NULL DEFAULT 'USD'"],
    ['temp_transfer', 'transfer_type', "ALTER TABLE `temp_transfer` ADD COLUMN `transfer_type` VARCHAR(20) NOT NULL DEFAULT 'standard'"],
    ['temp_transfer', 'source_account_no', "ALTER TABLE `temp_transfer` ADD COLUMN `source_account_no` VARCHAR(40) NULL DEFAULT NULL"],
    ['temp_transfer', 'destination_account_no', "ALTER TABLE `temp_transfer` ADD COLUMN `destination_account_no` VARCHAR(40) NULL DEFAULT NULL"],
    ['account',       'auth_method',   "ALTER TABLE `account`       ADD COLUMN `auth_method`   VARCHAR(20) NULL DEFAULT NULL"],
    ['account',       'login_method',  "ALTER TABLE `account`       ADD COLUMN `login_method`  VARCHAR(20) NULL DEFAULT NULL"],
    ['account',       'pin',           "ALTER TABLE `account`       ADD COLUMN `pin`           VARCHAR(20) NULL DEFAULT NULL"],
];
foreach ($_alters as [$tbl, $col, $sql]) {
    if (!$_colExists($conn, $tbl, $col)) {
        try { $conn->query($sql); } catch (Throwable $e) {}
    }
}

try {
    $conn->query("CREATE TABLE IF NOT EXISTS `customer_accounts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `owner_acc_no` VARCHAR(50) NOT NULL,
        `account_no` VARCHAR(40) NOT NULL,
        `currency_code` VARCHAR(10) NOT NULL,
        `balance` DECIMAL(20,8) NOT NULL DEFAULT 0,
        `status` VARCHAR(20) NOT NULL DEFAULT 'active',
        `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_account_no (`account_no`),
        UNIQUE KEY uq_owner_currency (`owner_acc_no`, `currency_code`),
        KEY idx_owner (`owner_acc_no`),
        KEY idx_currency (`currency_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable $e) {}

// ── Auto-Migration v12 ─────────────────────────────────────────────────────────
// v12: crypto deposit wallet config + user crypto deposit/withdrawal requests.
try {
    $r12 = $conn->query("SELECT `" . $_ssValCol . "` AS migration_value FROM site_settings WHERE `" . $_ssKeyCol . "`='db_migration_v12' LIMIT 1");
    if (!($r12 && $r12->num_rows > 0 && ($r12->fetch_assoc()['migration_value'] ?? '') === 'done')) {

        $conn->query("CREATE TABLE IF NOT EXISTS `crypto_deposit_wallets` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `currency_code` VARCHAR(10) NOT NULL,
            `network_name` VARCHAR(60) NOT NULL DEFAULT '',
            `wallet_label` VARCHAR(120) NOT NULL DEFAULT '',
            `wallet_address` VARCHAR(255) NOT NULL DEFAULT '',
            `qr_code_path` VARCHAR(255) NULL DEFAULT NULL,
            `instructions` TEXT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            UNIQUE KEY `uq_crypto_wallet_currency` (`currency_code`),
            KEY `idx_crypto_wallet_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $conn->query("CREATE TABLE IF NOT EXISTS `crypto_deposit_requests` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `deposit_ref` VARCHAR(32) NOT NULL,
            `acc_no` VARCHAR(50) NOT NULL,
            `email` VARCHAR(190) NOT NULL,
            `currency_code` VARCHAR(10) NOT NULL,
            `network_name` VARCHAR(60) NOT NULL DEFAULT '',
            `wallet_address` VARCHAR(255) NOT NULL DEFAULT '',
            `sender_wallet_address` VARCHAR(255) NOT NULL DEFAULT '',
            `tx_hash` VARCHAR(255) NOT NULL DEFAULT '',
            `amount` DECIMAL(20,8) NOT NULL DEFAULT 0,
            `proof_path` VARCHAR(255) NULL DEFAULT NULL,
            `user_note` TEXT NULL,
            `admin_note` TEXT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
            `approved_by` VARCHAR(190) NULL DEFAULT NULL,
            `approved_at` DATETIME NULL DEFAULT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            UNIQUE KEY `uq_crypto_deposit_ref` (`deposit_ref`),
            KEY `idx_crypto_deposit_acc_status` (`acc_no`, `status`),
            KEY `idx_crypto_deposit_currency` (`currency_code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $conn->query("CREATE TABLE IF NOT EXISTS `crypto_withdrawal_requests` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `withdrawal_ref` VARCHAR(32) NOT NULL,
            `acc_no` VARCHAR(50) NOT NULL,
            `email` VARCHAR(190) NOT NULL,
            `currency_code` VARCHAR(10) NOT NULL,
            `network_name` VARCHAR(60) NOT NULL DEFAULT '',
            `destination_address` VARCHAR(255) NOT NULL DEFAULT '',
            `amount` DECIMAL(20,8) NOT NULL DEFAULT 0,
            `user_note` TEXT NULL,
            `admin_note` TEXT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
            `processed_by` VARCHAR(190) NULL DEFAULT NULL,
            `processed_at` DATETIME NULL DEFAULT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            UNIQUE KEY `uq_crypto_withdrawal_ref` (`withdrawal_ref`),
            KEY `idx_crypto_withdrawal_acc_status` (`acc_no`, `status`),
            KEY `idx_crypto_withdrawal_currency` (`currency_code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $conn->query(
            "INSERT INTO site_settings (`" . $_ssKeyCol . "`, `" . $_ssValCol . "`) VALUES ('db_migration_v12','done') " .
            "ON DUPLICATE KEY UPDATE `" . $_ssValCol . "`='done'"
        );
    }
} catch (Throwable $e) {}

// Normalize symbol-based legacy currencies into ISO codes.
try {
    $conn->query("UPDATE account SET currency = 'USD' WHERE TRIM(currency) = '$'");
    $conn->query("UPDATE account SET currency = 'EUR' WHERE TRIM(currency) IN ('€', 'Â‚¬')");
    $conn->query("UPDATE account SET currency = 'GBP' WHERE TRIM(currency) IN ('£', 'Â£')");
    $conn->query("UPDATE account SET currency = UPPER(TRIM(currency)) WHERE currency IS NOT NULL");
    $conn->query("UPDATE account SET currency = 'USD' WHERE currency IS NULL OR TRIM(currency) = '' OR UPPER(TRIM(currency)) NOT REGEXP '^[A-Z0-9]{2,10}$'");
} catch (Throwable $e) {}

try {
    $conn->query("UPDATE account_balances SET currency_code = 'USD' WHERE TRIM(currency_code) = '$'");
    $conn->query("UPDATE account_balances SET currency_code = 'EUR' WHERE TRIM(currency_code) IN ('€', 'Â‚¬')");
    $conn->query("UPDATE account_balances SET currency_code = 'GBP' WHERE TRIM(currency_code) IN ('£', 'Â£')");
    $conn->query("UPDATE account_balances SET currency_code = 'USD' WHERE HEX(currency_code) = '24'");
    $conn->query("UPDATE account_balances SET currency_code = 'EUR' WHERE HEX(currency_code) = 'C382E2809AC2AC'");
    $conn->query("UPDATE account_balances SET currency_code = UPPER(TRIM(currency_code))");
    $conn->query("UPDATE account_balances SET currency_code = 'USD' WHERE currency_code IS NULL OR TRIM(currency_code) = '' OR UPPER(TRIM(currency_code)) NOT REGEXP '^[A-Z0-9]{2,10}$'");
    $conn->query("UPDATE account_balances
                  SET currency_code = 'USD'
                  WHERE UPPER(TRIM(currency_code)) NOT IN (SELECT code FROM currencies)");
} catch (Throwable $e) {}

try {
    $conn->query("UPDATE customer_accounts SET currency_code = 'USD' WHERE TRIM(currency_code) = '$'");
    $conn->query("UPDATE customer_accounts SET currency_code = 'EUR' WHERE TRIM(currency_code) IN ('€', 'Â‚¬')");
    $conn->query("UPDATE customer_accounts SET currency_code = 'GBP' WHERE TRIM(currency_code) IN ('£', 'Â£')");
    $conn->query("UPDATE customer_accounts SET currency_code = 'USD' WHERE HEX(currency_code) = '24'");
    $conn->query("UPDATE customer_accounts SET currency_code = 'EUR' WHERE HEX(currency_code) = 'C382E2809AC2AC'");
    $conn->query("UPDATE customer_accounts SET currency_code = UPPER(TRIM(currency_code))");
    $conn->query("DELETE FROM customer_accounts WHERE currency_code IS NULL OR TRIM(currency_code) = '' OR UPPER(TRIM(currency_code)) NOT REGEXP '^[A-Z0-9]{2,10}$'");
    $conn->query("DELETE FROM customer_accounts
                  WHERE UPPER(TRIM(currency_code)) NOT IN (SELECT code FROM currencies)");
    $conn->query("DELETE ca FROM customer_accounts ca
                  JOIN customer_accounts e ON e.owner_acc_no = ca.owner_acc_no AND e.currency_code = 'EUR'
                  WHERE HEX(ca.currency_code) = 'C382E2809AC2AC'");
    $conn->query("DELETE ca FROM customer_accounts ca
                  JOIN customer_accounts u ON u.owner_acc_no = ca.owner_acc_no AND u.currency_code = 'USD'
                  WHERE HEX(ca.currency_code) = '24'");
} catch (Throwable $e) {}

try {
    $conn->query("CREATE TABLE IF NOT EXISTS `crypto_transfers` (
        `id`             INT AUTO_INCREMENT PRIMARY KEY,
        `acc_no`         VARCHAR(20)    NOT NULL,
        `email`          VARCHAR(120)   NOT NULL,
        `currency_code`  VARCHAR(10)    NOT NULL,
        `amount`         DECIMAL(20,8)  NOT NULL DEFAULT 0,
        `wallet_address` VARCHAR(200)   NOT NULL DEFAULT '',
        `network`        VARCHAR(50)    NOT NULL DEFAULT '',
        `tx_hash`        VARCHAR(200)   DEFAULT NULL,
        `status`         ENUM('pending','confirmed','failed') NOT NULL DEFAULT 'pending',
        `remarks`        TEXT,
        `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_acc (`acc_no`),
        KEY idx_email (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable $e) {}

try {
    $conn->query("CREATE TABLE IF NOT EXISTS `account_otp_codes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `acc_no` VARCHAR(50) NULL,
        `email` VARCHAR(190) NULL,
        `purpose` VARCHAR(50) NOT NULL,
        `otp_code` VARCHAR(10) NOT NULL,
        `expires_at` DATETIME NOT NULL,
        `used_at` DATETIME NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_otp_acc (`acc_no`, `purpose`, `otp_code`, `used_at`),
        KEY idx_otp_email (`email`, `purpose`, `otp_code`, `used_at`),
        KEY idx_otp_exp (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable $e) {}

// Backfill real per-currency accounts from existing wallet rows.
try {
    $conn->query("INSERT INTO customer_accounts (owner_acc_no, account_no, currency_code, balance, status, is_primary)
        SELECT
            ab.acc_no,
            CONCAT(ab.acc_no, '-', UPPER(ab.currency_code)),
            UPPER(ab.currency_code),
            ab.balance,
            'active',
            CASE WHEN UPPER(ab.currency_code) = UPPER(COALESCE(a.currency, 'USD')) THEN 1 ELSE 0 END
        FROM account_balances ab
        LEFT JOIN account a ON a.acc_no = ab.acc_no
        ON DUPLICATE KEY UPDATE
            account_no = VALUES(account_no),
            balance = VALUES(balance),
            status = 'active',
            is_primary = VALUES(is_primary)");
} catch (Throwable $e) {}

// Keep legacy account_balances in sync while old pages still depend on it.
try {
    $conn->query("INSERT INTO account_balances (acc_no, currency_code, balance)
                  SELECT owner_acc_no, currency_code, balance FROM customer_accounts
                  ON DUPLICATE KEY UPDATE balance = VALUES(balance)");
} catch (Throwable $e) {}

// Ensure every user has at least one primary customer account from account table.
try {
    $conn->query("INSERT INTO customer_accounts (owner_acc_no, account_no, currency_code, balance, status, is_primary)
        SELECT
            a.acc_no,
            CONCAT(a.acc_no, '-', UPPER(COALESCE(NULLIF(a.currency, ''), 'USD'))),
            UPPER(COALESCE(NULLIF(a.currency, ''), 'USD')),
            COALESCE(a.a_bal, a.t_bal, 0),
            'active',
            1
        FROM account a
        WHERE a.acc_no IS NOT NULL AND TRIM(a.acc_no) <> ''
        ON DUPLICATE KEY UPDATE
            account_no = VALUES(account_no),
            balance = VALUES(balance),
            status = 'active',
            is_primary = CASE WHEN customer_accounts.is_primary = 1 THEN 1 ELSE VALUES(is_primary) END");
} catch (Throwable $e) {}

// Normalize legacy status values that encoded login method.
try {
    $conn->query("UPDATE account
        SET login_method = 'otp', status = 'Active'
        WHERE LOWER(TRIM(status)) = 'otp'");
    $conn->query("UPDATE account
        SET login_method = 'pin', status = 'Active'
        WHERE LOWER(TRIM(status)) IN ('pincode', 'pin')");
    $conn->query("UPDATE account
        SET login_method = 'pin'
        WHERE (login_method IS NULL OR TRIM(login_method) = '')");

        // mname -> pin backfill and reverse fill for compatibility.
        $conn->query("UPDATE account
                SET pin = mname
                WHERE (pin IS NULL OR TRIM(pin) = '')
                    AND mname IS NOT NULL
                    AND TRIM(mname) <> ''");

        $conn->query("UPDATE account
                SET mname = pin
                WHERE (mname IS NULL OR TRIM(mname) = '')
                    AND pin IS NOT NULL
                    AND TRIM(pin) <> ''");
} catch (Throwable $e) {}

try {
    foreach (['db_migration_v2', 'db_migration_v3', 'db_migration_v4', 'db_migration_v5', 'db_migration_v6', 'db_migration_v7', 'db_migration_v8', 'db_migration_v9'] as $__mk) {
        $conn->query(
            "INSERT INTO site_settings (`" . $_ssKeyCol . "`, `" . $_ssValCol . "`) VALUES ('" . $__mk . "','done') " .
            "ON DUPLICATE KEY UPDATE `" . $_ssValCol . "`='done'"
        );
    }
} catch (Throwable $e) {}

// ── Auto-Migration v10 ─────────────────────────────────────────────────────────
// v10: temp_transfer.amount INT → DECIMAL(20,8) so fractional amounts are stored
//      correctly; strip legacy "balance - label" prefix from account_types.type_key.
try {
    $r10 = $conn->query("SELECT `" . $_ssValCol . "` AS migration_value FROM site_settings WHERE `" . $_ssKeyCol . "`='db_migration_v10' LIMIT 1");
    if (!($r10 && $r10->num_rows > 0 && ($r10->fetch_assoc()['migration_value'] ?? '') === 'done')) {

        // Change temp_transfer.amount from INT to DECIMAL if not already.
        $amtCol = $conn->query("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='temp_transfer' AND COLUMN_NAME='amount'");
        if ($amtCol && ($amtRow = $amtCol->fetch_assoc()) && strtolower($amtRow['DATA_TYPE'] ?? '') !== 'decimal') {
            $conn->query("ALTER TABLE `temp_transfer` MODIFY COLUMN `amount` DECIMAL(20,8) NOT NULL DEFAULT 0");
        }

        // Strip legacy "NNNN - " numeric prefix from account_types.type_key values.
        $conn->query("UPDATE `account_types` SET `type_key` = TRIM(SUBSTRING_INDEX(`type_key`, ' - ', -1)) WHERE `type_key` REGEXP '^[0-9]+[[:space:]]*-[[:space:]]*'");

        $conn->query(
            "INSERT INTO site_settings (`" . $_ssKeyCol . "`, `" . $_ssValCol . "`) VALUES ('db_migration_v10','done') " .
            "ON DUPLICATE KEY UPDATE `" . $_ssValCol . "`='done'"
        );
    }
} catch (Throwable $e) {}

// ── Auto-Migration v11 ─────────────────────────────────────────────────────────
// v11: Treat acc_no as internal customer ID while adding IBAN fields on
// customer_accounts for external account references.
try {
    $r11 = $conn->query("SELECT `" . $_ssValCol . "` AS migration_value FROM site_settings WHERE `" . $_ssKeyCol . "`='db_migration_v11' LIMIT 1");
    if (!($r11 && $r11->num_rows > 0 && ($r11->fetch_assoc()['migration_value'] ?? '') === 'done')) {

        if (!$_colExists($conn, 'customer_accounts', 'iban')) {
            $conn->query("ALTER TABLE `customer_accounts` ADD COLUMN `iban` VARCHAR(34) NULL DEFAULT NULL");
        }
        if (!$_colExists($conn, 'customer_accounts', 'bban')) {
            $conn->query("ALTER TABLE `customer_accounts` ADD COLUMN `bban` VARCHAR(30) NULL DEFAULT NULL");
        }
        if (!$_colExists($conn, 'customer_accounts', 'account_display')) {
            $conn->query("ALTER TABLE `customer_accounts` ADD COLUMN `account_display` VARCHAR(64) NULL DEFAULT NULL");
        }

        $ibanIndexExists = false;
        $idxRes = $conn->query("SHOW INDEX FROM `customer_accounts` WHERE Key_name='uq_customer_accounts_iban'");
        if ($idxRes && $idxRes->num_rows > 0) {
            $ibanIndexExists = true;
        }
        if (!$ibanIndexExists) {
            $conn->query("ALTER TABLE `customer_accounts` ADD UNIQUE KEY `uq_customer_accounts_iban` (`iban`)");
        }

        $conn->query("UPDATE customer_accounts SET account_display = account_no WHERE account_display IS NULL OR TRIM(account_display) = ''");

        $ibanCountry = fw_setting_get($conn, 'iban_country', 'GB');
        $ibanBankCode = fw_setting_get($conn, 'iban_bank_code', 'FWLT');

        $rows = $conn->query("SELECT id, owner_acc_no, currency_code, account_no FROM customer_accounts WHERE iban IS NULL OR TRIM(iban) = ''");
        if ($rows) {
            while ($row = $rows->fetch_assoc()) {
                $id = (int)($row['id'] ?? 0);
                if ($id <= 0) {
                    continue;
                }
                $owner = (string)($row['owner_acc_no'] ?? '');
                $cur = (string)($row['currency_code'] ?? 'USD');
                $legacyNo = (string)($row['account_no'] ?? '');
                $ibanData = fw_generate_iban($owner, $cur, $id, $ibanCountry, $ibanBankCode);

                $ibanEsc = $conn->real_escape_string($ibanData['iban']);
                $bbanEsc = $conn->real_escape_string($ibanData['bban']);
                $displayEsc = $conn->real_escape_string($ibanData['display']);
                $legacyNoEsc = $conn->real_escape_string($legacyNo);

                $conn->query("UPDATE customer_accounts
                    SET iban = '{$ibanEsc}',
                        bban = '{$bbanEsc}',
                        account_display = CASE
                            WHEN account_display IS NULL OR TRIM(account_display) = '' THEN '{$displayEsc}'
                            ELSE account_display
                        END,
                        account_no = CASE
                            WHEN account_no IS NULL OR TRIM(account_no) = '' THEN '{$legacyNoEsc}'
                            ELSE account_no
                        END
                    WHERE id = {$id}");
            }
        }

        $conn->query(
            "INSERT INTO site_settings (`" . $_ssKeyCol . "`, `" . $_ssValCol . "`) VALUES ('db_migration_v11','done') " .
            "ON DUPLICATE KEY UPDATE `" . $_ssValCol . "`='done'"
        );
    }
} catch (Throwable $e) {}

// ── Auto-Migration v13 ─────────────────────────────────────────────────────────
// v13: Transfer status management + IBAN editing capability by admins.
try {
    $r13 = $conn->query("SELECT `" . $_ssValCol . "` AS migration_value FROM site_settings WHERE `" . $_ssKeyCol . "`='db_migration_v13' LIMIT 1");
    if (!($r13 && $r13->num_rows > 0 && ($r13->fetch_assoc()['migration_value'] ?? '') === 'done')) {

        // Add transfer status tracking columns
        if (!$_colExists($conn, 'transfer', 'status_updated_by')) {
            $conn->query("ALTER TABLE `transfer` ADD COLUMN `status_updated_by` VARCHAR(190) NULL DEFAULT NULL");
        }
        if (!$_colExists($conn, 'transfer', 'status_updated_at')) {
            $conn->query("ALTER TABLE `transfer` ADD COLUMN `status_updated_at` DATETIME NULL DEFAULT NULL");
        }
        if (!$_colExists($conn, 'transfer', 'status_notes')) {
            $conn->query("ALTER TABLE `transfer` ADD COLUMN `status_notes` TEXT NULL DEFAULT NULL");
        }
        if (!$_colExists($conn, 'transfer', 'auto_update_enabled')) {
            $conn->query("ALTER TABLE `transfer` ADD COLUMN `auto_update_enabled` TINYINT(1) NOT NULL DEFAULT 0");
        }
        if (!$_colExists($conn, 'transfer', 'auto_update_at')) {
            $conn->query("ALTER TABLE `transfer` ADD COLUMN `auto_update_at` DATETIME NULL DEFAULT NULL");
        }

        // Add IBAN management columns
        if (!$_colExists($conn, 'customer_accounts', 'iban_custom')) {
            $conn->query("ALTER TABLE `customer_accounts` ADD COLUMN `iban_custom` TINYINT(1) NOT NULL DEFAULT 0");
        }
        if (!$_colExists($conn, 'customer_accounts', 'iban_updated_by')) {
            $conn->query("ALTER TABLE `customer_accounts` ADD COLUMN `iban_updated_by` VARCHAR(190) NULL DEFAULT NULL");
        }
        if (!$_colExists($conn, 'customer_accounts', 'iban_updated_at')) {
            $conn->query("ALTER TABLE `customer_accounts` ADD COLUMN `iban_updated_at` DATETIME NULL DEFAULT NULL");
        }

        // Create transfer status history audit table
        $conn->query("CREATE TABLE IF NOT EXISTS `transfer_status_history` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `transfer_id` INT NOT NULL,
            `old_status` VARCHAR(20) NULL DEFAULT NULL,
            `new_status` VARCHAR(20) NOT NULL,
            `changed_by` VARCHAR(190) NOT NULL,
            `changed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `notes` TEXT NULL DEFAULT NULL,
            KEY `idx_transfer_id` (`transfer_id`),
            KEY `idx_changed_at` (`changed_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Create IBAN change history audit table
        $conn->query("CREATE TABLE IF NOT EXISTS `iban_change_history` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `customer_account_id` INT NOT NULL,
            `old_iban` VARCHAR(34) NULL DEFAULT NULL,
            `new_iban` VARCHAR(34) NOT NULL,
            `changed_by` VARCHAR(190) NOT NULL,
            `changed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `change_reason` VARCHAR(100) NULL DEFAULT NULL,
            KEY `idx_account_id` (`customer_account_id`),
            KEY `idx_changed_at` (`changed_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Create transfer configuration settings table
        $conn->query("CREATE TABLE IF NOT EXISTS `transfer_settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `setting_key` VARCHAR(100) NOT NULL UNIQUE,
            `setting_value` TEXT NULL,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `idx_setting_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Insert default transfer settings
        $conn->query("INSERT IGNORE INTO transfer_settings (setting_key, setting_value, updated_at) VALUES
            ('auto_update_enabled', '1', NOW()),
            ('auto_update_delay_minutes', '1440', NOW()),
            ('auto_update_target_status', 'successful', NOW())");

        $conn->query(
            "INSERT INTO site_settings (`" . $_ssKeyCol . "`, `" . $_ssValCol . "`) VALUES ('db_migration_v13','done') " .
            "ON DUPLICATE KEY UPDATE `" . $_ssValCol . "`='done'"
        );
    }
} catch (Throwable $e) {}

// ── Auto-Migration v14 ─────────────────────────────────────────────────────────
// v14: Normalize transfer.status and set default to pending.
try {
    $r14 = $conn->query("SELECT `" . $_ssValCol . "` AS migration_value FROM site_settings WHERE `" . $_ssKeyCol . "`='db_migration_v14' LIMIT 1");
    if (!($r14 && $r14->num_rows > 0 && ($r14->fetch_assoc()['migration_value'] ?? '') === 'done')) {

        $conn->query("ALTER TABLE `transfer` MODIFY COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'pending'");
        $conn->query("UPDATE `transfer` SET `status` = LOWER(TRIM(`status`)) WHERE `status` IS NOT NULL");
        $conn->query("UPDATE `transfer` SET `status` = 'pending' WHERE `status` IS NULL OR TRIM(`status`) = ''");

        $conn->query(
            "INSERT INTO site_settings (`" . $_ssKeyCol . "`, `" . $_ssValCol . "`) VALUES ('db_migration_v14','done') " .
            "ON DUPLICATE KEY UPDATE `" . $_ssValCol . "`='done'"
        );
    }
} catch (Throwable $e) {}
