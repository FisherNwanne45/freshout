<?php
/**
 * Shared schema definitions used by both migrate.php and install.php.
 * Returns arrays of SQL strings; does NOT execute anything.
 */

if (!function_exists('fw_schema_legacy_tables')) {
    /**
     * CREATE TABLE statements for the original legacy tables that must exist
     * before the new feature-tables can be added.
     */
    function fw_schema_legacy_tables(): array {
        return [
            "CREATE TABLE IF NOT EXISTS `admin` (
                `id`             int(10)          NOT NULL AUTO_INCREMENT,
                `uname`          varchar(40)      NOT NULL,
                `upass`          varchar(255)     NOT NULL,
                `email`          varchar(100)     NOT NULL,
                `verified_count` enum('Y','N')    DEFAULT 'Y',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `site` (
                `id`    int(11)       NOT NULL,
                `image` varchar(100)  NOT NULL DEFAULT '',
                `qr`    varchar(100)  NOT NULL DEFAULT '',
                `name`  varchar(100)  NOT NULL DEFAULT '',
                `addr`  varchar(100)  NOT NULL DEFAULT '',
                `phone` varchar(100)  NOT NULL DEFAULT '',
                `email` varchar(100)  NOT NULL DEFAULT '',
                `tawk`  varchar(10000) NOT NULL DEFAULT '',
                `tawkk` varchar(100)  NOT NULL DEFAULT '',
                `tawk2` varchar(100)  NOT NULL DEFAULT '',
                `year`  varchar(100)  NOT NULL DEFAULT '',
                `url`   varchar(100)  NOT NULL DEFAULT '',
                `urlh`  varchar(100)  NOT NULL DEFAULT '',
                `login` varchar(100)  NOT NULL DEFAULT 'user',
                `color` varchar(50)   NOT NULL DEFAULT '#1d4ed8',
                `code1` varchar(100)  NOT NULL DEFAULT '',
                `code2` varchar(100)  NOT NULL DEFAULT '',
                `code3` varchar(100)  NOT NULL DEFAULT '',
                `code1b` varchar(100) NOT NULL DEFAULT '',
                `code2b` varchar(100) NOT NULL DEFAULT '',
                `code3b` varchar(100) NOT NULL DEFAULT '',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `account` (
                `id`           int(10)        NOT NULL AUTO_INCREMENT,
                `acc_no`       varchar(20)    NOT NULL,
                `uname`        varchar(100)   NOT NULL,
                `upass`        varchar(100)   NOT NULL,
                `upass2`       varchar(100)   NOT NULL DEFAULT '',
                `email`        varchar(100)   NOT NULL,
                `type`         varchar(100)   NOT NULL DEFAULT '',
                `fname`        varchar(100)   NOT NULL,
                `pin`          varchar(100)   NOT NULL DEFAULT '',
                `lname`        varchar(100)   NOT NULL,
                `addr`         varchar(100)   NOT NULL DEFAULT '',
                `work`         varchar(100)   NOT NULL DEFAULT '',
                `sex`          varchar(10)    NOT NULL DEFAULT '',
                `dob`          varchar(30)    NOT NULL DEFAULT '',
                `phone`        varchar(50)    NOT NULL DEFAULT '',
                `reg_date`     varchar(25)    DEFAULT NULL,
                `marry`        varchar(20)    NOT NULL DEFAULT '',
                `t_bal`        int(20)        NOT NULL DEFAULT 0,
                `a_bal`        int(20)        NOT NULL DEFAULT 0,
                `logins`       int(50)        DEFAULT 0,
                `status`       enum('Active','Dormant/Inactive','Disabled','Closed','pincode','otp') DEFAULT 'Active',
                `currency`     varchar(5)     NOT NULL DEFAULT 'USD',
                `cot`          varchar(20)    NOT NULL DEFAULT '',
                `tax`          varchar(20)    NOT NULL DEFAULT '',
                `lppi`         varchar(20)    NOT NULL DEFAULT '',
                `imf`          varchar(20)    NOT NULL DEFAULT '',
                `pp`           varchar(100)   NOT NULL DEFAULT '',
                `image`        varchar(100)   NOT NULL DEFAULT '',
                `ccard`        varchar(100)   NOT NULL DEFAULT '',
                `ccdate`       varchar(100)   NOT NULL DEFAULT '',
                `cvv`          varchar(100)   NOT NULL DEFAULT '',
                `loan`         varchar(100)   NOT NULL DEFAULT '',
                `intra`        varchar(100)   NOT NULL DEFAULT '',
                `lodur`        varchar(100)   NOT NULL DEFAULT '',
                `auth_method`  varchar(20)    DEFAULT NULL,
                `login_method` varchar(20)    DEFAULT NULL,
                `code5`        varchar(20)    NOT NULL DEFAULT '',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `alerts` (
                `id`          int(100)    NOT NULL AUTO_INCREMENT,
                `uname`       varchar(40) NOT NULL,
                `amount`      int(40)     NOT NULL DEFAULT 0,
                `sender_name` varchar(40) NOT NULL DEFAULT '',
                `type`        varchar(10) NOT NULL DEFAULT '',
                `remarks`     varchar(100) NOT NULL DEFAULT '',
                `date`        varchar(20) NOT NULL DEFAULT '',
                `time`        varchar(20) NOT NULL DEFAULT '',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `message` (
                `id`          int(10)      NOT NULL AUTO_INCREMENT,
                `sender_name` varchar(40)  NOT NULL DEFAULT '',
                `reci_name`   varchar(40)  NOT NULL DEFAULT '',
                `subject`     varchar(100) NOT NULL DEFAULT '',
                `msg`         varchar(2000) NOT NULL DEFAULT '',
                `read`        enum('unread','opened') DEFAULT 'unread',
                `date`        timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `is_read`     tinyint(1)   NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `ticket` (
                `id`          int(10)      NOT NULL AUTO_INCREMENT,
                `tc`          int(10)      NOT NULL DEFAULT 0,
                `sender_name` varchar(40)  NOT NULL DEFAULT '',
                `mail`        varchar(40)  DEFAULT NULL,
                `subject`     varchar(100) NOT NULL DEFAULT '',
                `msg`         varchar(1000) NOT NULL DEFAULT '',
                `status`      enum('Pending','Replied') DEFAULT 'Pending',
                `date`        timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `transfer` (
                `id`                    int(10)       NOT NULL AUTO_INCREMENT,
                `amount`                varchar(100)  NOT NULL DEFAULT '',
                `bank_name`             varchar(40)   NOT NULL DEFAULT '',
                `acc_name`              varchar(100)  NOT NULL DEFAULT '',
                `acc_no`                varchar(100)  NOT NULL DEFAULT '',
                `reci_name`             varchar(30)   NOT NULL DEFAULT '',
                `type`                  varchar(20)   NOT NULL DEFAULT '',
                `swift`                 varchar(100)  NOT NULL DEFAULT '',
                `routing`               varchar(100)  NOT NULL DEFAULT '',
                `remarks`               varchar(500)  NOT NULL DEFAULT '',
                `email`                 varchar(100)  DEFAULT NULL,
                `status`                varchar(20)   NOT NULL DEFAULT 'pending',
                `date`                  timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `currency_code`         varchar(10)   NOT NULL DEFAULT 'USD',
                `transfer_type`         varchar(20)   NOT NULL DEFAULT 'standard',
                `source_account_no`     varchar(40)   DEFAULT NULL,
                `destination_account_no` varchar(40)  DEFAULT NULL,
                `status_updated_by`     varchar(190)  DEFAULT NULL,
                `status_updated_at`     datetime      DEFAULT NULL,
                `status_notes`          text          DEFAULT NULL,
                `auto_update_enabled`   tinyint(1)    NOT NULL DEFAULT 0,
                `auto_update_at`        datetime      DEFAULT NULL,
                `reversal_processed`    tinyint(1)    NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `temp_transfer` (
                `id`                    int(100)      NOT NULL AUTO_INCREMENT,
                `email`                 varchar(40)   NOT NULL DEFAULT '',
                `amount`                decimal(20,8) NOT NULL DEFAULT 0,
                `acc_no`                varchar(30)   NOT NULL DEFAULT '',
                `acc_name`              varchar(60)   NOT NULL DEFAULT '',
                `bank_name`             varchar(40)   NOT NULL DEFAULT '',
                `swift`                 varchar(15)   NOT NULL DEFAULT '',
                `routing`               varchar(20)   NOT NULL DEFAULT '',
                `type`                  varchar(20)   NOT NULL DEFAULT '',
                `remarks`               text          NOT NULL,
                `currency_code`         varchar(10)   NOT NULL DEFAULT 'USD',
                `transfer_type`         varchar(20)   NOT NULL DEFAULT 'standard',
                `source_account_no`     varchar(40)   DEFAULT NULL,
                `destination_account_no` varchar(40)  DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `temp_account` (
                `id`       int(10)      NOT NULL AUTO_INCREMENT,
                `upass`    varchar(100) NOT NULL DEFAULT '',
                `email`    varchar(100) NOT NULL DEFAULT '',
                `type`     varchar(100) NOT NULL DEFAULT '',
                `fname`    varchar(100) NOT NULL DEFAULT '',
                `mname`    varchar(100) NOT NULL DEFAULT '',
                `lname`    varchar(100) NOT NULL DEFAULT '',
                `addr`     varchar(100) NOT NULL DEFAULT '',
                `work`     varchar(100) NOT NULL DEFAULT '',
                `sex`      varchar(10)  NOT NULL DEFAULT '',
                `dob`      varchar(30)  NOT NULL DEFAULT '',
                `ip`       varchar(30)  NOT NULL DEFAULT '',
                `phone`    varchar(50)  NOT NULL DEFAULT '',
                `reg_date` timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `marry`    varchar(20)  NOT NULL DEFAULT '',
                `currency` varchar(5)   NOT NULL DEFAULT 'USD',
                `code`     varchar(6)   NOT NULL DEFAULT '',
                `verify`   enum('Y','N') DEFAULT 'N',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        ];
    }
}

if (!function_exists('fw_schema_feature_tables')) {
    /**
     * CREATE TABLE statements for all feature/new tables (same list as migrate.php).
     */
    function fw_schema_feature_tables(): array {
        return [
            "CREATE TABLE IF NOT EXISTS `site_settings` (
                `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `key`        VARCHAR(191)    NOT NULL,
                `value`      LONGTEXT        NULL,
                `created_at` TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_site_settings_key` (`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `currencies` (
                `id`         INT AUTO_INCREMENT PRIMARY KEY,
                `code`       VARCHAR(10)  NOT NULL,
                `symbol`     VARCHAR(10)  NOT NULL,
                `flag_code`  VARCHAR(8)   NOT NULL DEFAULT '',
                `name`       VARCHAR(60)  NOT NULL,
                `is_crypto`  TINYINT(1)   NOT NULL DEFAULT 0,
                `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
                `sort_order` INT          NOT NULL DEFAULT 0,
                UNIQUE KEY `uq_code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `account_types` (
                `id`          INT AUTO_INCREMENT PRIMARY KEY,
                `label`       VARCHAR(80)    NOT NULL,
                `type_key`    VARCHAR(100)   NOT NULL,
                `min_balance` DECIMAL(15,2)  NOT NULL,
                `is_active`   TINYINT(1)     NOT NULL DEFAULT 1,
                UNIQUE KEY `uq_type_key` (`type_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `account_balances` (
                `id`            INT AUTO_INCREMENT PRIMARY KEY,
                `acc_no`        VARCHAR(20)   NOT NULL,
                `currency_code` VARCHAR(10)   NOT NULL,
                `balance`       DECIMAL(20,8) NOT NULL DEFAULT 0,
                `total_balance` DECIMAL(20,8) NOT NULL DEFAULT 0,
                `available_balance` DECIMAL(20,8) NOT NULL DEFAULT 0,
                UNIQUE KEY `uq_acc_cur` (`acc_no`, `currency_code`),
                KEY `idx_acc` (`acc_no`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `customer_accounts` (
                `id`              INT AUTO_INCREMENT PRIMARY KEY,
                `owner_acc_no`    VARCHAR(50)  NOT NULL,
                `account_no`      VARCHAR(40)  NOT NULL,
                `currency_code`   VARCHAR(10)  NOT NULL,
                `balance`         DECIMAL(20,8) NOT NULL DEFAULT 0,
                `status`          VARCHAR(20)  NOT NULL DEFAULT 'active',
                `is_primary`      TINYINT(1)   NOT NULL DEFAULT 0,
                `iban`            VARCHAR(34)  NULL DEFAULT NULL,
                `bban`            VARCHAR(30)  NULL DEFAULT NULL,
                `account_display` VARCHAR(64)  NULL DEFAULT NULL,
                `iban_custom`     TINYINT(1)   NOT NULL DEFAULT 0,
                `iban_updated_by` VARCHAR(190) NULL DEFAULT NULL,
                `iban_updated_at` DATETIME     NULL DEFAULT NULL,
                `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uq_account_no` (`account_no`),
                UNIQUE KEY `uq_owner_currency` (`owner_acc_no`, `currency_code`),
                UNIQUE KEY `uq_customer_accounts_iban` (`iban`),
                KEY `idx_owner` (`owner_acc_no`),
                KEY `idx_currency` (`currency_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `exchange_rates` (
                `id`         INT AUTO_INCREMENT PRIMARY KEY,
                `from_code`  VARCHAR(10)   NOT NULL,
                `to_code`    VARCHAR(10)   NOT NULL,
                `rate`       DECIMAL(20,8) NOT NULL,
                `source`     ENUM('manual','api') NOT NULL DEFAULT 'manual',
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uq_pair` (`from_code`, `to_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `loan_applications` (
                `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `application_ref`  VARCHAR(32)  NOT NULL,
                `acc_no`           VARCHAR(50)  NOT NULL,
                `email`            VARCHAR(190) NOT NULL,
                `full_name`        VARCHAR(190) NOT NULL,
                `purpose`          VARCHAR(255) NOT NULL,
                `amount`           DECIMAL(18,2) NOT NULL DEFAULT 0.00,
                `currency_code`    VARCHAR(10)  NOT NULL DEFAULT 'USD',
                `details`          TEXT         NULL,
                `status`           VARCHAR(30)  NOT NULL DEFAULT 'submitted',
                `admin_note`       TEXT         NULL,
                `reviewed_by`      VARCHAR(190) NULL,
                `reviewed_at`      DATETIME     NULL,
                `created_at`       DATETIME     NOT NULL,
                `updated_at`       DATETIME     NOT NULL,
                UNIQUE KEY `uq_loan_ref` (`application_ref`),
                KEY `idx_loan_acc_status` (`acc_no`, `status`),
                KEY `idx_loan_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `card_requests` (
                `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `request_ref`  VARCHAR(32) NOT NULL,
                `acc_no`       VARCHAR(50) NOT NULL,
                `card_type`    VARCHAR(30) NOT NULL DEFAULT 'debit',
                `card_tier`    VARCHAR(30) NOT NULL DEFAULT 'standard',
                `status`       VARCHAR(30) NOT NULL DEFAULT 'requested',
                `issue_note`   TEXT        NULL,
                `requested_at` DATETIME    NOT NULL,
                `updated_at`   DATETIME    NOT NULL,
                UNIQUE KEY `uq_card_request_ref` (`request_ref`),
                KEY `idx_card_request_acc_status` (`acc_no`, `status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `cards` (
                `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `card_ref`      VARCHAR(32)   NOT NULL,
                `acc_no`        VARCHAR(50)   NOT NULL,
                `request_id`    BIGINT UNSIGNED NULL,
                `masked_pan`    VARCHAR(24)   NOT NULL,
                `token_ref`     VARCHAR(128)  NULL,
                `expiry_mm_yy`  VARCHAR(10)   NOT NULL,
                `status`        VARCHAR(30)   NOT NULL DEFAULT 'issued',
                `card_limit`    DECIMAL(18,2) NOT NULL DEFAULT 0.00,
                `currency_code` VARCHAR(10)   NOT NULL DEFAULT 'USD',
                `created_at`    DATETIME      NOT NULL,
                `updated_at`    DATETIME      NOT NULL,
                UNIQUE KEY `uq_card_ref` (`card_ref`),
                KEY `idx_cards_acc_status` (`acc_no`, `status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `term_deposits` (
                `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `deposit_ref`     VARCHAR(32)   NOT NULL,
                `acc_no`          VARCHAR(50)   NOT NULL,
                `principal`       DECIMAL(18,2) NOT NULL,
                `annual_rate`     DECIMAL(7,4)  NOT NULL,
                `tenor_months`    INT           NOT NULL,
                `start_date`      DATE          NOT NULL,
                `maturity_date`   DATE          NOT NULL,
                `maturity_amount` DECIMAL(18,2) NOT NULL,
                `status`          VARCHAR(30)   NOT NULL DEFAULT 'active',
                `payout_mode`     VARCHAR(20)   NOT NULL DEFAULT 'payout',
                `currency_code`   VARCHAR(10)   NOT NULL DEFAULT 'USD',
                `created_at`      DATETIME      NOT NULL,
                `updated_at`      DATETIME      NOT NULL,
                UNIQUE KEY `uq_term_deposit_ref` (`deposit_ref`),
                KEY `idx_term_deposit_acc_status` (`acc_no`, `status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `investment_accounts` (
                `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `account_ref`   VARCHAR(32) NOT NULL,
                `acc_no`        VARCHAR(50) NOT NULL,
                `base_currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
                `risk_profile`  VARCHAR(30) NOT NULL DEFAULT 'moderate',
                `status`        VARCHAR(30) NOT NULL DEFAULT 'active',
                `created_at`    DATETIME NOT NULL,
                `updated_at`    DATETIME NOT NULL,
                UNIQUE KEY `uq_investment_account_ref` (`account_ref`),
                KEY `idx_investment_acc_status` (`acc_no`, `status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `investment_positions` (
                `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `investment_account_id` BIGINT UNSIGNED NOT NULL,
                `symbol`                VARCHAR(30)   NOT NULL,
                `instrument_type`       VARCHAR(20)   NOT NULL,
                `quantity`              DECIMAL(24,8) NOT NULL DEFAULT 0,
                `avg_price`             DECIMAL(18,6) NOT NULL DEFAULT 0,
                `market_price`          DECIMAL(18,6) NOT NULL DEFAULT 0,
                `market_value`          DECIMAL(18,2) NOT NULL DEFAULT 0,
                `updated_at`            DATETIME NOT NULL,
                KEY `idx_position_account` (`investment_account_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `robo_profiles` (
                `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `acc_no`                VARCHAR(50) NOT NULL,
                `score`                 INT         NOT NULL,
                `risk_band`             VARCHAR(30) NOT NULL,
                `model_name`            VARCHAR(60) NOT NULL,
                `rebalancing_frequency` VARCHAR(30) NOT NULL DEFAULT 'quarterly',
                `created_at`            DATETIME NOT NULL,
                `updated_at`            DATETIME NOT NULL,
                KEY `idx_robo_profile_acc` (`acc_no`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `product_activity` (
                `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `acc_no`       VARCHAR(50)   NOT NULL,
                `product_type` VARCHAR(30)   NOT NULL,
                `product_ref`  VARCHAR(32)   NOT NULL,
                `event_type`   VARCHAR(40)   NOT NULL,
                `status`       VARCHAR(30)   NOT NULL,
                `amount`       DECIMAL(18,2) NULL,
                `currency_code` VARCHAR(10)  NULL,
                `details`      VARCHAR(255)  NULL,
                `event_at`     DATETIME      NOT NULL,
                `created_at`   DATETIME      NOT NULL,
                KEY `idx_product_activity_acc_date` (`acc_no`, `event_at`),
                KEY `idx_product_activity_ref` (`product_type`, `product_ref`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `beneficiaries` (
                `id`             INT AUTO_INCREMENT PRIMARY KEY,
                `acc_no`         VARCHAR(50)  NOT NULL,
                `nick_name`      VARCHAR(100) NOT NULL DEFAULT '',
                `bank_name`      VARCHAR(150) NOT NULL DEFAULT '',
                `account_number` VARCHAR(60)  NOT NULL DEFAULT '',
                `swift`          VARCHAR(30)  DEFAULT NULL,
                `routing`        VARCHAR(30)  DEFAULT NULL,
                `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY `idx_bene_acc` (`acc_no`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `ticket_replies` (
                `id`          INT AUTO_INCREMENT PRIMARY KEY,
                `ticket_id`   INT         NOT NULL,
                `sender_role` VARCHAR(20) NOT NULL DEFAULT 'customer',
                `sender_name` VARCHAR(150) DEFAULT NULL,
                `msg`         TEXT        NOT NULL,
                `is_read_user` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at`  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY `idx_ticket` (`ticket_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `site_branches` (
                `id`          INT AUTO_INCREMENT PRIMARY KEY,
                `branch_name` VARCHAR(100) NOT NULL DEFAULT '',
                `address`     VARCHAR(255) NOT NULL DEFAULT '',
                `phone`       VARCHAR(50)  NOT NULL DEFAULT '',
                `sort_order`  INT          NOT NULL DEFAULT 99,
                `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
                `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `account_otp_codes` (
                `id`         INT AUTO_INCREMENT PRIMARY KEY,
                `acc_no`     VARCHAR(50)  NULL,
                `email`      VARCHAR(190) NULL,
                `purpose`    VARCHAR(50)  NOT NULL,
                `otp_code`   VARCHAR(10)  NOT NULL,
                `expires_at` DATETIME     NOT NULL,
                `used_at`    DATETIME     NULL,
                `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_otp_acc`   (`acc_no`, `purpose`, `otp_code`, `used_at`),
                INDEX `idx_otp_email` (`email`, `purpose`, `otp_code`, `used_at`),
                INDEX `idx_otp_exp`   (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `crypto_deposit_wallets` (
                `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `currency_code`  VARCHAR(10)  NOT NULL,
                `network_name`   VARCHAR(60)  NOT NULL DEFAULT '',
                `wallet_label`   VARCHAR(120) NOT NULL DEFAULT '',
                `wallet_address` VARCHAR(255) NOT NULL DEFAULT '',
                `qr_code_path`   VARCHAR(255) NULL DEFAULT NULL,
                `instructions`   TEXT         NULL,
                `is_active`      TINYINT(1)   NOT NULL DEFAULT 1,
                `created_at`     DATETIME     NOT NULL,
                `updated_at`     DATETIME     NOT NULL,
                UNIQUE KEY `uq_crypto_wallet_currency` (`currency_code`),
                KEY `idx_crypto_wallet_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `crypto_deposit_requests` (
                `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `deposit_ref`           VARCHAR(32)   NOT NULL,
                `acc_no`                VARCHAR(50)   NOT NULL,
                `email`                 VARCHAR(190)  NOT NULL,
                `currency_code`         VARCHAR(10)   NOT NULL,
                `network_name`          VARCHAR(60)   NOT NULL DEFAULT '',
                `wallet_address`        VARCHAR(255)  NOT NULL DEFAULT '',
                `sender_wallet_address` VARCHAR(255)  NOT NULL DEFAULT '',
                `tx_hash`               VARCHAR(255)  NOT NULL DEFAULT '',
                `amount`                DECIMAL(20,8) NOT NULL DEFAULT 0,
                `proof_path`            VARCHAR(255)  NULL DEFAULT NULL,
                `user_note`             TEXT          NULL,
                `admin_note`            TEXT          NULL,
                `status`                VARCHAR(20)   NOT NULL DEFAULT 'pending',
                `approved_by`           VARCHAR(190)  NULL DEFAULT NULL,
                `approved_at`           DATETIME      NULL DEFAULT NULL,
                `created_at`            DATETIME      NOT NULL,
                `updated_at`            DATETIME      NOT NULL,
                UNIQUE KEY `uq_crypto_deposit_ref` (`deposit_ref`),
                KEY `idx_crypto_deposit_acc_status` (`acc_no`, `status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `crypto_withdrawal_requests` (
                `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `withdrawal_ref`      VARCHAR(32)   NOT NULL,
                `acc_no`              VARCHAR(50)   NOT NULL,
                `email`               VARCHAR(190)  NOT NULL,
                `currency_code`       VARCHAR(10)   NOT NULL,
                `network_name`        VARCHAR(60)   NOT NULL DEFAULT '',
                `destination_address` VARCHAR(255)  NOT NULL DEFAULT '',
                `amount`              DECIMAL(20,8) NOT NULL DEFAULT 0,
                `user_note`           TEXT          NULL,
                `admin_note`          TEXT          NULL,
                `status`              VARCHAR(20)   NOT NULL DEFAULT 'pending',
                `processed_by`        VARCHAR(190)  NULL DEFAULT NULL,
                `processed_at`        DATETIME      NULL DEFAULT NULL,
                `created_at`          DATETIME      NOT NULL,
                `updated_at`          DATETIME      NOT NULL,
                UNIQUE KEY `uq_crypto_withdrawal_ref` (`withdrawal_ref`),
                KEY `idx_crypto_withdrawal_acc_status` (`acc_no`, `status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `crypto_transfers` (
                `id`             INT AUTO_INCREMENT PRIMARY KEY,
                `acc_no`         VARCHAR(20)   NOT NULL,
                `email`          VARCHAR(120)  NOT NULL,
                `currency_code`  VARCHAR(10)   NOT NULL,
                `amount`         DECIMAL(20,8) NOT NULL DEFAULT 0,
                `wallet_address` VARCHAR(200)  NOT NULL DEFAULT '',
                `network`        VARCHAR(50)   NOT NULL DEFAULT '',
                `tx_hash`        VARCHAR(200)  DEFAULT NULL,
                `status`         ENUM('pending','confirmed','failed') NOT NULL DEFAULT 'pending',
                `remarks`        TEXT,
                `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY `idx_acc`   (`acc_no`),
                KEY `idx_email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `transfer_status_history` (
                `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `transfer_id` INT         NOT NULL,
                `old_status`  VARCHAR(20) NULL DEFAULT NULL,
                `new_status`  VARCHAR(20) NOT NULL,
                `changed_by`  VARCHAR(190) NOT NULL,
                `changed_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `notes`       TEXT         NULL DEFAULT NULL,
                KEY `idx_transfer_id` (`transfer_id`),
                KEY `idx_changed_at`  (`changed_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `iban_change_history` (
                `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `customer_account_id` INT          NOT NULL,
                `old_iban`            VARCHAR(34)  NULL DEFAULT NULL,
                `new_iban`            VARCHAR(34)  NOT NULL,
                `changed_by`          VARCHAR(190) NOT NULL,
                `changed_at`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `change_reason`       VARCHAR(100) NULL DEFAULT NULL,
                KEY `idx_account_id` (`customer_account_id`),
                KEY `idx_changed_at` (`changed_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `transfer_settings` (
                `id`            INT AUTO_INCREMENT PRIMARY KEY,
                `setting_key`   VARCHAR(100) NOT NULL UNIQUE,
                `setting_value` TEXT         NULL,
                `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `idx_setting_key` (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        ];
    }
}

if (!function_exists('fw_schema_seed_data')) {
    /**
     * Returns seed SQL for currencies, account_types, exchange_rates, transfer_settings.
     * All use INSERT IGNORE / ON DUPLICATE KEY so they are safe to re-run.
     */
    function fw_schema_seed_data(): array {
        return [
            "INSERT IGNORE INTO `currencies` (code, symbol, flag_code, name, is_crypto, is_active, sort_order) VALUES
                ('USD', '$',    'us', 'US Dollar',    0, 1, 1),
                ('GBP', '£',    'gb', 'British Pound', 0, 1, 2),
                ('EUR', '€',    'eu', 'Euro',          0, 1, 3),
                ('CHF', 'CHF',  'ch', 'Swiss Franc',   0, 1, 4),
                ('BTC', 'BTC',  '',   'Bitcoin',        1, 1, 5),
                ('ETH', 'ETH',  '',   'Ethereum',       1, 1, 6),
                ('USDT','USDT', '',   'Tether USD',     1, 1, 7)",

            "INSERT IGNORE INTO `account_types` (label, type_key, min_balance, is_active) VALUES
                ('Savings Account',  'Savings',       1050.00, 1),
                ('Current Account',  'Current',       3650.00, 1),
                ('Checking Account', 'Checking',      7500.00, 1),
                ('Fixed Deposit',    'Fixed_Deposit', 10000.00, 1)",

            "INSERT IGNORE INTO `exchange_rates` (from_code, to_code, rate, source) VALUES
                ('USD','GBP',  0.79000000, 'manual'),
                ('USD','EUR',  0.92000000, 'manual'),
                ('USD','CHF',  0.90000000, 'manual'),
                ('USD','BTC',  0.00001053, 'manual'),
                ('USD','ETH',  0.00031250, 'manual'),
                ('USD','USDT', 1.00000000, 'manual')",

            "INSERT INTO `transfer_settings` (setting_key, setting_value) VALUES
                ('auto_update_enabled',       '1'),
                ('auto_update_delay_minutes', '1440'),
                ('auto_update_target_status', 'successful'),
                ('initial_transfer_status',   'pending')
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
        ];
    }
}

if (!function_exists('fw_schema_default_site_settings')) {
    /**
     * Returns the default site_settings key=>value map.
     * Keys that should be overridden by the installer (smtp, branding, etc.)
     * are included here as empty strings so they exist in the table.
     */
    function fw_schema_default_site_settings(): array {
        return [
            'theme'                   => 'theme5',
            'auth_color_scheme'       => 'classic',
            'frontend_color_scheme'   => 'classic',
            'translator_languages'    => 'en,es,fr,de,it,pt,ru,zh-CN',
            'dormant_transfer_message' => 'Your account is currently inactive. Please contact support to reactivate transfer access.',
            'dormant_message'         => 'Your account is currently inactive. Please contact support to reactivate transfer access.',
            'success_transfer_title'  => 'Transfer Initiated',
            'success_transfer_note'   => 'International transfers are processed within 2-3 business days.',
            'transfer_success_title'  => 'Transfer Initiated',
            'transfer_success_note'   => 'International transfers are processed within 2-3 business days.',
            'transfer_failure_title'  => 'Transfer Failed',
            'transfer_failure_note'   => 'We could not complete your transfer. Please verify details and try again.',
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
            'tx_max_codes'            => '3',
            'tx_code1_name'           => 'TAC',
            'tx_code2_name'           => 'IMF',
            'tx_code3_name'           => 'FIU',
            'tx_code4_name'           => 'LPPI',
            'tx_code5_name'           => 'CODE5',
            'promo_enabled'           => '0',
            'promo_image_url'         => '',
            'promo_headline'          => '',
            'promo_body'              => '',
            'promo_btn_label'         => '',
            'promo_btn_url'           => '',
            'promo_card_enabled'      => '0',
            'promo_popup_enabled'     => '0',
            'promo_popup_condition'   => 'once_session',
            'site_favicon'            => '',
            'auth_logo_url'           => '',
            'dashboard_logo_url'      => '',
            'frontend_logo_url'       => '',
            'admin_logo_url'          => '',
            'iban_country'            => 'GB',
            'iban_bank_code'          => 'FWLT',
            'registration_welcome'    => 'enabled',
            'debit_alert'             => 'enabled',
            'application_approved'    => 'enabled',
            'application_declined'    => 'enabled',
            'ticket_alert'            => 'enabled',
            'loan_alert'              => 'enabled',
            'transaction_alert'       => 'enabled',
            'smtp_host'               => '',
            'smtp_port'               => '465',
            'smtp_secure'             => 'ssl',
            'smtp_username'           => '',
            'smtp_password'           => '',
            'smtp_from'               => '',
            'smtp_from_name'          => '',
            'smtp_reply_to'           => '',
            'sms_enabled'             => '0',
            'sms_provider'            => 'textbelt',
            'sms_brand_name'          => '',
            'twilio_sid'              => '',
            'twilio_token'            => '',
            'twilio_from'             => '',
            'termii_api_key'          => '',
            'termii_sender'           => 'N-Alert',
            'textbelt_key'            => 'textbelt',
        ];
    }
}
