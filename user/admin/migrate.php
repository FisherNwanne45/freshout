<?php
session_start();
require_once __DIR__ . '/class.admin.php';
include_once __DIR__ . '/session.php';
require_once dirname(__DIR__, 2) . '/config.php';

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$logs = [];
$hadErrors = false;
$didRun = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migration'])) || isset($_GET['run']);

$hardcodedSiteSettings = [
    'theme' => 'theme5',
    'auth_color_scheme' => 'classic',
    'frontend_color_scheme' => 'classic',
    'translator_languages' => 'en,es,fr,de,it,pt,ru,zh-CN',

    'dormant_transfer_message' => 'Your account is currently inactive. Please contact support to reactivate transfer access.',
    'dormant_message' => 'Your account is currently inactive. Please contact support to reactivate transfer access.',

    'success_transfer_title' => 'Transfer Initiated',
    'success_transfer_note' => 'International transfers are processed within 2-3 business days.',
    'transfer_success_title' => 'Transfer Initiated',
    'transfer_success_note' => 'International transfers are processed within 2-3 business days.',
    'transfer_failure_title' => 'Transfer Failed',
    'transfer_failure_note' => 'We could not complete your transfer. Please verify details and try again.',

    'tx_max_codes' => '3',
    'tx_code1_name' => 'TAC',
    'tx_code2_name' => 'IMF',
    'tx_code3_name' => 'FIU',
    'tx_code4_name' => 'LPPI',
    'tx_code5_name' => 'CODE5',

    'promo_enabled' => '0',
    'promo_image_url' => '',
    'promo_headline' => '',
    'promo_body' => '',
    'promo_btn_label' => '',
    'promo_btn_url' => '',
    'promo_card_enabled' => '0',
    'promo_popup_enabled' => '0',
    'promo_popup_condition' => 'once_session',

    'site_favicon' => '',

    'registration_welcome' => 'enabled',
    'debit_alert' => 'enabled',
    'application_approved' => 'enabled',
    'application_declined' => 'enabled',
    'ticket_alert' => 'enabled',
    'loan_alert' => 'enabled',
    'transaction_alert' => 'enabled',

    'sms_enabled' => '0',
    'sms_provider' => 'textbelt',
    'sms_brand_name' => 'Fisher Wallet',
    'twilio_sid' => '',
    'twilio_token' => '',
    'twilio_from' => '',
    'termii_api_key' => '',
    'termii_sender' => 'N-Alert',
    'textbelt_key' => 'textbelt',

    'db_migration_v2' => 'done',
    'db_migration_v3' => 'done',
    'db_migration_v4' => 'done',
    'db_migration_v5' => 'done',
    'db_migration_v6' => 'done',
    'db_migration_v7' => 'done',
    'db_migration_v8' => 'done',
    'db_migration_v9' => 'done',
    'db_migration_v10' => 'done',
    'db_migration_v11' => 'done',
    'db_migration_v12' => 'done',
    'db_migration_v13' => 'done',
    'db_migration_v14' => 'done',
];

$hardcodedSiteRow = [
    'name' => 'Fisher Wallet',
    'addr' => 'Global Financial District',
    'phone' => 'TOLL FREE',
    'email' => 'support@example.com',
    'login' => 'user',
    'color' => '#1d4ed8',
    'code1' => 'TAC',
    'code2' => 'IMF',
    'code3' => 'FIU',
    'code1b' => 'TRANSFER AUTHORIZATION CODE',
    'code2b' => 'INTERNATIONAL MONETARY FUND',
    'code3b' => 'FINANCIAL INTELLIGENCE UNIT CODE'
];

if ($didRun) {
    $log = static function (string $line) use (&$logs): void {
        $logs[] = $line;
    };

    $exec = static function (mysqli $conn, string $sql, string $okMsg, string $skipMsg = '') use (&$hadErrors, $log): bool {
        $res = $conn->query($sql);
        if ($res === false) {
            $hadErrors = true;
            $log('[ERROR] ' . $okMsg . ' :: ' . $conn->error);
            return false;
        }
        $log($skipMsg !== '' ? $skipMsg : ('[OK] ' . $okMsg));
        return true;
    };

    $tableExists = static function (mysqli $conn, string $table): bool {
        $safe = $conn->real_escape_string($table);
        $r = $conn->query("SELECT COUNT(*) AS n FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '" . $safe . "'");
        return $r && ((int)($r->fetch_assoc()['n'] ?? 0) > 0);
    };

    $columnExists = static function (mysqli $conn, string $table, string $column): bool {
        $tbl = $conn->real_escape_string($table);
        $col = $conn->real_escape_string($column);
        $r = $conn->query("SELECT COUNT(*) AS n FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '" . $tbl . "' AND column_name = '" . $col . "'");
        return $r && ((int)($r->fetch_assoc()['n'] ?? 0) > 0);
    };

    $indexExists = static function (mysqli $conn, string $table, string $index): bool {
        $tbl = $conn->real_escape_string($table);
        $idx = $conn->real_escape_string($index);
        $r = $conn->query("SELECT COUNT(*) AS n FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = '" . $tbl . "' AND index_name = '" . $idx . "'");
        return $r && ((int)($r->fetch_assoc()['n'] ?? 0) > 0);
    };

    $log('[START] Build migration started');

    if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_errno) {
        $hadErrors = true;
        $log('[ERROR] Database connection is not available in config.php.');
    } else {
        $log('[OK] Connected to target database: ' . $conn->real_escape_string((string)($APP_CONFIG['db']['name'] ?? 'unknown')));

        $exec(
            $conn,
            "CREATE TABLE IF NOT EXISTS `site_settings` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `key` VARCHAR(191) NOT NULL,
                `value` LONGTEXT NULL,
                `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_site_settings_key` (`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            'Ensured table site_settings exists'
        );

        if ($columnExists($conn, 'site_settings', 'setting_key') && $columnExists($conn, 'site_settings', 'setting_value')) {
            $exec(
                $conn,
                "INSERT INTO site_settings (`key`, `value`)
                 SELECT `setting_key`, `setting_value`
                 FROM site_settings
                 WHERE `setting_key` IS NOT NULL AND TRIM(`setting_key`) <> ''
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
                'Merged legacy setting_key/setting_value rows into key/value schema'
            );
        }

        $schemaSql = [
            "CREATE TABLE IF NOT EXISTS currencies (
              id INT AUTO_INCREMENT PRIMARY KEY,
              code VARCHAR(10) NOT NULL,
              symbol VARCHAR(10) NOT NULL,
                            flag_code VARCHAR(8) NOT NULL DEFAULT '',
              name VARCHAR(60) NOT NULL,
              is_crypto TINYINT(1) NOT NULL DEFAULT 0,
              is_active TINYINT(1) NOT NULL DEFAULT 1,
              sort_order INT NOT NULL DEFAULT 0,
              UNIQUE KEY uq_code (code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS account_types (
              id INT AUTO_INCREMENT PRIMARY KEY,
              label VARCHAR(80) NOT NULL,
              type_key VARCHAR(100) NOT NULL,
              min_balance DECIMAL(15,2) NOT NULL,
              is_active TINYINT(1) NOT NULL DEFAULT 1,
              UNIQUE KEY uq_type_key (type_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS account_balances (
              id INT AUTO_INCREMENT PRIMARY KEY,
              acc_no VARCHAR(20) NOT NULL,
              currency_code VARCHAR(10) NOT NULL,
              balance DECIMAL(20,8) NOT NULL DEFAULT 0,
              UNIQUE KEY uq_acc_cur (acc_no, currency_code),
              KEY idx_acc (acc_no)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS customer_accounts (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            owner_acc_no VARCHAR(50) NOT NULL,
                            account_no VARCHAR(40) NOT NULL,
                            currency_code VARCHAR(10) NOT NULL,
                            balance DECIMAL(20,8) NOT NULL DEFAULT 0,
                            status VARCHAR(20) NOT NULL DEFAULT 'active',
                            is_primary TINYINT(1) NOT NULL DEFAULT 0,
                            iban VARCHAR(34) NULL DEFAULT NULL,
                            bban VARCHAR(30) NULL DEFAULT NULL,
                            account_display VARCHAR(64) NULL DEFAULT NULL,
                            iban_custom TINYINT(1) NOT NULL DEFAULT 0,
                            iban_updated_by VARCHAR(190) NULL DEFAULT NULL,
                            iban_updated_at DATETIME NULL DEFAULT NULL,
                            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            UNIQUE KEY uq_account_no (account_no),
                            UNIQUE KEY uq_owner_currency (owner_acc_no, currency_code),
                            KEY idx_owner (owner_acc_no),
                            KEY idx_currency (currency_code)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS exchange_rates (
              id INT AUTO_INCREMENT PRIMARY KEY,
              from_code VARCHAR(10) NOT NULL,
              to_code VARCHAR(10) NOT NULL,
              rate DECIMAL(20,8) NOT NULL,
              source ENUM('manual','api') NOT NULL DEFAULT 'manual',
              updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              UNIQUE KEY uq_pair (from_code, to_code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS loan_applications (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS card_requests (
              id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
              request_ref VARCHAR(32) NOT NULL,
              acc_no VARCHAR(50) NOT NULL,
              card_type VARCHAR(30) NOT NULL DEFAULT 'debit',
              card_tier VARCHAR(30) NOT NULL DEFAULT 'standard',
              status VARCHAR(30) NOT NULL DEFAULT 'requested',
              issue_note TEXT NULL,
              requested_at DATETIME NOT NULL,
              updated_at DATETIME NOT NULL,
              UNIQUE KEY uq_card_request_ref (request_ref),
              KEY idx_card_request_acc_status (acc_no, status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS cards (
              id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
              card_ref VARCHAR(32) NOT NULL,
              acc_no VARCHAR(50) NOT NULL,
              request_id BIGINT UNSIGNED NULL,
              masked_pan VARCHAR(24) NOT NULL,
              token_ref VARCHAR(128) NULL,
              expiry_mm_yy VARCHAR(10) NOT NULL,
              status VARCHAR(30) NOT NULL DEFAULT 'issued',
              card_limit DECIMAL(18,2) NOT NULL DEFAULT 0.00,
              currency_code VARCHAR(10) NOT NULL DEFAULT 'USD',
              created_at DATETIME NOT NULL,
              updated_at DATETIME NOT NULL,
              UNIQUE KEY uq_card_ref (card_ref),
              KEY idx_cards_acc_status (acc_no, status),
              KEY idx_cards_request (request_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS term_deposits (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS investment_accounts (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS investment_positions (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS robo_profiles (
              id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
              acc_no VARCHAR(50) NOT NULL,
              score INT NOT NULL,
              risk_band VARCHAR(30) NOT NULL,
              model_name VARCHAR(60) NOT NULL,
              rebalancing_frequency VARCHAR(30) NOT NULL DEFAULT 'quarterly',
              created_at DATETIME NOT NULL,
              updated_at DATETIME NOT NULL,
              KEY idx_robo_profile_acc (acc_no)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS product_activity (
              id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
              acc_no VARCHAR(50) NOT NULL,
              product_type VARCHAR(30) NOT NULL,
              product_ref VARCHAR(32) NOT NULL,
              event_type VARCHAR(40) NOT NULL,
              status VARCHAR(30) NOT NULL,
              amount DECIMAL(18,2) NULL,
              currency_code VARCHAR(10) NULL,
              details VARCHAR(255) NULL,
              event_at DATETIME NOT NULL,
              created_at DATETIME NOT NULL,
              KEY idx_product_activity_acc_date (acc_no, event_at),
              KEY idx_product_activity_ref (product_type, product_ref)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS beneficiaries (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            acc_no VARCHAR(50) NOT NULL,
                            nick_name VARCHAR(100) NOT NULL,
                            bank_name VARCHAR(150) NOT NULL,
                            account_number VARCHAR(60) NOT NULL,
                            swift VARCHAR(30) DEFAULT NULL,
                            routing VARCHAR(30) DEFAULT NULL,
                            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            KEY idx_bene_acc (acc_no)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS ticket_replies (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            ticket_id INT NOT NULL,
                            sender_role VARCHAR(20) NOT NULL DEFAULT 'customer',
                            sender_name VARCHAR(150) DEFAULT NULL,
                            msg TEXT NOT NULL,
                            is_read_user TINYINT(1) NOT NULL DEFAULT 0,
                            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            INDEX idx_ticket (ticket_id)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS site_branches (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            branch_name VARCHAR(100) NOT NULL DEFAULT '',
                            address VARCHAR(255) NOT NULL DEFAULT '',
                            phone VARCHAR(50) NOT NULL DEFAULT '',
                            sort_order INT NOT NULL DEFAULT 99,
                            is_active TINYINT(1) NOT NULL DEFAULT 1,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS account_otp_codes (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            acc_no VARCHAR(50) NULL,
                            email VARCHAR(190) NULL,
                            purpose VARCHAR(50) NOT NULL,
                            otp_code VARCHAR(10) NOT NULL,
                            expires_at DATETIME NOT NULL,
                            used_at DATETIME NULL,
                            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            INDEX idx_otp_acc (acc_no, purpose, otp_code, used_at),
                            INDEX idx_otp_email (email, purpose, otp_code, used_at),
                            INDEX idx_otp_exp (expires_at)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS crypto_deposit_wallets (
                            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            currency_code VARCHAR(10) NOT NULL,
                            network_name VARCHAR(60) NOT NULL DEFAULT '',
                            wallet_label VARCHAR(120) NOT NULL DEFAULT '',
                            wallet_address VARCHAR(255) NOT NULL DEFAULT '',
                            qr_code_path VARCHAR(255) NULL DEFAULT NULL,
                            instructions TEXT NULL,
                            is_active TINYINT(1) NOT NULL DEFAULT 1,
                            created_at DATETIME NOT NULL,
                            updated_at DATETIME NOT NULL,
                            UNIQUE KEY uq_crypto_wallet_currency (currency_code),
                            KEY idx_crypto_wallet_active (is_active)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS crypto_deposit_requests (
                            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            deposit_ref VARCHAR(32) NOT NULL,
                            acc_no VARCHAR(50) NOT NULL,
                            email VARCHAR(190) NOT NULL,
                            currency_code VARCHAR(10) NOT NULL,
                            network_name VARCHAR(60) NOT NULL DEFAULT '',
                            wallet_address VARCHAR(255) NOT NULL DEFAULT '',
                            sender_wallet_address VARCHAR(255) NOT NULL DEFAULT '',
                            tx_hash VARCHAR(255) NOT NULL DEFAULT '',
                            amount DECIMAL(20,8) NOT NULL DEFAULT 0,
                            proof_path VARCHAR(255) NULL DEFAULT NULL,
                            user_note TEXT NULL,
                            admin_note TEXT NULL,
                            status VARCHAR(20) NOT NULL DEFAULT 'pending',
                            approved_by VARCHAR(190) NULL DEFAULT NULL,
                            approved_at DATETIME NULL DEFAULT NULL,
                            created_at DATETIME NOT NULL,
                            updated_at DATETIME NOT NULL,
                            UNIQUE KEY uq_crypto_deposit_ref (deposit_ref),
                            KEY idx_crypto_deposit_acc_status (acc_no, status),
                            KEY idx_crypto_deposit_currency (currency_code)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS crypto_withdrawal_requests (
                            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            withdrawal_ref VARCHAR(32) NOT NULL,
                            acc_no VARCHAR(50) NOT NULL,
                            email VARCHAR(190) NOT NULL,
                            currency_code VARCHAR(10) NOT NULL,
                            network_name VARCHAR(60) NOT NULL DEFAULT '',
                            destination_address VARCHAR(255) NOT NULL DEFAULT '',
                            amount DECIMAL(20,8) NOT NULL DEFAULT 0,
                            user_note TEXT NULL,
                            admin_note TEXT NULL,
                            status VARCHAR(20) NOT NULL DEFAULT 'pending',
                            processed_by VARCHAR(190) NULL DEFAULT NULL,
                            processed_at DATETIME NULL DEFAULT NULL,
                            created_at DATETIME NOT NULL,
                            updated_at DATETIME NOT NULL,
                            UNIQUE KEY uq_crypto_withdrawal_ref (withdrawal_ref),
                            KEY idx_crypto_withdrawal_acc_status (acc_no, status),
                            KEY idx_crypto_withdrawal_currency (currency_code)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS crypto_transfers (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            acc_no VARCHAR(20) NOT NULL,
                            email VARCHAR(120) NOT NULL,
                            currency_code VARCHAR(10) NOT NULL,
                            amount DECIMAL(20,8) NOT NULL DEFAULT 0,
                            wallet_address VARCHAR(200) NOT NULL DEFAULT '',
                            network VARCHAR(50) NOT NULL DEFAULT '',
                            tx_hash VARCHAR(200) DEFAULT NULL,
                            status ENUM('pending','confirmed','failed') NOT NULL DEFAULT 'pending',
                            remarks TEXT,
                            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            KEY idx_acc (acc_no),
                            KEY idx_email (email)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS transfer_status_history (
                            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            transfer_id INT NOT NULL,
                            old_status VARCHAR(20) NULL DEFAULT NULL,
                            new_status VARCHAR(20) NOT NULL,
                            changed_by VARCHAR(190) NOT NULL,
                            changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            notes TEXT NULL DEFAULT NULL,
                            KEY idx_transfer_id (transfer_id),
                            KEY idx_changed_at (changed_at)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS iban_change_history (
                            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            customer_account_id INT NOT NULL,
                            old_iban VARCHAR(34) NULL DEFAULT NULL,
                            new_iban VARCHAR(34) NOT NULL,
                            changed_by VARCHAR(190) NOT NULL,
                            changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            change_reason VARCHAR(100) NULL DEFAULT NULL,
                            KEY idx_account_id (customer_account_id),
                            KEY idx_changed_at (changed_at)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS transfer_settings (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            setting_key VARCHAR(100) NOT NULL UNIQUE,
                            setting_value TEXT NULL,
                            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            KEY idx_setting_key (setting_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        ];

        foreach ($schemaSql as $idx => $sql) {
            $exec($conn, $sql, 'Schema step ' . ($idx + 1) . ' applied');
        }

        $seedSql = [
            "INSERT IGNORE INTO currencies (code,symbol,name,is_crypto,is_active,sort_order) VALUES
            ('USD','$','US Dollar',0,1,1),
            ('GBP','£','British Pound',0,1,2),
            ('EUR','€','Euro',0,1,3),
            ('CHF','CHF','Swiss Franc',0,1,4),
            ('BTC','BTC','Bitcoin',1,1,5),
            ('ETH','ETH','Ethereum',1,1,6),
            ('USDT','USDT','Tether USD',1,1,7)",

            "INSERT IGNORE INTO account_types (label,type_key,min_balance,is_active) VALUES
            ('Savings Account',   'Savings',         1050.00, 1),
            ('Current Account',   'Current',         3650.00, 1),
            ('Checking Account',  'Checking',        7500.00, 1),
            ('Fixed Deposit',     'Fixed_Deposit',  10000.00, 1)",

            "INSERT IGNORE INTO exchange_rates (from_code,to_code,rate,source) VALUES
            ('USD','GBP',0.79000000,'manual'),
            ('USD','EUR',0.92000000,'manual'),
            ('USD','CHF',0.90000000,'manual'),
            ('USD','BTC',0.00001053,'manual'),
            ('USD','ETH',0.00031250,'manual'),
            ('USD','USDT',1.00000000,'manual')"
        ];

        foreach ($seedSql as $sql) {
            $exec($conn, $sql, 'Seed data ensured');
        }

        $columnSql = [
            ['currencies', 'flag_code', "ALTER TABLE `currencies` ADD COLUMN `flag_code` VARCHAR(8) NOT NULL DEFAULT '' AFTER `symbol`"],
            ['message', 'is_read', "ALTER TABLE `message` ADD COLUMN `is_read` TINYINT(1) NOT NULL DEFAULT 0"],
            ['ticket_replies', 'is_read_user', "ALTER TABLE `ticket_replies` ADD COLUMN `is_read_user` TINYINT(1) NOT NULL DEFAULT 0"],
            ['transfer', 'currency_code', "ALTER TABLE `transfer` ADD COLUMN `currency_code` VARCHAR(10) NOT NULL DEFAULT 'USD'"],
            ['transfer', 'transfer_type', "ALTER TABLE `transfer` ADD COLUMN `transfer_type` VARCHAR(20) NOT NULL DEFAULT 'standard'"],
            ['transfer', 'source_account_no', "ALTER TABLE `transfer` ADD COLUMN `source_account_no` VARCHAR(40) NULL DEFAULT NULL"],
            ['transfer', 'destination_account_no', "ALTER TABLE `transfer` ADD COLUMN `destination_account_no` VARCHAR(40) NULL DEFAULT NULL"],
            ['transfer', 'status_updated_by', "ALTER TABLE `transfer` ADD COLUMN `status_updated_by` VARCHAR(190) NULL DEFAULT NULL"],
            ['transfer', 'status_updated_at', "ALTER TABLE `transfer` ADD COLUMN `status_updated_at` DATETIME NULL DEFAULT NULL"],
            ['transfer', 'status_notes', "ALTER TABLE `transfer` ADD COLUMN `status_notes` TEXT NULL DEFAULT NULL"],
            ['transfer', 'auto_update_enabled', "ALTER TABLE `transfer` ADD COLUMN `auto_update_enabled` TINYINT(1) NOT NULL DEFAULT 0"],
            ['transfer', 'auto_update_at', "ALTER TABLE `transfer` ADD COLUMN `auto_update_at` DATETIME NULL DEFAULT NULL"],
            ['temp_transfer', 'currency_code', "ALTER TABLE `temp_transfer` ADD COLUMN `currency_code` VARCHAR(10) NOT NULL DEFAULT 'USD'"],
            ['temp_transfer', 'transfer_type', "ALTER TABLE `temp_transfer` ADD COLUMN `transfer_type` VARCHAR(20) NOT NULL DEFAULT 'standard'"],
            ['temp_transfer', 'source_account_no', "ALTER TABLE `temp_transfer` ADD COLUMN `source_account_no` VARCHAR(40) NULL DEFAULT NULL"],
            ['temp_transfer', 'destination_account_no', "ALTER TABLE `temp_transfer` ADD COLUMN `destination_account_no` VARCHAR(40) NULL DEFAULT NULL"],
            ['account', 'auth_method', "ALTER TABLE `account` ADD COLUMN `auth_method` VARCHAR(20) NULL DEFAULT NULL"],
            ['account', 'login_method', "ALTER TABLE `account` ADD COLUMN `login_method` VARCHAR(20) NULL DEFAULT NULL"],
            ['customer_accounts', 'iban', "ALTER TABLE `customer_accounts` ADD COLUMN `iban` VARCHAR(34) NULL DEFAULT NULL"],
            ['customer_accounts', 'bban', "ALTER TABLE `customer_accounts` ADD COLUMN `bban` VARCHAR(30) NULL DEFAULT NULL"],
            ['customer_accounts', 'account_display', "ALTER TABLE `customer_accounts` ADD COLUMN `account_display` VARCHAR(64) NULL DEFAULT NULL"],
            ['customer_accounts', 'iban_custom', "ALTER TABLE `customer_accounts` ADD COLUMN `iban_custom` TINYINT(1) NOT NULL DEFAULT 0"],
            ['customer_accounts', 'iban_updated_by', "ALTER TABLE `customer_accounts` ADD COLUMN `iban_updated_by` VARCHAR(190) NULL DEFAULT NULL"],
            ['customer_accounts', 'iban_updated_at', "ALTER TABLE `customer_accounts` ADD COLUMN `iban_updated_at` DATETIME NULL DEFAULT NULL"],
        ];

        foreach ($columnSql as [$tableName, $columnName, $sql]) {
            if ($tableExists($conn, $tableName) && !$columnExists($conn, $tableName, $columnName)) {
                $exec($conn, $sql, 'Added missing column ' . $tableName . '.' . $columnName);
            } else {
                $log('[SKIP] Column ' . $tableName . '.' . $columnName . ' already present or table missing.');
            }
        }

        if ($tableExists($conn, 'customer_accounts') && !$indexExists($conn, 'customer_accounts', 'uq_customer_accounts_iban')) {
            $exec($conn, "ALTER TABLE `customer_accounts` ADD UNIQUE KEY `uq_customer_accounts_iban` (`iban`)", 'Added unique index uq_customer_accounts_iban');
        } else {
            $log('[SKIP] Index customer_accounts.uq_customer_accounts_iban already present or table missing.');
        }

        if ($tableExists($conn, 'temp_transfer')) {
            $exec($conn, "ALTER TABLE `temp_transfer` MODIFY COLUMN `amount` DECIMAL(20,8) NOT NULL DEFAULT 0", 'Normalized temp_transfer.amount to DECIMAL(20,8)');
        } else {
            $log('[SKIP] temp_transfer table missing; amount normalization skipped.');
        }

        if ($tableExists($conn, 'transfer')) {
            $exec($conn, "ALTER TABLE `transfer` MODIFY COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'pending'", 'Normalized transfer.status definition');
        } else {
            $log('[SKIP] transfer table missing; status normalization skipped.');
        }

        if ($tableExists($conn, 'account')) {
            $haspin = $columnExists($conn, 'account', 'pin');
            $hasPin = $columnExists($conn, 'account', 'pin');

            if ($haspin && !$hasPin) {
                $exec($conn, "ALTER TABLE `account` CHANGE COLUMN `pin` `pin` VARCHAR(100) NOT NULL", 'Renamed account.pin to account.pin');
            } elseif ($haspin && $hasPin) {
                $exec(
                    $conn,
                    "UPDATE `account` SET `pin` = `pin` WHERE (`pin` IS NULL OR TRIM(`pin`) = '') AND `pin` IS NOT NULL AND TRIM(`pin`) <> ''",
                    'Backfilled account.pin from account.pin (pin already existed)'
                );
                $log('[SKIP] account.pin was not renamed because account.pin already exists. Data has been copied to pin.');
            } elseif (!$haspin && $hasPin) {
                $log('[SKIP] account.pin already present and account.pin not found.');
            } else {
                $log('[SKIP] account table does not have pin or pin columns.');
            }
        } else {
            $log('[SKIP] account table not found; rename step skipped.');
        }

        $excludedKeys = [
            'smtp_host',
            'smtp_port',
            'smtp_secure',
            'smtp_username',
            'smtp_password',
            'smtp_from',
            'smtp_from_name',
            'smtp_reply_to'
        ];

        foreach ($hardcodedSiteSettings as $k => $v) {
            if (in_array($k, $excludedKeys, true)) {
                continue;
            }
            $ks = $conn->real_escape_string((string)$k);
            $vs = $conn->real_escape_string((string)$v);
            $exec(
                $conn,
                "INSERT INTO site_settings (`key`, `value`) VALUES ('{$ks}','{$vs}') ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
                'Applied setting: ' . $k
            );
        }

        if ($tableExists($conn, 'transfer_settings')) {
            $transferSettingSeeds = [
                'auto_update_enabled' => '1',
                'auto_update_delay_minutes' => '1440',
                'auto_update_target_status' => 'successful',
                'initial_transfer_status' => 'pending',
            ];
            foreach ($transferSettingSeeds as $key => $value) {
                $ks = $conn->real_escape_string($key);
                $vs = $conn->real_escape_string($value);
                $exec($conn, "INSERT INTO `transfer_settings` (`setting_key`, `setting_value`) VALUES ('{$ks}','{$vs}') ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)", 'Applied transfer setting: ' . $key);
            }
        } else {
            $log('[SKIP] transfer_settings table missing; transfer config seed skipped.');
        }

        if ($tableExists($conn, 'site')) {
            $siteRowRes = $conn->query("SELECT id FROM site ORDER BY id ASC LIMIT 1");
            $siteId = 1;
            if ($siteRowRes && $siteRowRes->num_rows > 0) {
                $siteId = (int)($siteRowRes->fetch_assoc()['id'] ?? 1);
            }

            $sets = [];
            foreach ($hardcodedSiteRow as $k => $v) {
                $sets[] = "`" . $conn->real_escape_string($k) . "`='" . $conn->real_escape_string((string)$v) . "'";
            }
            if (!empty($sets)) {
                $exec($conn, "UPDATE `site` SET " . implode(', ', $sets) . " WHERE id=" . $siteId, 'Updated site table with hardcoded admin profile');
            }
        } else {
            $log('[SKIP] site table not found; site profile update skipped.');
        }

        $autoMigratePath = dirname(__DIR__) . '/partials/auto-migrate.php';
        if (is_file($autoMigratePath)) {
            require $autoMigratePath;
            $log('[OK] Ran built-in auto migration steps (v2-v14)');
        } else {
            $log('[SKIP] Built-in auto migration file not found.');
        }
    }

    $log($hadErrors ? '[DONE] Migration finished with errors. Review the log above.' : '[DONE] Migration completed successfully.');
}

$pageTitle = 'Build Migration';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<div class="max-w-5xl space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800">Remote Build Migration</h2>
        <p class="text-sm text-gray-600 mt-2">This runs an idempotent schema and settings migration for older databases after deploying this codebase. SMTP keys are excluded by design.</p>
        <form method="post" class="mt-4">
            <button type="submit" name="run_migration" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer">Run Build Migration</button>
            <a href="?run=1" class="inline-flex items-center gap-2 ml-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium px-4 py-2 rounded-lg transition-colors">Run via URL</a>
        </form>
    </div>

    <?php if ($didRun): ?>
        <div class="bg-slate-950 text-slate-100 rounded-xl border border-slate-800 p-5">
            <h3 class="text-sm font-semibold tracking-wide uppercase text-slate-300 mb-3">Migration Progress Log</h3>
            <div class="text-xs leading-6 font-mono whitespace-pre-wrap"><?php foreach ($logs as $line) {
                                                                                echo htmlspecialchars($line) . "\n";
                                                                            } ?></div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>