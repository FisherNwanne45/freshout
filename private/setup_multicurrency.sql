-- Multi-currency system setup
-- Run: mysql -u root --password="" fresh < /Applications/XAMPP/xamppfiles/htdocs/fresh/private/setup_multicurrency.sql

CREATE TABLE IF NOT EXISTS currencies (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  code       VARCHAR(10)  NOT NULL,
  symbol     VARCHAR(10)  NOT NULL,
  name       VARCHAR(60)  NOT NULL,
  is_crypto  TINYINT(1)   NOT NULL DEFAULT 0,
  is_active  TINYINT(1)   NOT NULL DEFAULT 1,
  sort_order INT          NOT NULL DEFAULT 0,
  UNIQUE KEY uq_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO currencies (code,symbol,name,is_crypto,is_active,sort_order) VALUES
('USD','$','US Dollar',0,1,1),
('GBP','£','British Pound',0,1,2),
('EUR','€','Euro',0,1,3),
('CHF','CHF','Swiss Franc',0,1,4),
('BTC','BTC','Bitcoin',1,1,5),
('ETH','ETH','Ethereum',1,1,6),
('USDT','USDT','Tether USD',1,1,7);

CREATE TABLE IF NOT EXISTS account_types (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  label       VARCHAR(80)    NOT NULL,
  type_key    VARCHAR(100)   NOT NULL,
  min_balance DECIMAL(15,2)  NOT NULL,
  is_active   TINYINT(1)     NOT NULL DEFAULT 1,
  UNIQUE KEY uq_type_key (type_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO account_types (label,type_key,min_balance,is_active) VALUES
('Savings Account',   '1050 - Savings ',         1050.00, 1),
('Current Account',   '3650 - Current  ',        3650.00, 1),
('Checking Account',  '7500 - Checking  ',       7500.00, 1),
('Fixed Deposit',     '10000 - Fixed Deposit  ', 10000.00, 1);

CREATE TABLE IF NOT EXISTS account_balances (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  acc_no        VARCHAR(20)    NOT NULL,
  currency_code VARCHAR(10)    NOT NULL,
  balance       DECIMAL(20,8)  NOT NULL DEFAULT 0,
  UNIQUE KEY uq_acc_cur (acc_no, currency_code),
  KEY idx_acc (acc_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customer_accounts (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  owner_acc_no   VARCHAR(50)    NOT NULL,
  account_no     VARCHAR(40)    NOT NULL,
  currency_code  VARCHAR(10)    NOT NULL,
  balance        DECIMAL(20,8)  NOT NULL DEFAULT 0,
  status         VARCHAR(20)    NOT NULL DEFAULT 'active',
  is_primary     TINYINT(1)     NOT NULL DEFAULT 0,
  created_at     TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_account_no (account_no),
  UNIQUE KEY uq_owner_currency (owner_acc_no, currency_code),
  KEY idx_owner (owner_acc_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- source_account_no / destination_account_no are added by auto-migrate.php for existing schemas.

CREATE TABLE IF NOT EXISTS exchange_rates (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  from_code   VARCHAR(10)    NOT NULL,
  to_code     VARCHAR(10)    NOT NULL,
  rate        DECIMAL(20,8)  NOT NULL,
  source      ENUM('manual','api') NOT NULL DEFAULT 'manual',
  updated_at  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_pair (from_code, to_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO exchange_rates (from_code,to_code,rate,source) VALUES
('USD','GBP',0.79000000,'manual'),
('USD','EUR',0.92000000,'manual'),
('USD','CHF',0.90000000,'manual'),
('USD','BTC',0.00001053,'manual'),
('USD','ETH',0.00031250,'manual'),
('USD','USDT',1.00000000,'manual'),
('GBP','USD',1.26500000,'manual'),
('GBP','EUR',1.16500000,'manual'),
('EUR','USD',1.08700000,'manual'),
('EUR','GBP',0.85800000,'manual'),
('CHF','USD',1.11000000,'manual'),
('BTC','USD',94900.00000000,'manual'),
('ETH','USD',3200.00000000,'manual'),
('USDT','USD',1.00000000,'manual');
