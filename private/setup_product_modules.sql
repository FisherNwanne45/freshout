-- Product modules foundation schema
-- Run once on your MySQL database.

CREATE TABLE IF NOT EXISTS loan_applications (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS card_requests (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cards (
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
  KEY idx_cards_request (request_id),
  CONSTRAINT fk_cards_request FOREIGN KEY (request_id) REFERENCES card_requests(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS term_deposits (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS investment_accounts (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS investment_positions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  investment_account_id BIGINT UNSIGNED NOT NULL,
  symbol VARCHAR(30) NOT NULL,
  instrument_type VARCHAR(20) NOT NULL,
  quantity DECIMAL(24,8) NOT NULL DEFAULT 0,
  avg_price DECIMAL(18,6) NOT NULL DEFAULT 0,
  market_price DECIMAL(18,6) NOT NULL DEFAULT 0,
  market_value DECIMAL(18,2) NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL,
  KEY idx_position_account (investment_account_id),
  CONSTRAINT fk_position_invest_account FOREIGN KEY (investment_account_id) REFERENCES investment_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS robo_profiles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  acc_no VARCHAR(50) NOT NULL,
  score INT NOT NULL,
  risk_band VARCHAR(30) NOT NULL,
  model_name VARCHAR(60) NOT NULL,
  rebalancing_frequency VARCHAR(30) NOT NULL DEFAULT 'quarterly',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  KEY idx_robo_profile_acc (acc_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS product_activity (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
